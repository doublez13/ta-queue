<?php
require_once 'config.php';
require_once 'queue.php';
/**
 * SPDX-License-Identifier: GPL-3.0-or-later
 * Copyright (c) 2018 Zane Zakraisek
 *
 * Functions for pulling statistics out of the database.
 */

/**
 * Returns a log of the queue sessions for stud_username
 *
 * @param string $stud_username
 * @return array of student log entries
 *         int -1 on error
 */
function get_stud_log($stud_username){
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return -1;
  }

  $query = "SELECT (SELECT course_name FROM courses WHERE course_id=student_log.course_id) AS course_name, question, location, enter_tmstmp, help_tmstmp, exit_tmstmp, helped_by
            FROM student_log 
            WHERE username=? AND exit_tmstmp !='0' AND help_tmstmp !='0'";
  $stmt  = mysqli_prepare($sql_conn, $query);
  if(!$stmt){
    mysqli_close($sql_conn);
    return -1;
  }
  mysqli_stmt_bind_param($stmt, "s", $stud_username);
  if(!mysqli_stmt_execute($stmt)){
    mysqli_stmt_close($stmt);
    mysqli_close($sql_conn);
    return -1;
  }

  mysqli_stmt_bind_result($stmt, $course_name, $question, $location, $enter_tmstmp, $help_tmstmp, $exit_tmstmp, $helped_by); 
  $result = [];
  while (mysqli_stmt_fetch($stmt)){
    $result[] = array("course_name"  => $course_name, 
                      "question"     => $question, 
                      "location"     => $location, 
                      "enter_tmstmp" => $enter_tmstmp, 
                      "help_tmstmp"  => $help_tmstmp, 
                      "exit_tmstmp"  => $exit_tmstmp, 
                      "helped_by"    => $helped_by,
                     ); 
  }
  
  mysqli_stmt_close($stmt);
  mysqli_close($sql_conn);
  return $result;
}

/**
 * Returns a log of the queue sessions for course_name
 *
 * @param string $course_name
 * @return array of course log entries
 *         int -1 on error
 */
function get_course_log($course_name){
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return -1;
  }

  $query = "SELECT username, question, location, enter_tmstmp, help_tmstmp, exit_tmstmp, helped_by
            FROM student_log 
            WHERE course_id=(SELECT course_id from courses where course_name=?) AND exit_tmstmp !='0' AND help_tmstmp !='0'";
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

  mysqli_stmt_bind_result($stmt, $username, $question, $location, $enter_tmstmp, $help_tmstmp, $exit_tmstmp, $helped_by);
  $result = [];
  while (mysqli_stmt_fetch($stmt)){
    $result[] = array("username"     => $username,
                      "question"     => $question,
                      "location"     => $location,
                      "enter_tmstmp" => $enter_tmstmp,
                      "help_tmstmp"  => $help_tmstmp,
                      "exit_tmstmp"  => $exit_tmstmp,
                      "helped_by"    => $helped_by,
                     );
  }

  mysqli_stmt_close($stmt);
  mysqli_close($sql_conn);
  return $result;
}

/**
 * Returns a log of the queue sessions for stud_username in course_name 
 * 
 * @param string $stud_username
 * @param string $course_name
 * @return array of student log entries for course
 *         int -1 on error
 */
function get_stud_log_for_course($stud_username, $course_name){
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return -1;
  }

  $query = "SELECT question, location, enter_tmstmp, help_tmstmp, exit_tmstmp, helped_by
            FROM student_log 
            WHERE username=? AND course_id=(SELECT course_id from courses where course_name=?) AND exit_tmstmp !='0' AND help_tmstmp !='0'";
  $stmt  = mysqli_prepare($sql_conn, $query);
  if(!$stmt){
    mysqli_close($sql_conn);
    return -1;
  }
  mysqli_stmt_bind_param($stmt, "ss", $stud_username, $course_name);
  if(!mysqli_stmt_execute($stmt)){
    mysqli_stmt_close($stmt);
    mysqli_close($sql_conn);
    return -1;
  }
  
  mysqli_stmt_bind_result($stmt, $question, $location, $enter_tmstmp, $help_tmstmp, $exit_tmstmp, $helped_by);
  $result = [];
  while (mysqli_stmt_fetch($stmt)){
    $result[] = array("question"     => $question,
                      "location"     => $location,
                      "enter_tmstmp" => $enter_tmstmp,
                      "help_tmstmp"  => $help_tmstmp,
                      "exit_tmstmp"  => $exit_tmstmp,
                      "helped_by"    => $helped_by,
                     );
  }

  mysqli_stmt_close($stmt);
  mysqli_close($sql_conn);
  return $result;
}

