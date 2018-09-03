<?php
/**
 * SPDX-License-Identifier: GPL-3.0-or-later
 * Copyright (c) 2018 Zane Zakraisek
 *
 * Routes all API requests to the appropriate controller.
 * Returns the requested page from the view directory.
 *
 */

session_start();

$REQUEST_URI = $_SERVER["REQUEST_URI"];
$path        = urldecode(parse_url($REQUEST_URI, PHP_URL_PATH));

//////// REQUESTS FOR API ////////
if( substr($path, 0, 5) === "/api/" ){
  header('Content-Type: application/json');

  require_once "model/config.php";
  require_once "model/auth.php";
  require_once "model/courses.php";
  require_once "model/queue.php";
  require_once "model/stats.php";
  require_once "controllers/errors.php";

  if( is_login_endpoint($path) ){
    require_once './controllers/auth.php';
    die();
  }

  if (!is_authenticated()){    //Trying to access protected endpoint, not authenticated.
    invalid_auth_reply();
    die();
  }

  $username     = $_SESSION['username'];
  $is_admin     = is_admin($username);         //TODO: Adds overhead to every request
  $user_courses = get_user_courses($username); //TODO: Adds overhead to every request
  $ta_courses   = $user_courses["ta"];
  $stud_courses = $user_courses["student"];

  $controller = explode("/", $path)[2];
  switch($controller){
    case "user": 
      require_once './controllers/user.php';
      break;
    case "queue":
      require_once './controllers/queue.php';
      break;
    case "courses":
      require_once './controllers/courses.php';
      break;
    case "stats":
      require_once './controllers/stats.php';
      break;
    default:
      header('Location: /swagger');
  }
}
//////// REQUESTS FOR PAGES ////////
else{
  $source = './view'.$path.'.php';
  //Open access pages
  if(is_open_page($path)){
    require_once $source;
  }
  elseif(is_root_dir($path)){
    if(!is_authenticated()){
      require_once './view/index.php';
    }elseif(is_redirect()){
      $url = $_SESSION["redirect_url"];
      unset($_SESSION["redirect_url"]);
      header("Location: $url");
    }else{
      header("Location: courses");
    }
  }
  //Not authenticated
  elseif(!is_authenticated()){
    $_SESSION["redirect_url"] = $REQUEST_URI;
    header("Location: /");
  }
  //Admin access needed
  elseif(is_admin_page($path)){
    require_once "model/auth.php";
    $username  = $_SESSION['username'];
    if(is_admin($username)){
      require_once $source;
    }else{
      header("Location: courses");
    }
  }
  //Regular pages
  elseif(file_exists($source)){
    require_once $source;
  }
  //Nonexistant page
  else{
    header("Location: courses");
  }
}

function is_authenticated(){
  return isset($_SESSION["username"]);
}
function is_redirect(){
  return isset($_SESSION["redirect_url"]);
}
function is_open_page($path){
  return $path == "/about"       ||
         $path == "/help";
}
function is_login_endpoint($path){
  return $path == '/api/login'   || 
         $path == '/api/logout';
}
function is_root_dir($path){
  return $path == '/';
}
function is_admin_page($path){
  return $path == "/new_course"   || 
         $path == "/edit_course";
}
function basic_auth_provided(){
  return isset($_SERVER['PHP_AUTH_USER']) && 
         isset($_SERVER['PHP_AUTH_PW']);
}

function invalid_auth_reply(){
  http_response_code(401);
  $return = array(
    "authenticated" => False,
    "error" => "Not Authenticated"
  );
  echo json_encode($return);
}
?>
