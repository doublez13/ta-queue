<?php
// File: auth.php
// SPDX-License-Identifier: GPL-3.0-or-later

$path_split = explode("/", $path);
$endpoint = $path_split[2];

switch( $endpoint ){
  case "login":
    if(isset($_SESSION["redirect_url"])){
      $redirect_url = $_SESSION["redirect_url"];
    }
    $_SESSION = array();
    if(isset($redirect_url)){
      $_SESSION["redirect_url"] = $redirect_url;
    }

    if ($_SERVER['REQUEST_METHOD'] !== "POST"){
      http_response_code(405);
      echo json_encode( invalid_method("POST") );
      die();
    }

    if (!isset($_POST['username']) || !isset($_POST['password'])){
      http_response_code(422);
      echo json_encode( missing_auth() );
      die();
    }

    $username = $_POST['username'];
    $password = $_POST['password'];

    if (!auth($username, $password)){
      http_response_code(401);
      $return = array(
        "authenticated" => False,
        "error" => "Username and/or password is incorrect"
      );
      echo json_encode($return);
      die();
    }

    $return     = get_info($username);
    $is_admin   = is_admin($username);
    $ta_courses = get_ta_courses($username);

    if (is_null($return) || is_null($is_admin) || is_null($ta_courses)){
      http_response_code(500);
      echo json_encode( json_err("Unable to retrieve info from LDAP") );
      die();
    }

    $_SESSION["ta_courses"]   = $ta_courses;
    $_SESSION["username"]     = $username;
    $_SESSION["is_admin"]     = $is_admin;
    $return["authenticated"]  = True;
    $return["is_admin"]       = $is_admin;

    http_response_code(200);
    break;
  case "logout":
    //Clear session variables
    $_SESSION = array();
    
    $return = array(
      "authenticated" => False,
      "success" => "User logged out"
    );
    http_response_code(200);
    session_destroy();
}
echo json_encode($return);
?>
