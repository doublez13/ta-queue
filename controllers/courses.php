<?php
// File: courses.php
// SPDX-License-Identifier: GPL-3.0-or-later

$path_split = explode("/", $path);

switch( $_SERVER['REQUEST_METHOD'] ){
  case "GET": //Get the course list
    if ( isset($path_split[3])  ){  //Admin endpoint: Get all settings for specific course
      if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']){
        http_response_code(403);
        echo json_encode( not_authorized() );
        die();
      }
      $course = $path_split[3];
      $res    = get_course($course);
      $field  = "parameters";
      $text   = $res; 
    }
    else{                          //Get all availible courses
      $res   = get_avail_courses();
      $field = "all_courses";
      $text  = $res;
    }
    break;
  case "PUT":  //Edit a course
    if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']){
      http_response_code(403);
      echo json_encode( not_authorized() );
      die();
    }
    if ( !isset($path_split[3]) ){
      http_response_code(422);
      echo json_encode( missing_info() );
      die();
    }
    $_POST['course_name'] = $path_split[3]; //Fall through
  case "POST": //Create a course
    if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']){
      http_response_code(403);
      echo json_encode( not_authorized() );
      die();
    }
    if (!isset($_POST['course_name']) || !isset($_POST['depart_pref']) || !isset($_POST['course_num']) || 
        !isset($_POST['description']) || !isset($_POST['ldap_group'])    || !isset($_POST['professor']))
    {
      http_response_code(422);
      echo json_encode( missing_info() );
      die();
    }

    $course_name = $_POST['course_name'];
    $depart_pref = $_POST['depart_pref'];
    $course_num  = $_POST['course_num'];
    $description = $_POST['description'];
    $ldap_group  = $_POST['ldap_group'];
    $professor   = $_POST['professor'];
    if ($_POST['acc_code']){
      $acc_code    = $_POST['acc_code'];
    }else{
      $acc_code    = null;
    }
    $res   = new_course($course_name, $depart_pref, $course_num, $description, $ldap_group, $professor, $acc_code);
    $field = "success";
    $text  = "Course created/updated"; 
    break;
  case "DELETE": //Delete a course
    if ($is_admin){
      http_response_code(403);
      echo json_encode( not_authorized() );
      die();
    }
    if ( !isset($path_split[3]) ){
      http_response_code(422);
      echo json_encode( missing_info() );
      die();
    }
    $course_name = $path_split[3];
    $res   = del_course($course_name);
    $field = "success"; 
    $text  = "Course deleted";
    break;
  default:
    http_response_code(405);
    echo json_encode( invalid_method("GET, POST, DELETE") );
    die();
}

//TODO: convert get_avail_courses() to error codes, and not null on error
if ( (is_int($res) && $res) || is_null($res) ){
  $return = array(
    "authenticated" => True,
    "error" => "Unable to process course request"
  );
  http_response_code(500);
}else{
  $return = array(
    "authenticated" => True,
    $field => $text
  );
  http_response_code(200);
}
echo json_encode($return);
?>
