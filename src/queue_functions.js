var dialog;
var form;
var my_username;
var first_name;
var last_name;
var course_id;
var is_admin = localStorage.is_admin == "true";

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
	      var lab_location = document.getElementById("location").value;
	      var question = document.getElementById("question").value;
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
	        enqueue_student(course_id, question, lab_location);
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

  $("#title").text(course);
  my_username = localStorage.username;
  first_name  = localStorage.first_name;
  last_name   = localStorage.last_name;

  var url = "../api/user/"+my_username+"/courses";
  var get_req = $.get(url);
  var done = function(data){
    var dataString = JSON.stringify(data);
    var dataParsed = JSON.parse(dataString);
    is_TA = false;
    if($.inArray(course, Object.keys(dataParsed['ta_courses'])) != -1){
      is_TA = true;
    }

    var url = "../api/courses";
    var get_req = $.get(url);
    var done = function(data){
      var dataString = JSON.stringify(data);
      var dataParsed = JSON.parse(dataString);
      //Check if the course they're requesting exists
      if(course in dataParsed['all_courses']){
        course_id = dataParsed['all_courses'][course]['course_id'];
      }else{
        window.location = '/';
      }
      get_queue(course_id);
      setInterval(get_queue, 5000, course_id);
    };
    get_req.done(done);
  };
  get_req.done(done); 
});

//This function is called every X seconds,
//and is what updates the dataParsed  
function get_queue(course_id) {
  var url = "../api/queue/"+course_id;
  var posting = $.get(url);
  var done = function(data){
    var dataString = JSON.stringify(data);
    var dataParsed = JSON.parse(dataString);
    renderView(dataParsed);
  };
  var fail = function(data){
    var dataString = JSON.stringify(data.responseJSON);
    var dataParsed = JSON.parse(dataString);
    if(dataParsed.error){
      var error = dataParsed.error;
      //If they're not enrolled in the course, attempt to
      //enroll them with no access code
      if(error == "Forbidden"){
        enrollCourse(course_id, null);
      }else{
        alert(dataParsed.error);
        window.location = '/';
      }
    }
  };
  posting.done(done);
  posting.fail(fail);
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
    render_ta_view(dataParsed);
  }else{
    render_student_view(dataParsed);
  }
}

function render_stats(dataParsed){
  var state  = dataParsed.state.charAt(0).toUpperCase() + dataParsed.state.slice(1);
  var length = dataParsed.queue_length; 

  // SET AND COLOR QUEUE STATE
  $("#queue_state").empty();
  $("#queue_state").append("<span>State: </span>")
  $("#queue_state").append("<span id='state'><b>"+state+"</b></span>")
  if(state == "Open"){
    $("#state").css('color', 'green');
    document.title = "("+length+") TA Help Queue"
  }
  else if(state == "Closed"){
    $("#state").css('color', 'red');
    document.title = "TA Help Queue"
  }
  else if(state == "Frozen"){
    $("#state").css('color', 'blue');
    document.title = "("+length+") TA Help Queue"
  }

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
  if(dataParsed.cooldown > 0){
    $("#cd_time").append("<b>"+dataParsed.cooldown+" Minutes</b>");
  }
  else
    $("#cd_time").append("None");

  // SET QUEUE LENGTH
  $("#in_queue").text("Length: " + length);
}

function render_ann_box(anns){
  $("#anns_body").empty();
  $('#anns_body').append('<tr class="flex" style="background: none;"> ' +
                           '<th class="flex-noShrink" style="width:110px;">Date</th>' +
                           '<th class="flex-noShrink" style="width:100px;">Time</th>' +
                           '<th class="flex-noShrink" style="width:180px;">Poster</th>' +
                           '<th>Announcement</th> </tr>');
  var ann;
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
        del_announcement(course_id, announcement_id)
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
        add_announcement(course_id, announcement)
      }
    });
  }
}

