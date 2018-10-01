<?php
require_once 'config.php';
/**
 * SPDX-License-Identifier: GPL-3.0-or-later
 * Copyright (c) 2018 Zane Zakraisek
 *
 * Functions for manipulating the queues
 * 
 */

/**
 * Returns the state of a queue
 * TODO: Consider breaking this into four smaller functions
 *
 * @param string $course
 * @return array of queue data on success
 *         int -1 on general error
 *         int -2 on nonexistent course
 */
function get_queue($course_name){
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return -1; //SQL error
  }

  #NOTE: we don't have to use SQL Parameters in ths 
  #function since the block of code below sanitizes the $course_name input
  $course_id = course_name_to_id($course_name, $sql_conn);
  if($course_id < 0){
    mysqli_close($sql_conn);
    return $course_id; //SQL error
  }

  #Build return array
  $return = array();


  #Get the state of the queue, if its not here, it must be closed
  $query  = "SELECT * FROM queue_state WHERE course_id ='".$course_id."'";
  $result = mysqli_query($sql_conn, $query);
  if(!$result){
    mysqli_close($sql_conn);
    return -1;
  }
  if(!mysqli_num_rows($result)){
    $return["state"] = "closed";
  }else{
    $entry = mysqli_fetch_assoc($result);
    $return["state"]    = $entry["state"];
    $return["time_lim"] = intval($entry["time_lim"]);
    $return["cooldown"] = intval($entry["cooldown"]);
  }


  #Get the announcements
  $return["announcements"] = [];
  $query  = "SELECT id, announcement, (SELECT full_name FROM users WHERE username = announcements.poster) AS poster, tmstmp 
             FROM announcements WHERE course_id ='".$course_id."' 
             ORDER BY id DESC";
  $result = mysqli_query($sql_conn, $query);
  if(!$result){
    mysqli_close($sql_conn);
    return -1;
  }
  while($entry = mysqli_fetch_assoc($result)){
    $return["announcements"][] = $entry;
  }


  #Get the state of the TAs
  $return["TAs"]      = [];
  $query  = "SELECT ta_status.username, (SELECT TIMEDIFF(NOW(), ta_status.state_tmstmp)) as duration, users.full_name, (SELECT username FROM queue WHERE position=helping LIMIT 1) as helping 
             FROM ta_status INNER JOIN users on ta_status.username = users.username 
             WHERE course_id='".$course_id."'";
  $result = mysqli_query($sql_conn, $query);
  if(!$result){
    mysqli_close($sql_conn);
    return -1;
  }
  while($entry = mysqli_fetch_assoc($result)){
    $return["TAs"][] = $entry;
  }


  #Get the actual queue
  $return["queue"]    = [];
  $query  = "SELECT queue.username, users.full_name, queue.question, queue.location 
             FROM queue INNER JOIN users on queue.username = users.username
             WHERE course_id ='".$course_id."' ORDER BY position";
  $result = mysqli_query($sql_conn, $query);
  if(!$result){
    mysqli_close($sql_conn);
    return -1;
  }
  while($entry = mysqli_fetch_assoc($result)){
    $return["queue"][] = $entry;
  }
  $return["queue_length"] = count($return["queue"]);


  mysqli_close($sql_conn);
  return $return;
}

/**
 * Adds student to queue
 *
 * @param string $username
 * @param string $course_name
 * @param string $question
 * @param string $location
 * @return int 0  on success
 *         int -1 on general error
 *         int -2 on nonexistent course
 *         int -3 on closed course
 *         int -7 on user on cooldown state
 */
