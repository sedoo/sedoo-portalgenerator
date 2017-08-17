<?php

require_once('bd/inserted_dataset.php');
require_once('bd/variable.php');
require_once('bd/place.php');
require_once('sortie/print_utils.php');
require_once('scripts/filtreProjets.php');
require_once('utils/calendrier_utils.php');

$insD = new inserted_dataset;

$urlWSGraph = 'http://sedoo.sedoo.fr/mistrals_visu/service/graph/st';

$url = $_SERVER['REQUEST_URI'];
$url_courte = parse_url($url, PHP_URL_PATH).'?adm&pageId=9';
if(isset($_REQUEST['ins_dats_id']) && ! empty($_REQUEST['ins_dats_id']))
	$ins_dats_id = $_REQUEST['ins_dats_id'];
if(isset($_REQUEST['var_id']) && ! empty($_REQUEST['var_id']))
	$var_id = $_REQUEST['var_id'];
if(isset($_REQUEST['place_id']) && ! empty($_REQUEST['place_id']))
	$place_id = $_REQUEST['place_id'];
if(isset($_REQUEST['year']) && ! empty($_REQUEST['year']))
	$year = $_REQUEST['year'];

$withDats = isset($ins_dats_id) && !empty($ins_dats_id);
$withVar = isset($var_id) && !empty($var_id);
$withPlace = isset($place_id) && !empty($place_id);
$withYear = isset($year) && !empty($year);


if ( $withDats ) {

	$dataset = $insD->getById($ins_dats_id,$var_id,$place_id);

	if ( $withVar || $withPlace)
		$title = "<a href='$url_courte&ins_dats_id=$ins_dats_id'>".$dataset->ins_dats_name.'</a>';
	else{
		$title = $dataset->ins_dats_name;
		
		
	}
	if ( $withVar ) {
		$var = new variable;
		$var = $var->getById($var_id);
		$title .= " / <a href='$url_courte&ins_dats_id=$ins_dats_id&var_id=$var_id'>".printVarName($var).'</a>';
	}

	if ( $withPlace ) {
		$place = new place;
		$place = $place->getById($place_id);
		$title .= " / <a href='$url_courte&ins_dats_id=$ins_dats_id&place_id=$place_id'>".$place->place_name.'</a>';
	}

	echo "<h1>$title</h1><br/><br/>";
	
	if (! ($withPlace || $withVar) ){
		echo "<h2>Database information</h2>";
		echo "<br/>Dataset id: $dataset->ins_dats_id";
		echo "<br/>Created on $dataset->date_insertion, last modified on $dataset->date_last_update<br/>";
		
		echo "<h2>Corresponding metadata</h2>";
		foreach($dataset->datasets as $dats){
			echo "<br>$dats->dats_title";
		}
	}

	if ( ! ($withPlace && $withVar) ){
		echo "<h2>Temporal coverage</h2>";
        echo "$dataset->date_min - $dataset->date_max";
	}
	
	if (!$withVar){
		echo '<br/><h2>Parameters ('.count($dataset->vars).')</h2>';
		foreach($dataset->vars as $var){
			echo "<br><a href='$url&var_id=$var->var_id'>".printvarName($var).' ('.printGcmdScience($var->gcmd).')</a>';
	        }
	}
	if (!$withPlace){
		echo '<br/><h2>Sites ('.count($dataset->places).')</h2>';
        	foreach($dataset->places as $place){
			echo "<br><a href='$url&place_id=$place->place_id'>$place->place_name</a>";
		}
	}
	if ($withPlace && $withVar){
		$dateMin = new DateTime($dataset->date_min);
		$dateMax = new DateTime($dataset->date_max);
		if (!$withYear){
                $year = $dateMin->format('Y');
				echo "<h2>Temporal coverage</h2>";
	            echo "<a href='$url&year=$year'>$dataset->date_min - $dataset->date_max</a>";
	            
	            echo "<br /><h2>Graph</h2>";
	            echo "<img src='$urlWSGraph/$ins_dats_id/$var_id/$place_id' />";
	            
		}else{
			
			$yearMin = $dateMin->format('Y');
			$yearMax = $dateMax->format('Y');
			
			afficheListeAnnees($year,$yearMin,$yearMax,"$url_courte&ins_dats_id=$ins_dats_id&place_id=$place_id&var_id=$var_id");
			afficheCalendriers($ins_dats_id,$var_id,$place_id,$year);
			echo "<img src='$urlWSGraph/$ins_dats_id/$var_id/$place_id/$year' />";
		}
	}

}else{
	$projects = get_filtre_projets($project_name);
	$datasets = $insD->getByProjects($projects);

	echo '<h1>Inserted datasets</h1><br/><br/>';

echo '<table><tr><th>Name</th><th>Insertion date</th><th>Last update</th><th>Temporal coverage</th><th>Variables</th><th>Places</th></tr>';

foreach($datasets as $dataset){
	echo "<tr><td><a href='$url&ins_dats_id=$dataset->ins_dats_id'>$dataset->ins_dats_name</a></td>"
		.'<td>'.substr($dataset->date_insertion,0,10).'</td><td>'.substr($dataset->date_last_update,0,10).'</td><td>';
	echo "$dataset->date_min - $dataset->date_max</td><td align='right'>".count($dataset->vars).'</td><td align="right">'.count($dataset->places).'</td></tr>';
}
echo '</table>';
}
?>
