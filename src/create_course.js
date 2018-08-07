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
  if(typeof course === 'undefined'){ //Create new course
    document.getElementById("page_title").innerHTML = "New Course";
    document.getElementById("panel_title").innerHTML = "New Course";
    document.getElementById("create_course_button").innerText= "Create Course";
    document.getElementById("delete_course_button").style.display = "none";
    $("#create_course").submit( create_course );
  }
  else{                              //Edit exsisting course
    document.getElementById("page_title").innerHTML  = "Edit Course";
    document.getElementById("panel_title").innerHTML = "Edit Course";
    document.getElementById("course_name").disabled  = true;
    document.getElementById("create_course_button").innerText= "Edit Course";
    
    get_course();
    get_TAs(); 

    $("#create_course").submit( create_course );
    $("#delete_course_button").click( delete_course );
  }
});

create_course = function( event ) {
  event.preventDefault();

  var $form = $(this);
  url = "../api/courses";
  var posting = $.post( url, { course_name: $form.find( "input[id='course_name']" ).val(), 
                               depart_pref: $form.find( "input[id='depart_pref']" ).val(),
                               course_num:  $form.find( "input[id='course_num']" ).val(),
                               professor:   $form.find( "input[id='professor']" ).val(),
                               access_code: $form.find( "input[id='access_code']" ).val(),
                               description: $('#description').val(),
                             } );

  posting.done(function(data){
    edit_TAs()
  });
  posting.fail(function(data){
    var dataString = JSON.stringify(data.responseJSON);
    var dataParsed = JSON.parse(dataString);
    var error_msg = dataParsed["error"];
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
    var del = $.ajax({
                method: "DELETE",
                url: "../api/courses/"+course,
              });
    del.done(function(data){
      alert("Course deleted successfully!");
      window.location = "./courses";
    });
    del.fail(function(data){
      var dataString = JSON.stringify(data.responseJSON);
      var dataParsed = JSON.parse(dataString);
      alert(dataParsed["error"]);
    });
  }
}

function get_course(){
  url = "../api/courses/"+course;
  var get = $.get( url, function(data) {
    var dataString = JSON.stringify(data);
    var dataParsed = JSON.parse(dataString);
    var attributes = ["course_name", "depart_pref", "course_num", "description", "professor", "access_code"];
    attributes.forEach(function(attribute){
      if(attribute in dataParsed.parameters){
        document.getElementById(attribute).value  = dataParsed.parameters[attribute]
      }
    });
  }).fail(function(data){window.location = "./courses"}); //Silent redirect to course page on error or access denied
}


//TODO: Fix the ugliness in these two functions!!
//Get the TA List
function get_TAs(){
  url = "../api/courses/"+course+'/ta';
  var get = $.get( url, function(data) {
    var dataString = JSON.stringify(data);
    var dataParsed = JSON.parse(dataString);
    dataParsed.TAs.forEach(function(TA){
      TAs.value += ' ' + TA;
    });
  }).fail(function(data){window.location = "./courses"}); //Silent redirect to course page on error or access denied
}

//Update the TA List
function edit_TAs(){
  var TAString = document.getElementById("TAs").value.trim();
  var course   = document.getElementById("course_name").value.trim();
  var newTAs   = TAString.split(" ");

  //Get all current TAs
  var $url = "../api/courses/"+course+"/ta";
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

    //Do any removal if necessary
    del.forEach( function(item, index) {
      $.ajax({
        method: "DELETE",
        url: "../api/user/"+item+"/courses/"+course+"/ta"
      });
    });

    //Do any adding if necessary
    var error = 0;
    add.forEach( function(item, index) {
      $.ajax({
        async: false,
        method: "POST",
        url: "../api/user/"+item+"/courses/"+course+"/ta"
      }).fail(function(data){
         var dataString = JSON.stringify(data.responseJSON);
         var dataParsed = JSON.parse(dataString);
         alert("Adding " + item + ": " +dataParsed["error"]);
         error = 1;
      });
    });
    if(!error){
      alert("Course Created Successfully!");
      window.location = "./courses";
    }

  });
}
