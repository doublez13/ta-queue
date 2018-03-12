<?php
require_once 'config.php';
require_once 'auth.php';
/**
 * SPDX-License-Identifier: GPL-3.0-or-later
 * Functions for courses
 * 
 */

/**
 * Returns array of all registered courses
 *
 * @return array of course names
 * @return null on fail
 */
function get_avail_courses(){
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return NULL;
  }

  $query  = "SELECT course_name, access_code FROM courses";
  $result = mysqli_query($sql_conn, $query);
  if(!$result){
    return NULL;
  }

  $courses = array();
  while($entry = mysqli_fetch_assoc($result)){

    $acc_req = false;
    if(!is_null($entry["access_code"])){
      $acc_req = true;
    }

    $courses += [ $entry["course_name"] => array("acc_req" => $acc_req)  ];
  }

  mysqli_close($sql_conn);
  return $courses;
}

 /**
  * Adds a new course to the database
  *
  * @param string $course_name
  * @param string $depart_prefix
  * @param string $course_num
  * @param string $description
  * @param string $ldap_group
  * @param string $professor
  * @param string $acc_code, null if none
  * @return int 0 on success, 1 on fail
  */
function new_course($course_name, $depart_prefix, $course_num, $description, $ldap_group, $professor, $acc_code){
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return 1;
  }
 
  $query = "INSERT INTO courses (depart_pref, course_num, course_name, description, ldap_group, professor, access_code)
            VALUES (?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE description=?, ldap_group=?, professor=?, access_code=?";
  $stmt  = mysqli_prepare($sql_conn, $query);
  if(!$stmt){
    mysqli_close($sql_conn);
    return 1;
  }
  mysqli_stmt_bind_param($stmt, "sssssssssss", $depart_prefix, $course_num, $course_name, $description, $ldap_group, $professor, $acc_code, $description, $ldap_group, $professor, $acc_code);
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
 * Removes the course from the database
 *
 * @param string $course_name
 * @return int 0 on success, 1 on fail
 */
function del_course($course_name){
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return 1;
  }

  $query = "DELETE FROM courses WHERE course_name=?";
  $stmt  = mysqli_prepare($sql_conn, $query);
  if(!$stmt){
    mysqli_close($sql_conn);
    return 1;
  }
  mysqli_stmt_bind_param($stmt, "s",$course_name);
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
  * Returns a list of TAs for the course.
  *
  * @param string $course_name
  * @return array of TA usernames
  * @return null on fail
  */
function get_tas($course_name){
  $course_group = get_course_group($course_name);
  if(is_null($course_group)){
    return NULL;
  }

  $result = srch_by_sam($course_group);
  if(is_null($result)){
    return NULL;
  }

  $members = $result["member"];
  foreach($members as &$member) {
    $member = dn_to_sam($member);
  }

  return $members;
}

 /**
  * Get courses that the user is a TA for
  *
  * @param string $username
  * @return array of courses the user is a TA for 
  * @return null on error
  */
function get_ta_courses($username){
  $result = srch_by_sam($username);
  if(is_null($result)){
    return NULL;
  } 

  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return NULL;
  }
 
  $groups = $result["memberof"];
  unset($groups["count"]);

  $courses = array();
  foreach($groups as $group) { //Iterate groups the user is a member of
    $group_sam = dn_to_sam($group);
    if(is_null($group_sam)){
      continue; //In theory, this is not possible, but we'll check
    }

    #group_sam is returned from LDAP, so we won't worry about SQL injection here
    $query  = "SELECT course_name FROM courses WHERE ldap_group ='".$group_sam."'";
    $result = mysqli_query($sql_conn, $query);
    if(!mysqli_num_rows($result)){
      continue; //No class in the database with this ldap group
    }
    
    //possible multiple courses use the same ldap_group
    while($entry = mysqli_fetch_assoc($result)){
      $courses[] = $entry["course_name"]; 
    }
  }
  
  mysqli_close($sql_conn);
  return $courses;
}

 /**
  * Get courses that the user has joined as a student
  *
  * @param string $username
  * @return array of courses the user is a student in
  * @return null on error
  */
