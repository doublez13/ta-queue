<?php
/**
 * SPDX-License-Identifier: GPL-3.0-or-later
 * Copyright (c) 2018 Zane Zakraisek
 *               2018 Blake Burton
 *
 * Controller for queue endpoints
 * 
 */

$path_split = explode("/", $path);
if(empty($path_split[3])){
  http_response_code(422);
  echo json_encode( json_err("No course_id specified") );
  die();
}
$course_id   = $path_split[3];
$endpoint    = "queue";
if(isset($path_split[4])){
  $endpoint = $path_split[4];
}

switch( $endpoint ){
  case "announcements":
    switch( $_SERVER['REQUEST_METHOD'] ){
      case "POST":
        if (!in_array($course_id, get_user_courses2($username)['ta'])){
          http_response_code(403);
          echo json_encode( forbidden() );
          die();
        }
        if (!isset($_POST['announcement'])){
          http_response_code(422);
          echo json_encode( json_err("No announcement specified") );
          die();
        }
        $announcement = filter_var($_POST['announcement'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
        $res  = add_announcement($course_id, $announcement, $username);
        $text = "Announcement posted";
        break;
      case "DELETE":
        if (!in_array($course_id, get_user_courses2($username)['ta'])){
          http_response_code(403);
          echo json_encode( forbidden() );
          die();
        }
        if( !isset($path_split[5]) ){ //announcement_id in url
          http_response_code(422);
          echo json_encode( json_err("No announcement specified") );
          die();
        }
        $announcement_id = $path_split[5];
        $res  = del_announcement($course_id, $announcement_id);
        $text = "Announcement deleted";
        break; 
      default:
        http_response_code(405);
        echo json_encode( invalid_method("DELETE or POST") );
        die();
    }
    break;

  
  case "queue":
    switch( $_SERVER['REQUEST_METHOD'] ){
      case "GET":
        //For now, these return the same information.
        //Later, we may want the TAs to see more,
        //or the students to see less.
        $user_courses = get_user_courses2($username);
        $ta_courses   = $user_courses['ta'];
        $stud_courses = $user_courses['student'];
        if(in_array($course_id, $ta_courses)){         //TA
          $role = "ta" ;
        }elseif(in_array($course_id, $stud_courses)){  //Student
          $role = "student";
        }elseif(is_admin($username)){                  //Admin
          $role = "admin";
        }else{                                         //Not in course
          http_response_code(403);
          echo json_encode( forbidden() );
          die();
        }
        $ret = get_queue($course_id, $role);
        break;
      default:
        http_response_code(405);
        echo json_encode( invalid_method("GET") );
        die();
    }//TODO: Not the cleanest way to do it, but it works for now
    $text = "Queue fetched";
    $res = $ret;
    if(!is_int($ret)){
      $res = 0;
    }
    break;


  case "settings":
    switch( $_SERVER['REQUEST_METHOD'] ){
      case "POST":
        if (!in_array($course_id, get_user_courses2($username)['ta'])){
          http_response_code(403);
          echo json_encode( forbidden() );
          die();
        }
        if (!isset($_POST['setting'])){
          http_response_code(422);
          echo json_encode( json_err("Missing setting") );
          die();
        }
        $setting = $_POST['setting'];
        switch( $setting ){ //Right now we just support time_lim, cooldown and quest_public
          case "time_lim":
            if (!isset($_POST['time_lim']) || !is_numeric($_POST['time_lim']) || $_POST['time_lim'] < 0 ){
              http_response_code(422);
              echo json_encode( json_err("Missing or bad time limit") );
              die();
            }
            $res = set_time_lim( $_POST['time_lim'], $course_id);
            break;
          case "cooldown":
            if (!isset($_POST['time_lim']) || !is_numeric($_POST['time_lim']) || $_POST['time_lim'] < 0 ){
              http_response_code(422);
              echo json_encode( json_err("Missing or bad time limit") );
              die();
            }
            $res = set_cooldown( $_POST['time_lim'], $course_id);     
            break;
          case "quest_public":
            if (!isset($_POST['quest_public'])){
              http_response_code(422);
              echo json_encode( json_err("Missing or bad boolean") );
              die();
            }
            $quest_public = filter_var( $_POST['quest_public'], FILTER_VALIDATE_BOOLEAN);
            $res = set_quest_vis($quest_public, $course_id);
            break;
          default:
            http_response_code(422);
            echo json_encode( json_err("Invalid setting (time_lim or cooldown)") );
            die();
        }
        $text = "Setting changed";
        break;
      default:
        http_response_code(405);
        echo json_encode( invalid_method("POST") );
        die();
    }
    break;


  case "state":
    switch( $_SERVER['REQUEST_METHOD'] ){
      case "POST":
        if (!in_array($course_id, get_user_courses2($username)['ta'])){
          http_response_code(403);
          echo json_encode( forbidden() );
          die();
        }
        if (!isset($_POST['state'])){
          http_response_code(422);
          echo json_encode( json_err("Mising state") );
          die();
        }
        $state = $_POST['state'];
        switch($state){
          case "closed":
            $res = close_queue($course_id);
            break;
          case "frozen":
            $res = freeze_queue($course_id);
            break;
          case "open":
            $res = open_queue($course_id);
            break;
          default:
            http_response_code(422);
            echo json_encode( json_err("Invalid state") );
            die();
        }
        $text = "Queue state changed";
        break;
      default:
        http_response_code(405);
        echo json_encode( invalid_method("POST") );
        die();
    }
    break;


  case "student":
    if (!isset($path_split[5])){//  /api/queue/course_id/student
      switch($_SERVER['REQUEST_METHOD']){
        case "POST":
          if( !isset($_POST["question"]) || !isset($_POST["location"]) || !$_POST["question"] || !$_POST["location"]){
            http_response_code(422);
            $return = array(
              "authenticated" => True,
              "username"      => $username,
              "error" => "Missing course_id, question, or location"
            );
            echo json_encode($return);
            die();
          }
          $question = filter_var($_POST['question'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
          $location = filter_var($_POST['location'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
          $res  = enq_stu($username, $course_id, $question, $location);
          $text = "Student enqueued";
          break;
        default:
          http_response_code(405);
          echo json_encode( invalid_method("POST or DELETE") );
          die();
      }
    }else{ //  /api/queue/course_id/student/username
      $student = strtolower($path_split[5]); //username of student converted to lower case
      if (!isset($path_split[6])){
        switch($_SERVER['REQUEST_METHOD']){
          case "DELETE":
            if ($student != $username && !in_array($course_id, get_user_courses2($username)['ta'])){ //Not a TA
              http_response_code(403);
              echo json_encode( forbidden() );
              die();
            }
            $res  = deq_stu($student, $course_id);
            $text = "Student dequeued";
            break;
          default:
            http_response_code(405);
            echo json_encode( invalid_method("POST or DELETE") );
            die();
        }
      }else{//  /api/queue/course_id/student/username/operation
        $operation = $path_split[6];
        switch($operation){
          case "help":
            switch( $_SERVER['REQUEST_METHOD'] ){
              case "POST":
                if (!in_array($course_id, get_user_courses2($username)['ta'])){
                  http_response_code(403);
                  echo json_encode( forbidden() );
                  die();
                }
                $res  = help_student($username, $student, $course_id);
                $text = "TA status changed";
                break;
              default:
                http_response_code(405);
                echo json_encode( invalid_method("POST") );
                die();
            }
            break;
          case "position":
            switch( $_SERVER['REQUEST_METHOD'] ){
              case "POST":
                if( !isset($_POST['direction'])){
                  http_response_code(422);
                  echo json_encode( json_err("Missing direction (up or down)") );
                  die();
                }
                $direction = $_POST['direction'];
                switch( $direction ){
                  case "up":
                    if (!in_array($course_id, get_user_courses2($username)['ta'])){//Only need to be a TA to move up, not down
                      http_response_code(403);
                      echo json_encode( forbidden() );
                      die();
                    } 
                    $res = increase_stud_priority($student, $course_id);
                    break;
                  case "down":
                    if(!in_array($course_id, get_user_courses2($username)['ta']) && $student != $username ){
                      http_response_code(403);
                      echo json_encode( forbidden() );
                      die();
                    }
                    $res = decrease_stud_priority($student, $course_id);
                    break;
                  default:
                    http_response_code(422);
                    echo json_encode( json_err("Invalid Operation (up or down)") );
                    die();
                }
                $text = "Student position switched";
                break;
              default:
                http_response_code(405);
                echo json_encode( invalid_method("POST") );
                die();
            }
        }
      }
    }
    break;


  case "ta":
    switch( $_SERVER['REQUEST_METHOD'] ){
      case "POST":
        if (!in_array($course_id, get_user_courses2($username)['ta'])){
          http_response_code(403);
          echo json_encode( forbidden() );
          die();
        }
        $res  = enq_ta($username, $course_id);
        $text = "TA on duty";
        break;
      case "DELETE":
        $res  = deq_ta($username, $course_id);
        $text = "TA off duty";
        break;
      default:
        http_response_code(405);
        echo json_encode( invalid_method("POST or DELETE") );
        die();
    }
    break;
}//ENDPOINT SWITCH


if($res){
  $return = return_JSON_error($res);
  http_response_code(500);
}else{
  $return = array(
    "authenticated" => True,
    "username"      => $username,
    "success" => $text
  );
  if(isset($ret)){//Any additional info
    $return = array_merge($return, $ret);
  }
  http_response_code(200);
}
echo json_encode($return);
?>
