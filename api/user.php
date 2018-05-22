<?php
//File: user.php
// SPDX-License-Identifier: GPL-3.0-or-later

$path_split = explode("/", $path);
if(empty($path_split[3])){
  http_response_code(422);
  echo json_encode( missing_course() );
  die();
}
$req_username = $path_split[3];
if($req_username != $username && !$is_admin){
  http_response_code(403);
  echo json_encode( not_authorized() );
  die();
}
$endpoint     = "info";
if(isset($path_split[4])){
  $endpoint = $path_split[4];
}

switch($endpoint){

  case "courses":
    switch($_SERVER['REQUEST_METHOD']){
      case "GET":
        $stud_courses = get_stud_courses($req_username);
        if (is_null($stud_courses)){
          $return = my_course_list_error();
          http_response_code(500);
        }else{
          $return = array(
            "authenticated" => True,
            "student_courses" => $stud_courses,
            "ta_courses"      => $ta_courses
          );
          http_response_code(200);
        }
        break;

      case "POST":
        if (!isset($_POST['course'])){
          http_response_code(422);
          echo json_encode( missing_course() );
          die();
        }
        $course   = $_POST['course'];
        $acc_code = NULL;
        if (isset($_POST['acc_code'])){
          $acc_code = $_POST['acc_code'];
        }
        $res = add_stud_course($req_username, $course, $acc_code);
        if ($res < 0){
          $return = return_JSON_error($res);
          http_response_code(500);
        }else{
          $return = array(
            "authenticated" => True,
            "success" => "Student Course Added Successfully"
          );
          http_response_code(200);
        }
        break;

      case "DELETE":
        if ( !isset($path_split[5]) ){
          http_response_code(422);
          echo json_encode( missing_course() );
          die();
        }
        $course = $path_split[5];
        $res = rem_stud_course($req_username, $course);
        if ($res < 0){
          $return = return_JSON_error($res);
          http_response_code(500);
        }
        else{
          $return = array(
            "authenticated" => True,
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
          $return = ldap_issue();
          http_response_code(500);
        }else{
          $return = array(
            "authenticated" => True,
            "student_info" => $stud_info
          );
          http_response_code(200);
        }
        break;
      default:
        http_response_code(405);
        echo json_encode( invalid_method("iGET") );
        die();
    }
}

echo json_encode($return);
?>
