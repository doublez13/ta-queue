<?php
/**
 * SPDX-License-Identifier: GPL-3.0-or-later
 * Copyright (c) 2018 Zane Zakraisek
 *
 * Routes all requests to the appropriate controller or view.
 * Returns the requested page from the view directory.
 *
 */

session_start();

$REQUEST_URI = $_SERVER['REQUEST_URI'];
$path        = urldecode(parse_url($REQUEST_URI, PHP_URL_PATH));

require_once './model/config.php';
require_once './model/auth.php';

//////// REQUESTS FOR API ////////
if( substr($path, 0, 5) === '/api/' ){
  header('Content-Type: application/json');

  require_once './model/courses.php';
  require_once './model/queue.php';
  require_once './controllers/errors.php';

  if(is_login_endpoint($path)){
    require_once './controllers/auth.php';
    die();
  }

  //Authentication required beyond this point
  if(!is_authenticated()){
    invalid_auth_reply();
    die();
  }
  $username = $_SESSION['username']; //NOTE: This is always lowercase

  $controller = explode("/", $path)[2];
  switch($controller){
    case "queue":
      require_once './controllers/queue.php';
      break;
    case "user":
      require_once './controllers/user.php';
      break;
    case "courses":
      require_once './controllers/courses.php';
      break;
    case "stats":
      require_once './model/stats.php';
      require_once './controllers/stats.php';
      break;
    case "admins":
      require_once './controllers/admins.php';
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
  if(!is_authenticated()){
    if(AUTH == 'CAS'){
      require_once $phpcas_path . '/CAS.php';

      phpCAS::client(CAS_VERSION_3_0, $cas_host, $cas_port, $cas_context);
      phpCAS::setCasServerCACert($cas_server_ca_cert_path);
      phpCAS::forceAuthentication();

      $username  = strtolower(phpCAS::getUser());
      if(is_null(get_info($username))){
        echo "User authenticated but information could not be obtained";
        die();
      }

      $_SESSION["username"] = $username;
      header('Location: /');
    }elseif(AUTH == 'LDAP'){
      require_once './view/index.php';
    }
    else{
      echo "Invalid server auth config: Must be CAS or LDAP";
    }
  }
  //Authentication required beyond this point
  //Admin Page
  elseif(is_admin_page($path)){
    $username  = $_SESSION['username'];
    if(is_admin($username)){
      require_once $source;
    }else{
      header('Location: /courses');
    }
  }
  //Regular pages
  elseif(file_exists($source)){
    require_once $source;
  }
  //Nonexistant page
  else{
    header('Location: /courses');
  }
}

function is_authenticated(){
  return isset($_SESSION['username']);
}
function is_open_page($path){
  return $path == '/about'       ||
         $path == '/help';
}
function is_login_endpoint($path){
  return $path == '/api/login'   ||
         $path == '/api/logout';
}
function is_admin_page($path){
  return $path == '/new_course'   ||
         $path == '/edit_course';
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
