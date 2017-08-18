<?php

class ldapIds {
	
	const LDAP_IDS_FILE = 'ldapIds.json';
		
	var $ids = array();
	
	function __construct (){
		if (file_exists(self::LDAP_IDS_FILE)){
			$this->ids = json_decode(file_get_contents(self::LDAP_IDS_FILE), true);
			 if (json_last_error() > 0){
			 	throw new Exception('ERREUR JSON: ' . json_last_error());
			 }
		}
	}
	
	function __destruct (){
		file_put_contents (self::LDAP_IDS_FILE , json_encode($this->ids, JSON_PRETTY_PRINT));
	}
	
	function getId($project){
		$p = $this->traiteNomProjet($project);
		if (! array_key_exists($p, $this->ids)){
			$this->ids[$p] = $this->getLastId() + 1;
		}
		return $this->ids[$p];
	}
	
	private function traiteNomProjet($project){
		return strtolower(trim($project));
	}
			
	private function getLastId(){
		if ($this->ids == null || empty($this->ids)){
			return 0;
		}else{
			return max(array_values($this->ids));
		}
	}
}

?>