function get_stud_courses($username){
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return NULL;
  }

  $query = "SELECT course_name FROM courses NATURAL JOIN enrolled WHERE username=?";
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
  mysqli_stmt_bind_result($stmt, $course_name);
  
  $courses = array();
  while(mysqli_stmt_fetch($stmt)){
    $courses[] = $course_name;
  }

  mysqli_stmt_close($stmt);
  mysqli_close($sql_conn);
  return $courses;
}

 /**
  * Add user to course as a student
  *
  * @param string $username
  * @param string $course_name
  * @return int 0 on success, 
  *             -1 on fail, 
  *             -5 if user already has TA role, 
  *             -6 on invalid access code
  */
function add_stud_course($username, $course_name, $acc_code){ 
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return -1;
  }

  //Don't allow user to enroll in course if they're a TA
  if (in_array($username, get_tas($course_name))){
    mysqli_close($sql_conn);
    return -5;
  }

  $real_acc_code = get_course_acc_code($course_name); 
  if($real_acc_code == -1 ){
    mysqli_close($sql_conn);
    return -1;//error
  } elseif(!is_null($real_acc_code) &&  $acc_code != $real_acc_code){
    mysqli_close($sql_conn);
    return -6;//invalid access code
  }
  //Proper access code provided, or one isn't required

  $query = "REPLACE enrolled (username, course_id) VALUES ( ?, (SELECT course_id FROM courses WHERE course_name=?) )";
  $stmt  = mysqli_prepare($sql_conn, $query);
  if(!$stmt){
    mysqli_close($sql_conn);
    return -1;
  }
  mysqli_stmt_bind_param($stmt, "ss", $username, $course_name);
  if(!mysqli_stmt_execute($stmt)){
    mysqli_stmt_close($stmt);
    mysqli_close($sql_conn);
    return -1;
  }

  mysqli_stmt_close($stmt);
  mysqli_close($sql_conn);
  return 0;
}

 /**
  * Remove user (student) from course 
  *
  * @param string $username
  * @param string $course_name
  * @return int 0 on success, 
  *             -1 on fail
  */
function rem_stud_course($username, $course_name){
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return 1;
  }

  $query = "DELETE enrolled FROM enrolled NATURAL JOIN courses WHERE username=? AND course_name=?";
  $stmt  = mysqli_prepare($sql_conn, $query);
  if(!$stmt){
    mysqli_close($sql_conn);
    return 1;
  }
  mysqli_stmt_bind_param($stmt, "ss", $username, $course_name);
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
 * Returns the LDAP group for the course
 *
 * @param int $course_name
 * @return string ldap group
 * @return null on error
 */
function get_course_group($course_name){
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return NULL;
  }

  $query = "SELECT ldap_group FROM courses WHERE course_name=?";
  $stmt  = mysqli_prepare($sql_conn, $query);
  if(!$stmt){
    mysqli_close($sql_conn);
    return NULL;
  }
  mysqli_stmt_bind_param($stmt, "s", $course_name);
  if(!mysqli_stmt_execute($stmt)){
    mysqli_stmt_close($stmt);
    mysqli_close($sql_conn);
    return NULL;
  }
  mysqli_stmt_bind_result($stmt, $ldap_group);
  mysqli_stmt_fetch($stmt);

  mysqli_stmt_close($stmt);
  mysqli_close($sql_conn);
  return $ldap_group;
}

function get_course_acc_code($course_name){
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return -1;
  }

  $query = "SELECT access_code FROM courses WHERE course_name=?";
  $stmt  = mysqli_prepare($sql_conn, $query);
  if(!$stmt){
    mysqli_close($sql_conn);
    return -1;
  }
  mysqli_stmt_bind_param($stmt, "s", $course_name);
  if(!mysqli_stmt_execute($stmt)){
    mysqli_stmt_close($stmt);
    mysqli_close($sql_conn);
    return -1;
  }
  mysqli_stmt_bind_result($stmt, $access_code);
  mysqli_stmt_fetch($stmt);

  mysqli_stmt_close($stmt);
  mysqli_close($sql_conn);
  return $access_code;
}

?>
