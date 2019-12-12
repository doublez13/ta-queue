<?php
require_once 'config.php';
require_once 'auth.php';
/**
 * SPDX-License-Identifier: GPL-3.0-or-later
 * Copyright (c) 2019 Zane Zakraisek
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
 * @param string $acc_code, null if none
 * @return int 0  on success
 *             -1 generic error
 *             -8 user does not exist
 */
function new_course($course_name, $depart_pref, $course_num, $description, $acc_code, $enabled, $generic){
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return -1;
  }

  $query = "INSERT INTO courses (depart_pref, course_num, course_name, description, access_code, enabled, generic)
            VALUES (?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE description=?, access_code=?, enabled=?";
  $stmt  = mysqli_prepare($sql_conn, $query);
  if(!$stmt){
    mysqli_close($sql_conn);
    return -1;
  }
  #NOTE: We only update description, access_code, and enabled. All other fields are locked after creation
  mysqli_stmt_bind_param($stmt, "sssssiissi", $depart_pref, $course_num, $course_name, $description, $acc_code, $enabled, $generic, $description, $acc_code, $enabled);
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
  $query = "SELECT depart_pref, course_num, course_name, description, access_code, enabled, generic FROM courses WHERE course_id=?";
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
  mysqli_stmt_bind_result($stmt, $depart_pref, $course_num, $course_name, $description, $access_code, $enabled, $generic);
  if(mysqli_stmt_fetch($stmt)){
    mysqli_stmt_close($stmt);
    mysqli_close($sql_conn);
    return array("depart_pref" => $depart_pref,
                 "course_num"  => $course_num,
                 "course_name" => $course_name,
                 "course_id"   => $course_id,
                 "description" => $description,
                 "access_code" => $access_code,
                 "enabled"     => $enabled,
                 "generic"     => $generic
           );
  }
  return null;
}

/**
 * Returns an array of instructors enrolled in course_id
 *
 * @param string $course_id
 * @return array of students enrolled in course_id
 *         null on error
 */
function get_instructors($course_id){
  return get_enrolled($course_id, "instructor");
}

/**
 * Returns an array of TAs for course_id
 *
 * @param string $course_id
 * @return array of TAs for course_id
 *         null on error
 */
function get_tas($course_id){
  return get_enrolled($course_id, "ta");
}

/**
 * Returns an array of students enrolled in course_id
 *
 * @param string $course_id
 * @return array of students enrolled in course_id
 *         null on error
 */
function get_students($course_id){
  return get_enrolled($course_id, "student");
}

/**
  * Get courses that the user has joined as a student
  *
  * @param string $username
  * @return array of courses the user is a student in
  * @return null on error
  */
