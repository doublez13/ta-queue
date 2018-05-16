<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>

<head>
  <title>TA Help Queue - Stats</title>
  <?php include('./view/head.html'); ?>
  
  <!-- Highcharts CDN -->
  <script src="https://code.highcharts.com/highcharts.src.js" ></script>
  <script src="https://code.highcharts.com/modules/exporting.src.js"></script>
  <script src="https://code.highcharts.com/highcharts-3d.src.js" integrity="sha384-yYHfGKTlXFMrQe8uQPl37NB5145K3wGM4E0NueblXUuuBbVBPtsn36f4D4Ph5WzT" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.1/moment.js" integrity="sha384-jTMy6EIZUv6UQkT/OrJic59RKQRr8cYNcNBBsHUAlAdKt3sSMfCaf5V2YE97wLkB" crossorigin="anonymous"></script>
        
  <script src="./src/logout.js"></script>
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
                          $is_admin    = $_SESSION["is_admin"];
                          if($is_admin){
                            readFile("./view/adminHeader.html");
                          }
                        ?>
                        <li>
                            <a href="classes">Courses</a>
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
                    <label class="col-md-8 control-label" for="chart">Choose Form</label>
                    <div class="col-md-8 col-md-offset-2">
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

      <div id="container" class="padding-bottom-30" style="min-width: 960px; height: 600px; max-width: 960px; margin: 0 auto"></div>
    </body>
