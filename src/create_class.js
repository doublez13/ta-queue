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
    document.getElementById("create_class_button").innerText= "Create Course";
    document.getElementById("delete_class_button").style.display = "none";
    $("#create_class").submit( create_class );
  }
  else{                              //Edit exsisting course
    document.getElementById("page_title").innerHTML  = "Edit Course";
    document.getElementById("panel_title").innerHTML = "Edit Course";
    document.getElementById("course_name").disabled  = true;

    url = "../api/courses/"+course;
    var get = $.get( url, function(data) {
      var dataString = JSON.stringify(data);
      var dataParsed = JSON.parse(dataString);
      var attributes = ["course_name", "depart_pref", "course_num", "description", "ldap_group", "professor", "access_code"];
      attributes.forEach(function(attribute){
        if(attribute in dataParsed.parameters){
          document.getElementById(attribute).value  = dataParsed.parameters[attribute]
        }
      });
    }).fail(function(data){window.location = "./classes"}); //Silent redirect to course page on error

    document.getElementById("create_class_button").innerText= "Edit Course";
    $("#create_class").submit( create_class );
    $("#delete_class_button").click( delete_class );
  }
});

done = function(data){
  window.location = "./classes";
}

fail = function(data){
  var httpStatus = data.status;
  var dataString = JSON.stringify(data.responseJSON);
  var dataParsed = JSON.parse(dataString);
  alert(dataParsed["error"]);
}

create_class = function( event ) {
  event.preventDefault();
  var $form = $(this);
  url = "../api/courses";
  var posting = $.post( url, { course_name: $form.find( "input[id='course_name']" ).val(), 
                               depart_pref: $form.find( "input[id='depart_pref']" ).val(),
                               course_num:  $form.find( "input[id='course_num']" ).val(),
                               description: $('#description').val(),
                               ldap_group:  $form.find( "input[id='ldap_group']" ).val(),
                               professor:   $form.find( "input[id='professor']" ).val(),
                               access_code: $form.find( "input[id='access_code']" ).val(),
                             } );

  posting.done(done);
  posting.fail(fail); 
}

delete_class = function( event ){
  event.preventDefault();
  if(confirm("Are you sure you want to delete the course? All data and logs will be wiped.")){
    var del = $.ajax({
                method: "DELETE",
                url: "../api/courses/"+course,
              });
    del.done(done);
    del.fail(fail);
  }
}
