<?php
  include "router.php"
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
		<title>Queue - All Classes</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta name="AUTHOR"      content="Ryan Welling, Blake Burton, Zane Zakraisek"/>
		<meta name="keywords"    content="University of Utah, 2017-2018"/>
		<meta name="description" content="Senior Project"/>

		<!-- ALL CSS FILES -->
		<link rel="stylesheet" type="text/css" href="../resources/CSS/global.css">
		<!-- Latest compiled and minified CSS -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
		<!-- Optional theme -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
		<!-- jQuery CDN -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script> 
		<!-- Latest compiled and minified JavaScript -->
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

                <script src="./src/logout.js"></script>
		<script src="./src/create_class.js"></script>
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
                    <a class="navbar-brand" href="#">
                        <img alt="Brand" src="../resources/img/UHz.png">
                    </a>
                </div>

                <!-- Collect the nav links, forms, and other content for toggling -->
                <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                    <ul class="nav navbar-nav navbar-right">
                        <?php
                          session_start();
                          $is_admin    = $_SESSION["is_admin"];
                          if($is_admin){
                            readFile("./adminHeader.html");
                          }
                        ?>
                        <li>
                            <a href="classes.php">All Classes</a>
                        </li>
                        <li>
                            <a href="my_classes.php">My Classes</a>
                        </li>
                        <li>
                            <a href="#" onclick="logout();">Logout</a>
                        </li>
                    </ul>
                </div><!-- /.navbar-collapse -->
            </div><!-- /.container-fluid -->
        </nav>  

		<div class="jumbotron jumbotron-billboard" style="margin-top: -15px; opacity: 0.75;">
			<div align="center" style="margin-top:  -40px; margin-bottom: -20px">	
				<h1 style="color: #404040; text-shadow: 2px 2px #000000;">New Class</h1>
			</div>
		</div>

		<div class="container">
			<div class="row">
				<div class="col-sm-8 col-sm-offset-2">
					<div id ="class_table">
						<div class="panel panel-primary">
						<!-- Default panel contents -->
							<div class="panel-heading">
								<h3 class="panel-title">Create New Class</h3>
                                                        </div>
<style>
input[type=text], select, textarea {
    width: 100%; /* Full width */
    padding: 12px; /* Some padding */  
    border: 1px solid #ccc; /* Gray border */
    border-radius: 4px; /* Rounded borders */
    box-sizing: border-box; /* Make sure that padding and width stays in place */
    margin-top: 6px; /* Add a top margin */
    margin-bottom: 16px; /* Bottom margin */
    resize: vertical /* Allow the user to vertically resize the textarea (not horizontally) */
}
</style>
                                                         
  <form id="create_class">
    <label>Course Name</label>
    <input type="text" id="course_name" placeholder="Course Name.." required>

    <label>Department</label>
    <input type="text" id="depart_prefix" placeholder="CS" required>

    <label>Course Number</label>
    <input type="text" id="course_num" placeholder="4400" required>

    <label>Instructor username</label>
    <input type="text" id="professor" placeholder="username" required>

    <label>LDAP group</label>
    <input type="text" id="ldap_group" placeholder="cs4400" required>

    <label>Access Code</label>
    <input type="text" id="acc_code">

    <label>Description</label>
    <textarea id="description" style="height:200px" required></textarea>

    <input type="submit" value="Create Course">
  </form>

						</div><!--panel-->
					</div><!--id-->
				</div><!--col-sm-12-->

			</div><!--row-->
		</div><!--container-->
	</body>
</html>
