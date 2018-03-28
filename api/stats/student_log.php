<?php
// File: dequeue_student.php
// SPDX-License-Identifier: GPL-3.0-or-later

require_once '../../model/stats.php';
require_once '../errors.php';

// get the session variables
session_start();
header('Content-type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== "POST")
{
  http_response_code(405);
  echo json_encode( invalid_method("POST") );
  die();
}

if (!isset($_SESSION['username']))
{
  http_response_code(401);
  echo json_encode( not_authenticated() );
  die();
}

$username   = $_SESSION['username'];
$ta_courses = $_SESSION["ta_courses"];

//If a course is specified, get the log
//for the student in that course. If not,
//get the log for all the student's courses
if (isset($_POST['course']))
{
  $course = $_POST['course'];
  //Since this enpoint is used for students and TAs,
  //we check if the request came from a TA
  if (in_array($course, $ta_courses)){
    if (!isset($_POST['username']))
    {
      http_response_code(422);
      echo json_encode( missing_student() );
      die();
    }
    $username = $_POST['username']; // Set to grab the stats for student
  }
  $res = get_stud_log_for_course($username, $course);
}
else{
  $res = get_stud_log($username);
}

if($res == -1)
{
  $return = return_JSON_error($res);
  http_response_code(500);
}else{
  $return = array(
    "authenticated" => True,
    "student_log"   => $res
  );
  http_response_code(200);
}

echo json_encode($return);
?>
