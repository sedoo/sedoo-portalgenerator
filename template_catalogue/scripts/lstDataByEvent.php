<?php
require_once('bd/bdConnect.php');
require_once('bd/event.php');
require_once('bd/dataset.php');
require_once('bd/url_event.php');
require_once('scripts/lstDataUtils.php');

function lstByEvent($eventId)
{
	$event = new event;
	$event = $event->getById($eventId);
	echo "<h1>Data available for date ".$event->event_date_begin." to ".$event->event_date_end."</h1>";

	include 'legende.php';

	$query = "select dats_id, dats_title from dataset where dats_id in (select distinct dats_id from url) and (dats_date_begin is null or dats_date_begin <= '".$event->event_date_end."') and (dats_date_end is null or dats_date_end >= '".$event->event_date_begin."') AND (is_archived is null OR NOT is_archived) order by dats_title";
//	echo $query.'<br>';
	$dts = new dataset;
	$dts_list = $dts->getOnlyTitles($query);
        echo "<ul>";
        foreach ($dts_list as $dt){
           echo '<li>'.printDataset($dt).'</li>';
        }
        echo "</ul>";
}

$eventId = $_REQUEST['event_id'];
if (isset($eventId) && !empty($eventId))
{
	lstByEvent($eventId);
}
else
{
	echo '<h1>Events</h1><br><br>';
	$ev = new event;
	$ev_list = $ev->getAll();
	echo "<ul>";
	foreach($ev_list as $event)
	{
		echo "<li><a href='?event_id=".$event->event_id."'>".$event->event_name."</a></li>";
	}
	echo "</ul>";
}
?>
