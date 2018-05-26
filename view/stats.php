<!DOCTYPE html>
<html lang="en">

<head>
  <title>TA Help Queue - Stats</title>
  <?php include('./view/head.html'); ?>
  
  <!-- Highcharts CDN -->
  <script src="https://code.highcharts.com/highcharts.src.js" ></script>
  <script src="https://code.highcharts.com/modules/exporting.src.js"></script>
  <script src="https://code.highcharts.com/highcharts-3d.src.js" integrity="sha384-yYHfGKTlXFMrQe8uQPl37NB5145K3wGM4E0NueblXUuuBbVBPtsn36f4D4Ph5WzT" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.1/moment.js" integrity="sha384-jTMy6EIZUv6UQkT/OrJic59RKQRr8cYNcNBBsHUAlAdKt3sSMfCaf5V2YE97wLkB" crossorigin="anonymous"></script>
        
  <script src="./src/stats_graph.js"></script>
</head>

<body>
  <?php include('./view/navbar.php'); ?>

		<div class="jumbotron jumbotron-billboard" style="margin-top: -15px; opacity: 0.75;">
			<div align="center" style="margin-top:  -60px; margin-bottom: -50px">
                <!--RYAN'S STYLE-->
                <h1 id="title" style="color: #404040; text-shadow: 2px 2px #000000;">Course Statistics</h1>
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
