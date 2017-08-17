<?php

require_once('extract/contactsJeu.php');

class fichierResultat{
	
	var $filename;
	
	var $contacts;
	var $associatedFiles;
	
	var $project_name;
	
	function fichierResultat($xmlElt,$project_name){
		$this->project_name = $project_name;
		$this->filename = (string)$xmlElt->filename;

		$this->contacts = array();
		foreach ($xmlElt->dataset as $d){
			$this->contacts[] = new contactsJeu($d);
		}
		
		$this->associatedFiles = array();
		foreach ($xmlElt->associated_file as $f){
			$this->associatedFiles[] = (string)$f;
		}
		
		
	}
	
	function getFileSize() {
		$size = filesize($this->filename);

		$units = array(' B', ' KB', ' MB', ' GB', ' TB');
		for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
		return round($size, 2).$units[$i];
	}
	
	function toHtml(){
		$rows = count($this->contacts);
		$id = uniqid('extract_');
		//$_SESSION[$id] = (string)$this->filename;
		$_SESSION[$id] = serialize($this);
		$urlFichier = "/extract/dl.php?file=$id&project_name=$this->project_name";
		echo "<tr><td rowspan=$rows><a href=$urlFichier>".basename($this->filename).'</a></td>';
		echo "<td style='white-space:nowrap;' rowspan=$rows>".$this->getFileSize().'</td>';
		
		foreach ($this->contacts as $c){
			echo '<td>';
			$c->toHtml();
			echo '</td></tr>';
		}
		//echo '</tr>';
	}
	
	
}

?>