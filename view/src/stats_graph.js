var course;

/**
 * Parse the URL for the course information from GET
 **/
$(document).ready(function(){
  //GET parsing snippet from CHRIS COYIER
  var query = window.location.search.substring(1);
  var vars = query.split("&");
  for (var i=0;i<vars.length;i++) {
    var pair = vars[i].split("=");
    if(pair[0] == "course"){
      course = decodeURIComponent(pair[1]);
      break;
    }
  }
  if(typeof course === 'undefined'){
    window.location ='./my_courses.php';
  }
});

function average_plot(course_data) {
  $('#container').highcharts({
      chart: {
          type: 'scatter',
          zoomType: 'xy'
      },
      title: {
          text: 'Users helped per day'
      },
      xAxis: {
          title: {
              text: 'Year'
          },
          startOnTick: true,
          endOnTick: true,
          showLastLabel: true
      },
      yAxis: {
          title: {
              text: 'Number Enrolled'
          }
      },
      plotOptions: {
          scatter: {
              marker: {
                  radius: 5,
                  states: {
                      hover: {
                          enabled: true,
                          lineColor: 'rgb(100,100,100)'
                      }
                  }
              },
              states: {
                  hover: {
                      marker: {
                          enabled: false
                      }
                  }
              },
              tooltip: {
                  headerFormat: '<b>{series.name}</b><br>',
                  pointFormat: '{point.x}, {point.y}'
              }
          }
      },
      series: [{
          name: 'Number Admitted',
          color: 'rgba(223, 83, 83, .5)',
          data: course_data
      }], 
  });
});

// $(document).on("change", "#Student", function(e){
//     val = $("#Student").val();
//     $("#table_result").load("student_info.php?id=" +val);
// });

// $(document).on("change", "#chart", function(e){
//     val = $("#chart").val();
//     $("#chart_result").load("charts.php?chart=" +val);
// });

function get_number(course) {
  var url = "../api/stats/course_stats.php";
  var posting = $.post( url, { course: course } );
  posting.done(average_plot);
}

function get_full(course) {
  var url = "../api/stats/course_stats.php";
  var posting = $.post( url, { course: course } );
  posting.done(render_view);
}