function enq_stu($username, $course_name, $question, $location){
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return -1;
  }

  $queue_state = get_queue_state($course_name);
  if($queue_state < 0){
    mysqli_close($sql_conn);
    return $queue_state;
  }elseif($queue_state != "open"){
    mysqli_close($sql_conn);
    return -3;
  }

  //Check cooldown settings for queue
  $course_id = course_name_to_id($course_name, $sql_conn);
  $course_cooldown = get_course_cooldown($course_id, $sql_conn);
  if($course_cooldown < 0){ //error
    return $course_cooldown;
  }elseif($course_cooldown){//cooldown period enabled
    $result = check_user_cooldown($username, $course_cooldown, $course_id, $sql_conn);
    if($result < 0){
      return $result; //error
    }elseif($result){ //user still has time left on cooldown
      mysqli_close($sql_conn);
      return -7;
    }
  }

  $query = "INSERT INTO queue (username, course_id, question, location) 
            VALUES (?, (SELECT course_id FROM courses WHERE course_name=?), ?, ?) 
            ON DUPLICATE KEY UPDATE question=?";
  $stmt  = mysqli_prepare($sql_conn, $query);
  if(!$stmt){
    mysqli_close($sql_conn);
    return -1;
  }
  mysqli_stmt_bind_param($stmt, "sssss", $username, $course_name, $question, $location, $question);
 
  $res = mysqli_stmt_execute($stmt);
  if(!$res){
    mysqli_stmt_close($stmt);
    mysqli_close($sql_conn);
    return -1;
  }

  //TODO: If UPDATE, return here so it won't add another log entry.

  #Log the student in the student_log table
  #If MySQL worked properly, we'd be able to completely implement all logging
  #stricly in the DB with triggers. Bug #11472, and the fact that you cannot swap rows
  #with uniqueness constraints in MySQL force me to take this route instead.
  $query = "INSERT INTO student_log (username, course_id, question, location) 
            VALUES (?, (SELECT course_id FROM courses WHERE course_name=?), ?, ?)"; 
  $stmt  = mysqli_prepare($sql_conn, $query);
  if(!$stmt){
    mysqli_close($sql_conn);
    return -1;
  }
  $ret = 0;
  mysqli_stmt_bind_param($stmt, "ssss", $username, $course_name, $question, $location);
  if(!mysqli_stmt_execute($stmt)){
    $ret = -1;
  } 

  mysqli_stmt_close($stmt);
  mysqli_close($sql_conn);
  return $ret;
}

/**
 * Remove student from queue
 *
 * If a TA is helping this student, SQL will free the TA.
 * 
 * @param string $username
 * @param string $course
 * @return int 0  on success
 *         int -1 on general error
 *         int -2 on nonexistent course
 *         int -3 on closed course
 */
function deq_stu($username, $course_name){
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return -1;
  }

  $queue_state = get_queue_state($course_name);
  if($queue_state < 0){
    mysqli_close($sql_conn);
    return $queue_state;
  }
  elseif($queue_state == "closed"){
    mysqli_close($sql_conn);
    return -3;
  }  

  $query = "DELETE queue from queue NATURAL JOIN courses 
            WHERE username=? AND course_name=?";
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
  //If nobody was effected, return gracefully
  //instead of trying to log the deletion.
  if(!mysqli_stmt_affected_rows($stmt)){
    mysqli_stmt_close($stmt);
    mysqli_close($sql_conn);
    return 0;
  }

  #Log the student in the student_log table
  #If MySQL worked properly, we'd be able to completely implement all logging
  #stricly in the DB with triggers. Bug #11472, and the fact that you cannot swap rows
  #with uniqueness constraints in MySQL force me to take this route instead.
  $query = "UPDATE student_log SET exit_tmstmp = NOW() 
            WHERE username = ? AND course_id = (SELECT course_id FROM courses WHERE course_name = ?) ORDER BY id DESC limit 1;";
  $stmt  = mysqli_prepare($sql_conn, $query);
  if(!$stmt){
    mysqli_close($sql_conn);
    return -1;
  }
  $ret = 0;
  mysqli_stmt_bind_param($stmt, "ss", $username, $course_name);
  if(!mysqli_stmt_execute($stmt)){
    $ret = -1;
  }

  mysqli_stmt_close($stmt);
  mysqli_close($sql_conn);
  return $ret;
}

