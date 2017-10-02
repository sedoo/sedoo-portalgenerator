<?php
require_once ("bd/dataset.php");
require_once ("bd/dats_quality.php");
require_once ('filtreProjets.php');
require_once ('conf/conf.php');

$bd = new bdConnect ();
$bd->db_open ();
if ( isset($_REQUEST['datsId']) ){
	$datsId = $_REQUEST['datsId'];
}else{
	$datsId = 0;
}
if ($datsId > 0){
	$dats = new dataset();
	$dats = $dats->getById($datsId);
	if ($project_name == strtolower(MainProject)){
		echo "<a href='/Admin-Corner/?adm&pageId=13'>&lt;&lt;&nbsp;Back</a>";
	}else{
		echo "<a href='/$project_name/Admin-Corner/?adm&pageId=13'>&lt;&lt;&nbsp;Back</a>";
	}
	echo "<h1>$dats->dats_title</h1><p/>";
	$qual = new dats_quality();
	$qual->init($dats);
	$query = "select dats_type_id from dats_type left join dataset using (dats_id) where dats_id in ($datsId)";
	$req = $bd->exec ( $query );
	$datsType = pg_fetch_assoc ( $req );
	$qual->display($datsType['dats_type_id']);
}else{
	function datstype ($value, $project_name){
		$projects = 'SELECT DISTINCT dats_id FROM dats_proj WHERE project_id IN ('.get_filtre_projets($project_name).')';
		$dts = new dataset;
		$query = "";
		if ($value>0){
		$liste_dats = $dts->getByQuery("SELECT * FROM dataset LEFT JOIN dats_type using (dats_id) WHERE dats_id IN ($projects) AND is_requested IS NULL AND dats_type_id in ($value)  ORDER BY dats_title ");
		echo '<table class="quality"><tr><th align="center">Dataset</th><th>Dataset description</th><th>Dates</th><th>Use constraints</th><th>Sites</th><th>Params</th><th>Total</th>';
		echo '</tr>';
		$urlBase =  $_SERVER['REQUEST_URI'];
		foreach($liste_dats as $dats){
			echo "<tr><td><a href='$urlBase&datsId=$dats->dats_id'>$dats->dats_title</a></td>";
			$qual = new dats_quality();
			$qual->init($dats);
			printTd(round($qual->getScoreCore() + $qual->getScoreInfo(),0), 25, $qual->commentCore.$qual->commentInfo);
			printTd(round($qual->getScoreDates(),0), 15, $qual->commentDates);
			printTd(round($qual->getScoreUse(),0), 10, $qual->commentUse);
			printTd(round($qual->getScoreSite(),0), 15, $qual->commentSite);
			printTd(round($qual->getScoreVar(),0), 20, $qual->commentVar);
			$score = $qual->getScore();
			$color = getCouleurScore($score,100);
			echo "<td><b><font color='$color'>$score %</font></b></td></tr>";
		}
		echo '</table>';
		}else {
			$liste_dats = $dts->getByQuery("SELECT * FROM dataset LEFT JOIN dats_type using (dats_id) WHERE dats_id IN ($projects) AND is_requested IS NULL AND dats_type_id IS NULL ORDER BY dats_title ");
			echo '<table class="quality"><tr><th align="center">Dataset</th><th>Dataset description</th><th>Dates</th><th>Use constraints</th><th>Sensors</th><th>Sites</th><th>Params</th><th>Total</th>';
			echo '</tr>';
			$urlBase =  $_SERVER['REQUEST_URI'];
			foreach($liste_dats as $dats){
				echo "<tr><td><a href='$urlBase&datsId=$dats->dats_id'>$dats->dats_title</a></td>";
				$qual = new dats_quality();
				$qual->init($dats);
				printTd(round($qual->getScoreCore() + $qual->getScoreInfo(),0), 25, $qual->commentCore.$qual->commentInfo);
				printTd(round($qual->getScoreDates(),0), 15, $qual->commentDates);
				printTd(round($qual->getScoreUse(),0), 10, $qual->commentUse);
				printTd(round($qual->getScoreSensor(),0), 15, $qual->commentSensor);
				printTd(round($qual->getScoreSite(),0), 15, $qual->commentSite);
				printTd(round($qual->getScoreVar(),0), 20, $qual->commentVar);
				$score = $qual->getScore();
				$color = getCouleurScore($score,100);
				echo "<td><b><font color='$color'>$score %</font></b></td></tr>";
			}
			echo '</table>';
		}
	}
	echo "<h1>Metadata quality</h1><p/>";
	echo '<script src="/utils/jquery-ui-1.9.2/jquery-1.8.3.js"></script>';
	echo '<script src="/utils/jquery-ui-1.9.2/ui/jquery.ui.core.js"></script>';
	echo '<script src="/utils/jquery-ui-1.9.2/ui/jquery.ui.widget.js"></script>';
	echo '<script src="/utils/jquery-ui-1.9.2/ui/jquery.ui.tabs.js"></script>';
	echo '<script>
	$(function() {
		$( "#tabs" ).tabs();
	});
	</script>';
	static $dats_type = array(0,1,2);
	echo '<div id="content">';
	echo '<div id="tabs" >';
	echo '<ul>';
	echo "<li><a href='#datsType=$dats_type[0]' title='IN SITU'>IN SITU</a></li>";
	echo "<li><a href='#datsType=$dats_type[2]' title='MODEL'>MODEL</a></li>";
    echo  "<li><a href='#datsType=$dats_type[1]' title='SATELLITE'>SATELLITE</a></li>";
	echo '</ul>';
		echo "<div id='datsType=$dats_type[0]'>";
		datstype ($dats_type[0], $project_name);
		echo '</div>';
		echo "<div id='datsType=$dats_type[1]'>";
		datstype ($dats_type[1], $project_name);
		echo '</div>';
		echo "<div id='datsType=$dats_type[2]'>";
		datstype ($dats_type[2], $project_name);
		echo '</div>';
	echo '</div>';
	echo '</div>';
}
?>
