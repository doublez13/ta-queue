<?php
// File: delete_course.php
// SPDX-License-Identifier: GPL-3.0-or-later

require_once '../../model/auth.php';
require_once '../../model/courses.php';
require_once '../../model/queue.php';
require_once '../errors.php';

// get the session variables
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== "POST"){
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

if (!isset($_SESSION['is_admin']))
{
  http_response_code(403);
  echo json_encode( not_authorized() );
  die();
}

if (!isset($_POST['course']))
{
  http_response_code(422);
  echo json_encode( missing_info() );
  die(); 
}
$course_name = $_POST['course'];

$res = del_course($course_name);
if ($res)
{
  $return = return_JSON_error($res);
  http_response_code(500);
}else
{
  $return = array(
    "authenticated" => True,
    "success" => "Course deleted"
  );
  http_response_code(200);
}
echo json_encode($return);
?>
