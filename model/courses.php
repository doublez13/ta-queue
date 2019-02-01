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
 * Returns array of all registered courses that are enabled
 *
 * @return array of course names
 *         null on fail
 */
function get_enabled_courses(){
  return get_courses(true);
}

/**
 * Returns array of all registered courses
 *
 * @return array of course names
 *         null on fail
 */
function get_all_courses(){
  return get_courses(false);
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
 * @return int 0  on success
 *             -1 generic error
 *             -8 user does not exist
 */
function new_course($course_name, $depart_pref, $course_num, $description, $professor, $acc_code, $enabled){
  //If the prof has never logged in, they're not in the users table
  //and therefore fail the Foreign Key Constraint.
  //Calling get_info(user) automatically adds a valid user to the users table.
  if(is_null(get_info($professor))){
    return -8;
  }

  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return -1;
  }

  $query = "INSERT INTO courses (depart_pref, course_num, course_name, description, professor, access_code, enabled)
            VALUES (?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE description=?, professor=?, access_code=?, enabled=?";
  $stmt  = mysqli_prepare($sql_conn, $query);
  if(!$stmt){
    mysqli_close($sql_conn);
    return -1;
  }
  mysqli_stmt_bind_param($stmt, "ssssssisssi", $depart_pref, $course_num, $course_name, $description, $professor, $acc_code, $enabled, $description, $professor, $acc_code, $enabled);
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
 * Removes the course from the database
 *
 * @param int $course_id
 * @return int 0 on success
 *             -1 on fail
 */
function del_course($course_id){
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return -1;
  }

  $query = "DELETE FROM courses WHERE course_id=?";
  $stmt  = mysqli_prepare($sql_conn, $query);
  if(!$stmt){
    mysqli_close($sql_conn);
    return -1;
  }
  mysqli_stmt_bind_param($stmt, "i",$course_id);
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
 * Returns all settings for a course
 *
 * @param string $course_id
 * @return array course settings on success
 *         null on error
 */
function get_course($course_id){
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return null;
  }
  $query = "SELECT depart_pref, course_num, course_name, professor, description, access_code, enabled FROM courses WHERE course_id=?";
  $stmt  = mysqli_prepare($sql_conn, $query);
  if(!$stmt){
    mysqli_close($sql_conn);
    return null;
  }
  mysqli_stmt_bind_param($stmt, "i",$course_id);
  if(!mysqli_stmt_execute($stmt)){
    mysqli_stmt_close($stmt);
    mysqli_close($sql_conn);
    return null;
  }
  mysqli_stmt_bind_result($stmt, $depart_pref, $course_num, $course_name, $professor, $description, $access_code, $enabled);
  if(mysqli_stmt_fetch($stmt)){
    mysqli_stmt_close($stmt);
    mysqli_close($sql_conn);
    return array("depart_pref" => $depart_pref,
                 "course_num"  => $course_num,
                 "course_name" => $course_name,
                 "course_id"   => $course_id,
                 "professor"   => $professor,
                 "description" => $description,
                 "access_code" => $access_code,
                 "enabled"     => $enabled
           );
  }
  return null;
}

/**
 * Returns an array of TAs for course_id
 *
 * @param string $course_id
 * @return array of TAs for course_id
 *         null on error
 */
function get_tas($course_id){
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return NULL;
  }

  $query = "SELECT username FROM courses NATURAL JOIN enrolled WHERE course_id=? AND role='ta'";
  $stmt  = mysqli_prepare($sql_conn, $query);
  if(!$stmt){
    mysqli_close($sql_conn);
    return NULL;
  }
  mysqli_stmt_bind_param($stmt, "i", $course_id);
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
   return get_user_courses($username)['ta'];
}

/**
 * Get courses that the user has joined as a student
 *
 * @param string $username
 * @return array of courses the user is a student in
 * @return null on error
 */
function get_stud_courses($username){
  return get_user_courses($username)['student'];
}