/**
 * Put TA on duty.
 * If TA is already on duty, this frees them if they
 * were helping a student, but does NOT dequeue the student.
 *
 * @param string $username
 * @param string $course_name
 * @return int 0  on success
 *         int -1 on general error
 *         int -2 on nonexistent course
 *         int -3 on closed course
 */
function enq_ta($username, $course_name){
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return -1;
  }

  $queue_state = get_queue_state($course_name);
  if($queue_state < 0){
    mysqli_close($sql_conn);
    return $queue_state;
  }
  elseif($queue_state == "closed"){
    mysqli_close($sql_conn);
    return -3;
  }

  $query = "INSERT INTO ta_status (username, course_id) 
            VALUES (?, (SELECT course_id FROM courses WHERE course_name=?) )
            ON DUPLICATE KEY UPDATE helping=NULL";
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
 * Remove TA from queue
 *
 * @param string $username
 * @param string $course_name
 * @return int 0  on success
 *         int -1 on general error
 *         int -2 on nonexistent course
 *         int -3 on closed course
 */
function deq_ta($username, $course_name){
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return -1;
  }

  $queue_state = get_queue_state($course_name);
  if($queue_state < 0){
    mysqli_close($sql_conn);
    return $queue_state;
  }
  elseif($queue_state == "closed"){
    mysqli_close($sql_conn);
    return -3;
  }

  $query = "DELETE FROM ta_status 
            WHERE username=? 
            AND course_id=(SELECT course_id FROM courses WHERE course_name=?)"; 
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
 * Gets the status of the TA for the course
 *
 * @param string $username
 * @param string $course_name
 * @return int -1 on general error
 *         int -2 on nonexistent course
 *         int -3 on closed course
 *         int  1 if TA not on duty
 *         int  2 if on duty, but not helping anyone
 *         int  3 if on duty, and helping someone
 */
function get_ta_status($username, $course_name){
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return -1;
  }

  $queue_state = get_queue_state($course_name);
  if($queue_state < 0){
    mysqli_close($sql_conn);
    return $queue_state;
  }
  elseif($queue_state == "closed"){
    mysqli_close($sql_conn);
    return -3;
  }

  $query  = "SELECT helping FROM ta_status 
             WHERE username=? 
             AND course_id=(SELECT course_id FROM courses WHERE course_name=?)"; 
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
  mysqli_stmt_bind_result($stmt, $helping);
  if(mysqli_stmt_fetch($stmt) == NULL){
    mysqli_stmt_close($stmt);
    mysqli_close($sql_conn);
    return -1;
  }

  mysqli_stmt_close($stmt);
  mysqli_close($sql_conn);

  if($helping == NULL){
    return 2;
  }
  return 3;
}

/**
 * Help particular student in queue
 *
 * @param string $TA_username
 * @param string $stud_username
 * @param string $course
 * @return int 0  on success
 *         int -1 on general fail
 *         int -2 on nonexistent course
 *         int -3 on closed course
 *         int -4 on TA not on duty
 */
function help_student($TA_username, $stud_username, $course_name){
 $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return -1;
  }

  $queue_state = get_queue_state($course_name);
  if($queue_state < 0){
    mysqli_close($sql_conn);
    return $queue_state;
  }elseif($queue_state == "closed"){
    mysqli_close($sql_conn);
    return -3;
  }

  if(get_ta_status($TA_username, $course_name) < 2){
    mysqli_close($sql_conn);
    return -4;
  }

  //TODO: Do you even SQL brah?
  $query = "REPLACE INTO ta_status (username, course_id, helping)
            VALUES (?,(SELECT course_id FROM courses WHERE course_name=?), (SELECT position FROM queue WHERE username=? AND course_id=(SELECT course_id FROM courses WHERE course_name=?)))";
  $stmt  = mysqli_prepare($sql_conn, $query);
  if(!$stmt){
    mysqli_close($sql_conn);
    return -1;
  }
  mysqli_stmt_bind_param($stmt, "ssss", $TA_username, $course_name, $stud_username, $course_name);
  if(!mysqli_stmt_execute($stmt) || !mysqli_stmt_affected_rows($stmt)){
    mysqli_stmt_close($stmt);
    mysqli_close($sql_conn);
    return -1;
  }

  mysqli_stmt_close($stmt);
  mysqli_close($sql_conn);
  return 0;
}

