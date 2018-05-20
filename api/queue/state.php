<?php
// File: state.php
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

if (!isset($_POST['state']))
{
  http_response_code(422);
  echo json_encode( missing_course() );
  die();
}

$course     = $_POST['course'];
$state      = $_POST['state'];
$ta_courses = $_SESSION["ta_courses"];

if (!in_array($course, $ta_courses))
{
  http_response_code(403);
  echo json_encode( not_authorized() );
  die();
}

switch($state){
  case "closed":
    $res = close_queue($course);
    break;
  case "frozen":
    $res = freeze_queue($course);
    break;
  case "open":
    $res = open_queue($course);
    break;
  default:
    http_response_code(422);
    echo json_encode( missing_course() );
    die();
}

if($res != $state)
{
  $return = array(
    "authenticated" => True,
    "error" => "Unable to change queue state"
  );
  http_response_code(500);
}else
{
  $return = array(
    "authenticated" => True,
    "success" => "Queue " + $state
  );
  http_response_code(200);
}
echo json_encode($return);
?>
