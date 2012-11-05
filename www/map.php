<html>
<head>
 <link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.4/leaflet.css" />
 <!--[if lte IE 8]>
  <link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.4/leaflet.ie.css" />
 <![endif]-->
 <script src="http://cdn.leafletjs.com/leaflet-0.4/leaflet.js"></script>
 <script src="leaflet/Scale.js"></script>
 <script src="leaflet/Permalink.js"></script>
 <script src="leaflet/Bing.js"></script>
</head>
<body onload="init()">
<script>
function getCookie(name) {
  var cookie = " " + document.cookie;
  var search = " " + name + "=";
  var setStr = null;
  var offset = 0;
  var end = 0;
  if (cookie.length > 0) {
    offset = cookie.indexOf(search);
    if (offset != -1) {
      offset += search.length;
      end = cookie.indexOf(";", offset)
  if (end == -1) {
    end = cookie.length;
  }
      setStr = unescape(cookie.substring(offset, end));
    }
  }
  return(setStr);
}

function init() {
  var loc = getCookie('_osm_location');
  var center;
  var zoom;
  if(loc) {
    var locs = loc.split('|');
    center = new L.LatLng(locs[1], locs[0]);
    zoom = locs[2];
  } else {
    center = new L.LatLng(62.0, 88.0);
    zoom = 8;
  }

  var OSM_COPY = 'Map data &copy; <a href="http://www.openstreetmap.org/copyright" target="_new">OpenStreetMap</a> contributors'
  var KOSM_COPY = 'map rendering by <a href="http://kosmosnimki.ru">kosmosnimki.ru</a>';
  var OV3_COPY = 'OrbView-3 imagery &copy; Public Domain from <a href="http://geoeye.com/" target="_new">GeoEye, inc.</a> and <a href="http://www.usgs.gov/" target="_new">US Geological Survey</a>';

  var map = new L.Map('map', {center: center, zoom: zoom});
  var mapnik = new L.TileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 18, attribution: OSM_COPY }); 
  var km = new L.TileLayer('http://{s}.tile.osmosnimki.ru/kosmo/{z}/{x}/{y}.png', {
        maxZoom: 18, 
        attribution: OSM_COPY + ', ' + KOSM_COPY }
  );

  // Bing key for osm.sbin.ru 
  var bing = new L.BingLayer('AjNsLhRbwTu3T2lUw5AuzE7oCERzotoAdzGXnK8-lWKKlc2Ax3d9kzbxbdi3IdKt', {maxZoom: 18}); 
  var layers = { "Карта": mapnik, "Осмоснимки": km, "Bing": bing };
  var kh = new L.TileLayer('http://{s}.tile.osmosnimki.ru/hyb/{z}/{x}/{y}.png', {
        maxZoom: 18, 
        attribution: OSM_COPY + ', ' + KOSM_COPY }
    ); 
  var ov3 = new L.TileLayer.WMS('http://osm.sbin.ru/ov3/',{minZoom: 6, layers: ['orbview3rec,orbview3pv'], format: 'image/png', attribution: OV3_COPY});
  var ov3cat = new L.TileLayer.WMS('http://osm.sbin.ru/ov3/',{layers: ['orbview3cat'], format: 'image/png', attribution: OV3_COPY});
  var overlays = { "OrbView-3": ov3, "OrbView-3 границы": ov3cat, "Осмоснимки (гибрид)": kh };

  map.addLayer(km);
  map.addLayer(ov3);
  map.addLayer(ov3cat);
  map.addLayer(kh);

  control_layers = new L.Control.Layers(layers, overlays);
  var permalink = new L.Control.Permalink(control_layers);
  map.addControl(control_layers);
  map.addControl(permalink);
  var scale = new L.Control.Scale({});
  map.addControl(scale);

  var popup = new L.Popup();
  map.on('click', onMapClick);

  map.on('moveend', saveLocation);

  function onMapClick(e) {
    var latlngStr = '(' + e.latlng.lat.toFixed(3) + ', ' + e.latlng.lng.toFixed(3) + ')';

    var url = 'click?lat='+e.latlng.lat+'&lon='+e.latlng.lng;
    var rq = new XMLHttpRequest();
    rq.open('GET', url, false);
    rq.send(null);
    if(rq.status == 200) {
      popup.setLatLng(e.latlng);
      popup.setContent("You clicked the map at " + latlngStr + "<hr>" + rq.responseText);
      map.openPopup(popup);
    }
  }

  function saveLocation() {
    var ll = map.getCenter();
    var z = map.getZoom();
    var d = new Date();
    d.setYear(d.getFullYear()+10);

    document.cookie = "_osm_location=" + ll.lng + "|" + ll.lat + "|" + z + "; expires=" + d.toGMTString();
  }

}

function addwms(id) { 
  var rq = new XMLHttpRequest();
  var url='http://127.0.0.1:8111/imagery?title=OrbView-3 - '+id+'&urldecode=false&url=wms:http://osm.sbin.ru/ov3/'+id+'?FORMAT=image/png&VERSION=1.1.1&SERVICE=WMS&REQUEST=GetMap&LAYERS=orbview3rec&STYLES=&SRS={proj}&WIDTH={width}&HEIGHT={height}&BBOX={bbox}';
  rq.open('GET',url,true);
  rq.send(null);
}
</script>
<table border=0 width=100% height=100%>
<tr><td><h2>Снимки OrbView-3</h2><a href="./info" target=_blank>Подробнее</a>. Клик по карте показывает список снимков в этой точке. Звёздочка - снимок
уже обработан.</td></tr>
<tr><td height=100%>
<div id="map" style="width: 100%; height: 100%"></div>
</td></tr>
</table>
