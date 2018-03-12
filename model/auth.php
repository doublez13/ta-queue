<?php
require_once 'config.php';
/**
 * SPDX-License-Identifier: GPL-3.0-or-later
 * Functions for Authentication and Authorization.
 *
 * NOTE: All user and group 'names' are references to 
 *       sAMAccountName, not cn or displayName.
 */

/**
 * Authenticates a user
 *
 * @param string $username
 * @param string $pass
 * @return int 1 on success, 0 on failure
 */
function auth($username, $pass){
  $auth = 0;
  $username = $username."@".LDAP_DOMAIN;
  $ldap_conn = ldap_connect(LDAP_SERVER); 
  if($ldap_conn){
    $ldap_bind = ldap_bind($ldap_conn, $username, $pass);
    if($ldap_bind){
      $auth = 1;
    }
  }
  ldap_unbind($ldap_conn);
  return $auth; 
}

/**
 * Returns an array of information on the user
 *
 * @param string $username samaccountname
 * @return array
 */
function get_info($username){
  $result = srch_by_sam($username); 
  if(is_null($result)){
    return NULL;
  }

  if(!(array_key_exists('givenname', $result) && array_key_exists('sn', $result))){ 
    return NULL;
  }
  $first_name = $result["givenname"][0];
  $last_name  = $result["sn"][0];
  
  $first_name = ucwords(strtolower($first_name));
  $last_name  = ucwords(strtolower($last_name));
  
  #Touches the user entry in the sql table
  if(touch_user($username, $first_name, $last_name, $first_name." ".$last_name)){
    return NULL;
  }
 
  return array(
    "username"   => $username,
    "first_name" => $first_name,
    "last_name"  => $last_name,
  );
}

/**
 * Returns whether or not $username is a queue admin
 *
 * @param string $username samaccountname
 * @return true if queue admin, false if not
 */
function is_admin($username){
  $result = srch_by_sam(ADMIN_GROUP);
  if(is_null($result)){
    return NULL;
  }

  $members = $result["member"];
  foreach($members as &$member) {
    $member = dn_to_sam($member);
    if($member == $username){
      return true;
    }
  }

  return false;
}



//Helper Functions for LDAP: No reason to call these from outside the model.
/**
 * Connect to Active Directory server
 *
 * @return ldap_link
 */
function _ldap_connect(){
  $ldap_conn = ldap_connect(LDAP_SERVER);
  if($ldap_conn){
    $ldap_bind = ldap_bind($ldap_conn, BIND_USER."@".LDAP_DOMAIN, BIND_PASSWD);
    if($ldap_bind){
      return $ldap_conn;
    } 
  }
  return NULL;
}

/**
 * Disconnect from Active Directory server 
 *
 * @param [type] $ldap_conn
 * @return void
 */
function _ldap_disconnect($ldap_conn){
  ldap_unbind($ldap_conn);
}

/**
 * Converts a distinguishedName to a samaccountname
 *
 * @param string $dn
 * @return string samaccountname
 */
function dn_to_sam($dn){
  $filter = "(distinguishedName=$dn)";
  $ldap_conn = _ldap_connect();

  if(is_null($ldap_conn)){
    return NULL;
  }

  $results = ldap_search($ldap_conn, BASE_OU, $filter);
  $entries = ldap_get_entries($ldap_conn, $results);

  if(!$entries["count"]){
    return NULL;
  }

  _ldap_disconnect($ldap_conn);
  return $entries[0]["samaccountname"][0];
}

/**
 * Returns all LDAP attributes for samaccountname
 *
 * @param string $sam
 * @return array
 */
function srch_by_sam($sam){
  if(empty($sam)){
    return NULL;
  }

  $ldap_conn = _ldap_connect();
  if(is_null($ldap_conn)){
    return NULL;
  }

  $filter = "(sAMAccountName=$sam)";
  $results = ldap_search($ldap_conn, BASE_OU, $filter);
  $entries = ldap_get_entries($ldap_conn, $results);

  _ldap_disconnect($ldap_conn);
  if(!$entries["count"]){
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
 * @return int 0 on success, 1 on fail
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
  mysqli_stmt_bind_param($stmt, "ssss", $username, $first, $last, $full);
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