function get_instuctor_courses($username){
    return get_user_courses($username)['instructor'];
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
 * Get courses that the user has joined as an instructor
 * @param string $username
 * @return int 0 on success
 *             -1 on fail
 *             -2 on nonexistant course
 *             -8 on nonexistant user
 */
function add_instructor_course($username, $course_id){
  return add_user_course($username, $course_id, "instructor"); 
}

/**
 * Unenrolls a user from the course as an instructor
 *
 * @param string $course_id
 * @param string $username
 * @return int 0 on success
 *             -1 on fail
 */
function rem_instructor_course($username, $course_id){
  return rem_user_course($username, $course_id, "instructor");
}

/**
 * Get courses that the user has joined as a TA
 * @param string $username
 * @return int 0 on success
 *             -1 on fail
 *             -2 on nonexistant course
 *             -8 on nonexistant user
 */
function add_ta_course($username, $course_id){
  return add_user_course($username, $course_id, "ta");
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
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return -1;
  }
  $real_acc_code = get_course_acc_code($course_id, $sql_conn);
  if($real_acc_code === -1 ){//Since -1 could be an access code, use the identical operator
    mysqli_close($sql_conn);
    return -1;//error
  } elseif(!is_null($real_acc_code) &&  $acc_code != $real_acc_code){
    mysqli_close($sql_conn);
    return -6;//invalid access code
  }
  mysqli_close($sql_conn);
  //Proper access code provided, or one isn't required

  return add_user_course($username, $course_id, "student");
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
  if(!$sql_conn){
    return -1;
  }

  $query = "SELECT access_code FROM courses WHERE course_id=?";
  $stmt  = mysqli_prepare($sql_conn, $query);
  if(!$stmt){
    return -1;
  }
  mysqli_stmt_bind_param($stmt, "i", $course_id);
  if(!mysqli_stmt_execute($stmt)){
    mysqli_stmt_close($stmt);
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

  $query = "SELECT course_name, course_id, description, role FROM courses NATURAL JOIN enrolled WHERE username=? AND enabled=true";
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
  mysqli_stmt_bind_result($stmt, $course_name, $course_id, $description, $role);

  #TODO: Return course_name as well
  $courses               = array();
  $courses['instructor'] = array();
  $courses['student']    = array();
  $courses['ta']         = array();
  #TODO: Add role validity check
  while(mysqli_stmt_fetch($stmt)){
    $courses[$role][$course_name] = array("course_id"   => $course_id,
                                          "description" => $description);
  }

  mysqli_stmt_close($stmt);
  mysqli_close($sql_conn);
  return $courses;
}

/**
 * Get courses the user is enrolled in
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
  $courses               = array();
  $courses['instructor'] = array();
  $courses['student']    = array();
  $courses['ta']         = array();
  while(mysqli_stmt_fetch($stmt)){
    $courses[$role][] = $course_id;
  }

  mysqli_stmt_close($stmt);
  mysqli_close($sql_conn);
  return $courses;
}

/**
 * Returns an array of users enrolled in course_id with role
 *
 * @param string $course_id
 * @return array of students enrolled in course_id
 *         null on error
 */
function get_enrolled($course_id, $role){
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return NULL;
  }

  $query = "SELECT username, full_name FROM courses NATURAL JOIN enrolled NATURAL JOIN users WHERE course_id=? AND role=?";
  $stmt  = mysqli_prepare($sql_conn, $query);
  if(!$stmt){
    mysqli_close($sql_conn);
    return NULL;
  }
  mysqli_stmt_bind_param($stmt, "is", $course_id, $role);
  if(!mysqli_stmt_execute($stmt)){
    mysqli_stmt_close($stmt);
    mysqli_close($sql_conn);
    return NULL;
  }

  $enrolled = array();
  $result   = mysqli_stmt_get_result($stmt);
  while($user = mysqli_fetch_assoc($result)){
    $enrolled[$user["username"]] = $user;
  }

  mysqli_stmt_close($stmt);
  mysqli_close($sql_conn);
  return $enrolled;
}

/**
 * Add user to course as role
 * Replaces the role if already enrolled
 * @param string $username
 * @param string $course_id
 * @return int 0 on success,
 *             -1 on fail,
 *             -2 on nonexistant course
 *             -8 on nonexistant user
 */
function add_user_course($username, $course_id, $role){
  //If the user has never logged in, they're not in the users table
  //and therefore fail the Foreign Key Constraint.
  //Calling get_info(user) automatically adds a valid user to the users table.
  if(is_null(get_info($username))){
    return -8;
  }

  if(!in_array($role, array("student", "ta", "instructor"))) {
    return -1;
  }

  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return -1;
  }

  $res = check_course_id($course_id, $sql_conn);
  if($res == -1){
    mysqli_close($sql_conn);
    return -1; //SQL error
  }elseif($res == 0){
    mysqli_close($sql_conn);
    return -2; //Nonexistant course
  }

  $query = "REPLACE enrolled (username, course_id, role) VALUES ( ?, ?, ?)";
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
 * Returns true if the course is enabled, false if disabled
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

  $query = "SELECT course_id, course_name, access_code, description, enabled, generic FROM courses ORDER BY depart_pref, course_name";
  if($enabled_only){
    $query = "SELECT course_id, course_name, access_code, description, enabled, generic FROM courses WHERE enabled=true ORDER BY depart_pref, course_name";
  }
  $result = mysqli_query($sql_conn, $query);
  if(!$result){
    mysqli_close($sql_conn);
    return NULL;
  }

  $courses = array();
  while($entry = mysqli_fetch_assoc($result)){
    $acc_req = (is_null($entry["access_code"]) ? 0 : 1);
    $courses += [ $entry["course_name"] => array("acc_req"     => $acc_req,
                                                 "course_id"   => $entry["course_id"],
                                                 "description" => $entry["description"],
                                                 "enabled"     => boolval($entry["enabled"]),
                                                 "generic"     => boolval($entry["generic"])
                                                ) ];
  }

  mysqli_close($sql_conn);
  return $courses;
}
?>