/**
 * Set the time limit for the queue or 0 for no limit.
 *
 * @param string $time_lim in minutes
 * @param string $course_name
 * @return int 0  on success,
 *         int -1 on general fail
 *         int -2 on nonexistent course
 *         int -3 on closed course
 */
function set_time_lim($time_lim, $course_name){
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return -1;
  }

  $queue_state = get_queue_state($course_name);
  if($queue_state < 0){
    mysqli_close($sql_conn);
    return $queue_state;
  }elseif($queue_state == "closed"){
    mysqli_close($sql_conn);
    return -3;
  }

  $query = "UPDATE queue_state SET time_lim = ? 
            WHERE course_id=(SELECT course_id FROM courses WHERE course_name = ?)";
  $stmt  = mysqli_prepare($sql_conn, $query);
  if(!$stmt){
    mysqli_close($sql_conn);
    return -1;
  }
  mysqli_stmt_bind_param($stmt, "is", $time_lim, $course_name);
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
 * Set the cool down time for the queue or 0 for no limit.
 * This is the number of minutes a student must wait before
 * reentering the queue.
 *
 * @param string $cooldown in minutes
 * @param string $course_name
 * @return int 0  on success,
 *         int -1 on general error
 *         int -2 on nonexistent course
 *         int -3 on closed course
 */
function set_cooldown($time_lim, $course_name){
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return -1;
  }

  $queue_state = get_queue_state($course_name);
  if($queue_state < 0){
    mysqli_close($sql_conn);
    return $queue_state;
  }elseif($queue_state == "closed"){
    mysqli_close($sql_conn);
    return -3;
  }

  $query = "UPDATE queue_state SET cooldown = ? 
            WHERE course_id=(SELECT course_id FROM courses WHERE course_name = ?)";
  $stmt  = mysqli_prepare($sql_conn, $query);
  if(!$stmt){
    mysqli_close($sql_conn);
    return -1;
  }
  mysqli_stmt_bind_param($stmt, "is", $time_lim, $course_name);
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
 * Moves a student up one position in the queue
 *
 * @param string $stud_username
 * @param string $course
 * @param string $operation {increase, decrease}
 * @return int 0  on success
 *         int -1 on general error
 *         int -2 on nonexistent course
 *         int -3 on closed course
 */
function increase_stud_priority($stud_username, $course_name){
  return change_stud_priority($stud_username, $course_name, "increase");
}

/**
 * Moves a student down one position in the queue
 *
 * @param string $stud_username
 * @param string $course
 * @param string $operation {increase, decrease}
 * @return int 0  on success
 *         int -1 on general 
 *         int -2 on nonexistent course
 *         int -3 on closed course
 */
function decrease_stud_priority($stud_username, $course_name){
  return change_stud_priority($stud_username, $course_name, "decrease");
}

/**
 * Get the state of the queue
 *
 * @param string $course_name
 * @return string $state of queue
 *         int -1 on general error
 *         int -2 on nonexistent course
 */
function get_queue_state($course_name){
  return change_queue_state($course_name, NULL);
}

/**
 * Open the queue
 *
 * @param string $course_name
 * @return int  0 on success
 *         int -1 on general error
 *         int -2 on nonexistent course
 */
function open_queue($course_name){
  $ret = change_queue_state($course_name, "open");
  if($ret == "open"){
    return 0;
  }
  return $ret;
}

/**
 * Close the queue
 *
 * @param string $course_name
 * @return int  0 on success
 *         int -1 on general error
 *         int -2 on nonexistent course
 */
