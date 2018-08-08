<?php
require_once 'config.php';
/**
 * SPDX-License-Identifier: GPL-3.0-or-later
 * Copyright (c) 2018 Zane Zakraisek
 *
 * Functions for Authentication and Authorization.
 *
 * NOTE: All usernames are references to 
 *       sAMAccountName.
 */

/**
 * Authenticates a user
 *
 * @param string $username
 * @param string $pass
 * @return int 1 on success, 0 on failure
 */
function auth($username, $password){
  $auth = 0;
  if(!is_null(_ldap_connect($username, $password))){
    $auth = 1;
  }
  return $auth; 
}

/**
 * Returns an array of information on the user
 *
 * @param string $username samaccountname
 * @return array consisting of first name, last name, and username
 *         null on error
 */
function get_info($username){
  $result = srch_by_sam($username); 
  if(is_null($result)){
    return NULL;
  }

  if(!(array_key_exists('givenname', $result) && array_key_exists('sn', $result))){ 
    return NULL;
  }
  $first_name = $result['givenname'][0];
  $last_name  = $result['sn'][0];
  
  $first_name = ucwords(strtolower($first_name));
  $last_name  = ucwords(strtolower($last_name));
  
  #Touches the user entry in the sql table
  if(touch_user($username, $first_name, $last_name, $first_name.' '.$last_name)){
    return NULL;
  }
 
  return array(
    'username'   => $username,
    'first_name' => $first_name,
    'last_name'  => $last_name,
  );
}

/**
 * Returns whether or not $username is a queue admin
 *
 * @param string $username samaccountname
 * @return true if queue admin
 *         false if not queue admin
 */
function is_admin($username){
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return NULL;
  }

  $query = "SELECT admin FROM users WHERE username=?";
  $stmt  = mysqli_prepare($sql_conn, $query);
  if(!$stmt){
    mysqli_close($sql_conn);
    return NULL;
  }
  mysqli_stmt_bind_param($stmt, "s", $username);
  if(!mysqli_stmt_execute($stmt)){
    mysqli_stmt_close($stmt);
    mysqli_close($sql_conn);
    return NULL;
  }
  mysqli_stmt_bind_result($stmt, $admin);
  mysqli_stmt_fetch($stmt);

  mysqli_stmt_close($stmt);
  mysqli_close($sql_conn);
  return $admin;
}

function grant_admin($username){
  return admin_access($username, 'true');
}

function revoke_admin($username){
    return admin_access($username, 'false');
}

/**
 * Deletes a user in the database.
 *
 * @param string $username
 * @param string $first
 * @param string $last
 * @param string $full
 * @return int 0 on success
 *         int 1 on fail
 */
function del_user($username){
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return 1;
  }

  $query = "DELETE FROM users WHERE username=?";
  $stmt  = mysqli_prepare($sql_conn, $query);
  if(!$stmt){
    mysqli_close($sql_conn);
    return 1;
  }
  mysqli_stmt_bind_param($stmt, 's', $username);
  if(!mysqli_stmt_execute($stmt)){
    mysqli_stmt_close($stmt);
    mysqli_close($sql_conn);
    return 1;
  }

  mysqli_stmt_close($stmt);
  mysqli_close($sql_conn);
  return 0;
}

######### HELPER METHODS #########
/**
 * Connect to Active Directory server
 *
 * @return ldap_link on success
 *         null on error
 */
function _ldap_connect($username, $password){
  if(empty($username) || empty($password)){
    return null;
  }
  $ldap_conn = ldap_connect(LDAP_SERVER);
  ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
  ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);
  
  //TLS cert disabling code requires php 7.0.5+
  if(version_compare(phpversion(), '7.0.5') > 0){
    ldap_set_option($ldap_conn, LDAP_OPT_X_TLS_REQUIRE_CERT, 0);
    ldap_start_tls($ldap_conn);
  }elseif(TLS_REQCERT_DISABLED){
    ldap_start_tls($ldap_conn);
  }

  if($ldap_conn){
    if(ldap_bind($ldap_conn, $username.'@'.LDAP_DOMAIN, $password)){
      return $ldap_conn;
    } 
  }
  return NULL;
}

/**
 * Returns all LDAP attributes for samaccountname
 *
 * @param string $sam
 * @return array of user attributes
 *         null on error
 */
function srch_by_sam($sam){
  if(empty($sam)){
    return NULL;
  }

  $ldap_conn = _ldap_connect(BIND_USER, BIND_PASSWD);
  if(is_null($ldap_conn)){
    return NULL;
  }

  $filter = "(sAMAccountName=$sam)";
  $results = ldap_search($ldap_conn, BASE_OU, $filter);
  $entries = ldap_get_entries($ldap_conn, $results);

  ldap_unbind($ldap_conn);
  if(!$entries['count']){//No results found for that group
    return NULL;
  }
  
  return $entries[0];
}

/**
 * Touches the SQL entry for $username.
 * Updates the login timestamp
 *
 * @param string $username
 * @param string $first
 * @param string $last
 * @param string $full
 * @return int 0 on success
 *         int 1 on fail
 */
function touch_user($username, $first, $last, $full){
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return 1;
  }

  $query = "INSERT INTO users (username, first_name, last_name, full_name, last_login) 
            VALUES (?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE last_login=NOW()";
  $stmt  = mysqli_prepare($sql_conn, $query);
  if(!$stmt){
    mysqli_close($sql_conn);
    return 1;
  }
  mysqli_stmt_bind_param($stmt, 'ssss', $username, $first, $last, $full);
  if(!mysqli_stmt_execute($stmt)){
    mysqli_stmt_close($stmt);
    mysqli_close($sql_conn);
    return 1;
  }

  mysqli_stmt_close($stmt);
  mysqli_close($sql_conn);
  return 0;
}

/**
 * Adds or removes a user from the admin group.
 *
 * @param string $username
 * @param bool $admin
 * @return int 0 on success
 *         int 1 on fail
 */
function admin_access($username, $admin){
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return 1;
  }

  if(is_null(get_info($username))){
    return 1;
  }

  $query = "UPDATE users SET admin=? WHERE username=?";
  $stmt  = mysqli_prepare($sql_conn, $query);
  if(!$stmt){
    mysqli_close($sql_conn);
    return 1;
  }
  mysqli_stmt_bind_param($stmt, 'ss', $admin, $username);
  if(!mysqli_stmt_execute($stmt)){
    mysqli_stmt_close($stmt);
    mysqli_close($sql_conn);
    return 1;
  }

  mysqli_stmt_close($stmt);
  mysqli_close($sql_conn);
  return 0;
}

?>
