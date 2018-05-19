var dialog;
var form;

$(document).ready(function(){
  //GET parsing snippet from CHRIS COYIER
  var query = window.location.search.substring(1);
  var vars = query.split("&");
  for (var i=0;i<vars.length;i++) {
    var pair = vars[i].split("=");
    if(pair[0] == "course"){
      course = decodeURIComponent(pair[1]);
      break;
    }
  }
  if(typeof course === 'undefined'){
    window.location ='/';
  }

  dialog = $( "#dialog-form" ).dialog({
    autoOpen: false,
    height: 350,
    width: 350,
    modal: true,
    buttons: {
      "Enter Queue": function() {
	      lab_location = document.getElementById("location").value;
	      question = document.getElementById("question").value;
        var cont = true;
        document.getElementById('location').style.borderColor = "black";
        document.getElementById('question').style.borderColor = "black";
        if(lab_location == ""){
          document.getElementById('location').style.borderColor = "red";
          cont = false;
        }
        if(question == ""){
          document.getElementById('question').style.borderColor = "red";
          cont = false;
        }
        if(cont){
	        enqueue_student(course, question, lab_location);
	        dialog.dialog( "close" );
        }
      },
      Cancel: function() {
        dialog.dialog( "close" );
        document.getElementById('location').style.borderColor = "black";
        document.getElementById('question').style.borderColor = "black";
      }
    }
  });

  $("#stats_button").click(function( event ) {
    event.preventDefault();
    window.location = "stats?course="+course;
  });

  start();
});

function start(){
  $("#title").text(course+' Queue');
  my_username = localStorage.username;
  first_name  = localStorage.first_name;
  last_name   = localStorage.last_name;
    
  var url = "../api/user/my_courses";
  var get_req = $.get( url);
  var done = function(data){
    var dataString = JSON.stringify(data);
    var dataParsed = JSON.parse(dataString);
    is_TA = false;
    if($.inArray(course, dataParsed["ta_courses"]) != -1){
      is_TA = true;
    }
    get_queue(course);
    setInterval(get_queue, 5000, course);
  }
  get_req.done(done);
}

//This function is called every X seconds,
//and is what updates the dataParsed  
function get_queue(course) {
  var url = "../api/queue/get_queue";
  var posting = $.post( url, { course: course } );
  var done = function(data){
    var dataString = JSON.stringify(data);
    var dataParsed = JSON.parse(dataString);
    if(dataParsed.error){
      var error = dataParsed.error;
      //If they're not enrolled in the course, attempt to
      //enroll them with no access code
      if(error == "Not enrolled in course"){
        enrollCourse(course, null);
      }else{
        alert(dataParsed.error);
        return;
      }
    }
    renderView(dataParsed);
  }
  posting.done(done);
}

//This function renders the view from the data
//TODO: Put this on its own timer
//      Then only update the page when it changes
function renderView(dataParsed) {

  //Render the top stats: state, time, length
  render_stats(dataParsed);

  //Render the announcements box
  render_ann_box(dataParsed.announcements);

  //Render the TA side box
  render_ta_table(dataParsed.TAs)
 
  //Render the queue table
  render_queue_table(dataParsed);

  //Render buttons, and so on
  if(is_TA){
    render_ta_view(dataParsed)
  }else{
    render_student_view(dataParsed)
  }
}

function render_stats(dataParsed){
  var state = dataParsed.state.charAt(0).toUpperCase() + dataParsed.state.slice(1);

  // SET AND COLOR QUEUE STATE
  $("#queue_state").empty();
  $("#queue_state").append("<span>State: </span>")
  $("#queue_state").append("<span id='state'><b>"+state+"</b></span>")
  if(state == "Open"){
    $("#state").css('color', 'green');
  }
  else if(state == "Closed"){
    $("#state").css('color', 'red');
  }
  else
    $("#state").css('color', 'blue');

  // SET TIME LIMIT
  $("#time_limit").empty();
  $("#time_limit").append("<span id='time_lim'>Time Limit: </span>")
  if(dataParsed.time_lim >0){
    $("#time_lim").append("<b>"+dataParsed.time_lim+" Minutes</b>");
  }
  else
    $("#time_lim").append("None");

  // SET COOL DOWN
  $("#cooldown").empty();
  $("#cooldown").append("<span id='cd_time'>Cool-Down: </span>")
  if(dataParsed.cooldown >0){
    $("#cd_time").append("<b>"+dataParsed.cooldown+" Minutes</b>");
  }
  else
    $("#cd_time").append("None");

  // SET QUEUE LENGTH
  $("#in_queue").text("Length: " + dataParsed.queue_length);
}

