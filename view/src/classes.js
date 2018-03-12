get_classes();

function get_classes(){
  var $url = "../api/classes/all_classes.php";
  var $get = $.get( $url );
  $get.done(function(data){
    var dataString = JSON.stringify(data);
    var dataParsed = JSON.parse(dataString);
    var allCourses = dataParsed.all_courses;

    var $url = "../api/user/my_classes.php";
    var $get = $.get( $url );
    $get.done( function(data) {
      var dataString = JSON.stringify(data);
      var dataParsed = JSON.parse(dataString);
      renderCourseTable(allCourses, dataParsed);
    });
  });
}

function renderCourseTable(allCourses, dataParsed) {
  $('#all_classes tr').remove();
  var table = $('#all_classes');
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
      if(allCourses[course_name]["acc_req"]){
        tableRow.append('<td> <button class="btn btn-primary" onclick="'+action+'" style="width: 100%;" > <span class="glyphicon glyphicon-lock"></span> '+text+'</button> </td>');
      }else{
        tableRow.append('<td> <button class="btn btn-primary" onclick="'+action+'" style="width: 100%;" >'+text+'</button> </td>');
      }
    }
    else{
      var text = "Enroll";
      if(allCourses[course_name]["acc_req"]){
        var action = "prompt_acc_code('"+course_name+"')";
        tableRow.append('<td> <button class="btn btn-primary" onclick="'+action+'" style="width: 100%;" > <span class="glyphicon glyphicon-lock"></span> '+text+'</button> </td>');
      }else{
        var action = "enrollCourse('"+course_name+"', null)";
        tableRow.append('<td> <button class="btn btn-primary" onclick="'+action+'" style="width: 100%;" >'+text+'</button> </td>');
      }
    }
    table.append(tableRow);
  }
}

function prompt_acc_code(course_name){
  var code = prompt("Please enter the course access code:");
  if(code != null){
    enrollCourse(course_name, code);
  }
}

function enrollCourse(course, code) {
  var url = "../api/user/add_class.php";
  if(code == null){
    var $posting = $.post( url, { course: course } );
  }else{
    var $posting = $.post( url, { course: course, acc_code: code } );
  }
  $posting.done( function(data) {
    var dataString = JSON.stringify(data);
    var dataParsed = JSON.parse(dataString);
    if(dataParsed["error"]){
      alert(dataParsed["error"])
    }else{
      get_classes(); 
    }
  });
}

function dropCourse(course) {
  var url = "../api/user/rem_class.php";
  var $posting = $.post( url, { course: course} );
  $posting.done( function(data) {
    var dataString = JSON.stringify(data);
    var dataParsed = JSON.parse(dataString);
    if(dataParsed["error"]){
      alert(dataParsed["error"])
    }else{
      get_classes();
    }
  });
}
