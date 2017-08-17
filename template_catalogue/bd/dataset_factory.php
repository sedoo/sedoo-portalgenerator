<?php

require_once ("bd/satellite_dataset.php");
require_once ("bd/dataset_type.php");
require_once ("bd/dataset.php");

class dataset_factory{

	public static function createDatasetById($id){
		$query = "SELECT dataset_type.* FROM dats_type JOIN dataset_type USING (dats_type_id) WHERE dats_id = $id";

		$dt = new dataset_type();
		$types = $dt->getByQuery($query);
		
		if (empty($types)){
			//IN SITU
		}else{
			switch ($types[0]->dats_type_title) {
				case dataset_type::TYPE_SATELLITE:
					return self::createSatelliteDatasetById($id);
					break;
				case dataset_type::TYPE_MODEL:
					
					break;
				default:
					//IN SITU
			}
		}
		
		$dataset = new dataset();
		return $dataset->getById($id);
		
	}
	
	public static function createSatelliteDatasetById($id){
		return  self::createSatelliteDataset(  self::searchById($id) );
	}
	
	private static function createSatelliteDataset($tab){
		return  self::createDataset($tab,'satellite_dataset');
	}
	
	private static function createDataset($tab, $type){
		if (isset($tab) && !empty($tab)){
			$dts = new $type();
			$dts->init($tab);
			return $dts;
		}
		return null;
	}

	private static function searchById($id){
		if (!isset($id) || empty($id))
			return null;

		$query = "select * from dataset where dats_id = ".$id;
		$bd = new bdConnect;
		if ($resultat = $bd->get_data($query)){
			return $resultat[0];
		}
		return null;
	}
}

?>