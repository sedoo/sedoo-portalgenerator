<?php

require_once ("bd/sensor.php");
require_once ("bd/place.php");

$satId=$_GET["satId"];

header("Content-Type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
echo "<response>";

if (isset($satId) && ($satId > 0)){

	$sensor = new sensor;
	$listeInstrus = $sensor->getByPlace($satId);
	foreach ($listeInstrus as $instru){
		echo "<instrument id=\"".$instru->sensor_id."\">";
		echo $instru->sensor_model;
		echo "</instrument>";
	}
	
	
}
echo "</response>";
?>

