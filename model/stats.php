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
 * Returns a log of the queue sessions where stud_username was helped. If no dates are
 * specified, a log for the entire history of the student is returned. If start_date is
 * specified, a log from start_date (inclusive) to the present is returned. If start_date and
 * end_date are specified, a log from start_date (inclusive) to end_date (exclusive) is returned.
 *
 * @param string $stud_username
 * @param string $start_date enter queue timestamp (inclusive)
 * @param string $end_date enter queue timestamp (exclusive)
 * @return array of student log entries
 *         int -1 on error
 *         int -2 on nonexistent course
 */
function get_stud_log($stud_username, $start_date, $end_date){
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return -1;
  }

  $query = "SELECT (SELECT course_name FROM courses WHERE course_id=student_log.course_id) AS course_name, question, location, enter_tmstmp, help_tmstmp, exit_tmstmp, helped_by
            FROM student_log
            WHERE username = ? AND exit_tmstmp != '0' AND help_tmstmp != '0'";

  // Append date ranges if necessary
  if(!is_null($start_date))
    if (!is_null($end_date))
      $query = $query . " AND enter_tmstmp >=? AND enter_tmstmp <?";
    else
      $query = $query . " AND enter_tmstmp >=?";

  $stmt  = mysqli_prepare($sql_conn, $query);
  if(!$stmt){
    mysqli_close($sql_conn);
    return -1;
  }

  // BETTER WAY TO DO THIS IN A SINGLE FUNCTION CALL?
  if(!is_null($start_date))
    if(!is_null($end_date))
      mysqli_stmt_bind_param($stmt, 'sss', $stud_username, $start_date, $end_date);
    else
      mysqli_stmt_bind_param($stmt, 'ss', $stud_username, $start_date);
  else
    mysqli_stmt_bind_param($stmt, 's', $stud_username);

  if(!mysqli_stmt_execute($stmt)){
    mysqli_stmt_close($stmt);
    mysqli_close($sql_conn);
    return -1;
  }

  mysqli_stmt_bind_result($stmt, $course_name, $question, $location, $enter_tmstmp, $help_tmstmp, $exit_tmstmp, $helped_by);
  $result = [];
  while (mysqli_stmt_fetch($stmt)){
    $result[] = array('course_name'  => $course_name,
                      'question'     => $question,
                      'location'     => $location,
                      'enter_tmstmp' => $enter_tmstmp,
                      'help_tmstmp'  => $help_tmstmp,
                      'exit_tmstmp'  => $exit_tmstmp,
                      'helped_by'    => $helped_by,
                     );
  }

  mysqli_stmt_close($stmt);
  mysqli_close($sql_conn);
  return $result;
}

/**
 * Returns a log of the queue sessions where students were helped for course_name. If no dates are
 * specified, a log for the entire history of the queue is returned. If start_date is
 * specified, a log from start_date (inclusive) to the present is returned. If start_date and
 * end_date are specified, a log from start_date (inclusive) to end_date (exclusive) is returned.
 *
 * @param string $course_name
 * @param string $start_date enter queue timestamp (inclusive)
 * @param string $end_date enter queue timestamp (exclusive)
 * @return array of course log entries
 *         int -1 on error
 */
function get_course_log($course_name, $start_date, $end_date){
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return -1;
  }

  $query = "SELECT username, question, location, enter_tmstmp, help_tmstmp, exit_tmstmp, helped_by
            FROM student_log
            WHERE course_id=(SELECT course_id from courses where course_name=?) AND exit_tmstmp !='0' AND help_tmstmp !='0'";

  // Append date ranges if necessary
  if(!is_null($start_date))
    if (!is_null($end_date))
      $query = $query . " AND enter_tmstmp >=? AND enter_tmstmp <?";
    else
      $query = $query . " AND enter_tmstmp >=?";

  $stmt  = mysqli_prepare($sql_conn, $query);
  if(!$stmt){
    mysqli_close($sql_conn);
    return -1;
  }

  // BETTER WAY TO DO THIS IN A SINGLE FUNCTION CALL?
  if(!is_null($start_date))
    if(!is_null($end_date))
      mysqli_stmt_bind_param($stmt, 'sss', $course_name, $start_date, $end_date);
    else
      mysqli_stmt_bind_param($stmt, 'ss', $course_name, $start_date);
  else
    mysqli_stmt_bind_param($stmt, 's', $course_name);

  if(!mysqli_stmt_execute($stmt)){
    mysqli_stmt_close($stmt);
    mysqli_close($sql_conn);
    return -1;
  }

  mysqli_stmt_bind_result($stmt, $username, $question, $location, $enter_tmstmp, $help_tmstmp, $exit_tmstmp, $helped_by);
  $result = [];
  while (mysqli_stmt_fetch($stmt)){
    $result[] = array('student'     => $username,
                      'question'     => $question,
                      'location'     => $location,
                      'enter_tmstmp' => $enter_tmstmp,
                      'help_tmstmp'  => $help_tmstmp,
                      'exit_tmstmp'  => $exit_tmstmp,
                      'helped_by'    => $helped_by,
                     );
  }

  mysqli_stmt_close($stmt);
  mysqli_close($sql_conn);
  return $result;
}

