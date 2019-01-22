<!DOCTYPE html>
<html lang="en">

<head>
  <title>TA Help Queue - Stats</title>
  <?php
   include('./view/head.html');

   $includes = ["./src/stats_graph.js"];
   foreach($includes as $include){
     $filemtime = filemtime($include);
     $source    = $include.'?ver='.$filemtime;
     echo "<script src='".$source."'></script>\n";
   }
  ?>
  
  <!-- Highcharts CDN -->
  <script src="https://code.highcharts.com/highcharts.src.js" integrity="sha384-FYisK27wreiNhnjrtRFQC6eGL2LN/gV+SNGBQZhK3Cnxgln2gGVutPzePwE9RIxx" crossorigin="anonymous"></script>
  <script src="https://code.highcharts.com/modules/exporting.src.js" integrity="sha384-t3InkGFPpG7qAfxhe5V7mfudiR6stKo1O2tRAudyVjxbNcESro4lkRN3Rv8lrUks" crossorigin="anonymous"></script>
  <script src="https://code.highcharts.com/highcharts-3d.src.js" integrity="sha384-tQAmxFwtvZsQAvixjcGgTE/1m5dLy/jhorj9D8UXJWSZ43QQgR8fkxBw1dDv1Cpk" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.1/moment.js" integrity="sha384-jTMy6EIZUv6UQkT/OrJic59RKQRr8cYNcNBBsHUAlAdKt3sSMfCaf5V2YE97wLkB" crossorigin="anonymous"></script>
</head>

<body>
  <?php include('./view/navbar.php'); ?>

    <div class="jumbotron jumbotron-billboard" style="margin-top: -20px; opacity: 0.75;">
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
