<?php
// File: login.php
// SPDX-License-Identifier: GPL-3.0-or-later

require_once '../model/auth.php';
require_once '../model/courses.php';
require_once './errors.php';

session_start();
$_SESSION = array();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== "POST"){
  http_response_code(405);
  echo json_encode( invalid_method() );
  die();
}

if(!isset($_POST['username']) || !isset($_POST['password']))
{
  http_response_code(422);
  echo json_encode( invalid_auth() );
  die();
}

$username = $_POST['username'];
$password = $_POST['password'];

if(!auth($username, $password))
{
  $return = array(
    "authenticated" => False,
    "error" => "Username and/or password is incorrect"
  );
  echo json_encode($return);
  die();
}

$info       = get_info($username);
$is_admin   = is_admin($username);
$ta_courses = get_ta_courses($username);

if(is_null($info) || is_null($is_admin) || is_null($ta_courses))
{
  echo json_encode( ldap_issue() );
  die();
}

$_SESSION["ta_courses"] = $ta_courses;
$_SESSION["username"]   = $username;
$_SESSION["is_admin"]   = $is_admin;
$info["authenticated"]  = TRUE;

echo json_encode($info);
?>