/**
 * Returns a log of the queue sessions where stud_username was helped in course_name.  If no dates are
 * specified, a log for the entire history of the student in course_name is returned. If start_date is
 * specified, a log from start_date (inclusive) to the present is returned. If start_date and
 * end_date are specified, a log from start_date (inclusive) to end_date (exclusive) is returned.
 *
 * @param string $stud_username
 * @param string $course_name
 * @param string $start_date enter queue timestamp (inclusive)
 * @param string $end_date enter queue timestamp (exclusive)
 * @return array of student log entries for course
 *         int -1 on error
 */
function get_stud_log_for_course($stud_username, $course_name, $start_date, $end_date){
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return -1;
  }

  $query = "SELECT question, location, enter_tmstmp, help_tmstmp, exit_tmstmp, helped_by
            FROM student_log
            WHERE username=? AND course_id=(SELECT course_id from courses where course_name=?) AND exit_tmstmp !='0' AND help_tmstmp !='0'";

  // Append date ranges if necessary
  if(!is_null($start_date))
    if (!is_null($end_date))
      $query = $query . " AND enter_tmstmp >=? AND enter_tmstmp <?";
    else
      $query = $query . " AND enter_tmstmp >=?";

  $stmt  = mysqli_prepare($sql_conn, $query);
  if(!$stmt){
    mysqli_close($sql_conn);
    return -1;
  }

  // BETTER WAY TO DO THIS IN A SINGLE FUNCTION CALL?
  if(!is_null($start_date))
    if(!is_null($end_date))
      mysqli_stmt_bind_param($stmt, 'ssss', $stud_username, $course_name, $start_date, $end_date);
    else
      mysqli_stmt_bind_param($stmt, 'sss', $stud_username, $course_name, $start_date);
  else
    mysqli_stmt_bind_param($stmt, 'ss', $stud_username, $course_name);

  if(!mysqli_stmt_execute($stmt)){
    mysqli_stmt_close($stmt);
    mysqli_close($sql_conn);
    return -1;
  }

  mysqli_stmt_bind_result($stmt, $question, $location, $enter_tmstmp, $help_tmstmp, $exit_tmstmp, $helped_by);
  $result = [];
  while (mysqli_stmt_fetch($stmt)){
    $result[] = array('question'     => $question,
                      'location'     => $location,
                      'enter_tmstmp' => $enter_tmstmp,
                      'help_tmstmp'  => $help_tmstmp,
                      'exit_tmstmp'  => $exit_tmstmp,
                      'helped_by'    => $helped_by,
                     );
  }

  mysqli_stmt_close($stmt);
  mysqli_close($sql_conn);
  return $result;
}

/**
 * Returns a log of the helped students by ta_username in course_name. If no dates are
 * specified, a log for the entire history of the TA is returned. If start_date is
 * specified, a log from start_date (inclusive) to the present is returned. If start_date and
 * end_date are specified, a log from start_date (inclusive) to end_date (exclusive) is returned.
 *
 * @param string $ta_username
 * @param string $course_name
 * @param string $start_date helped timestamp (inclusive)
 * @param string $end_date helped timestamp (exclusive)
 * @return array of ta log entries for course
 *         int -1 on error
 */
