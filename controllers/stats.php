<?php
/**
 * SPDX-License-Identifier: GPL-3.0-or-later
 * Copyright (c) 2018 Zane Zakraisek
 *               2018 Blake Burton
 *
 * Controller for stats endpoints
 * 
 */


$path_split = explode("/", $path);
if(empty($path_split[3])){
  http_response_code(422);
  echo json_encode( json_err("Missing endpoint (course, student, ta)") );
  die();
}
if(empty($path_split[4])){
  http_response_code(422);
  echo json_encode( json_err("Missing entity ( which course, student, or ta)") );
  die();
}
$endpoint = $path_split[3]; //course, student, ta

switch($endpoint){

  case "student":
    switch($_SERVER['REQUEST_METHOD']){
      case "GET":
        $student = $path_split[4];
        if(!in_array($course, $ta_courses) && $student != $username){ //Not a TA
          http_response_code(403);
          echo json_encode( forbidden() );
          die();
        }
        $return = student_stats($student);
        break;
      default:
        http_response_code(405);
        echo json_encode( invalid_method("GET") );
        die();
    }
    break;
  case "course":
    switch( $_SERVER['REQUEST_METHOD'] ){
      case "GET":
        $course = $path_split[4];
        $return = course_stats($course); //Course stats are public
        break;
      default:
        http_response_code(405);
        echo json_encode( invalid_method("GET") );
        die();
    }
    break;
  case "ta":
    switch( $_SERVER['REQUEST_METHOD'] ){
      case "GET":
        $ta = $path_split[4];
        if(!$is_admin && $ta != $username){ //Not an admin
          http_response_code(403);
          echo json_encode( forbidden() );
          die();
        }

        break;
      default:
      http_response_code(405);
        echo json_encode( invalid_method("GET") );
        die();
      }    
  default:
    http_response_code(422);
    echo json_encode( json_err("Invalid endpoint (course, student, ta)") );
    die();

}
echo json_encode($return);

function course_stats($course){
  $dates = check_date();

  $stats = get_course_stats($course, $dates[0], $dates[1]);
  $usage = get_course_usage_by_day($course, $dates[0], $dates[1]);

  if($stats < 0 || $usage < 0){
    $return = return_JSON_error($stats < 0 ? $stats : $usage);
    http_response_code(500);
  }else{
    $return = array(
      "authenticated" => True,
      "stats"         => $stats,
      "usage"         => $usage
       );
      http_response_code(200);
  }
  return $return;
}

function user_stats(){
  http_response_code(422);
  echo json_encode( json_err("Not implemented yet") );
  die();
}

function ta_stats(){
  http_response_code(422);
  echo json_encode( json_err("Not implemented yet") );
  die();
}

function check_date(){
  $date_format = "/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/"; // yyyy-mm-dd
  $format_err  = false;
  $start_date  = null;
  $end_date    = null;
  if(isset($_GET['start_date'])){
    $start_date = $_GET['start_date'];
    $format_err = !((bool)preg_match($date_format, $start_date));
  }
  if(isset($_GET['end_date'])){
    $end_date   = $_GET['end_date'];
    $format_err = !((bool)preg_match($date_format, $end_date)) || $format_err;
  }
  // Make sure start_date was sent if end_date was sent and ensure correct formats
  if ((isset($end_date) && !isset($start_date)) || $format_err){
    http_response_code(422); // 400 FOR BAD DATE?
    echo json_encode( json_err("Missing or bad date (required: yyyy-mm-dd)") );
    die();
  }
  return [$start_date, $end_date];
}
?>
