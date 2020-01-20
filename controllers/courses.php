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
  case "PATCH":  //Edit a course
    if (!$is_admin){
      http_response_code(403);
      echo json_encode( forbidden() );
      die();
    }
    if (!isset($path_split[3])){
      http_response_code(422);
      echo json_encode( json_err("Missing course_id") );
      die();
    }
    $course_id = $path_split[3];

    #Looks like this is a semi-common way of accessing form data, maybe there's a better method than this.
    parse_str(file_get_contents('php://input'), $_PATCH);

    $parameters = [];
    if (isset($_PATCH['depart_pref'])){
      $parameters['depart_pref'] = trim(filter_var($_PATCH['depart_pref'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH));
    }
    if (isset($_PATCH['course_num'])){
      #TODO: Don't allow updating this field if generic course. Currently enforced by the view, but not ideal.
      $parameters['course_num'] = filter_var($_PATCH['course_num'],  FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
    }
    if (isset($_PATCH['course_name'])){
      $parameters['course_name'] = trim(filter_var($_PATCH['course_name'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH));
    }
    if (isset($_PATCH['description'])){
      $parameters['description'] = filter_var($_PATCH['description'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
    }
    if (isset($_PATCH['access_code'])){
      $parameters['access_code'] = $_PATCH['access_code'];
    }
    if (isset($_PATCH['enabled'])){
      $parameters['enabled'] = filter_var($_PATCH['enabled'], FILTER_VALIDATE_BOOLEAN);
    }

    $res   = edit_course($course_id, $parameters);
    $field = "success";
    $text  = "Course updated";
    break;
  case "POST": //Create a course
    if (!$is_admin){
      http_response_code(403);
      echo json_encode( forbidden() );
      die();
    }
    if (!isset($_POST['course_name']) || !isset($_POST['depart_pref']) || 
        !isset($_POST['enabled'])     || !isset($_POST['generic'])){
      http_response_code(422);
      echo json_encode( json_err("Missing required parameters") );
      die();
    }

    $course_name = trim(filter_var($_POST['course_name'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH));
    $depart_pref = trim(filter_var($_POST['depart_pref'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH));
    $enabled     = filter_var($_POST['enabled'],     FILTER_VALIDATE_BOOLEAN);
    $generic     = filter_var($_POST['generic'],     FILTER_VALIDATE_BOOLEAN);

    $course_num = null;
    if (!$generic){
      if (!isset($_POST['course_num'])){
        http_response_code(422);
        echo json_encode( json_err("Missing required parameters") );
        die();
      }
      $course_num = filter_var($_POST['course_num'],  FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
    }

    $description = null;
    if ($_POST['description']){
      $description = filter_var($_POST['description'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
    }

    $acc_code = null;
    if ($_POST['access_code']){
      $acc_code = $_POST['access_code'];
    }

    $res   = add_course($course_name, $depart_pref, $course_num, $description, $acc_code, $enabled, $generic);
    $field = "success";
    $text  = "Course created";
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
