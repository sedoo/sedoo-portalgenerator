<?php

require_once('bd/param.php');
require_once('bd/inserted_dataset.php');
require_once('utils/calendrier_utils.php');
require_once('scripts/filtreProjets.php');

$p = new param;

$urlWSGraph = 'http://sedoo.sedoo.fr/mistrals_visu/service/graph/st';
$url = $_SERVER['REQUEST_URI'];
$url_courte = parse_url($url, PHP_URL_PATH).'?adm&pageId=10';

if(isset($_REQUEST['ins_dats_id']) && ! empty($_REQUEST['ins_dats_id']))
	$ins_dats_id = $_REQUEST['ins_dats_id'];
if(isset($_REQUEST['var_id']) && ! empty($_REQUEST['var_id']))
	$var_id = $_REQUEST['var_id'];
if(isset($_REQUEST['place_id']) && ! empty($_REQUEST['place_id']))
	$place_id = $_REQUEST['place_id'];
if(isset($_REQUEST['year']) && ! empty($_REQUEST['year']))
	$year = $_REQUEST['year'];

$withVar = isset($var_id) && !empty($var_id);
$withDats = isset($ins_dats_id) && !empty($ins_dats_id);
$withPlace = isset($place_id) && !empty($place_id);
$withYear = isset($year) && !empty($year);

if ($withVar){
	$param = $p->getById($var_id);
	
	$title = "<a href='$url_courte&var_id=$var_id'>".printvarName($param->var).'</a>';
		
	$insD = new inserted_dataset;
	
	if ($withDats){
		$dats = $insD->getById($ins_dats_id,$var_id);
		$title .= " / <a href='$url_courte&ins_dats_id=$ins_dats_id&var_id=$var_id'>".$dats->ins_dats_name.'</a>';
	} 

	if ($withPlace){
		$place = new place;
        $place = $place->getById($place_id);
        $title .= " / $place->place_name";
	}
	
	echo "<h1>$title</h1><br/><br/>";
	
	if (!$withDats){
		$datasets = $insD->getByVarId($var_id);
		echo '<br/><h2>Inserted datasets ('.count($datasets).')</h2>';
		foreach($datasets as $dataset){
			echo "<br><a href='$url&ins_dats_id=$dataset->ins_dats_id'>$dataset->ins_dats_name</a>";
		}
	}
	
	if ($withDats && !$withPlace){
		echo '<br/><h2>Sites ('.count($dats->places).')</h2>';
		foreach($dats->places as $place){
			echo "<br><a href='$url&place_id=$place->place_id'>$place->place_name</a>";
		}
	}
	
	if ($withDats && $withPlace){
		$dateMin = new DateTime($dats->date_min);
		$dateMax = new DateTime($dats->date_max);
		if (!$withYear){
            $year = $dateMin->format('Y');
			echo "<h2>Period</h2>";
		    echo "<a href='$url&year=$year'>$dats->date_min - $dats->date_max</a>";
	    	echo "<br /><h2>Graph</h2>";
	    	echo "<img src='$urlWSGraph/$ins_dats_id/$var_id/$place_id' />";
		}else{
			$yearMin = $dateMin->format('Y');
			$yearMax = $dateMax->format('Y');
			
			/*echo "<a href='$url_courte&ins_dats_id=$ins_dats_id&place_id=$place_id&var_id=$var_id'>Back</a><br/><br/>";
			
						
			echo '<center><b>';
			if ($yearMin < $year)
				echo "<a href='$url_courte&ins_dats_id=$ins_dats_id&place_id=$place_id&var_id=$var_id&year=".($year-1)."'>&lt;&lt;</a>";
			echo "&nbsp;&nbsp;$year&nbsp;&nbsp;";
			if ($yearMax > $year)
				echo "<a href='$url_courte&ins_dats_id=$ins_dats_id&place_id=$place_id&var_id=$var_id&year=".($year+1)."'>&gt;&gt;</a></b>";
			echo '</center><br/><br/>';*/
			afficheListeAnnees($year,$yearMin,$yearMax,"$url_courte&ins_dats_id=$ins_dats_id&place_id=$place_id&var_id=$var_id");
			afficheCalendriers($ins_dats_id,$var_id,$place_id,$year);
		}
	}
	
}else{
//	$params = $p->getAll();

	$projects = get_filtre_projets($project_name);
        $params = $p->getByProjects($projects);
	
	echo '<table><tr><th>Code</th><th>Name</th><th>GCMD keyword</th><th>Unit</th><th>CF standard name</th></tr>';
	foreach($params as $param){
		echo '<tr>';
		echo "<td>$param->param_code</td><td>";
		if ($param->hasData){
			echo "<a href='$url&var_id=$param->var_id'>".printvarName($param->var).'</a>';
		}else{
			echo printvarName($param->var);
		}
		echo '</td><td>'.printGcmdScience($param->var->gcmd).'</td>';
		echo '<td>'.printUnit($param->unit).'</td><td>'.$param->standard_name.'</td></tr>';
	}
	echo '</table>';
}

?>
