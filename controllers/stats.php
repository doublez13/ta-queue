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
        $student = strtolower($path_split[4]); //username of student converted to lower case
        if(!in_array($course_id, get_user_courses2($username)['ta']) && $student != $username){ //Not a TA
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
        $course_id = $path_split[4];
        $return    = course_stats($course_id); //Course stats are public
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
        $course_id = $path_split[4];
        $return = ta_stats($course_id);
        break;
      default:
      http_response_code(405);
        echo json_encode( invalid_method("GET") );
        die();
    }
    break;    
  default:
    http_response_code(422);
    echo json_encode( json_err("Invalid endpoint (course, student, ta)") );
    die();

}
echo json_encode($return);

function course_stats($course_id){
  $dates = check_date();

  $stats = get_course_stats($course_id, $dates[0], $dates[1]);
  $usage = get_course_usage_by_day($course_id, $dates[0], $dates[1]);

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

function ta_stats($course_id){
  $dates = check_date();

  $usage             = get_ta_proportions($course_id, $dates[0], $dates[1]);
  $ta_avg_help_time  = get_ta_avg_help_time($course_id, $dates[0], $dates[1]);

  if($usage < 0 || $ta_avg_help_time < 0){
    $return = return_JSON_error($usage);
    http_response_code(500);
  }else{
    $return = array(
      "authenticated"    => True,
      "ta_proportions"   => $usage,
      "avg_ta_help_time" => $ta_avg_help_time
    );
    http_response_code(200);
  }
  return $return;
}

function check_date(){
  $date_format = "/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/"; // yyyy-mm-dd
  $format_err  = false;
  //If no start date was set, set it to the beginning of the semester
  //Jan 1st     - May 9th
  //May 10th    - August 19th
  //August 20st - December 31st
  date_default_timezone_set('America/Denver');
  $curr_year  = date('Y');
  $curr_month = date('m');
  $curr_day   = date('d'); 
  if($curr_month < 5 || ($curr_month == 5 && $curr_day <= 9)){
    $start_date = $curr_year.'-01-01';
    $end_date   = $curr_year.'-05-09';
  }elseif($curr_month < 8 || ($curr_month == 8 && $curr_day <= 19)){
    $start_date = $curr_year.'-05-10';
    $end_date   = $curr_year.'-08-19';
  }else{
    $start_date = $curr_year.'-08-20';
    $end_date   = $curr_year.'-12-31';
  }
  if(isset($_GET['start_date']) && isset($_GET['end_date'])){
    $start_date = $_GET['start_date'];
    $end_date   = $_GET['end_date'];
    $format_err = !((bool)preg_match($date_format, $start_date));
    $format_err = !((bool)preg_match($date_format, $end_date)) || $format_err;
    if ($format_err){
      http_response_code(422); // 400 FOR BAD DATE?
      echo json_encode( json_err("bad date (required: yyyy-mm-dd)") );
      die();
    }
  }
  return [$start_date, $end_date];
}
?>
