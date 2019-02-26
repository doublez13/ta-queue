<!DOCTYPE html>
<html lang="en">

<head>
  <title>TA Help Queue - Index</title>
  <?php
   include('./view/head.php');

   $includes = ["./src/login.js"];
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
    <div style="text-align:center;">  
      <h1 style="color: #404040; text-shadow: 2px 2px #000000;">Welcome to the TA Help Queue</h1>
    </div>
  </div>

  <div class="container">
    <div class="row">
      <div class="col-sm-12">
        <h2 style="padding-bottom: 25px; text-align:center;"><span style="color: #e8002b;">University of Utah</span> College of Engineering</h2>
      </div><!--col-sm-12-->
    </div><!--row-->

    <div class="row">
      <div class="col-sm-12">
        <p style="font-size:150%; text-align:center;"> Please login using your CADE credentials </p>
      </div><!--col-sm-6-->
    </div><!--row-->

    <form class="form" id="login_form" style="text-align:center; width: 350px; margin: auto; position: relative;">
      <div class="form-group">
        <label class="sr-only" for="Login">Login</label>
        <input class="form-control" id="Login" name="username" type="text" placeholder="Username" required autofocus>
      </div>
      <div class="form-group">
        <label class="sr-only" for="password">Password</label>
        <input class="form-control" id="password" name="password" type="password" placeholder="Password" required>
      </div>
      <button id="saveForm" name="saveForm" type="submit" value="Submit" class="btn btn-primary">Sign in</button>
    </form>
  </div><!--container-->

  <!--Waiting gif which appears under the sign in button-->
  <div id="waiting_spinner" class="padding-top-10" style="text-align: center; visibility: hidden">
    <img src="resources/animations/loading.gif" alt="Loading spinner">
  </div>

  <div class="padding-top-10" style="text-align: center;">
    <a style="color: #e8002b; font-size:130%;" href="https://webhandin.eng.utah.edu/cade/create_account/index.php" target="_blank">No CADE account? Create one here.</a>
  </div>
  <div class="padding-top-10" style="text-align: center;">
    <a style="color: #e8002b; font-size:150%;" href="https://github.com/doublez13/suzie-queue" target="_blank">REPORT BUGS OR SUGGESTIONS HERE</a>
  </div>


</body>
</html>
