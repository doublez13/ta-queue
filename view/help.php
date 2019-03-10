<!DOCTYPE html>
<html lang="en">

<head>
  <title>TA Help Queue - Tutorial</title>
  <?php
   include('./view/head.php');

   $includes = ["./src/logout.js"];
   foreach($includes as $include){
     $filemtime = filemtime($include);
     $source    = $include.'?ver='.$filemtime;
     echo "<script src='".$source."'></script>\n";
   }
   ?>
</head>

<body>
  <?php include('./view/navbar.php'); ?>
 
  <div class="jumbotron jumbotron-billboard">
    <div style="text-align: center;">
      <h1 style="color: #404040; text-shadow: 2px 2px #000000;">TA Queue Help</h1>
    </div>
  </div>

  <div class="container  padding-bottom-20">
    <div class="col-md-12">
      <h1 style="color: #cc0000">Account Creation</h1>
      <p style="font-size: 17px">
        The queue currently makes use of College of Engineering CADE accounts. Any student with a CADE account can login using their credentials and their personal information will be pulled from the upstream servers. Students without a CADE account can create one
        <a style="color: #e8002b" href="https://webhandin.eng.utah.edu/cade/create_account/index.php" target="_blank"> here.</a>
      </p>

      <h1 class="padding-top-20" style="color: #cc0000">Access Control</h1>
      <p style="font-size: 17px">
        TA privileges are granted via the course settings page. To be able to modify the settings for a course, a user must be a member of the admin group. This can be done by sending an email to opers@eng.utah.edu
      </p>

      <h1 class="padding-top-20" style="color: #cc0000">User Roles</h1>
      <p style="font-size: 17px">
        The queue currently has three major roles: Student, Teaching Assistant (TA), and Administrator.
      </p>

      <h3>Student</h3>
      <p style="font-size: 17px">
        On the Courses page, students may enroll in any course. At the instructor's discretion, some courses may require an access code to enroll.
      </p>

      <h3>Teaching Assistant</h3>
      <p style="font-size: 17px">
        Users may not enroll in a course as a student if they are already registered as a TA for that course. If the user was already enrolled as a student in a course before being added to its TA group, they are automatically unenrolled as a student and assume the TA role.
      </p>

      <h3>Administrator</h3>
      <p style="font-size: 17px">
        If the user is an Administrator, they will be presented with a page that allows them to view and modify all courses. Additionally, they will see an Admin dropdown menu in the navbar which allows them to create courses.
      </p>

      <h1 class="padding-top-20" style="color: #cc0000">Enrolling and Unenrolling in Courses</h1>
      <p style="font-size: 17px">
        Upon login, users are routed to the Courses page. Here they can enroll in any course by simply clicking Enroll. If an access code is required, students must enter that before proceeding. This page is also where a student would unenroll from a course. In this case, the user can simply click Leave for the desired course.
      </p>

      <h1 class="padding-top-20" style="color: #cc0000">Viewing Queues</h1>
      <p style="font-size: 17px">
        Queues are accessed from the Courses page. Simply click Go to view a queue.
      </p>

      <h1 class="padding-top-20" style="color: #cc0000">Queue Functionality</h1>
      <h3>Queue States</h3>
      <h4 style="color: green">Open</h4>
      <p style="font-size: 17px">
        Students can enter the queue. However, students on cool-down cannot enter. NOTE: The queue can be open, even if no TAs are on duty.
      </p>
      <h4 style="color: red">Closed</h4>
      <p style="font-size: 17px">
        Students cannot enter the queue, and no TAs are on duty.
      </p>
      <h4 style="color: blue">Frozen</h4>
      <p style="font-size: 17px">
        No new students can enter the queue. Students in the queue when it was frozen remain in the queue.
      </p>
      <h3>Announcements</h3>
      <p style="font-size: 17px">
        TAs are allowed to post announcements for their course. All announcements appear at the top of the screen, with newer announcements appearing towards the top. Announcements within the last hour appear in green. TAs can delete an announcement by clicking the X next to it.
      </p>
      <h3>Reordering</h3>
      <p style="font-size: 17px">
        TAs may wish to reorder student in the queue. In this case, TAs can use the up and down arrow buttons next to a student to shift their position in the queue. Students also have the ability to move themselves down by clicking the down arrow on their row.
      </p>
      <h3>Time Limits</h3>
      <p style="font-size: 17px">
        TAs may choose to impose time limits when helping students. In this case, TAs can set the time limit using the Time Limit Per Student feature. When a student's time has passed, that student's row turns red.
      </p>
      <h3>Cool-down Timers</h3>
      <p style="font-size: 17px">
        TAs may wish to impose cool-down timers for students. If a student gets helped, they are not allowed to reenter the queue until the specified amount of minutes has passed. TAs can set the cool-down time using the Cool-down feature.
      </p>

      <h1 class="padding-top-20" style="color: #cc0000">Creating a New Course</h1>
      <h3>Assigning and Removing TAs</h3>
      <p style="font-size: 17px">
        TAs for a course are assigned on the course creation page. The list may also be modified at any time by clicking the gear icon next to a course. 
      </p>
      <h3>Access Codes</h3>
      <p style="font-size: 17px">
        If desired, administrators may require an access code to enroll in a course. In this case, the course will appear yellow on the Courses page, and have a padlock icon next to it. Students will be required to enter a code to enroll. Access codes can include any ASCII character and be up to 16 characters long.
      </p>

      <h1 class="padding-top-20" style="color: #cc0000">Statistics</h1>
      <p style="font-size: 17px">
        Nearly every queue operation is logged. When a student enters a queue, logged data includes the student's name, course, question, location, and timestamps for entering, getting helped, and exiting the queue, as well as the TA that helped them.
        The only metrics that are currently visible via the web interface are the number of students helped per day for a particular course.
        However, <a style="color: #e8002b" href="../swagger/index.html" target="_blank">public API endpoints</a> are available that give much more detailed information on a per course, per student, and per TA basis.
      </p>
    </div>
  </div>

</body>
</html>
