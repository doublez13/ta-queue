<?php
// File: set_cooldown.php
// SPDX-License-Identifier: GPL-3.0-or-later

require_once '../../model/auth.php';
require_once '../../model/courses.php';
require_once '../../model/queue.php';
require_once '../errors.php';

// get the session variables
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

if (!isset($_POST['cooldown_time']) || !is_numeric($_POST['cooldown_time']) || $_POST['cooldown_time'] < 0 )
{
  http_response_code(422);
  echo json_encode( missing_time("cooldown_time") );
  die();
}

$username   = $_SESSION['username'];
$course     = $_POST['course'];
$cooldown   = $_POST['cooldown_time'];
$ta_courses = $_SESSION["ta_courses"];

if (!in_array($course, $ta_courses))
{
  http_response_code(403);
  echo json_encode( not_authorized() );
  die();
}

$res = set_cooldown($cooldown, $course);
if ($res)
{
  $return = return_JSON_error($res);
  http_response_code(500);
}else
{
  $return = array(
    "authenticated" => True,
    "success" => "Cool-down time set"
  );
  http_response_code(200);
}
echo json_encode($return);
?>
