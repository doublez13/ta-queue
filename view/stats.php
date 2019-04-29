<!DOCTYPE html>
<html lang="en">

<head>
  <title>TA Help Queue - Stats</title>
  <?php
   include('./view/head.php');

   $includes = ["./src/stats_graph.js", "./src/logout.js"];
   foreach($includes as $include){
     $filemtime = filemtime($include);
     $source    = $include.'?ver='.$filemtime;
     echo "<script src='".$source."'></script>\n";
   }
  ?>
  
  <!-- Highcharts CDN -->
  <script src="https://code.highcharts.com/highcharts.src.js" crossorigin="anonymous"></script>
  <script src="https://code.highcharts.com/modules/exporting.src.js" crossorigin="anonymous"></script>
  <script src="https://code.highcharts.com/highcharts-3d.src.js" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.1/moment.js" integrity="sha384-jTMy6EIZUv6UQkT/OrJic59RKQRr8cYNcNBBsHUAlAdKt3sSMfCaf5V2YE97wLkB" crossorigin="anonymous"></script>
</head>

<body>
  <?php include('./view/navbar.php'); ?>

    <div class="jumbotron jumbotron-billboard">
      <div align="center">
        <h1 id="title" style="color: #404040; text-shadow: 2px 2px #000000;">Course Statistics</h1>
      </div>
    </div>

        <div class="container-fluid" style="padding-right: 10px;">
        <div class="row">
          <div class="col-sm-12">      
            <form class="form-inline">
              <fieldset>
                <!-- Form Name -->
                <legend>Available Charts</legend>
                <!-- Select Basic -->

                  <div class="form-group">
                    <label class="col-md-8 control-label" for="chart">Choose Stats</label>
                    <div class="col-md-8">
                      <select id="stats_selector" class="form-control">
                        <option selected="selected" value="num_student">Students Helped Per Day</option>
                        <option value="ta_proportions">Portion of Students Helped by TA</option>
                        <option value="ta_avg_help_time">Average TA Help Time</option>
                      </select>
                    </div>
                  </div><!--form-group-->

                  <div class="form-group">
                    <label class="col-md-8 control-label" for="chart">Start Date</label>
                    <div class="col-md-8">
                      <input id="start_date" type="date">
                    </div>
                  </div><!--form-group-->

                  <div class="form-group">
                    <label class="col-md-8 control-label" for="chart">End Date</label>
                    <div class="col-md-8">
                      <input id="end_date" type="date">
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
