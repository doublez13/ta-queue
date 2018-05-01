<?php
// File: ta_log.php
// SPDX-License-Identifier: GPL-3.0-or-later

require_once '../../model/stats.php';
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

// Optional date range parameters
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];
$date_format = "/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/"; // yyyy-mm-dd

if (!is_null($start_date))
{
  $start_date = filter_var($start_date, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH); // NECESSARY?
  $bad_start_date = !((bool)preg_match($date_format, $start_date)); // MOVE FORMAT CHECKS TO MODEL?
}
if (!is_null($end_date))
{
  $end_date = filter_var($end_date, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
  $bad_end_date = !((bool)preg_match($date_format, $end_date));
}

// Make sure start_date was sent if end_date was sent and ensure correct formats
if ((is_null($start_date) && !is_null($end_date)) || $bad_start_date || $bad_end_date)
{
  http_response_code(422); // 400 FOR BAD DATE?
  echo json_encode( missing_date() );
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

$res = get_ta_log_for_course($username, $course, $start_date, $end_date);
if($res < 0)
{
  $return = return_JSON_error($res);
  http_response_code(500);
}else{
  $return = array(
    "authenticated" => True,
    "ta_log"   => $res
  );
  http_response_code(200);
}

echo json_encode($return);
?>
