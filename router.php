<?php
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

  if ( !is_authenticated() ){    //Trying to access protected endpoint
    if( basic_auth_provided() ){ //They provided Basic authentication
      $username = $_SERVER['PHP_AUTH_USER'];
      $password = $_SERVER['PHP_AUTH_PW'];
      if(!authenticate($username, $password)){
        invalid_auth_reply();
        die();
      } //Successful auth falls through
    }else{ //Deny Access, but present opportinity to provide Basic authentication
      access_denied_reply();
      die();
    }
  }

  $username   = $_SESSION['username'];
  $ta_courses = $_SESSION["ta_courses"];
  $is_admin   = $_SESSION["is_admin"];

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
function credentials_provided(){
  return isset($_POST['username']) && isset($_POST['password']);
}
function is_open_page($path){
  return $path == "/about"       ||
         $path == "/tutorial";
}
function is_login_endpoint($path){
  return $path == '/api/login'   || 
         $path == '/api/logout';
}
function is_root_dir($path){
  return $path == '/';
}
function is_admin_page($path){
  return $path == "/new_class"   || 
         $path == "/edit_class";
}
function basic_auth_provided(){
  return isset($_SERVER['PHP_AUTH_USER']) && 
         isset($_SERVER['PHP_AUTH_PW']);
}

function invalid_auth_reply(){
  http_response_code(401);
  $return = array(
    "authenticated" => False,
    "error" => "Username and/or password is incorrect"
  );
  echo json_encode($return);
}
function access_denied_reply(){
  header('WWW-Authenticate: Basic realm="TA Queue"');
  http_response_code(401);
  $return = array("authenticated" => False);
  echo json_encode($return);
}

//Only used for BASIC auth.
//login endpoint does not use this.
function authenticate($username, $password){
  require_once "model/auth.php";
  require_once "model/courses.php";
  if(auth($username, $password)){
    $info       = get_info($username);
    $is_admin   = is_admin($username);
    $ta_courses = get_ta_courses($username);

    if (is_null($info) || is_null($is_admin) || is_null($ta_courses)){
      return false;
    }

    $_SESSION["ta_courses"]   = $ta_courses;
    $_SESSION["username"]     = $username;
    $_SESSION["is_admin"]     = $is_admin;
      return true;
  }
  return false;
}

?>
