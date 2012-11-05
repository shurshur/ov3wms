<?

include_once("config.php");

$pg = pg_connect($pgconnstr);

$lat = $_GET["lat"]+0;
$lon = $_GET["lon"]+0;

$q = "SELECT entity_id, FIRST(usgs_id) AS usgs_id, FIRST(cloud) AS cloud, FIRST(acq_date) AS acq_date FROM ov3cat WHERE ST_SetSRID(ST_MakePoint(".$lon.",".$lat."),4326) && the_geom GROUP BY entity_id";
$res = pg_query($q);

while($row = pg_fetch_assoc($res)) {
  $id = $row["usgs_id"];
  $lid = strtolower($id);
  if(!$row["cloud"] && $row["cloud"]!="0") $row["cloud"]="???";
  $add = "";
  if (file_exists($ov3base."/rec/$lid.rec.tif")) $add="*";

  $links = array();
  if (file_exists($ov3base."/data/$id.ZIP")) $links []= "<a href=data/$id.ZIP>zip</a>";
  if (file_exists($ov3base."/pv/$lid.jpg")) $links []= "<a href=pv/$lid.jpg>pv</a>";
  if (file_exists($ov3base."/pv/$lid.jgw")) $links []= "<a href=pv/$lid.jgw>pv-jgw</a>";
  if (file_exists($ov3base."/rec/$lid.rec.tif")) $links []= "<a href=rec/$lid.rec.tif>rec</a>";
  if (file_exists($ov3base."/map/$id.map")) $links []= "<a href=\"javascript:addwms('$id')\">wms</a>";
  $links = implode("&nbsp;", $links);
  print "$add<a href=./browse:$id target=_blank>$id</a><br>&raquo;&nbsp;дата:&nbsp;".$row["acq_date"]."&nbsp;облачность&nbsp;".$row["cloud"]."%&nbsp;$links<br>";
}

?>
