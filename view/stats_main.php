<?php
  include "router.php"
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
		<title>Stats - Main</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta name="AUTHOR"      content="Ryan Welling, Blake Burton, Zane Zakraisek"/>
		<meta name="keywords"    content="University of Utah, 2017-2018, College of Engineering"/>
		<meta name="description" content="Senior Project"/>

		<!-- ALL CSS FILES -->
		<link rel="stylesheet" type="text/css" href="../resources/CSS/global.css">
		<!-- Latest compiled and minified CSS -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
		<!-- Optional theme -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
		<!-- jQuery CDN -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"  integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
		<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css" integrity="sha384-Nlo8b0yiGl7Dn+BgLn4mxhIIBU6We7aeeiulNCjHdUv/eKHx59s3anfSUjExbDxn" crossorigin="anonymous">
        <!-- Latest compiled and minified JavaScript -->
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
        <!-- Cool buttons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
		<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js" integrity="sha384-Dziy8F2VlJQLMShA6FHWNul/veM9bCkRUaLqr199K94ntO5QUrLJBEbYegdSkkqX" crossorigin="anonymous"></script>
        <!-- Highcharts CDN -->
        <script src="https://code.highcharts.com/highcharts.src.js"></script>
        <script src="https://code.highcharts.com/modules/exporting.src.js"></script>
        <script src="https://code.highcharts.com/highcharts-3d.src.js"></script>
    	<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.1/moment.js"></script>
        <!-- queue source -->
        <script src="./src/logout.js"></script>
        <!-- stats source -->
        <script src="./src/stats_graph.js"></script>

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
                            <a href="classes.php">All Courses</a>
                        </li>
                        <li>
                            <a href="my_classes.php">My Courses</a>
                        </li>
                        <li>
                            <a href="#" onclick="logout();">Logout <script> document.write(localStorage.first_name)</script></a>
                        </li>
                    </ul>
                </div><!-- /.navbar-collapse -->
            </div><!-- /.container-fluid -->
        </nav>  

		<div class="jumbotron jumbotron-billboard" style="margin-top: -15px; opacity: 0.75;">
			<div align="center" style="margin-top:  -60px; margin-bottom: -50px">
                <!--RYAN'S STYLE-->
<!--			<h1 id="title" style="color: #404040; text-shadow: 2px 2px #000000; font-family: garamond;"></h1>-->
                <h1 id="title" style="color: #404040; text-shadow: 2px 2px #000000;">Class Statistics</h1>
                <h4 id="queue_state" style="color: #404040;"></h4>
                <h4 id="time_limit"  style="color: #404040;"></h4>
                <h4 id="in_queue"    style="color: #404040;"></h4>
                <h4 id="cooldown"    style="color: #404040;"></h4>
			</div>
		</div>

        <div class="container-fluid" style="padding-right: 10px;">
        <div class="row">
          <div class="col-sm-12">      
            <form class="form-horizontal">
              <fieldset>
                <!-- Form Name -->
                <legend>Available Charts</legend>
                <!-- Select Basic -->
                  <div class="form-group">
                    <label class="col-md-4 control-label" for="chart">Choose Form</label>
                    <div class="col-md-4">
                      <select id="chart" name="chart" class="form-control">
                        <option value="blank"></option>
                        <option value="num_student">Number of Students Helped Per Day</option>
                      </select>
                    </div>
                  </div><!--form-group-->
                </fieldset>
              </form>
            </div><!--col-sm-8-->
          </div><!--row-->
        <div class="row">
          <div class="col-sm-4"> 
            <div id="table_result">
            </div>
          </div> <!--col-sm-4-->
          <div class="col-sm-8"> 
            <div id="chart_result">
            </div>
          </div> <!--col-sm-8-->
        </div><!--row-->
      </div><!--container-->

      <div id="container" style="min-width: 310px; height: 600px; max-width: 960px; margin: 0 auto"></div>
    </body>