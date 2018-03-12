<?php

require_once '../auth.php';
require_once '../courses.php';
require_once '../queue.php';


if (touch_user("zakraise", "zane", "zak", "zane zak")){
  echo "Test 01 failed";
  die();
}
if (touch_user("blakeb", "blake", "burton", "blake burton")){
  echo "Test 02 failed";
  die();
}
if (touch_user("welling", "ryan", "welling", "ryan welling")){
  echo "Test 03 failed";
  die();
}
if (touch_user("jim", "jim", "germain", "jim germain")){
  echo "Test 04 failed";
  die();
}
if (touch_user("peter", "peter", "jenssen", "peter jenssen")){
  echo "Test 05 failed";
  die();
}
if (touch_user("erin", "erin", "parker", "erin parker")){
  echo "Test 06 failed";
  die();
}
if (touch_user("mrTA", "mr", "TA", "mrTA")){
  echo "Test 07 failed";
  die();
}
if (touch_user("mrsTA", "mrs", "TA", "mrsTA")){
  echo "Test 08 failed";
  die();
}


if (new_course("CS 1030: Foundations of CS", "CS", "1030", "The hard stuff 1!", "fake 1", "erin")){
  echo "Test 10.1 failed";
  die();
}

if (new_course("CS 1410: Introduction to Object-Oriented Programming", "CS", "1410", "The hard stuff 1!", "fake 1", "erin")){
  echo "Test 10.2 failed";
  die();
}

if (new_course("CS 2100: Descrete Scructures", "CS", "2100", "The hard stuff 1!", "fake 1", "erin")){
  echo "Test 10.3 failed";
  die();
}

if (new_course("CS 2420: Introduction to Algorithms and Data Structures", "CS", "2420", "The hard stuff 1!", "fake 1", "erin")){
  echo "Test 10.4 failed";
  die();
}

if (new_course("CS 3100: Models of Computation", "CS", "3100", "The hard stuff 1!", "fake 1", "erin")){
  echo "Test 10.5 failed";
  die();
}

if (new_course("CS 3500: Software Practice 1", "CS", "3500", "The hard stuff 1!", "fake 1", "erin")){
  echo "Test 10.6 failed";
  die();
}

if (new_course("CS 3505: Software Practice 2", "CS", "3505", "The hard stuff 1!", "fake 1", "erin")){
  echo "Test 10.7 failed";
  die();
}

if (new_course("CS 3810: Computer Organization", "CS", "3810", "The hard stuff 1!", "fake 1", "erin")){
  echo "Test 10.8 failed";
  die();
}

if (new_course("CS 4150: Algorithms", "CS", "4150", "The hard stuff 1!", "fake 1", "erin")){
  echo "Test 10.9 failed";
  die();
}

if (new_course("CS 4400: Computer Systems", "CS", "4400", "The hard stuff 1!", "fake 1", "erin")){
  echo "Test 10.10 failed";
  die();
}

if (del_course("CS 4400: Computer Systems")){
  echo "Test 10.11 failed";
  die();
}

if (new_course("CS 4400: Computer Systems", "CS", "4400", "The hard stuff 1!", "fake 1", "erin")){
  echo "Test 10.11 failed";
  die();
}




if (add_stud_course("zakraise", "CS 4400: Computer Systems")){
  echo "Test 11 failed";
  die();
}

if (add_stud_course("blakeb", "CS 4400: Computer Systems")){
  echo "Test 12 failed";
  die();
}

if (add_stud_course("welling", "CS 4400: Computer Systems")){
  echo "Test 13 failed";
  die();
}

if (add_stud_course("jim", "CS 4400: Computer Systems")){
  echo "Test 14 failed";
  die();
}

if (add_stud_course("peter", "CS 4400: Computer Systems")){
  echo "Test 15 failed";
  die();
}

if (add_stud_course("erin", "CS 4400: Computer Systems")){
  echo "Test 16 failed";
  die();
}



$zakraise_courses = get_stud_courses("zakraise");
if (sizeof($zakraise_courses) != 1){
  echo "Test 17 failed";
  die();
}

if ($zakraise_courses[0] != "CS 4400: Computer Systems"){
  echo "Test 17 failed";
  die();
}





$courses_avail = get_avail_courses();
if (sizeof($courses_avail) != 10){
  echo "Test 18 failed";
  die();
}

if (get_queue_state("CS 4400: Computer Systems") != "closed"){
  echo "Test 19 failed";
  die();
}

