var new_course;
$(document).ready(function(){
  //GET parsing snippet from CHRIS COYIER
  var query = window.location.search.substring(1);
  var vars = query.split("&");
  var url_course_name;
  var url_course_id;
  for (var i=0;i<vars.length;i++) {
    var pair = vars[i].split("=");
    if(pair[0] == "course"){
      url_course_name = decodeURIComponent(pair[1]);
      break;
    }
    if(pair[0] == "course_id"){
      url_course_id = decodeURIComponent(pair[1]);
      break;
    }
  }
  if(typeof url_course_name === 'undefined' && typeof url_course_id === 'undefined'){ //Create new course
    new_course = true;
    document.getElementById("page_title").innerHTML = "New Course";
    document.getElementById("panel_title").innerHTML = "New Course";
    document.getElementById("create_course_button").innerText= "Create Course";
    document.getElementById("delete_course_button").style.display = "none";
    document.getElementById("edit_instr_button").style.display = "none";
    document.getElementById("edit_ta_button").style.display = "none";
    document.getElementById("edit_stud_button").style.display = "none";
    $("#create_course").submit( create_course );
    doneMsg = "Course successfully created";
  }else{                             //Edit exsisting course
    new_course = false;
    document.getElementById("page_title").innerHTML  = "Edit Course";
    document.getElementById("panel_title").innerHTML = "Edit Course";
    document.getElementById("course_name").disabled  = true;
    document.getElementById("create_course_button").innerText= "Done";

    if(typeof url_course_id === 'undefined'){
      url_course_id = course_name_to_id(url_course_name);
    }

    get_course(url_course_id);

    $("#create_course").submit( create_course );
    $("#delete_course_button").click( delete_course );
    doneMsg = "Course successfully modified";
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
                               description: $('#description').val(),
                             } );

  var new_course_name = $form.find( "input[id='course_name']" ).val();
  posting.done(function(data){ //Modify the course, then modify the TAs
    if(new_course){
      window.location = "./edit_course?course="+new_course_name;
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
    var del_course_name = document.getElementById("course_name").value
    var del_course_id   = course_name_to_id(del_course_name);
    var del = $.ajax({
                method: "DELETE",
                url: "../api/courses/"+del_course_id,
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
    var attributes = ["course_name", "depart_pref", "course_num", "description", "access_code", "enabled"];
    attributes.forEach(function(attribute){
      if(attribute in dataParsed.parameters){
        if(attribute == "enabled"){
          document.getElementById('enabled').checked = dataParsed.parameters[attribute];
        }else{
          document.getElementById(attribute).value = dataParsed.parameters[attribute];
        }
      }
    });
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
    var allCourses = dataParsed.all_courses
    course_id = allCourses[course_name]['course_id'];
  });
  return course_id;
}
