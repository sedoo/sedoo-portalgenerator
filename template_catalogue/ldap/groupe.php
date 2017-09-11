<?php

require_once ("ldap/entry.php");

class groupe extends entry{
	
	var $id;
	var $cn;
	var $description;

	var $project;
	var $isAdmin = false;
	
	function stringToBoolean($str){
		$trueStrings = array('TRUE','True','true','yes','Yes','YES');
		
		if (in_array($str,$trueStrings))
			return true;
		else
			return false;
	}
	
	function __construct($dn = null,$attrs = null) {
		if (isset($dn))
		parent::__construct($dn);

		if (isset($attrs)){
			$this->cn=$attrs["cn"][0];
			$this->id=$attrs["groupId"][0];
			if (isset($attrs["description"]) && !empty($attrs["description"])){
				$this->description=$attrs["description"][0];
			}
			if (isset($attrs["parentProject"]) && !empty($attrs["parentProject"])){
				$this->project=$attrs["parentProject"][0];
			}
			if (isset($attrs["isAdmin"]) && !empty($attrs["isAdmin"])){
				$this->isAdmin=$this->stringToBoolean($attrs["isAdmin"][0]);
			}else{
				$this->isAdmin = false;
			}
		}
	}

}

?>