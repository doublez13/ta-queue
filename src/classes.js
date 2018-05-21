get_all_classes();
get_my_classes();

function get_my_classes(){
  var $url = "../api/user/courses";
  var $get = $.get( $url );
  $get.done(function(data){
    var dataString = JSON.stringify(data);
    var dataParsed = JSON.parse(dataString);
    var stud_courses = dataParsed.student_courses;
    var ta_courses   = dataParsed.ta_courses;

    //Edge case where a user may somehow be registered as a student AND a TA 
    var intersection = stud_courses.filter(function(n) {
      return ta_courses.indexOf(n) !== -1;
    });
    if(intersection.length){ 
      alert("You're registered on the queue as both a student and TA for one or more courses. Unregistering as student...");
      for(course in intersection){
        var url = "../api/user/rem_course";
        var $posting = $.post( url, { course: intersection[course]} );
      }
      location.reload;
    }

    $('#my_classes_body tr').remove();
    renderMyCourseTable(ta_courses, "TA");
    renderMyCourseTable(stud_courses, "Student");
  });
}

function renderMyCourseTable(courses, role) {
  var table = $('#my_classes_body'); 

  courses.forEach(function (course) {
    var tableRow = $('<tr>');
    tableRow.append($('<td>').text(course));
    tableRow.append($('<td>').text(role));
    var URI = encodeURI("queue?course="+course);
    tableRow.append( '<td> <a href="'+URI+'"> <button class="btn btn-primary" style="width: 100%;" ><span>GoTo</span> </button></a> </td> '  );
    table.append(tableRow);
  });
}

function get_all_classes(){
  var $url = "../api/courses";
  var $get = $.get( $url );
  $get.done(function(data){
    var dataString = JSON.stringify(data);
    var dataParsed = JSON.parse(dataString);
    var allCourses = dataParsed.all_courses;

    var $url = "../api/user/courses";
    var $get = $.get( $url );
    $get.done( function(data) {
      var dataString = JSON.stringify(data);
      var dataParsed = JSON.parse(dataString);
      renderAllCourseTable(allCourses, dataParsed);
    });
  });
}

function renderAllCourseTable(allCourses, dataParsed) {
  $('#all_classes_body').empty();
  
  var myCourses = dataParsed.student_courses;
  var ta_courses= dataParsed.ta_courses;

  for(course in allCourses) {
    var course_name = course;
    var tableRow = $('<tr>');

    tableRow.append($('<td>').text( course_name ));
    if( $.inArray(course_name, ta_courses) >= 0 ){
      tableRow.append('<td> <button class="btn btn-primary" disabled style="width: 100%;" > TA </button></td>');
    }
    else if( $.inArray(course_name, myCourses) >= 0 ){
      var text = "Leave";
      var action = "dropCourse('"+course_name+"')";
      tableRow.append('<td> <button class="btn btn-danger" onclick="'+action+'" style="width: 100%;" >'+text+'</button></td>');
    }
    else{
      var text = " Enroll"; // extra space on left creates a little separation between icon and text
      if(allCourses[course_name]["acc_req"]){
        var action = "prompt_acc_code('"+course_name+"')";
        tableRow.append('<td> <button class="btn btn-warning" onclick="'+action+'" style="width: 100%;"><i class="glyphicon glyphicon-lock"></i>'+text+'</button></td>');
      }else{
        var action = "enrollCourse('"+course_name+"', null)";
        tableRow.append('<td> <button class="btn btn-primary" onclick="'+action+'" style="width: 100%;" >'+text+'</button></td>');
      }
    }

    $('#all_classes_body').append(tableRow);
  }
}

done = function(data){
  get_all_classes(); //reloads the content on the page
  get_my_classes();  
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
  var url = "../api/user/courses";
  if(code == null){
    var posting = $.post( url, { course: course } );
  }else{
    var posting = $.post( url, { course: course, acc_code: code } );
  }
  posting.done(done);
  posting.fail(fail);
}

function dropCourse(course) {
  var del = $.ajax({
                  method: "DELETE",
                  url: "../api/user/courses?course="+course
                  });
  del.done(done);
  del.fail(fail);
}
