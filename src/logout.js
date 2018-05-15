function logout(){
  var $get_req = $.get("../api/logout.php");
  $get_req.done( function(data) {
    location.reload();
  });
}
