<?php
// File: course_stats.php
// SPDX-License-Identifier: GPL-3.0-or-later

require_once '../../model/stats.php';
require_once '../errors.php';

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

// UNCOMMENT TO RESTRICT ENDPOINT TO TAS
//$ta_courses = $_SESSION["ta_courses"];
//
//if (!in_array($course, $ta_courses))
//{
//  http_response_code(403);
//  echo json_encode( not_authorized() );
//  die();
//}

$stats = get_course_stats($course); 
$usage = get_course_usage_by_day($course); 

if($stats < 0 || $usage < 0)
{
  $return = return_JSON_error(-1);
  http_response_code(500);
}else{
  $return = array(
    "authenticated" => True,
    "stats"         => $stats,
    "usage"         => $usage
  );
  http_response_code(200);
}

echo json_encode($return);
?>
