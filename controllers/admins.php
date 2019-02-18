<?php
/**
 * SPDX-License-Identifier: GPL-3.0-or-later
 * Copyright (c) 2019 Zane Zakraisek
 *               2018 Blake Burton
 *
 * Controller for admin group
 * 
 */

$path_split = explode("/", $path);

switch( $_SERVER['REQUEST_METHOD'] ){
  case "GET": //Retrieve a list of the admins
    if (!is_admin($username)){
      http_response_code(403);
      echo json_encode( forbidden() );
      die();
    }
    $res = get_admins();
    $field = "admin";
    $text  = $res;
    break;
  case "POST": //Add a user to the admins group
    if (!is_admin($username)){
      http_response_code(403);
      echo json_encode( forbidden() );
      die();
    }
    if (!isset($path_split[3])){
      http_response_code(422);
      echo json_encode( json_err("Missing username to add") );
      die();
    }
    $admin_username = $path_split[3];
    $res = grant_admin($admin_username);
    $field = "success";
    $text  = "Added to admin group";
    break;
  case "DELETE": //Remove a user from the admins group
    if (!is_admin($username)){
      http_response_code(403);
      echo json_encode( forbidden() );
      die();
    }
    if (!isset($path_split[3])){
      http_response_code(422);
      echo json_encode( json_err("Missing username to remove") );
      die();
    }
    $admin_username = $path_split[3];
    $res = revoke_admin($admin_username);
    $field = "success";
    $text  = "Removed from admin group";
    break;
  default:
    http_response_code(405);
    echo json_encode( invalid_method("GET, POST, DELETE") );
    die();
}

//TODO: convert methods to error codes, and not null on error
if ( is_int($res) && $res ){
  $return = array(
    "authenticated" => True,
    "username"      => $username,
    "error" => "Unable to update admins group"
  );
  http_response_code(500);
}elseif(is_null($res)){
  $return = array(
    "authenticated" => True,
    "username"      => $username,
    "error" => "Generic SQL error"
  );
  http_response_code(500);
}else{
  $return = array(
    "authenticated" => True,
    "username"      => $username,
    $field => $text
  );
  http_response_code(200);
}
echo json_encode($return);
?>
