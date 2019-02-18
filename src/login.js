login = function( event ) {
  event.preventDefault();

  $('#waiting_spinner').css("visibility", "visible"); // show waiting spinner
  
  var $form = $( this );
  var username = $form.find( "input[name='username']" ).val();
  var password = $form.find( "input[name='password']" ).val();
  var url = "./api/login";

  var $posting = $.post( url, { username: username, password: password } );
  $posting.always(function( data ) {

    $('#waiting_spinner').css("visibility", "hidden"); // hide waiting spinner

    var dataString = JSON.stringify(data);
    var dataParsed = JSON.parse(dataString);
    if(dataParsed.authenticated){
      //TODO: check for dataParsed.error

      //let the router figure out where to send us
      location.reload();
    }
    else{
      document.getElementById("Login").style.borderWidth = "medium";
      document.getElementById("Login").style.borderColor = "red";
      document.getElementById("password").style.borderWidth = "medium";
      document.getElementById("password").style.borderColor = "red";
    }
  });
}

$(document).ready(function(){
  $("#login_form").submit( login );
});
