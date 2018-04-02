<?php
// File: add_course.php
// SPDX-License-Identifier: GPL-3.0-or-later

require_once '../../model/courses.php';
require_once '../errors.php';

session_start(); 
header('Content-Type: application/json');

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

$acc_code = NULL;
if (isset($_POST['acc_code']))
{
  $acc_code = $_POST['acc_code'];
}

$username = $_SESSION['username'];
$course   = $_POST['course'];

$res = add_stud_course($username, $course, $acc_code);
if ($res < 0)
{
  $return = return_JSON_error($res);
  http_response_code(500);
}else
{
  $return = array(
    "authenticated" => True,
    "success" => "Student Course Added Successfully"
  );
  http_response_code(200);
}

echo json_encode($return);
?>
