get_classes();

function get_classes(){
  var $url = "../api/classes/all_courses.php";
  var $get = $.get( $url );
  $get.done(function(data){
    var dataString = JSON.stringify(data);
    var dataParsed = JSON.parse(dataString);
    var allCourses = dataParsed.all_courses;

    var $url = "../api/user/my_courses.php";
    var $get = $.get( $url );
    $get.done( function(data) {
      var dataString = JSON.stringify(data);
      var dataParsed = JSON.parse(dataString);
      renderCourseTable(allCourses, dataParsed);
    });
  });
}

function renderCourseTable(allCourses, dataParsed) {
  $('#all_classes_body').empty();
  var myCourses = dataParsed.student_courses;
  var ta_courses= dataParsed.ta_courses;
  for(course in allCourses){
    var course_name = course;
    var tableRow = $('<tr>');
    tableRow.append($('<td>').text( course_name ));
    if( $.inArray(course_name, ta_courses) >= 0 ){
      tableRow.append('<td> <button class="btn btn-primary" disabled style="width: 100%;" > TA </button> </td>');
    }
    else if( $.inArray(course_name, myCourses) >= 0 ){
      var text = "Leave";
      var action = "dropCourse('"+course_name+"')";
      tableRow.append('<td> <button class="btn btn-danger" onclick="'+action+'" style="width: 100%;" >'+text+'</button> </td>');
    }
    else{
      var text = " Enroll"; // extra space on left creates a little separation between icon and text
      if(allCourses[course_name]["acc_req"]){
        var action = "prompt_acc_code('"+course_name+"')";
        tableRow.append('<td> <button class="btn btn-primary" onclick="'+action+'" style="width: 100%;"><i class="glyphicon glyphicon-lock"></i>'+text+'</button> </td>');
      }else{
        var action = "enrollCourse('"+course_name+"', null)";
        tableRow.append('<td> <button class="btn btn-primary" onclick="'+action+'" style="width: 100%;" >'+text+'</button> </td>');
      }
    }

    $('#all_classes_body').append(tableRow);
  }
}


done = function(data){
  get_classes(); //reloads the content on the page
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
  var url = "../api/user/add_course.php";
  if(code == null){
    var posting = $.post( url, { course: course } );
  }else{
    var posting = $.post( url, { course: course, acc_code: code } );
  }
  posting.done(done);
  posting.fail(fail);
}

function dropCourse(course) {
  var url = "../api/user/rem_course.php";
  var posting = $.post( url, { course: course} );
  posting.done(done);
  posting.fail(fail);
}
