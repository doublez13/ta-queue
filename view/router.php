<?php
session_start();

$REQUEST_URI = $_SERVER["REQUEST_URI"];

if(strpos($REQUEST_URI, 'index.php') || $REQUEST_URI == '/'){
  if(isset($_SESSION["username"])){
    header("Location: ./view/classes.php");
  }
}
else{ //Authenticated Page

  if (!isset($_SESSION["username"])){
    header("Location: ../index.php");
    die();
  }

  //Admin access needed
  if(strpos($REQUEST_URI, 'new_class.php') || strpos($REQUEST_URI, 'edit_class.php')){
    $is_admin = isset($_SESSION["is_admin"]) && $_SESSION["is_admin"];
    if(!$is_admin){
      header("Location: ./classes.php");
      die();
    }
  }
 
}

?>
