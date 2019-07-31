<!DOCTYPE html>
<html lang="en">

<head>
  <title>TA Help Queue - Courses</title>
  <?php
   include('./view/head.php');

   $includes = ["./src/group_modify.js", "./src/logout.js"];
   foreach($includes as $include){
     $filemtime = filemtime($include);
     $source    = $include.'?ver='.$filemtime;
     echo "<script src='".$source."'></script>\n";
   }
  ?>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jsgrid/1.5.3/jsgrid.min.css" integrity="sha384-FANc0lm1AdWuiUkSMYxBxLFUvidZ7pRJO1AuBAfeViE4Q+frQYW5mDHrTt3FeBw8" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jsgrid/1.5.3/jsgrid-theme.min.css" integrity="sha384-/HgoqTJg9K/cC9jt0ds5CDTasJyiigTXQT1VQknBRLnh4ud/915dApA0G0bDOfER" crossorigin="anonymous">

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jsgrid/1.5.3/jsgrid.min.js" integrity="sha384-fJUy9UkxvbaEoX982Lz2GVXfixLrgbslonJJpy3u6e9w3w87kECVrQcj9LI24aWv" crossorigin="anonymous"></script>
</head>

<body>
  <?php include('./view/navbar.php'); ?>

  <div class="jumbotron jumbotron-billboard">
    <div style="text-align: center;"> 
      <h1 style="color: #404040; text-shadow: 2px 2px #000000;">Group Modify</h1>
    </div>
  </div>

  <div class="container">
    <div class="row">
      <div class="col-sm-8 col-sm-offset-2">
        <div class="panel panel-primary">
          <!-- Default panel contents -->
          <div class="panel-heading">
            <h3 id="panel_title" class="panel-title" style="font-size:20px; text-align: center"></h3>
          </div><!--panel-heading-->
          <div id="jsGrid"></div>
          <button id="done_button" class="btn btn-primary" type="submit" onclick="window.location = '/';">Done</button>
        </div><!--panel-->
      </div><!--col-sm-8-->
    </div><!--row-->
  </div><!--container-->

</body>
</html>
