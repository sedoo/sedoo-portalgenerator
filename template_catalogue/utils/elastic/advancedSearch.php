<?php

require_once ("utils/elastic/search_form.php");
require_once ("bd/gcmd_science_keyword.php");
require_once ("bd/gcmd_instrument_keyword.php");
require_once('editDataset.php');
require_once ("utils/elastic/ElasticSearchUtils.php");

if(isset($_REQUEST['project_name']) && !empty($_REQUEST['project_name'])){
	$project_name = $_REQUEST['project_name'];
}

if(isset($_REQUEST['datsId']) && !empty($_REQUEST['datsId'])){
	//Jeu
	$datsId = $_REQUEST ['datsId'];
	echo "<h1>Dataset Edition</h1>";
		
	if (array_key_exists('terms',$_REQUEST)){
		ElasticSearchUtils::addBackToSearchResultLink();
	}
	
	parse_str ( $_SERVER ["QUERY_STRING"], $query_array );
	unset ( $query_array ['datsId'] );
	editDataset($datsId, $project_name, false, $query_array);
}else if ( array_key_exists('terms',$_REQUEST) ){
	//GET
	$q = $_REQUEST['terms'];
	if ( array_key_exists('bbox',$_REQUEST) ){
		if (!empty($_REQUEST['bbox'])){
			$bbox = $_REQUEST['bbox'];
		}
	}
	
	if ( array_key_exists('dtstart',$_REQUEST) ){
		if (!empty($_REQUEST['dtstart'])){
			$dtstart = $_REQUEST['dtstart'];
		}
	}
	
	if ( array_key_exists('dtend',$_REQUEST) ){
		if (!empty($_REQUEST['dtend'])){
			$dtend = $_REQUEST['dtend'];
		}
	}
	if ( array_key_exists('instrument',$_REQUEST) ){
		if (!empty($_REQUEST['instrument'])){
			$instrument = $_REQUEST['instrument'];
		}
	}
	if ( array_key_exists('platform',$_REQUEST) ){
		if (!empty($_REQUEST['platform'])){
			$platform = $_REQUEST['platform'];
		}
	}
	if ( array_key_exists('parameter',$_REQUEST) ){
		if (!empty($_REQUEST['parameter'])){
			$parameter = $_REQUEST['parameter'];
		}
	}
	if ( array_key_exists('availability',$_REQUEST) ){
		if (!empty($_REQUEST['availability'])){
			$availability = $_REQUEST['availability'];
		}
	}
	if ( array_key_exists('project',$_REQUEST) ){
		if (!empty($_REQUEST['project'])){
			$project = $_REQUEST['project'];
		}
	}
	$recherche = array(
			"keywords" => $q
			);

	if ( array_key_exists('allKeywords',$_REQUEST) ){
		$recherche['keywords_all'] = true;
	}
	
	if ($bbox) {
		$coords = explode(',', $bbox);
		$recherche['zone'] = array(
				'west' => $coords[0],
				'south' => $coords[1],
				'east' => $coords[2],
				'north' => $coords[3]
		);
	}
	
	if ($dtstart) {
		$recherche['period']['min'] = $dtstart;
	}
	if ($dtend) {
		$recherche['period']['max'] = $dtend;
	}
	if ($instrument) {
		$recherche['instrument'] = $instrument;
	}
	if ($platform) {
		$recherche['platform'] = $platform;
	}
	if ($parameter) {
		$recherche['parameter'] = $parameter;
	}
	if ($availability) {
		$recherche['availability'] = $availability;
	}
	if ($project) {
		$recherche['project'] = $project;
	}
			
	echo '<h1>Search result</h1>';
	ElasticSearchUtils::lstQueryData($recherche, $project);
	
}else if ( isset($_POST['keywords_search_menu']) ){
	//Recherche simple
	$recherche = array();
	if (empty($_POST['keywords_search_menu'])){
		$recherche['keywords'] = "*";
	}else{
		$recherche['keywords'] = $_POST['keywords_search_menu'];
	}
	$recherche['keywords_all'] = true;
	
	
	if ( isset($_POST['search_project']) ){
		$projectName = $_POST['search_project'];
		if (isset ( $projectName ) && !empty($projectName) && ($projectName != 'BAOBAB')) {
			$recherche['project'] = $projectName;
		}
	}
	echo '<h1>Search result</h1>';
	ElasticSearchUtils::lstQueryData($recherche, $projectName);
	
}else{
	//Formulaire
	$form = new search_form ();
	$form->createForm ($project_name);
	if (isset ( $_POST ['bouton_search'] )) {
		$form->saveForm ();
		$form->addValidationRules ();
		if ($form->validate ()) {
			$recherche = $form->toSearchRequest();
							
			echo '<h1>Search result</h1>';
			ElasticSearchUtils::lstQueryData($recherche, $project_name);
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