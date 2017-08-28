<?php
require_once ("utils/elastic/elastic_form.php");
require_once ("utils/elastic/ElasticClient.php");

if ($form->isRoot ()) {
	echo "<h1>Search index</h1><p/>";
	
	$form = new elastic_form ();
	$form->createForm ();
	
	if (isset ( $_POST ['bouton_index_datasets'] )) {
		$client = new ElasticClient ();
		$client->indexAllDatasets (true);
	}
	if (isset ( $_POST ['bouton_index_keywords'] )) {
		$client = new ElasticClient ();
		$client->indexAllKeywords (true);
	}
	if (isset ( $_POST ['bouton_reset_indexes'] )) {
		$client = new ElasticClient();
		try {
			$ret = $client->deleteIndex();
		} catch (Exception $e) {
			echo 'Exception dans deleteIndex: '.$e->getMessage()."<br/>";
		}
		print_r($ret);
		echo '<br/>';
		try {
			$ret = $client->createIndex();
		} catch (Exception $e) {
			echo 'Exception dans createIndex: '.$e->getMessage()."<br/>";
		}
		print_r($ret);
		echo '<br/>';
		$client->indexAllDatasets(true);
		$client->indexAllKeywords(true);
	}
	$form->displayElasticForm ();
}

?>