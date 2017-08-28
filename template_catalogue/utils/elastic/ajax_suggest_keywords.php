<?php
require_once ("utils/elastic/ElasticClient.php");
$q = trim($_GET['term']);

$suggestions = array();

$mots = explode(" ", $q);

if (count($mots) == 1){
	$client = new ElasticClient();
	$result = $client->searchKeyword($q);
	foreach ($result['hits']['hits'] as $hit){
		//$suggestions[] = array("id" => $hit['_id'], "label" => $hit['_source']['name']);
		$suggestions[] = strtolower($hit['_source']['name']);
	}
}
$suggestions = array_unique($suggestions);
echo json_encode($suggestions);

?>