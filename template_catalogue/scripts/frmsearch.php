<?php
/*
 * Created on 27 janv. 2011 To change the template for this generated file go to Window - Preferences - PHPeclipse - PHP - Code Templates
 */
require_once ("forms/search_form.php");
require_once ("bd/dataset.php");
require_once ("bd/url.php");
require_once ("bd/gcmd_instrument_keyword.php");
require_once ('scripts/TreeMenu.php');

require_once ('scripts/treeByPlat.php');
require_once ('filtreProjets.php');
require_once ('lstDataUtils.php');
function makeWhereKeywords($f) {
	if (isset ( $f->keywords ) && ! empty ( $f->keywords )) {
		$len = count ( $f->keywords );
		$return = "";
		foreach ( $f->keywords as $index => $key ) {
			$key = str_replace ( '&', '&amp;', $key );
			$return .= "lower(dats_xml) ilike '%" . strtolower ( $key ) . "%'";
			if ($index != $len - 1)
				$return .= " " . $f->and_or . " ";
		}
		return $return;
	}
	return null;
}
function makeWhereDates($f) {
	$dateBeginOk = isset ( $f->date_begin ) && ! empty ( $f->date_begin );
	$dateEndOk = isset ( $f->date_end ) && ! empty ( $f->date_end );
	if ($dateBeginOk && ! $dateEndOk) {
		return "( dats_date_end >= '$f->date_begin' OR (dats_date_end IS NULL AND dats_date_begin IS NOT NULL) )";
	} else if (! $dateBeginOk && $dateEndOk) {
		return "( dats_date_begin <= '$f->date_end' OR (dats_date_begin IS NULL AND dats_date_end IS NOT NULL) )";
	} else if ($dateBeginOk && $dateEndOk) {
		return "( (dats_date_begin IS NULL AND dats_date_end >= '$f->date_begin') OR (dats_date_end IS NULL AND dats_date_begin <= '$f->date_end') OR (dats_date_end >= '$f->date_begin' AND dats_date_begin <= '$f->date_end') )";
	} else {
		return null;
	}
}
function makeWhereDateBegin($f) {
	if (isset ( $f->date_begin ) && ! empty ( $f->date_begin ))
		return "(dats_date_end is null or dats_date_end >='" . $f->date_begin . "')";
	return null;
}
function makeWhereDateEnd($f) {
	if (isset ( $f->date_end ) && ! empty ( $f->date_end ))
		return "(dats_date_begin is null or dats_date_begin <='" . $f->date_end . "')";
	return null;
}
function makeWherePeriod($f) {
	if (isset ( $f->period ) && $f->period != 0)
		return "period_id = " . $f->period;
	return null;
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
function makeWhereVar($f) {
	if (isset ( $f->gcmd_variable ) && $f->gcmd_variable > 0)
		return "dats_id in (select distinct dats_id from dats_var where var_id in " . "(select distinct var_id from variable where gcmd_id = " . $f->gcmd_variable . ") or " . "var_id in (select distinct var_id from variable where gcmd_id in (" . "select distinct gcmd_id from gcmd_science_keyword where gcm_gcmd_id = " . $f->gcmd_variable . ") " . "or gcmd_id in (select distinct gcmd_id from gcmd_science_keyword where gcm_gcmd_id in (" . "select distinct gcmd_id from gcmd_science_keyword where gcm_gcmd_id = " . $f->gcmd_variable . "))))";
	return null;
}
function makeWhereBoundings($f) {
	$where_lat_min = null;
	$where_lat_max = null;
	$where_lon_min = null;
	$where_lon_max = null;
	$where_boundings = null;
	$minLatDefault = 250000;
	$maxLatDefault = 500000;
	$minLonDefault = - 100000;
	$maxLonDefault = 400000;
	
	if (($f->latMin == $minLatDefault) && ($f->latMax == $maxLatDefault) && ($f->lonMin == $minLonDefault) && ($f->lonMax == $maxLonDefault)) {
		// Zone par défaut
		return null;
	} else {
		// bound_id de la table dataset
		$where_boundings = "bound_id IN (SELECT bound_id FROM boundings WHERE north_bounding_coord >= $f->latMin AND south_bounding_coord <= $f->latMax AND east_bounding_coord >= $f->lonMin AND west_bounding_coord <= $f->lonMax)";
		
		// sensor
		$where_sensors = "sensor_id IN (SELECT sensor_id FROM sensor WHERE $where_boundings)";
		$where_places = "place_id IN (SELECT place_id FROM place WHERE $where_boundings)";
		
		return "($where_boundings OR dats_id IN (SELECT DISTINCT dats_id FROM dats_sensor WHERE $where_sensors) OR dats_id IN (SELECT DISTINCT dats_id FROM dats_place WHERE $where_places))";
	}
	return null;
}
function makeWhereBoundingsOld($f) {
	$where_lat_min = null;
	$where_lat_max = null;
	$where_lon_min = null;
	$where_lon_max = null;
	$where_boundings = null;
	$minLat = 250000;
	$maxLat = 500000;
	$minLon = - 100000;
	$maxLon = 400000;
	
	if (isset ( $f->latMin ) && $f->latMin > $minLat)
		$where_lat_min = "(north_bounding_coord is null or north_bounding_coord >= " . $f->latMin . ")";
	if (isset ( $f->latMax ) && $f->latMax < $maxLat)
		$where_lat_max = "(south_bounding_coord is null or south_bounding_coord <= " . $f->latMax . ")";
	if (isset ( $f->lonMin ) && $f->lonMin > $minLon)
		$where_lon_min = "(east_bounding_coord is null or east_bounding_coord >= " . $f->lonMin . ")";
	if (isset ( $f->lonMax ) && $f->lonMax < $maxLon)
		$where_lon_max = "(west_bounding_coord is null or west_bounding_coord <= " . $f->lonMax . ")";
	if (isset ( $where_lat_min ) || isset ( $where_lat_max ) || isset ( $where_lon_min ) || isset ( $where_lon_max )) {
		$where_boundings = "bound_id in (select distinct bound_id from boundings where ";
		$where_boundings_sensor = "dats_id in (select distinct dats_id from dats_sensor where sensor_id in " . "(select distinct sensor_id from sensor where bound_id is null ";
		$where_boundings_sensor .= "or bound_id in (select distinct bound_id from boundings where ";
		if (isset ( $where_lat_min )) {
			$where_boundings .= $where_lat_min;
			$where_boundings_sensor .= $where_lat_min;
			if (isset ( $where_lat_max )) {
				$where_boundings_sensor .= " and " . $where_lat_max;
				$where_boundings .= " and " . $where_lat_max;
			}
			if (isset ( $where_lon_min )) {
				$where_boundings_sensor .= " and " . $where_lon_min;
				$where_boundings .= " and " . $where_lon_min;
			}
			if (isset ( $where_lon_max )) {
				$where_boundings_sensor .= " and " . $where_lon_max;
				$where_boundings .= " and " . $where_lon_max;
			}
		} else if (isset ( $where_lat_max )) {
			$where_boundings .= $where_lat_max;
			$where_boundings_sensor .= $where_lat_max;
			if (isset ( $where_lon_min )) {
				$where_boundings_sensor .= " and " . $where_lon_min;
				$where_boundings .= " and " . $where_lon_min;
			}
			if (isset ( $where_lon_max )) {
				$where_boundings_sensor .= " and " . $where_lon_max;
				$where_boundings .= " and " . $where_lon_max;
			}
		} else if (isset ( $where_lon_min )) {
			$where_boundings .= $where_lon_min;
			$where_boundings_sensor .= $where_lon_min;
			if (isset ( $where_lon_max )) {
				$where_boundings_sensor .= " and " . $where_lon_max;
				$where_boundings .= " and " . $where_lon_max;
			}
		} else if (isset ( $where_lon_max )) {
			$where_boundings .= $where_lon_max;
			$where_boundings_sensor .= $where_lon_max;
		}
		$where_boundings .= ") or (bound_id is null ";
		$where_boundings_sensor .= ")))";
		return "(" . $where_boundings . " and " . $where_boundings_sensor . "))";
	}
	return null;
}
function makeWhereProject($project_name) {
	$projects = get_filtre_projets ( $project_name );
	
	return "dats_id in (select distinct dats_id from dats_proj where project_id in ($projects)) ";
}
function makeWhereSensorGcmd($f) {
	$where_sensor_gcmd = null;
	if (isset ( $f->gcmd_sensor ) && $f->gcmd_sensor > 0)
		$where_sensor_gcmd = "dats_id in (select distinct dats_id from dats_sensor where sensor_id in (select distinct sensor_id from sensor where gcmd_sensor_id = " . $f->gcmd_sensor . "))";
	return $where_sensor_gcmd;
}
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
	$compteur = 0;
	$dts = new dataset ();
	$dts_list = $dts->getOnlyTitles ( $query );
	if (isset ( $dts_list ) && ! empty ( $dts_list )) {
		$node = new HTML_TreeNode ( array (
				'text' => $sensor->gcmd_sensor_name,
				'expanded' => 'true' 
		) );
		foreach ( $dts_list as $dt ) {
			addDataset ( $node, $dt, $project_name, 1 );
			$compteur ++;
		}
		$parent->addItem ( $node );
	}
	return $compteur;
}
function listDatasetsByPlatform($f, $project_name) {
	$where_date_begin = makeWhereDateBegin ( $f );
	$where_date_end = makeWhereDateEnd ( $f );
	$where_boundings = makeWhereBoundings ( $f );
	$where_var = makeWhereVar ( $f );
	$where_period = makeWherePeriod ( $f );
	$where_project = makeWhereProject ( $project_name );
	$where_keywords = makeWhereKeywords ( $f );
	
	$where_data = makeWhereFilterData ( $f );
	
	if (isset ( $f->gcmd_sensor ) && $f->gcmd_sensor > 0) {
		$where_sensor_gcmd = "dats_id in (select distinct dats_id from dats_sensor where sensor_id in (select distinct sensor_id from sensor where gcmd_sensor_id = $f->gcmd_sensor))";
	}
	$query_dat = $where_project;
	
	if (isset ( $where_boundings ) && ! empty ( $where_boundings )) {
		$query_dat .= " and " . $where_boundings;
	}
	if (isset ( $where_period ) && ! empty ( $where_period )) {
		$query_dat .= " and " . $where_period;
	} else {
		if (isset ( $where_date_begin ) && ! empty ( $where_date_begin )) {
			$query_dat .= " and " . $where_date_begin;
		}
		if (isset ( $where_date_end ) && ! empty ( $where_date_end )) {
			$query_dat .= " and " . $where_date_end;
		}
	}
	if (isset ( $where_var ) && ! empty ( $where_var )) {
		$query_dat .= " and " . $where_var;
	}
	if (isset ( $where_keywords ) && ! empty ( $where_keywords )) {
		$query_dat .= " and (" . $where_keywords . ")";
	}
	
	if (isset ( $where_sensor_gcmd ) && ! empty ( $where_sensor_gcmd )) {
		$query_dat .= " AND $where_sensor_gcmd";
	}
	
	if (isset ( $where_data ) && ! empty ( $where_data )) {
		$query_dat .= " AND $where_data";
	}
	
	$arbre = new treeByPlat ( false, - 1, $query_dat, 1 );
	$arbre->project_name = $project_name;
	$arbre->projects = get_filtre_projets ( $project_name );
	$arbre->build ( true );
	if ($arbre->isEmpty ()) {
		return null;
	} else {
		return $arbre->treeMenu;
	}
}
function listDatasetsBySensor($f, $project_name) {
	$cptDts = 0;
	$order_by = "order by dats_title asc";
	$where_insitu = "dats_id not in (select distinct dats_id from dats_type)";
	$where_satmod = "dats_id in (select distinct dats_id from dats_type)";
	$where_date_begin = makeWhereDateBegin ( $f );
	$where_date_end = makeWhereDateEnd ( $f );
	$where_boundings = makeWhereBoundings ( $f );
	$where_var = makeWhereVar ( $f );
	$where_period = makeWherePeriod ( $f );
	$where_project = makeWhereProject ( $project_name );
	$where_keywords = makeWhereKeywords ( $f );
	$where_data = makeWhereFilterData ( $f );
	$gcmd_liste = getSensorListe ( $f );
	$tree = new HTML_TreeMenu ();
	foreach ( $gcmd_liste as $sensor ) {
		$where_sensor_gcmd = "dats_id in (select distinct dats_id from dats_sensor where sensor_id in (select distinct sensor_id from sensor where gcmd_sensor_id = " . $sensor->gcmd_sensor_id . "))";
		$query_dat = "select dats_id, dats_title from dataset where " . $where_sensor_gcmd;
		if (isset ( $where_boundings ) && ! empty ( $where_boundings )) {
			$query_dat .= " and " . $where_boundings;
		}
		if (isset ( $where_period ) && ! empty ( $where_period )) {
			$query_dat .= " and " . $where_period;
		} else {
			if (isset ( $where_date_begin ) && ! empty ( $where_date_begin )) {
				$query_dat .= " and " . $where_date_begin;
			}
			if (isset ( $where_date_end ) && ! empty ( $where_date_end )) {
				$query_dat .= " and " . $where_date_end;
			}
		}
		if (isset ( $where_var ) && ! empty ( $where_var )) {
			$query_dat .= " and " . $where_var;
		}
		if (isset ( $where_project ) && ! empty ( $where_project )) {
			$query_dat .= " and " . $where_project;
		}
		if (isset ( $where_keywords ) && ! empty ( $where_keywords )) {
			$query_dat .= " and (" . $where_keywords . ")";
		}
		if (isset ( $where_data ) && ! empty ( $where_data )) {
			$query_dat .= " AND $where_data";
		}
		$query_dat .= " " . $order_by;
		$cptDts += addSensor ( $tree, $sensor, $query_dat, $project_name );
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
function listDatasetsByName($f, $project_name) {
	$cptDts = 0;
	$order_by = "order by dats_title asc";
	$where_date_begin = makeWhereDates ( $f );
	$where_date_end = null;
	$where_boundings = makeWhereBoundings ( $f );
	$where_var = makeWhereVar ( $f );
	$where_period = makeWherePeriod ( $f );
	$where_project = makeWhereProject ( $project_name );
	$where_keywords = makeWhereKeywords ( $f );
	$where_data = makeWhereFilterData ( $f );
	
	if (isset ( $f->gcmd_sensor ) && $f->gcmd_sensor > 0) {
		$where_sensor_gcmd = "dats_id in (select distinct dats_id from dats_sensor where sensor_id in (select distinct sensor_id from sensor where gcmd_sensor_id = $f->gcmd_sensor))";
	}
	$tree = new HTML_TreeMenu ();
	$query = "select dats_id, dats_title from dataset where $where_project";
	if (isset ( $where_boundings ) && ! empty ( $where_boundings )) {
		$query .= " and " . $where_boundings;
	}
	if (isset ( $where_period ) && ! empty ( $where_period )) {
		$query .= " and " . $where_period;
	} else {
		if (isset ( $where_date_begin ) && ! empty ( $where_date_begin )) {
			$query .= " and " . $where_date_begin;
		}
		if (isset ( $where_date_end ) && ! empty ( $where_date_end )) {
			$query .= " and " . $where_date_end;
		}
	}
	if (isset ( $where_var ) && ! empty ( $where_var )) {
		$query .= " and " . $where_var;
	}
	if (isset ( $where_keywords ) && ! empty ( $where_keywords )) {
		$query .= " and (" . $where_keywords . ")";
	}
	if (isset ( $where_data ) && ! empty ( $where_data )) {
		$query .= " AND $where_data";
	}
	
	if (isset ( $where_sensor_gcmd ) && ! empty ( $where_sensor_gcmd )) {
		$query .= " AND $where_sensor_gcmd";
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
	if ($form->order_by == 1) { // liste par instrument
		$treeMenu = listDatasetsBySensor ( $form, $project_name );
	} else if ($form->order_by == 2) { // liste par plateforme
		$treeMenu = listDatasetsByPlatform ( $form, $project_name );
	} else {
		// ordre alpha
		$treeMenu = listDatasetsByName ( $form, $project_name );
	}
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

if(isset($_REQUEST['datsId']) && !empty($_REQUEST['datsId']))
	$datsId = $_REQUEST ['datsId'];

if (isset ( $datsId ) && ! empty ( $datsId )) {
	include 'frmresult.php';
} else {
	$form = new search_form ();
	$form->createForm ();
	if (isset ( $_POST ['bouton_search'] )) {
		$form->saveForm ();
		$form->addValidationRules ();
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