function close_queue($course_name){
  $ret = change_queue_state($course_name, "closed");
  if($ret == "closed"){
    return 0;
  }
  return $ret;
}

/**
 * Freeze the queue
 *
 * @param string $course_name
 * @return int  0 on success
 *         int -1 on general error
 *         int -2 on nonexistent course
 */
function freeze_queue($course_name){
  $ret = change_queue_state($course_name, "frozen");
  if($ret == "frozen"){
    return 0;
  }
  return $ret;
}

/**
 * Post announcement to the course
 *
 * @param string $course_name
 * @param string $announcement
 * @param string $poster
 * @return int 0  on success
 *         int -1 on general error
 *         int -2 on nonexistent course
 */
function add_announcement($course_name, $announcement, $poster){
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return -1;
  }

  $course_id = course_name_to_id($course_name, $sql_conn);
  if($course_id == -1){
    mysqli_close($sql_conn);
    return -1; //SQL error
  }elseif($course_id == -2){
    mysqli_close($sql_conn);
    return -2; //Nonexistent course
  }

  $query = "INSERT INTO announcements (course_id, announcement, poster) VALUES (?, ?, ?)";
  $stmt  = mysqli_prepare($sql_conn, $query);
  if(!$stmt){
    mysqli_close($sql_conn);
    return -1;
  }
  mysqli_stmt_bind_param($stmt, "iss", $course_id, $announcement, $poster);
  if(!mysqli_stmt_execute($stmt) || !mysqli_stmt_affected_rows($stmt)){
    mysqli_stmt_close($stmt);
    mysqli_close($sql_conn);
    return -1;
  }

  mysqli_stmt_close($stmt);
  mysqli_close($sql_conn);
  return 0;
}

/**
 * Delete announcement for course
 *
 * @param string $course_name
 * @param int    $announcement_id
 * @return int  0 on success
 *         int -1 on general error
 *         int -2 on nonexistent course
 */
function del_announcement($course_name, $announcement_id){
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return -1;
  }

  $course_id = course_name_to_id($course_name, $sql_conn);
  if($course_id == -1){
    mysqli_close($sql_conn);
    return -1; //SQL error
  }elseif($course_id == -2){
    mysqli_close($sql_conn);
    return -2; //Nonexistent course
  }

  $query = "DELETE FROM announcements 
            WHERE id = ? AND course_id = ?";
  $stmt  = mysqli_prepare($sql_conn, $query);
  if(!$stmt){
    mysqli_close($sql_conn);
    return -1;
  }
  mysqli_stmt_bind_param($stmt, "ii", $announcement_id, $course_id);
  if(!mysqli_stmt_execute($stmt)){
    mysqli_stmt_close($stmt);
    mysqli_close($sql_conn);
    return -1;
  }

  mysqli_stmt_close($stmt);
  mysqli_close($sql_conn);
  return 0;
}

//HELPER FUNCTIONS
/**
 * Changes the state of the course queue
 * 
 * I'd like to move the input and output states
 * from strings to ints
 *
 * TODO: This function should be rewritten. It's not clean.
 *
 * @param string $course_name
 * @param string $state
 * @return string $state of queue
 *         int -1 on general error
 *         int -2 on nonexistent course
 */
