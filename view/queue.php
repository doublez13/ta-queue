<!DOCTYPE html>
<html lang="en">

<head>
  <title>TA Help Queue - Main</title>
  <?php include('./view/head.html'); ?>
  <script src="./src/queue_functions.js"></script>
</head>

<body>
  <?php include('./view/navbar.php'); ?>

    <div class="jumbotron jumbotron-billboard padding-left-20 padding-right-20 flex flex-column flex-alignItems-center flex-noShrink" style="margin-top: -15px; opacity: 0.75; padding:0px;">
      <h1 id="title" style="color: #404040; text-shadow: 2px 2px #000000; word-wrap: break-word; text-align: center">Queue</h1>
      <div class="flex flex-justifyContent-center flex-alignItems-center">
        <h4 class="margin-left-10 margin-right-10" id="queue_state" style="color: #404040;"></h4>|
        <h4 class="margin-left-10 margin-right-10" id="in_queue"    style="color: #404040;"></h4>|
        <h4 class="margin-left-10 margin-right-10" id="time_limit"  style="color: #404040;"></h4>|
        <h4 class="margin-left-10 margin-right-10" id="cooldown"    style="color: #404040;"></h4>
      </div>
    </div>

    <div class="container flex-fillSpace flex-md" style="width: 100%; max-width:1500px;">

            <!--TAS ON DUTY AND QUEUE BUTTONS COLUMN-->
            <div class="col-xs-12 col-sm-3 col-md-2  padding-bottom-15 flex flex-column flex-alignItems-stretch">

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
                        <label for="time_limit_input">Time Limit Per Student</label>
                         <div class="input-group" style='width:8em'>
                            <input type="number" min="0" id="time_limit_input" class="form-control">
                            <span class="input-group-btn">
                                <button class="btn btn-success" type="submit">Set</button>
                            </span>
                        </div>
                    </form>
                    <form id="cooldown_form" title="Queue reentry wait time (minutes)" style="display: none">
                        <label for="cooldown_input" >Cool-down Time</label>
                        <div class="input-group" style='width:8em'>
                            <input type="number" min="0" id="cooldown_input" class="form-control">
                            <span class="input-group-btn">
                                <button class="btn btn-success" type="submit">Set</button>
                            </span>
                        </div>
                    </form>

                    <button class="btn btn-primary" style="width: 100%" id="stats_button"> <i class="fa fa-database"></i> Course Stats</button>

                </div>

            </div>


            <!--ANNOUNCEMENTS AND QUEUE COLUMN-->
            <div class="col-xs-12 col-sm-9 col-md-10 flex flex-column">

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
                            <input type="text" id="new_ann" class="flex-fillSpace form-control" aria-label='...'>
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
                        <table class="table table-hover" id="queue" align="center" style="margin-left:auto; margin-right:auto; overflow-y:auto; table-layout: fixed;">
                            <tbody id="queue_body"></tbody>
                        </table>
                        </div>
                    </div>
                </div>


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
                        <input type="text" name="location" id="location" style="width: 100%" class="text" maxlength="50" required>
                    </div>
                    <br>
                    <label for="question">Question</label>
                    <div>
                        <input type="text" name="question" id="question" style="width: 100%" class="text" maxlength="50" required>
                    </div>
                </fieldset>
            </form>
        </div>

  </body>
</html>


