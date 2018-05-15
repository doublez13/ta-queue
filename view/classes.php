<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
		<title>TA Help Queue - Courses</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta name="AUTHOR"      content="Ryan Welling, Blake Burton, Zane Zakraisek"/>
		<meta name="keywords"    content="University of Utah, 2017-2018, College of Engineering"/>
		<meta name="description" content="Senior Project"/>

        <!--U icon for browser tabs-->
        <link rel="icon" type="image/png" href="./resources/img/favicon-32x32.png"/>
        <link rel="icon" type="image/png" href="./resources/img/favicon-16x16.png"/>
        <link rel="icon" type="image/png" href="./resources/img/favicon.ico"/>

		<!-- ALL CSS FILES -->
		<link rel="stylesheet" type="text/css" href="./resources/CSS/global.css">
		<!-- Latest compiled and minified CSS -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
		<!-- Optional theme -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
        <!-- jQuery CDN -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"    integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
        <!-- Latest compiled and minified JavaScript -->
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
        <!-- queue source -->
        <script src="./src/logout.js"></script>
		<script src="./src/classes.js"></script>
	</head>
	<body>

		<nav class="navbar navbar-default">
            <div class="container-fluid">
                <!-- Brand and toggle get grouped for better mobile display -->
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand">
                        <img alt="Brand" src="./resources/img/UHz.png">
                    </a>
                </div>

                <!-- Collect the nav links, forms, and other content for toggling -->
                <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                    <ul class="nav navbar-nav navbar-left">
                        <li>
                            <a href="./about">About</a>
                        </li>
                        <li>
                            <a href="./tutorial" target="_blank">Tutorial</a>
                        </li>
                        <li>
                            <a href="../swagger/index.html" target="_blank">Public API</a>
                        </li>
                    </ul>
                    <ul class="nav navbar-nav navbar-right">
                        <?php
                          session_start();
                          $is_admin    = $_SESSION["is_admin"];
                          if($is_admin){
                            readFile("./view/adminHeader.html");
                          }
                        ?>
                        <li class="active">
                            <a href="#">Courses</a>
                        </li>
                        <li>
                            <a href="#" onclick="logout();">Logout <script> document.write(localStorage.first_name)</script></a>
                        </li>
                    </ul>
                </div><!-- /.navbar-collapse -->
            </div><!-- /.container-fluid -->
        </nav>  

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
