<?php
use Elasticsearch\Endpoints\Indices\Validate\Query;

require_once ('utils/elastic/dataset_json.php');
require_once ("bd/url.php");
require_once ("utils/elastic/ElasticClient.php");
class ElasticSearchUtils {
	private static function queryToQueryArgs($query) {
		$query_array = array ();
		
		$query_array ['terms'] = $query ['keywords'];
		
		if ($query ['keywords_all']) {
			$query_array ['allKeywords'] = 1;
		}
		
		if ($query ['period'] ['min']) {
			$query_array ['dtstart'] = $query ['period'] ['min'];
		}
		if ($query ['period'] ['max']) {
			$query_array ['dtend'] = $query ['period'] ['max'];
		}
		
		if ($query ['project']) {
			$query_array ['project'] = $query ['project'];
		}
		
		if ($query ['parameter']) {
			$query_array ['parameter'] = $query ['parameter'];
		}
		
		if ($query ['parameter']) {
			$query_array ['parameter'] = $query ['parameter'];
		}
		
		if ($query ['instrument']) {
			$query_array ['instrument'] = $query ['instrument'];
		}
		
		if ($query ['platform']) {
			$query_array ['platform'] = $query ['platform'];
		}
		
		if (array_key_exists ( 'zone', $query )) {
			$query_array ['bbox'] = $query ['zone'] ['west'] . "," . $query ['zone'] ['south'] . "," . $query ['zone'] ['east'] . "," . $query ['zone'] ['north'];
		}
		
		if ($query ['availability']) {
			$query_array ['availability'] = $query ['availability'];
		}
		return $query_array;
	}
	
	
	
	static function lstQueryData($query, $projectName) {
		$client = new ElasticClient ();
		$result = $client->searchDataset ( $query );
		
		if ($result) {
			
			$queryArgs = ElasticSearchUtils::queryToQueryArgs ( $query );
			if ($projectName) {
				$queryArgs ['project_name'] = $projectName;
			}
			
			$searchDatsIds = array();
			foreach ( $result ['hits'] ['hits'] as $hit ) {
				if ($hit ['fields'] ['dataAvailability'] [0] == dataset_json::WITH_INSERTED_DATA){
					$searchDatsIds[] = $hit ['_id'];
				}else{
					break;
				}
			}
			self::printLegende($projectName, $searchDatsIds, $queryArgs);
			
			echo '<b>' . $result ['hits'] ['total'] . ' dataset(s) found</b><br/>';
			
			echo "<ul>";
			foreach ( $result ['hits'] ['hits'] as $hit ) {
				echo '<li>';
				$datsDataAvailability = $hit ['fields'] ['dataAvailability'] [0];
				echo self::printDataset ( $hit ['_id'], $hit ['fields'] ['title'] [0], ($datsDataAvailability == dataset_json::WITH_INSERTED_DATA), $projectName, $queryArgs );
				echo '</li>';
			}
			echo "</ul>";
		}
	}
	
	static function printDataset($datsId, $datsTitle, $withInsertedData, $projectName, $queryArgs = array(), $withTitle = false) {
		$nodeConf = self::getDataNodeConf ( $datsId, $datsTitle, $withInsertedData, $projectName, $queryArgs );
				
		if ($withTitle == false)
			$result = "<a href='" . $nodeConf ['link'] . "'>" . $nodeConf ['text'] . "</a>";
		else
			$result = "<a href='" . $nodeConf ['link'] . "'>View</a><br>";
		
		if (isset ( $nodeConf ['dataLink'] )) {
			$result .= '&nbsp;&nbsp;<a href="' . $nodeConf ['dataLink'] . '"><img width="15" height="16" class="text" src="' . $nodeConf ['dataIcon'] . '" title="' . $nodeConf ['dataTitle'] . '" /></a>';
		}
		if (isset ( $nodeConf ['extDataLink'] )) {
			$result .= '&nbsp;&nbsp;<a href="' . $nodeConf ['extDataLink'] . '" target="_blank"><img width="15" height="16" class="text" src="' . $nodeConf ['extDataIcon'] . '" title="' . $nodeConf ['extDataTitle'] . '" /></a>';
		}
		if (isset ( $nodeConf ['bdLink'] )) {
			$result .= '&nbsp;&nbsp;<a href="' . $nodeConf ['bdLink'] . '"><img width="15" height="16" class="text" src="' . $nodeConf ['bdIcon'] . '" title="' . $nodeConf ['bdTitle'] . '" /></a>';
		}
		if (isset ( $nodeConf ['qlLink'] )) {
			$result .= '&nbsp;&nbsp;<a href="' . $nodeConf ['qlLink'] . '" target="_blank"><img width="15" height="16" class="text" src="' . $nodeConf ['qlIcon'] . '" title="' . $nodeConf ['qlTitle'] . '" /></a>';
		}
		if (isset ( $nodeConf ['viewerLink'] )) {
			$result .= '&nbsp;&nbsp;<a href="' . $nodeConf ['viewerLink'] . '" ><img width="15" height="16" class="text" src="' . $nodeConf ['viewerIcon'] . '" title="' . $nodeConf ['viewerTitle'] . '" /></a>';
		}
		if (isset ( $nodeConf ['quicklooksLink'] )) {
			$result .= '&nbsp;&nbsp;<a href="' . $nodeConf ['quicklooksLink'] . '" ><img width="15" height="16" class="text" src="' . $nodeConf ['quicklooksIcon'] . '" title="' . $nodeConf ['quicklooksTitle'] . '" /></a>';
		}
		return $result;
	}
	