function change_queue_state($course_name, $state){
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return -1;
  }

  #Note that we don't have to use SQL Parameters in the 
  #following functions since the block of code below
  #sanitizes the input
  $course_id = course_name_to_id($course_name, $sql_conn);
  if($course_id == -1){
    mysqli_close($sql_conn);
    return -1; //SQL error
  }elseif($course_id == -2){
    mysqli_close($sql_conn);
    return -2; //Nonexistent course
  }

  if(is_null($state)){ //Just querying the state of the queue if $state==NULL
    $query  = "SELECT state FROM queue_state WHERE course_id ='".$course_id."'";
    $result = mysqli_query($sql_conn, $query);
    if(!$result){
      mysqli_close($sql_conn);
      return -1;
    }
    if(!mysqli_num_rows($result)){
      mysqli_close($sql_conn);
      return "closed";
    }
    $entry = mysqli_fetch_assoc($result);
    mysqli_close($sql_conn);
    return $entry["state"];
  }elseif($state == "closed"){ //By deleting the entry in queue_state, we cascade the other entries
    $query = "DELETE FROM queue_state WHERE course_id = '".$course_id."'";
  }elseif($state == 'frozen' || $state == 'open'){ //Since REPLACE calls DELETE then INSERT, calling REPLACE would CASCADE all other tables, we use ON DUPLICATE KEY UPDATE instead
    $query = "INSERT INTO queue_state (course_id, state) VALUES ('".$course_id."','".$state."') ON DUPLICATE KEY UPDATE state='".$state."'";
  }else{
    mysqli_close($sql_conn);
    return -1;
  }

  if(!mysqli_query($sql_conn, $query)){
    mysqli_close($sql_conn);
    return -1;
  }

  mysqli_close($sql_conn);
  return $state;
}

/**
 * Converts the name of a course to the course ID used in SQL
 * 
 * I'd like to eventually get rid of this function in favor of 
 * just embedding subqueries or doing table joins
 * 
 * For now though, this function is used mainly to prevent
 * SQL injection in the functions that call it, by verifying
 * course_name input
 *
 * @param string $course_name
 * @param sql_conn $sql_conn
 * @return int course_id used in SQL
 *         int -1 on general error
 *         int -2 on nonexistent course
 */
function course_name_to_id($course_name, $sql_conn){
  if(!$sql_conn){
    return -1;
  }

  $query = "SELECT course_id FROM courses WHERE course_name=?";
  $stmt  = mysqli_prepare($sql_conn, $query);
  if(!$stmt){
    return -1;
  }
  mysqli_stmt_bind_param($stmt, "s", $course_name);
  if(!mysqli_stmt_execute($stmt)){
    mysqli_stmt_close($stmt);
    return -1;
  }

  $course_id = -2;
  mysqli_stmt_bind_result($stmt, $course_id);
  mysqli_stmt_fetch($stmt);
  
  mysqli_stmt_close($stmt);
  return $course_id;
}

/**
 * Changes a students position in the queue
 *
 * @param string $stud_username
 * @param string $course
 * @param string $operation {increase, decrease}
 * @return int 0  on success
 *         int -1 on general error
 *         int -2 on nonexistent course
 *         int -3 on closed course
 */
function change_stud_priority($stud_username, $course_name, $operation){
  $sql_conn = mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASSWD, DATABASE);
  if(!$sql_conn){
    return -1;
  }

  $queue_state = get_queue_state($course_name);
  if($queue_state < 0){
    mysqli_close($sql_conn);
    return $queue_state;
  }elseif(get_queue_state($course_name) == "closed"){
    mysqli_close($sql_conn);
    return -3;
  }

  $query = "SELECT position, username, course_id, question, location FROM queue WHERE username=? AND course_id=(SELECT course_id from courses where course_name=?)";
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
  mysqli_stmt_bind_result($stmt, $position1, $username1, $course_id, $question1, $location1);
  mysqli_stmt_fetch($stmt);
  mysqli_stmt_close($stmt);

  if($operation == "increase"){
    $query = "SELECT position, username, course_id, question, location FROM queue 
              WHERE position<'".$position1."' AND course_id='".$course_id."' AND position NOT IN (SELECT helping FROM ta_status WHERE helping IS NOT NULL AND course_id='".$course_id."') 
              ORDER BY position DESC LIMIT 1";
  }elseif($operation == "decrease"){
    $query = "SELECT position, username, course_id, question, location FROM queue 
              WHERE position>'".$position1."' AND course_id='".$course_id."' AND position NOT IN (SELECT helping FROM ta_status WHERE helping IS NOT NULL AND course_id='".$course_id."') 
              ORDER BY position ASC LIMIT 1";
  }else{
    mysqli_close($sql_conn);
    return -1;
  }

  $result = mysqli_query($sql_conn, $query);
  if(!$result){
    mysqli_close($sql_conn);
    return -1;
  }

  $entry = mysqli_fetch_assoc($result);
  if(!$entry){
    mysqli_close($sql_conn);
    return 0;//Nobody to switch with
  }

  $position2 = $entry['position'];
  $username2 = $entry['username'];
  $question2 = $entry['question'];
  $location2 = $entry['location'];

  #####SQL TRANSACTION#####
  mysqli_autocommit($sql_conn, false);

  $query = "DELETE FROM queue WHERE position = '".$position1."'";
  $res = mysqli_query($sql_conn, $query);
  $query = "DELETE FROM queue WHERE position = '".$position2."'";
  $res = mysqli_query($sql_conn, $query) && $res;

  $query = "INSERT INTO queue (position, username, course_id, question, location) 
            VALUES ('".$position2."', '".$username1."', '".$course_id."', '".$question1."', '".$location1."')";
  $res = mysqli_query($sql_conn, $query) && $res;
  $query = "INSERT INTO queue (position, username, course_id, question, location) 
            VALUES ('".$position1."', '".$username2."', '".$course_id."', '".$question2."', '".$location2."');";
  $res = mysqli_query($sql_conn, $query) && $res;
  
  $ret = 0;
  if($res){
    mysqli_commit($sql_conn);
  }else{
    mysqli_rollback($sql_conn);
    $ret = -1;
  }
  #########################

  mysqli_close($sql_conn);
  return $ret;
}

