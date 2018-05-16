<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>

<head>
  <title>TA Help Queue - Courses</title>
  <?php include('./view/head.html'); ?>
  <script src="./src/logout.js"></script>
  <script src="./src/classes.js"></script>
</head>

<body>
  <?php include('./view/navbar.php'); ?>


		<div class="jumbotron jumbotron-billboard" style="margin-top: -15px; opacity: 0.75;">
			<div align="center" style="margin-top:  -40px; margin-bottom: -20px">	
				<h1 style="color: #404040; text-shadow: 2px 2px #000000;">Courses</h1>
			</div>
		</div>

               <div class="container">
                        <div class="row">
                                <div class="col-sm-8 col-sm-offset-2">
                                        <div id ="class_table">
                                                <div class="panel panel-primary">
                                                <!-- Default panel contents -->
                                                        <div class="panel-heading">
                                <h3 class="panel-title" style="font-size:20px;" align="center">My Courses</h3>
                                                        </div>
                            <table class="table table-hover" id="my_classes" align="center" style="margin-left:auto; margin-right:auto;">
                              <tr style="background: none;">
                                <th>Course</th>
                                <th>Role</th>
                                <th>Queue</th>
                              </tr>
                              <tbody id="my_classes_body"></tbody>
                                                                <!--classes.js should write here-->
                                                        </table>
                                                </div><!--panel-->
                                        </div><!--id-->
                                </div><!--col-sm-12-->
                        </div><!--row-->
                </div><!--container-->

		<div class="container">
			<div class="row">
				<div class="col-sm-8 col-sm-offset-2">
					<div id ="class_table">
						<div class="panel panel-primary">
						<!-- Default panel contents -->
							<div class="panel-heading">
                                <h3 class="panel-title" style="font-size:20px;" align="center">All Available Courses</h3>
							</div>
							<div class="panel-body">
                                <p align="center"><b>Enroll/Leave your courses here.</b></p>
							</div>
                            <table class="table table-hover " id="all_classes" align="center" style="margin-left:auto; margin-right:auto;">
                                <tbody id="all_classes_body"></tbody>
								<!--classes.js should write here-->
							</table>
						</div><!--panel-->
					</div><!--id-->
				</div><!--col-sm-12-->

			</div><!--row-->
		</div><!--container-->

	</body>
</html>
