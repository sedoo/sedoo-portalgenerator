<?php

require_once('extract/contact.php');

class contactsJeu{
	
	var $datsId;
	var $datsTitle;
	
	var $contacts;
	
	function contactsJeu($xmlElt){
		$this->datsId = (string)$xmlElt->dataset_id;
		$this->datsTitle = (string)$xmlElt->dataset_title;
		$this->contacts = array();
		foreach ($xmlElt->contacts->contact as $c){
			$this->contacts[] = new contact($c);
		}
		
	}
	
	function toHtml(){
		$url = $_SERVER['REQUEST_URI']."&datsId=$this->datsId";
		echo "<a href='$url'>$this->datsTitle</a>";
		foreach ($this->contacts as $c){
			echo '<br>'.$c->toString();
		}
		
	}
	
}

?>