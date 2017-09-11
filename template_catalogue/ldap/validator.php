<?php


/*
 * Teste si un email est déjà présent dans l'annuaire.
 */
function not_in_directory($element, $value, $args){
	try{
		$ldap = new ldapConnect();
		$ldap->openAdm();
		return !$ldap->exists($ldap->getUserDn($value));
	}catch(Exception $e){
		return true;
	}
}

?>