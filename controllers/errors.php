<?php
/**
 * SPDX-License-Identifier: GPL-3.0-or-later
 * Copyright (c) 2018 Zane Zakraisek
 *
 * Error codes
 * 
 */

//Error codes returned by the model
function return_JSON_error($err_code){
  global $username;
  $err_codes = array(
    -1 => "Generic SQL error",
    -2 => "Course does not exist",
    -3 => "Queue closed for this course",
    -4 => "TA not on duty",
    -5 => "User already registered as TA for course",
    -6 => "Invalid access code provided",
    -7 => "User in cool-down period",
    -8 => "User does not exist",
    -9 => "Course is disabled"
  );
  return array(
    "authenticated" => True,
    "username"      => $username,
    "error" => $err_codes[$err_code]
  );
}

function invalid_method($allowed_method){
  global $username;
  $ret =  array(
    "authenticated" => False,
    "error"         => "Only $allowed_method is allowed"
  );
  if(isset($username)){ //Possible to get this error without being authed
    $ret["authenticated"] = True;
    $ret["username"]      = $username;
  }
  return $ret;
}

function missing_auth(){
  return  array(
    "authenticated" => False,
    "error"         => "No username and/or password specified"
  );
}

function forbidden(){ //403
  global $username;
  $ret = array(
    "authenticated" => False,
    "error"         => "Forbidden"
  );
  if(isset($username)){ //Possible to get this error without being authed
    $ret["authenticated"] = True;
    $ret["username"]      = $username;
  }
  return $ret;
}

//Generic error
function json_err($err){
  global $username;
  $ret = array(
    "authenticated" => False,
    "error"         => $err
  );
  if(isset($username)){ //Possible to get this error without being authed
    $ret["authenticated"] = True;
    $ret["username"]      = $username;
  }
  return $ret;
}
?>
