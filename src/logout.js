function logout(){
  var $get_req = $.post("../api/logout");
  $get_req.done( function(data) {
    window.location = '/';
  });
}
