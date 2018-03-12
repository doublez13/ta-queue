$(function () {
  var $url = "../api/user/my_classes.php";
  var $get = $.get( $url );
  $get.done(function(data){
    var dataString = JSON.stringify(data);
    var dataParsed = JSON.parse(dataString);
    var stud_courses = dataParsed.student_courses;
    var ta_courses   = dataParsed.ta_courses;
  
    var intersection = stud_courses.filter(function(n) {
                     return ta_courses.indexOf(n) !== -1;
                   });    
    if(intersection.length){
      alert("You're registered on the queue as both a student and TA for one or more courses. Unregisting as student...");
      for(course in intersection){ 
        var url = "../api/user/rem_class.php";
        var $posting = $.post( url, { course: intersection[course]} );
      }
      location.reload;
    }

    renderCourseTable(ta_courses, "TA");
    renderCourseTable(stud_courses, "Student");
  });
});

function renderCourseTable(courses, role) {
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