function get_ta_log_for_course($ta_username, $course_name, $start_date, $end_date){
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return -1;
  }

  $query = "SELECT username, question, location, enter_tmstmp, help_tmstmp, exit_tmstmp, helped_by
            FROM student_log
            WHERE helped_by=? AND course_id=(SELECT course_id from courses where course_name=?) AND exit_tmstmp !='0' AND help_tmstmp !='0'";

  // Append date ranges if necessary
  if(!is_null($start_date))
    if (!is_null($end_date))
      $query = $query . " AND help_tmstmp >=? AND help_tmstmp <?";
    else
      $query = $query . " AND help_tmstmp >=?";

  $stmt  = mysqli_prepare($sql_conn, $query);
  if(!$stmt){
    mysqli_close($sql_conn);
    return -1;
  }

  // BETTER WAY TO DO THIS IN A SINGLE FUNCTION CALL?
  if(!is_null($start_date))
    if(!is_null($end_date))
      mysqli_stmt_bind_param($stmt, 'ssss', $ta_username, $course_name, $start_date, $end_date);
    else
      mysqli_stmt_bind_param($stmt, 'sss', $ta_username, $course_name, $start_date);
  else
    mysqli_stmt_bind_param($stmt, 'ss', $ta_username, $course_name);

  if(!mysqli_stmt_execute($stmt)){
    mysqli_stmt_close($stmt);
    mysqli_close($sql_conn);
    return -1;
  }

  mysqli_stmt_bind_result($stmt, $username, $question, $location, $enter_tmstmp, $help_tmstmp, $exit_tmstmp, $helped_by);
  $result = [];
  while (mysqli_stmt_fetch($stmt)){
    $result[] = array('student'      => $username,
                      'question'     => $question,
                      'location'     => $location,
                      'enter_tmstmp' => $enter_tmstmp,
                      'help_tmstmp'  => $help_tmstmp,
                      'exit_tmstmp'  => $exit_tmstmp,
                      'helped_by'    => $helped_by,
                     );
  }

  mysqli_stmt_close($stmt);
  mysqli_close($sql_conn);
  return $result;
}

/**
 * Returns a list of common statistics for course_id. If no dates are
 * specified, stats for the entire history of the queue are returned. If start_date is
 * specified, stats from start_date (inclusive) to the present are returned. If start_date and
 * end_date are specified, stats from start_date (inclusive) to end_date (exclusive) are returned.
 *
 * @param string $course_id
 * @param string $start_date enter queue timestamp (inclusive)
 * @param string $end_date enter queue timestamp (exclusive)
 * @return array of course stats
 *         int -1 on error
 */
function get_course_stats($course_id, $start_date, $end_date){
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return -1;
  }

  $query = "SELECT
              ROUND(AVG(TIME_TO_SEC(TIMEDIFF(help_tmstmp, enter_tmstmp)))   , 0)  AS avg_wait_time,
              ROUND(STDDEV(TIME_TO_SEC(TIMEDIFF(help_tmstmp, enter_tmstmp))), 0)  AS stddev_wait_time,
              ROUND(AVG(TIME_TO_SEC(TIMEDIFF(exit_tmstmp, help_tmstmp)))    , 0)  AS avg_help_time,
              ROUND(STDDEV(TIME_TO_SEC(TIMEDIFF(exit_tmstmp, help_tmstmp))) , 0)  AS stddev_help_time
              FROM student_log
              WHERE exit_tmstmp !='0' AND help_tmstmp !='0' AND course_id=?";

  // Append date ranges if necessary
  if(!is_null($start_date))
    if (!is_null($end_date))
      $query = $query . " AND enter_tmstmp >=? AND enter_tmstmp <?";
    else
      $query = $query . " AND enter_tmstmp >=?";

  $stmt  = mysqli_prepare($sql_conn, $query);
  if(!$stmt){
    mysqli_close($sql_conn);
    return -1;
  }

  // BETTER WAY TO DO THIS IN A SINGLE FUNCTION CALL?
  if(!is_null($start_date))
    if(!is_null($end_date))
      mysqli_stmt_bind_param($stmt, 'sss', $course_id, $start_date, $end_date);
    else
      mysqli_stmt_bind_param($stmt, 'ss', $course_id, $start_date);
  else
    mysqli_stmt_bind_param($stmt, 's', $course_id);

  if(!mysqli_stmt_execute($stmt)){
    mysqli_stmt_close($stmt);
    mysqli_close($sql_conn);
    return -1;
  }

  mysqli_stmt_bind_result($stmt, $avg_wait_time, $stddev_wait_time, $avg_help_time, $stddev_help_time);
  mysqli_stmt_fetch($stmt);
  return array('avg_wait_time'    => $avg_wait_time,
               'stddev_wait_time' => $stddev_wait_time,
               'avg_help_time'    => $avg_help_time,
               'stddev_help_time' => $stddev_help_time
              );
}

