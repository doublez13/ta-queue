var new_course;
var url_course_id;
var done_msg
$(document).ready(function(){
  //GET parsing snippet from CHRIS COYIER
  var query = window.location.search.substring(1);
  var vars = query.split("&");
  for (var i=0;i<vars.length;i++) {
    var pair = vars[i].split("=");
    if(pair[0] == "course_id"){
      url_course_id = decodeURIComponent(pair[1]);
      break;
    }
  }
  if(typeof url_course_id === 'undefined'){ //Create new course
    new_course = true;
    document.getElementById("page_title").innerHTML = "New Course";
    document.getElementById("panel_title").innerHTML = "New Course";
    document.getElementById("create_course_button").innerText= "Create Course";
    document.getElementById("delete_course_button").style.display = "none";
    document.getElementById("edit_instr_button").style.display = "none";
    document.getElementById("edit_ta_button").style.display = "none";
    document.getElementById("edit_stud_button").style.display = "none";
    done_msg = "Course successfully created";
  }else{                             //Edit exsisting course
    new_course = false;
    document.getElementById("page_title").innerHTML  = "Edit Course";
    document.getElementById("panel_title").innerHTML = "Edit Course";
    document.getElementById("create_course_button").innerText= "Done";

    get_course(url_course_id);

    $("#delete_course_button").click( delete_course );
    done_msg = "Course successfully modified";
    $("#edit_ta_button").click(function( event ) {
      event.preventDefault();
      window.location = "group_mod?type=ta&course_id="+url_course_id;
    });
    $("#edit_stud_button").click(function( event ) {
      event.preventDefault();
      window.location = "group_mod?type=student&course_id="+url_course_id;
    });
    $("#edit_instr_button").click(function( event ) {
      event.preventDefault();
      window.location = "group_mod?type=instructor&course_id="+url_course_id;
    });
  }
  $("#create_course").submit( create_course );
});

create_course = function( event ) {
  event.preventDefault();

  var $form = $(this);
  var url = "../api/courses";
  var posting = $.post( url, { course_name: $form.find( "input[id='course_name']" ).val(), 
                               depart_pref: $form.find( "input[id='depart_pref']" ).val(),
                               course_num:  $form.find( "input[id='course_num']" ).val(),
                               access_code: $form.find( "input[id='access_code']" ).val(),
                               enabled:     document.getElementById('enabled').checked,
                               generic:     document.getElementById('generic').checked,
                               description: $('#description').val(),
                             } );

  posting.done(function(data){
    alert(done_msg);
    if(new_course){
      new_course_id   = course_name_to_id($form.find( "input[id='course_name']" ).val());
      window.location = "./edit_course?course_id="+new_course_id;
    }else{
      window.location = "/";
    }
  });
  posting.fail(function(data){
    var dataString = JSON.stringify(data.responseJSON);
    var dataParsed = JSON.parse(dataString);
    alert(dataParsed["error"]);
  });
}

delete_course = function( event ){
  event.preventDefault();

  if(confirm("Are you sure you want to delete the course? All data and logs will be wiped.")){
    var del = $.ajax({
                method: "DELETE",
                url: "../api/courses/"+url_course_id,
              });
    del.done(function(data){
      alert("Course successfully deleted");
      window.location = "./courses";
    });
    del.fail(function(data){
      var dataString = JSON.stringify(data.responseJSON);
      var dataParsed = JSON.parse(dataString);
      alert(dataParsed["error"]);
    });
  }
}

function get_course(course_id){
  var url = "../api/courses/"+course_id;
  $.get( url, function(data) {
    var dataString = JSON.stringify(data);
    var dataParsed = JSON.parse(dataString);
    var attributes = ["course_name", "depart_pref", "course_num", "description", "access_code", "enabled", "generic"];
    attributes.forEach(function(attribute){
      if(attribute in dataParsed.parameters){
        if(attribute == "enabled" || attribute == "generic"){
          document.getElementById(attribute).checked = dataParsed.parameters[attribute];
        }else{
          document.getElementById(attribute).value = dataParsed.parameters[attribute];
        }
      }
    });
    //Disable the fields that we don't allow editing
    document.getElementById("course_name").disabled  = true;
    document.getElementById("depart_pref").disabled  = true;
    document.getElementById("course_num").disabled  = true;
    document.getElementById("generic").disabled  = true;
  }).fail(function(data){window.location = "./courses"}); //Silent redirect to course page on error or access denied
}

function course_name_to_id(course_name){
  //Trim any whitespace off the ends here since the backend will when it creates it.
  course_name = course_name.trim();
  $.ajax({
    async: false,
    method: "GET",
    url: "../api/courses"
  }).done(function(data){
    var dataString = JSON.stringify(data);
    var dataParsed = JSON.parse(dataString);
    var allCourses = dataParsed.all_courses;
    course_id = allCourses[course_name]['course_id'];
  });
  return course_id;
}
