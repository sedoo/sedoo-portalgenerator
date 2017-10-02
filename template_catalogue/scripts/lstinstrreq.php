<?php
require_once ("bd/bdConnect.php");
require_once ("bd/dataset.php");
require_once ('scripts/lstDataUtils.php');
require_once ('scripts/filtreProjets.php');

	//Liste des instruments
	$ds = new dataset;
	echo "<br><br><h2>Already Requested Data List</h2>";
	echo "<ul>";
	
	$typePrec = -1;
	$dataset_liste = array();
	if ($project_name == strtolower(MainProject)) //OverAll datasets
	{
		echo '<h2>IN SITU</h2>';
		 $query = "select dataset.dats_id, dataset.dats_title from dataset where " .
                                "dats_id not in (select distinct dats_id from dats_type)" .
                                "AND (is_archived is null OR NOT is_archived) order by dats_title asc";
		$dataset_liste = $ds->getOnlyTitles($query);
		foreach ($dataset_liste as $dataset){
			echo    '<li>'.printDataset($dataset).'</li>';
		}
		echo '<br><br>';
		// Mod et Sat
		$query = "select dataset.dats_id, dataset.dats_title from dataset left join dats_type using (dats_id) left join dataset_type using (dats_type_id) where " .
                                "dats_id in (select distinct dats_id from dats_type)" .
                                "AND (is_archived is null OR NOT is_archived) order by dats_type_title desc,dats_title asc";
        $dataset_liste = $ds->getOnlyTitles($query);
        foreach ($dataset_liste as $dataset){
		$dataset->get_dataset_types();
		
		if ( $typePrec != $dataset->dataset_types[0]->dats_type_id ){
			$typePrec = $dataset->dataset_types[0]->dats_type_id;
			if ($typePrec > 0){			
				echo '<h2>'.$dataset->dataset_types[0]->dats_type_title.'</h2><p/>';
			}
		}
		echo	'<li>'.printDataset($dataset).'</li>';
        }
	}else{
		$query = "select dataset.dats_id, dataset.dats_title from dataset left join dats_type using (dats_id) left join dataset_type using (dats_type_id) where is_requested = true and (dats_id in (select distinct dats_id from dats_proj where " .
				"project_id in (".get_filtre_projets($project_name).") or project_id in (select distinct project_id from project where pro_project_id in (".get_filtre_projets($project_name)."))) or dats_id not in (select distinct dats_id from dats_proj)) " .
				"AND (is_archived is null OR NOT is_archived) order by dats_type_title desc,dats_title asc";
		$dataset_liste = $ds->getOnlyTitles($query);
		foreach ($dataset_liste as $dataset){
		
			$dataset->get_dataset_types();
			if ( $typePrec != $dataset->dataset_types[0]->dats_type_id ){
				$typePrec = $dataset->dataset_types[0]->dats_type_id;
				if ($typePrec > 0){
					echo '<h2>'.$dataset->dataset_types[0]->dats_type_title.'</h2><p/>';
				}else{
					echo '<h2>In-Situ</h2><p/>';
				}
			}
			echo    '<li>'.printDataset($dataset).'</li>';
		}
	}
	echo "</ul>";
?>