	static function getMultipleInsertedDatsLink($searchDatsIds, $projectName, $queryArgs = array()){
		
		if (count($searchDatsIds) > 1){
			$queryString = "";
			if ( isset($projectName) && !empty($projectName) && !array_key_exists('project_name', $queryArgs )){
				$queryString .= "&project_name=$projectName";
			}
			foreach ( $queryArgs as $arg => $val ) {
				$queryString .= "&$arg=$val";
			}
			
			$queryString = "searchDatsIds=".implode(',', $searchDatsIds).$queryString;
			$url = "/Data-Download-BD/?$queryString";
			
			return $url;
		}
	}
	
	static function printLegende($projectName, $searchDatsIds = array(), $queryArg = array()) {
		$legende = array ();
		if (constant ( strtolower ( $projectName ) . '_HasBlueTag' ) == 'true'){
			$legende ['Blue'] = 'Dataset files';
		}
		if (constant ( strtolower ( $projectName ) . '_HasGreenTag' ) == 'true'){
			$legende ['Green'] = 'Data and output format selection';
		}
		if (constant ( strtolower ( $projectName ) . '_HasPurpleTag' ) == 'true'){
			$legende ['Purple'] = 'Data in another database';
		}
		if (constant ( strtolower ( $projectName ) . '_HasOrangeTag' ) == 'true'){
			$legende ['Orange'] = 'Charts from the campaign website';
		}
		/*if (constant ( strtolower ( $projectName ) . '_HasPinkTag' ) == 'true'){
			$legende ['Pink'] = 'Quicklooks';
		}
		if (constant ( strtolower ( $projectName ) . '_HasLightBlueTag' ) == 'true'){
			$legende ['LightBlue'] = 'Data preview';
		}*/
				
		
		echo '<section role="legend"><h3>Access to...</h3><ul>';
		foreach ( $legende as $color => $texte ) {
			if ( $color == 'Green' && count($searchDatsIds) > 1 ){
				$url = self::getMultipleInsertedDatsLink($searchDatsIds, $projectName, $queryArg);
				echo "<li><span class='icon-folder-open' data-color='$color'></span><a href='$url' >$texte</a></li>";
			}else{
				echo "<li><span class='icon-folder-open' data-color='$color'></span>$texte</li>";
			}
		}
		echo "</ul></section>";
	}
	
