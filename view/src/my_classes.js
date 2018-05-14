get_my_classes();

function get_my_classes(){
  var $url = "../api/user/my_courses.php";
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
        var url = "../api/user/rem_course.php";
        var $posting = $.post( url, { course: intersection[course]} );
      }
      location.reload;
    }

    renderMyCourseTable(ta_courses, "TA");
    renderMyCourseTable(stud_courses, "Student");
  });
}

function renderMyCourseTable(courses, role) {
  var table = $('#my_classes');
  courses.forEach(function (course) {
    var tableRow = $('<tr>');
    tableRow.append($('<td>').text(course));
    tableRow.append($('<td>').text(role));
    var URI = encodeURI("queue.php?course="+course);
    tableRow.append( '<td> <a href="'+URI+'"> <button class="btn btn-primary" style="width: 100%;" ><span>GoTo</span> </button></a> </td> '  );
    table.append(tableRow);
  });
}
