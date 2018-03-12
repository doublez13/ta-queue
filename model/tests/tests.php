<?php

require_once '../auth.php';
require_once '../courses.php';
require_once '../queue.php';

#test01
if(!auth(BIND_USER, BIND_PASSWD)){
  echo "Test01 failed";
  die();
}

#test02
if(!_ldap_connect()){
  echo "Test02 failed";
  die();
}

#test03
if(get_info("zakraise") == NULL){
  echo "Test 03 failed";
  die();
}

#test04
if(get_info("doesntExist") != NULL){
  echo "Test 04 failed";
  die();
}

$sam = dn_to_sam("CN=zakraise,OU=Domain Admin OU,DC=users,DC=coe,DC=utah,DC=edu");
if($sam == NULL || $sam =! 'zakraise'){
  echo "Test 05 failed";
  die();
}


if (touch_user("zakraise", "zane", "zak", "zane zak")){
  echo "Test 09 failed";
  die();
}
if (touch_user("zakraise2", "zane2", "zak2", "zane2 zak2")){
  echo "Test 09 failed";
  die();
}
if (touch_user("zakraise3", "zane3", "zak3", "zane3 zak3")){
  echo "Test 09 failed";
  die();
}
if (touch_user("mrTA", "zane3", "zak3", "zane3 zak3")){
  echo "Test 09 failed";
  die();
}

if (!is_array(get_avail_courses())){
  echo "Test 9.5 failed";
  die();
}

if (new_course("Computer Systems", "CS", "4400", "The hard stuff!", "fake", "zakraise")){
  echo "Test 10 failed";
  die();
}

if (!new_course("Computer Systems", "CS", "4401", "The hard stuff 1!", "fake 1", "zakraise")){
  echo "Test 10.5 failed";
  die();
}

if (sizeof(get_avail_courses()) != 1){
  echo "Test 10.6 failed";
  die();
}


if (add_stud_course("zakraise", "Computer Systems")){
  echo "Test 11 failed";
  die();
}

if (add_stud_course("zakraise", "Computer Systems")){
  echo "Test 12 failed";
  die();
}

for ($i = 0; $i<10; $i++){
  if (rem_stud_course("zakraise", "Computer Systems")){
    echo "Test 13 failed";
    die();
  }
}

for ($i = 0; $i<10; $i++){
  if (add_stud_course("zakraise", "Computer Systems")){
    echo "Test 14 failed";
    die();
  }
}


if (new_course("Algorithms", "CS", "4150", "Algorithms", "fake", "zakraise")){
  echo "Test 15 failed";
  die();
}
if (add_stud_course("zakraise", "Algorithms")){
  echo "Test 15 failed";
  die();
}

if (sizeof(get_stud_courses("zakraise")) != 2){
  echo "Test 16 failed";
  die();
}



if (new_course("CS 9999", "CS", "9999", "course desc", "cs9999", "zakraise")){
  echo "Test 17 failed";
  die();
}

$TAs = get_tas("CS 9999");
if($TAs == NULL){
 echo "Test 18 failed";
 die();
}
if($TAs[1] != "zakraise"){
  echo "Test 19 failed";
  die();
}

if(!in_array("CS 9999", get_ta_courses('zakraise'))){
  echo "Test 20 failed";
  die();
}

if(get_ta_courses('fake-user') != NULL){
  echo "Test 21 failed";
  die();
}


$courses_avail = get_avail_courses();
if (sizeof($courses_avail) != 3){
  echo "Test 22 failed";
  die();
}

if (get_queue_state("CS 9999") != "closed"){
  echo "Test 23 failed";
  die();
}

if (close_queue("CS 9999") != "closed"){
  echo "Test 24 failed";
  die();
}

if (close_queue("CS 9999") != "closed"){
  echo "Test 25 failed";
  die();
}

if (open_queue("CS 9999") != "open"){
  echo "Test 26 failed";
  die();
}

if (pause_queue("CS 9999") != "paused"){
  echo "Test 27 failed";
  die();
}

if (open_queue("CS 9999") != "open"){
  echo "Test 28 failed";
  die();
}

if (close_queue("CS 9999") != "closed"){
  echo "Test 29 failed";
  die();
}

if (close_queue("CS 9999") != "closed"){
  echo "Test 30 failed";
  die();
}

if (get_queue_state("CS 9999") != "closed"){
  echo "Test 31 failed";
  die();
}


echo "All Tests Completed Successfully";
?>
