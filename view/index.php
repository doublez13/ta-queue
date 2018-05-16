<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <title>TA Help Queue - Index</title>
  <?php include('./view/head.html'); ?> 
  <script src="./src/login.js"></script>
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
                            <a href="about">About</a>
                        </li>
                        <li>
                            <a href="tutorial" target="_blank">Tutorial</a>
                        </li>
                        <li>
                            <a href="../api" target="_blank">Public API</a>
                        </li>
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

            <form class="form" id="login_form" style="text-align:center; width: 350px; margin: auto; position: relative;">
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

        <!--Waiting gif which appears under the sign in button-->
        <div id="waiting_spinner" class="padding-top-10" style="text-align: center; visibility: hidden">
            <img src="resources/animations/loading.gif"/>
        </div>

        <div class="padding-top-10" style="text-align: center;">
            <a style="color: #e8002b; font-size:130%;" href="https://webhandin.eng.utah.edu/cade/create_account/index.php" target="_blank">No CADE account? Create one here.</a>
        </div>

<div id="footer">
    <img src="https://www.gnu.org/graphics/gplv3-127x51.png">
</div>

    </body>
</html>
