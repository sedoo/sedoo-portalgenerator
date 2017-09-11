<?php

require_once ('bd/bdConnect.php');
require_once ('bd/country.php');
require_once ('filtreProjets.php');
require_once ('lstDataUtils.php');
require_once ('TreeMenu.php');

echo "<h1>Datasets ordered by country</h1>";
include 'legende.php';
	
$tree = new HTML_TreeMenu();

$c = new country;
$query = "select * from country where country_id in (select distinct country_id from country_place) order by country_name;";
$country_list = $c->getByQuery($query);
	
foreach($country_list as $country){
	addCountry($tree,$country,$project_name);
}
$treeMenu = new HTML_TreeMenu_DHTML($tree,array('images' => '/scripts/images','defaultClass' => 'treeMenuDefault'));
$treeMenu->printMenu();

function addCountry(&$parent,$country,$project_name){
	$node = new HTML_TreeNode(array('text' => $country->country_name));
        $dts = new dataset;
	$projects = get_filtre_projets($project_name);
        $query = "select dats_id, dats_title from dataset where dats_id in (select distinct dats_id from dats_proj where project_id in ($projects)) and dats_id in (select distinct dats_id from dats_place where place_id in (select distinct place_id from country_place where country_id = $country->country_id)) AND (is_archived is null OR NOT is_archived) order by dats_title;";
        $dts_list = $dts->getOnlyTitles($query);
        foreach ($dts_list as $dt) {
	        addDataset($node,$dt,$project_name);
        }
	if (!empty($dts_list))
	        $parent->addItem($node);
}

?>