function render_ann_box(anns){

  $("#anns_body").empty();
  $('#anns_body').append('<tr class="flex" style="background: none;"> ' +
                           '<th class="flex-noShrink" style="width:110px;">Date</th>' +
                           '<th class="flex-noShrink" style="width:100px;">Time</th>' +
                           '<th class="flex-noShrink" style="width:180px;">Poster</th>' +
                           '<th>Announcement</th> </tr>');
  for(ann in anns){
    var date = anns[ann]["tmstmp"].split(" ")[0];
    var time = tConvert(anns[ann]["tmstmp"].split(" ")[1].substr(0, 5));

    // Calculate how hold the announcement is -> ann_age_sec (doesn't work in IE)
    var current_timestamp = new Date(new Date().toISOString().slice(0, 19).replace('T', ' '));
    current_timestamp.setHours(current_timestamp.getHours() - 6); // new Date() is 6 hours ahead
    var announcement_timestamp = new Date(anns[ann]["tmstmp"]);
    var ann_age_sec = (current_timestamp - announcement_timestamp) / 1000; // ms -> s

    var poster          = anns[ann]["poster"];
    var announcement    = anns[ann]["announcement"];
    let announcement_id = anns[ann]["id"];
    var new_row =  $('<tr class="flex">' +
                       '<td class="flex-noShrink" style="width:110px;">' + date + '</td>' +
                       '<td class="flex-noShrink" style="width:100px;">' + time + '</td>' +
                       '<td class="flex-noShrink" style="width:180px; word-wrap:break-word;">' + poster + '</td>' +
                       '<td class="flex-fillSpace">' + announcement + '</td> </tr>');
    if(is_TA){
      var del_ann_button = $('<td><div align="right"><button class="btn btn-primary"><i class="fa fa-close" title="Delete"></i></button></div></td>');
      del_ann_button.click(function(event){
        del_announcement(course, announcement_id)
      });
      new_row.append(del_ann_button);
    }

    // Change color of announcement if it's less than X seconds old (color doesn't show in mobile Safari)
    if (ann_age_sec < 3600) {
      new_row.css("background-color", "#b3ffb3"); // 00ff00 ccff33
    }

    $('#anns_body').append(new_row);
  }

  // show new announcement form if TA
  if(is_TA){
    $("#ann_button").unbind("click");
    $("#new_ann_form").show();
    $("#ann_button").click(function( event ) {
      event.preventDefault();
      var announcement = document.getElementById("new_ann").value;
      if (announcement !== "") {
        document.getElementById("new_ann").value = "";
        add_announcement(course, announcement)
      }
    });
  }

  // ~~~~~~~~~~~~~~ DO NOT DELETE/EDIT: WORKING BACK UP CODE ACROSS ALL BROWSERS  ~~~~~~~~~~~~~~~~~~~~~~~~
  // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ NO FLEX OR SCROLL BAR OPTION ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
  // $("#anns_body").empty();
  // $('#anns_body').append("<tr style='background: none;'> " +
  //                          "<th class='col-sm-2' align='left' style='word-wrap:break-word'>Date</th>" +
  //                          "<th class='col-sm-2' align='left' style='word-wrap:break-word'>Time</th>" +
  //                          "<th class='col-sm-2' align='left' style='word-wrap:break-word'>Poster</th>" +
  //                          "<th class='col-sm-5' align='left' style='word-wrap:break-word'>Announcement</th>" +
  //                          "<th class='col-sm-1'></th></tr>");
  //
  // for(ann in anns){
  //     var date       = anns[ann]["tmstmp"].split(" ")[0];
  //     var time       = tConvert(anns[ann]["tmstmp"].split(" ")[1].substr(0, 5));
  //
  //     // Calculate how hold the announcement is -> ann_age_sec (doesn't work in IE)
  //     var current_timestamp = new Date(new Date().toISOString().slice(0, 19).replace('T', ' '));
  //     current_timestamp.setHours(current_timestamp.getHours() - 6); // new Date() is 6 hours ahead
  //     var announcement_timestamp = new Date(anns[ann]["tmstmp"]);
  //     var ann_age_sec = (current_timestamp - announcement_timestamp) / 1000; // ms -> s
  //     var poster          = anns[ann]["poster"];
  //     var announcement    = anns[ann]["announcement"];
  //     let announcement_id = anns[ann]["id"]
  //     var new_row =  $("<tr>" +
  //                        "<td class='col-sm-2' align='left' style='word-wrap:break-word'>"+date+"</td>" +
  //                        "<td class='col-sm-2' align='left' style='word-wrap:break-word'>"+time+"</td>" +
  //                        "<td class='col-sm-2' align='left' style='word-wrap:break-word'>"+poster+"</td>" +
  //                        "<td class='col-sm-5'>"+announcement+"</td> </tr>");
  //
  //     var td = $('<td class='col-sm-1'></td>');
  //     if(is_TA){
  //
  //       // blue X icon below:
  //       var del_ann_button = $('<div align="right"><button class="btn btn-primary"><i class="fa fa-close" title="Delete"></i></button></div>');
  //       // red circle X icon below:
  //       //var del_ann_button = $('<td><button class="btn btn-danger"><i class="glyphicon glyphicon-remove-sign" title="Delete"></i></button><td>');
  //       del_ann_button.click(function(event){
  //           del_announcement(course, announcement_id)
  //       });
  //       td.append(del_ann_button);
  //     }
  //     new_row.append(td);
  //
  //     // Change color of announcement if it's less than X seconds old
  //     if (ann_age_sec < 900) {
  //       new_row.css("background-color", "#b3ffb3");//        00ff00 ccff33
  //     }
  //
  //     $('#anns_body').append(new_row);
  // }
  // if(is_TA){
  //     $("#ann_button").unbind("click");
  //     $("#new_ann_form").show();
  //     $("#ann_button").click(function( event ) {
  //         event.preventDefault();
  //         var announcement = document.getElementById("new_ann").value;
  //         document.getElementById("new_ann").value = "";
  //         add_announcement(course, announcement)
  //     });
  // }

  // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ END WORKING BACK UP CODE ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
}

