<?
include_once("config.php");
?>
<style>
table { line-height: 10px; }
td { font-size: 10px; }
</style>
<?
$id = $_GET["id"];
$lid = strtolower($id);
$id = strtoupper($id);

$pg = pg_connect($pgconnstr);

$q = "SELECT * FROM ov3cat WHERE usgs_id='".pg_escape_string($id)."'";
$res = pg_query($q);

if (pg_num_rows($res) < 1) {
  print "No image with ID <b>$id</b>\n";
  exit;
}
$row = pg_fetch_assoc($res);
unset($row["gid"]);
unset($row["the_geom"]);

print "OrbView-3 <b>$id<b><hr>";

print "<b>Metadata:</b>";

print "<table border=0>\n";
print "<tr><td colspan=3></td></tr>\n";

foreach($row as $k=>$v) {
  print "<tr><td align=right><b>$k:</b></td><td>&nbsp;</td><td align=left>".$v."</td></tr>\n";
}
print "</table>";

print "<hr>";

if (file_exists($ov3base."/pv/$lid.jgw")) {
  print "Preview (<a href=pv/$lid.jgw>jgw</a>):<br><img src=pv/$lid.jpg>";
} else {
  #print "No preview image, use USGS preview:<br><img src=".$row["browse"].">";
  print "Preview:<br><img src=".$row["browse"].">";
}

?>
