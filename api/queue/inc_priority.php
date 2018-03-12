<?php
// File: inc_priority.php
// SPDX-License-Identifier: GPL-3.0-or-later

require_once '../../model/auth.php';
require_once '../../model/courses.php';
require_once '../../model/queue.php';
require_once '../errors.php';

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
  echo json_encode( not_authenticated() );
  die();
}

if (!isset($_POST['course']))
{
  http_response_code(422);
  echo json_encode( missing_course() );
  die();
}

if (!isset($_POST['student']))
{
  http_response_code(422);
  echo json_encode( missing_student() );
  die();
}

$username   = $_SESSION['username'];
$course     = $_POST['course'];
$student    = $_POST['student'];
$ta_courses = $_SESSION["ta_courses"];

if (!in_array($course, $ta_courses))
{
  http_response_code(403);
  echo json_encode( not_authorized() );
  die();
}

$res = increase_stud_priority($student, $course);
if($res < 0)
{
  $return = return_JSON_error($res);
  http_response_code(500);
}else
{
  $return = array(
    "authenticated" => True,
    "success" => "Student priority increased"
  );
  http_response_code(200);
}
echo json_encode($return);
?>