//Shows the TAs that are on duty
function render_ta_table(TAs){
  $("#ta_on_duty h4").remove();
  if(TAs.length < 2)
    $("#tas_header").text("TA on Duty");
  else
    $("#tas_header").text("TAs on Duty");
  for(TA in TAs){
    var full_name = TAs[TA]["full_name"];
    $('#ta_on_duty').append("<h4>"+full_name+"</h4>");
  }
}

function render_ta_view(dataParsed){
  $("#state_button").unbind("click");
  $("#duty_button").unbind("click");
  $("#freeze_button").unbind("click");
  $("#time_form").unbind("submit");
  $("#cooldown_form").unbind("submit");

  var queue_state = dataParsed.state;
  if(queue_state == "closed"){
    document.getElementById("state_button").className="btn btn-success";
    $("#state_button").text("Open Queue");
    $("#state_button").click(function( event ) {
      event.preventDefault();
      open_queue(course);
    });
    $("#duty_button").hide();
    $("#freeze_button").hide();
    $("#time_form").hide();
    $("#cooldown_form").hide();
  }else{ //open or frozen
    document.getElementById("state_button").className="btn btn-danger";
    $("#state_button").text("Close Queue");
    $("#state_button").click(function( event ) {
      event.preventDefault();
      if (dataParsed.queue_length > 0) {
        var res = confirm("Are you sure you want to close the queue? All students will be removed.")
        if (res) {
          close_queue(course);
        }
      }
      else
        close_queue(course);
    });
   
    if(queue_state == "open"){ 
      //$("body").css("background-image", "-webkit-linear-gradient(top, #808080 0%, #FFFFFF 50%");
      document.getElementById("freeze_button").className="btn btn-primary";
      document.getElementById("freeze_button").title="Prevent new entries";
      $("#freeze_button").text("Freeze Queue");
      $("#freeze_button").click(function( event ) {
        event.preventDefault();
        freeze_queue(course);
      });
    }else{ //frozen
      //$("body").css("background-image", "-webkit-linear-gradient(top, #075685 0%, #FFF 50%");
      document.getElementById("freeze_button").className="btn btn-warning";
      document.getElementById("freeze_button").title="Allow new entries";
      $("#freeze_button").text("Resume Queue");
      $("#freeze_button").click(function( event ) {
        event.preventDefault();
        open_queue(course);
      });
    }

    var TAs_on_duty = dataParsed.TAs;
    var on_duty     = false;
    for(var entry = 0; entry < TAs_on_duty.length; entry++){
      if(TAs_on_duty[entry].username == my_username){
        on_duty = true;
        break;
      }
    } 

    if(!on_duty) {
      document.getElementById("duty_button").className="btn btn-success";
      $("#duty_button").text("Go On Duty");
      $("#duty_button").click(function(event){
        event.preventDefault();
        enqueue_ta(course);
      });
    }
    else{
      document.getElementById("duty_button").className="btn btn-danger";
      $("#duty_button").text("Go Off Duty");
      $("#duty_button").click(function(event){
	    event.preventDefault();
	    dequeue_ta(course);
      });
    }
    $("#duty_button").show();
    $("#freeze_button").show();

    // Don't refresh while editing
    if (!$("#time_limit_input").is(":focus")) {
      $("#time_limit_input").val(dataParsed.time_lim);
    }
    $("#time_form").show();
    $("#time_form").submit(function(event){
      event.preventDefault();
      var limit = $(this).find( "input[id='time_limit_input']" ).val();
      set_limit(course, limit);
    });

    // Don't refresh while editing
    if (!$("#cooldown_input").is(":focus")) {
      $("#cooldown_input").val(dataParsed.cooldown);
    }
    $("#cooldown_form").show();
    $("#cooldown_form").submit(function(event){
      event.preventDefault();
      var limit = $(this).find( "input[id='cooldown_input']" ).val();
      set_cooldown(course, limit);
    });
  }
  $("#state_button").show();
}

