$(document).ready(function(){
    $("#create_class").submit( create_class );
    $("#course_name").focus();
});

done = function(data){
  window.location = "./classes";
  alert("Course created successfully.\n\nRedirecting to Courses...");
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
  var course_name   = $form.find( "input[id='course_name']" ).val();
  var depart_prefix = $form.find( "input[id='depart_prefix']" ).val();
  var course_num    = $form.find( "input[id='course_num']" ).val();
  var description   = $('#description').val();
  var ldap_group    = $form.find( "input[id='ldap_group']" ).val();
  var professor     = $form.find( "input[id='professor']" ).val();
  var acc_code      = $form.find( "input[id='acc_code']" ).val();

  url = "../api/admin/courses";
  var posting = $.post( url, { course_name:   course_name, 
                               depart_prefix: depart_prefix,
                               course_num:    course_num,
                               description:   description,
                               ldap_group:    ldap_group,
                               professor:     professor,
                               acc_code:      acc_code,
                             } );

  posting.done(done);
  posting.fail(fail);
}
