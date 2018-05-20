<?php
session_start();

$REQUEST_URI = $_SERVER["REQUEST_URI"];
$path        = parse_url($REQUEST_URI, PHP_URL_PATH);

//REQUESTS FOR API
if( substr($path, 0, 5) === "/api/" ){
  $source = ".".$path.".php";
  if(file_exists($source)){
    header('Content-Type: application/json');
    require_once "model/auth.php";
    require_once "model/config.php";
    require_once "model/courses.php";
    require_once "model/queue.php";
    require_once "model/stats.php";
    require_once "api/errors.php";
    require_once $source;
  }else{
    //direct to swagger page
  }
  die();
}

//REQUESTS FOR PAGES
$source      = './view'.$path.'.php';
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
    header("Location: classes");
  }
}
//Not authenticated
elseif(!is_authenticated()){
  $_SESSION["redirect_url"] = $REQUEST_URI;
  header("Location: /");
}
//Admin access needed
elseif(is_admin_page($path)){
  if(is_administrator()){
    require_once $source;
  }else{
    header("Location: classes");
  }
}
//Regular pages
elseif(file_exists($source)){
  require_once $source;
}
//Nonexistant page
else{
  header("Location: classes");
}


function is_authenticated(){
  return isset($_SESSION["username"]);
}
function is_redirect(){
  return isset($_SESSION["redirect_url"]);
}
function is_administrator(){
  return isset($_SESSION["is_admin"]) && $_SESSION["is_admin"];
}
function is_open_page($path){
  return $path == "/about"       ||
         $path == "/tutorial";
}
function is_root_dir($path){
  return $path == '/';
}
function is_admin_page($path){
  return $path == "/new_class"   || 
         $path == "/edit_class";
}
?>
