<?php

require_once('forms/validation.php');

/*
 * Teste qu'un champ texte est saisi si une option a été choisie dans un select
 * element: element liste sur lequel s'applique la regle
 * value: valeur choisie dans la liste (0 => rien) 
 * args: array(0 => formulaire, 1 => champ texte à considérer 
 */
function required_if_not_void($element, $value, $args) {
	$arg_value = $args[0]->exportValue($args[1]);
        if (empty($arg_value) && $value != 0){
        	return false;
        }else{
		return true;
        }
}

/*
 * Teste qu'un champ texte est saisi si un 
* element: element liste sur lequel s'applique la regle
* value: valeur saisie dans le champ texte
* args: array(0 => formulaire, 1 => champ texte à considérer
		*/
function required_if_not_void3($element, $value, $args) {
	$arg_value = $args[0]->exportValue($args[1]);
	if (empty($arg_value) && !empty($value)){
		return false;
	}else{
		return true;
	}
}


/**
 * validation au niveau formulaire des champs boundings
 */
function test_valid_form($fields){
	//echo 'test_valid_form'.count($fields).'<br/>';
	
	$retour = array();
	
	$boundings_fields = array('west_bound','east_bound','south_bound', 'north_bound');	
	$cpt = 0;
	foreach ($boundings_fields as $field){
		if (strlen($fields[$field]) > 0)
			$cpt++;
	}
	//echo 'boundings: '.$cpt.'<br>';
	if ($cpt == 0 || $cpt == 4){
		//return true;
	}else{
		$retour['west_bound'] = 'Site: Incomplete boundings';
		//return array('west_bound' => 'Site: Incomplete boundings');
	}
	
	if (!empty($fields['place_alt_min']) || !empty($fields['place_alt_max'])){
		if (empty($fields['new_place'])){
			$retour['new_place'] = 'Site: Exact location is required when altitude min or max is set';
			//return array('new_place' => 'Site: exact location is required');
		}
	}
	
	if (empty($retour)){
		return true;
	}else{
		return $retour;
	}
}

/*
 * Teste qu'une option a été choisie dans un select si un champ texte n'est pas vide
 * element: element liste sur lequel s'applique la regle
 * value: valeur choisie dans la liste (0 => rien) 
 * args: array(0 => formulaire, 1 => champ texte à considérer 
 */
function required_if_not_void2($element, $value, $args){
	$arg_value = $args[0]->exportValue($args[1]);
        if (!empty($arg_value) && $value == 0){
		return false;
        }else{
                return true;
        }
}

/*
 * Teste si des entrées existent déjà dans la base
 * value: valeurs à tester (séparées par des ';'
 * args : 0 -> table, 1 -> colonne
 */
function existInDb($element, $value, $args){
	$values = split(";",$value);
        $result = true;
        foreach ($values as $val){
        	if (!empty($val)){
                	$result = $result && existe($element,$val,$args);
                }
        }
}

function not_void($elements, $values){
	foreach ($elements as $elt){
        	echo "- elt: ".$elt;
        }
                foreach ($values as $val){
                        echo "- val: ".$val;
                }


                        return true;

        }

function distinct($element_names, $element_values){
                        sort($element_values);
                        $valPrec = '';
                        $distinct = true;

                        foreach ($element_values as $val){
                                echo '- '.$val.'.<br>';
                                if (!empty($val)){
                                        if (!empty($valPrec) && ($valPrec == $val) ){
                                                $distinct = false;
                                                break;
                                        }
                                }
                                $valPrec = $val;
                        }
                        return $distinct;
                }


?>
