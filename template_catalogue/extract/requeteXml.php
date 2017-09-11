<?php

require_once('extract/conf.php');

class requeteXml{

	var $projectName;
	var $latMin;
	var $latMax;
	var $lonMin;
	var $lonMax;
	var $dateMin;
	var $dateMax;		
	var $format;
	var $format_version;
	var $compression;	
	var $withFlag;
	var $withDelta;	
	var $datasets;
	var $places;
	var $variables;	
	var $user;

	function requeteXml($user, $projectName){
		$this->projectName = $projectName;
		$this->user = $user;
		
		$this->format = XML_DEFAULT_FORMAT;
		$this->format_version = XML_DEFAULT_FORMAT_VERSION;
		$this->compression = XML_DEFAULT_COMPRESSION;

		$this->withDelta = XML_DEFAULT_FLAG;
		$this->withFlag = XML_DEFAULT_DELTA;
		
		$this->datasets = array();
		$this->places = array();
		$this->variables = array();
	}

	static function readXml($xml){
		$xml = simplexml_load_string($xml);
		$user = new portalUser;
		$user->mail = $xml->utilisateur->utilisateur_email;
		$user->cn = $xml->utilisateur->utilisateur_nom;
		$user->affiliation = $xml->utilisateur->utilisateur_institute;

		$requete = new requeteXml($user,$xml->projet);

		foreach ($xml->selection->datasets->dats_id as $datsId)
				$requete->datasets[] = $datsId;

		foreach ($xml->selection->variables->var_id as $varId)
				$requete->variables[] = $varId;

		foreach ($xml->selection->places->place_id as $placeId)
				$requete->places[] = $placeId;

		$requete->dateMin = str_replace('T00:00:00','',$xml->selection->periode->date_min);
		$requete->dateMax = str_replace('T23:59:59','',$xml->selection->periode->date_max);

		$requete->latMin = round((double)$xml->selection->zone->lat_min,2);
		$requete->lonMin = round((double)$xml->selection->zone->lon_min,2);
		$requete->lonMax = round((double)$xml->selection->zone->lon_max,2);
		$requete->latMax = round((double)$xml->selection->zone->lat_max,2);


		$requete->format = $xml->options->format;
		$requete->format_version = $xml->options->format_version;
		$requete->compression = $xml->options->compression;
		$requete->withFlag = $xml->options->valeur_flag;
		$requete->withDelta = $xml->options->valeur_delta;

		return $requete;
    }

	function toString() {
		return "Period: $this->dateMin - $this->dateMax
				Zone: $this->latMin, $this->latMax, $this->lonMin, $this->lonMax
				Datasets: ".count($this->datasets)."
				Variables: ".count($this->variables).
				//                      "\nPlaces: ".count($this->places).
				"\nFormat: $this->format
				Compression: $this->compression";
    }
	
	function toXml(){
		$xml = simplexml_load_file(XML_TEMPLATE);

		$xml->projet = $this->projectName;

		if (isset($this->dateMin) && !empty($this->dateMin))
			$xml->selection->periode->date_min = $this->dateMin.'T00:00:00';
		if (isset($this->dateMax) && !empty($this->dateMax))
			$xml->selection->periode->date_max = $this->dateMax.'T23:59:59';

		if (isset($this->latMin) && !empty($this->latMin))
			$xml->selection->zone->lat_min = $this->latMin;
		if (isset($this->latMax) && !empty($this->latMax))
			$xml->selection->zone->lat_max = $this->latMax;
		if (isset($this->lonMin) && !empty($this->lonMin))
			$xml->selection->zone->lon_min = $this->lonMin;
		if (isset($this->lonMax) && !empty($this->lonMax))
			$xml->selection->zone->lon_max = $this->lonMax;

		foreach ($this->datasets as $datsId)
			$xml->selection->datasets->dats_id[] = $datsId;

		foreach ($this->places as $placeId)
			$xml->selection->places->place_id[] = $placeId;
			
		foreach ($this->variables as $varId)
			$xml->selection->variables->var_id[] = $varId;
			
		$xml->utilisateur->utilisateur_email = $this->user->mail;
		$xml->utilisateur->utilisateur_nom = $this->user->cn;
		$xml->utilisateur->utilisateur_institute = $this->user->affiliation;

		$xml->options->format = $this->format;
		$xml->options->format_version = $this->format_version;
		$xml->options->compression = $this->compression;
		$xml->options->valeur_flag = $this->withFlag;
		$xml->options->valeur_delta = $this->withDelta;

		return $xml->asXml();
	}

}

?>
