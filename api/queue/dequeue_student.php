<?php
// File: dequeue_student.php
// SPDX-License-Identifier: GPL-3.0-or-later

if ($_SERVER['REQUEST_METHOD'] !== "POST")
{
  http_response_code(405);
  echo json_encode( invalid_method("POST") );
  die();
}

if (!isset($_POST['course']))
{
  http_response_code(422);
  echo json_encode( missing_course() );
  die();
}

$course     = $_POST['course'];

//Since this enpoint is used for students to
//remove themselves, and TAs to remove students,
//we check if the request came from a TA
if (in_array($course, $ta_courses)){
  if (!isset($_POST['student']))
  {
    http_response_code(422);
    echo json_encode( missing_student() );
    die();
  }
  $username = $_POST['student']; // Set to dequeue another student
}else{//Came from student
  if (isset($_POST['student']) && $_POST['student'] != $username)
  {
    http_response_code(422);
    echo json_encode( not_authorized() );
    die();
  }
}

$res = deq_stu($username, $course);
if($res)
{
  $return = return_JSON_error($res);
  http_response_code(500);
}else{
  $return = array(
    "authenticated" => True,  
    "success" => "Student dequeued"
  );
  http_response_code(200);
}

echo json_encode($return);
?>
