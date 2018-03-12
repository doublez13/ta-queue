<?php
// File: get_queue.php
// SPDX-License-Identifier: GPL-3.0-or-later

require_once '../../model/auth.php';
require_once '../../model/courses.php';
require_once '../../model/queue.php';
require_once '../errors.php';

// get the session variables
session_start();
header('Content-type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== "POST")
{
  http_response_code(405);
  echo json_encode( invalid_method() );
  die();
}

if (!isset($_SESSION['username']))
{
  http_response_code(401);
  $return = array("authenticated" => False);
  echo json_encode($return);
  die();
}

if (!isset($_POST['course']))
{
  http_response_code(422);
  echo json_encode( missing_course() );
  die();
}

$username   = $_SESSION['username'];
$course     = $_POST['course'];
$ta_courses = $_SESSION["ta_courses"];

//For now, these return the same information.
//Later, we may want the TAs to see more,
//or the students to see less.
if (in_array($course, $ta_courses)) //TA
{
  $return = get_queue($course);
}
elseif (in_array($course, get_stud_courses($username))) //Student
{
  $return = get_queue($course);
}else //Not in course
{
  http_response_code(403);
  $return = array(
    "authenticated" => True,
    "error" => "Not enrolled in course"
  );
}

if($return < 0)
{
  $return = return_JSON_error($res);
  http_response_code(500);
}else
{
  $return["authenticated"] = True;
  http_response_code(200);  
}
echo json_encode($return);
?>
