<?php
/**
 * SPDX-License-Identifier: GPL-3.0-or-later
 * Copyright (c) 2018 Zane Zakraisek
 *               2018 Blake Burton
 *
 * Controller for course endpoints
 * 
 */

$path_split = explode("/", $path);

$is_admin = is_admin($username);
if(is_null($is_admin)){
  $return = array(
    "authenticated" => True,
    "username"      => $username,
    "error" => "Generic SQL error"
  );
  http_response_code(500);
  echo json_encode($return);
  die();
}

switch( $_SERVER['REQUEST_METHOD'] ){
  case "GET": //Get the course list
    if ( isset($path_split[3])  ){  //Admin endpoint: Get information on specific course
      if (!$is_admin){
        http_response_code(403);
        echo json_encode( forbidden() );
        die();
      }
      $course_id = $path_split[3];

      if ( isset($path_split[4]) ){//Get list of enrolled (students, TAs, instructors)
        switch($path_split[4]){
          case "instructors":
            $res   = get_instructors($course_id);
            $field = "instructors";
            $text  = $res;
            break;
          case "ta":
            $res   = get_tas($course_id);  
            $field = "TAs";
            $text  = $res;
            break;
          case "students":
            $res   = get_students($course_id); 
            $field = "students";
            $text  = $res;
            break;
          default:
            http_response_code(422);
            echo json_encode( json_err("Invalid Endpoint") );
            die();
        }
      }else{ //Get course settings
        $res    = get_course($course_id);
        $field  = "parameters";
        $text   = $res; 
      }
    }else{                          //Get all availible courses
      if ($is_admin){
        $res = get_all_courses();
      }else{
        $res = get_enabled_courses();
      }
      $field = "all_courses";
      $text  = $res;
    }
    break;
  case "PUT":  //Edit a course
    if (!$is_admin){
      http_response_code(403);
      echo json_encode( forbidden() );
      die();
    }
    if (!isset($path_split[3])){
      http_response_code(422);
      echo json_encode( json_err("Missing course_name") );
      die();
    }
    $_POST['course_name'] = $path_split[3]; //Fall through
  case "POST": //Create a course
    if (!$is_admin){
      http_response_code(403);
      echo json_encode( forbidden() );
      die();
    }
    if (!isset($_POST['course_name']) || !isset($_POST['depart_pref']) || 
        !isset($_POST['course_num'])  || !isset($_POST['enabled'])){
      http_response_code(422);
      echo json_encode( json_err("Missing required parameters") );
      die();
    }

    $course_name = trim(filter_var($_POST['course_name'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH));
    $depart_pref = trim(filter_var($_POST['depart_pref'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH));
    $course_num  = filter_var($_POST['course_num'],  FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
    $enabled     = filter_var($_POST['enabled'],     FILTER_VALIDATE_BOOLEAN);

    if ($_POST['description']){
      $description = filter_var($_POST['description'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
    }else{
      $description = null;
    }
    
    if ($_POST['access_code']){
      $acc_code = $_POST['access_code'];
    }else{
      $acc_code = null;
    }

    //new_course is used both for creating and modifying courses
    $res   = new_course($course_name, $depart_pref, $course_num, $description, $acc_code, $enabled);
    $field = "success";
    $text  = "Course created/updated"; 
    break;
  case "DELETE": //Delete a course
    if (!$is_admin){
      http_response_code(403);
      echo json_encode( forbidden() );
      die();
    }
    if ( !isset($path_split[3]) ){
      http_response_code(422);
      echo json_encode( json_err("Missing course_id") );
      die();
    }
    $course_id = $path_split[3];
    $res   = del_course($course_id);
    $field = "success"; 
    $text  = "Course deleted";
    break;
  default:
    http_response_code(405);
    echo json_encode( invalid_method("GET, POST, DELETE") );
    die();
}

//TODO: convert methods to error codes, and not null on error
if ( is_int($res) && $res ){
  $return = return_JSON_error($res);
  http_response_code(500);
}elseif(is_null($res)){
  $return = array(
    "authenticated" => True,
    "username"      => $username,
    "admin"         => $is_admin,
    "error" => "Generic SQL error"
  );
  http_response_code(500);
}else{
  $return = array(
    "authenticated" => True,
    "username"      => $username,
    "admin"         => $is_admin,
    $field => $text
  );
  http_response_code(200);
}
echo json_encode($return);
?>
