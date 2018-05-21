<?php
// File: enqueue_student.php
// SPDX-License-Identifier: GPL-3.0-or-later

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
    $res = deq_stu($username, $course);
    break;

  default:
    http_response_code(405);
    echo json_encode( invalid_method("POST") );
    die();
}

if($res){
  $return = return_JSON_error($res);
  http_response_code(500);
}else{
  $return = array(
    "authenticated" => True,
    "success" => $text
  );
  http_response_code(200);
}
echo json_encode($return);
?>
