
<script src="<?=HTTP?>application/views/js/amcharts/core.js"></script>
<script src="<?=HTTP?>application/views/js/amcharts/charts.js"></script>
<script src="<?=HTTP?>application/views/js/amcharts/animated.js"></script>


<style>
#chart {
  width: 100%;
  height: 400px;
}

</style>


<!-- Chart code -->
<script>

function prepare_chartdata(raw_data) {

  var chartdata = []

    $.each(raw_data.data, function (i, o) {
      chartdata.push({date : i, value : o});
    }) 

    return chartdata
}


function load_chart(raw_data, element) {

  am4core.ready(function() {

    am4core.useTheme(am4themes_animated);

    // Create chart instance
    var chart = am4core.create("chart", am4charts.XYChart);
    chart.paddingRight = 20;


    // Add data
    chart.data = prepare_chartdata(raw_data);
    chart.dateFormatter.inputDateFormat = "yyyy-MM-dd";

    // Axes
    var dateAxis = chart.xAxes.push(new am4charts.DateAxis());
    var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
    valueAxis.baseValue = 0;

    dateAxis.dateFormatter = new am4core.DateFormatter();
    dateAxis.dateFormatter.dateFormat = "MM";
    dateAxis.dateFormats.setKey("month", "MMM");
    dateAxis.periodChangeDateFormats.setKey("month", "MMM");
    
    dateAxis.renderer.labels.template.rotation = 270
    dateAxis.renderer.minGridDistance = 18;
    dateAxis.renderer.grid.template.disabled = true;


    function createRangeGrid(date) {
      var range = dateAxis.axisRanges.create();
      range.date = date;
      range.grid.strokeOpacity = 0.3;
      range.tick.disabled = false;
      range.tick.strokeOpacity = 0.3;
      range.tick.length = 40;
      range.label.paddingTop = -40;
    }

    function createRange(from, to, label) {
      var range = dateAxis.axisRanges.create();
      range.date = from;
      range.endDate = to;
      range.label.text = label;
      range.label.paddingTop = 40;
      range.label.location = 0.5;
      range.label.rotation = 0
      range.label.horizontalCenter = "middle";
      range.label.fontWeight = "bolder";
      range.grid.disabled = true;
    }

    for (i=0; i <= chart.data.length; i += 12) {
      var x = chart.data[i].date.split('-')
      createRangeGrid(new Date(x[0], parseInt(x[1])-1, x[2]));
      createRange(new Date(x[0], 0, 1), new Date(parseInt(x[0])+1, 0, 1), x[0])
    }


    // Create series
    var series = chart.series.push(new am4charts.LineSeries());
    series.dataFields.valueY = "value";
    series.dataFields.dateX = "date";
    series.tooltipText = "{value}"
    series.strokeWidth = 2;
    series.minBulletDistance = 15;
    series.tensionX = 0.77;


    // bullet is added because we add tooltip to a bullet for it to change color
    var bullet = series.bullets.push(new am4charts.Bullet());
    bullet.tooltipText = "{valueX} {valueY}";

    bullet.adapter.add("fill", function(fill, target){
        if(target.dataItem.valueY < 0){
            return am4core.color("#FF0000");
        }
        return fill;
    })

    var range = valueAxis.createSeriesRange(series);
    range.value = 0.4;
    range.endValue = 0;
    range.contents.stroke = am4core.color("#930905");
    range.contents.fill = range.contents.stroke;

    range = valueAxis.createSeriesRange(series);
    range.value = 0.6;
    range.endValue = 0.4;
    range.contents.stroke = am4core.color("#d03a27");
    range.contents.fill = range.contents.stroke;

    range = valueAxis.createSeriesRange(series);
    range.value = 0.8;
    range.endValue = 0.6;
    range.contents.stroke = am4core.color("#e6987b");
    range.contents.fill = range.contents.stroke;

    range = valueAxis.createSeriesRange(series);
    range.value = 1.0;
    range.endValue = 0.8;
    range.contents.stroke = am4core.color("#fbd91d");
    range.contents.fill = range.contents.stroke;

    range = valueAxis.createSeriesRange(series);
    range.value = 8.0;
    range.endValue = 1.0;
    range.contents.stroke = am4core.color("#8be1b5");
    range.contents.fill = range.contents.stroke;

    // Add scrollbar
    // var scrollbarX = new am4charts.XYChartScrollbar();
    // scrollbarX.series.push(series);
    // chart.scrollbarX = scrollbarX;

    chart.cursor = new am4charts.XYCursor();

  }); 
}


$.get("<?=HTTP?>data/chart_cdi/1101/Jul-2019", function (_district_data) {
  
  var chartdata = []

    load_chart(_district_data, 'chart')

}, 'json');

</script>


<!-- HTML -->
<div id="chart"></div>

