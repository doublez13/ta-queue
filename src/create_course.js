$(document).ready(function(){
  //GET parsing snippet from CHRIS COYIER
  var query = window.location.search.substring(1);
  var vars = query.split("&");
  var url_course_name;
  for (var i=0;i<vars.length;i++) {
    var pair = vars[i].split("=");
    if(pair[0] == "course"){
      url_course_name = decodeURIComponent(pair[1]);
      break;
    }
  }
  if(typeof url_course_name === 'undefined'){ //Create new course
    document.getElementById("page_title").innerHTML = "New Course";
    document.getElementById("panel_title").innerHTML = "New Course";
    document.getElementById("create_course_button").innerText= "Create Course";
    document.getElementById("delete_course_button").style.display = "none";
    $("#create_course").submit( create_course );
    doneMsg = "Course successfully created";
  }else{                             //Edit exsisting course
    document.getElementById("page_title").innerHTML  = "Edit Course";
    document.getElementById("panel_title").innerHTML = "Edit Course";
    document.getElementById("course_name").disabled  = true;
    document.getElementById("create_course_button").innerText= "Edit Course";
   
    var url_course_id = course_name_to_id(url_course_name);
    get_course(url_course_id);
    get_TAs(url_course_id); 

    $("#create_course").submit( create_course );
    $("#delete_course_button").click( delete_course );
    doneMsg = "Course successfully modified";
  }
});

create_course = function( event ) {
  event.preventDefault();

  var $form = $(this);
  var url = "../api/courses";
  var posting = $.post( url, { course_name: $form.find( "input[id='course_name']" ).val(), 
                               depart_pref: $form.find( "input[id='depart_pref']" ).val(),
                               course_num:  $form.find( "input[id='course_num']" ).val(),
                               professor:   $form.find( "input[id='professor']" ).val(),
                               access_code: $form.find( "input[id='access_code']" ).val(),
                               enabled:     document.getElementById('enabled').checked,
                               description: $('#description').val(),
                             } );

  var new_course_name = $form.find( "input[id='course_name']" ).val();
  posting.done(function(data){ //Modify the course, then modify the TAs
    var new_course_id = course_name_to_id(new_course_name);
    edit_TAs(new_course_id)
  });
  posting.fail(function(data){
    var dataString = JSON.stringify(data.responseJSON);
    var dataParsed = JSON.parse(dataString);
    var error_msg  = dataParsed["error"];
    if(error_msg == "User does not exist"){
      alert("Instructor does not exist"); //Little bit more specific
    }
    else{
      alert(dataParsed["error"]);
    }
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
    var attributes = ["course_name", "depart_pref", "course_num", "description", "professor", "access_code", "enabled"];
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

//TODO: Fix the ugliness in these two functions!!
//Get the TA List
function get_TAs(course_id){
  var url = "../api/courses/"+course_id+'/ta';
  $.get( url, function(data) {
    var dataString = JSON.stringify(data);
    var dataParsed = JSON.parse(dataString);
    dataParsed.TAs.forEach(function(TA){
      TAs.value += TA + ' ';
    });
  }).fail(function(data){window.location = "./courses"}); //Silent redirect to course page on error or access denied
}

//Update the TA List
//TODO: Courses can't have forward slashes in names
function edit_TAs(course_id){
  var TAString = document.getElementById("TAs").value.trim();
  var newTAs   = TAString.split(" ").filter(v=>v!='');

  //Get all current TAs
  var $url = "../api/courses/"+course_id+"/ta";
  var $get = $.get( $url );
  $get.done(function(data){
    var dataString = JSON.stringify(data);
    var dataParsed = JSON.parse(dataString);
    var currentTAs = dataParsed.TAs;

    //Diff the lists
    var add = [];
    var del = [];
    currentTAs.forEach(function(item, index) {
      if(newTAs.indexOf(item) == -1){
        del.push(item);
      }
    });

    newTAs.forEach(function(item, index) { 
      if(currentTAs.indexOf(item) == -1){
        add.push(item);
      } 
    });
    //TODO: This is such a hack
    //Instead of plumbing a professor role into the model and controller,
    //we simply add the professor to the TA list so they'll automatically
    //inherit TA permissions.
    //Currently the professor attribute isn't used in the backend, but I'd 
    //like to eventually allow professors to edit their own course, and 
    //have access to a wider range of stats
    var professor = document.getElementById("professor").value;
    add.push(professor);

    //Do any removal if necessary
    del.forEach( function(item, index) {
      $.ajax({
        async: false,
        method: "DELETE",
        url: "../api/user/"+item+"/courses/"+course_id+"/ta"
      });
    });
    
    //Do any adding if necessary
    var error = 0;
    add.forEach( function(item, index) {
      $.ajax({
        async: false,
        method: "POST",
        url: "../api/user/"+item+"/courses/"+course_id+"/ta"
      }).fail(function(data){
         var dataString = JSON.stringify(data.responseJSON);
         var dataParsed = JSON.parse(dataString);
         alert("Adding " + item + ": " +dataParsed["error"]);
         error = 1;
      });
    });
    if(!error){
      alert(doneMsg);
      window.location = "./courses";
    }

  });
}
