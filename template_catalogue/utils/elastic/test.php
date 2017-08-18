<?php
require_once ("utils/elastic/ElasticClient.php");

$client = new ElasticClient();

try {
	$ret = $client->deleteIndex();
} catch (Exception $e) {
	echo 'Exception dans deleteIndex: '.$e->getMessage()."<br/>";
}

try {
	$ret = $client->createIndex();
} catch (Exception $e) {
	echo 'Exception dans createIndex: '.$e->getMessage()."<br/>";
}
print_r($ret);
echo '<br/>';

$client->indexAllDatasets();
$client->indexAllKeywords();

return;

?>