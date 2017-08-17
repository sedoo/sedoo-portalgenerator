<?php

function printVarName($var){
	if (isset($var->var_name) && !empty($var->var_name))
        	return $var->var_name;
        else
               	return $var->gcmd->gcmd_name;
}

function printUnit($unit){
	if (isset($unit->unit_code) && !empty($unit->unit_code))
        	return $unit->unit_code;
        else
               	return $unit->unit_name;
}

function printGcmdScience($gcmd){
	if (isset($gcmd->gcmd_parent)  && !empty($gcmd->gcmd_parent)){
		return printGcmdScience($gcmd->gcmd_parent).' > '.$gcmd->gcmd_name;
	}else
		return $gcmd->gcmd_name;
}

function printGcmdInstrument($gcmd){
	if (isset($gcmd->gcmd_parent)  && !empty($gcmd->gcmd_parent)){
		return printGcmdInstrument($gcmd->gcmd_parent).' > '.$gcmd->gcmd_sensor_name;
	}else
		return $gcmd->gcmd_sensor_name;
}

function printGcmdLocation($gcmd){
	if (isset($gcmd->gcmd_parent)  && !empty($gcmd->gcmd_parent)){
		return printGcmdLocation($gcmd->gcmd_parent).' > '.$gcmd->gcmd_loc_name;
	}else
		return $gcmd->gcmd_loc_name;
}

function printGcmdPlateform($gcmd){
	if (isset($gcmd->gcmd_parent)  && !empty($gcmd->gcmd_parent)){
		return printGcmdPlateform($gcmd->gcmd_parent).' > '.$gcmd->gcmd_plat_name;
	}else
		return $gcmd->gcmd_plat_name;
}

function printPredefinedSite($site){
	if (isset($site->parent_place)  && !empty($site->parent_place)){
		return printPredefinedSite($site->parent_place).' > '.$site->place_name;
	}else
		return $site->place_name;
}

function printParamName($var){
	if (isset($var->var_name) && !empty($var->var_name))
		return ucfirst($var->var_name);
	else
		return $var->gcmd->gcmd_name;
}

function printLocationName($site){
	if (isset($site->place_name))
		return $site->place_name;
	else if (isset($site->parent_place) && !empty($site->parent_place))
		return $site->parent_place->place_name;
	
}

function printContact($pi,$withMail = false){
	$str = ucwords(strtolower($pi->pers_name)).' - '.$pi->organism->getName();
	if ($withMail && isset($pi->pers_email_1) && !empty($pi->pers_email_1))
		$str .= ' - '.$pi->pers_email_1;
	$str .= ' ('.$pi->contact_type->contact_type_name.')';
	return $str;
}

function printProject($proj){
	return (($proj->parent_project)?$proj->parent_project->project_name.' > ':'').$proj->project_name;
}


?>
