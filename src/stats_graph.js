/**
 * Parse the URL for the course information from GET
 **/
$(document).ready(function(){
  //GET parsing snippet from CHRIS COYIER
  var query = window.location.search.substring(1);
  var vars = query.split("&");
  for (var i=0;i<vars.length;i++) {
    var pair = vars[i].split("=");
    if(pair[0] == "course_id"){
      course_id = decodeURIComponent(pair[1]);
      break;
    }
  }
  if(typeof course_id === 'undefined'){
    window.location ='./my_courses';
  }
  get_course_stats(course_id);
});

$(document).on("change", "#stats_selector", function(e){
  var stat = document.getElementById("stats_selector").value;
  choose_stats(stat);
});
$(document).on("change", "#start_date", function(e){
  var stat = document.getElementById("stats_selector").value;
  choose_stats(stat);
});
$(document).on("change", "#end_date", function(e){
  var stat = document.getElementById("stats_selector").value;
  choose_stats(stat);
});

function choose_stats(stat){
  if(stat == "num_student"){
    get_course_stats(course_id);
  }else if(stat == "ta_proportions"){
    get_ta_stats(course_id);
  }else if(stat == "ta_avg_help_time"){
    get_ta_avg_help_time(course_id);
  }
}


function get_course_stats(course_id) {
  var url = "../api/stats/course/"+course_id;

  var start_date = document.getElementById("start_date").value; 
  var end_date   = document.getElementById("end_date").value;

  if(!start_date || !end_date){
    var d = new Date();
    var curr_year  = d.getFullYear();
    var curr_month = d.getMonth()+1;//Month is 0 indexed
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
    var new_arr = dataParsed.map((element) => {
      return [Date.parse(element.date), element.students_helped, element.helped_by]
    });
    stud_helped_per_day_column_chart(new_arr);
  });
};

function get_ta_stats(course_id) {
  var url = "../api/stats/ta/"+course_id;

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
    var dataString = JSON.stringify(data.ta_proportions);
    var dataParsed = JSON.parse(dataString);
    var new_arr = dataParsed.map((element) => {
      return [element.students_helped, element.helped_by]
    });
    ta_proportions_pie_chart(new_arr);
  });
};

function get_ta_avg_help_time(course_id){
  var url = "../api/stats/ta/"+course_id;

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
    var dataString = JSON.stringify(data.avg_ta_help_time);
    var dataParsed = JSON.parse(dataString);
    var new_arr = dataParsed.map((element) => {
      return [element.TA, element.avg_help_time]
    });
    ta_avg_help_time_column_chart(new_arr);
  });
}

function stud_helped_per_day_column_chart(course_data) {
  var tmp_data = {};
  for(var i = 0; i < course_data.length; i++){
    let TA = course_data[i][2];
    if(!(TA in tmp_data)){
      tmp_data[TA] = [];
    }
    tmp_data[TA].push(course_data[i]);
  }
  
  var series_data = []
  for(var TA in tmp_data){
    series_data.push({ "name": TA,
                       "data": tmp_data[TA]});
  }

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
      },
      stackLabels: {
        enabled: true
      }
    },
    plotOptions: {
      column: {
        stacking: 'normal',
      }
    },
    series: series_data, 
  });
};

function ta_proportions_pie_chart(course_data) {
  var series_data = [];
  for(var TA in course_data){
    series_data.push({ "name": course_data[TA][1],
                       "y": course_data[TA][0]});
  }

  $('#container').highcharts({
    chart: {
      type: 'pie'
    },
    plotOptions: {
      pie: {
        allowPointSelect: true,
        cursor: 'pointer',
        dataLabels: {
          enabled: true,
          format: '<b>{point.name}</b>: {point.percentage:.1f} %',
        }
      }
    },
    title: {
      text: 'Portion of Students Helped by TA'
    },
    series: [{
      name: 'Students Helped',
      data: series_data
    }],
  });
};

function ta_avg_help_time_column_chart(course_data) {
  var series_data = []
  for(var entry in course_data){
    var minutes = Math.round((course_data[entry][1]/60)*10)/10
    series_data.push({ "name": course_data[entry][0],
                       "y":    minutes});
  }

  $('#container').highcharts({
    chart: {
      type: 'column',
    },
    title: {
      text: 'Average TA Help Time'
    },
    xAxis: {
      type: 'category'
    },
    yAxis: {
      title: {
        text: 'Minutes'
      },
      units: 'minutes'
    },
    legend: {
      enabled: false
    },
    tooltip: {
      pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y:.2f}</b> minutes<br/>'
    },
    series:[{
      name: "TAs",
      data: series_data,
      colorByPoint: true}]
  });
};
