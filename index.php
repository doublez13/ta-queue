<?php
  include "router.php"
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
		<title>TA Help Queue - Index</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

		<meta name="AUTHOR"      content="Ryan Welling, Blake Burton, Zane Zakraisek"/>
		<meta name="keywords"    content="University of Utah, 2017-2018"/>
		<meta name="description" content="Senior Project"/>

		<!-- ALL CSS FILES -->
		<link rel="stylesheet" type="text/css" href="./resources/CSS/global.css">
		<!-- Latest compiled and minified CSS -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
		<!-- Optional theme -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
		<!-- jQuery CDN -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script> 
		<!-- Latest compiled and minified JavaScript -->
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
                <script src="./view/src/login.js"></script>
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
                        <img alt="Brand" src="./resources/img/UHz.png">
                    </a>
                </div>

                <!-- Collect the nav links, forms, and other content for toggling -->
                <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                    <ul class="nav navbar-nav navbar-right">

                    </ul>
                </div><!-- /.navbar-collapse -->
            </div><!-- /.container-fluid -->
        </nav>

		<div class="jumbotron jumbotron-billboard" style="margin-top: -15px; opacity: 0.75;">
			<div align="center" style="margin-top:  -40px; margin-bottom: -20px">	
				<h1 style="color: #404040; text-shadow: 2px 2px #000000;">Welcome to the TA Help Queue</h1>
			</div>
		</div>

		<div class="container">
			<div class="row">
				<div class="col-sm-12">
					<h2 style="padding-bottom: 25px;" align="center"><span style="color: #e8002b;">University of Utah</span> College of Engineering</h2>
				</div><!--col-sm-12-->
			</div><!--row-->
			<div class="row">
				<div class="col-sm-12">
					<p style="font-size:150%;" align="center"> Please login using your CADE credentials </p>
				</div><!--col-sm-6-->
			</div><!--row-->

                <form class="form" id="login_form" align="center" style="width: 350px; margin: auto; position: relative;">
                  <div class="form-group">
                    <label class="sr-only" for="exampleInputEmail3">Login</label>
                    <input class="form-control" id="Login" name="username" type="text" pattern="[a-zA-Z0-9]+" placeholder="User Name" required autofocus>
                  </div>
                  <div class="form-group">
                    <label class="sr-only" for="exampleInputPassword3">Password</label>
                    <input class="form-control" id="password" name="password" type="password" minlength="8"  placeholder="Password" required>
                  </div>
                  <button id="saveForm" name="saveForm" type="submit" value="Submit" class="btn btn-primary">Sign in</button>
                </form>
		</div><!--container-->

	</body>
</html>
