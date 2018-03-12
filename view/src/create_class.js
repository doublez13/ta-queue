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

  url = "../api/admin/create_course.php";
  var posting = $.post( url, { course_name:   course_name, 
                               depart_prefix: depart_prefix,
                               course_num:    course_num,
                               description:   description,
                               ldap_group:    ldap_group,
                               professor:     professor,
                               acc_code:      acc_code,
                             } );

  var done = function(data){
    var dataString = JSON.stringify(data);
    var dataParsed = JSON.parse(dataString);
    if(dataParsed.error){
      alert(dataParsed["error"]);
    }else{
      window.location("./classes.php");
    }
  }
  posting.done(done);
}

$(document).ready(function(){
  $("#create_class").submit( create_class ); 
});
