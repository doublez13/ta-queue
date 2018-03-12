<?php
// File: freeze.php
// SPDX-License-Identifier: GPL-3.0-or-later

require_once '../../model/auth.php';
require_once '../../model/courses.php';
require_once '../../model/queue.php';
require_once '../errors.php';

session_start();
header('Content-Type: application/json');

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

if (!in_array($course, $ta_courses))
{
  http_response_code(403);
  echo json_encode( not_authorized() );
  die();
}

$res = freeze_queue($course);
if ($res != "frozen")
{
  $return = array(
    "authenticated" => True,
    "error" => "Unable to freeze queue"
  );
  http_response_code(500);
}else
{
  $return = array(
    "authenticated" => True,
    "success" => "Queue frozen"
  );
  http_response_code(200);
}
echo json_encode($return);
?>
