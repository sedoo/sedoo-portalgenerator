<?php


/*
 * Teste si un email est déjà présent dans l'annuaire.
 */
function not_in_directory($element, $value, $args){
	//echo 'not_in_directory:'.$value.'<br>';
	try{
		$ldap = new ldapConnect();
		$ldap->openAdm();
		return !$ldap->exists($ldap->getUserDn($value));
	}catch(Exception $e){
		//echo 'erreur<br>';
		return true;
	}
}

?>