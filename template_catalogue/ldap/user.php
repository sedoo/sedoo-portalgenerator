<?php

require_once ("ldap/constants.php");
require_once ("ldap/entry.php");

class user extends entry{

	var $cn;
	var $sn;

	function __construct($dn = null,$attrs = null) {
		if (isset($dn))
			parent::__construct($dn);
		if (isset($attrs))
			$this->initUser($attrs);
	}

	function initUser($attrs){
		$this->cn=$attrs["cn"][0];

		if ($attrs["sn"]){
			$this->lastname=$attrs["sn"][0];
		}
	}
	
	function testGroups($groups){
		return false;
	}
	
	/*
	 * Teste si l'utilisateur est membre d'un des groupes du tableau $groups.
	 * @return false
	 */
	function isMemberOf($groups){
		return false;
	}
	
	function isRoot(){
		return false;
	}
	
	/*function isAdmin(){
		return false;
	}*/
	
	function isProjectAdmin(){
		return false;
	}
	
}

?>