//Shows the TAs that are on duty
function render_ta_table(TAs){
  $("#ta_on_duty h4").remove();
  if(TAs.length < 2)
    $("#tas_header").text("TA on Duty");
  else
    $("#tas_header").text("TAs on Duty");
  var TA;
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
      open_queue(course_id);
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
          close_queue(course_id);
        }
      }
      else
        close_queue(course_id);
    });
   
    if(queue_state == "open"){ 
      document.getElementById("freeze_button").className="btn btn-primary";
      document.getElementById("freeze_button").title="Prevent new entries";
      $("#freeze_button").text("Freeze Queue");
      $("#freeze_button").click(function( event ) {
        event.preventDefault();
        freeze_queue(course_id);
      });
    }else{ //frozen
      document.getElementById("freeze_button").className="btn btn-warning";
      document.getElementById("freeze_button").title="Allow new entries";
      $("#freeze_button").text("Resume Queue");
      $("#freeze_button").click(function( event ) {
        event.preventDefault();
        open_queue(course_id);
      });
    }

    if(!(my_username in dataParsed.TAs)) {//Checks if the TA is on duty
      document.getElementById("duty_button").className="btn btn-success";
      $("#duty_button").text("Go On Duty");
      $("#duty_button").click(function(event){
        event.preventDefault();
        enqueue_ta(course_id);
      });
    }
    else{
      document.getElementById("duty_button").className="btn btn-danger";
      $("#duty_button").text("Go Off Duty");
      $("#duty_button").click(function(event){
	    event.preventDefault();
	    dequeue_ta(course_id);
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
      set_limit(course_id, limit);
    });

    // Don't refresh while editing
    if (!$("#cooldown_input").is(":focus")) {
      $("#cooldown_input").val(dataParsed.cooldown);
    }
    $("#cooldown_form").show();
    $("#cooldown_form").submit(function(event){
      event.preventDefault();
      var limit = $(this).find( "input[id='cooldown_input']" ).val();
      set_cooldown(course_id, limit);
    });
  }
  $("#state_button").show();
}

