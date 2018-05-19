<?php
// File: course_stats.php
// SPDX-License-Identifier: GPL-3.0-or-later

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
$date_format = "/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/"; // yyyy-mm-dd
$format_err  = false;
$start_date  = null;
$end_date    = null;
if(isset($_POST['start_date'])){
  $start_date = $_POST['start_date'];
  $format_err = !((bool)preg_match($date_format, $start_date));
}
if(isset($_POST['end_date'])){
  $end_date   = $_POST['end_date'];
  $format_err = !((bool)preg_match($date_format, $end_date)) || $format_err;
}

// Make sure start_date was sent if end_date was sent and ensure correct formats
if ((isset($end_date) && !isset($start_date)) || $format_err)
{
  http_response_code(422); // 400 FOR BAD DATE?
  echo json_encode( missing_date() );
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

$stats = get_course_stats($course, $start_date, $end_date);
$usage = get_course_usage_by_day($course, $start_date, $end_date);

if($stats < 0 || $usage < 0)
{
  $return = return_JSON_error($stats < 0 ? $stats : $usage);
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
