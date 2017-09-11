<?php

require_once ('bd/project.php');
require_once ('scripts/lstDataUtils.php');
require_once ('scripts/TreeMenu.php');
require_once ('/sites/kernel/#MainProject/conf.php');

$tree = new HTML_TreeMenu();

if (constant(strtolower($project_name).'_HasCampaignSearch') == 'true' || in_array($project_name,$MainProjects)){
	echo '<h1>Campaigns list</h1>';
}else{
	echo '<h1>Projects list</h1>';
}
include 'legende.php';

if ( $project_name &&  $project_name != MainProject){
	$query = "SELECT * FROM project WHERE pro_project_id IS NULL AND project_name = '$project_name' ORDER BY project_name";
}else{
	$query = "SELECT * FROM project WHERE pro_project_id IS NULL ORDER BY project_name";
}

$proj = new project;
$proj_list = $proj->getByQuery($query);
foreach ($proj_list as $p){
	addProject($tree, $p,$project_name);
}
$treeMenu = new HTML_TreeMenu_DHTML($tree,array('images' => '/scripts/images','defaultClass' => 'treeMenuDefault'));
$treeMenu->printMenu();
function addProject(&$parent,$project,$projectName){
	$node = new HTML_TreeNode(array('text' => $project->project_name));
	//Sous-projets
	$proj_list = $project->getSousProjets();
	foreach ($proj_list as $p){
		addProject($node, $p, $projectName);
	}
	//Jeux de données
	//MERMEX: on n'affiche que les in-situ
	if ($projectName == 'MERMeX'){
		$query = 'SELECT dats_id,dats_title FROM dataset JOIN dats_proj USING (dats_id) JOIN project USING (project_id) '
				."WHERE project_id = $project->project_id AND dats_id NOT IN (SELECT DISTINCT dats_id FROM dats_type) AND (is_archived is null OR NOT is_archived) ORDER BY dats_title";
	}else{
		$query = "SELECT dats_id,dats_title FROM dataset JOIN dats_proj USING (dats_id) JOIN project USING (project_id) "
			."WHERE project_id = $project->project_id AND (is_archived is null OR NOT is_archived) ORDER BY dats_title";
	}
	$dts = new dataset;
	$dts_list = $dts->getOnlyTitles($query);
	foreach ($dts_list as $dt){
		addDataset($node,$dt,$projectName);
	}
	if (!empty($dts_list)){
		$parent->addItem($node);
	}
}

?>
