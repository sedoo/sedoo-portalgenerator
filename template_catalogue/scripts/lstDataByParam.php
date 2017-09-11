<?php
require_once ('bd/bdConnect.php');
require_once ('bd/gcmd_science_keyword.php');
require_once ('bd/variable.php');
require_once ('bd/dataset.php');
require_once ('scripts/TreeMenu.php');
require_once ('filtreProjets.php');
require_once ('lstDataUtils.php');
class varIds {
	var $var_name;
	var $ids;
}

$projects = get_filtre_projets ( $project_name );

$gcmd = new gcmd_science_keyword ();
echo "<h1>Parameter list</h1>";

include 'legende.php';

echo "<ul>";
if (isset ( $projects ) && ! empty ( $projects )) {
	$query = "select * from gcmd_science_keyword where gcmd_level = 2 and gcmd_id in (select distinct gcm_gcmd_id from gcmd_science_keyword where gcmd_id in (select distinct gcmd_id from variable where var_id in (select distinct var_id from dats_var where dats_id in (select distinct dats_id from dats_proj where project_id in ($projects)) and dats_id in (select distinct dats_id from dataset where is_requested is null AND (is_archived is null OR NOT is_archived))))) order by gcmd_name";
	$gcmd_list = $gcmd->getByQuery ( $query );
	$tree = new HTML_TreeMenu ();
	foreach ( $gcmd_list as $gc ) {
		addParam ( $tree, $gc, $projects, $project_name );
	}
	addOthers ( $tree, $project_name );
	$treeMenu = new HTML_TreeMenu_DHTML ( $tree, array (
			'images' => '/scripts/images',
			'defaultClass' => 'treeMenuDefault' 
	) );
	$treeMenu->printMenu ();
}
function varComp($var1, $var2) {
	return strcasecmp ( $var1->var_name, $var2->var_name );
}
function addParam(&$parent, $gcmd, $projects, $project_name) {
	$node = new HTML_TreeNode ( array (
			'text' => $gcmd->gcmd_name 
	) );
	// gcmd niveau suivant
	$query = "select * from variable where gcmd_id = " . $gcmd->gcmd_id . " or gcmd_id in (select distinct gcmd_id from gcmd_science_keyword where gcm_gcmd_id = " . $gcmd->gcmd_id . ")";
	$var = new variable ();
	$var_list = $var->getByQuery ( $query );
	$list_propre = array ();
	foreach ( $var_list as $va ) {
		if (! isset ( $va->var_name ) || empty ( $va->var_name )) {
			$gcmd = $gcmd->getById ( $va->gcmd_id );
			$va->var_name = $gcmd->gcmd_name;
		}
		$found = false;
		// virer les doublons pour avoir une liste propre. Il y a des doublons, c'est fort malheureux !
		foreach ( $list_propre as $elem ) {
			if (strcmp ( $elem->var_name, $va->var_name ) == 0) {
				$elem->ids [] = $va->var_id;
				$found = true;
				break;
			}
		}
		if (! $found) {
			$proper = new varIds ();
			$proper->ids = array ();
			$proper->var_name = $va->var_name;
			$proper->ids [] = $va->var_id;
			$list_propre [] = $proper;
		}
	}
	
	usort ( $list_propre, "varComp" );
	foreach ( $list_propre as $va ) {
		$where = "(";
		$count = 0;
		foreach ( $va->ids as $id ) {
			$where .= $id;
			if (count ( $va->ids ) - 1 != $count)
				$where .= ",";
			$count ++;
		}
		$where .= ")";
		if (isset ( $projects ) && ! empty ( $projects )) {
			$query_dts = "select dats_id, dats_title from dataset where dats_id in (select distinct dats_id from dats_var where var_id in " . $where . ") and dats_id in (select distinct dats_id from dats_proj where project_id in ($projects)) and is_requested is null AND (is_archived is null OR NOT is_archived) order by dats_title";
			$dts = new dataset ();
			$dts_list = $dts->getOnlyTitles ( $query_dts );
			if (isset ( $dts_list ) && ! empty ( $dts_list )) {
				addVar ( $node, $va, $dts_list, $project_name );
			}
		}
	}
	$parent->addItem ( $node );
}
function addVar(&$node, $va, $dts_list, $project_name) {
	$varnode = new HTML_TreeNode ( array (
			'text' => $va->var_name,
			'expanded' => 'true' 
	) );
	foreach ( $dts_list as $dt ) {
		addDataset ( $varnode, $dt, $project_name );
	}
	$node->addItem ( $varnode );
}
function addOthers(&$parent, $projectName) {
	$node = new HTML_TreeNode ( array (
			'text' => 'Other' 
	) );
	$projects = get_filtre_projets ( $projectName );
	if (isset ( $projects ) && ! empty ( $projects )) {
		$query = "SELECT dats_id,dats_title FROM dataset WHERE dats_id in (select distinct dats_id from dats_proj where project_id in ($projects)) AND dats_id not in (select distinct dats_id from dats_var) AND is_requested is null AND (is_archived is null OR NOT is_archived) order by dats_title";
		$dts = new dataset ();
		$dts_list = $dts->getOnlyTitles ( $query );
		foreach ( $dts_list as $dt ) {
			addDataset ( $node, $dt, $projectName );
		}
		if (count ( $dts_list ) > 0)
			$parent->addItem ( $node );
	}
}

?>
