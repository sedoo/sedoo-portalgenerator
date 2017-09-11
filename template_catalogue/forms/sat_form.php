<?php

require_once ("forms/base_form.php");
class sat_form extends base_form {
	function createForm() {
		$this->createFormBase ();
		$this->addElement ( 'reset', 'reset', 'Reset' );
		$tab ['seeker_0'] = '1';
		$this->setDefaults ( $tab );
		$this->addElement ( 'submit', 'bouton_add_pi', 'Add a contact', array (
				'onclick' => "document.getElementById('frmsat').action += '#a_contact'" 
		) );
		for($i = 0; $i < $this->dataset->nbSites - 1; $i ++) {
			$this->createFormSat ( $i );
		}
		$this->addElement ( 'submit', 'bouton_add_instru', 'Add an instrument', array (
				'onclick' => "document.getElementById('frmsat').action += '#a_instru'" 
		) );
		$this->addElement ( 'submit', 'bouton_add_projet', 'Add a project', array (
				'onclick' => "document.getElementById('frmsat').action += '#a_general'" 
		) );
		$this->addElement ( 'submit', 'bouton_add_variable', 'Add a parameter', array (
				'onclick' => "document.getElementById('frmsat').action += '#a_param'" 
		) );
		$this->createFormGrid ();
		$this->createFormResolution ();
		$this->createFormGeoCoverage ();
		
		// Required format
		$dformat = new data_format ();
		$dformat_select = $dformat->chargeFormDestFormat ( $this, 'required_data_format', 'Distribution data format', 'NetCDF' );
		$this->addElement ( $dformat_select );
		
		$this->getElement ( 'organism_0' )->setLabel ( "Organization short name" );
		$this->getElement ( 'project_0' )->setLabel ( "Useful in the framework of" );
		$this->getElement ( 'methode_acq_0' )->setLabel ( "Acquisition methodology and processing" );
		$this->getElement ( 'dats_purpose' )->setLabel ( "Purpose" );
		$this->getElement ( 'database' )->setLabel ( "Data center" );
		$this->getElement ( 'new_db_url' )->setLabel ( "Data center url" );
		$this->getElement ( 'dats_use_constraints' )->setLabel ( "Access and use constraints" );
		
		$place = new place ();
		$categ_select = $place->chargeFormSatCategs ( $this, 'sat_categ', 'Data type' );
		$this->addElement ( $categ_select );
		
		$this->getElement ( 'sensor_resol_temp' )->setLabel ( 'Temporal (yyyy-mm-dd hh:mm:ss)' );
		$this->getElement ( 'place_alt_min_0' )->setLabel ( "Altitude min" );
		$this->getElement ( 'place_alt_max_0' )->setLabel ( "Altitude max" );
		$this->getElement ( 'data_format_0' )->setLabel ( "Original data format" );
	}
	function createFormSat($i) {
		$this->addElement ( 'text', 'new_satellite_' . $i, 'Satellite name' );
		$this->applyFilter ( 'new_satellite_' . $i, 'trim' );
		$place = new place ();
		$sat_select = $place->chargeFormSat ( $this, $i );
		$this->addElement ( $sat_select );
		$this->createFormSensorKeyword ( '_' . $i );
		$this->addElement ( 'text', 'sensor_url_' . $i, 'Reference (URL or paper)', array (
				'cols' => 60,
				'rows' => 1 
		) );
		$this->applyFilter ( 'sensor_url_' . $i, 'trim' );
		$this->createFormInstru ( $i );
	}
	function createFormInstru($i) {
		$array = array ();
		$array [0] = "";
		
		if (isset ( $this->dataset->sats [$i] ) && ! empty ( $this->dataset->sats [$i] ) && $this->dataset->sats [$i]->place_id > 0) {
			$satId = $this->dataset->sats [$i]->place_id;
		} else {
			$satId = $this->exportValue ( 'satellite_' . $i );
		}
		
		if (isset ( $satId ) && ! empty ( $satId )) {
			if ($satId > 0) {
				$sensor = new sensor ();
				$listeInstrus = $sensor->getByPlace ( $satId );
				foreach ( $listeInstrus as $instru ) {
					$array [$instru->sensor_id] = $instru->sensor_model;
				}
			}
		}
		$boxesNames = "['new_instrument_" . $i . "','sensor_url_" . $i . "','sensor_gcmd_" . $i . "']";
		$columnsNames = "['sensor_model','sensor_url','gcmd_sensor_id']";
		$this->addElement ( 'select', 'instrument_' . $i, "Instrument", $array, array (
				'onchange' => "fillBoxes('instrument_" . $i . "'," . $boxesNames . ",'sensor'," . $columnsNames . ");" 
		) );
		$this->addElement ( 'text', 'new_instrument_' . $i, 'Instrument name' );
		$this->applyFilter ( 'new_instrument_' . $i, 'trim' );
	}
	function addSat() {
		$this->createFormSat ( $this->dataset->nbSites - 1 );
	}
	function addProjet() {
		$this->createFormProject ( $this->dataset->nbProj - 1 );
	}
	function initForm() {
		$this->initFormBase ();
		// Coverage
		$this->initFormGeoCoverage ();
		
		if (isset ( $this->dataset->dataType ) && ! empty ( $this->dataset->dataType )) {
			$this->getElement ( 'sat_categ' )->setSelected ( $this->dataset->dataType->place_id );
		}
				
		for($i = 0; $i < $this->dataset->nbSites; $i ++) {
			
			if (isset ( $this->dataset->sats ) && ! empty ( $this->dataset->sats )) {
				// Satellite
				if (isset ( $this->dataset->sats [$i] ) && ! empty ( $this->dataset->sats [$i] )) {
					
					$this->getElement ( 'satellite_' . $i )->setSelected ( $this->dataset->sats [$i]->place_id );
					$this->getElement ( 'new_satellite_' . $i )->setValue ( $this->dataset->sats [$i]->place_name );
				}
			}
			if (isset ( $this->dataset->dats_sensors [$i]->sensor->gcmd_instrument_keyword ) && ! empty ( $this->dataset->dats_sensors [$i]->sensor->gcmd_instrument_keyword )) {
				$this->getElement ( 'sensor_gcmd_' . $i )->setSelected ( $this->dataset->dats_sensors [$i]->sensor->gcmd_instrument_keyword->gcmd_sensor_id );
			}
			// Instrument
			if (isset ( $this->dataset->dats_sensors [$i]->sensor ) && ! empty ( $this->dataset->dats_sensors [$i]->sensor )) {
				$this->getElement ( 'instrument_' . $i )->setSelected ( $this->dataset->dats_sensors [$i]->sensor->sensor_id );
				$this->getElement ( 'new_instrument_' . $i )->setValue ( $this->dataset->dats_sensors [$i]->sensor->sensor_model );
				$this->getElement ( 'sensor_url_' . $i )->setValue ( $this->dataset->dats_sensors [$i]->sensor->sensor_url );
			}
		}
		$this->initFormResolution ();
		$this->initFormGrid ();
		// Parameter
		for($i = 0; $i < $this->dataset->nbVars; $i ++) {
			$this->initFormVariable ( $i, $i );
		}
		// REQ DATA FORMATS
		if (isset ( $this->dataset->required_data_formats [0] ) && ! empty ( $this->dataset->required_data_formats [0] )) {
			$this->getElement ( 'required_data_format' )->setSelected ( $this->dataset->required_data_formats [0]->data_format_id );
		} else
			$this->getElement ( 'required_data_format' )->setSelected ( 0 );
	}
	function saveForm() {
		$this->saveFormBase ();
		// Coverage
		$this->saveFormGeoCoverage ();
				
		// Data type
		$this->dataset->dataType = new place ();
		$this->dataset->dataType = $this->dataset->dataType->getById ( $this->exportValue ( 'sat_categ' ) );
		
		// Sat
		$this->dataset->sats = array ();
		$this->dataset->dats_sensors = array ();
		for($i = 0; $i < $this->dataset->nbSites; $i ++) {
			$this->dataset->sats [$i] = new place ();
			$this->dataset->sats [$i]->place_id = $this->exportValue ( 'satellite_' . $i );
			$this->dataset->sats [$i]->place_name = $this->exportValue ( 'new_satellite_' . $i );
			
			$this->dataset->sats [$i]->gcmd_plateform_keyword = new gcmd_plateform_keyword ();
			$this->dataset->sats [$i]->gcmd_plateform_keyword = $this->dataset->sats [$i]->gcmd_plateform_keyword->getByName ( "Satellites" );
			$this->dataset->sats [$i]->gcmd_plat_id = & $this->dataset->sats [$i]->gcmd_plateform_keyword->gcmd_plat_id;
			
			$this->dataset->sats [$i]->bound_id = - 1;
			if (empty ( $this->dataset->sats [$i]->place_name )) {
				$this->dataset->sats [$i]->place_id = - 1;
			}
			// Instrument
			$this->dataset->dats_sensors [$i] = new dats_sensor ();
			$this->dataset->dats_sensors [$i]->sensor = new sensor ();
			$this->dataset->dats_sensors [$i]->sensor->sensor_id = $this->exportValue ( 'instrument_' . $i );
			$this->dataset->dats_sensors [$i]->sensor->sensor_model = $this->exportValue ( 'new_instrument_' . $i );
			$this->dataset->dats_sensors [$i]->sensor->gcmd_sensor_id = $this->exportValue ( 'sensor_gcmd_' . $i );
			if ($this->dataset->dats_sensors [$i]->sensor->gcmd_sensor_id != 0) {
				$this->dataset->dats_sensors [$i]->sensor->gcmd_instrument_keyword = new gcmd_instrument_keyword ();
				$this->dataset->dats_sensors [$i]->sensor->gcmd_instrument_keyword = $this->dataset->dats_sensors [$i]->sensor->gcmd_instrument_keyword->getById ( $this->dataset->dats_sensors [$i]->sensor->gcmd_sensor_id );
			}
			$this->dataset->dats_sensors [$i]->sensor->sensor_url = $this->exportValue ( 'sensor_url_' . $i );
			$this->dataset->dats_sensors [$i]->sensor->manufacturer_id = - 1;
			$this->dataset->dats_sensors [$i]->sensor->bound_id = - 1;
			$this->dataset->dats_sensors [$i]->grid_original = $this->exportValue ( 'grid_original' );
			$this->dataset->dats_sensors [$i]->grid_process = $this->exportValue ( 'grid_process' );
			$this->dataset->dats_sensors [$i]->sensor_resol_temp = $this->exportValue ( 'sensor_resol_temp' );
			$this->dataset->dats_sensors [$i]->sensor_vert_resolution = $this->exportValue ( 'sensor_vert_resolution' );
			$this->dataset->dats_sensors [$i]->sensor_lat_resolution = $this->exportValue ( 'sensor_lat_resolution' );
			$this->dataset->dats_sensors [$i]->sensor_lon_resolution = $this->exportValue ( 'sensor_lon_resolution' );
		}
		$this->saveFormVariables ( $this->dataset->nbVars );
		// REQ DATA_FORMAT
		$reqDataFormatId = $this->exportValue ( 'required_data_format' );
		$this->dataset->required_data_formats = array ();
		if ($reqDataFormatId > 0) {
			$this->dataset->required_data_formats [0] = new data_format ();
			$this->dataset->required_data_formats [0]->data_format_id = $reqDataFormatId;
		}
	}
	function addValidationRules() {
		$this->registerRule ( 'validDate', 'function', 'validDate' );
		$this->registerRule ( 'validPeriod', 'function', 'validPeriod' );
		$this->registerRule ( 'existe', 'function', 'existe' );
		$this->registerRule ( 'number_range', 'function', 'number_range' );
		$this->registerRule ( 'validInterval', 'function', 'validInterval' );
		$this->registerRule ( 'couple_not_null', 'function', 'couple_not_null' );
		$this->registerRule ( 'validParam', 'function', 'validParam' );
		$this->registerRule ( 'validUnit_existe', 'function', 'validUnit_existe' );
		$this->registerRule ( 'validUnit_required', 'function', 'validUnit_required' );
		$this->addRule ( 'dats_title', 'Data description: Metadata informative title is required', 'required' );
		$this->addRule ( 'dats_title', 'Data description: Dataset name exceeds the maximum length allowed (100 characters)', 'maxlength', 100 );
		$this->addRule ( 'dats_date_begin', 'Temporal Coverage: Date begin is not a date', 'validDate' );
		$this->addRule ( 'dats_date_end', 'Temporal Coverage: Date end is not a date', 'validDate' );
		$this->addRule ( array (
				'dats_date_begin',
				'dats_date_end' 
		), 'Temporal Coverage: Date end must be after date begin', 'validPeriod' );
		if ($this->dataset->dats_id == 0) {
			$this->addRule ( 'dats_title', 'Data description: A dataset with the same title exists in the database', 'existe', array (
					'dataset',
					'dats_title' 
			) );
		}
		if (isset ( $this->dataset->data_policy ) && ! empty ( $this->dataset->data_policy ) && $this->dataset->data_policy->data_policy_id > 0) {
			$this->getElement ( 'new_data_policy' )->setAttribute ( 'onfocus', 'blur()' );
		} else {
		}
		$this->addRule ( 'new_data_policy', 'Data use information: Data policy exceeds the maximum length allowed (100 characters)', 'maxlength', 100 );
		$attrs = array ();
		if (isset ( $this->dataset->database ) && ! empty ( $this->dataset->database ) && $this->dataset->database->database_id > 0) {
			$this->disableElement ( 'new_database' );
			$this->disableElement ( 'new_db_url' );
		} else {
		}
		$this->addRule ( 'new_database', 'Data use information: Database name exceeds the maximum length allowed (250 characters)', 'maxlength', 250 );
		$this->addRule ( 'new_db_url', 'Data use information: Database url exceeds the maximum length allowed (250 characters)', 'maxlength', 250 );
		// Formats
		for($i = 0; $i < $this->dataset->nbFormats; $i ++) {
			$this->addRule ( 'data_format_' . $i, 'Data use information: Format name ' . ($i + 1) . ' exceeds the maximum length allowed (100 characters)', 'maxlength', 100 );
			if (isset ( $this->dataset->data_formats [$i] ) && ! empty ( $this->dataset->data_formats [$i] ) && $this->dataset->data_formats [$i]->data_format_id > 0) {
				$this->disableElement ( 'new_data_format_' . $i );
			} else {
			}
		}
		
		// Contacts
		$this->addRule ( 'pi_0', 'Contact 1 is required', 'couple_not_null', array (
				$this,
				'pi_name_0' 
		) );
		$this->addRule ( 'organism_0', 'Contact 1: organism short name is required', 'couple_not_null', array (
				$this,
				'org_sname_0' 
		) );
		$this->addRule ( 'email1_0', 'Contact 1: email1 is required', 'required' );
		for($i = 0; $i < $this->dataset->nbPis; $i ++) {
			$this->addRule ( 'pi_name_' . $i, 'Contact ' . ($i + 1) . ': Name exceeds the maximum length allowed (250 characters)', 'maxlength', 250 );
			$this->addRule ( 'email1_' . $i, 'Contact ' . ($i + 1) . ': email1 is incorrect', 'email' );
			$this->addRule ( 'email2_' . $i, 'Contact ' . ($i + 1) . ': email2 is incorrect', 'email' );
			$this->addRule ( 'org_fname_' . $i, 'Contact ' . ($i + 1) . ': Organization full name exceeds the maximum length allowed (250 characters)', 'maxlength', 250 );
			$this->addRule ( 'org_sname_' . $i, 'Contact ' . ($i + 1) . ': Organization short name exceeds the maximum length allowed (50 characters)', 'maxlength', 50 );
			$this->addRule ( 'org_url_' . $i, 'Contact ' . ($i + 1) . ': Organization url exceeds the maximum length allowed (250 characters)', 'maxlength', 250 );
			$this->addRule ( 'email1_' . $i, 'Contact ' . ($i + 1) . ': email1 exceeds the maximum length allowed (250 characters)', 'maxlength', 250 );
			$this->addRule ( 'email2_' . $i, 'Contact ' . ($i + 1) . ': email2 exceeds the maximum length allowed (250 characters)', 'maxlength', 250 );
			if (isset ( $this->dataset->originators [$i] ) && ! empty ( $this->dataset->originators [$i] ) && $this->dataset->originators [$i]->pers_id > 0) {
				$this->disableElement ( 'pi_name_' . $i );
				$this->disableElement ( 'email1_' . $i );
				$this->disableElement ( 'email2_' . $i );
				$this->disableElement ( 'organism_' . $i );
			} else {
			}
			
			if (isset ( $this->dataset->originators [$i]->organism ) && ! empty ( $this->dataset->originators [$i]->organism ) && $this->dataset->originators [$i]->organism->org_id > 0) {
				$this->disableElement ( 'org_sname_' . $i );
				$this->disableElement ( 'org_fname_' . $i );
				$this->disableElement ( 'org_url_' . $i );
			}
			if ($i != 0) {
				$this->addRule ( 'pi_name_' . $i, 'Contact ' . ($i + 1) . ': email1 is required', 'contact_email_required', array (
						$this,
						$i 
				) );
				$this->addRule ( 'pi_name_' . $i, 'Contact ' . ($i + 1) . ': organization is required', 'contact_organism_required', array (
						$this,
						$i 
				) );
			}
		}
		
		for($i = 0; $i < $this->dataset->nbSites; $i ++) {
			if ($i == 0) {
				$this->addRule ( 'satellite_' . $i, 'Instrument: satellite is required', 'couple_not_null', array (
						$this,
						'new_satellite_' . $i 
				) );
				$this->addRule ( 'instrument_' . $i, 'Instrument: instrument is required', 'couple_not_null', array (
						$this,
						'new_instrument_' . $i 
				) );
			}
			// Sat
			if (isset ( $this->dataset->sats [$i] ) && ! empty ( $this->dataset->sats [$i] ) && $this->dataset->sats [$i]->place_id > 0) {
				$this->disableElement ( 'new_satellite_' . $i );
			} else {
				$this->addRule ( 'new_satellite_' . $i, 'Instrument: This satellite name is already present in the database. Select it in the drop-down list or chose another name.', 'existe', array (
						'place',
						'place_name' 
				) );
			}
			// Instru
			if (isset ( $this->dataset->dats_sensors [$i]->sensor ) && ! empty ( $this->dataset->dats_sensors [$i]->sensor ) && $this->dataset->dats_sensors [$i]->sensor->sensor_id > 0) {
				$this->disableElement ( 'new_instrument_' . $i );
				$this->disableElement ( 'sensor_gcmd_' . $i );
				$this->disableElement ( 'sensor_url_' . $i );
			}
		}
		// COVERAGE
		$this->addValidationRulesResolution ( 'Coverage' );
		$this->addRule ( 'sensor_resol_temp', 'Coverage: temporal resolution is incorrect', 'regex', "/^[0-9]{4}[-][0-9]{2}[-][0-9]{2} [0-9]{2}[:][0-9]{2}[:][0-9]{2}$/" );
		$this->addValidationRulesGeoCoverage ();
		// PARAMETER
		for($i = 0; $i < $this->dataset->nbVars; $i ++) {
			$this->addValidationRulesVariable ( $i, $i, 'Parameter ' . ($i + 1) );
		}
	}
	function displayErrorsInstrument($i) {
		$this->displayErrors ( array (
				'satellite_' . $i,
				'instrument_' . $i,
				'new_satellite_' . $i,
				'new_instrument_' . $i,
				'sensor_url_' . $i 
		) );
	}
	function displayErrorsCoverage() {
		$this->displayErrors ( array (
				'dats_date_begin',
				'dats_date_end',
				'area',
				'new_area',
				'grid_type',
				'west_bound_0',
				'east_bound_0',
				'north_bound_0',
				'south_bound_0',
				'place_alt_min_0',
				'place_alt_max_0' 
		) );
	}
	function displayErrorsSatDataDescr() {
		$this->displayErrors ( array (
				'dats_title' 
		) );
	}
	function displayForm() {
		$this->addValidationRules ();
		$this->initForm ();
		// Affichage des erreurs
		if (! empty ( $this->_errors )) {
			foreach ( $this->_errors as $error ) {
				if (strpos ( $error, 'Data descr' ) === 0) {
					echo '<a href="' . $_SERVER ['REQUEST_URI'] . '#a_descr"><font size="3" color="red">' . $error . '</font></a><br>';
				} else if (strpos ( $error, 'Contact' ) === 0) {
					echo '<a href="' . $_SERVER ['REQUEST_URI'] . '#a_contact"><font size="3" color="red">' . $error . '</font></a><br>';
				} else if (strpos ( $error, 'Instru' ) === 0) {
					echo '<a href="' . $_SERVER ['REQUEST_URI'] . '#a_instru"><font size="3" color="red">' . $error . '</font></a><br>';
				} else if (strpos ( $error, 'Coverage' ) === 0) {
					echo '<a href="' . $_SERVER ['REQUEST_URI'] . '#a_cover"><font size="3" color="red">' . $error . '</font></a><br>';
				} else if (strpos ( $error, 'Param' ) === 0) {
					echo '<a href="' . $_SERVER ['REQUEST_URI'] . '#a_param"><font size="3" color="red">' . $error . '</font></a><br>';
				} else if (strpos ( $error, 'Data' ) === 0) {
					echo '<a href="' . $_SERVER ['REQUEST_URI'] . '#a_use"><font size="3" color="red">' . $error . '</font></a><br>';
				} else {
					echo '<font size="3" color="red">' . $error . '</font><br>';
				}
			}
		}
		$this->displayFormBegin ( 'frmsat' );
		echo '<tr><th colspan="4" align="center"><a name="a_contact" ></a><b>Contact information</b><br></th></tr>';
		for($i = 0; $i < $this->dataset->nbPis; $i ++) {
			echo '<tr><td colspan="4" align="center"><b>Contact ' . ($i + 1) . '</b><br>'; // </td></tr>';
			$this->displayErrorsContact ( $i );
			$this->displayPersonForm ( $i );
		}
		echo '<tr><td colspan="4" align="center">' . $this->getElement ( 'bouton_add_pi' )->toHTML () . '</td></tr>';
		echo '<tr><th colspan="4" align="center"><a name="a_descr" ><a name="a_general" ></a><b>Data description</b></td></tr>';
		$this->displayErrorsSatDataDescr ();
		echo '<tr><td><font color="#467AA7">' . $this->getElement ( 'dats_title' )->getLabel () . '</font></td><td colspan="3">' . $this->getElement ( 'dats_title' )->toHTML () . '</td></tr>';
		echo '<tr><td><font color="#467AA7">' . $this->getElement ( 'sat_categ' )->getLabel () . '</font></td><td colspan="3">' . $this->getElement ( 'sat_categ' )->toHTML () . '</td></tr>';
		echo '<tr><td><font>' . $this->getElement ( 'dats_doi' )->getLabel () . '</font></td><td colspan="3">' . $this->getElement ( 'dats_doi' )->toHTML () . '</td></tr>';
		echo '<tr><td>' . $this->getElement ( 'dats_version' )->getLabel () . '</td><td>' . $this->getElement ( 'dats_version' )->toHTML () . '</td><td colspan="2" /></tr>';
		for($i = 0; $i < $this->dataset->nbProj; $i ++) {
			echo '<tr>';
			if ($i == 0) {
				echo '<td rowspan="' . ($this->dataset->nbProj + 1) . '">Useful in the framework of</td>';
			}
			echo '<td colspan="3">' . $this->getElement ( 'project_' . $i )->toHTML () . '</td></tr>';
		}
		echo '<tr><td colspan="3" align="center">' . $this->getElement ( 'bouton_add_projet' )->toHTML () . '</td></tr>';
		echo '<tr><td>' . $this->getElement ( 'dats_purpose' )->getLabel () . '</td><td colspan="3">' . $this->getElement ( 'dats_purpose' )->toHTML () . '</td></tr>';
		echo '<tr name="gen_desc"><td>' . $this->getElement ( 'dats_reference' )->getLabel () . '</td><td colspan="3">' . $this->getElement ( 'dats_reference' )->toHTML () . '</td></tr>';
		echo '<tr><th colspan="4" align="center"><a name="a_site" ></a><a name="a_instru" ></a><b>Instrument' . (($this->dataset->nbSites > 2) ? 's' : '') . ' information</b></td></tr>';
		for($i = 0; $i < $this->dataset->nbSites; $i ++) {
			if ($this->dataset->nbSites > 1) {
				echo '<tr><td colspan="4" align="center"><b>Instrument ' . ($i + 1) . '</b></td></tr>';
			}
			$this->displayErrorsInstrument ( $i );
			if ($i == 0) {
				echo '<tr><td><font color="#467AA7">' . $this->getElement ( 'satellite_' . $i )->getLabel () . '</font></td><td colspan="3">' . $this->getElement ( 'satellite_' . $i )->toHTML ();
				echo '&nbsp;&nbsp;or add new&nbsp;' . $this->getElement ( 'new_satellite_' . $i )->toHTML () . '</td></tr>';
				echo '<tr><td><font color="#467AA7">' . $this->getElement ( 'instrument_' . $i )->getLabel () . '</font></td><td colspan="3">' . $this->getElement ( 'instrument_' . $i )->toHTML ();
				echo '&nbsp;&nbsp;or add new&nbsp;' . $this->getElement ( 'new_instrument_' . $i )->toHTML () . '</td></tr>';
			} else {
				echo '<tr><td>' . $this->getElement ( 'satellite_' . $i )->getLabel () . '</td><td colspan="3">' . $this->getElement ( 'satellite_' . $i )->toHTML ();
				echo '&nbsp;&nbsp;or add new&nbsp;' . $this->getElement ( 'new_satellite_' . $i )->toHTML () . '</td></tr>';
				echo '<tr><td>' . $this->getElement ( 'instrument_' . $i )->getLabel () . '</td><td colspan="3">' . $this->getElement ( 'instrument_' . $i )->toHTML ();
				echo '&nbsp;&nbsp;or add new&nbsp;' . $this->getElement ( 'new_instrument_' . $i )->toHTML () . '</td></tr>';
			}
			echo '<tr><td>' . $this->getElement ( 'sensor_gcmd_' . $i )->getLabel () . '</td><td colspan="3">' . $this->getElement ( 'sensor_gcmd_' . $i )->toHTML () . '</td></tr>';
			echo '<tr><td>' . $this->getElement ( 'sensor_url_' . $i )->getLabel () . '</td><td colspan="3">' . $this->getElement ( 'sensor_url_' . $i )->toHTML () . '</td></tr>';
		}
		echo '<tr><td colspan="4" align="center">In case of multi-instrument product : ' . $this->getElement ( 'bouton_add_instru' )->toHTML () . '</td></tr>';
		echo '<tr><th colspan="4" align="center"><a name="a_param" ></a><b>Parameters</b></td></tr>';
		for($i = 0; $i < $this->dataset->nbVars; $i ++) {
			echo '<tr><td colspan="4" align="center"><b>Parameter ' . ($i + 1) . '</b>' . $this->getElement ( 'var_id_' . $i )->toHTML () . '</td></tr>';
			$this->displayErrorsParams ( $i );
			$this->displayParamForm ( $i, false, false, true );
		}
		echo '<tr><td colspan="4" align="center">' . $this->getElement ( 'bouton_add_variable' )->toHTML () . '</td></tr>';
		echo '<tr><th colspan="4" align="center"><a name="a_cover" ></a><b>Coverage</b></td></tr>';
		$this->displayErrorsCoverage ();
		echo '<tr><td colspan="4" align="center"><b>Temporal Coverage</b><br></td></tr>';
		echo '<tr><td>' . $this->getElement ( 'dats_date_begin' )->getLabel () . '</td><td>' . $this->getElement ( 'dats_date_begin' )->toHTML () . "</td>";
		echo '<td>' . $this->getElement ( 'dats_date_end' )->getLabel () . '</td><td>' . $this->getElement ( 'dats_date_end' )->toHTML () . '</td></tr>';
		$this->displayGeoCoverageForm ();
		$this->displayDataResolutionForm ();
		$this->displayGridForm ();
		echo '<tr><th colspan="4" align="center"><a name="a_use" ></a><b>Data use information</b></td></tr>';
		$this->displayErrorsUseInfo ();
		echo '<tr><td>' . $this->getElement ( 'dats_use_constraints' )->getLabel () . '</td><td colspan="3">' . $this->getElement ( 'dats_use_constraints' )->toHTML () . '</td></tr>';
		echo '<tr><td>' . $this->getElement ( 'data_policy' )->getLabel () . '</td><td colspan="3">' . $this->getElement ( 'data_policy' )->toHTML ();
		echo '&nbsp;&nbsp;or add ' . $this->getElement ( 'new_data_policy' )->getLabel () . '&nbsp;' . $this->getElement ( 'new_data_policy' )->toHTML () . '</td></tr>';
		echo '<tr><td>' . $this->getElement ( 'database' )->getLabel () . '</td><td colspan="3">' . $this->getElement ( 'database' )->toHTML ();
		echo '&nbsp;&nbsp;or add ' . $this->getElement ( 'new_database' )->getLabel () . '&nbsp;' . $this->getElement ( 'new_database' )->toHTML () . '</td></tr>';
		echo '<td>' . $this->getElement ( 'new_db_url' )->getLabel () . '</td><td>' . $this->getElement ( 'new_db_url' )->toHTML () . '</td><td colspan="2"></td></tr>';
		echo '<tr><td><a name="a_data_format" ></a>' . $this->getElement ( 'data_format_0' )->getLabel () . '</td>';
		echo '<td colspan="3">' . $this->getElement ( 'data_format_0' )->toHTML ();
		echo '&nbsp;&nbsp;or add ' . $this->getElement ( 'new_data_format_0' )->getLabel () . '' . $this->getElement ( 'new_data_format_0' )->toHTML () . '</td></tr>';
		echo '<tr><td>' . $this->getElement ( 'required_data_format' )->getLabel () . '</td><td colspan="3">' . $this->getElement ( 'required_data_format' )->toHTML () . '</td></tr>';
		$this->displayFormEnd ();
	}
}

?>		
