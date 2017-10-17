<?php

require 'elastic/vendor/autoload.php';

require_once ("conf/conf.php");
require_once ("utils/elastic/elastic_dataset_factory.php");
require_once ("bd/gcmd_science_keyword.php");
require_once ("bd/gcmd_location_keyword.php");

class ElasticClient {

	const ELASTIC_INDEX_NAME = ELASTIC_INDEX;
	const ELASTIC_DATASET_TYPE = 'dataset';
	const ELASTIC_KEYWORD_TYPE = 'keyword';
	const ELASTIC_DATASET_MAPPING = 'utils/elastic/datasetMapping.json';
	const ELASTIC_KEYWORD_MAPPING = 'utils/elastic/keywordMapping.json';

	private $client;

	function __construct($host = ELASTIC_HOST){
		$this->open($host);
	}

	private function open($host){
		$params = array();
		$params['hosts'] = array ($host);
		$this->client = Elasticsearch\ClientBuilder::fromConfig($params);
		
		//Création de l'index s'il n'existe pas
		$indexParams['index'] = self::ELASTIC_INDEX_NAME;
		if ( ! $this->client->indices()->exists($indexParams) ){
			$this->createIndex();
			$this->indexAllDatasets();
			$this->indexAllKeywords();
		}
	}

	/*
	 * Create index from datasetMapping.json
	*/
	public function createIndex(){
		$indexParams['index']  = self::ELASTIC_INDEX_NAME;

		//Pour chercher des bouts de mots
		$indexParams['body']['settings']['analysis']['filter']['nGram_filter'] = array(
				"type" => "nGram",
				"min_gram" => 2,
				"max_gram" => 20,
				"token_chars" => array (
						"letter",
						"digit",
						"punctuation",
						"symbol"
				)
		);
		$indexParams['body']['settings']['analysis']['analyzer']['nGram_analyzer'] = array(
				"type" => "custom",
				"tokenizer" => "whitespace",
				"filter" => array("lowercase", "asciifolding", "nGram_filter")
		);


		$indexParams['body']['settings']['analysis']['analyzer']['whitespace_analyzer'] = array(
				"type" => "custom",
				"tokenizer" => "whitespace",
				"filter" => array("lowercase", "asciifolding")
		);

		//Analyzer pour trier en ignorant la casse
		$indexParams['body']['settings']['analysis']['analyzer']['case_insensitive_sort'] = array(
				"tokenizer" => "keyword",
				"filter" => array("lowercase")
		);

		$datasetMapping = file_get_contents(self::ELASTIC_DATASET_MAPPING, true);
		$map = json_decode($datasetMapping, true);
		$indexParams['body']['mappings'][self::ELASTIC_DATASET_TYPE] = $map;

		$keywordMapping = file_get_contents(self::ELASTIC_KEYWORD_MAPPING, true);
		$map2 = json_decode($keywordMapping, true);
		$indexParams['body']['mappings'][self::ELASTIC_KEYWORD_TYPE] = $map2;

		return $this->client->indices()->create($indexParams);
	}

	public function deleteIndex(){
		$deleteParams['index'] = self::ELASTIC_INDEX_NAME;
		return $this->client->indices()->delete($deleteParams);
	}


	public function indexAllKeywords($verbose = false){
		$this->indexKeywords('science_keyword', 'gcmd_science_keyword', 'gcmd_id', 'gcmd_name', 'variable', $verbose);
		
		$this->indexKeywords('location', 'gcmd_location_keyword', 'gcmd_loc_id', 'gcmd_loc_name', 'dats_loc', $verbose);
		$this->indexKeywords('instrument', 'gcmd_instrument_keyword', 'gcmd_sensor_id', 'gcmd_sensor_name', 'sensor', $verbose);
		$this->indexKeywords('platform', 'gcmd_plateform_keyword', 'gcmd_plat_id', 'gcmd_plat_name', 'place', $verbose);
		
		$this->indexKeywords('place', 'place', 'place_id', 'place_name', 'dats_place', $verbose);
		$this->indexKeywords('variable', 'variable', 'var_id', 'var_name', 'dats_var', $verbose);
		
		$this->indexKeywords('people', 'personne', 'pers_id', 'pers_name', 'dats_originators', $verbose);
	}
	
