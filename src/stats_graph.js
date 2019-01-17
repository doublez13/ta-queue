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

$(document).on("change", "#stats_selector", function(e){
    get_course_stats(course);
});
$(document).on("change", "#start_date", function(e){
  get_course_stats(course);
});
$(document).on("change", "#end_date", function(e){
    get_course_stats(course);
});


function get_course_stats(course) {
  var url = "../api/stats/course/"+course;

  var start_date = document.getElementById("start_date").value; 
  var end_date   = document.getElementById("end_date").value;

  if(!start_date || !end_date){
    var d = new Date();
    var curr_year  = d.getFullYear();
    var curr_month = d.getMonth();
    var curr_day   = d.getDate();
    if(curr_month < 5 || (curr_month == 5 && curr_day <= 9)){
      start_date = curr_year+'-01-01';
      end_date   = curr_year+'-05-09';
    }else if(curr_month < 8 || (curr_month == 8 && curr_day <= 19)){
      start_date = curr_year+'-05-10';
      end_date   = curr_year+'-08-19';
    }else{
      start_date = curr_year+'-08-20';
      end_date   = curr_year+'-12-31';
    }
    document.getElementById("start_date").value = start_date;
    document.getElementById("end_date").value   = end_date;
  }

  var get = $.get(url, {start_date: start_date, end_date: end_date});
  get.done(function(data){
    var dataString = JSON.stringify(data.usage);
    var dataParsed = JSON.parse(dataString);
    var new_arr = dataParsed.map((element, index) => {
      return [Date.parse(element.date), element.students_helped]
    });
    stud_helped_per_day_column_chart(new_arr);
  });
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
    series: [{
      "name": 'Students Helped',
      "color": 'rgba(223, 83, 83, .5)',
      "data": course_data
    }], 
  });
};