if (close_queue("CS 4400: Computer Systems") != "closed"){
  echo "Test 20 failed";
  die();
}
if (close_queue("CS 4400: Computer Systems") != "closed"){
  echo "Test 21 failed";
  die();
}

if (open_queue("CS 4400: Computer Systems") != "open"){
  echo "Test 22 failed";
  die();
}

if (pause_queue("CS 4400: Computer Systems") != "paused"){
  echo "Test 23 failed";
  die();
}

if (open_queue("CS 4400: Computer Systems") != "open"){
  echo "Test 24 failed";
  die();
}



if(enq_stu("zakraise", "CS 4400: Computer Systems", "What is love?", "baby dont hurt me")){
  echo "Test 35 failed";
  die();
}
if(enq_stu("zakraise", "CS 4400: Computer Systems", "What is love?", "baby dont hurt me")){
  echo "Test 36 failed";
  die();
}
if(enq_stu("blakeb", "CS 4400: Computer Systems", "What is love?", "baby dont hurt me")){
  echo "Test 37 failed";
  die();
}
if(enq_stu("welling", "CS 4400: Computer Systems", "What is love?", "baby dont hurt me")){
  echo "Test 38 failed";
  die(); 
}
if(enq_stu("erin", "CS 4400: Computer Systems", "What is love?", "baby dont hurt me")){
  echo "Test 39 failed";
  die();
}
if(enq_ta("mrTA", "CS 4400: Computer Systems")){
  echo "Test 40 failed";
  die();
}

if(get_queue_length("CS 4400: Computer Systems") != 4){
  echo "Test 41 failed";
  die();
}

if(help_next_student('mrTA', 'CS 4400: Computer Systems')){
  echo "Test 42 failed";
  die();
}

if(enq_ta("mrsTA", "CS 4400: Computer Systems")){
  echo "Test 43 failed";
  die();
}

if(help_next_student('mrsTA', 'CS 4400: Computer Systems')){
  echo "Test 44 failed";
  die();
}

if (close_queue("CS 4400: Computer Systems") != "closed"){
  echo "Test 45 failed";
  die();
}

if (open_queue("CS 4400: Computer Systems") != "open"){
  echo "Test 46 failed";
  die();
}

if(enq_ta("mrTA", "CS 4400: Computer Systems")){
  echo "Test 47 failed";
  die();
}

if(help_next_student('mrTA', 'CS 4400: Computer Systems')){
  echo "Test 48 failed";
  die();
}


#close queue
if (close_queue("CS 4400: Computer Systems") != "closed"){
  echo "Test 49 failed";
  die();
}
if(!help_next_student('mrsTA', 'CS 4400: Computer Systems')){
  echo "Test 50 failed";
  die();
}
if(!enq_ta("mrsTA", "CS 4400: Computer Systems")){
  echo "Test 51 failed";
  die();
}
if(!enq_stu("zakraise", "CS 4400: Computer Systems", "What is love?", "baby dont hurt me")){
  echo "Test 52 failed";
  die();
}
if(!enq_ta("mrTA", "CS 4400: Computer Systems")){
  echo "Test 53 failed";
  die();
}


#open queue
if (open_queue("CS 4400: Computer Systems") != "open"){
  echo "Test 54 failed";
  die();
}
if (rem_stud_course("zakraise", "CS 4400: Computer Systems")){
  echo "Test 55 failed";
  die();
}
if(!enq_stu("zakraise", "CS 4400: Computer Systems", "What is love?", "baby dont hurt me")){
  echo "Test 56 failed";
  die();
}
if (get_queue_length("CS 4400: Computer Systems") != 0){
  echo "Test 57 failed";
  die();
}



#unenroll student from course while in queue
if (open_queue("CS 4400: Computer Systems") != "open"){
  echo "Test 58 failed";
  die();
}
if (add_stud_course("zakraise", "CS 4400: Computer Systems")){
  echo "Test 59 failed";
  die();
}
if(enq_stu("zakraise", "CS 4400: Computer Systems", "What is love?", "baby dont hurt me")){
  echo "Test 60 failed";
  die();
}
if (rem_stud_course("zakraise", "CS 4400: Computer Systems")){
  echo "Test 61 failed";
  die();
}
if (get_queue_length("CS 4400: Computer Systems") != 0){
  echo "Test 62 failed";
  die();
}



