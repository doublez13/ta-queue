<?php

require_once '../auth.php';
require_once '../courses.php';
require_once '../queue.php';
require_once '../../api/errors.php';


echo enq_stu("rohith","CS 4400: Computer Systems", "dfg", "dfg");

//echo set_cooldown(3, "CS 4400: Computer Systems");
