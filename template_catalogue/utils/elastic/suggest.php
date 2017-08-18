<?php
require_once ("utils/elastic/ElasticClient.php");

$client = new ElasticClient ();

if (array_key_exists ( 'q', $_REQUEST )) {
	$q = $_REQUEST ['q'];
	
	if (! empty ( $q )) {
		$result = $client->searchKeyword ( $q );
		// print_r($result['hits']['hits'][0] );
		$liste = array ();
		foreach ( $result ['hits'] ['hits'] as $hit ) {
			$liste [] = $hit ['_source'] ['name'];
		}
		
		$json = array (
				$q 
		);
		$json [] = array_unique ( $liste );
		
		echo json_encode ( $json );
	}
}
?>
