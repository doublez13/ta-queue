<?php
require_once 'config.php';
require_once 'auth.php';
/**
 * SPDX-License-Identifier: GPL-3.0-or-later
 * Copyright (c) 2018 Zane Zakraisek
 *
 * Functions for courses.
 */

/**
 * Returns array of all registered courses
 *
 * @return array of course names
 *         null on fail
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
  * @param string $depart_pref
  * @param string $course_num
  * @param string $description
  * @param string $professor
  * @param string $acc_code, null if none
  * @return int 0 on success
  *             1 on fail
  */
function new_course($course_name, $depart_pref, $course_num, $description, $professor, $acc_code){
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return 1;
  }

  //If the prof has never logged in, they're not in the users table
  //and therefore fail the Foreign Key Constraint.
  //Calling get_info(user) automatically adds a valid user to the users table.
  get_info($professor);
 
  $query = "INSERT INTO courses (depart_pref, course_num, course_name, description, professor, access_code)
            VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE description=?, professor=?, access_code=?";
  $stmt  = mysqli_prepare($sql_conn, $query);
  if(!$stmt){
    mysqli_close($sql_conn);
    return 1;
  }
  mysqli_stmt_bind_param($stmt, "sssssssss", $depart_pref, $course_num, $course_name, $description, $professor, $acc_code, $description, $professor, $acc_code);
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
 * @return int 0 on success
 *             1 on fail
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
 * Returns all settings for a course
 *
 * @param string $course_name
 * @return array course settings on success
 *         null on error
 */
function get_course($course_name){
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return null;
  }
  $query = "SELECT depart_pref, course_num, course_name, professor, description, access_code FROM courses WHERE course_name=?";
  $stmt  = mysqli_prepare($sql_conn, $query);
  if(!$stmt){
    mysqli_close($sql_conn);
    return null;
  }
  mysqli_stmt_bind_param($stmt, "s",$course_name);
  if(!mysqli_stmt_execute($stmt)){
    mysqli_stmt_close($stmt);
    mysqli_close($sql_conn);
    return null;
  }
  mysqli_stmt_bind_result($stmt, $depart_pref, $course_num, $course_name, $professor, $description, $access_code);
  if(mysqli_stmt_fetch($stmt)){
    mysqli_stmt_close($stmt);
    mysqli_close($sql_conn);
    return array("depart_pref" => $depart_pref, 
                 "course_num"  => $course_num, 
                 "course_name" => $course_name, 
                 "professor"   => $professor, 
                 "description" => $description, 
                 "access_code" => $access_code
           );
  }
  return null;
}

function get_tas($course_name){
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return NULL;
  }

  $query = "SELECT username FROM courses NATURAL JOIN enrolled WHERE course_name=? AND role='ta'";
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
  mysqli_stmt_bind_result($stmt, $username);

  $tas = array();
  while(mysqli_stmt_fetch($stmt)){
    $tas[] = $username;
  }

  mysqli_stmt_close($stmt);
  mysqli_close($sql_conn);
  return $tas;
}

 /**
  * Get courses that the user is a TA for
  *
  * @param string $username
  * @return array of courses the user is a TA for 
  *         null on error
  */
function get_ta_courses($username){
   return get_user_courses($username, "ta");
}

 /**
  * Get courses that the user has joined as a student
  *
  * @param string $username
  * @return array of courses the user is a student in
  * @return null on error
  */
function get_stud_courses($username){
  return get_user_courses($username, "student");
}

function add_ta_course($username, $course_name){
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return -1;
  }

  get_info($username);

  //If they are already enrolled as a student, this automatically unenrolls them, and enrolls them as a TA
  $query = "REPLACE enrolled (username, course_id, role) VALUES ( ?, (SELECT course_id FROM courses WHERE course_name=?), 'ta')";
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

function rem_ta_course($username, $course_name){
  return rem_user_course($username, $course_name, "ta");
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

  get_info($username);

  //TODO: Should we error if they're a TA? Currently we just switch their role.

  $real_acc_code = get_course_acc_code($course_name); 
  if($real_acc_code == -1 ){//TODO: Nothing stopping -1 from being an access code
    mysqli_close($sql_conn);
    return -1;//error
  } elseif(!is_null($real_acc_code) &&  $acc_code != $real_acc_code){
    mysqli_close($sql_conn);
    return -6;//invalid access code
  }
  //Proper access code provided, or one isn't required

  $query = "REPLACE enrolled (username, course_id, role) VALUES ( ?, (SELECT course_id FROM courses WHERE course_name=?), 'student')";
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
  * Remove student from course 
  *
  * @param string $username
  * @param string $course_name
  * @return int  0 on success, 
  *             -1 on fail
  */
function rem_stud_course($username, $course_name){
  return rem_user_course($username, $course_name, "student");
}


######### HELPER METHODS #########
/**
 * Returns the access code for the course
 *
 * @param string $course_name
 * @return int access_code
 *             -1 on error
 */
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

 /**
  * Get courses where the user has role
  *
  * @param string $username
  * @return array of courses the user is a member of with that role
  *         null on error
  */
function get_user_courses($username, $role){
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return NULL;
  }

  $query = "SELECT course_name FROM courses NATURAL JOIN enrolled WHERE username=? AND role=?";
  $stmt  = mysqli_prepare($sql_conn, $query);
  if(!$stmt){
    mysqli_close($sql_conn);
    return NULL;
  }
  mysqli_stmt_bind_param($stmt, "ss", $username, $role);
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

function rem_user_course($username, $course_name, $role){
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return -1;
  }

  $query = "DELETE enrolled FROM enrolled NATURAL JOIN courses WHERE username=? AND course_name=? AND role=?";
  $stmt  = mysqli_prepare($sql_conn, $query);
  if(!$stmt){
    mysqli_close($sql_conn);
    return -1;
  }
  mysqli_stmt_bind_param($stmt, "sss", $username, $course_name, $role);
  if(!mysqli_stmt_execute($stmt)){
    mysqli_stmt_close($stmt);
    mysqli_close($sql_conn);
    return -1;
  }

  mysqli_stmt_close($stmt);
  mysqli_close($sql_conn);
  return 0;
}
?>