function render_student_view(dataParsed){
  var queue = dataParsed.queue;
  
  var in_queue = false;
  for(session in queue){
    if(my_username == queue[session]["username"]){
      in_queue = true;
      break;
    }
  }
 
  var state = dataParsed.state; 
  if(state == "closed" || (state == "frozen" && !in_queue )){
    $("#join_button").hide();
    return;
  }

  $("#join_button").unbind("click");
  if(!in_queue){//Not in queue
    document.getElementById("join_button").className="margin-top-5 btn btn-success";
    $("#join_button").text("Enter Queue");
    $("#join_button").click(function( event ) {
      event.preventDefault();
      dialog.dialog( "open" );
    });
  }
  else{ //In queue
    document.getElementById("join_button").className="margin-top-5 btn btn-danger";
    $("#join_button").text("Exit Queue");
    $("#join_button").click(function( event ) {
      event.preventDefault();
      if (confirm("Are you sure you want to exit the queue?")) {
        dequeue_student(course);
      }
    });
  }
  $("#join_button").show();
}

//Displays the queue table
function render_queue_table(dataParsed){
  var queue = dataParsed.queue;
  var TAs   = dataParsed.TAs;

  $("#queue_body").empty();
  $('#queue_body').append("<tr style='background: none;'>" +
                            "<th class='col-sm-1' align='left'>Pos.</th>"+
                            "<th class='col-sm-2' align='left' style='word-wrap: break-word'>Student</th>" +
                            "<th class='col-sm-2' align='left' style='word-wrap: break-word'>Location</th>" +
                            "<th class='col-sm-4' align='left' style='word-wrap: break-word'>Question</th>" +
                            "<th class='col-sm-3'></th> </tr>");
  var helping = {};
  for(TA in TAs ){
    if(TAs[TA].helping != null){
      helping[TAs[TA].helping] = {}; //Maps student being helped to info about their session in the queue
      helping[TAs[TA].helping]["duration"] = TAs[TA].duration;  //Time student has been helped
      helping[TAs[TA].helping]["TA"] = TAs[TA].username;        //TA helping the student
    }
  }
  
  var time_lim = dataParsed.time_lim;

  var i = 1;
  for(row in queue){
    let username  = queue[row].username;
    let full_name = queue[row].full_name;
    var Location  = queue[row].location;
    var question  = queue[row].question;
    var new_row = $("<tr>" +
                      "<td class='col-sm-1' align='left'>" + i + "</td>" +
                      "<td class='col-sm-2' align='left' style='word-wrap:break-word'>" + full_name + "</td>" +
                      "<td class='col-sm-2' align='left' style='word-wrap:break-word'>" + Location + "</td>" +
                      "<td class='col-sm-4' align='left' style='word-wrap:break-word'>" + question + "</td></tr>");
    i++;
 
    if( username in helping ){
      new_row.css("background-color", "#99ccff");//  b3ffb3
      if(time_lim > 0){
        var duration = helping[username]["duration"];
        var fields = duration.split(':');
        duration = parseInt(fields[0])*3600 + parseInt(fields[1])*60 + parseInt(fields[2]);
        var time_rem = time_lim*60-duration;

        if(time_rem <= 0){
          new_row.css("background-color", "#fe2b40"); //User is over time limit
	      //$("body").css("background-image", "-webkit-linear-gradient(top, #ff9C00 0%, #fFFFBB 50%");
        }
      }
    }

    if(is_TA) {
      // HELP BUTTON
      if( username in helping ){ //Student is currently being helped
        var TA_helping_them = helping[username]["TA"];
        if(my_username === TA_helping_them){ //The TA is currently helping student
          var help_button = $('<div class="btn-group" role="group"><button class="btn btn-primary" title="Stop Helping"> <i class="fa fa-undo"></i>  </button></div>');
          help_button.click(function(event){
            release_ta(course);
          });
        }
        else{ //The student is currently being helped, but not by this TA, so don't let them end the other TA's help session
          var help_button = $('<div class="btn-group" role="group"><button class="btn btn-primary" title="Stop Helping" disabled=true> <i class="fa fa-undo"></i>  </button></div>');
        }
      }else{ //Student is not being helped
        var help_button = $('<div class="btn-group" role="group"><button class="btn btn-primary" title="Help Student"><i class="glyphicon glyphicon-hand-left"></i></button></div>');
        help_button.click(function(event){//If a TA helps a user, but isn't on duty, put them on duty
          enqueue_ta(course); //Maybe make this cleaner. 
          help_student(course, username);
        });
      }

      // MOVE UP BUTTON
      var increase_button = $('<div class="btn-group" role="group"><button class="btn btn-primary" title="Move Up"> <i class="fa fa-arrow-up"></i>  </button></div>');
      if(row == 0){
        increase_button = $('<div class="btn-group" role="group"><button class="btn btn-primary" title="Move Up" disabled=true> <i class="fa fa-arrow-up"></i>  </button></div>');
      }
      increase_button.click(function(event){
        inc_priority(course, username); 
      });

      // MOVE DOWN BUTTON
      var decrease_button = $('<div class="btn-group" role="group"><button class="btn btn-primary" title="Move Down"> <i class="fa fa-arrow-down"></i>  </button></div>');
      if(row == dataParsed.queue_length -1){
        decrease_button = $('<div class="btn-group" role="group"><button class="btn btn-primary" title="Move Down" disabled=true> <i class="fa fa-arrow-down"></i>  </button></div>');
      }
      decrease_button.click(function(event){
        dec_priority(course, username);
      });

      // REMOVE BUTTON
      var dequeue_button = $('<div class="btn-group" role="group"><button class="btn btn-primary" title="Remove"> <i class="fa fa-close"></i>  </button></div>');
      dequeue_button.click(function(event) {
          dequeue_student(course, username);
      });

      // Create TA button group that spans entire td width and append it to the new row
      var td = $("<td class='col-sm-3'></td>");
      var button_group = $("<div class='btn-group btn-group-justified' role='group' aria-label='...'></div>");
      button_group.append(help_button);
      button_group.append(increase_button);
      button_group.append(decrease_button);
      button_group.append(dequeue_button);
      td.append(button_group);
      new_row.append(td);

    }else{//student

      // BUTTON ONLY RENDERED ON USER'S ROW (ROW DOESN'T RENDER ACROSS ENTIRE BOX IN CHROME AND FIREFOX)
      var td = $("<td class='col-sm-3'></td>");
      if(username === my_username){ // Only add the move down button if it's the user's row
        var decrease_button = $('<div align="right"><button class="btn btn-primary" title="Move Down"> <i class="fa fa-arrow-down"></i>  </button></div>');
        if(row == dataParsed.queue_length -1){
          decrease_button = $('<div align="right"><button class="btn btn-primary" disabled=true title="Move Down"> <i class="fa fa-arrow-down"></i>  </button></div>');
        }
        decrease_button.click(function(event){
          if (confirm("Are you sure you want to move one spot down?")) {
            dec_priority(course, my_username);
          }
        });
        td.append(decrease_button);
      }

      new_row.append(td);
    }

    $('#queue_body').append(new_row);
  }
}