/**
 * Get courses that the user has joined as a student
 * TODO: ADD CHECK FOR COURSE
 * @param string $username
 * @return int 0 on success
 *             -1 on fail
 *             -2 on nonexistant course
 *             -8 on nonexistant user
 */
function add_ta_course($username, $course_id){
  //If the user has never logged in, they're not in the users table
  //and therefore fail the Foreign Key Constraint.
  //Calling get_info(user) automatically adds a valid user to the users table.
  if(is_null(get_info($username))){
    return -8;
  }

  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return -1;
  }

  //If they are already enrolled as a student, this automatically unenrolls them, and enrolls them as a TA
  $query = "REPLACE enrolled (username, course_id, role) VALUES ( ?, ?, 'ta')";
  $stmt  = mysqli_prepare($sql_conn, $query);
  if(!$stmt){
    mysqli_close($sql_conn);
    return -1;
  }
  mysqli_stmt_bind_param($stmt, "si", $username, $course_id);
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
 * Unenrolls a user from the course as a TA
 *
 * @param string $course_id
 * @param string $username
 * @return int 0 on success
 *             -1 on fail
 */
function rem_ta_course($username, $course_id){
  return rem_user_course($username, $course_id, "ta");
}

/**
 * Add user to course as a student
 * TODO: ADD CHECKS FOR COURSE AND USER
 * @param string $username
 * @param string $course_id
 * @return int 0 on success,
 *             -1 on fail,
 *             -2 on nonexistant course
 *             -5 if user already has TA role,
 *             -6 on invalid access code
 *             -8 on nonexistant user
 */
function add_stud_course($username, $course_id, $acc_code){
  //If the prof has never logged in, they're not in the users table
  //and therefore fail the Foreign Key Constraint.
  //Calling get_info(user) automatically adds a valid user to the users table.
  if(is_null(get_info($username))){
    return -8;
  }

  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return -1;
  }

  //TODO: Should we error if they're a TA? Currently we just switch their role.

  $real_acc_code = get_course_acc_code($course_id, $sql_conn);
  if($real_acc_code == -1 ){//TODO: Nothing stopping -1 from being an access code
    mysqli_close($sql_conn);
    return -1;//error
  } elseif(!is_null($real_acc_code) &&  $acc_code != $real_acc_code){
    mysqli_close($sql_conn);
    return -6;//invalid access code
  }
  //Proper access code provided, or one isn't required

  $query = "REPLACE enrolled (username, course_id, role) VALUES ( ?, ?, 'student')";
  $stmt  = mysqli_prepare($sql_conn, $query);
  if(!$stmt){
    mysqli_close($sql_conn);
    return -1;
  }
  mysqli_stmt_bind_param($stmt, "si", $username, $course_id);
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
 * @param string $course_id
 * @return int  0 on success,
 *             -1 on fail
 */
function rem_stud_course($username, $course_id){
  return rem_user_course($username, $course_id, "student");
}


######### HELPER METHODS #########
/**
 * Returns the access code for the course
 *
 * @param  int    $course_id
 * @return string access_code
 *         int   -1 on error
 */
function get_course_acc_code($course_id, $sql_conn){
  $query = "SELECT access_code FROM courses WHERE course_id=?";
  $stmt  = mysqli_prepare($sql_conn, $query);
  if(!$stmt){
    mysqli_close($sql_conn);
    return -1;
  }
  mysqli_stmt_bind_param($stmt, "i", $course_id);
  if(!mysqli_stmt_execute($stmt)){
    mysqli_stmt_close($stmt);
    mysqli_close($sql_conn);
    return -1;
  }
  mysqli_stmt_bind_result($stmt, $access_code);
  mysqli_stmt_fetch($stmt);

  mysqli_stmt_close($stmt);
  return $access_code;
}

/**
 * Get courses where the user has role
 *
 * @param string $username
 * @return array of courses the user is a member of with that role
 *         null on error
 */
