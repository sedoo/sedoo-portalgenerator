<?php

require_once ('scripts/TreeMenu.php');
require_once ('lstDataUtils.php');
require_once ('editDataset.php');


$datsId = $_REQUEST['datsId'];
if (isset($datsId) && !empty($datsId)){

	echo "<h1>Dataset Edition</h1>";
	
	echo "<br/><a style='font-size:110%;font-weight:bold;' href='/$project_name/Search-result'>&lt;&lt;&nbsp;Back to search result</a><br/>";
	
	editDataset($datsId,$project_name);
}else{

	$page = $_REQUEST['page'];
	if (! isset($page)) $page = 0;

	if (isset($_SESSION['result_search_form'])){
		$treeMenu = unserialize($_SESSION['result_search_form']);
	}

	if (isset($_SESSION['result_search_form_datasets'])){
			$datasets = unserialize($_SESSION['result_search_form_datasets']);
		}
		
	$withBd = isset($datasets) && count($datasets) > 0; 
		
	$url = "$project_url/Search-result";

	echo '<h1>Search result</h1>';

	if (isset ($treeMenu)){
		include 'legende.php';
		$treeMenu->printMenu(array('filterData' => 0));
	}else{
		echo '<br><br><font style="font-size:110%;font-weight:bold;">No dataset found</font>';

		echo "<br/><br/><a href='/$project_name/Search-tool'>&lt;&lt;&nbsp;New search</a><br/>";
		
	}
}
?>