//API Endpoint calls
done = function(data){
  get_queue(course); //reloads the content on the page
}

fail = function(data){
  var httpStatus = data.status;
  var dataString = JSON.stringify(data.responseJSON);
  var dataParsed = JSON.parse(dataString);
  alert(dataParsed["error"]);
}

function open_queue(course){
  var url = "../api/queue/open";
  var posting = $.post( url, { course: course } );
  posting.done(done);
  posting.fail(fail);
}

function close_queue(course){
  var url = "../api/queue/close";
  var posting = $.post( url, { course: course } );
  posting.done(done);
  posting.fail(fail);
}

function freeze_queue(course){
  var url = "../api/queue/freeze";
  var posting = $.post( url, { course: course } );
  posting.done(done);
  posting.fail(fail);
}


function enqueue_student(course, question, Location){
  var url = "../api/queue/enqueue_student";
  var posting = $.post( url, { course: course, question: question, location: Location } );
  posting.done(done);
  posting.fail(fail);
}

/*
 *Students call dequeue_student(course, null) to dequeue themselves
 *TAs call dequeue_student(course, username) to dequeue student
 */
function dequeue_student(course, student){
  var url = "../api/queue/dequeue_student";
  if(student == null){
    posting = $.post( url, { course: course } );
  }
  else{
    posting = $.post( url, { course: course, student: student } );
  }
  posting.done(done);
  posting.fail(fail);
}

