<?php
require_once ('bd/dataset.php');
require_once ('sortie/projectPDF.php');
require_once ('sortie/print_utils.php');
function writeContacts($pis, & $out) {
	$out->addSousSection ( 'Contact(s)' );
	foreach ( $pis as $pi ) {
		$out->addText ( printContact ( $pi, true ) );
	}
}
function getProjectsList($dataset) {
	if (isset ( $dataset->projects ) && ! empty ( $dataset->projects )) {
		$list = array ();
		foreach ( $dataset->projects as $proj ) {
			$list [] = printProject ( $proj );
		}
		return $list;
	}
	return null;
}
function writeGeneralInfoSat(& $dataset, & $out) {
	$out->addSection ( 'General information' );
	$out->newLine ();
	$out->addLabelValue ( 'Dataset name', $dataset->dats_title );
	if (isset ( $dataset->dats_doi ) && ! empty ( $dataset->dats_doi ))
		;
	$out->addLabelValue ( 'Dataset DOI', $dataset->dats_doi );
	$out->addLabelValue ( 'Created on', $dataset->dats_pub_date );
	$out->addLabelValue ( 'Version', $dataset->dats_version );
	$out->addLabelList ( 'Useful in the framework of', getProjectsList ( $dataset ) );
	$out->addLabelValue ( 'Purpose', $dataset->dats_purpose );
	$out->addLabelValue ( 'References', $dataset->dats_reference );
	writeContacts ( $dataset->originators, $out );
}
function writeGeneralInfoMod(& $dataset, & $out) {
	$out->addSection ( 'General information' );
	$out->newLine ();
	$out->addLabelValue ( 'Dataset name', $dataset->dats_title );
	if (isset ( $dataset->dats_doi ) && ! empty ( $dataset->dats_doi ))
		;
	$out->addLabelValue ( 'Dataset DOI', $dataset->dats_doi );
	$out->addLabelValue ( 'Created on', $dataset->dats_pub_date );
	$out->addLabelList ( 'Useful in the framework of', getProjectsList ( $dataset ) );
	writeContacts ( $dataset->originators, $out );
}
function writeGeneralInfoInSitu(& $dataset, & $out) {
	$out->addSection ( 'General information' );
	$out->newLine ();
	$out->addLabelValue ( 'Dataset name', $dataset->dats_title );
	if (isset ( $dataset->dats_doi ) && ! empty ( $dataset->dats_doi ))
		;
	$out->addLabelValue ( 'Dataset DOI', $dataset->dats_doi );
	$out->addLabelValue ( 'Created on', $dataset->dats_pub_date );
	
	writeContacts ( $dataset->originators, $out );
	
	if ($dataset->dats_date_begin || $dataset->dats_date_end || $dataset->period->period_name) {
		$out->addSousSection ( 'Period' );
		$out->addLabelValue ( 'Period Name', $dataset->period->period_name );
		$out->addLabelValue ( 'Date begin (yyyy-mm-jj)', $dataset->dats_date_begin, false );
		$out->addLabelValue ( 'Date end (yyyy-mm-jj)', (($dataset->dats_date_end_not_planned) ? 'not planned' : $dataset->dats_date_end), false );
	}
	
	$out->addSousSection ( 'Project(s)', getProjectsList ( $dataset ) );
}
function writeDataDescrInSitu(& $dataset, & $out) {
	if ($dataset->dats_abstract || $dataset->dats_purpose || $dataset->dats_reference) {
		$out->addSection ( 'Data description' );
		$out->addSousSection ( 'Abstract', $dataset->dats_abstract );
		$out->addSousSection ( 'Observing strategy', $dataset->dats_purpose );
		$out->addSousSection ( 'References', $dataset->dats_reference );
	}
}
function writeDataDescrMod(& $dataset, & $out) {
	if ($dataset->dats_abstract || $dataset->dats_purpose || $dataset->dats_reference) {
		$out->addSection ( 'Data description' );
		$out->addSousSection ( 'Model / simulation description', $dataset->dats_abstract );
		$out->addSousSection ( 'Purpose', $dataset->dats_purpose );
		$out->addSousSection ( 'References', $dataset->dats_reference );
	}
}
function writeModelInfo(& $dataset, & $out) {
	$out->addSection ( 'Model information' );
	$out->newLine ();
	$out->addLabelValue ( 'Model', $dataset->sites [1]->place_name );
	$out->addLabelValue ( 'Simulation', $dataset->dats_sensors [0]->sensor->sensor_model );
}
function writeInstrumentInfoSat(& $dataset, & $out) {
	$out->addSection ( 'Instrument' . ((count ( $dataset->dats_sensors ) > 2) ? 's' : '') );
	$out->newLine ();
	for($i = 0; $i < count ( $dataset->dats_sensors ); $i ++) {
		if (count ( $dataset->dats_sensors ) > 1)
			$out->addSousSection ( 'Sensor' );
		$dataset->dats_sensors [$i]->sensor->get_sensor_places ();
		$out->addLabelValue ( 'Satellite', $dataset->dats_sensors [$i]->sensor->sensor_places [0]->place->place_name );
		$out->addLabelValue ( 'Instrument', $dataset->dats_sensors [$i]->sensor->sensor_model );
		$out->addLabelValue ( 'Instrument type', $dataset->dats_sensors [$i]->sensor->gcmd_instrument_keyword->gcmd_sensor_name );
		$out->addLabelValue ( 'Reference', $dataset->dats_sensors [$i]->sensor->sensor_url );
	}
}
function writeInstrumentInfoSite(& $dataset, & $out) {
	for($i = 0; $i < count ( $dataset->dats_sensors ); $i ++) {
		$titre = 'Instrument ' . ($i + 1);
		if (isset ( $dataset->dats_sensors [$i]->sensor->gcmd_instrument_keyword->gcmd_sensor_name ) && ! empty ( $dataset->dats_sensors [$i]->sensor->gcmd_instrument_keyword->gcmd_sensor_name )) {
			$titre .= ' (' . $dataset->dats_sensors [$i]->sensor->gcmd_instrument_keyword->gcmd_sensor_name . ')';
		}
		writeSensor ( $dataset->dats_sensors [$i], $out, $titre );
		
		foreach ( $dataset->dats_sensors [$i]->sensor->sensor_vars as $sensor_var ) {
			if ($sensor_var->flag_param_calcule != 1) {
				$out->addSousSection ( 'Measured parameter: ' . printParamName ( $sensor_var->variable ) );
			} else if ($sensor_var->flag_param_calcule == 1) {
				$out->addSousSection ( 'Derived parameter: ' . printParamName ( $sensor_var->variable ) );
			}
			$out->addLabelValue ( 'Parameter name', $sensor_var->variable->var_name );
			$out->addLabelValue ( 'Parameter keyword', printGcmdScience ( $sensor_var->variable->gcmd ) );
			$out->addLabelValue ( 'Unit', ((isset ( $sensor_var->unit ) && ! empty ( $sensor_var->unit )) ? $sensor_var->unit->toString () : "") );
			$out->addLabelValue ( 'Acquisition methodology and quality', $sensor_var->methode_acq );
			$out->addLabelValue ( 'Sensor precision', $sensor_var->sensor_precision );
			$out->addLabelValue ( 'Date begin (yyyy-mm-jj)', $sensor_var->date_min );
			$out->addLabelValue ( 'Date end (yyyy-mm-jj)', $sensor_var->date_max );
		}
	}
}
function writeInstrumentInfoInSitu(& $dataset, & $out) {
	writeSensor ( $dataset->dats_sensors [0], $out );
}
function writeSensor($ds, & $out, $titre = 'Instrument information') {
	$displaySensorInfo = ($ds->sensor->gcmd_instrument_keyword->gcmd_sensor_name || $ds->sensor->manufacturer->manufacturer_name || $ds->sensor->sensor_model || $ds->sensor->sensor_model || $ds->sensor->sensor_url || $ds->sensor->sensor_calibration);
	$displayResol = $ds->sensor_resol_temp || $ds->sensor_lat_resolution || $ds->sensor_vert_resolution;
	$displayLoc = ($ds->sensor->boundings->west_bounding_coord || $ds->sensor->boundings->north_bounding_coord || $ds->sensor->sensor_elevation);
	
	if ($displayResol || $displaySensorInfo || $displayLoc)
		$out->addSection ( $titre );
	
	if ($displaySensorInfo) {
		$out->addSousSection ( 'Sensor' );
		$out->addLabelValue ( 'Instrument type', $ds->sensor->gcmd_instrument_keyword->gcmd_sensor_name );
		$out->addLabelValue ( 'Manufacturer', $ds->sensor->manufacturer->manufacturer_name );
		$out->addLabelValue ( 'Model', $ds->sensor->sensor_model );
		$out->addLabelValue ( 'Reference', $ds->sensor->sensor_url );
		$out->addLabelValue ( 'Instrument features / Calibration', $ds->sensor->sensor_calibration );
	}
	
	if ($displayResol) {
		$out->addSousSection ( 'Sensor resolution' );
		$out->addLabelValue ( 'Observation frequency', $ds->sensor_resol_temp );
		$out->addLabelValue ( 'Horizontal coverage', $ds->sensor_lat_resolution );
		$out->addLabelValue ( 'Vertical coverage', $ds->sensor_vert_resolution );
	}
	
	if ($displayLoc) {
		$out->addSousSection ( 'Sensor location' );
		$out->addLabelValue ( 'Longitude (°)', $ds->sensor->boundings->west_bounding_coord );
		$out->addLabelValue ( 'Latitude (°)', $ds->sensor->boundings->north_bounding_coord );
		$out->addLabelValue ( 'Height above ground (m)', $ds->sensor->sensor_elevation );
	}
}
function writeParamSatMod(& $dataset, & $out) {
	$out->addSection ( 'Parameter' . ((count ( $dataset->dats_variables ) > 1) ? 's' : '') );
	
	foreach ( $dataset->dats_variables as $dats_var ) {
		$out->addSousSection ( printParamName ( $dats_var->variable ) );
		$out->addLabelValue ( 'Parameter name', $dats_var->variable->var_name );
		$out->addLabelValue ( 'Parameter keyword', printGcmdScience ( $dats_var->variable->gcmd ) );
		$out->addLabelValue ( 'Unit', ((isset ( $dats_var->unit ) && ! empty ( $dats_var->unit )) ? $dats_var->unit->toString () : "") );
		$out->addLabelValue ( 'Acquisition methodology and quality', $dats_var->methode_acq );
		$out->addLabelValue ( 'Vertical level type', $dats_var->level_type );
	}
}
function writeParamInSitu(& $dataset, & $out) {
	
	// $out->addText($dataset->nbVarsReel.' - '.$dataset->nbCalcVarsReel);
	if ($dataset->nbVarsReel >= 1) {
		$titreSection = 'Measured parameter' . (($dataset->nbVarsReel > 1) ? 's' : '');
		$out->addSection ( $titreSection );
		
		foreach ( $dataset->dats_variables as $dats_var ) {
			if ($dats_var->flag_param_calcule != 1) {
				$out->addSousSection ( printParamName ( $dats_var->variable ) );
				$out->addLabelValue ( 'Parameter name', $dats_var->variable->var_name );
				$out->addLabelValue ( 'Parameter keyword', printGcmdScience ( $dats_var->variable->gcmd ) );
				$out->addLabelValue ( 'Unit', ((isset ( $dats_var->unit ) && ! empty ( $dats_var->unit )) ? $dats_var->unit->toString () : "") );
				$out->addLabelValue ( 'Acquisition methodology and quality', $dats_var->methode_acq );
				$out->addLabelValue ( 'Sensor precision', $dats_var->variable->sensor_precision );
				$out->addLabelValue ( 'Date begin (yyyy-mm-jj)', $dats_var->date_min );
				$out->addLabelValue ( 'Date end (yyyy-mm-jj)', $dats_var->date_max );
			}
		}
	}
	
	if ($dataset->nbCalcVarsReel >= 1) {
		$titreSection = 'Derived parameter' . (($dataset->nbCalcVarsReel > 1) ? 's' : '');
		$out->addSection ( $titreSection );
		
		foreach ( $dataset->dats_variables as $dats_var ) {
			if ($dats_var->flag_param_calcule == 1) {
				$out->addSousSection ( printParamName ( $dats_var->variable ) );
				$out->addLabelValue ( 'Parameter name', $dats_var->variable->var_name );
				$out->addLabelValue ( 'Parameter keyword', printGcmdScience ( $dats_var->variable->gcmd ) );
				$out->addLabelValue ( 'Unit', ((isset ( $dats_var->unit ) && ! empty ( $dats_var->unit )) ? $dats_var->unit->toString () : "") );
				$out->addLabelValue ( 'Acquisition methodology and quality', $dats_var->methode_acq );
				$out->addLabelValue ( 'Sensor precision', $dats_var->variable->sensor_precision );
				$out->addLabelValue ( 'Date begin (yyyy-mm-jj)', $dats_var->date_min );
				$out->addLabelValue ( 'Date end (yyyy-mm-jj)', $dats_var->date_max );
			}
		}
	}
}
function writeBoundings($site, & $out) {
	$out->addLabelValue ( 'West bounding coordinate (°)', $site->west_bounding_coord );
	$out->addLabelValue ( 'East bounding coordinate (°)', $site->east_bounding_coord );
	$out->addLabelValue ( 'North bounding coordinate (°)', $site->north_bounding_coord );
	$out->addLabelValue ( 'South bounding coordinate (°)', $site->south_bounding_coord );
	
	$out->addLabelValue ( 'Altitude min', $site->place_elevation_min );
	$out->addLabelValue ( 'Altitude max', $site->place_elevation_max );
}
function writeCoverage(& $dataset, & $out) {
	$out->addSection ( 'Coverage' );
	if ($dataset->dats_date_begin || $dataset->dats_date_end) {
		$out->addSousSection ( 'Temporal coverage' );
		$out->addLabelValue ( 'Date begin (yyyy-mm-jj)', $dataset->dats_date_begin, false );
		$out->addLabelValue ( 'Date end (yyyy-mm-jj)', $dataset->dats_date_end, false );
	}
	if (isset ( $dataset->sites ) && isset ( $dataset->sites [0] ) && ! empty ( $dataset->sites [0] )) {
		$out->addSousSection ( 'Geographic coverage' );
		$out->addLabelValue ( 'Area name', $dataset->sites [0]->place_name );
		writeBoundings ( $dataset->sites [0], $out );
	}
	
	if ($dataset->dats_sensors [0]->sensor_resol_temp || $dataset->dats_sensors [0]->sensor_lat_resolution || $dataset->dats_sensors [0]->sensor_lon_resolution || $dataset->dats_sensors [0]->sensor_vert_resolution) {
		$out->addSousSection ( 'Data resolution' );
		$out->addLabelValue ( 'Temporal resolution', $dataset->dats_sensors [0]->sensor_resol_temp );
		$out->addLabelValue ( 'Latitude resolution', $dataset->dats_sensors [0]->sensor_lat_resolution );
		$out->addLabelValue ( 'Longitude resolution', $dataset->dats_sensors [0]->sensor_lon_resolution );
		$out->addLabelValue ( 'Vertical resolution', $dataset->dats_sensors [0]->sensor_vert_resolution );
	}
	
	if ($dataset->dats_sensors [0]->grid_original || $dataset->dats_sensors [0]->grid_process) {
		$out->addSousSection ( 'Grid type' );
		$out->addLabelValue ( 'Original Grid type', $dataset->dats_sensors [0]->grid_original );
		$out->addLabelValue ( 'Required grid processing', $dataset->dats_sensors [0]->grid_process );
	}
}
function writeLocation($site, & $out) {
	if (isset ( $site->parent_place ) && ! empty ( $site->parent_place )) {
		$out->addLabelValue ( 'Predefined site', printPredefinedSite ( $site->parent_place ) );
		$out->addLabelValue ( 'Plateform type', $site->parent_place->gcmd_plateform_keyword->gcmd_plat_name );
	}
	
	$out->addLabelValue ( 'Location name', $site->place_name );
	$out->addLabelValue ( 'Plateform type', $site->gcmd_plateform_keyword->gcmd_plat_name );
	
	writeBoundings ( $site, $out );
}
function writeSite(& $dataset, & $out) {
	$out->addSection ( 'Site information' );
	$out->newLine ();
	writeLocation ( $dataset->sites [0], $out );
}
function writeGeoInfo(& $dataset, & $out) {
	$out->addSection ( 'Geographic information' );
	$cpt = 1;
	foreach ( $dataset->sites as $site ) {
		$out->addSousSection ( printLocationName ( $site ) );
		writeLocation ( $site, $out );
	}
}
function writeDataUseInfo(& $dataset, & $out) {
	if ($dataset->dats_use_constraints || $dataset->data_policy->data_policy_name || $dataset->database->database_name || $dataset->data_formats || $dataset->required_data_formats) {
		$out->addSection ( 'Data use information' );
		$out->newLine ();
		$out->addLabelValue ( 'Use constraints', $dataset->dats_use_constraints );
		$out->addLabelValue ( 'Data policy', $dataset->data_policy->data_policy_name );
		$out->addLabelValue ( 'Database', $dataset->database->database_name );
		$out->addLabelList ( 'Original data format(s)', $dataset->data_formats, 'data_format_name' );
		$out->addLabelList ( 'Required data format(s)', $dataset->required_data_formats, 'data_format_name' );
	}
}
function fiche2pdf($datsId, $toFile = false) {
	if (isset ( $datsId ) && ! empty ( $datsId )) {
		$dataset = new dataset ();
		$dataset = $dataset->getById ( $datsId );
		
		$pdf = new projectPDF ( $dataset->dats_title );
		
		if (isset ( $dataset ) && ! empty ( $dataset )) {
			if ($dataset->isSatelliteDataset ()) {
				writeGeneralInfoSat ( $dataset, $pdf );
				writeInstrumentInfoSat ( $dataset, $pdf );
				writeParamSatMod ( $dataset, $pdf );
				writeCoverage ( $dataset, $pdf );
				writeDataUseInfo ( $dataset, $pdf );
			} else if ($dataset->isModelDataset ()) {
				writeGeneralInfoMod ( $dataset, $pdf );
				writeModelInfo ( $dataset, $pdf );
				writeDataDescrMod ( $dataset, $pdf );
				writeParamSatMod ( $dataset, $pdf );
				writeCoverage ( $dataset, $pdf );
				writeDataUseInfo ( $dataset, $pdf );
			} else if (count ( $dataset->dats_sensors ) <= 1) {
				writeGeneralInfoInSitu ( $dataset, $pdf );
				writeDataDescrInSitu ( $dataset, $pdf );
				writeInstrumentInfoInSitu ( $dataset, $pdf );
				writeGeoInfo ( $dataset, $pdf );
				writeParamInSitu ( $dataset, $pdf );
				writeDataUseInfo ( $dataset, $pdf );
				if (isset ( $dataset->image ) && ! empty ( $dataset->image )) {
					$pdf->addImage ( 'http://' . $_SERVER ['SERVER_NAME'] . $dataset->image );
				}
			} else {
				writeGeneralInfoInSitu ( $dataset, $pdf );
				writeDataDescrInSitu ( $dataset, $pdf );
				writeSite ( $dataset, $pdf );
				writeInstrumentInfoSite ( $dataset, $pdf );
				writeDataUseInfo ( $dataset, $pdf );
				if (isset ( $dataset->image ) && ! empty ( $dataset->image )) {
					$pdf->addImage ( 'http://' . $_SERVER ['SERVER_NAME'] . $dataset->image );
				}
			}
		}
		
		if ($toFile) {
			$filename = '/tmp/' . uniqid ( 'metadata-' . $dataset->dats_id . '-' ) . '.pdf';
			$pdf->Output ( $filename, 'F' );
			return $filename;
		} else {
			$pdf->Output ( 'metadata-' . $dataset->dats_id . '.pdf', 'I' );
		}
	}
}

?>
