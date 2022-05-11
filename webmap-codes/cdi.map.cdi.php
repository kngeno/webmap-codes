

<script src="<?=HTTP?>application/views/js/amcharts/core.js"></script>
<script src="<?=HTTP?>application/views/js/amcharts/charts.js"></script>
<script src="<?=HTTP?>application/views/js/amcharts/animated.js"></script>


<script src="<?=HTTP?>application/views/js/jquery.js"></script>
<script src="<?=HTTP?>application/views/js/ui/jquery-ui.min.js"></script>
<script src="<?=HTTP?>application/views/js/ui/jquery-ui.min.css"></script>
<script src="<?=HTTP?>application/views/js/ui/jquery-theme.min.css"></script>
<script src="<?=HTTP?>application/views/js/leaflet/leaflet.js"></script>
<script src="<?=HTTP?>application/views/js/map/regional.boundaries.geojson"></script>
<script src="<?=HTTP?>application/views/js/map/district.boundaries.geojson"></script>
<script src="<?=HTTP?>application/views/js/map/district.capitals.geojson"></script>
<script src="<?=HTTP?>application/views/js/map/district.capitals.geojson"></script>
<script src="<?=HTTP?>application/views/js/leaflet/leaflet-easyprint.js"></script>

<!-- VueJS -->
<script src="<?=HTTP?>application/views/js/vuejs/vue.min.js"></script>
<!-- /VueJS -->


<!-- time slider -->
<script src="<?=HTTP?>application/views/js/slider/js/ion.rangeSlider.min.js"></script>
<link rel='stylesheet' type='text/css' href='<?=HTTP?>application/views/js/slider/css/normalize.css' />
<link rel='stylesheet' type='text/css' href='<?=HTTP?>application/views/js/slider/css/ion.rangeSlider.css' />
<link rel='stylesheet' type='text/css' href='<?=HTTP?>application/views/js/slider/css/ion.rangeSlider.skinFlat.css' />
<!-- /time slider -->

<link rel='stylesheet' type='text/css' href='<?=HTTP?>application/views/js/leaflet/leaflet.css' />
<link href='<?=HTTP?>application/views/css/map.css' rel='stylesheet' />

<style>
[v-cloak] { display: none; }

</style>

<script>
var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
</script>

<div class='row' id='app'>

    <div class='col-md-5 col-lg-5 col-xs-6 col-sm-6'> 


      <div>
        <div  style='min-height:630px;background-color:#fff;' id='map'> </div>
      </div>

      <div style='padding:0.4em 1em 1em 1em ;background:#eef7f9;text-align:center'  id='date-nav' v-cloak>

      <div style='v-align:middle' class='h3'> CDI :

      <select name='year' id='year' v-model='current.yy' style='width:80px;border:0px dotted #666;background:#eef7f9;color:#555'>
        <option v-for='year in Array.from ((function*(x,y) { while (x <= y) yield x++; })(2002,<?=date('Y')?>))' v-bind:value='year'> 
            {{ year }} 
        </option>
      </select>

      <select name='month' id='month' v-model='current.mm'  style='width:80px;border:0px dotted #666;background:#eef7f9;color:#555'>
        <option v-for='m in Array.from ((function*(x,y) { while (x <= y) yield x++; })(0,11))' v-bind:value='m'> 
            {{ months[m] }} 
        </option>
      </select>
      &nbsp;&nbsp;&nbsp; 
      <button v-bind:class='( (new Date(_data.current.yy, _data.current.mm, 1)) > _data.latest) ? "btn btn-disabled " : "btn btn-primary" ' @click='select(event)' ><span class='fui-arrow-right'></span></button>
       
       </div>

      </div>

    </div>

    <div class='col-md-7 col-lg-7  col-xs-6 col-sm-6 style='min-height: 635px'> 

    <div class='text-center' style='background:#f8f8f8;padding:1em;font-size:1.6em'  id='district-title' v-cloak>    
        {{ _data._thedata.district }} district &nbsp;&nbsp;
        <a class='pull-right btn btn-primary' v-bind:href="'<?=HTTP?>outside/getcsv/' + _data.pcode "><i class='fui-triangle-down'></i> &nbsp;&nbsp;Download .CSV</a>
    </div>


      <div id='cdi-chart' style='width:100%;height:380px;margin-bottom:2em'></div>
      
      <div style='text-align:center;width:100%;height:30px;'>&nbsp</div>
      <div id='slider-range' style='text-align:center;width:100%;height:130px;'></div>
      <div style='text-align:center;width:100%;height:30px;'>&nbsp</div>
      <div id='legend'>
      <?=$legend?>
      </div>
      <div style='text-align:center;width:100%;height:30px;'>&nbsp</div>
      <div>
        <h3 style="font-weight: bolder;">Note on use of NDVI</h3>
        <p>The CDI monitoring tool explores the use of three climatic conditions that influence drought conditions. This includes rainfall, temperature and the Normalised Vegetation Drought Index (NVDI) which is a proxy of soil moisture. The NDVI is a dimensionless index that describes the difference between visible and near-infrared reflectance of vegetation cover and can be used to estimate the density of greenness on an area of land.</p>
        <p>Since the beginning of 2021, there has been a poor correlation of the NDVI and the ground information, therefore resulting to false values of CDI. In this regard, the use of NDVI in generating CDI values has been halted. Meanwhile,  SWALIM is in the process of looking for alternative component to represent soil moisture in place of NDVI</p>
        <p>The current values of CDI shown on the map does not include the NDVI values rather a combination of temperature and rainfall.</p>
      </div>


    </div>

