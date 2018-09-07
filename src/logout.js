function logout(){
  var $get_req = $.post("../api/logout");
  localStorage.clear();
  $get_req.done( function(data) {
    window.location = '/';
  });
}
