<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<title>Timer Stats by Zipcore</title>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
<link href="inc/style.css" rel="stylesheet" type="text/css" />
</head>
<body>

<?php
$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$start = $time;

require_once("inc/config.inc.php");
require_once("inc/functions.inc.php");
require_once("inc/pagenavigation.class.php");

$astylename = array_keys($styles);
$astyleid = array_values($styles);

$atrackname = array_keys($track_list);
$atrackid = array_values($track_list);

$aranks = array_keys($chat_ranks);
$arank = array_values($chat_ranks);

echo "<div id='container'>
<div id='logo'>
  <center><h1>".$name." - Timer Stats</h1></center>
</div>";

if($menu_enable == 1)
{
	echo "<div id='menu'>";
	echo "<center>";
	echo "<ul>";
	if($home_button == 1) echo "<li><b><a href='".$path_home."'><home><span>Forum</span></home></a></b></li>";
	echo "<li><b><a href='index.php'><server><span>Server Info</span></server></a></b></li>";
	echo "<li><b><a href='mapinfo.php'><mapinfo><span>Map Info</span></mapinfo></a></b></li>";
	echo "<li><b><a href='points.php'><points><span>Points Ranking</span></points></a></b></li>";
	echo "<li><b><a href='records.php'><records><span>Map Records</span></records></a></b></li>";
	echo "<li><b><a href='latest.php'><latest><span>Latest Records</span></latest></a></b></li>";
	echo "<li><b><a href='ranks.php'><ranks><span>Chatranks</span></ranks></a></b></li>";
	echo "<li><b><a href='player.php'><player><span>Player Search</span></player></a></b></li>";
	if($join_button == 1) echo "<li class='last'><b><a href='steam://connect/".$gameserverip.":".$serverport."'><join><span>Join Server</span></join></a></b></li>";
	echo "</ul>";
	echo "</center>";
	echo "</div>";
	echo "<div id='logo'>";
	echo "<h1></h1>";
	echo "</div>";
}

//NAVIGATION
$query = $link->query("SELECT COUNT(*) FROM `ranks` WHERE `points` >= ".$points_min."");
$array2 = mysqli_fetch_array($query);
$totalplayers = $array2[0];

$nav = new PageNavigation($totalplayers, $limit_view);

//COUNT MAPS
$query = $link->query("SELECT COUNT(*) FROM `mapzone` WHERE `type` = 0");
$array2 = mysqli_fetch_array($query);
$total_maps = $array2[0];

$query = $link->query("SELECT COUNT(*) FROM `mapzone` WHERE `type` = 7");
$array2 = mysqli_fetch_array($query);
$total_trackmaps = $array2[0];

$query = $link->query("SELECT COUNT(*) FROM `mapzone` WHERE `type` = 27");
$array2 = mysqli_fetch_array($query);
$total_shortmaps = $array2[0];

$query = $link->query("SELECT SUM(`points`) FROM `ranks` WHERE `points` > 0");
$array2 = mysqli_fetch_array($query);
$total_points = $array2[0];

$query = $link->query("SELECT AVG(`points`) FROM `ranks` WHERE `points` > 0");
$array2 = mysqli_fetch_array($query);
$avg_points = $array2[0];

//GET POINTS ETC...
$sql = "SELECT `points`, `lastname`, `lastplay`, `auth` FROM `ranks` WHERE `points` >= ".$points_min." ORDER BY `points` DESC LIMIT ".$nav->sql_limit."";
$rekorde = $link->query($sql);

echo "<div class='serverinfo'>";

echo "<center>";
echo "Total Points: <b>".$total_points."</b> Average Points: <b>".round($avg_points)."</b>";
echo "</center>";

echo "</div>"; //serverinfo

echo "<div class='list'>";
//CREATE TABLE
echo "<h1><center><p><b>Points Top</b></center></h1>";
echo "<center><table cellpadding=\"3\" cellspacing=\"0\" border=\"1\">";

echo $nav->createPageBar('pagebar-top');
echo $nav->createPageSelBox();

echo "<tr>";
echo "<th>#</th>";
echo "<th>Chatrank</th>";
echo "<th>Playername</th>";
echo "<th>Points</th>";
echo "<th>Depletion</th>";
echo "<th>Last Seen</th>";

echo "</tr>";

