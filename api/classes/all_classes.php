<?php
// File: all_classes.php
// SPDX-License-Identifier: GPL-3.0-or-later

require_once '../../model/courses.php';
require_once '../errors.php';

// get the session variables
session_start();
header('Content-type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== "GET"){
  http_response_code(405);
  echo json_encode( invalid_method() );
  die();
}

// return authenticated False if user isn't authenticated
if (!isset($_SESSION["username"]))
{
  http_response_code(401);
  echo json_encode( not_authenticated() );
  die();
}

$username = $_SESSION['username'];

$all_courses = get_avail_courses();
if (is_null($all_courses))
{
  $return = course_list_error();
}else
{
  $return = array(
    "authenticated" => True,
    "all_courses" => $all_courses
  );
}

echo json_encode($return);
?>
