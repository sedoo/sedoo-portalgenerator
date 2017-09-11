<?php
require_once ('bd/bdConnect.php');
require_once ('bd/gcmd_instrument_keyword.php');
require_once ('bd/dataset.php');
require_once ('TreeMenu.php');
require_once ('filtreProjets.php');
require_once ('lstDataUtils.php');

function addSensor(&$parent,$sensor,$query,$projectName)
{
	$node = new HTML_TreeNode(array('text' => $sensor->gcmd_sensor_name));
        $dts = new dataset;
        $dts_list = $dts->getOnlyTitles($query);
        foreach ($dts_list as $dt)
        {
                addDataset($node,$dt,$projectName);
        }
	$parent->addItem($node);
}


function addOthers(&$parent,$projectName){
	$node = new HTML_TreeNode(array('text' => 'Other'));
	$projects = get_filtre_projets($projectName);
	$query = "SELECT dats_id,dats_title FROM dataset WHERE dats_id in (select distinct dats_id from dats_proj where project_id in ($projects)) AND dats_id in (select distinct dats_id from dats_sensor where sensor_id in (select sensor_id from sensor where gcmd_sensor_id is null)) AND dats_id not in (select distinct dats_id from dats_type) AND is_requested is null AND (is_archived is null OR NOT is_archived) order by dats_title";
	$dts = new dataset;
	$dts_list = $dts->getOnlyTitles($query);
        foreach ($dts_list as $dt){
                addDataset($node,$dt,$projectName);
        }
	if (count($dts_list) > 0)
	        $parent->addItem($node);
}
if(isset($_REQUEST['sensorId']) && !empty($_REQUEST['sensorId']))
	$sensorId = $_REQUEST['sensorId'];
$gcmd = new gcmd_instrument_keyword;
echo "<h1>Instrument types list</h1>";

include 'legende.php';

$projects = get_filtre_projets($project_name);
$query = "select * from gcmd_instrument_keyword where gcmd_sensor_id in (select distinct gcmd_sensor_id from sensor where sensor_id in (select distinct sensor_id from dats_sensor where dats_id in (select distinct dats_id from dats_proj where project_id in ($projects)))) order by gcmd_sensor_name";
$sensor_list = $gcmd->getByQuery($query);
$tree = new HTML_TreeMenu();
foreach ($sensor_list as $sensor){
	$query_dats = "select dats_id, dats_title from dataset where dats_id in (select distinct dats_id from dats_proj where project_id in ($projects)) and dats_id in (select distinct dats_id from dats_sensor where sensor_id in (select distinct sensor_id from sensor where gcmd_sensor_id = ".$sensor->gcmd_sensor_id.")) and is_requested is null AND (is_archived is null OR NOT is_archived) order by dats_title";
	addSensor($tree,$sensor,$query_dats,$project_name);
}
addOthers($tree,$project_name);
$treeMenu = new HTML_TreeMenu_DHTML($tree,array('images' => '/scripts/images','defaultClass' => 'treeMenuDefault'));
$treeMenu->printMenu();
?>