	static function getDataNodeConf($datsId, $datsTitle, $withInsertedData, $projectName, $queryArgs = array()) {
		
		$queryString = "";
		if ( isset($projectName) && !empty($projectName) && !array_key_exists('project_name', $queryArgs )){
			$queryString .= "&project_name=$projectName";
		}
		foreach ( $queryArgs as $arg => $val ) {
			$queryString .= "&$arg=$val";
		}
				
		$nodeConf = array (
				'text' => $datsTitle,
				'link' => "/Data-Search/?datsId=$datsId$queryString",
				'datsId' => $datsId 
		); 
		$u = new url ();
		$urls = $u->getByDataset ( $datsId );
		foreach ( $urls as $url ) {
			if ($url->url_type == 'local file') {
				$nodeConf ['dataLink'] = "/Data-Download/?datsId=$datsId$queryString";
				$nodeConf ['dataIcon'] = "/scripts/images/dataBlue.gif";
				$nodeConf ['dataTitle'] = 'Dataset as provided by the Principal Investigator';
			} else if ($url->url_type == 'ftp') {
				if (strpos ( $url->url, 'ipsl' ) === false) {
					// Jeu ftp pas ipsl
					$nodeConf ['dataLink'] = $url->url;
					$nodeConf ['dataIcon'] = $root . "/scripts/images/dataPurple.gif";
					$db = new database ();
					$database = $db->getByDatsId ( $dts->dats_id );
					if (isset ( $database )) {
						$nodeConf ['dataTitle'] = $database->database_name . ' FTP access';
					} else {
						$nodeConf ['dataTitle'] = 'Dataset available in another database';
					}
				} else {
					$nodeConf ['dataLink'] = "/Data-Download-IPSL/?LnkFTP=$url->url$queryString";
					$nodeConf ['dataIcon'] = $root . "/scripts/images/dataBlue.gif";
					$nodeConf ['dataTitle'] = 'Original dataset as provided by the Principal Investigator';
				}
			} else if ($url->url_type == 'ql') {
				$nodeConf ['qlLink'] = $url->url;
				$nodeConf ['qlIcon'] = "/scripts/images/dataOrange.gif";
				$nodeConf ['qlTitle'] = 'Charts from the campaign website';
			} else if (strpos ( $url->url, 'http' ) === 0) {
				// Autre centre de données
				$nodeConf ['extDataLink'] = $url->url;
				$nodeConf ['extDataIcon'] = "/scripts/images/dataPurple.gif";
				$db = new database ();
				$database = $db->getByDatsId ( $datsId );
				if (isset ( $database )) {
					$nodeConf ['extDataTitle'] = $database->database_name;
				} else {
					$nodeConf ['extDataTitle'] = 'Dataset available in another database';
				}
			}
		}
		
		if ($withInsertedData) {
			// Données insérées
			$nodeConf ['bdLink'] = "/Data-Download-BD/?datsId=$datsId$queryString";
			$nodeConf ['bdIcon'] = "/scripts/images/dataGreen.gif";
			$nodeConf ['bdTitle'] = 'Further selection';
			
			$nodeConf ['calLink'] = "/Data-Calendar/?datsId=$datsId$queryString";
			$nodeConf ['calIcon'] = "/scripts/images/dataYellow.gif";
			$nodeConf ['calTitle'] = 'Data availability calendar';
		}
		
		//Quicklooks
		/*$queryConf = "SELECT * FROM config_ql WHERE dats_id = $datsId";
		$bd = new bdConnect ();
		$liste = array ();
		if ($resultat = $bd->get_data ( $queryConf )) {
			$nodeConf ['quicklooksLink'] = "/Data-QL/?datsId=$datsId$queryString";
			$nodeConf ['quicklooksIcon'] = "/scripts/images/dataPink.gif";
			$nodeConf ['quicklooksTitle'] = 'Quicklooks';
		}*/
		
		//Simple calendar
		/*$queryConf2 = "SELECT * FROM config_cal WHERE dats_id = $datsId";
		$bd = new bdConnect ();
		$liste = array ();
		if ($resultat = $bd->get_data ( $queryConf2 )) {
			$nodeConf ['calLink'] = "/Data-Calendar/?simple&datsId=$datsId$queryString";
			$nodeConf ['calIcon'] = "/scripts/images/dataYellow.gif";
			$nodeConf ['calTitle'] = 'Data availability calendar';
		}*/
		
		//Data preview
		$queryConf3 = "SELECT dats_id,ins_dats_id from inserted_dataset JOIN dats_data USING (ins_dats_id) LEFT JOIN extract_config USING (dats_id) WHERE dats_id = $datsId AND feature_type_id IN (2,5) ";
		$bd = new bdConnect ();
		$liste = array ();
		if ($resultat = $bd->get_data ( $queryConf3 )) {
			$nodeConf ['viewerLink'] = "/Data-Viewer/?datsId=$datsId$queryString";
			$nodeConf ['viewerIcon'] = "/scripts/images/dataLightBlue.gif";
			$nodeConf ['viewerTitle'] = 'Data preview';
		}
		
		// TODO autres urls (opendap, thredds, ...)
		
		return $nodeConf;
	}
	static function getQueryString() {
		parse_str ( $_SERVER ["QUERY_STRING"], $query_array );
		unset ( $query_array ['datsId'] );
		return http_build_query ( $query_array );
	}
	static function addBackToSearchResultLink() {
		$queryString = ElasticSearchUtils::getQueryString ();
		
		$reqUri = "/Data-Search/?$queryString";
		echo "<br/><a style='font-size:110%;font-weight:bold;' href='$reqUri'>&lt;&lt;&nbsp;Back to search result</a><br/>";
	}
}
?>