	private function indexKeywords($type, $class_name,$id_attribute, $name_attribute, $join_table, $verbose = false){
		$query = "SELECT DISTINCT ON ($name_attribute) $class_name.* FROM $class_name JOIN $join_table USING ($id_attribute)";
		
		if ($verbose){
			echo "-----<br/>$query<br/>";
		}
		$gcmd = new $class_name();
		$liste = $gcmd->getByQuery($query);
		foreach ($liste as $item){
			$keyToIndex['index'] = self::ELASTIC_INDEX_NAME;
			$keyToIndex['type']  = self::ELASTIC_KEYWORD_TYPE;
			$keyToIndex['id']    = $type.'_'.$item->$id_attribute;
			$keyToIndex['body']  = array(
					"name" => $item->$name_attribute,
					"type" => $type
			);
		
			$ret = $this->client->index($keyToIndex);
			if ($verbose){
				print_r($ret);
				echo "<br/>";
			}
		}
	}

	public function indexAllDatasets($verbose = false){
		$result = array();
		$query = "SELECT * FROM dataset WHERE is_requested IS NULL AND is_archived IS NULL ";
		$d = new dataset();
		$datasets = $d->getByQuery($query);

		foreach ($datasets as $dataset){
			$result[] = $this->indexDataset($dataset, $verbose);
		}
		return $result;
	}

	public function indexDataset($dataset, $verbose = false){
		$result = '';
		
		$datsJson = elastic_dataset_factory::datasetToJson($dataset);
		$datsToIndex['index'] = self::ELASTIC_INDEX_NAME;
		$datsToIndex['type']  = self::ELASTIC_DATASET_TYPE;
		$datsToIndex['id']    = $dataset->dats_id;
		$datsToIndex['body']  = $datsJson;
		
		$ret = $this->client->index($datsToIndex);
		
		$result .= "$dataset->dats_id: ";
		$result .= print_r($ret, true);
			
		if ($verbose){
			echo "$result<br/>";		
		}
		return $result;	
		
	}
	
	public function indexDatasetById($id){
		$datsJson = elastic_dataset_factory::datasetByIdToJson($id);
		$datsToIndex['index'] = self::ELASTIC_INDEX_NAME;
		$datsToIndex['type']  = self::ELASTIC_DATASET_TYPE;
		$datsToIndex['id']    = $id;
		$datsToIndex['body']  = $datsJson;

		$ret = $this->client->index($datsToIndex);
		print_r($ret);
		echo '<br/>';
	}

