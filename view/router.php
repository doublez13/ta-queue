<?php
session_start();

$REQUEST_URI = $_SERVER["REQUEST_URI"];
$is_admin    = $_SESSION["is_admin"];

if(strpos($REQUEST_URI, 'index.php')){
  if($_SESSION["username"]){
    header("Location: ./classes.php");
    die();
  }
}
else{ //Authenticated Page
  if (!$_SESSION["username"]){
    header("Location: ../index.php");
    die();
  }

  if(strpos($REQUEST_URI, 'new_class.php')){
    if(!$is_admin){
      header("Location: ./classes.php");
      die();
    }
  }  
  if(strpos($REQUEST_URI, 'edit_class.php')){
    if(!$is_admin){
      header("Location: ./classes.php");
      die();
    }
  }
}

?>
