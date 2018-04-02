<?php
// File: move_down.php
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
  echo json_encode( invalid_method("POST") );
  die();
}

if (!isset($_SESSION['username']))
{
  http_response_code(401);
  echo json_encode( not_authenticated() );
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

// If TA, set username to the posted student
if (in_array($course, $ta_courses)){
  if (!isset($_POST['student']))
  {
    http_response_code(422);
    echo json_encode( missing_student() );
    die();
  }
  $username = $_POST['student'];
}

$res = decrease_stud_priority($username, $course);
if($res)
{
  $return = return_JSON_error($res);
  http_response_code(500);
}else{
  $return = array(
    "authenticated" => True,
    "success" => "Student priority decreased"
  );
  http_response_code(200);
}

echo json_encode($return);
?>
