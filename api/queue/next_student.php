<?php
// File: next_student.php
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

$course     = $_POST['course'];

if (!in_array($course, $ta_courses))
{
  http_response_code(403);
  echo json_encode( not_authorized() );
  die();
}

$res = help_next_student($username, $course);
if($res)
{
  $return = array(
    "authenticated" => True,
    "error" => "Unable to change TA status"
  );
  http_response_code(500);
}else
{
  $return = array(
    "authenticated" => True,
    "success" => "TA status changed"
  );
  http_response_code(200);
}
echo json_encode($return);
?>