/**
 * Retrieves the cooldown setting for a course
 * 
 * @param string $course_id
 * @param string $sql_conn
 * @return int  0 if no cooldown set
 *         int -1 on general error
 *         int >0 in cooldown minutes
 */
function get_course_cooldown($course_id, $sql_conn){
  if(!$sql_conn){
    return -1;
  }

  $query = "SELECT cooldown FROM queue_state WHERE course_id=?";
  $stmt  = mysqli_prepare($sql_conn, $query);
  if(!$stmt){
    return -1;
  }
  mysqli_stmt_bind_param($stmt, "s", $course_id);
  if(!mysqli_stmt_execute($stmt)){
    mysqli_stmt_close($stmt);
    return -1;
  }

  $cooldown = 0;
  mysqli_stmt_bind_result($stmt, $cooldown);
  mysqli_stmt_fetch($stmt);

  mysqli_stmt_close($stmt);
  return $cooldown;
}

/**
 * Checks if a user may join the queue based on the given cooldown time.
 *
 * @param string $stud_username
 * @param string $course_cooldown in minutes
 * @param string $course_id
 * @param string $sql_conn
 * @return int  0 if able to join
 *         int -1 on general error
 *         int >0 in seconds until able to join
 */
function check_user_cooldown($stud_username, $course_cooldown, $course_id, $sql_conn){
  if(!$sql_conn){
    return -1;
  }

  $query = "SELECT TIME_TO_SEC(TIMEDIFF(NOW(), exit_tmstmp)) as user_cooldown FROM student_log WHERE username = ? AND course_id = ? AND help_tmstmp != 0 ORDER BY help_tmstmp DESC LIMIT 1";
  $stmt  = mysqli_prepare($sql_conn, $query);
  if(!$stmt){
    return -1;
  }
  mysqli_stmt_bind_param($stmt, "si", $stud_username, $course_id);
  if(!mysqli_stmt_execute($stmt)){
    mysqli_stmt_close($stmt);
    return -1;
  }

  mysqli_stmt_bind_result($stmt, $user_cooldown);
  mysqli_stmt_fetch($stmt);
  mysqli_stmt_close($stmt);

  $course_cooldown_min = $course_cooldown * 60;
  if(is_null($user_cooldown)){
    return 0; //Good to go
  }elseif($course_cooldown_min > $user_cooldown){
    return $course_cooldown_min - $user_cooldown;
  }else{
    return 0;
  }
}
?>
