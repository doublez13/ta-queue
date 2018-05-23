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
    $("#create_class").submit( create_class );
  }
  else{//Edit exsisting course
    document.getElementById("page_title").innerHTML = "Edit Course";
    document.getElementById("panel_title").innerHTML = "Edit Course";
    url = "../api/courses/"+course;
    var get = $.get( url, function(data) {
      var dataString = JSON.stringify(data);
      var dataParsed = JSON.parse(dataString);
      //TODO: Check for error
      document.getElementById("course_name").value  = dataParsed.parameters['course_name'];
      document.getElementById("depart_pref").value  = dataParsed.parameters['depart_pref'];
      document.getElementById("course_num").value   = dataParsed.parameters['course_num'];
      document.getElementById("description").value  = dataParsed.parameters['description'];
      document.getElementById("ldap_group").value   = dataParsed.parameters['ldap_group'];
      document.getElementById("professor").value    = dataParsed.parameters['professor'];
      document.getElementById("acc_code").value     = dataParsed.parameters['acc_code'];
    });
    $("#create_class").submit( create_class );
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
  var course_name = $form.find( "input[id='course_name']" ).val();
  var depart_pref = $form.find( "input[id='depart_pref']" ).val();
  var course_num  = $form.find( "input[id='course_num']" ).val();
  var description = $('#description').val();
  var ldap_group  = $form.find( "input[id='ldap_group']" ).val();
  var professor   = $form.find( "input[id='professor']" ).val();
  var acc_code    = $form.find( "input[id='acc_code']" ).val();

  url = "../api/courses";
  var posting = $.post( url, { course_name: course_name, 
                               depart_pref: depart_pref,
                               course_num:  course_num,
                               description: description,
                               ldap_group:  ldap_group,
                               professor:   professor,
                               acc_code:    acc_code,
                             } );

  posting.done(done);
  posting.fail(fail);
}