function get_user_courses($username){
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return NULL;
  }

  #TODO: Consider switching this to a subquery instead of a join
  $query = "SELECT course_name, course_id, role FROM courses NATURAL JOIN enrolled WHERE username=? AND enabled=true";
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
  mysqli_stmt_bind_result($stmt, $course_name, $course_id, $role);

  #TODO: Return course_name as well
  $courses            = array();
  $courses['student'] = array();
  $courses['ta']      = array();
  while(mysqli_stmt_fetch($stmt)){
    $courses[$role][$course_name] = array("course_id" => $course_id);
  }

  mysqli_stmt_close($stmt);
  mysqli_close($sql_conn);
  return $courses;
}

/**
 * Get courses where the user has role
 *
 * @param string $username
 * @return array of courses the user is a member of with that role
 *         null on error
 */
function get_user_courses2($username){
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return NULL;
  }

  #TODO: Consider switching this to a subquery instead of a join
  $query = "SELECT course_id, role FROM courses NATURAL JOIN enrolled WHERE username=? AND enabled=true";
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
  mysqli_stmt_bind_result($stmt, $course_id, $role);

  #TODO: Return course_name as well
  $courses            = array();
  $courses['student'] = array();
  $courses['ta']      = array();
  while(mysqli_stmt_fetch($stmt)){
    $courses[$role][] = $course_id;
  }

  mysqli_stmt_close($stmt);
  mysqli_close($sql_conn);
  return $courses;
}

/**
 * Remove user from course as $role
 *
 * @param string $username
 * @param int    $course_id
 * @param string $role [ta, student]
 * @return int  0 on success,
 *             -1 on fail
 */
function rem_user_course($username, $course_id, $role){
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return -1;
  }

  $query = "DELETE enrolled FROM enrolled NATURAL JOIN courses WHERE username=? AND course_id=? AND role=?";
  $stmt  = mysqli_prepare($sql_conn, $query);
  if(!$stmt){
    mysqli_close($sql_conn);
    return -1;
  }
  mysqli_stmt_bind_param($stmt, "sis", $username, $course_id, $role);
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
 * Returns true if the course is open, false if closed
 * NULL on error
 *
 * @param int    $course_id
 * @return bool
 */
function get_course_state($course_id){
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return NULL;
  }

  $query = "SELECT enabled from courses where course_id=?";
  $stmt  = mysqli_prepare($sql_conn, $query);
  if(!$stmt){
    mysqli_close($sql_conn);
    return NULL;
  }
  mysqli_stmt_bind_param($stmt, "i", $course_id);
  if(!mysqli_stmt_execute($stmt)){
    mysqli_stmt_close($stmt);
    mysqli_close($sql_conn);
    return NULL;
  }
  mysqli_stmt_bind_result($stmt, $enabled);
  $enabled = -1;
  mysqli_stmt_fetch($stmt);

  mysqli_stmt_close($stmt);
  mysqli_close($sql_conn);

  return $enabled;
}

/**
 * Returns array of all registered courses
 *
 * @param boolean $enabled_only
 * @return array of course names
 *         null on fail
 */
function get_courses($enabled_only){
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return NULL;
  }

  $query = "SELECT course_id, course_name, access_code, description, enabled FROM courses ORDER BY depart_pref, course_name";
  if($enabled_only){
    $query = "SELECT course_id, course_name, access_code, description, enabled FROM courses WHERE enabled=true ORDER BY depart_pref, course_name";
  }
  $result = mysqli_query($sql_conn, $query);
  if(!$result){
    return NULL;
  }

  $courses = array();
  while($entry = mysqli_fetch_assoc($result)){
    $acc_req = (is_null($entry["access_code"]) ? 0 : 1);
    $courses += [ $entry["course_name"] => array("acc_req"     => $acc_req,
                                                 "course_id"   => $entry["course_id"],
                                                 "description" => $entry["description"],
                                                 "enabled"     => $entry["enabled"]
                                                ) ];
  }

  mysqli_close($sql_conn);
  return $courses;
}
?>
