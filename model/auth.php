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
 * Check the database first, if a user is not there,
 * then try LDAP and create a database entry if successful
 *
 * @param string $username samaccountname
 * @return array consisting of first name, last name, and username
 *         null on error
 */
function get_info($username){
  $info = get_info_sql($username);
  if(is_null($info)){
    $info = get_info_ldap($username);
    if(is_null($info)){
      return NULL;
    }
    $info['is_admin'] = False;
  }

  #Touches the user entry in the sql table
  if(touch_user($info['username'], $info['first_name'], $info['last_name'], $info['full_name'])){
    return NULL;
  }
  return $info;
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
  return admin_access($username, 1);
}

function revoke_admin($username){
    return admin_access($username, 0);
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
  //TLS cert disabling code requires php >= 7.0.5
  //If running php < 7.0.5, disable requiring the cert at the OS level if needed
  if(version_compare(phpversion(), '7.0.5') > 0){
    ldap_set_option($ldap_conn, LDAP_OPT_X_TLS_REQUIRE_CERT, 0);
  }
  ldap_start_tls($ldap_conn);

  if($ldap_conn){
    if(@ldap_bind($ldap_conn, $username.'@'.LDAP_DOMAIN, $password)){
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
 * @param int $admin
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
  mysqli_stmt_bind_param($stmt, 'is', $admin, $username);
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
 * Returns an array of information on the user from SQL
 *
 * @param string $username samaccountname
 * @return array consisting of first name, last name, and username
 *         null on error
 */
function get_info_sql($username){
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return NULL;
  }

  $query = "SELECT username, first_name, last_name, full_name, admin FROM users WHERE username=?"; 
  $stmt  = mysqli_prepare($sql_conn, $query);
  if(!$stmt){
    mysqli_close($sql_conn);
    return NULL;
  }
  mysqli_stmt_bind_param($stmt, 's', $username);
  if(!mysqli_stmt_execute($stmt)){
    mysqli_stmt_close($stmt);
    mysqli_close($sql_conn);
    return NULL;
  }

  mysqli_stmt_bind_result($stmt, $username, $first_name, $last_name, $full_name, $admin);
  if(!mysqli_stmt_fetch($stmt)){
    mysqli_stmt_close($stmt);
    mysqli_close($sql_conn);
    return NULL;
  }

  mysqli_stmt_close($stmt);
  mysqli_close($sql_conn);

  return array(
    'username'   => $username,
    'first_name' => $first_name,
    'last_name'  => $last_name,
    'full_name'  => $full_name,
    'is_admin'   => $admin
  );
}

/**
 * Returns an array of information on the user from LDAP
 *
 * @param string $username samaccountname
 * @return array consisting of first name, last name, and username
 *         null on error
 */
function get_info_ldap($username){
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

  return array(
    'username'   => $username,
    'first_name' => $first_name,
    'last_name'  => $last_name,
    'full_name'  => $first_name.' '.$last_name
  );
}
?>