/**
 * Returns a log of the number of users helped per day in the queue. If no dates are
 * specified, a log for the entire history of the queue is returned. If start_date is
 * specified, a log from start_date (inclusive) to the present is returned. If start_date and
 * end_date are specified, a log from start_date (inclusive) to end_date (exclusive) is returned.
 *
 * @param string $course_id
 * @param string $start_date enter queue timestamp (inclusive)
 * @param string $end_date enter queue timestamp (exclusive)
 * @return array of course usage stats by day
 *         int -1 on error
 */
function get_course_usage_by_day($course_id, $start_date, $end_date){
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return -1;
  }

  // Append date ranges if necessary
  $range_condition = "";
  if(!is_null($start_date))
    if (!is_null($end_date))
      $range_condition = " AND enter_tmstmp >=? AND enter_tmstmp <? ";
    else
      $range_condition = " AND enter_tmstmp >=? ";

  $query = "SELECT
            (SELECT full_name FROM users WHERE username = helped_by) AS helped_by, DATE(enter_tmstmp), COUNT(*)
            FROM student_log
            WHERE exit_tmstmp !='0' AND help_tmstmp !='0' AND course_id=?" . $range_condition .
           "GROUP BY DATE(enter_tmstmp), helped_by";
  $stmt  = mysqli_prepare($sql_conn, $query);
  if(!$stmt){
    mysqli_close($sql_conn);
    return -1;
  }

  // BETTER WAY TO DO THIS IN A SINGLE FUNCTION CALL?
  if(!is_null($start_date))
    if (!is_null($end_date))
      mysqli_stmt_bind_param($stmt, 'sss', $course_id, $start_date, $end_date);
    else
      mysqli_stmt_bind_param($stmt, 'ss', $course_id, $start_date);
  else
    mysqli_stmt_bind_param($stmt, 's', $course_id);

  if(!mysqli_stmt_execute($stmt)){
    mysqli_stmt_close($stmt);
    mysqli_close($sql_conn);
    return -1;
  }

  mysqli_stmt_bind_result($stmt, $helped_by, $date, $count);
  $result = [];
  while (mysqli_stmt_fetch($stmt)){
    $result[] = array('date'            => $date,
                      'helped_by'       => $helped_by,
                      'students_helped' => $count
                     );
  }

  mysqli_stmt_close($stmt);
  mysqli_close($sql_conn);
  return $result;
}



/**
 * Returns a log of the number of users helped per day in the queue. If no dates are
 * specified, a log for the entire history of the queue is returned. If start_date is
 * specified, a log from start_date (inclusive) to the present is returned. If start_date and
 * end_date are specified, a log from start_date (inclusive) to end_date (exclusive) is returned.
 *
 * @param string $course_name
 * @param string $start_date enter queue timestamp (inclusive)
 * @param string $end_date enter queue timestamp (exclusive)
 * @return array of course usage stats by day
 *         int -1 on error
 */
function get_course_avg_help_time($course_name, $start_date, $end_date){
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return -1;
  }

  // Append date ranges if necessary
  $range_condition = "";
  if(!is_null($start_date))
    if (!is_null($end_date))
      $range_condition = " AND enter_tmstmp >=? AND enter_tmstmp <? ";
    else
      $range_condition = " AND enter_tmstmp >=? ";

  $query = "SELECT DATE(enter_tmstmp) as date, SEC_TO_TIME(AVG(TIME_TO_SEC(TIMEDIFF(exit_tmstmp,enter_tmstmp)))) as avg_help_time
            FROM student_log
            WHERE course_id=(SELECT course_id FROM courses where course_name=?)" . $range_condition .
            "GROUP BY DATE(enter_tmstmp)";

  $stmt  = mysqli_prepare($sql_conn, $query);
  if(!$stmt){
    mysqli_close($sql_conn);
    return -1;
  }

  // BETTER WAY TO DO THIS IN A SINGLE FUNCTION CALL?
  if(!is_null($start_date))
    if (!is_null($end_date))
      mysqli_stmt_bind_param($stmt, 'sss', $course_name, $start_date, $end_date);
    else
      mysqli_stmt_bind_param($stmt, 'ss', $course_name, $start_date);
  else
    mysqli_stmt_bind_param($stmt, 's', $course_name);

  if(!mysqli_stmt_execute($stmt)){
    mysqli_stmt_close($stmt);
    mysqli_close($sql_conn);
    return -1;
  }

  mysqli_stmt_bind_result($stmt, $date, $count);
  $result = [];
  while (mysqli_stmt_fetch($stmt)){
    $result[] = array('date'            => $date,
                      'avg_help_time'   => $count
                     );
  }

  mysqli_stmt_close($stmt);
  mysqli_close($sql_conn);
  return $result;
}


