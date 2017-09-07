<?php

require_once('extract/conf.php');
require_once('/sites/kernel/#MainProject/conf.php');

class requeteFilesXml{

	var $projectName;
	var $user;
	
	var $compression;
	
	var $dataset;
	var $racine;
	var $files;
	

	function requeteFilesXml($user, $projectName, $dataset, $racine){
		if ( $projectName == 'Overall' ){
			$this->projectName = MainProject;
		}else{
			$this->projectName = $projectName;
		}
		$this->user = $user;
		$this->dataset = $dataset;
		$this->racine = $racine;
		$this->compression = XML_DEFAULT_COMPRESSION;
		$this->files = array();
	}
	
	function addFile($file){
		$this->files[] = $file;
	}
		
	function toXml(){
		$xml = simplexml_load_file(XML_FICHIERS_TEMPLATE);

		$xml->projet = $this->projectName;

		$xml->utilisateur->utilisateur_email = $this->user->mail;
		$xml->utilisateur->utilisateur_nom = $this->user->cn;
		$xml->utilisateur->utilisateur_institute = $this->user->affiliation;

		$xml->selection->datsId = $this->dataset->dats_id;
		$xml->selection->racine = $this->racine;
		
		if ( is_dir($this->racine.'/'.DOC_DIR) ){
			$xml->selection->file[] = $this->racine.'/'.DOC_DIR;
		}
		foreach ($this->files as $file)
			$xml->selection->file[] = $file;
				
		$xml->compression = $this->compression;
		
		return $xml->asXml();
	}

}

?>
