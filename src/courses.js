var username;
var is_admin;;

get_all_courses();

function get_all_courses(){
  var $url = "../api/courses";
  var $get = $.get($url);
  $get.done(function(data){
    var dataString = JSON.stringify(data);
    var dataParsed = JSON.parse(dataString);
    var allCourses = dataParsed.all_courses;
    username       = dataParsed.username;
    is_admin       = dataParsed.admin;

    var $url = "../api/user/"+username+"/courses";
    var $get = $.get( $url );
    $get.done( function(data) {
      var dataString = JSON.stringify(data);
      var myCourses  = JSON.parse(dataString);
      renderCourseTables(allCourses, myCourses);
    });
  });
}

function renderCourseTables(allCourses, myCourses) {
  $('#all_courses_body').empty();
  $('#my_courses_body tr').remove();

  if(is_admin){
    document.getElementById('all_courses_paragraph').innerHTML = "<b>Admin Interface: View/Edit Courses</b>";
  }else{
    document.getElementById('all_courses_paragraph').innerHTML = "<b>Enroll/Leave your courses here.</b>";
    var instructor_courses   = myCourses.instructor_courses;
    var ta_courses           = myCourses.ta_courses;
    var stud_courses         = myCourses.student_courses;
    $("#my_course_table").hide();
    if(Object.keys(instructor_courses).length + Object.keys(ta_courses).length + Object.keys(stud_courses).length){
      renderMyCourseTable(instructor_courses, "Instructor");
      renderMyCourseTable(ta_courses, "TA");
      renderMyCourseTable(stud_courses, "Student");
      $("#my_course_table").show();
    }
  }

  $("#course_table").hide();
  var course_name;
  for(course_name in allCourses) {
    var course_id = allCourses[course_name]['course_id'];
    var acc_req   = allCourses[course_name]['acc_req'];
    var enabled   = allCourses[course_name]['enabled'];
    var desc      = allCourses[course_name]['description'];
    var tableRow = $('<tr>');

    var rowCell = $('<td>');
    rowCell.text(course_name);
    rowCell.attr("title", desc);
    tableRow.append(rowCell);

    if(is_admin){                                       //They're an admin
      var URI = encodeURI("queue?course_id="+course_id);
      var url = "./edit_course?course_id="+course_id;
      var onclick = "window.location='"+url+"'";

      var td = $("<td class='col-xs-3 col-sm-2'></td>");
      var button_group = $("<div class='btn-group btn-group-justified' role='group' aria-label='...'></div>");
      var go_button;
      if(enabled == 1){
        go_button = $('<div class="btn-group" role="group"> <a href="'+URI+'"> <button class="btn btn-primary" title="Go to Course">Go</button></div>');
      }else{
        go_button = $('<div class="btn-group" role="group"> <a href="'+URI+'"> <button class="btn btn-primary" title="Go to Course" disabled=true>Go</button></div>');
      }
      var edit_button1 = $('<div class="btn-group" role="group"><button class="btn btn-primary" onclick="'+onclick+'"  title="Edit Course"><i class="fa fa-cog"></i></button></div>');
      button_group.append(go_button);
      button_group.append(edit_button1);
      td.append(button_group);
      tableRow.append(td);
    }
    else if( $.inArray(course_name, Object.keys(ta_courses) ) >= 0 ){ //They're a TA for the course
      tableRow.append('<td> <button class="btn btn-primary" disabled style="width: 100%;" > TA </button></td>');
    }
    else if( $.inArray(course_name, Object.keys(stud_courses) ) >= 0 ){  //They're a student in the course
      var text = "Leave";
      var action = "dropCourse('"+course_id+"')";
      tableRow.append('<td> <button class="btn btn-danger" onclick="'+action+'" style="width: 100%;" >'+text+'</button></td>');
    }
    else{                                               //They're able to enroll as student
      var text = " Enroll";
      if(acc_req){
        var action = "prompt_acc_code('"+course_id+"')";
        tableRow.append('<td> <button class="btn btn-warning" onclick="'+action+'" style="width: 100%;"><i class="glyphicon glyphicon-lock"></i>'+text+'</button></td>');
      }else{
        var action = "enrollCourse('"+course_id+"', null)";
        tableRow.append('<td> <button class="btn btn-primary" onclick="'+action+'" style="width: 100%;" >'+text+'</button></td>');
      }
    }

    $('#all_courses_body').append(tableRow);
  }
  $("#course_table").show();
}

function renderMyCourseTable(courses, role) {
  var table = $('#my_courses_body');

  var course;
  for(course in courses) {
    var desc      = courses[course]['description'];
    var course_id = courses[course]['course_id'];
    var tableRow  = $('<tr>');
    var rowCell   = $('<td>');
    rowCell.attr("title", desc);
    rowCell.text(course);
    tableRow.append(rowCell);
    tableRow.append($('<td>').text(role));
    var URI = encodeURI("queue?course_id="+course_id);
    tableRow.append( '<td> <a href="'+URI+'"> <button class="btn btn-primary" style="width: 100%;" ><span>Go</span> </button></a> </td> '  );
    table.append(tableRow);
  }
}

done = function(data){ //Repopulates the content on the page after they add/rem a course
  get_all_courses();
}

fail = function(data){
  var dataString = JSON.stringify(data.responseJSON);
  var dataParsed = JSON.parse(dataString);
  alert(dataParsed["error"]);
}

function prompt_acc_code(course_id){
  var code = prompt("Please enter the course access code:");
  if(code != null){
    enrollCourse(course_id, code);
  }
}

function enrollCourse(course_id, code) {
  var url = "../api/user/"+username+"/courses/"+course_id+"/student";
  if(code == null){
    var posting = $.post( url );
  }else{
    var posting = $.post( url, { acc_code: code } );
  }
  posting.done(done);
  posting.fail(fail);
}

function dropCourse(course_id) {
  var del = $.ajax({
                  method: "DELETE",
                  url: "../api/user/"+username+"/courses/"+course_id+"/student"
                  });
  del.done(done);
  del.fail(fail);
}