	public function searchKeyword($q, $returnJson = false){
		$searchParams['index'] = self::ELASTIC_INDEX_NAME;
		$searchParams['type']  = self::ELASTIC_KEYWORD_TYPE;
		$searchParams['size']  = 20;
		$searchParams['from']  = 0;

		$searchParams['sort'] = array( 'name.raw');

		$searchParams['body']['query']['query_string']['query'] = $q;

		$retDoc = $this->client->search($searchParams);

		if ($returnJson){
			return json_encode($retDoc, JSON_NUMERIC_CHECK + JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
		}else{
			return $retDoc;
		}
	}

	/*
	 * TODO escape reserved chars :
	* The reserved characters are: + - = && || > < ! ( ) { } [ ] ^ " ~ * ? : \ /
	*/
	public function searchDatasetByKeyword($q, $returnJson = false){
		$searchParams['index'] = self::ELASTIC_INDEX_NAME;
		$searchParams['type']  = self::ELASTIC_DATASET_TYPE;
		$searchParams['fields']  = array('title', 'dataAvailability');
		$searchParams['size']  = 10000;
		$searchParams['from']  = 0;
		$searchParams['sort'] = array('dataAvailability', 'title.raw');

		$searchParams['body']['query']['query_string']['query'] = $q;

		$retDoc = $this->client->search($searchParams);

		if ($returnJson){
			return json_encode($retDoc, JSON_NUMERIC_CHECK + JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
			//$retJson =
			//echo nl2br($retJson);
			//echo '<br/>';
		}else{
			return $retDoc;
		}
	}

	public function searchDataset($recherche, $returnJson = false){
		$searchParams['index'] = self::ELASTIC_INDEX_NAME;
		$searchParams['type']  = self::ELASTIC_DATASET_TYPE;
		$searchParams['fields']  = array('title', 'dataAvailability');
		$searchParams['size']  = 10000;
		$searchParams['from']  = 0;

		$searchParams['sort'] = array('dataAvailability', 'title.raw');

		$query = array();
		if ( array_key_exists('keywords', $recherche) && !empty($recherche['keywords'])){
			if ( array_key_exists('keywords_all',$recherche) && $recherche['keywords_all'] ){
				$keywords = explode(" ", $recherche['keywords']);
				$filtered_keywords = array_filter($keywords);
				$query[] = '('.implode(' AND ', $filtered_keywords).')';
			}else{
				$query[] = '('.$recherche['keywords'].')';
			}
		}
		if (array_key_exists('parameter',$recherche)){
			$query[] = 'variables.gcmd:"'.$recherche['parameter'].'"';
		}
		if (array_key_exists('instrument',$recherche)){
			$query[] = 'sensors.gcmd:"'.$recherche['instrument'].'"';
		}
		if (array_key_exists('platform',$recherche)){
			$query[] = 'places.gcmd:"'.$recherche['platform'].'"';
		}
		if (array_key_exists('project',$recherche)){
			$query[] = 'projects:"'.$recherche['project'].'"';
		}
		if (array_key_exists('period',$recherche)){
			//date max ok
			if ( isset($recherche['period']['max']) && ! empty($recherche['period']['max']) ){
				$query[] = 'dateBegin:{* TO '.$recherche['period']['max'].'}';
			}
			//date min ok
			if ( isset($recherche['period']['min']) && ! empty($recherche['period']['min']) ){
				$query[] = 'dateEnd:{'.$recherche['period']['min'].' TO *}';
			}
		}
		if (array_key_exists('availability',$recherche)){
			$query[] = 'dataAvailability:<='.$recherche['availability'];
		}
		
		
		$q = implode(' AND ', $query);
		if (empty($q)){
			$q = "*";
		}
		//echo "queryString: $q<br/>";
		
		if (array_key_exists('zone', $recherche)){
			$searchParams['body']['query']['filtered']['query']['query_string']['query'] = $q;
			$searchParams['body']['query']['filtered']['filter']['geo_shape']['features']['shape'] =  array(
					'type' => 'envelope',
					'coordinates' => array(
							array($recherche['zone']['west'], $recherche['zone']['north']),
							array($recherche['zone']['east'], $recherche['zone']['south'])
					)
			);
		}else{
			$searchParams['body']['query']['query_string']['query'] = $q;
		}

		//print_r($searchParams);
		//echo "<br/>";
		
		$retDoc = $this->client->search($searchParams);

		if ($returnJson){
			return json_encode($retDoc, JSON_NUMERIC_CHECK + JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
			//$retJson =
			//echo nl2br($retJson);
			//echo '<br/>';
		}else{
			return $retDoc;
		}
	}

	public function searchDatasetByBbox($bbox, $returnJson = false){
		$coords = explode(',', $bbox);
		return $this->searchDatasetByEnvelope($coords[0], $coords[1], $coords[2], $coords[3], $returnJson);
	}
	
	public function searchDatasetByEnvelope( $west, $south, $east, $north, $returnJson = false){
		$searchParams['index'] = self::ELASTIC_INDEX_NAME;
		$searchParams['type']  = self::ELASTIC_DATASET_TYPE;
		$searchParams['fields']  = 'title';
		$searchParams['size']  = 1000;
		$searchParams['from']  = 0;

		$searchParams['sort'] = array('title.raw');

		$searchParams['body']['query']['geo_shape']['features']['shape'] = array(
				'type' => 'envelope',
				'coordinates' => array(
						array($west, $north),
						array($east, $south)
				)
		);

		$retDoc = $this->client->search($searchParams);

		if ($returnJson){
			return json_encode($retDoc, JSON_NUMERIC_CHECK + JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
		}else{
			return $retDoc;
		}
	}

	public function suggest($q, $returnJson = false){
		$params['index'] = self::ELASTIC_INDEX_NAME;
		$params['body']['search_suggest']['text'] = $q;
		$params['body']['search_suggest']['completion'] = array(
				"field" => "suggest",
				"size" => "15"
		);

		$retDoc = $this->client->suggest($params);

		if ($returnJson){
			return json_encode($retDoc, JSON_NUMERIC_CHECK + JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
		}else{
			return $retDoc;
		}

	}

}

?>