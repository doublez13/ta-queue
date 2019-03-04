$(function() {
  $("#panel_title").text("Admins");
  $("#jsGrid").jsGrid({
    width: "100%",
    height: "auto",

    editing: false,
    sorting: true,
    paging: true,
    pageSize: 15,
    autoload: true,
    inserting: true,

    deleteConfirm: "Are you sure you want to remove this user from the admin group?",

    controller: {
      loadData: function(filter){
        var deferred = $.Deferred();
        $.ajax({
          type: "GET",
          url: "/api/admins",
          dataType: "json",
          data: filter,
          success: function(response) {
            var admin = [];
            var row;
            for(row in response.admin){
              var current = response.admin[row];
              admin.push({"username": current.username, "full_name": current.full_name});
            }
            deferred.resolve(admin);
          },
          error: function(xhr){
            if(xhr.status == 403){
               window.location = '/';
            }
          }
        });
        return deferred.promise();
      },
      insertItem: function(item) {
        var username = item['username']
        $.ajax({
          type: "POST",
          async: false,
          url: "/api/admins/"+username,
          error: function() {
            alert("User does not exist");
          }
        });
        $("#jsGrid").jsGrid("loadData");
      },
      deleteItem: function(item) {
        var username = item['username']
        return $.ajax({
          type: "DELETE",
          async: false,
          url: "/api/admins/"+username
        });
      },
     },

    fields: [
      { type: "text", name: "username", title: "Username", validate: "required" },
      { type: "text", name: "full_name", title: "Full Name", readOnly: true},
      { type: "control"}
    ]

  });
});
