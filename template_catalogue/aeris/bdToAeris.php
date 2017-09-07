<?php

require_once ("aeris/AerisUtils.php");

$datsId = $_REQUEST ['datsId'];
$test = $_REQUEST ['test'];
$project = $_REQUEST ['project'];
$collection = $_REQUEST ['collection'];
$filter = $_REQUEST ['filter'];

if (isset ( $datsId )) {
	if (!isset($collection) || empty($collection)){
		$collection = DEFAULT_COLLECTION;
	}
	AerisUtils::datasetToAeris ($datsId, $test, $collection);
}else if ( isset ( $project ) ){
	AerisUtils::projectToAeris($project, $test, $filter, $collection);
}else{
		
}

?>