</div>



<script src="<?=HTTP?>application/views/js/amcharts/core.js"></script>
<script src="<?=HTTP?>application/views/js/amcharts/charts.js"></script>
<script src="<?=HTTP?>application/views/js/amcharts/animated.js"></script>


<style>
#chart {
  width: 100%;
  height: 500px;
}

</style>


<!-- Chart code -->

<script>

var _globals = {
    map : {},
    districts_layer : {},
    regions_layer : {},
    districts : {},
    lechart : {},
    selected_layer : null,
    click_selected_layer : null,
    from : null,
    to : null
}

var charts = {
    cdi : null,
}

var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

function prepare_chartdata(raw_data) {

  var chartdata = []

    $.each(raw_data, function (i, o) {
      o.date = i
      chartdata.push(o);
    }) 

    return chartdata
}


function load_cdi_chart(chart_data, element) {

  am4core.ready(function() {

    am4core.useTheme(am4themes_animated);

    // Create chart instance
    var chart = am4core.create("cdi-chart", am4charts.XYChart);
    
    chart.paddingRight = 20;

    // Add data
    chart.data = chart_data;
    chart.dateFormatter.inputDateFormat = "yyyy-MM-dd";

    var title = chart.titles.create();
    title.text = "CDI time series";
    title.fontSize = 25;
    title.marginBottom = 30;

    // Axes
    var dateAxis = chart.xAxes.push(new am4charts.DateAxis());
    chart.dateAxis = dateAxis
    var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
    valueAxis.min = 0.0;
    valueAxis.renderer.labels.template.paddingLeft = -10

    dateAxis.dateFormatter = new am4core.DateFormatter();
    dateAxis.dateFormatter.dateFormat = "YYYY-MM";
    dateAxis.dateFormats.setKey("month", "MMM-YY");
    dateAxis.periodChangeDateFormats.setKey("month", "MMM-YY");
    dateAxis.periodChangeDateFormats.setKey("year", "YYYY");
    
    dateAxis.renderer.labels.template.rotation = 0
    dateAxis.renderer.minGridDistance = 40;
    dateAxis.renderer.grid.template.disabled = true;


    function createRangeGrid(date) {
      var range = dateAxis.axisRanges.create();
      range.date = date;
      range.grid.strokeOpacity = 0.1;
      range.tick.disabled = false;
      range.tick.strokeOpacity = 0.3;
      range.tick.length = 10;
      range.label.rotation = 0
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

    var j = 0;
    for (i=1; i <= chart.data.length; i += 12) {

      if ( (chart.data.length - 1) < i) j = chart.data.length - 1
      else j = i
      var x = chart.data[j].date.split('-')
      createRangeGrid(new Date(x[0], parseInt(x[1])-1, x[2]));
      // createRange(new Date(x[0], 0, 1), new Date(parseInt(x[0])+1, 0, 1), x[0])
    }

    // Create data series
    var series = chart.series.push(new am4charts.LineSeries());
    series.name = "CDI";
    series.dataFields.valueY = "cdi";
    series.dataFields.dateX = "date";
    series.tooltipText = "{cdi}"
    series.strokeWidth = 2;
    series.minBulletDistance = 15;
    series.tensionX = 2.7;

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

    chart.legend = new am4charts.Legend();
    chart.legend.labels.template.text = "{name}";

    chart.cursor = new am4charts.XYCursor();
    chart.cursor.lineY.disabled = true;
    var axisTooltip = valueAxis.tooltip;
    axisTooltip.disabled = true;

    charts.cdi = chart
  }); 

}

</script>



<script>

ACCESS_TOKEN = 'pk.eyJ1IjoibmR1bmdpIiwiYSI6ImNpcm43ZGd2eTAwNWlocG0xazU2ZHJvNjUifQ.9krBA9p2y0CKmRaIprvNjA';
BASE_MAP_URL = 'https://{s}.basemaps.cartocdn.com/light_nolabels/{z}/{x}/{y}.png';
ATTR = '';'&copy; <a href="https://osm.org/copyright">OpenStreetMap</a> contributors';

var monthName = ["Jan", "Feb", "Mar", "Apr", "May", "Jun",
      "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
    ];

var _data = {
    // current : new Date('<?=$date['year']?>', '<?=($date['month']-1)?>', 1),
    latest : new Date('<?=$date['year']?>', '<?=($date['month']-1)?>', 1),
    oldest : new Date(2001, 0, 1),
    _thedata : {district : '', data : []},
    pcode : '<?=$min_cdi_pcode?>',
    current : {
        yy : '<?=$date['year']?>',
        mm : '<?=($date['month']-1)?>'
    }
}

var _computed = {
}

var district_style = {
        weight: 4,
        color: '#fff',
        fillOpacity: 1,
        opacity : 1
    }

prev_style= {}

var selected_layer_style = {
        weight: 6,
        color: '#08c',
        opacity: 1,
    }

var dist_title = new Vue({

    el: '#district-title',
    data: _data,
})

var cdimap = new Vue({

    el: '#date-nav',
    data: _data,
    computed :  { },

    mounted: function() {       

        _globals.map = L.map('map', {scrollWheelZoom: false}).setView([5.299432,46.5192871], zoom = 6.2);

        L.tileLayer(BASE_MAP_URL, {
            attribution: ATTR,
            maxZoom: 18,
            id: 'mapbox.light',
            accessToken: ACCESS_TOKEN           
        }).addTo(_globals.map);

        this.region_labels();
        this.load_map();
        this.update_map();   
        this.load_charts()   

        // add print button
        L.easyPrint({
            title: 'Download map as PNG image',
            position: 'bottomright',
            exportOnly : true,
            hideControlContainer : true
        }).addTo(_globals.map);
    },

    methods: {

        load_map : function () {
          var $this = this
          _globals.districts_layer = L.geoJson(district_boundaries, { })
          .addTo(_globals.map)
        },

        update_map : function () {

            var $this = this
            
            $('body').addClass('wait')
            $.post('<?=HTTP?>data/_cdi/', {'date' : monthName[_data.current.mm] +'-'+_data.current.yy}, function (cdi) {


                if (_globals.click_selected_layer) {
                    _globals.selected_layer = _globals.click_selected_layer
                    _data.pcode = _globals.click_selected_layer.feature.properties.DIS_CODE
                } else {
                    var lowest_index_pcode = $this.lowest_index_pcode(cdi)
                    _globals.selected_layer = null
                    _data.pcode = lowest_index_pcode
                }

                _globals.districts_layer.eachLayer(function (layer) {

                    if (cdi[layer.feature.properties.DIS_CODE] != undefined)                     
                      $this.descriptions(layer.feature.properties.DIS_CODE, layer, cdi)     

                    var style = district_style
                    
                    style['fillColor'] =  (cdi[layer.feature.properties.DIS_CODE] != undefined) ? $this.getCDIColor(cdi[layer.feature.properties.DIS_CODE].cdi) : "#ddd"
                    style['fillOpacity'] = 1
                    
                    layer.setStyle(style)

                  if ( _data.pcode && (_data.pcode == layer.feature.properties.DIS_CODE) ) {
                    layer.setStyle(selected_layer_style)
                    _globals.selected_layer = layer 
                    _globals.prev_layer = layer 
                    layer.bringToFront()
                  }

                  
                  layer.on({
                      click: function (e) {    

                        if ( _globals.selected_layer && _data.pcode && (_data.pcode != layer.feature.properties.DIS_CODE) ) {
                            var blah = district_style
                            blah['fillColor'] = _globals.prev_layer.options.fillColor
                            _globals.selected_layer.setStyle(blah)
                        }

                        _data.pcode = layer.feature.properties.DIS_CODE
                        _globals.selected_layer = layer   
                        _globals.prev_layer = layer                           
                        _globals.click_selected_layer = layer                              
                        layer.setStyle(selected_layer_style).bringToFront();                            
                        $this.update_charts()
                      }

                  })

                })                              
                
                // $('body').removeClass('wait')

            }, 'json')
        },

        getCDIColor : function (cdi) {

            return cdi < 0.4 ? '#930905' :
                   cdi < 0.6 ? '#d03a27' :
                   cdi < 0.8 ? '#e6987b' :
                   cdi < 1.0 ? '#ffffbe' : '#d2fbd2'; 
        },

        number_format : function (x) {
            return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        },

        capitalize : function (text) {
            return text.replace(/\b\w/g , function(m){ return m.toUpperCase(); } );
        },

        region_labels : function () {

            _globals.map.createPane('regions');

            var regions_layer = L.geoJson(regional_boundaries, {
                pane : 'regions',
                style: {
                    fillOpacity : 0,
                    weight:2,
                    opacity:0.6,
                    color:'#aaa'
                },
                interactive: false,

            }).addTo(_globals.map);

            
            var districts = {}
            $.each(district_capitals.features, function (i, feature) {
                districts[feature.properties.DIS_CODE] = feature.geometry.coordinates                
            })

            regions_layer.eachLayer(function (layer) {

                var feature = layer.feature
                var region_centers = {            
                        11:1104,
                        12:1203,
                        13:1301,
                        14:1404,
                        15:1501,
                        16:1604,
                        17:1701,
                        18:1803,
                        19:1901,
                        20:2001,
                        21:2102,
                        22:22,
                        23:2304,
                        24:2401,
                        25:2502,
                        26:2604,
                        27:2703,
                        28:2802
                }

                var centre_district = region_centers[feature.properties.REG_CODE]
                var mid_dis = districts[centre_district]

                var m = L.marker([mid_dis[1], mid_dis[0]], {draggable:false,  opacity: 0.01 })
                .addTo(_globals.map)
                .bindTooltip(feature.properties.REG_NAME, {
                    permanent: true, 
                    direction:"center", 
                    className : 'region-label', 
                    offset : [-55,-50]
                }).openTooltip()
            })
          
        },

        descriptions : function (k, l, cdi) {

            $this = this

            if (cdi[k] == undefined) return

            l.unbindTooltip()

            var desc = "<div class='district-label "+this.classify(cdi[k].cdi)+"'><div class='district-title ellipsis'>"+cdi[k].district+" / "+ this.classify(cdi[k].cdi) +"</div> </div>"

            desc += 
            "<table class='details table table-striped '>"+
                " <tr><td> CDI&nbsp;</td><td class='col-1'>" + cdi[k].cdi + "</div></tr>" +
                " <tr><td colspan='2'>" + 
                "</td></tr> </table> </div>"


            l.bindTooltip(desc, { 
                'noHide': true, 
                offset : [40, -40],
                'sticky' : true,
                direction: 'top',
                sticky : true
            });   
        },

        classify : function (cdi) {
            return cdi < 0.4 ? 'extreme' :
                   cdi < 0.6 ? 'severe' :
                   cdi < 0.8 ? 'moderate' :
                   cdi < 1.0 ? 'mild' : 'normal';             
        },

        select : function() {
            var $this = this
            var _current = new Date(_data.current.yy, _data.current.mm, 1);
            if (_current < _data.oldest) return;
            if (_current > _data.latest) return;
            this.update_map()
            setTimeout(function (){$this.update_charts()}, 800)
        }, 

        load_charts : function () {
            $('body').addClass('wait')
            var _thedate = "01-" + monthName[_data.current.mm] + "-" + _data.current.yy
            
            _globals.from = _data.oldest
            _globals.to = _data.current

            $.get("<?=HTTP?>data/chart_data_cdi/" + _data.pcode + "/" + _thedate, function (_district_data) {

                _data._thedata = _district_data
                load_cdi_chart(prepare_chartdata(_district_data.data.cdi), 'cdi-chart')  

                $('body').removeClass('wait')

            }, 'json') 

        },

        update_charts : function()
        {
            $('body').addClass('wait')
            var _thedate = "01-" + monthName[_data.current.mm] + "-" + _data.current.yy

            $.get("<?=HTTP?>data/chart_data_cdi/" + _data.pcode + "/" + _thedate, function (_district_data) {

                _data._thedata = _district_data             
   
                for (x in charts) {
                    charts[x].data = prepare_chartdata(_district_data.data[x])
                }
   
                slidertimer = setTimeout(function () {
                    for (x in charts) {
                        charts[x].dateAxis.zoomToDates(_globals.from, _globals.to);
                    }
                }, '1000')

                update_slider()

                $('body').removeClass('wait')

            }, 'json')                          
        },

        highlightSelected : function () {
            _data.selected_layer.setStyle(selected_layer_style);
        },

        lowest_index_pcode : function (data) {
            var lowest = 10;
            var lowest_pcode = 0;

            $.each(data, function (pcode, district) {
                if (district.cdi < lowest) {
                    lowest = district.cdi
                    lowest_pcode = pcode
                }
            })

            return lowest_pcode
        }

    },

})

slidertimer = 0
var slider = $("#slider-range").ionRangeSlider({
    type: "double",
    min: _data.oldest/1000,
    max: new Date(_data.current.yy, _data.current.mm, 1)/1000,
    from: _globals.from,
    to: _globals.to,
    hide_min_max: true,
    force_edges: true,
    grid: true,
    prettify_enabled: true,
    prettify: function (timestamp) {
        var date = new Date(timestamp * 1000);        
        return  date.getFullYear() + '-' + months[date.getMonth()];
    },

    onStart: function (range) {
        clearTimeout(slidertimer);        
    },
    onChange: function (range) {
        clearTimeout(slidertimer);
    },    
    onFinish: function (range) {
        var $this = $("#slider-range")

        slidertimer = setTimeout(function () {

            _globals.from = new Date(range.from * 1000);
            _globals.to = new Date(range.to * 1000);

            for (x in charts) {
                charts[x].dateAxis.zoomToDates(_globals.from, _globals.to);
            }

        }, 20);
    },
});

var slider = $("#slider-range").data("ionRangeSlider");

function update_slider()
{
    slider.update({
        from: _globals.from /1000,
        to: _globals.to /1000,        
    })
}

$('.leaflet-control-attribution').css('opacity', 0.1);


</script>