/**
 * Returns a log of the number of users helped per day in the queue. If no dates are
 * specified, a log for the entire history of the queue is returned. If start_date is
 * specified, a log from start_date (inclusive) to the present is returned. If start_date and
 * end_date are specified, a log from start_date (inclusive) to end_date (exclusive) is returned.
 *
 * @param string $course_id
 * @param string $start_date enter queue timestamp (inclusive)
 * @param string $end_date enter queue timestamp (exclusive)
 * @return array of course usage stats by day
 *         int -1 on error
 */
function get_ta_proportions($course_id, $start_date, $end_date){
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return -1;
  }

  // Append date ranges if necessary
  $range_condition = "";
  if(!is_null($start_date))
    if (!is_null($end_date))
      $range_condition = " AND enter_tmstmp >=? AND enter_tmstmp <? ";
    else
      $range_condition = " AND enter_tmstmp >=? ";

  $query = "SELECT
            (SELECT full_name FROM users WHERE username = helped_by) AS helped_by, COUNT(*)
            FROM student_log
            WHERE exit_tmstmp !='0' AND help_tmstmp !='0' AND course_id=?" . $range_condition .
           "GROUP BY helped_by";
  $stmt  = mysqli_prepare($sql_conn, $query);
  if(!$stmt){
    mysqli_close($sql_conn);
    return -1;
  }

  // BETTER WAY TO DO THIS IN A SINGLE FUNCTION CALL?
  if(!is_null($start_date)){
    if (!is_null($end_date))
      mysqli_stmt_bind_param($stmt, 'sss', $course_id, $start_date, $end_date);
    else
      mysqli_stmt_bind_param($stmt, 'ss', $course_id, $start_date);
  }else
    mysqli_stmt_bind_param($stmt, 's', $course_id);

  if(!mysqli_stmt_execute($stmt)){
    mysqli_stmt_close($stmt);
    mysqli_close($sql_conn);
    return -1;
  }

  mysqli_stmt_bind_result($stmt, $helped_by, $count);
  $result = [];
  while (mysqli_stmt_fetch($stmt)){
    $result[] = array('helped_by'       => $helped_by,
                      'students_helped' => $count
                     );
  }

  mysqli_stmt_close($stmt);
  mysqli_close($sql_conn);
  return $result;
}

function get_ta_avg_help_time($course_id, $start_date, $end_date){
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return -1;
  }

  // Append date ranges if necessary
  $range_condition = "";
  if(!is_null($start_date))
    if (!is_null($end_date))
      $range_condition = " AND enter_tmstmp >=? AND enter_tmstmp <? ";
    else
      $range_condition = " AND enter_tmstmp >=? ";

  $query = "SELECT
            (SELECT full_name FROM users WHERE username = helped_by) AS TA,
            AVG(TIME_TO_SEC(TIMEDIFF(exit_tmstmp, help_tmstmp))) AS avg_help_time FROM student_log
            WHERE exit_tmstmp !='0' AND help_tmstmp !='0' AND course_id=?" . $range_condition .
           "GROUP BY TA";
  $stmt  = mysqli_prepare($sql_conn, $query);
  if(!$stmt){
    mysqli_close($sql_conn);
    return -1;
  }

  // BETTER WAY TO DO THIS IN A SINGLE FUNCTION CALL?
  if(!is_null($start_date)){
    if (!is_null($end_date))
      mysqli_stmt_bind_param($stmt, 'sss', $course_id, $start_date, $end_date);
    else
      mysqli_stmt_bind_param($stmt, 'ss', $course_id, $start_date);
  }else
    mysqli_stmt_bind_param($stmt, 's', $course_id);

  if(!mysqli_stmt_execute($stmt)){
    mysqli_stmt_close($stmt);
    mysqli_close($sql_conn);
    return -1;
  }

  mysqli_stmt_bind_result($stmt, $TA, $avg_help_time);
  $result = [];
  while (mysqli_stmt_fetch($stmt)){
    $result[] = array('TA'            => $TA,
                      'avg_help_time' => intval($avg_help_time)
                     );
  }

  mysqli_stmt_close($stmt);
  mysqli_close($sql_conn);
  return $result;
}

?>