function release_ta(course){
  var url = "../api/queue/release_ta";
  var posting = $.post( url, { course: course } );
  posting.done(done);
  posting.fail(fail);
}

function enqueue_ta(course){
  var url = "../api/queue/go_on_duty";
  var posting = $.post( url, { course: course } );
  posting.done(done);
  posting.fail(fail);
}

function dequeue_ta(course){
  var url = "../api/queue/go_off_duty";
  var posting = $.post( url, { course: course } );
  posting.done(done);
  posting.fail(fail);
}

function inc_priority(course, student){
  var url = "../api/queue/move_up";
  var posting = $.post( url, { course: course, student: student } );
  posting.done(done);
  posting.fail(fail);
}

function dec_priority(course, student){
  var url = "../api/queue/move_down";
  var posting = $.post( url, { course: course, student: student } );
  posting.done(done);
  posting.fail(fail);
}

function next_student(course){
  var url = "../api/queue/next_student";
  var posting = $.post( url, { course: course } );
  posting.done(done);
  posting.fail(fail);
}

function help_student(course, username){
  var url = "../api/queue/help_student";
  var posting = $.post( url, { course: course, student: username } );
  posting.done(done);
  posting.fail(fail);
}

function set_limit(course, limit){
  var url = "../api/queue/set_limit";
  var posting = $.post( url, { course: course, time_lim: limit.toString() } );
  posting.done(done);
  posting.fail(fail);
}

function set_cooldown(course, limit){
  var url = "../api/queue/set_cooldown";
  var posting = $.post( url, { course: course, time_lim: limit.toString() } );
  posting.done(done);
  posting.fail(fail);
}

function add_announcement(course, announcement){
  var url = "../api/queue/add_announcement";
  var posting = $.post( url, { course: course, announcement: announcement } );
  posting.done(done);
  posting.fail(fail);
}

function del_announcement(course, announcement_id){
  var url = "../api/queue/del_announcement";
  var posting = $.post( url, { course: course, announcement_id: announcement_id } );
  posting.done(done);
  posting.fail(fail);
}

// source: stackoverflow.com/questions/13898423/javascript-convert-24-hour-time-of-day-string-to-12-hour-time-with-am-pm-and-no
function tConvert (time) {
    // Check correct time format and split into components
    time = time.toString ().match (/^([01]\d|2[0-3])(:)([0-5]\d)(:[0-5]\d)?$/) || [time];

    if (time.length > 1) { // If time format correct
        time = time.slice (1);  // Remove full string match value
        time[5] = +time[0] < 12 ? ' am' : ' pm'; // Set AM/PM
        time[0] = +time[0] % 12 || 12; // Adjust hours
    }
    return time.join (''); // return adjusted time or original string
}

function enrollCourse(course, code) {
  var url = "../api/user/add_course";
  if(code == null){
    var posting = $.post( url, { course: course } );
  }else{
    var posting = $.post( url, { course: course, acc_code: code } );
  }
  posting.done(done);
  posting.fail( function(data){ window.location = '/'; });
}