/**
 * Returns a log of the queue sessions for ta_username in course_name
 *
 * @param string $ta_username
 * @param string $course_name
 * @return array of ta log entries for course
 *         int -1 on error
 */
function get_ta_log_for_course($ta_username, $course_name){
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return -1;
  }

  $query = "SELECT question, location, enter_tmstmp, help_tmstmp, exit_tmstmp, helped_by
            FROM student_log 
            WHERE helped_by=? AND course_id=(SELECT course_id from courses where course_name=?) AND exit_tmstmp !='0' AND help_tmstmp !='0'";
  $stmt  = mysqli_prepare($sql_conn, $query);
  if(!$stmt){
    mysqli_close($sql_conn);
    return -1;
  }
  mysqli_stmt_bind_param($stmt, "ss", $ta_username, $course_name);
  if(!mysqli_stmt_execute($stmt)){
    mysqli_stmt_close($stmt);
    mysqli_close($sql_conn);
    return -1;
  }

  mysqli_stmt_bind_result($stmt, $question, $location, $enter_tmstmp, $help_tmstmp, $exit_tmstmp, $helped_by);
  $result = [];
  while (mysqli_stmt_fetch($stmt)){
    $result[] = array("question"     => $question,
                      "location"     => $location,
                      "enter_tmstmp" => $enter_tmstmp,
                      "help_tmstmp"  => $help_tmstmp,
                      "exit_tmstmp"  => $exit_tmstmp,
                      "helped_by"    => $helped_by,
                     );
  }

  mysqli_stmt_close($stmt);
  mysqli_close($sql_conn);
  return $result;
}

/**
 * Returns a list of common statistics for course_name
 *
 * @param string $course_name
 * @return array of course stats
 *         int -1 on error
 */
function get_course_stats($course_name){
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return -1;
  }

  $query = "SELECT
            AVG(TIME_TO_SEC(TIMEDIFF(help_tmstmp, enter_tmstmp)))    AS avg_wait_time,
            AVG(TIME_TO_SEC(TIMEDIFF(exit_tmstmp, help_tmstmp)))     AS avg_help_time, 
            STDDEV(TIME_TO_SEC(TIMEDIFF(exit_tmstmp, help_tmstmp)))  AS stddev_help_time
            FROM student_log 
            WHERE exit_tmstmp !='0' AND help_tmstmp !='0' AND course_id=(SELECT course_id FROM courses where course_name=?)";
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

  mysqli_stmt_bind_result($stmt, $avg_wait_time, $avg_help_time, $stddev_help_time);
  mysqli_stmt_fetch($stmt);
  return array("avg_wait_time"    => $avg_wait_time,
               "avg_help_time"    => $avg_help_time,
               "stddev_help_time" => $stddev_help_time
              );
}

/**
 * Returns a log of the number of users per day in the queue
 *
 * @param string $course_name
 * @return array of course usage stats by day
 *         int -1 on error
 */
function get_course_usage_by_day($course_name){
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return -1;
  }

  $query = "SELECT
            DATE(enter_tmstmp), COUNT(*)
            FROM student_log 
            WHERE exit_tmstmp !='0' AND help_tmstmp !='0' AND course_id=(SELECT course_id FROM courses where course_name=?)
            GROUP BY DATE(enter_tmstmp)";
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

  mysqli_stmt_bind_result($stmt, $date, $count);
  $result = [];
  while (mysqli_stmt_fetch($stmt)){
    $result[] = array("date"     => $date,
                      "count"    => $count
                     );
  }

  mysqli_stmt_close($stmt);
  mysqli_close($sql_conn);
  return $result;
}
?>
