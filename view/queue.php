<?php
  include "router.php"
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
		<title>Queue - Main</title>

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
        <!-- queue source -->
        <script src="./src/queue_functions.js"></script>
        <script src="./src/logout.js"></script>
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

		<div class="jumbotron jumbotron-billboard padding-left-20 padding-right-20 flex flex-column flex-alignItems-center flex-noShrink" style="margin-top: -15px; opacity: 0.75; padding:0px;">
            <h1 id="title" style="color: #404040; text-shadow: 2px 2px #000000; word-wrap: break-word; text-align: center">Queue</h1>
<!--        <h2 id="title" style="color: #404040; font-weight: bold; word-wrap: break-word; text-align: center">Queue</h2>-->
			<div class="flex flex-justifyContent-center flex-alignItems-center">
                <h4 class="margin-left-10 margin-right-10" id="queue_state" style="color: #404040;"></h4>|
                <h4 class="margin-left-10 margin-right-10" id="in_queue"    style="color: #404040;"></h4>|
                <h4 class="margin-left-10 margin-right-10" id="time_limit"  style="color: #404040;"></h4>|
                <h4 class="margin-left-10 margin-right-10" id="cooldown"    style="color: #404040;"></h4>
			</div>
		</div>

		<div class="container flex-fillSpace flex-md" style="width: 100%; max-width:1500px;">

            <!--TAS ON DUTY AND QUEUE BUTTONS COLUMN-->
            <div class="col-xs-12 col-sm-3 flex flex-column flex-alignItems-stretch">

                <div class="col-xs-12 padding-0 flex-noShrink">
                    <div id="onDutyTA-wrapper" class="panel panel-primary flex-sm flex-column">
                        <div class="panel-heading">
                            <h3 class="panel-title" id="tas_header" style="font-size:20px;" align="center">TA on Duty</h3>
                        </div>
                        <div class="panel-body flex-fillSpace" id="ta_on_duty" style="overflow-y:auto;"></div>
                    </div>
                </div>

                <div class='btn-group-vertical' role='group' align="left" aria-label='...'>
                    <button class="btn btn-success" id="state_button" style="display: none"></button>
                    <button class="btn btn-success" id="duty_button" style="display: none"></button>
                    <button class="btn btn-info" id="freeze_button" style="display: none"></button>
                </div>

                <button class="margin-top-5 btn btn-success" id="join_button" style="display: none"></button>

                <div class="col-xs-12 margin-top-15 padding-0" align="left">

                    <form id="time_form" title="(minutes)" style="display: none">
                        <label>Time Limit Per Student</label>
                         <div class="input-group" style='width:8em'>
                            <input type="number" min="0" id="time_limit_input" class="form-control">
                            <span class="input-group-btn">
                                <button class="btn btn-success" type="submit">Set</button>
                            </span>
                        </div>
                    </form>
                    <form id="cooldown_form" title="Queue reentry wait time (minutes)" style="display: none">
                        <label>Cool-down Time</label>
                        <div class="input-group" style='width:8em'>
                            <input type="number" min="0" id="cooldown_input" class="form-control">
                            <span class="input-group-btn">
                                <button class="btn btn-success" type="submit">Set</button>
                            </span>
                        </div>
                    </form>
                </div>

                <button class="btn btn-primary" id="stats_button"> <i class="fa fa-database"></i> Course Stats</button>

            </div>



            <!--ANNOUNCEMENTS AND QUEUE COLUMN-->
            <div class="col-xs-12 col-sm-9 flex flex-column">

                <!--ANNOUNCEMENTS WITH SCROLL BAR-->
                <div id="announcements-wrapper" class="panel panel-primary flex flex-column flex-noShrink">
                    <div class="panel-heading">
                        <h3 class="panel-title"  style="font-size:20px;" align="center">Announcements</h3>
                    </div>
                    <div class="flex-fillSpace table-scrollOnOverflow-sm">
                        <table class="table table-hover" id="anns" align="center" style="margin-left:auto; margin-right:auto; display: block; overflow-y: auto;">
                            <tbody class="flex flex-column" id="anns_body"></tbody>
                        </table>
                    </div>

                    <!--POST ANNOUNCEMENT BOX (ONLY SHOWN FOR TAS)-->
                    <form class="bgColor-grey-1 padding-10 margin-0 flex flex-noShrink" id="new_ann_form" style="display: none">
                        <div class="input-group">
                            <input type="text" id="new_ann" class="flex-fillSpace form-control">
                            <span class="input-group-btn">
                                <input class="btn btn-success flex-noShrink" id="ann_button" type="submit" value="Post">
                            </span>
                        </div>
                    </form>
                </div>


                <!--QUEUE WITH SCROLL BAR-->
                <div id ="queue_table" class="flex-fillSpace flex flex-column">
                    <div class="panel panel-primary flex-fillSpace flex flex-column">
                        <div class="panel-heading flex-noShrink">
                            <h3 class="panel-title" style="font-size: 20px;" align="center">Queue</h3>
                        </div>
                        <div class="flex-fillSpace table-scrollOnOverflow-sm">
                        <table class="table table-hover" id="queue" align="center" style="margin-left:auto; margin-right:auto; display: block; overflow-y:auto; table-layout: fixed;">
                            <tbody id="queue_body"></tbody>
                        </table>
                        </div>
                    </div>
                </div>