//View for students
//NOTE: admin users that are NOT registered as a
//TA for the course get this view.
function render_student_view(dataParsed){
  var queue = dataParsed.queue;
  
  var in_queue = my_username in queue;
  var state = dataParsed.state; 
  if(state == "closed" || (state == "frozen" && !in_queue )){
    $("#join_button").hide();
    return;
  }

  //Admin users are able to view any queue.
  //If they're not registered as a TA, they'll
  //be presented with the student view, but since
  //they're not enrolled as a student, the backend
  //won't allow them to enter the queue, so let's
  //just disable the button.
  if(is_admin){
    $("#join_button").hide();
    return;
  }

  $("#join_button").unbind("click");
  if(!in_queue){ //Student not in queue
    document.getElementById("join_button").className="margin-top-5 btn btn-success";
    $("#join_button").text("Enter Queue");
    $("#join_button").click(function( event ) {
      event.preventDefault();
      dialog.dialog( "open" );
    });
  }
  else{ //Student in queue
    document.getElementById("join_button").className="margin-top-5 btn btn-danger";
    $("#join_button").text("Exit Queue");
    $("#join_button").click(function( event ) {
      event.preventDefault();
      if (confirm("Are you sure you want to exit the queue?")) {
        dequeue_student(course_id, my_username);
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
                            "<th class='col-sm-2' align='left' style='word-wrap: break-word'>Question</th>" +
                            "<th class='col-sm-2' align='left' style='word-wrap: break-word'>TA</th>" +
                            "<th class='col-sm-3'></th> </tr>");
  var helping = {};
  var TA;
  for(TA in TAs ){
    if(TAs[TA].helping != null){
      helping[TAs[TA].helping] = {}; //Maps student being helped to info about their session in the queue
      helping[TAs[TA].helping]["duration"] = TAs[TA].duration;  //Time student has been helped
      helping[TAs[TA].helping]["TA"]       = TA;                //Username of TA helping student
      helping[TAs[TA].helping]["TA_full"]  = TAs[TA].full_name;  //Full name of TA helping student
    }
  }
  
  
  var time_lim = dataParsed.time_lim;

  var i = 1; //NOTE: The indexing starts at 1
  var row;
  for(row in queue){
    let username  = row;
    let full_name = queue[row].full_name;
    var Location  = queue[row].location;
    var question  = queue[row].question;
    var new_row = $("<tr>" +
                      "<td class='col-sm-1' align='left'>" + i + "</td>" +
                      "<td class='col-sm-2' align='left' style='word-wrap:break-word'>" + full_name + "</td>" +
                      "<td class='col-sm-2' align='left' style='word-wrap:break-word'>" + Location + "</td>" +
                      "<td class='col-sm-2' align='left' style='word-wrap:break-word'>" + question + "</td>" +
                      "</tr>");

    var TA_full = ""; 
    if( username in helping ){
      new_row.css("background-color", "#99ccff");//  b3ffb3
      TA_full = helping[username]["TA_full"];
      if(time_lim > 0){
        var duration = helping[username]["duration"];
        var fields = duration.split(':');
        duration = parseInt(fields[0])*3600 + parseInt(fields[1])*60 + parseInt(fields[2]);
        var time_rem = time_lim*60-duration;

        if(time_rem <= 0){
          new_row.css("background-color", "#fe2b40"); //User is over time limit
        }
      }
    }
    var TA_td = "<td class='col-sm-2' align='left' style='word-wrap:break-word'>" + TA_full + "</td>";
    new_row.append(TA_td);

    if(is_TA) {
      // HELP BUTTON
      if( username in helping ){ //Student is currently being helped
        var TA_helping_them = helping[username]["TA"];
        if(my_username === TA_helping_them){ //The TA is currently helping student
          var help_button = $('<div class="btn-group" role="group"><button class="btn btn-primary" title="Stop Helping"> <i class="fa fa-undo"></i>  </button></div>');
          help_button.click(function(event){
            release_ta(course_id);
          });
        }
        else{ //The student is currently being helped, but not by this TA, so don't let them end the other TA's help session
          var help_button = $('<div class="btn-group" role="group"><button class="btn btn-primary" title="Stop Helping" disabled=true> <i class="fa fa-undo"></i>  </button></div>');
        }
      }else{ //Student is not being helped
        var help_button = $('<div class="btn-group" role="group"><button class="btn btn-primary" title="Help Student"><i class="glyphicon glyphicon-hand-left"></i></button></div>');
        help_button.click(function(event){//If a TA helps a user, but isn't on duty, put them on duty
          enqueue_ta(course_id); //TODO:Race condition 
          help_student(course_id, username);
        });
      }

      // MOVE UP BUTTON
      var increase_button = $('<div class="btn-group" role="group"><button class="btn btn-primary" title="Move Up"> <i class="fa fa-arrow-up"></i>  </button></div>');
      if(i == 1){
        increase_button = $('<div class="btn-group" role="group"><button class="btn btn-primary" title="Move Up" disabled=true> <i class="fa fa-arrow-up"></i>  </button></div>');
      }
      increase_button.click(function(event){
        inc_priority(course_id, username); 
      });

      // MOVE DOWN BUTTON
      var decrease_button = $('<div class="btn-group" role="group"><button class="btn btn-primary" title="Move Down"> <i class="fa fa-arrow-down"></i>  </button></div>');
      if(i == dataParsed.queue_length){
        decrease_button = $('<div class="btn-group" role="group"><button class="btn btn-primary" title="Move Down" disabled=true> <i class="fa fa-arrow-down"></i>  </button></div>');
      }
      decrease_button.click(function(event){
        dec_priority(course_id, username);
      });

      // REMOVE BUTTON
      var dequeue_button = $('<div class="btn-group" role="group"><button class="btn btn-primary" title="Remove"> <i class="fa fa-close"></i>  </button></div>');
      dequeue_button.click(function(event) {
          dequeue_student(course_id, username);
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
        if(i == dataParsed.queue_length){
          decrease_button = $('<div align="right"><button class="btn btn-primary" disabled=true title="Move Down"> <i class="fa fa-arrow-down"></i>  </button></div>');
        }
        decrease_button.click(function(event){
          if (confirm("Are you sure you want to move one spot down?")) {
            dec_priority(course_id, my_username);
          }
        });
        td.append(decrease_button);
      }

      new_row.append(td);
    }

    $('#queue_body').append(new_row);
    i++
  }
}


//API Endpoint calls
done = function(data){
  get_queue(course_id); //reloads the content on the page
};

fail = function(data){
  var dataString = JSON.stringify(data.responseJSON);
  var dataParsed = JSON.parse(dataString);
  alert(dataParsed["error"]);
};

function open_queue(course_id){
  var url = "../api/queue/"+course_id+"/state";
  var posting = $.post( url, { state: "open" } );
  posting.done(done);
  posting.fail(fail);
}

function close_queue(course_id){
  var url = "../api/queue/"+course_id+"/state";
  var posting = $.post( url, { state: "closed" } );
  posting.done(done);
  posting.fail(fail);
}

function freeze_queue(course_id){
  var url = "../api/queue/"+course_id+"/state";
  var posting = $.post( url, { state: "frozen" } );
  posting.done(done);
  posting.fail(fail);
}


function enqueue_student(course_id, question, Location){
  var url = "../api/queue/"+course_id+"/student";
  var posting = $.post( url, { question: question, location: Location } );
  posting.done(done);
  posting.fail(fail);
}

/*
 *Students call dequeue_student(course_id, null) to dequeue themselves
 *TAs call dequeue_student(course_id, username) to dequeue student
 */
function dequeue_student(course_id, student){
  var url = "../api/queue/"+course_id+"/student/"+student;
  var del = $.ajax({
              method: "DELETE",
              url: url,
            });
  del.done(done);
  del.fail(fail);
}

function release_ta(course_id){
  var url = "../api/queue/"+course_id+"/ta";
  var posting = $.post( url );
  posting.done(done);
  posting.fail(fail);
}

function enqueue_ta(course_id){
  var url = "../api/queue/"+course_id+"/ta";
  var posting = $.post( url );
  posting.done(done);
  posting.fail(fail);
}

function dequeue_ta(course_id){
  var url = "../api/queue/"+course_id+"/ta";
  var del = $.ajax({
              method: "DELETE",
              url: url,
            });
  del.done(done);
  del.fail(fail);
}

function inc_priority(course_id, student){
  var url = "../api/queue/"+course_id+"/student/"+student+"/position";
  var posting = $.post( url, { direction: "up" } );
  posting.done(done);
  posting.fail(fail);
}

function dec_priority(course_id, student){
  var url = "../api/queue/"+course_id+"/student/"+student+"/position";
  var posting = $.post( url, { direction: "down" } );
  posting.done(done);
  posting.fail(fail);
}

function help_student(course_id, username){
  var url = "../api/queue/"+course_id+"/student/"+username+"/help";
  var posting = $.post( url );
  posting.done(done);
  posting.fail(fail);
}

function set_limit(course_id, limit){
  var url = "../api/queue/"+course_id+"/settings";
  var posting = $.post( url, { setting: "time_lim", time_lim: limit.toString() } );
  posting.done(done);
  posting.fail(fail);
}

function set_cooldown(course_id, limit){
  var url = "../api/queue/"+course_id+"/settings";
  var posting = $.post( url, { setting: "cooldown", time_lim: limit.toString() } );
  posting.done(done);
  posting.fail(fail);
}

function add_announcement(course_id, announcement){
  var url = "../api/queue/"+course_id+"/announcements";
  var posting = $.post( url, { announcement: announcement } );
  posting.done(done);
  posting.fail(fail);
}

function del_announcement(course_id, announcement_id){
  var del = $.ajax({
              method: "DELETE",
              url: "../api/queue/"+course_id+"/announcements/"+announcement_id,
            });
  del.done(done);
  del.fail(fail);
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

function enrollCourse(course_id, code) {
  var url = "../api/user/"+my_username+"/courses/"+course_id+"/student";
  if(code == null){
    var posting = $.post( url );
  }else{
    var posting = $.post( url, { acc_code: code } );
  }
  posting.done(done);
  posting.fail( function(data){ window.location = '/'; });
}
