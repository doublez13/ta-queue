function logout(){
  var $get_req = $.get("../api/logout");
  $get_req.done( function(data) {
    window.location = '/';
  });
}
