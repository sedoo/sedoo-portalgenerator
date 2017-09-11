<?php
require_once ('bd/gcmd_science_keyword.php');
require_once ('bd/variable.php');
require_once ('bd/dataset.php');
require_once ('scripts/TreeMenu.php');
require_once ('filtreProjets.php');
require_once ('lstDataUtils.php');
function nodeCompare($n1, $n2) {
	return strcasecmp ( $n1->text, $n2->text );
}

echo "<h1>Parameter list</h1>";
include 'legende.php';

$gcmd = new gcmd_science_keyword ();
$tree = new HTML_TreeMenu ();
$query = "select * from gcmd_science_keyword where gcmd_level = 2 order by gcmd_name";
$liste_topic = $gcmd->getByQuery ( $query );
foreach ( $liste_topic as $topic ) {
	addGcmd ( $tree, $topic, $project_name );
}
addOthers ( $tree, $project_name );
$treeMenu = new HTML_TreeMenu_DHTML ( $tree, array (
		'images' => '/scripts/images',
		'defaultClass' => 'treeMenuDefault' 
) );
$treeMenu->printMenu ();
function addGcmd(&$parent, $gcmd, $project_name) {
	$node = new HTML_TreeNode ( array (
			'text' => $gcmd->gcmd_name 
	) );
	
	$emptyNode = true;
	
	$query = "select * from gcmd_science_keyword where gcm_gcmd_id = $gcmd->gcmd_id order by gcmd_name";
	$g = new gcmd_science_keyword ();
	$liste_gcmd = $g->getByQuery ( $query );
	foreach ( $liste_gcmd as $gc ) {
		if (addGcmd ( $node, $gc, $project_name )) {
			$emptyNode = false;
		}
	}
	
	$var = new variable ();
	$query = "select * from variable where gcmd_id = $gcmd->gcmd_id and var_name is not null and var_name != '' order by var_name";
	$liste_var = $var->getByQuery ( $query );
	foreach ( $liste_var as $v ) {
		if (addVar ( $node, $v, $project_name )) {
			$emptyNode = false;
		}
	}
	
	if (! $emptyNode)
		usort ( $node->items, "nodeCompare" );
	$proj_ids = get_filtre_projets ( $project_name );
	if (isset ( $proj_ids ) && ! empty ( $proj_ids )) {
		$query = "select dats_id, dats_title from dataset where dats_id in (select distinct dats_id from dats_var where var_id in (select var_id from variable where gcmd_id = $gcmd->gcmd_id and (var_name is null or var_name = ''))) and dats_id in (select distinct dats_id from dats_proj where project_id in (" . $proj_ids . ")) AND (is_archived is null OR NOT is_archived) order by dats_title";
		$d = new dataset ();
		$liste_dts = $d->getOnlyTitles ( $query );
		foreach ( $liste_dts as $dts ) {
			addDataset ( $node, $dts, $project_name );
			$emptyNode = false;
		}
		if (! $emptyNode) {
			$parent->addItem ( $node );
		}
		return ! $emptyNode;
	}
}
function addVar(&$parent, $var, $project_name) {
	$varnode = new HTML_TreeNode ( array (
			'text' => ucfirst ( $var->var_name ),
			'expanded' => 'true' 
	) );
	$emptyNode = true;
	$proj_ids = get_filtre_projets ( $project_name );
	if (isset ( $proj_ids ) && ! empty ( $proj_ids )) {
		$query = "select dats_id, dats_title from dataset where dats_id in (select distinct dats_id from dats_var where var_id = $var->var_id) and dats_id in (select distinct dats_id from dats_proj where project_id in (" . $proj_ids . ")) AND (is_archived is null OR NOT is_archived) order by dats_title";
		$d = new dataset ();
		$liste_dts = $d->getOnlyTitles ( $query );
		foreach ( $liste_dts as $dts ) {
			addDataset ( $varnode, $dts, $project_name );
			$emptyNode = false;
		}
		if (! $emptyNode)
			$parent->addItem ( $varnode );
		return ! $emptyNode;
	}
}
function addOthers(&$parent, $projectName) {
	$node = new HTML_TreeNode ( array (
			'text' => 'Other' 
	) );
	$proj_ids = get_filtre_projets ( $projectName );
	if (isset ( $proj_ids ) && ! empty ( $proj_ids )) {
		$query = "SELECT dats_id,dats_title FROM dataset WHERE dats_id in (select distinct dats_id from dats_proj where project_id in ($proj_ids)) AND dats_id not in (select distinct dats_id from dats_var) AND is_requested is null AND (is_archived is null OR NOT is_archived) order by dats_title";
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
