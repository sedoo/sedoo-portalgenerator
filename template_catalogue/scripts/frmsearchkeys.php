<?php
/*
 * Created on 27 janv. 2011 To change the template for this generated file go to Window - Preferences - PHPeclipse - PHP - Code Templates
 */
require_once ("forms/search_keys_form.php");
require_once ("bd/dataset.php");
require_once ("bd/gcmd_instrument_keyword.php");
require_once ("editDataset.php");
require_once ("scripts/TreeMenu.php");
require_once ('treeByPlat.php');
function getSensorListe($f) {
	$query_inst = "select * from gcmd_instrument_keyword where gcmd_sensor_id ";
	if (isset ( $f->gcmd_sensor ) && $f->gcmd_sensor > 0)
		$query_inst .= "= " . $f->gcmd_sensor;
	else
		$query_inst .= " in " . " (select distinct gcmd_sensor_id from sensor where sensor_id in " . "(select distinct sensor_id from dats_sensor)) order by gcmd_sensor_name asc";
	$gcmd = new gcmd_instrument_keyword ();
	$gcmd_liste = $gcmd->getByQuery ( $query_inst );
	return $gcmd_liste;
}
function addSensor(&$parent, $sensor, $query, $project_name) {
	$dts = new dataset ();
	$dts_list = $dts->getOnlyTitles ( $query );
	if (isset ( $dts_list ) && ! empty ( $dts_list )) {
		$node = new HTML_TreeNode ( array (
				'text' => $sensor->gcmd_sensor_name,
				'expanded' => 'true' 
		) );
		foreach ( $dts_list as $dt ) {
			addDataset ( $node, $dt, $project_name );
		}
		$parent->addItem ( $node );
	}
}
function makeWhereKeywords($f) {
	if (isset ( $f->keywords ) && ! empty ( $f->keywords )) {
		$len = count ( $f->keywords );
		$return = "";
		foreach ( $f->keywords as $index => $key ) {
			$key = str_replace ( '&', '&amp;', $key );
			$return .= "lower(dats_xml) like '%" . strtolower ( $key ) . "%'";
			if ($index != $len - 1)
				$return .= " " . $f->and_or . " ";
		}
		return $return;
	}
	return null;
}
function makeWhereProject($project_name) {
	$projects = get_filtre_projets ( $project_name );
	
	return "dats_id in (select distinct dats_id from dats_proj where project_id in ($projects)) ";
}
function makeWhereFilterData($f) {
	if ($f->filter_data_db) {
		return "dats_id in (select dats_id from dats_data)";
	} else if ($f->filter_data) {
		return "dats_id in (select dats_id from url where url_type != 'map')";
	} else {
		return null;
	}
}
function listDatasetsByPlatform($f, $project_name) {
	$where_project = makeWhereProject ( $project_name );
	$where_keywords = makeWhereKeywords ( $f );
	
	$where_data = makeWhereFilterData ( $f );
	if (isset ( $where_data ) && ! empty ( $where_data )) {
		$where_data = "and ($where_data)";
	}
	$arbre = new treeByPlat ( false, - 1, null, 1 );
	$arbre->setFilter ( "$where_project and ($where_keywords) $where_data" );
	$arbre->project_name = $project_name;
	$arbre->projects = get_filtre_projets ( $project_name );
	$arbre->build ();
	if ($arbre->isEmpty ()) {
		return null;
	} else {
		return $arbre->treeMenu;
	}
}
function listDatasets($f, $project_name) {
	echo '<h1>Search Result</h1>';
	include 'legende.php';
	$order_by = "order by dats_title asc";
	$where_project = makeWhereProject ( $project_name );
	$where_keywords = makeWhereKeywords ( $f );
	$gcmd_liste = getSensorListe ( $f );
	$tree = new HTML_TreeMenu ();
	foreach ( $gcmd_liste as $sensor ) {
		$where_sensor_gcmd = "dats_id in (select distinct dats_id from dats_sensor where sensor_id in (select distinct sensor_id from sensor where gcmd_sensor_id = " . $sensor->gcmd_sensor_id . "))";
		$query_dat = "select dats_id, dats_title from dataset where " . $where_sensor_gcmd;
		if (isset ( $where_boundings ))
			$query_dat .= " and " . $where_boundings;
		if (isset ( $where_period ))
			$query_dat .= " and " . $where_period;
		else {
			if (isset ( $where_date_begin ))
				$query_dat .= " and " . $where_date_begin;
			if (isset ( $where_date_end ))
				$query_dat .= " and " . $where_date_end;
		}
		if (isset ( $where_var ))
			$query_dat .= " and " . $where_var;
		if (isset ( $where_project ))
			$query_dat .= " and " . $where_project;
		if (isset ( $where_keywords ))
			$query_dat .= " and (" . $where_keywords . ")";
		$query_dat .= " " . $order_by;
		addSensor ( $tree, $sensor, $query_dat, $project_name );
	}
	
	// Tmp
	$query_null = "select dats_id, dats_title from dataset where dats_id in (select distinct dats_id from dats_sensor where sensor_id in (select distinct sensor_id from sensor where gcmd_sensor_id is null)) and $where_project and ($where_keywords)";
	$dts = new dataset ();
	$dts_list = $dts->getOnlyTitles ( $query_null );
	if (isset ( $dts_list ) && ! empty ( $dts_list )) {
		foreach ( $dts_list as $dt ) {
			addDataset ( $tree, $dt );
		}
	}
	// Tmp fin
	$treeMenu = new HTML_TreeMenu_DHTML ( $tree, array (
			'images' => '/scripts/images',
			'defaultClass' => 'treeMenuDefault' 
	) );
	$treeMenu->printMenu ();
}

