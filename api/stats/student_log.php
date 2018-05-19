<?php
// File: student_log.php
// SPDX-License-Identifier: GPL-3.0-or-later

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

// Optional date range parameters
$date_format = "/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/"; // yyyy-mm-dd
$format_err  = false;
$start_date  = null;
$end_date    = null;
if(isset($_POST['start_date'])){
  $start_date = $_POST['start_date'];
  $format_err = !((bool)preg_match($date_format, $start_date));
}
if(isset($_POST['end_date'])){
  $end_date   = $_POST['end_date'];
  $format_err = !((bool)preg_match($date_format, $end_date)) || $format_err;
}

// Make sure start_date was sent if end_date was sent and ensure correct formats
if ((isset($end_date) && !isset($start_date)) || $format_err)
{
  http_response_code(422); // 400 FOR BAD DATE?
  echo json_encode( missing_date() );
  die();
}

$username   = $_SESSION['username'];
$ta_courses = $_SESSION["ta_courses"];

//If a course is specified, get the log
//for the student in that course. If not,
//get the log for all the student's courses.
if (isset($_POST['course']))
{
  $course = $_POST['course'];
  //Since this endpoint is used for students and TAs,
  //we check if the request came from a TA.
  if (in_array($course, $ta_courses)){
    if (!isset($_POST['student']))
    {
      http_response_code(422); // 400 FOR BAD DATE?
      echo json_encode( missing_student() );
      die();
    }
    $username = $_POST['student']; // Set to grab the stats for student
  }
  $res = get_stud_log_for_course($username, $course, $start_date, $end_date);
}
else{
  $res = get_stud_log($username, $start_date, $end_date);
}

if($res < 0)
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
