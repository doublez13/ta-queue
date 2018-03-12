login = function( event ) {
  event.preventDefault();
  
  var $form = $( this );
  var username = $form.find( "input[name='username']" ).val();
  var password = $form.find( "input[name='password']" ).val();
  var url = "./api/login.php";

  var $posting = $.post( url, { username: username, password: password } );
  $posting.done(function( data ) {
    var dataString = JSON.stringify(data);
    var dataParsed = JSON.parse(dataString);
    if(dataParsed.authenticated){

      var $get_req = $.get("./api/user/my_classes.php");
      $get_req.done( function(data) {
        var dataString = JSON.stringify(data);
        var dataParsed = JSON.parse(dataString);
        if(dataParsed.error){
          alert(dataParsed.error);
          window.location.href = './index.php';
        }
        if(dataParsed.student_courses.length + dataParsed.ta_courses.length === 0){
          window.location.href = './view/classes.php';
        }
        else{
          window.location.href = './view/my_classes.php';
        }
      });

    }
    else{
      alert("Invalid username or password");
    }
  });
}

$(document).ready(function(){
  $("#login_form").submit( login ); 
});