// Tri par ordre alpha
function listDatasetsByName($f, $project_name) {
	$cptDts = 0;
	$order_by = "order by dats_title asc";
	$tree = new HTML_TreeMenu ();
	$where_project = makeWhereProject ( $project_name );
	$where_keywords = makeWhereKeywords ( $f );
	$query = "select dats_id, dats_title from dataset where $where_project";
	$where_data = makeWhereFilterData ( $f );
	if (isset ( $where_data ) && ! empty ( $where_data )) {
		$query .= " and ($where_data)";
	}
	if (isset ( $where_keywords ) && ! empty ( $where_keywords )) {
		$query .= " and (" . $where_keywords . ")";
	}
	$query .= " " . $order_by;
	$dts = new dataset ();
	$dts_list = $dts->getOnlyTitles ( $query );
	foreach ( $dts_list as $dt ) {
		addDataset ( $tree, $dt, $project_name, 1 );
		$cptDts ++;
	}
	if ($cptDts > 0) {
		$treeMenu = new HTML_TreeMenu_DHTML ( $tree, array (
				'images' => '/scripts/images',
				'defaultClass' => 'treeMenuDefault' 
		) );
		return $treeMenu;
	} else {
		return null;
	}
}
function buildResult($form, $project_name) {
	$treeMenu = listDatasetsByName ( $form, $project_name );
	if ($treeMenu) {
		$_SESSION ['result_search_form'] = serialize ( $treeMenu );
		$datasets = array ();
		get_av_datasets ( $treeMenu->menu, $datasets );
		$_SESSION ['result_search_form_datasets'] = serialize ( $datasets );
		return true;
	} else {
		return false;
	}
}

$datsId = $_REQUEST ['datsId'];
if (isset ( $datsId ) && ! empty ( $datsId )) {
	include 'frmresult.php';
} else {
	$form = new search_keys_form ();
	$form->createForm ();
	
	if (isset ( $_POST ['bouton_search'] )) {
		$form->saveForm ();
		if ($form->validate ()) {
			if (buildResult ( $form, $project_name )) {
				include 'frmresult.php';
			} else {
				echo "<h1>Data search</h1>";
				echo '<br><br><font size="3" color="red"><b>No dataset is matching your criteria.</b></font>';
				$form->displayForm ();
			}
		} else {
			echo 'form not valid';
			echo "<h1>Data search</h1>";
			$form->displayForm ();
		}
	} else {
		echo "<h1>Data search</h1>";
		$form->displayForm ();
	}
}
?>