#add student to queue
if (open_queue("CS 4400: Computer Systems") != "open"){
  echo "Test 63 failed";
  die();
}
if (add_stud_course("zakraise", "CS 4400: Computer Systems")){
  echo "Test 64 failed";
  die();
}
if(enq_stu("zakraise", "CS 4400: Computer Systems", "What is love?", "baby dont hurt me")){
  echo "Test 65 failed";
  die();
}
if (get_queue_length("CS 4400: Computer Systems") != 1){
  echo "Test 66 failed";
  die();
}
if(close_queue("CS 4400: Computer Systems") != "closed"){
  echo "Test 67 failed";
  die();
}
if(get_queue_length("CS 4400: Computer Systems") != 0){
  echo "Test 68 failed";
  die();
}




if (open_queue("CS 4400: Computer Systems") != "open"){
  echo "Test 69 failed";
  die();
}
if(enq_ta("mrTA", "CS 4400: Computer Systems")){
  echo "Test 70 failed";
  die();
}
if(get_ta_status("mrTA", "CS 4400: Computer Systems")  != 2){
  echo "Test 71 failed";
  die();
}
if(help_next_student("mrTA", "CS 4400: Computer Systems")){
  echo "Test 72 failed";
  die();
}
if(get_ta_status("mrTA", "CS 4400: Computer Systems")  != 2){
  echo "Test 73 failed";
  die();
}
if(enq_stu("zakraise", "CS 4400: Computer Systems", "What is love?", "baby dont hurt me")){
  echo "Test 74 failed";
  die();
}
if(get_ta_status("mrTA", "CS 4400: Computer Systems")  != 2){
  echo "Test 75 failed";
  die();
}
if(help_next_student("mrTA", "CS 4400: Computer Systems")){
  echo "Test 76 failed";
  die();
}
if(get_ta_status("mrTA", "CS 4400: Computer Systems")  != 3){
  echo "Test 77 failed";
  die();
}
if(get_ta_status("mrsTA", "CS 4400: Computer Systems")  != 1){
  echo "Test 78 failed";
  die();
}
if(enq_ta("mrsTA", "CS 4400: Computer Systems")){
  echo "Test 79 failed";
  die();
}
if(get_ta_status("mrsTA", "CS 4400: Computer Systems")  != 2){
  echo "Test 80 failed";
  die();
}
if(help_next_student("mrsTA", "CS 4400: Computer Systems")){
  echo "Test 81 failed";
  die();
}
if(get_ta_status("mrsTA", "CS 4400: Computer Systems")  != 2){
  echo "Test 82 failed";
  die();
}
if(free_ta("mrsTA", "CS 4400: Computer Systems")){
  echo "Test 83 failed";
  die();
}
if(free_ta("mrTA", "CS 4400: Computer Systems")){
  echo "Test 84 failed";
  die();
}
if(help_student("mrTA", "zakraise", "CS 4400: Computer Systems")){
  echo "Test 85 failed";
  die();
}
if(help_student("mrsTA", "zakraise", "CS 4400: Computer Systems")){
  echo "Test 86 failed";
  die();
}
if(deq_stu("zakraise", "CS 4400: Computer Systems")){
  echo "Test 87 failed";
  die();
}
if(get_ta_status("mrTA", "CS 4400: Computer Systems")  != 2){
  echo "Test 88 failed";
  die();
}
if(get_ta_status("mrsTA", "CS 4400: Computer Systems")  != 2){
  echo "Test 89 failed";
  die();
}


#invalid input handeling
if(!help_student("mrlkjlkjTA", "zakraise", "CS 4400: Computer Systems")){
  echo "Test 90 failed";
  die();
}
#if(!help_student("mrTA", "zalkjlkjkraise", "CS 4400: Computer Systems")){
#  echo "Test 91 failed: Function should return error, but returned success";
#  #die();
#}
if(!help_student("mrTA", "zakraise", "CS 4400: Cihjkhjkhjkhjkhjkhomputer Systems")){
  echo "Test 92 failed";
  die();
}



#get_queue testing
if(enq_stu("zakraise", "CS 4400: Computer Systems", "What is love?", "baby dont hurt me")){
  echo "Test 93 failed";
  die();
}
if(help_next_student("mrsTA", "CS 4400: Computer Systems")){
  echo "Test 94 failed";
  die();
}
print_r(get_queue( "CS 4400: Computer Systems" ));

echo "All Tests Completed Successfully";
?>
