username = localStorage.username;
is_admin = localStorage.is_admin == true;

if(!is_admin){
  get_my_courses();
}
get_all_courses();

function get_my_courses(){
  var $url = "../api/user/"+username+"/courses";
  var $get = $.get( $url );
  $get.done(function(data){
    var dataString = JSON.stringify(data);
    var dataParsed = JSON.parse(dataString);
    var stud_courses = dataParsed.student_courses;
    var ta_courses   = dataParsed.ta_courses;

    $('#my_courses_body tr').remove();
    renderMyCourseTable(ta_courses, "TA");
    renderMyCourseTable(stud_courses, "Student");
  });
}

function renderMyCourseTable(courses, role) {
  $("#my_course_table").show();
  var table = $('#my_courses_body'); 

  courses.forEach(function (course) {
    var tableRow = $('<tr>');
    tableRow.append($('<td>').text(course));
    tableRow.append($('<td>').text(role));
    var URI = encodeURI("queue?course="+course);
    tableRow.append( '<td> <a href="'+URI+'"> <button class="btn btn-primary" style="width: 100%;" ><span>Go</span> </button></a> </td> '  );
    table.append(tableRow);
  });
}

function get_all_courses(){
  var $url = "../api/courses";
  var $get = $.get($url);
  $get.done(function(data){
    var dataString = JSON.stringify(data);
    var dataParsed = JSON.parse(dataString);
    var allCourses = dataParsed.all_courses;

    var $url = "../api/user/"+username+"/courses";
    var $get = $.get( $url );
    $get.done( function(data) {
      var dataString = JSON.stringify(data);
      var dataParsed = JSON.parse(dataString);
      renderAllCourseTable(allCourses, dataParsed);
    });
  });
}

function renderAllCourseTable(allCourses, dataParsed) {
  $('#all_courses_body').empty();
  
  var myCourses = dataParsed.student_courses;
  var ta_courses= dataParsed.ta_courses;

  for(course in allCourses) {
    var course_name = course;
    var tableRow = $('<tr>');

    tableRow.append($('<td>').text( course_name ));

    if(is_admin){                                       //They're an admin
      var URI = encodeURI("queue?course="+course);
      var url = "./edit_course?course="+course_name;
      var onclick = "window.location='"+url+"'";

      var td = $("<td class='col-sm-2'></td>");
      var button_group = $("<div class='btn-group btn-group-justified' role='group' aria-label='...'></div>");
      var go_button = $('<div class="btn-group" role="group"> <a href="'+URI+'"> <button class="btn btn-primary" title="Go to Course">Go</button></div>');
      var edit_button1 = $('<div class="btn-group" role="group"><button class="btn btn-primary" onclick="'+onclick+'"  title="Edit Course"><i class="fa fa-cog"></i></button></div>');
      button_group.append(go_button);
      button_group.append(edit_button1);
      td.append(button_group);
      tableRow.append(td);
    }
    else if( $.inArray(course_name, ta_courses) >= 0 ){ //They're a TA for the course
      tableRow.append('<td> <button class="btn btn-primary" disabled style="width: 100%;" > TA </button></td>');
    }
    else if( $.inArray(course_name, myCourses) >= 0 ){  //They're a student in the course
      var text = "Leave";
      var action = "dropCourse('"+course_name+"')";
      tableRow.append('<td> <button class="btn btn-danger" onclick="'+action+'" style="width: 100%;" >'+text+'</button></td>');
    }
    else{                                               //They're able to enroll as student
      var text = " Enroll";
      if(allCourses[course_name]["acc_req"]){
        var action = "prompt_acc_code('"+course_name+"')";
        tableRow.append('<td> <button class="btn btn-warning" onclick="'+action+'" style="width: 100%;"><i class="glyphicon glyphicon-lock"></i>'+text+'</button></td>');
      }else{
        var action = "enrollCourse('"+course_name+"', null)";
        tableRow.append('<td> <button class="btn btn-primary" onclick="'+action+'" style="width: 100%;" >'+text+'</button></td>');
      }
    }

    $('#all_courses_body').append(tableRow);
  }
}

done = function(data){ //Repopulates the content on the page after they add/rem a course
  get_all_courses();
  get_my_courses();  
}

fail = function(data){
  var httpStatus = data.status;
  var dataString = JSON.stringify(data.responseJSON);
  var dataParsed = JSON.parse(dataString);
  alert(dataParsed["error"]);
}

function prompt_acc_code(course_name){
  var code = prompt("Please enter the course access code:");
  if(code != null){
    enrollCourse(course_name, code);
  }
}

function enrollCourse(course, code) {
  var url = "../api/user/"+username+"/courses/"+course+"/student";
  if(code == null){
    var posting = $.post( url );
  }else{
    var posting = $.post( url, { acc_code: code } );
  }
  posting.done(done);
  posting.fail(fail);
}

function dropCourse(course) {
  var del = $.ajax({
                  method: "DELETE",
                  url: "../api/user/"+username+"/courses/"+course+"/student"
                  });
  del.done(done);
  del.fail(fail);
}
