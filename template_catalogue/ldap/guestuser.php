<?php

require_once("ldap/constants.php");
require_once("ldap/entry.php");

class guestuser {

	var $cn;
	var $sn;
	var $mail;

	function __construct($mail) {
		$this->mail = $mail;
		$this->cn = "guest";
		$this->sn = "guest";
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
	
	function isAdmin(){
		return false;
	}

	function isProjectAdmin(){
		return false;
	}
	
	
}

?>
