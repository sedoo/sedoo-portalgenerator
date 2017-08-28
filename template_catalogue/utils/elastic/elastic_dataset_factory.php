<?php

require_once ("bd/dataset.php");
require_once ("bd/url.php");
require_once ("utils/elastic/dataset_json.php");

class elastic_dataset_factory{
	
	public static function datasetToJson($dats){
		if ( isset($dats) ) {
			$datsJson = new dataset_json($dats->dats_id, $dats->dats_title);
				
			$datsJson->abstract = $dats->dats_abstract;
			$datsJson->purpose = $dats->dats_purpose;
			//$datsJson->references = $dats->dats_reference;
			if ( $dats->dats_date_begin ){
				$datsJson->dateBegin = $dats->dats_date_begin;
			}else{
				$datsJson->dateBegin = null;
			}
			
			if ( $dats->dats_date_end ){
				$datsJson->dateEnd = $dats->dats_date_end;
			}else{
				$datsJson->dateEnd = null;
			}
			
			if ($dats->dats_date_end_not_planned){
				$datsJson->dateEnd = date('Y-m-d');
			}
					
			
			if (isset($dats->period)){
				$datsJson->periodName = $dats->period->period_name;
			}
				
			for ($i = 0; $i < count($dats->projects); $i++){
				if (isset($dats->projects[$i])){
					$datsJson->projects[$i] = $dats->projects[$i]->toString();
				}
			}
			
			for ($i = 0; $i < count($dats->originators); $i++){
				$datsJson->contacts[$i] = array();
				$datsJson->contacts[$i]['name'] = $dats->originators[$i]->pers_name;
				$datsJson->contacts[$i]['organisation'] = $dats->originators[$i]->organism->getName();
			}
				
			for ($i = 0; $i < count($dats->dats_variables); $i++){
				$datsJson->variables[$i] = array();
				if ( !empty($dats->dats_variables[$i]->variable->var_name ) ){
					$datsJson->variables[$i]['name'] = $dats->dats_variables[$i]->variable->var_name;
					
				}else{
					$datsJson->variables[$i]['name'] = null;
				}
				if (isset($dats->dats_variables[$i]->variable->gcmd)){
					$datsJson->variables[$i]['gcmd'] = $dats->dats_variables[$i]->variable->gcmd->toString();
				}
			}

			for ($i = 0; $i < count($dats->dats_sensors);$i++){
				if (isset($dats->dats_sensors[$i]->sensor->gcmd_instrument_keyword) ){
					$datsJson->sensors[$i]['gcmd'] = $dats->dats_sensors[$i]->sensor->gcmd_instrument_keyword->gcmd_sensor_name;
				}
				if (isset($dats->dats_sensors[$i]->sensor->manufacturer)){
					$datsJson->sensors[$i]['manufacturer'] = $dats->dats_sensors[$i]->sensor->manufacturer->manufacturer_name;
				}
				if (isset($dats->dats_sensors[$i]->sensor->sensor_model) && !empty($dats->dats_sensors[$i]->sensor->sensor_model)){
					$datsJson->sensors[$i]['model'] = $dats->dats_sensors[$i]->sensor->sensor_model;
				}
				if (isset($dats->dats_sensors[$i]->sensor->boundings)){
					$datsJson->addBoundings($dats->dats_sensors[$i]->sensor->boundings);
				}
			}
			
			$indicePlace = 0;
			for ($i = 0; $i < count($dats->sites); $i++){
												
				$datsJson->places[$indicePlace]['name'] = $dats->sites[$i]->place_name;
				if ( isset($dats->sites[$i]->gcmd_plateform_keyword) ){
					$datsJson->places[$indicePlace]['gcmd'] = $dats->sites[$i]->gcmd_plateform_keyword->gcmd_plat_name;
				}
								
				if (isset($dats->sites[$i]->boundings)){
					//print_r($dats->sites[$i]->boundings);echo "<br/>";
					$datsJson->addBoundings($dats->sites[$i]->boundings);
				}
				
				if (isset($dats->sites[$i]->parent_place)){
					$datsJson->places[$indicePlace]['parent'] = $dats->sites[$i]->parent_place->place_name;
				}
				$indicePlace++;
			}
			
		/*	switch (get_class($dats)){
				case self::SATELLITE_DATASET_CLASS:
					//Satellites
					for ($i = 0; $i < count($dats->sats); $i++) {
						$datsJson->places[$indicePlace]['name'] = $dats->sats[$i]->place_name;
						if ( isset($dats->sats[$i]->gcmd_plateform_keyword) ){
							$datsJson->places[$indicePlace]['gcmd'] = $dats->sats[$i]->gcmd_plateform_keyword->gcmd_plat_name;
						}
					}
					
					$datsJson->dataType = $dats->dataType->place_name;
					break;
				case self::MODEL_DATASET_CLASS:
							
					break;
				default:
				
					
			}
			*/
			
			//urls
			$u = new url();
			$urls = $u->getByDataset($dats->dats_id);
			$indiceUrl = 0;
			$withData = false;
			foreach ($urls as $url){
				if ($url->url_type == 'ql'){
					$datsJson->urls[$indiceUrl]['type'] = 'ql';
					$datsJson->urls[$indiceUrl]['url'] = $url->url;
					$indiceUrl++;
				}else if($url->url_type == 'local file'){
					$datsJson->urls[$indiceUrl]['type'] = 'local';
					$datsJson->urls[$indiceUrl]['url'] = $url->url;
					$indiceUrl++;
					$withData = true;
				}else if($url->url_type == 'http' || $url->url_type == 'ftp'){
					$datsJson->urls[$indiceUrl]['type'] = 'extData';
					$datsJson->urls[$indiceUrl]['url'] = $url->url;
					$indiceUrl++;
					$withData = true;
				}				
			}
						
			if ($withData){
				$datsJson->dataAvailability = dataset_json::WITH_DATA;
			}
			
			if ($dats->isInsertedDataset()){
				$datsJson->dataAvailability = dataset_json::WITH_INSERTED_DATA;
			}
			
			/*$datsJson->suggest = array();
			$datsJson->suggest["input"] = explode(" ",$dats->dats_title);
			$datsJson->suggest["output"] = $dats->dats_title;
				*/		
			return json_encode($datsJson, JSON_NUMERIC_CHECK + JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE);
		}else{
			return null;
		}
	}
	
}

?>