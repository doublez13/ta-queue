<!DOCTYPE html>
<html lang="en">

<head>
  <title>TA Help Queue - Courses</title>
  <?php include('./view/head.html'); ?>
  <script src="./src/courses.js"></script>
</head>

<body>
  <?php include('./view/navbar.php'); ?>

    <div class="jumbotron jumbotron-billboard" style="margin-top: -15px; opacity: 0.75;">
      <div style="margin-top:  -40px; margin-bottom: -20px; text-align: center;"> 
        <h1 style="color: #404040; text-shadow: 2px 2px #000000;">Courses</h1>
      </div>
    </div>

    <div class="container">

      <div class="row">
        <div class="col-sm-8 col-sm-offset-2">
          <div id ="my_course_table" style="display: none">
            <div class="panel panel-primary">
              <!-- Default panel contents -->
              <div class="panel-heading">
                <h3 class="panel-title" style="font-size:20px; text-align: center;">My Courses</h3>
              </div>
              <table class="table table-hover" id="my_courses" style="margin-left:auto; margin-right:auto;">
                <tr style="background: none;">
                  <th>Course</th>
                  <th>Role</th>
                  <th>Queue</th>
                </tr>
                <tbody id="my_courses_body"></tbody>
                <!--courses.js should write here-->
              </table>
            </div><!--panel-->
          </div><!--id-->
        </div><!--col-sm-12-->
      </div><!--row-->

      <div class="row">
        <div class="col-sm-8 col-sm-offset-2">
          <div id ="course_table">
            <div class="panel panel-primary">
              <!-- Default panel contents -->
              <div class="panel-heading">
                <h3 class="panel-title" style="font-size:20px; text-align: center;">All Available Courses</h3>
              </div>
              <div class="panel-body">
                <p style="text-align: center;"><b>Enroll/Leave your courses here.</b></p>
              </div>
              <table class="table table-hover " id="all_courses" style="margin-left:auto; margin-right:auto;">
                <tbody id="all_courses_body"></tbody>
                <!--courses.js should write here-->
              </table>
            </div><!--panel-->
          </div><!--id-->
        </div><!--col-sm-12-->
      </div><!--row-->

    </div><!--container-->

  </body>
</html>
