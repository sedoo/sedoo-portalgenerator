<?php

function isValidDate($atester){
	$temp = explode('-', $atester);
	if (count($temp) != 3){
		return false;
	}else{
		return checkdate($temp['1'],$temp['2'],$temp['0']);
	}
}

function isValidInterval($min,$max){
	return $min <= $max;
}

function validGid($element_name, $element_value){
	if ( empty($element_value) || ( is_numeric($element_value) && $element_value > 999 ) )
		return true;
	else
		return false;
}

/**
 * Makes sure a "date" is a valid MYSQL format.
 *
 * @param   string  $element_name	name of form field to check
 * @param   string  $element_value	Date to Check
 * @return  bool
 */
function validDate($element_name, $element_value){
	return isValidDate($element_value);
}

function validPeriod($element_names, $element_values){

	if (isset($element_values[0]) && isset($element_values[1]) && isValidDate($element_values[0]) && isValidDate($element_values[1]) ){
		return $element_values[0] <= $element_values[1];
	}else{
		return true;
	}

}

/*
 * Teste si une entrée existe déjà dans la base
 * args : 0 -> table, 1 -> colonne
 */
function existe($element, $value, $args){
	$obj = new $args[0];
	$obj->$args[1] = $value;
	return ! $obj->existe();
}


/*
 * Si un nom de contact est saisi, email1 et organisme sont obligatoires.
 * element: element texte sur lequel s'applique la regle
 * value: valeur saisie
 * args: array(0 => formulaire, 1 => indice du contact
 */
function contact_email_required($element, $value, $args)
{

	$email = $args[0]->exportValue("email1_".$args[1]);
	$org = $args[0]->exportValue("organism_".$args[1]);

	if (!empty($value) && empty($email)){
		return false;
	}else
	return true;
}
/*
 * Si un nom de contact est saisi, organisme est obligatoire (sname et/ou fname).
 * element: element texte sur lequel s'applique la regle
 * value: valeur saisie
 * args: array(0 => formulaire, 1 => indice du contact
 */
function contact_organism_required($element, $value, $args)
{

	$org = $args[0]->exportValue("organism_".$args[1]);
	$org_sname = $args[0]->exportValue("org_sname_".$args[1]);
	$org_fname = $args[0]->exportValue("org_fname_".$args[1]);

	if ( !empty($value) && ($org == 0) && empty($org_sname) && empty($org_sname) ) {
		return false;
	}else{
		return true;
	}

}

//args: array(0 => min, 1 => max
function number_range($element, $value, $args)
{

	if ( (isset($args[0]) && ($value < $args[0]) ) || (isset($args[1]) && ($value > $args[1]) ) ){
		return false;
	}else{
		return true;
	}
}

function validInterval($element_names, $element_values){

	if (isset($element_values[0]) && !empty($element_values[0]) && isset($element_values[1]) && !empty($element_values[1])){
		return isValidInterval($element_values[0],$element_values[1]);
	}else{
		return true;
	}

}

/*
 * $args: form, gcmd, name
 */
function validParam($element, $value, $args){
	$suffix = $args[1];
	$gcmd = $args[0]->exportValue('gcmd_science_key_'.$suffix);
	$varName = $args[0]->exportValue('new_variable_'.$suffix);

	if (!empty($value) && ($gcmd[0] == 0) && empty($varName) ){
		return false;
	}else{
		return true;
	}

}

function validUnit_existe($element_names, $element_values){

	if ($element_values[0] == 0){
		if (isset($element_values[1]) && !empty($element_values[1])){
			return existe(null,$element_values[1],array('unit','unit_name'));
		}else
		return true;
	}else{
		return true;
	}

}

function validUnit_required($element_names, $element_values){

	if ($element_values[0] == 0){
		if (isset($element_values[2]) && !empty($element_values[2])){
			return (isset($element_values[1]) && !empty($element_values[1]));
		}else
		return true;
	}else{
		return true;
	}

}

/*
 * $element_names : west, east, south, north
 */
function completeBoundings($element_names, $element_values){
	$cpt = 0;
	foreach ($element_values as $val){
		if (strlen($val) > 0)
		$cpt++;
	}

	if ($cpt == 0 || $cpt == 4){
		return true;
	}else{
		return false;
	}
}



function validBoundings($element_names, $element_values){
	if (completeBoundings($element_names, $element_values)){
		return isValidInterval($element_values[0],$element_values[1]) && isValidInterval($element_values[2],$element_values[3]);
	}else
	return true;
}


	/*
	 * element: element liste sur lequel s'applique la regle
	 * value: valeur choisie dans la liste (0 => rien) 
	 * args: array(0 => formulaire, 1 => champ texte à vérifier 
	 */
	function couple_not_null($element, $value, $args)
		{

			$arg_value = $args[0]->exportValue($args[1]);
			
			if ($value == 0 && empty($arg_value)){
				return false;
			}else{
				return true;
			}
			
		}

?>
