<?php
require_once ("bd/dataset.php");
require_once ("xml/xmlTemplate.php");
require_once 'utils/SphinxAutocompleteAndcorrection/sphinx_keyword_insertion.php';
function createOriginatorXml($originator_xml, $originator) {
	$originator_xml->addChild ( 'pers_name', $originator->pers_name );
	$originator_xml->addChild ( 'pers_email1', $originator->pers_email_1 );
	if (isset ( $originator->pers_email2 ) && ! empty ( $originator->pers_email_2 ))
		$originator_xml->addChild ( 'pers_email2', $originator->pers_email_2 );
	if (isset ( $originator->organism ) && ! empty ( $originator->organism )) {
		$org_xml = $originator_xml->addChild ( 'pers_organism' );
		$org_xml = createOrganismXml ( $org_xml, $originator->organism );
	}
	return $originator_xml;
}
function createOrganismXml($org_xml, $organisme) {
	$org_xml->org_fname = $organisme->org_fname;
	$org_xml->org_sname = $organisme->org_sname;
	if (isset ( $organisme->org_url ) && ! empty ( $organisme->org_url )) {
		$org_xml->org_url = $organisme->org_url;
	}
	return $org_xml;
}
function createBoundingsXml($bound_xml, $boundings) {
	$bound_xml->addChild ( 'west_bounding_coord', $boundings->west_bounding_coord );
	$bound_xml->addChild ( 'east_bounding_coord', $boundings->east_bounding_coord );
	$bound_xml->addChild ( 'north_bounding_coord', $boundings->north_bounding_coord );
	$bound_xml->addChild ( 'south_bounding_coord', $boundings->south_bounding_coord );
	return $bound_xml;
}
function createSensorXml($sensor_xml, $dats_sens) {
	$sensor = new sensor ();
	$sensor = $sensor->getById ( $dats_sens->sensor_id );
	if (isset ( $sensor->manufacturer ) && ! empty ( $sensor->manufacturer )) {
		$sensor_xml->sensor_manufacturer = $sensor->manufacturer->manufacturer_name;
	}
	if (isset ( $sensor->gcmd_instrument_keyword ) && ! empty ( $sensor->gcmd_instrument_keyword )) {
		$gcmd_xml = $sensor_xml->addChild ( 'gcmd_sensor' );
		$gcmd_xml->addChild ( 'gcmd_sensor_name', $sensor->gcmd_instrument_keyword->gcmd_sensor_name );
	}
	if (isset ( $sensor->boundings ) && ! empty ( $sensor->boundings )) {
		$bound_xml = $sensor_xml->addChild ( 'boundings' );
		$bound_xml = createBoundingsXml ( $bound_xml, $sensor->boundings );
	}
	if (isset ( $sensor->sensor_model ) && ! empty ( $sensor->sensor_model )) {
		$sensor_xml->sensor_model = $sensor->sensor_model;
	}
	if (isset ( $sensor->sensor_calibration ) && ! empty ( $sensor->sensor_calibration )) {
		$sensor_xml->sensor_calibration = $sensor->sensor_calibration;
	}
	if (isset ( $sensor->sensor_date_begin ))
		$sensor_xml->addChild ( 'sensor_date_begin', $sensor->sensor_date_begin );
	if (isset ( $sensor->sensor_date_end ))
		$sensor_xml->addChild ( 'sensor_date_end', $sensor->sensor_date_end );
	if (isset ( $dats_sens->sensor_resol_temp ) && ! empty ( $dats_sens->sensor_resom_temp ))
		$sensor_xml->addChild ( 'sensor_resol_temp', $dats_sens->sensor_resol_temp );
	if (isset ( $dats_sens->sensor_lat_resolution ) && ! empty ( $dats_sens->sensor_lat_resolution ))
		$sensor_xml->addChild ( 'sensor_lat_resol', $dats_sens->sensor_lat_resolution );
	if (isset ( $dats_sens->sensor_lon_resolution ) && ! empty ( $dats_sens->sensor_lon_resolution ))
		$sensor_xml->addChild ( 'sensor_lon_resol', $dats_sens->sensor_lon_resolution );
	if (isset ( $dats_sens->sensor_vert_resolution ) && ! empty ( $dats_sens->sensor_vert_resolution ))
		$sensor_xml->addChild ( 'sensor_vert_resol', $dats_sens->sensor_vert_resolution );
	if (isset ( $sensor->sensor_url ) && ! empty ( $sensor->sensor_url )) {
		$sensor_xml->sensor_url = $sensor->sensor_url;
	}
	if (isset ( $sensor->sensor_elevation ))
		$sensor_xml->addChild ( 'sensor_height_above_ground', $sensor->sensor_elevation );
	if (isset ( $dats_sens->nb_sensor ))
		$sensor_xml->addChild ( 'sensor_nb', $dats_sens->nb_sensor );
	if (isset ( $dats_sens->grid_original ) && ! empty ( $dats_sens->grid_original ))
		$sensor_xml->addChild ( 'grid_original', $dats_sens->grid_original );
	if (isset ( $dats_sens->grid_process ) && ! empty ( $dats_sens->grid_process ))
		$sensor_xml->addChild ( 'grid_process', $dats_sens->grid_process );
	return $sensor_xml;
}
function createSiteXml($site_xml, $site) {
	if (isset ( $site->parent_place ) && ! empty ( $site->parent_place )) {
		$parent_xml = $site_xml->addChild ( 'parent_place' );
		$parent_xml = createSiteXml ( $parent_xml, $site->parent_place );
	}
	if (isset ( $site->boundings ) && ! empty ( $site->boundings )) {
		$bound_xml = $site_xml->addChild ( 'place_boundings' );
		$bound_xml = createBoundingsXml ( $bound_xml, $site->boundings );
	}
	if (isset ( $site->gcmd_plateform_keyword ) && ! empty ( $site->gcmd_plateforme_keyword )) {
		$site_xml->addChild ( 'gcmd_plateform_keyword', $site->gcmd_plateform_keyword->gcmd_plat_name );
	}
	if (isset ( $site->place_name ) && ! empty ( $site->place_name )) {
		$site_xml->place_name = $site->place_name;
	}
	if (isset ( $site->place_elevation_min ))
		$site_xml->addChild ( 'place_elevation_min', $site->place_elevation_min );
	if (isset ( $site->place_elevation_max ))
		$site_xml->addChild ( 'place_elevation_max', $site->place_elevation_max );
}
function createGcmdScienceXml($gcmd_xml, $gcmd) {
	$gcmd_xml->addChild ( 'gcmd_science_name', $gcmd->gcmd_name );
	if (isset ( $gcmd->gcmd_parent ) && ! empty ( $gcmd->gcmd_parent )) {
		$parent_xml = $gcmd_xml->addChild ( 'gcmd_parent' );
		$parent_xml = createGcmdScienceXml ( $parent_xml, $gcmd->gcmd_parent );
	}
	if (isset ( $gcmd->gcmd_level ))
		$gcmd_xml->addChild ( 'gcmd_level', $gcmd->gcmd_level );
	return $gcmd_xml;
}
function createVariableXml($var_xml, $dats_var) {
	if (isset ( $dats_var->variable ) && ! empty ( $dats_var->variable )) {
		if ($dats_var->variable->gcmd_id != 0) {
			$gcmd_xml = $var_xml->addChild ( 'gcmd_science_keyword' );
			$gcmd_xml = createGcmdScienceXml ( $gcmd_xml, $dats_var->variable->gcmd );
		}
		if (isset ( $dats_var->variable->var_name ) && ! empty ( $dats_var->variable->var_name ))
			$var_xml->addChild ( 'var_name', $dats_var->variable->var_name );
		if (isset ( $dats_var->unit ) && ! empty ( $dats_var->unit ))
			$var_xml->addChild ( 'var_unit', $dats_var->unit->unit_name );
		if (isset ( $dats_var->min_value ))
			$var_xml->addChild ( 'var_min_possible_value', $dats_var->min_value );
		if (isset ( $dats_var->max_value ))
			$var_xml->addChild ( 'var_max_possible_value', $dats_var->max_value );
		if (isset ( $dats_var->vertical_level_type ) && ! empty ( $dats_var->vertical_level_type ))
			$var_xml->addChild ( 'vertical_level_type', $dats_var->vertical_level_type->vert_level_type_name );
		if (isset ( $dats_var->methode_acq ) && ! empty ( $dats_var->methode_acq )) {
			$var_xml->var_acquisition_method = $dats_var->methode_acq;
		}
		if ($dats_var->flag_param_calcule)
			$var_xml->addChild ( 'flag_param_calc', true );
		else
			$var_xml->addChild ( 'flag_param_calc', false );
		if (isset ( $dats_var->date_min ))
			$var_xml->addChild ( 'var_date_deb', $dats_var->date_min );
		if (isset ( $dats_var->date_max ))
			$var_xml->addChild ( 'var_date_end', $dats_var->date_max );
	}
	return $var_xml;
}
function createProjectXml($proj_xml, $proj) {
	if (isset ( $proj->pro_project_id ) && ! empty ( $proj->pro_project_id )) {
		$parent_pro = new project ();
		$parent_pro = $parent_pro->getById ( $proj->pro_project_id );
		$parent_xml = $proj_xml->addChild ( "project_parent" );
		createProjectXml ( $parent_xml, $parent_pro );
	}
	$proj_xml->addChild ( "project_name", $proj->project_name );
}
function createXml($dts, $xmlstr) {
	$xml = simplexml_load_string ( $xmlstr );
	$xml->title = $dts->dats_title;
	$xml->addChild ( 'dats_pub_date', $dts->dats_pub_date );
	if (isset ( $dts->dats_version ) && ! empty ( $dts->dats_version ))
		$xml->addChild ( 'dats_version', $dts->dats_version );
	if (isset ( $dts->dats_process_level ) && ! empty ( $dts->dats_process_level ))
		$xml->addChild ( 'dats_process_level', $dts->dats_process_level );
	if (isset ( $dts->dats_other_cit ) && ! empty ( $dts->dats_other_cit )) {
		$xml->dats_other_cit = $dts->dats_other_cit;
	}
	if (isset ( $dts->dats_abstract ) && ! empty ( $dts->dats_abstract )) {
		$xml->dats_abstract = $dts->dats_abstract;
	}
	if (isset ( $dts->dats_purpose ) && ! empty ( $dts->dats_purpose )) {
		$xml->dats_purpose = $dts->dats_purpose;
	}
	if (isset ( $dts->dats_elevation_min ) && ! empty ( $dts->dats_elevation_min ))
		$xml->addChild ( 'dats_elevation_min', $dts->dats_elevation_min );
	if (isset ( $dts->dats_elevation_max ) && ! empty ( $dts->dats_elevation_max ))
		$xml->addChild ( 'dats_elevation_max', $dts->dats_elevation_max );
	if (isset ( $dts->dats_date_begin ) && ! empty ( $dts->dats_date_begin ))
		$xml->addChild ( 'dats_date_begin', $dts->dats_date_begin );
	if (isset ( $dts->dats_date_end ) && ! empty ( $dts->dats_date_end ))
		$xml->addChild ( 'dats_date_end', $dts->dats_date_end );
	if (isset ( $dts->dats_use_constraints ) && ! empty ( $dts->dats_use_constraints )) {
		$xml->dats_use_constraints = $dts->dats_use_constraints;
	}
	if (isset ( $dts->dats_access_constraints ) && ! empty ( $dts->dats_access_constraints )) {
		$xml->dats_access_constraints = $dts->dats_access_constraints;
	}
	if (isset ( $dts->dats_reference ) && ! empty ( $dts->dats_reference )) {
		$xml->dats_reference = $dts->dats_reference;
	}
	if (isset ( $dts->dats_quality ) && ! empty ( $dts->dats_quality )) {
		$xml->dats_quality = $dts->dats_quality;
	}
	if ($dts->dats_date_end_not_planned)
		$xml->addChild ( 'dats_date_end_not_planned', 'true' );
	else
		$xml->addChild ( 'dats_date_end_not_planned', 'false' );
	if (isset ( $dts->dats_sensors ) && ! empty ( $dts->dats_sensors )) {
		foreach ( $dts->dats_sensors as $dats_sens ) {
			$sensor_xml = $xml->addChild ( "dats_sensors" );
			$sensor_xml = createSensorXml ( $sensor_xml, $dats_sens );
		}
	}
	if (isset ( $dts->sites ) && ! empty ( $dts->sites )) {
		foreach ( $dts->sites as $site ) {
			$site_xml = $xml->addChild ( "dats_places" );
			$site_xml = createSiteXml ( $site_xml, $site );
		}
	}
	if (isset ( $dts->dats_variables ) && ! empty ( $dts->dats_variables )) {
		foreach ( $dts->dats_variables as $dats_var ) {
			$variable_xml = $xml->addChild ( "dats_vars" );
			$variable_xml = createVariableXml ( $variable_xml, $dats_var );
		}
	}
	if (isset ( $dts->originators ) && ! empty ( $dts->originators )) {
		foreach ( $dts->originators as $originator ) {
			$originator_xml = $xml->addChild ( "dats_originators" );
			$originator_xml = createOriginatorXml ( $originator_xml, $originator );
		}
	}
	if (isset ( $dts->organism ) && ! empty ( $dts->organism )) {
		$organism_xml = $xml->addChild ( "dats_organism" );
		$organism_xml = createOrganismXml ( $organism_xml, $dts->organism );
	}
	if (isset ( $dts->status_progress ) && $dts->status_progress_id > 0) {
		$xml->addChild ( "dats_progress_status", $dts->status_progress->status_progress_name );
	}
	if (isset ( $dts->status_final ) && $dts->status_final_id > 0) {
		$xml->addChild ( "dats_final_status", $dts->status_final->status_final_name );
	}
	if (isset ( $dts->projects )) {
		foreach ( $dts->projects as $proj ) {
			if (isset ( $proj )) {
				$proj_xml = $xml->addChild ( "dats_project" );
				createProjectXml ( $proj_xml, $proj );
			}
		}
	}
	if (isset ( $dts->database )) {
		$db_xml = $xml->addchild ( "database" );
		$db_xml->addChild ( "database_name", $dts->database->database_name );
	}
	if (isset ( $dts->period )) {
		$period_xml = $xml->addchild ( "period" );
		$period_xml->addChild ( "period_name", $dts->period->period_name );
	}
	
	return $xml;
}
function dataset2xml($dataset) {
	// créer un flux xml pour dataset
	global $xmlstr;
	$xml = createXml ( $dataset, $xmlstr );
	// enregistrer le flux xml dans la table dataset
	$tagXml = str_replace ( "'", "\'", $xml->asXml () );
	$tagXml = preg_replace ( '/(<\/[^>]+?>)(<[^>\/][^>]*?>)/', '$1 $2', $tagXml );
	$dataset->insertXml ( strip_tags ( $tagXml ) );
	insert_keywords_docs_suggest ( strip_tags ( $tagXml ) );
	update_sphinx_indexes ();
}
?>