//FILL TABLE
$count = $nav->first_item_id;
while($array = mysqli_fetch_array($rekorde))
{
	//BUILD COMUNITY PROFILE LINK
	$steam64 = (int) steam2friend($array["auth"]);

	//FORMAT LAST PLAY
	$lastplay = $array["lastplay"];
	$timestamp = gmdate("Y-m-d\ H:i:s", $lastplay);
	
	$steamid = $array["auth"];
	$steamfix = $array["auth"];
	
	if($steamid[6] == '0'){
		$steamfix[6] = '1';
	}
	else{
		$steamfix[6] = '0';
	}

	$ex = '"';

	//GetFinishd Maps Count
	$query = $link->query("SELECT COUNT(*) FROM `round` WHERE `track` = 0 AND (`auth` = ".$ex.$array["auth"].$ex." OR `auth` = ".$ex.$steamfix.$ex.")");
	$array2 = mysqli_fetch_array($query);
	$count_records = $array2[0];
	
	$query = $link->query("SELECT COUNT(*) FROM `round` WHERE `track` = 1 AND (`auth` = ".$ex.$array["auth"].$ex." OR `auth` = ".$ex.$steamfix.$ex.")");
	$array2 = mysqli_fetch_array($query);
	$count_trackrecords = $array2[0];
	
	$query = $link->query("SELECT COUNT(*) FROM `round` WHERE `track` = 2 AND (`auth` = ".$ex.$array["auth"].$ex." OR `auth` = ".$ex.$steamfix.$ex.")");
	$array2 = mysqli_fetch_array($query);
	$count_shortrecords = $array2[0];
	
	$query = $link->query("SELECT COUNT(*) FROM (SELECT * FROM `round` WHERE (`auth` = ".$ex.$array["auth"].$ex." OR `auth` = ".$ex.$steamfix.$ex.") AND `track` = 0 GROUP BY `map`) AS s");
	$array2 = mysqli_fetch_array($query);
	$count_normal_records = $array2[0];
	
	$query = $link->query("SELECT COUNT(*) FROM (SELECT * FROM `round` WHERE (`auth` = ".$ex.$array["auth"].$ex." OR `auth` = ".$ex.$steamfix.$ex.") AND `track` = 1 GROUP BY `map`) AS s");
	$array2 = mysqli_fetch_array($query);
	$count_track_records = $array2[0];
	
	$query = $link->query("SELECT COUNT(*) FROM (SELECT * FROM `round` WHERE (`auth` = ".$ex.$array["auth"].$ex." OR `auth` = ".$ex.$steamfix.$ex.") AND `track` = 2 GROUP BY `map`) AS s");
	$array2 = mysqli_fetch_array($query);
	$count_short_records = $array2[0];
	
	$maxstyles = count($astyleid);
	$complete = round(100*($count_normal_records+$count_track_records+$count_short_records)/($total_maps+$total_trackmaps+$total_shortmaps));

	//CHATRANK
	$chatrank = $unranked;
	for ($i = 0; $i < count($arank); $i++) {
		if($arank[$i] >= $count){
			$chatrank = $aranks[$i];
			break;
		}
	}
	echo "<tr>";
	echo "<td align=\"middle\">".$count."</td>";
	echo "<td align=\"middle\">".$chatrank."</td>";
	echo "<td align=\"left\"><a href='player.php?searchkey=".$array["auth"]."'>&nbsp;".$array["lastname"]."&nbsp;</a></td>";
	echo "<td align=\"middle\">".$array["points"]."</td>";
	
	echo "<td align=middle>".$complete."%</td>";
	
	echo "<td align=middle>".$timestamp."</td>";
	echo "</tr>";
	$count ++; 
}
echo "</table>";
echo $nav->createPageBar('pagebar-bottom');
echo "</center>";
echo "</div>";
echo "</div>";

echo "<table width='100%' border='0' cellpadding='5' cellspacing='0'>";
echo "<tr>";
echo "<td>";

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$finish = $time;
$total_time = round(($finish - $start), 4);
echo "<center>Page generated in ".$total_time." seconds.</center>";
echo "<center><a target='_blank' href='http://forums.alliedmods.net/member.php?u=74431'>Timer Stats &copy; 2014 by Zipcore</a></center>";

echo "</td>";
echo "</tr>";
echo "</table>";


?>
</body>
</html>