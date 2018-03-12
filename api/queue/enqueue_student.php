<?php
// File: enqueue_student.php
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
  echo json_encode( not_authenticated() );
  die();
}

if (!isset($_POST["course"]) || !isset($_POST["question"]) || !isset($_POST["location"]))
{
  http_response_code(422);
  $return = array(
    "authenticated" => True,
    "error" => "Missing course, question, or location"
  );
  echo json_encode($return);
  die();
}

$username = $_SESSION['username'];
$course   = $_POST['course'];
$question = $_POST['question'];
$location = $_POST['location'];

$question = filter_var($question, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
$location = filter_var($location, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);

$res = enq_stu($username, $course, $question, $location);
if($res)
{
  $return = return_JSON_error($res);
  http_response_code(500);
}else
{
  $return = array(
    "authenticated" => True,
    "success" => "Student enqueued"
  );
  http_response_code(200);
}
echo json_encode($return);
?>

