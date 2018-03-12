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
    window.location ='./my_classes.php';
  }

  dialog = $( "#dialog-form" ).dialog({
    autoOpen: false,
    height: 400,
    width: 350,
    modal: true,
    buttons: {
      "Enter Queue": function() {
	  lab_location = document.getElementById("location").value;
	  question = document.getElementById("question").value;
	  enqueue_student(course, question, lab_location);
	  dialog.dialog( "close" );
      },
      Cancel: function() {
        dialog.dialog( "close" );
      }
    }
  });
  $("#duty_button").hide();
  $("#state_button").hide();
  $("#freeze_button").hide();
  $("#time_form").hide(); 
  $("#join_button").hide();
  $("#new_ann").hide();
  $("#ann_button").hide(); 
  start();
});

function start(){
  $("#title").text(course+' Queue');
  var url = "../api/user/get_info.php";
  var get_req = $.get( url);
  var done = function(data){
    var dataString = JSON.stringify(data);
    var dataParsed = JSON.parse(dataString);
    my_username = dataParsed.student_info["username"];
    var url = "../api/user/my_classes.php";
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
  get_req.done(done);
}

function get_queue(course) {
  var url = "../api/queue/get_queue.php";
  var posting = $.post( url, { course: course } );
  posting.done(render_view);
}



//This function renders the view from the data
var render_view = function(data) {
  var dataString = JSON.stringify(data);
  var dataParsed = JSON.parse(dataString);
  if(dataParsed.error){
    alert(dataParsed.error);
    return;
  }

  //Render the top stats: state, time, length
  render_stats(dataParsed);

  //Render the announcements box
  render_ann_box(dataParsed.announce);

  render_ta_table(dataParsed.TAs)
  if(is_TA){
    render_queue_table(dataParsed, "ta");
    render_ta_view(dataParsed)
  }else{
    render_queue_table(dataParsed, "student");
    render_student_view(dataParsed)
  }

}

function render_stats(dataParsed){
 var state = dataParsed.state.charAt(0).toUpperCase() + dataParsed.state.slice(1);
  $("#queue_state").text("State: "+state);
  if(dataParsed.time_lim >0){
     $("#time_limit").text("Time Limit: " + dataParsed.time_lim + " Minutes");
  }else{
     $("#time_limit").text("Time Limit: None");
  }
  $("#in_queue").text("Queue Length: " + dataParsed.queue_length);
}

function render_ann_box(anns){
  $("#anns tr").remove();
  $('#anns').append("<tr> <th class='col-sm-1' align='left' style='padding-left:10px; text-decoration:underline;'>Date</th> <th class='col-sm-6' align='left' style='padding-left:0px; text-decoration:underline;'>Announcement</th> </tr>");
  for(ann in anns){
    var timestamp    = anns[ann]["tmstmp"].split(" ")[0];
    var announcement = anns[ann]["announcement"]; 
    var new_row = $('<tr>  <td style="padding-left:10px;"><b>'+timestamp+':</b></td>  <td><b>'+announcement+'</b></td> </tr>');
    $('#anns').append(new_row);
  }
  if(is_TA){
    $("#ann_button").unbind("click");
    $("#new_ann").show();
    $("#ann_button").show();
    $("#ann_button").click(function( event ) {
      event.preventDefault();
      var announcement = document.getElementById("new_ann").value;
      add_announcement(course, announcement)
    });
  }
}

//Shows the TAs that are on duty
function render_ta_table(TAs){
  $("#ta_on_duty h4").remove();
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

  var queue_state = dataParsed.state;
  if(queue_state == "closed"){
    document.getElementById("state_button").style.background='ForestGreen';
    $("#state_button").text("OPEN QUEUE");
    $("#state_button").click(function( event ) {
      event.preventDefault();
      open_queue(course);
    });
    $("#duty_button").hide();
    $("#freeze_button").hide();
    $("#time_form").hide();
  }else{ //open or frozen
    document.getElementById("state_button").style.background='FireBrick';
    $("#state_button").text("CLOSE QUEUE");
    $("#state_button").click(function( event ) {
      event.preventDefault();
      close_queue(course);
    });
   
    if(queue_state == "open"){ 
      //$("body").css("background-image", "-webkit-linear-gradient(top, #808080 0%, #FFFFFF 50%");
      document.getElementById("freeze_button").style.background='#1B4F72';
      $("#freeze_button").text("FREEZE QUEUE");
      $("#freeze_button").click(function( event ) {
        event.preventDefault();
        freeze_queue(course);
      });
    }else{ //frozen
      //$("body").css("background-image", "-webkit-linear-gradient(top, #075685 0%, #FFF 50%");
      document.getElementById("freeze_button").style.background='Orange';
      $("#freeze_button").text("RESUME QUEUE");
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
      document.getElementById("duty_button").style.background='ForestGreen';
      $("#duty_button").text("GO ON DUTY");
      $("#duty_button").click(function(event){
         event.preventDefault();
	 enqueue_ta(course); 
      });
    }
    else{
      document.getElementById("duty_button").style.background='FireBrick';
      $("#duty_button").text("GO OFF DUTY");
      $("#duty_button").click(function(event){
	 event.preventDefault();
         dequeue_ta(course); 
      });
    }
    $("#duty_button").show();
    $("#freeze_button").show();
    $("#time_form").show();
    $("#time_form").submit(function(event){
      event.preventDefault();
      var limit = $(this).find( "input[id='time_limit_input']" ).val();
      set_limit(course, limit);
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
    $("#join_button").text("Enter Queue");
    $("#join_button").show();
    $("#join_button").click(function( event ) {
      event.preventDefault();
      dialog.dialog( "open" );
    });
  }
  else{ //In queue
    $("#join_button").text("Leave Queue");
    $("#join_button").show();
    $("#join_button").click(function( event ) {
      event.preventDefault();
      dequeue_student(course);
    });
  }
}

//Displays the queue table
function render_queue_table(dataParsed, role){
  var queue = dataParsed.queue;
  var TAs   = dataParsed.TAs;
  $("#queue tr").remove();
  $('#queue').append("<tr> <th class='col-sm-1' align='left' style='padding-left:10px; text-decoration:underline;'>Pos.</th>"+ 
                           "<th class='col-sm-2' align='left' style='padding-left:0px; text-decoration:underline;'>Student</th>"+ 
                           "<th class='col-sm-1' align='left' style='padding-left:0px; text-decoration:underline;'>Location</th>"+ 
                           "<th class='col-sm-4' align='left' style='padding-left:5px; text-decoration:underline;'>Question</th> </tr>");
 
  var helping = {};
  for(TA in TAs ){
    if(TAs[TA].helping != null){
      helping[TAs[TA].helping] = TAs[TA].duration;  
    }
  }
  
  var time_lim = dataParsed.time_lim;

  var i = 1;
  for(row in queue){
    let username  = queue[row].username;
    let full_name = queue[row].full_name;
    var question  = queue[row].question;
    var Location  = queue[row].location;
    var new_row = $("<tr> <td style='padding-left: 10px;'>"+ i +"</td> <td>" + full_name + "</td> <td>" + Location + "</td> <td style='padding-left:5px;'>" + question + "</td> </tr>");
    i++;   
 
    if( username in helping ){
      new_row.css("background-color", "#b3ffb3");
      if(time_lim > 0){
        var duration = helping[username];
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
      var dequeue_button = $('<button class="btn btn-primary"> <i class="fa fa-close"></i>  </button>');
      dequeue_button.click(function(event) {
        dequeue_student(course, username);
      });
      if( username in helping ){
        var help_button = $('<button class="btn btn-primary"> <i class="fa fa-undo"></i>  </button>');
        help_button.click(function(event){
          release_ta(course);
        });
      }else{
        var help_button = $('<button class="btn btn-primary"><span> <i class="fa fa-clipboard"></i>  </span> </button>');
        help_button.click(function(event){//If a TA helps a user, but isn't on duty, put them on duty
          enqueue_ta(course); //Maybe make this cleaner. 
          help_student(course, username);
        });
      }
      var increase_button = $('<button class="btn btn-primary"> <i class="fa fa-arrow-up"></i>  </button>');
      if(row == 0){
        increase_button = $('<button class="btn btn-primary" disabled=true> <i class="fa fa-arrow-up"></i>  </button>');
      }
      increase_button.click(function(event){
        inc_priority(course, username); 
      });

      var decrease_button = $('<button class="btn btn-primary"> <i class="fa fa-arrow-down"></i>  </button>');
      if(row == dataParsed.queue_length -1){
        decrease_button = $('<button class="btn btn-primary" disabled=true> <i class="fa fa-arrow-down"></i>  </button>');
      }
      decrease_button.click(function(event){
        dec_priority(course, username);
      });

      new_row.append("<td>");
      new_row.append(help_button);
      new_row.append("</td>");

      new_row.append("<td>");
      new_row.append(dequeue_button);
      new_row.append("</td>");

      new_row.append("<td>");
      new_row.append(increase_button);
      new_row.append("</td>");

      new_row.append("<td>");
      new_row.append(decrease_button);
      new_row.append("</td>");
    }
    $('#queue').append(new_row);
  }
}

//API Endpoint calls
done = function(data){
  var dataString = JSON.stringify(data);
  var dataParsed = JSON.parse(dataString);
  if(dataParsed.error){
    alert(dataParsed["error"]);
  }else{
    get_queue(course); //reloads the content on the page
  }
}
function open_queue(course){
  var url = "../api/queue/open.php";
  var posting = $.post( url, { course: course } );
  posting.done(done);
}

function close_queue(course){
  var url = "../api/queue/close.php";
  var posting = $.post( url, { course: course } );
  posting.done(done);
}

function freeze_queue(course){
  var url = "../api/queue/freeze.php";
  var posting = $.post( url, { course: course } );
  posting.done(done);
}


function enqueue_student(course, question, Location){
  var url = "../api/queue/enqueue_student.php";
  var posting = $.post( url, { course: course, question: question, location: Location } );
  posting.done(done);
}

/*
 *Students call dequeue_student(course, null) to dequeue themselves
 *TAs call dequeue_student(course, username) to dequeue student
 */
function dequeue_student(course, username){
  var url = "../api/queue/dequeue_student.php";
  if(username == null){
    posting = $.post( url, { course: course } );
  }
  else{
    posting = $.post( url, { course: course, username: username } );
  }
  posting.done(done);
}

function release_ta(course){
  var url = "../api/queue/release_ta.php";
  var posting = $.post( url, { course: course } );
  posting.done(done);
}

function enqueue_ta(course){
  var url = "../api/queue/enqueue_ta.php";
  var posting = $.post( url, { course: course } );
  posting.done(done);
}

function dequeue_ta(course){
  var url = "../api/queue/dequeue_ta.php";
  var posting = $.post( url, { course: course } );
  posting.done(done);
}

function inc_priority(course, student){
  var url = "../api/queue/inc_priority.php";
  var posting = $.post( url, { course: course, student: student } );
  posting.done(done);
}

function dec_priority(course, student){
  var url = "../api/queue/dec_priority.php";
  var posting = $.post( url, { course: course, student: student } );
  posting.done(done);
}

next_student = function(course){
  var url = "../api/queue/next_student.php";
  var posting = $.post( url, { course: course } );
  posting.done(done);
}

function help_student(course, username){
  var url = "../api/queue/help_student.php";
  var posting = $.post( url, { course: course, student: username } );
  posting.done(done);
}

function set_limit(course, limit){
  var url = "../api/queue/set_limit.php";
  var posting = $.post( url, { course: course, time_lim: limit.toString() } );
  posting.done(done);
}

function add_announcement(course, announcement){
  var url = "../api/queue/add_announcement.php";
  var posting = $.post( url, { course: course, announcement: announcement } );
  posting.done(done);
}

function del_announcement(course, announcement_id){
  var url = "../api/queue/del_announcement.php";
  var posting = $.post( url, { course: course, announcement_id: announcement_id } );
  posting.done(done);
}
