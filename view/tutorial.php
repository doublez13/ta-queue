<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
        "http://www.w3.org/TR/html4/loose.dtd">
<html>

<head>
  <title>TA Help Queue - Tutorial</title>
  <?php include('./view/head.html'); ?>
</head>

<body>
  <?php include('./view/navbar.php'); ?>
 
    <div class="jumbotron jumbotron-billboard" style="margin-top: -15px; opacity: 0.75;">
        <div align="center" style="margin-top:  -40px; margin-bottom: -20px">
            <h1 style="color: #404040; text-shadow: 2px 2px #000000;">TA Help Queue Tutorial</h1>
        </div>
    </div>

    <div class="container  padding-bottom-20">
        <div class="col-md-12">
            <h1 style="color: #cc0000">Account Creation</h1>
            <p style="font-size: 17px">
                The queue currently makes use of College of Engineering CADE accounts. Any student with a CADE account can login using their credentials and their personal information will be pulled from the servers. Students without a CADE account can create one
                <a style="color: #e8002b" href="https://webhandin.eng.utah.edu/cade/create_account/index.php" target="_blank"> here.</a>
                <span style="font-weight: bold">Logging out does not remove students from queues or take TAs off duty</span>.
            </p>

            <h1 class="padding-top-20" style="color: #cc0000">Access Control</h1>
            <p style="font-size: 17px">
                TA and administrative privileges are granted via CADE Active Directory LDAP groups. TAs must be members of the corresponding course group to receive TA privileges. Professors must be members of the group <span style="font-weight: bold">queue-admin-GROUP</span> to receive administrative privileges. (see "Assigning and Removing TAs" below for more details)
            </p>

            <h1 class="padding-top-20" style="color: #cc0000">User Roles</h1>
            <p style="font-size: 17px">
                The queue currently has three major roles: Student, Teaching Assistant (TA), and Administrator.
            </p>

            <h3>Student</h3>
            <p style="font-size: 17px">
                On the Courses page, students may enroll in any course. At the professor's discretion, some courses may require an access code to enroll.
            </p>

            <h3>Teaching Assistant</h3>
            <p style="font-size: 17px">
                Users may not enroll in a course if they belong to its TA group. If the user was already enrolled as a student in a course before being added to its TA group, they are automatically unenrolled as a student and assume their TA role.
            </p>

            <h3>Administrator</h3>
            <p style="font-size: 17px">
                If the user is an admin, a dropdown menu appears in the navbar which allows them to create courses.
            </p>

            <h1 class="padding-top-20" style="color: #cc0000">Enrolling and Unenrolling in Courses</h1>
            <p style="font-size: 17px">
                Upon login, users are routed to the Courses page after login. Here they can enroll in any course by simply clicking Enroll. If an access code is required, students must enter this before proceeding. This page is also where a student would unenroll from a course. In this case, the user can simply click Leave for the desired course.
            </p>

            <h1 class="padding-top-20" style="color: #cc0000">Viewing Queues</h1>
            <p style="font-size: 17px">
                Queues are accessed from the Courses page. Simply click GoTo to view a queue. Naturally, students and TAs have different functionalities available on a queue page.
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
                TAs are allowed to post announcements for their course. All announcements appear at the top of the screen, with newer announcements appearing towards the top. Announcements within the last fifteen minutes appear in green. TAs can delete an announcement by clicking the X next to it.
            </p>
            <h3>Reordering</h3>
            <p style="font-size: 17px">
                TAs may wish to reorder the queue. In this case, TAs can use the up and down arrow buttons next to a student to shift their position in the queue. Students also have the ability to move themselves down by clicking the down arrow on their row.
            </p>
            <h3>Time Limits</h3>
            <p style="font-size: 17px">
                TAs may choose to impose time limits when helping students. In this case, TAs can set the time limit using the Time Limit Per Student feature. When a student's time has passed, the row corresponding to that student turns red.
            </p>
            <h3>Cool-down Timers</h3>
            <p style="font-size: 17px">
                TAs may wish to impose cool-down timers for students. This means that if a student gets helped, they are not allowed to enter the queue again until the specified amount of minutes has passed. TAs can set the cool-down time using the Cool-down feature.
            </p>

            <h1 class="padding-top-20" style="color: #cc0000">Creating a New Course</h1>
            <h3>Assigning and Removing TAs</h3>
            <p style="font-size: 17px">
                Administrators specify the TA LDAP group for a particular course. This group should exist before creating the course. After the group is specified, adding and removing TAs is then done completely via an LDAP interface. The most common way administrators update groups is via the
                <a style="color: #e8002b" href="https://webhandin.eng.utah.edu/groupmodify" target="_blank"> web interface </a>
                or by sending an email to <span style="font-style: italic">opers@eng.utah.edu</span>. To request an LDAP group for a new course, send an email to <span style="font-style: italic">opers@eng.utah.edu</span>.
            </p>
            <h3>Access Codes</h3>
            <p style="font-size: 17px">
                If desired, administrators may require an access code to enroll in a course. In this case, the course will appear yellow on the Courses page, and have a padlock icon next to it. Students will be required to enter the code to enroll. Access codes can include any ASCII character and be up to 16 characters long.
            </p>

            <h1 class="padding-top-20" style="color: #cc0000">Statistics</h1>
            <p style="font-size: 17px">
                Nearly every queue operation is logged. When a student enters a queue, logged data includes the student's name, course, question, and timestamps for entering, getting helped, and exiting the queue, as well as the TA that helped them.
                The only metrics that are currently visible via the web interface are the number of students helped per day for a particular course.
                However, <a style="color: #e8002b" href="../swagger/index.html" target="_blank">public API endpoints</a> are available that give much more detailed information on a per course, per student, and per TA basis.
            </p>
        </div>
     </div>

</body>
</html>
