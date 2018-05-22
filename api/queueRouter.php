<?php
// File: queueRouter.php
// SPDX-License-Identifier: GPL-3.0-or-later

$path_split = explode("/", $path);
if(empty($path_split[3])){
  http_response_code(422);
  echo json_encode( missing_course() );
  die();
}
$course   = $path_split[3];
$endpoint = $path_split[4];


switch( $endpoint ){

case "announcements":
  switch( $_SERVER['REQUEST_METHOD'] ){
    case "POST":
      if (!isset($_POST['announcement'])){
        http_response_code(422);
        echo json_encode( missing_announcement() );
        die();
      }
      if (!in_array($course, $ta_courses)){
        http_response_code(403);
        echo json_encode( not_authorized() );
        die();
      }
      $announcement = $_POST['announcement'];
      $announcement = filter_var($announcement, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
      $res  = add_announcement($course, $announcement, $username);
      $text = "Announcement set";
      break;
    case "DELETE":  
      if (!isset($_GET['announcement_id'])){
        http_response_code(422);
        echo json_encode( missing_announcement() );
        die();
      }
      if (!in_array($course, $ta_courses)){
        http_response_code(403);
        echo json_encode( not_authorized() );
        die();
      }
      $announcement_id = $_GET['announcement_id'];
      $res  = del_announcement($course, $announcement_id);
      $text = "Announcement deleted";
      break; 
    default:
      http_response_code(405);
      echo json_encode( invalid_method("DELETE or POST") );
      die();
  }
  break;

  
case "help_student":
  switch( $_SERVER['REQUEST_METHOD'] ){
    case "POST":
      if (!isset($_POST['student'])){
        http_response_code(422);
        echo json_encode( missing_student() );
        die();
      }
      $student    = $_POST['student'];

      if (!in_array($course, $ta_courses)){
        http_response_code(403);
        echo json_encode( not_authorized() );
        die();
      }
      $res  = help_student($username, $student, $course);
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
      if (!isset($_POST['operation'])){
        http_response_code(422);
        echo json_encode( missing_course() );
        die();
      }
      $operation = $_POST['operation']; 

      switch( $operation ){
        case "up":
          if (!isset($_POST['student'])){
            http_response_code(422);
            echo json_encode( missing_student() );
            die();
          }
          $student = $_POST['student'];
          if (!in_array($course, $ta_courses)){
            http_response_code(403);
            echo json_encode( not_authorized() );
            die();
          }
          $res = increase_stud_priority($student, $course);
          break;
        case "down":
          //If TA, set username to the posted student
          if (in_array($course, $ta_courses)){
            if (!isset($_POST['student'])){
              http_response_code(422);
              echo json_encode( missing_student() );
              die();
            }
            $username = $_POST['student'];
          }
          $res = decrease_stud_priority($username, $course);
          break;
        default:
          http_response_code(422);
          echo json_encode( missing_course() );
          die();
      }
      $text = "Student position switched";
      break;

    default:
      http_response_code(405);
      echo json_encode( invalid_method("POST") );
      die();
  }
  break;


case "queue":
  switch( $_SERVER['REQUEST_METHOD'] ){
    case "GET":
      //For now, these return the same information.
      //Later, we may want the TAs to see more,
      //or the students to see less.
      if (in_array($course, $ta_courses)){ //TA
        $ret = get_queue($course);
      }elseif (in_array($course, get_stud_courses($username))){ //Student
        $ret = get_queue($course);
      }else{ //Not in course
        http_response_code(403);
        echo json_encode( not_authorized() );
        die();
      }
      break;
    default:
      http_response_code(405);
      echo json_encode( invalid_method("GET") );
      die();
  }//TODO: Not the cleanest way to do it, but it works
  $text = "Queue fetched";
  $res = $ret;
  if(!is_int($ret)){
    $res = 0; 
  }

case "settings":
  switch( $_SERVER['REQUEST_METHOD'] ){
    case "POST":
      if (!isset($_POST['setting'])){
        http_response_code(422);
        echo json_encode( missing_course() );
        die();
      }

      $setting = $_POST['setting'];
    
      if (!in_array($course, $ta_courses)){
        http_response_code(403);
        echo json_encode( not_authorized() );
        die();
      }
      switch( $setting ){
        case "time_lim":
          if (!isset($_POST['time_lim']) || !is_numeric($_POST['time_lim']) || $_POST['time_lim'] < 0 ){
            http_response_code(422);
            echo json_encode( missing_time('time_lim') );
            die();
          }
          $res = set_time_lim( $_POST['time_lim'], $course);
          break;
        case "cooldown":
          if (!isset($_POST['time_lim']) || !is_numeric($_POST['time_lim']) || $_POST['time_lim'] < 0 ){
            http_response_code(422);
            echo json_encode( missing_time('time_lim') );
            die();
          }
          $res = set_cooldown( $_POST['time_lim'], $course);     
          break;
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
      if (!isset($_POST['state'])){
        http_response_code(422);
        echo json_encode( missing_course() );
        die();
      }

      $state      = $_POST['state'];

      if (!in_array($course, $ta_courses)){
        http_response_code(403);
        echo json_encode( not_authorized() );
        die();
      }
    
      switch($state){
        case "closed":
          $res = close_queue($course);
          break;
        case "frozen":
          $res = freeze_queue($course);
          break;
        case "open":
          $res = open_queue($course);
          break;
        default:
          http_response_code(422);
          echo json_encode( missing_course() );
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
  switch($_SERVER['REQUEST_METHOD']){
    case "POST":
      if (  !isset($_POST["question"]) || !isset($_POST["location"]) || !$_POST["question"] || !$_POST["location"]){
        http_response_code(422);
        $return = array(
          "authenticated" => True,
          "error" => "Missing course, question, or location"
        );
        echo json_encode($return);
        die();
      }
      $question = filter_var($_POST['question'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
      $location = filter_var($_POST['location'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
      $res  = enq_stu($username, $course, $question, $location);
      $text = "Student enqueued";
      break;

    case "DELETE":
      //Since this enpoint is used for students to
      //remove themselves, and TAs to remove students,
      //we check if the request came from a TA
      if (in_array($course, $ta_courses)){
        if (!isset($_GET['student'])){
          http_response_code(422);
          echo json_encode( missing_student() );
          die();
        }
        $username = $_GET['student']; // Set to dequeue student
      }else{//Request came from student
        if (isset($_GET['student']) && $_GET['student'] != $username){
          http_response_code(422);
          echo json_encode( not_authorized() );
          die();
        }
      }
      $res  = deq_stu($username, $course);
      $text = "Student dequeued";
      break;

    default:
      http_response_code(405);
      echo json_encode( invalid_method("POST") );
      die();
  }
  break;


case "ta":
  switch( $_SERVER['REQUEST_METHOD'] ){
    case "POST":
      if (!in_array($course, $ta_courses)){
        http_response_code(403);
        echo json_encode( not_authorized() );
        die();
      }
      $res  = enq_ta($username, $course);
      $text = "TA on duty";
      break;
 
    case "DELETE":
      $res  = deq_ta($username, $course);
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
    "success" => $text
  );
  if(isset($ret)){//Any additional info
    $return = array_merge($return, $ret);
  }
  http_response_code(200);
}
echo json_encode($return);
?>
