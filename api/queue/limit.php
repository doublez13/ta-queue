<?php
// File: set_limit.php
// SPDX-License-Identifier: GPL-3.0-or-later

if ($_SERVER['REQUEST_METHOD'] !== "POST")
{
  http_response_code(405);
  echo json_encode( invalid_method("POST") );
  die();
}

if (!isset($_POST['course']))
{
  http_response_code(422);
  echo json_encode( missing_course() );
  die();
}

if (!isset($_POST['time_lim']) || !is_numeric($_POST['time_lim']) || $_POST['time_lim'] < 0 )
{
  http_response_code(422);
  echo json_encode( missing_time("time_lim") );
  die();
}

$course     = $_POST['course'];
$time_lim   = $_POST['time_lim'];

if (!in_array($course, $ta_courses))
{
  http_response_code(403);
  echo json_encode( not_authorized() );
  die();
}

$res = set_time_lim($time_lim, $course);
if ($res)
{
  $return = return_JSON_error($res);
  http_response_code(500);
}else
{
  $return = array(
    "authenticated" => True,
    "success" => "Time limit set"
  );
  http_response_code(200);
}
echo json_encode($return);
?>
