<?php
/**
 * SPDX-License-Identifier: GPL-3.0-or-later
 * Copyright (c) 2018 Zane Zakraisek
 *               2018 Blake Burton
 *
 * Controller for user endpoints
 * 
 */

$path_split = explode("/", $path);
if(empty($path_split[3])){
  http_response_code(422);
  echo json_encode( json_err("Missing username") );
  die();
}

$is_admin = is_admin($username);
if(is_null($is_admin)){
  $return = array(
    "authenticated" => True,
    "username"      => $username,
    "error" => "Internal Server Error"
  );
  http_response_code(500);
  echo json_encode($return);
  die();
}

$req_username = strtolower($path_split[3]); //requested username converted to lower case
if($req_username != $username && !$is_admin){
  http_response_code(403);
  echo json_encode( forbidden() );
  die();
}
$endpoint = "info";
if(isset($path_split[4])){
  $endpoint = $path_split[4];
}

switch($endpoint){

  case "courses":
    switch($_SERVER['REQUEST_METHOD']){
      case "GET":
        $user_courses = get_user_courses($username);
        if (is_null($user_courses)){
          $return = json_err("Unable to fetch courses");
          http_response_code(500);
        }else{
          $return = array(
            "authenticated"      => True,
            "username"           => $username,
            "ta_courses"         => $user_courses['ta'],
            "student_courses"    => $user_courses['student'],
            "instructor_courses" => $user_courses['instructor'],
          );
          http_response_code(200);
        }
        break;

      case "POST":
        if ( !isset($path_split[5]) ){
          http_response_code(422);
          echo json_encode( json_err("No course_id specified") );
          die();
        }
        if ( !isset($path_split[6]) ){
          http_response_code(422);
          echo json_encode( json_err("No role specified") );
          die();
        }

        $course_id = $path_split[5];
        $role      = $path_split[6];
        $acc_code  = NULL;
        if (isset($_POST['acc_code'])){
          $acc_code = $_POST['acc_code'];
        }

        if($role == "student"){
          $res = add_stud_course($req_username, $course_id, $acc_code);
        }elseif($role == "ta"){
          if (!$is_admin){ //Must be an admin to add user as TA
            http_response_code(403);
            echo json_encode( forbidden() );
            die();
          }
          $res = add_ta_course($req_username, $course_id);
        }
        elseif($role == "instructor"){
          if (!$is_admin){ //Must be an admin to add user as instructor
            http_response_code(403);
            echo json_encode( forbidden() );
            die();
          }
          $res = add_instructor_course($req_username, $course_id);
        }
        else{
          http_response_code(422);
          echo json_encode( json_err("Invalid Role: student, ta, or instructor are valid") );
          die();
        }
        
        if ($res < 0){
          $return = return_JSON_error($res);
          http_response_code(500);
        }else{
          $return = array(
            "authenticated" => True,
            "username"      => $username,
            "success" => "Course Added Successfully"
          );
          http_response_code(200);
        }
        break;

      case "DELETE":
        if ( !isset($path_split[5]) ){
          http_response_code(422);
          echo json_encode( json_err("No course_id specified") );
          die();
        }
        if ( !isset($path_split[6]) ){
          http_response_code(422);
          echo json_encode( json_err("No role specified") );
          die();
        }

        $course_id = $path_split[5];
        $role      = $path_split[6];

        if($role == "student"){
          $res = rem_stud_course($req_username, $course_id);
        }elseif($role == "ta"){
          $res = rem_ta_course($req_username, $course_id);
        }elseif($role == "instructor"){
          $res = rem_instructor_course($req_username, $course_id);
        }
        else{
          http_response_code(422);
          echo json_encode( json_err("Invalid Role: student, ta, or instructor are valid") );
          die();
        }

        if ($res < 0){
          $return = return_JSON_error($res);
          http_response_code(500);
        }
        else{
          $return = array(
            "authenticated" => True,
            "username"      => $username,
            "success" => "Student Course Removed Successfully"
          );
          http_response_code(200);
        }
        break;

      default:
        http_response_code(405);
        echo json_encode( invalid_method("GET, POST, DELETE") );
        die();
    }
    break;
  case "info":
    switch( $_SERVER['REQUEST_METHOD'] ){
      case "GET":
        $stud_info = get_info($req_username);
        if (is_null($stud_info)){
          $return = json_err("Unable to retrieve info from SQL or LDAP");
          http_response_code(500);
        }else{
          $return = array(
            "authenticated" => True,
            "username"      => $username,
            "student_info" => $stud_info
          );
          http_response_code(200);
        }
        break;
      case "DELETE":
        if (!$is_admin){
          http_response_code(403);
          echo json_encode( forbidden() );
          die();
        }
        $res = del_user($req_username);
        if ($res < 0){
          $return = return_JSON_error($res);
          http_response_code(500);
        }else{
          $return = array(
            "authenticated" => True,
            "username"      => $username,
            "success" => "User deleted"
          );
          http_response_code(200);
        }
        break;
      default:
        http_response_code(405);
        echo json_encode( invalid_method("GET, DELETE") );
        die();
    }
}

echo json_encode($return);
?>
