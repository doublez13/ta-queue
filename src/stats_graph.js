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
    window.location ='./my_courses';
  }
  get_course_stats(course);
});

$(document).on("change", "#chart", function(e){
  get_course_stats(course);
});

function get_course_stats(course) {
  var url = "../api/stats/course/"+course;
  var get = $.get(url);
  get.done(parse_it);
};

function parse_it(data) {
  var dataString = JSON.stringify(data.usage);
  var dataParsed = JSON.parse(dataString);
  var new_arr = dataParsed.map((element, index) => {
  	return [Date.parse(element.date), element.students_helped]
  });
  stud_helped_per_day_column_chart(new_arr);
};

function stud_helped_per_day_column_chart(course_data) {
  $('#container').highcharts({
    chart: {
      type: 'column',
      zoomType: 'xy'
	  },
	  title: {
      text: 'Students Helped Per Day'
	  },
    xAxis: {
      tickInterval: (24 * 3600 * 1000), // the number of milliseconds in a day
      allowDecimals: false,
      title: {
  	    text: 'Date',
  	    scalable: false
      },
      type: 'datetime',
      labels: {
  	    formatter: function() {
          return Highcharts.dateFormat('%y-%b-%d', this.value);
        }
      }
    },
    yAxis: {
      title: {
        text: 'Number of Students Helped'
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
          pointFormat: '{point.y} students helped'
        }
      }
    },
    series: [{
      "name": 'Students Helped',
      "color": 'rgba(223, 83, 83, .5)',
      "data": course_data
    }], 
  });
};