<!-- ~~~~~~~~~~~~~~~~~~~~~ DO NOT DELETE/EDIT CODE BELOW!: WORKING BACK UP CODE ACROSS ALL BROWSERS  ~~~~~~~~~~~~~~~~~~~~~~~~~-->

                <!--NO SCROLL BAR: GROWING/SHRINKING ANNOUNCEMENTS-->
<!--                <div class="panel panel-primary">-->
<!--                    <div class="panel-heading">-->
<!--                        <h3 class="panel-title"  style="font-size:20px;" align="center">Announcements</h3>-->
<!--                    </div>-->
<!--                    <table class="table table-hover" id="anns" align="center" style="margin-left:auto; margin-right:auto;">-->
<!--                        <tbody id="anns_body"></tbody>-->
<!--                    </table>-->
<!---->
                    <!--POST ANNOUNCEMENT BOX (ONLY SHOWN FOR TAS)-->
<!--                    <form class="bgColor-grey-1 padding-10 margin-0 flex flex-noShrink" id="new_ann_form" style="display: none">-->
<!--                        <div class="input-group">-->
<!--                            <input type="text" id="new_ann" class="flex-fillSpace form-control">-->
<!--                            <span class="input-group-btn">-->
<!--                                <input class="btn btn-success flex-noShrink" id="ann_button" type="submit" value="Post">-->
<!--                            </span>-->
<!--                        </div>-->
<!--                    </form>-->
<!--                </div>-->

                <!--NO SCROLL BAR: GROWING/SHRINKING QUEUE-->
<!--                <div id ="queue_table">-->
<!--                    <div class="panel panel-primary">-->
                        <!-- Default panel contents -->
<!--                        <div class="panel-heading">-->
<!--                            <h3 class="panel-title" style="font-size: 20px;" align="center">Queue</h3>-->
<!--                        </div>-->
<!--                        <table class="table table-hover" id="queue" align="center" style="margin-left:auto; margin-right:auto; table-layout: fixed;">-->
                            <!--<thead id="queue_head"></thead>-->
<!--                            <tbody id="queue_body"></tbody>-->
<!--                        </table>-->
<!--                    </div>-->
<!--                </div>-->

<!-- ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ END WORKING BACK UP CODE ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->

            </div>
		</div><!--container-->

        <!--ENTER QUEUE MODAL-->
        <div id="dialog-form" title="Location and Question">
            <p class="validateTips">Both fields are required.<br>
                <i>(50 character max)</i>
            </p>
            <br>
            <form>
                <fieldset>
                    <label for="location">Location</label>
                    <div>
                        <input type="text" name="location" id="location" style="width: 100%" class="text" maxlength="50">
                    </div>
                    <br>
                    <label for="question">Question</label>
                    <div>
                        <input type="text" name="question" id="question" style="width: 100%" class="text" maxlength="50">
                    </div>
                </fieldset>
            </form>
        </div>

	</body>
</html>


