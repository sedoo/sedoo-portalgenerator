<?php

require_once ('extract/fichierResultat.php');
require_once ('extract/conf.php');

class reponseXml{
	
	var $id;	
	var $userName;
	var $mail;	
	var $roles;
	var $files;	
	var $project_name;	
	var $isPublic;

	function reponseXml($id,$project_name){
		$this->project_name = $project_name;
		
		$file = EXTRACT_RESULT_PATH.'/'.$id.'.xml';
		if (is_file($file)){
			$xml = simplexml_load_file($file);
			$this->readXml($xml);
		}else{
			throw new Exception("Result not found for id $id");
		}
	}
	
	function isPublic(){
		return 'true' == $this->isPublic;
	}

	function readXml($xml){
		$this->id = $xml->attributes()->requestId;
		$this->isPublic = $xml->attributes()->public;
		$this->mail = $xml->mail;
		$this->userName = $xml->user;
		
		$this->roles = array();
		foreach ($xml->roles->role as $r){
			$this->roles[] = $r;
		}
		$this->files = array();
		foreach ($xml->file as $f){
			$this->files[] = new fichierResultat($f,$this->project_name);
		}
		
	}
	
	function toHtml(){
		echo '<form action="" method="post" name="frmdl" id="frmdl"><table><tr><th align="center">File</th><th align="center">Size</th><th align="center">Contacts</th></tr>';
		foreach ($this->files as $f){
			$f->toHtml();
		}
		echo '</table>';
	}
	
}

?>
