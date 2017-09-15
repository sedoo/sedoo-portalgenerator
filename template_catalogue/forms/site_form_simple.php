<?php
require_once ("forms/base_form.php");
class site_form_simple extends base_form {
	var $nbVarsBySensor = array ();
	function createForm($projectName) {
		$this->createFormBase ( false ); // base_form ok
		$this->createFormPeriod ( $projectName ); // base_form ok
		$this->createFormSite (); // déplacé dans cette classe
		
		$this->addElement ( 'checkbox', 'dats_date_end_not_planned', 'not planned' );
		
		$this->addElement ( 'submit', 'bouton_add_projet', 'Add a project', array (
				'onclick' => "document.getElementById('frmsite').action += '#a_general'" 
		) );
		$this->addElement ( 'submit', 'bouton_add_format', 'Add a data format', array (
				'onclick' => "document.getElementById('frmsite').action += '#a_data_format'" 
		) );
		$this->addElement ( 'submit', 'bouton_add_pi', 'Add a contact', array (
				'onclick' => "document.getElementById('frmsite').action += '#a_contact'" 
		) );
		
		for($i = 0; $i < $this->dataset->nbSensors; $i ++) {
			$this->createFormSensor ( $i ); // ok
		}
		$this->addElement ( 'submit', 'bouton_add_sensor', 'Add an instrument', array (
				'onclick' => "document.getElementById('frmsite').action += '#a_sensor_last'" 
		) );
	}
	private function createFormSite() {
		$location = new gcmd_location_keyword ();
		$loc_select = $location->chargeFormLoc ( $this, 'locationByLev0', 'Location Keyword' );
		$this->addElement ( $loc_select );
		$key = new gcmd_plateform_keyword ();
		$key_select = $key->chargeForm ( $this, 'gcmd_plat_key_0', 'Platform type' );
		$this->addElement ( $key_select );
		$this->addElement ( 'text', 'new_place_0', 'Exact Location' );
		
		$this->createFormSiteBoundings ( 0 ); // remplacé par la fonction de base_form
		$this->addElement ( 'file', 'upload_image', 'Photo' );
		$this->addElement ( 'submit', 'upload', 'Upload' );
		$this->addElement ( 'submit', 'delete', 'Delete' );
	}
	private function createFormSensor($i) {
		$this->createFormManufacturer ( '_' . $i ); // base_form ok
		$this->createFormSensorKeyword ( '_' . $i ); // base_form ok
		
		$this->addElement ( 'hidden', 'sensor_id_' . $i );
		
		$this->addElement ( 'text', 'sensor_model_' . $i, 'Model' );
		$this->applyFilter ( 'sensor_model_' . $i, 'trim' );
		
		$this->addElement ( 'text', 'sensor_url_' . $i, 'Reference (URL or paper)' );
		$this->applyFilter ( 'sensor_url_' . $i, 'trim' );
		
		$this->addElement ( 'textarea', 'sensor_calibration_' . $i, 'Instrument features / Calibration' );
		$this->applyFilter ( 'sensor_calibration_' . $i, 'trim' );
		
		$this->createFormResolution ( $i ); // base_form ok
		$this->getElement ( 'sensor_resol_temp_' . $i )->setLabel ( "Observation frequency" );
		$this->getElement ( 'sensor_vert_resolution_' . $i )->setLabel ( "Vertical coverage" );
		$this->getElement ( 'sensor_lat_resolution_' . $i )->setLabel ( "Horizontal coverage" );
		
		$this->addElement ( 'text', 'sensor_latitude_' . $i, 'Latitude (°)' );
		$this->addElement ( 'text', 'sensor_longitude_' . $i, 'Longitude (°)' );
		$this->addElement ( 'text', 'sensor_altitude_' . $i, 'Height above ground (m)' );
		$this->addElement ( 'textarea', 'sensor_environment_' . $i, 'Instrument environment', array (
				'cols' => 60,
				'rows' => 5 
		) );
		$this->applyFilter ( 'sensor_environment_' . $i, 'trim' );
		
		// Variables
		for($j = 0; $j < $this->dataset->dats_sensors [$i]->nbVars; $j ++) {
			$this->createFormVariable ( $i, '', $j ); // base_form ok
		}
		
		$this->nbVarsBySensor [$i] = $this->dataset->dats_sensors [$i]->nbVars;
		
		$this->addElement ( 'submit', 'bouton_add_variable_' . $i, 'Add a parameter', array (
				'onclick' => "document.getElementById('frmsite').action += '#a_param_" . $i . "'" 
		) );
	}
	function addSensor() {
		$this->createFormSensor ( $this->dataset->nbSensors - 1 );
	}
	function addVariableSensor($i) {
		$this->createFormVariable ( $i, '', $this->dataset->dats_sensors [$i]->nbVars - 1 ); // base_form ok
	}
	private function initFormVariableSensor($i, $j) {
		if (isset ( $this->dataset->dats_sensors [$i]->sensor->sensor_vars [$j] ) && ! empty ( $this->dataset->dats_sensors [$i]->sensor->sensor_vars [$j] ) && ($this->dataset->dats_sensors [$i]->sensor->sensor_vars [$j]->var_id > 0)) {
			$this->getElement ( 'var_id_' . $i . '_' . $j )->setValue ( $this->dataset->dats_sensors [$i]->sensor->sensor_vars [$j]->var_id );
			$this->getElement ( 'new_variable_' . $i . '_' . $j )->setValue ( $this->dataset->dats_sensors [$i]->sensor->sensor_vars [$j]->variable->var_name );
			$gcmd = new gcmd_science_keyword ();
			$gcmd = $gcmd->getById ( $this->dataset->dats_sensors [$i]->sensor->sensor_vars [$j]->variable->gcmd_id );
			if (isset ( $gcmd ) && ! empty ( $gcmd )) {
				$table = array ();
				for($k = 4; $k >= 1; $k --) {
					if ($gcmd->gcmd_level == $k) {
						$table [$k - 1] = $gcmd->gcmd_id;
						$gcmd = $gcmd->gcmd_parent;
					} else
						$table [$k - 1] = 0;
				}
				ksort ( $table );
				
				$this->getElement ( 'gcmd_science_key_' . $i . '_' . $j )->setValue ( $table );
			}
			$this->getElement ( 'methode_acq_' . $i . '_' . $j )->setValue ( $this->dataset->dats_sensors [$i]->sensor->sensor_vars [$j]->methode_acq );
			
			$this->getElement ( 'sensor_precision_' . $i . '_' . $j )->setValue ( $this->dataset->dats_sensors [$i]->sensor->sensor_vars [$j]->sensor_precision );
			if (isset ( $this->dataset->dats_sensors [$i]->sensor->sensor_vars [$j]->unit ) && ! empty ( $this->dataset->dats_sensors [$i]->sensor->sensor_vars [$j]->unit ) && ($this->dataset->dats_sensors [$i]->sensor->sensor_vars [$j]->unit->unit_id > 0)) {
				$this->getElement ( 'unit_' . $i . '_' . $j )->setSelected ( $this->dataset->dats_sensors [$i]->sensor->sensor_vars [$j]->unit->unit_id );
				$this->getElement ( 'new_unit_' . $i . '_' . $j )->setValue ( $this->dataset->dats_sensors [$i]->sensor->sensor_vars [$j]->unit->unit_name );
				$this->getElement ( 'new_unit_code_' . $i . '_' . $j )->setValue ( $this->dataset->dats_sensors [$i]->sensor->sensor_vars [$j]->unit->unit_code );
			}
		}
	}
	function initForm() {
		$dataset = & $this->dataset;
		
		$this->initFormBase (); // base_form ok
		
		$this->getElement ( 'dats_date_end_not_planned' )->setChecked ( $this->dataset->dats_date_end_not_planned );
		
		// PERIOD
		$this->getElement ( 'period' )->setSelected ( $this->dataset->period_id );
		
		// SITE, 1 seul par dataset
		$this->initFormSiteBoundings ( 0 ); // base_form ok
		
		if (isset ( $dataset->sites [0]->parent_place ) && ! empty ( $dataset->sites [0]->parent_place )) {
			$table = array ();
			$predSite = $dataset->sites [0]->parent_place;
			$type = 0;
			for($j = 3; $j > 0; $j --) {
				if ($predSite->place_level == $j) {
					$table [$j] = $predSite->place_id;
					$type = $predSite->gcmd_plat_id;
					$predSite = $predSite->parent_place;
				} else
					$table [$j] = 0;
			}
			$table [0] = $type;
			ksort ( $table );
			$this->getElement ( 'placeByLev' )->setValue ( $table );
		}
				
		if (isset ( $dataset->sites [0]->gcmd_plateform_keyword ) && ! empty ( $dataset->sites [0]->gcmd_plateform_keyword )) {
			$this->getElement ( 'gcmd_plat_key_0' )->setSelected ( $dataset->sites [0]->gcmd_plateform_keyword->gcmd_plat_id );
		}
		
		$this->getElement ( 'new_place_0' )->setValue ( $dataset->sites [0]->place_name );
		
		// SENSOR, plusieurs par dataset
		if (isset ( $dataset->dats_sensors ) && ! empty ( $dataset->dats_sensors )) {
			for($i = 0; $i < $dataset->nbSensors; $i ++) {
				if (isset ( $dataset->dats_sensors [$i] ) && ! empty ( $dataset->dats_sensors [$i] )) {
					if (isset ( $dataset->dats_sensors [$i]->sensor->manufacturer ) && ! empty ( $dataset->dats_sensors [$i]->sensor->manufacturer )) {
						$this->getElement ( 'manufacturer_' . $i )->setSelected ( $dataset->dats_sensors [$i]->sensor->manufacturer->manufacturer_id );
						$this->getElement ( 'new_manufacturer_' . $i )->setValue ( $dataset->dats_sensors [$i]->sensor->manufacturer->manufacturer_name );
						$this->getElement ( 'new_manufacturer_url_' . $i )->setValue ( $dataset->dats_sensors [$i]->sensor->manufacturer->manufacturer_url );
					}
					
					if (isset ( $dataset->dats_sensors [$i]->sensor->gcmd_instrument_keyword ) && ! empty ( $dataset->dats_sensors [$i]->sensor->gcmd_instrument_keyword )) {
						$this->getElement ( 'sensor_gcmd_' . $i )->setSelected ( $dataset->dats_sensors [$i]->sensor->gcmd_instrument_keyword->gcmd_sensor_id );
					}
					
					$this->getElement ( 'sensor_id_' . $i )->setValue ( $dataset->dats_sensors [$i]->sensor->sensor_id );
					$this->getElement ( 'sensor_model_' . $i )->setValue ( $dataset->dats_sensors [$i]->sensor->sensor_model );
					$this->getElement ( 'sensor_calibration_' . $i )->setValue ( $dataset->dats_sensors [$i]->sensor->sensor_calibration );
					$this->initFormResolution ( $i ); // base_form ok
					
					$this->getElement ( 'sensor_url_' . $i )->setValue ( $dataset->dats_sensors [$i]->sensor->sensor_url );
					if (isset ( $dataset->dats_sensors [$i]->sensor->boundings ) && ! empty ( $dataset->dats_sensors [$i]->sensor->boundings )) {
						// pour les instruments fixes seulement, lt_min = lat_max et lon_min = lon_max
						$this->getElement ( 'sensor_longitude_' . $i )->setValue ( $dataset->dats_sensors [$i]->sensor->boundings->west_bounding_coord );
						$this->getElement ( 'sensor_latitude_' . $i )->setValue ( $dataset->dats_sensors [$i]->sensor->boundings->north_bounding_coord );
					}
					if (isset ( $dataset->dats_sensors [$i]->sensor->sensor_elevation ))
						$this->getElement ( 'sensor_altitude_' . $i )->setValue ( $dataset->dats_sensors [$i]->sensor->sensor_elevation );
					if (isset ( $dataset->dats_sensors [$i]->sensor->sensor_environment ))
						$this->getElement ( 'sensor_environment_' . $i )->setValue ( $dataset->dats_sensors [$i]->sensor->sensor_environment );
				}
				// Variables
				if (isset ( $dataset->dats_sensors [$i]->sensor->sensor_vars ) && ! empty ( $dataset->dats_sensors [$i]->sensor->sensor_vars )) {
					for($j = 0; $j < count ( $dataset->dats_sensors [$i]->sensor->sensor_vars ); $j ++) {
						$this->initFormVariableSensor ( $i, $j );
					}
				}
			}
		}
	}
	function addValidationRules() {
		$this->addValidationRulesBase (); // base_form ok
		
		$this->registerRule ( 'required_if_not_void', 'function', 'required_if_not_void' );
		$this->registerRule ( 'required_if_not_void2', 'function', 'required_if_not_void2' );
		$this->registerRule ( 'required_if_not_void3', 'function', 'required_if_not_void3' );
		
		$this->registerRule ( 'existe2', 'function', 'existInDb' );
		
		$this->registerRule ( 'validBoundings', 'function', 'validBoundings' );
		$this->registerRule ( 'completeBoundings', 'function', 'completeBoundings' );
		
		$this->registerRule ( 'contact_organism_required', 'function', 'contact_organism_required' );
		$this->registerRule ( 'contact_email_required', 'function', 'contact_email_required' );
		
		$this->registerRule ( 'distinct', 'function', 'distinct' );
		$this->registerRule ( 'not_void', 'function', 'not_void' );
		
		// Sensors
		for($i = 0; $i < $this->dataset->nbSensors; $i ++) {
			$this->addRule ( 'sensor_latitude_' . $i, 'Instrument: Latitude must be numeric', 'numeric' );
			$this->addRule ( 'sensor_latitude_' . $i, 'Instrument: Latitude is incorrect', 'number_range', array (
					- 90,
					90 
			) );
			$this->addRule ( 'sensor_longitude_' . $i, 'Instrument: Longitude must be numeric', 'numeric' );
			$this->addRule ( 'sensor_longitude_' . $i, 'Instrument: Longitude is incorrect', 'number_range', array (
					- 180,
					180 
			) );
			$this->addRule ( array (
					'sensor_longitude_' . $i,
					'sensor_longitude',
					'sensor_latitude',
					'sensor_latitude' 
			), 'Instrument: Incomplete coordinates', 'completeBoundings' );
			$this->addRule ( 'sensor_altitude_' . $i, 'Instrument: Height above ground must be numeric', 'numeric' );
			$this->addRule ( 'sensor_model_' . $i, 'Instrument: Model exceeds the maximum length allowed (100 characters)', 'maxlength', 100 );
			$this->addValidationRulesResolution ( 'Instrument ' . ($i + 1), $i ); // base_form ok
			$this->addRule ( 'sensor_calibration_' . $i, 'Instrument: Calibration exceeds the maximum length allowed (250 characters)', 'maxlength', 250 );
			
			$this->addRule ( 'new_manufacturer_' . $i, 'Instrument: Manufacturer name exceeds the maximum length allowed (250 characters)', 'maxlength', 250 );
			$this->addRule ( 'new_manufacturer_url_' . $i, 'Instrument: Manufacturer url exceeds the maximum length allowed (250 characters)', 'maxlength', 250 );
			
			if (isset ( $this->dataset->dats_sensors [$i]->sensor->manufacturer ) && ! empty ( $this->dataset->dats_sensors [$i]->sensor->manufacturer ) && $this->dataset->dats_sensors [$i]->sensor->manufacturer->manufacturer_id > 0) {
				$this->getElement ( 'new_manufacturer_' . $i )->setAttribute ( 'onfocus', 'blur()' );
				$this->getElement ( 'new_manufacturer_url_' . $i )->setAttribute ( 'onfocus', 'blur()' );
			} else {
			}
			// Variables
			for($j = 0; $j < $this->dataset->dats_sensors [$i]->nbVars; $j ++) {
				$suffix = $i . '_' . $j;
				$prefixMsg = "Param $j";
				$this->addRule ( 'sensor_precision_' . $suffix, $prefixMsg . ': Sensor precision exceeds the maximum length allowed (100 characters)', 'maxlength', 100 );
				$this->addRule ( 'new_variable_' . $suffix, $prefixMsg . ': Name exceeds the maximum length allowed (100 characters)', 'maxlength', 100 );
				$this->addRule ( 'new_unit_' . $suffix, $prefixMsg . ': Unit name exceeds the maximum length allowed (50 characters)', 'maxlength', 50 );
				$this->addRule ( 'new_unit_code_' . $suffix, $prefixMsg . ': Unit code exceeds the maximum length allowed (20 characters)', 'maxlength', 20 );
				
				if (isset ( $this->dataset->sensors [$i]->sensor_vars [$j]->unit ) && ($this->dataset->sensors [$i]->sensor_vars [$j]->unit->unit_id > 0)) {
					$this->disableElement ( 'new_unit_' . $suffix );
					$this->disableElement ( 'new_unit_code_' . $suffix );
				} else {
					$this->addRule ( array (
							'unit_' . $suffix,
							'new_unit_' . $suffix,
							'new_unit_code_' . $suffix 
					), $prefixMsg . ': Unit name is required', 'validUnit_required' );
					$this->addRule ( array (
							'unit_' . $suffix,
							'new_unit_' . $suffix,
							'new_unit_code_' . $suffix 
					), $prefixMsg . ': this unit is already present in the database', 'validUnit_existe' );
				}
				
				$this->addRule ( 'new_unit_' . $suffix, $prefixMsg . ': Keyword or name is required when unit is specified', 'validParam', array (
						$this,
						$suffix 
				) );
				$this->addRule ( 'methode_acq_' . $suffix, $prefixMsg . ': Keyword or name is required when methodology is specified', 'validParam', array (
						$this,
						$suffix 
				) );
				
				$this->addRule ( 'sensor_precision_' . $suffix, $prefixMsg . ': Keyword or name is required when precision is specified', 'validParam', array (
						$this,
						$suffix 
				) );
			}
		}
		
		// Site
		$this->addRule ( 'gcmd_plat_key_0', 'Site name is required when a platform type is selected', 'required_if_not_void', array (
				$this,
				'new_place_0' 
		) );
		$this->addRule ( 'gcmd_plat_key_0', 'Site: Platform type is required', 'required_if_not_void2', array (
				$this,
				'new_place_0' 
		) );
		$this->addRule ( 'west_bound_0', 'Site name and type are required when boundings are set', 'required_if_not_void3', array (
				$this,
				'new_place_0' 
		) );
		
		$this->addRule ( 'new_place_0', 'Name exceeds the maximum length allowed (100 characters)', 'maxlength', 100 );
		$this->addValidationRulesSiteBoundings ( 0, 'Site' ); // base_form ok
		
		$this->addFormRule ( 'test_valid_form' );
	}
	function saveForm() {
		$dataset = & $this->dataset;
		
		$this->saveFormBase (); // base_form ok
		
		$dataset->dats_date_end_not_planned = $this->getElement ( 'dats_date_end_not_planned' )->getChecked ();
		
		// SITE
		$dataset->sites = array ();
		$dataset->sites [0] = new place ();
		
		$sitesLev = $this->exportValue ( 'locationByLev0' );
		$pred_site_id = 0;
		for($j = 3; $j >= 0; $j --) {
			if (isset ( $sitesLev [$j] ) && $sitesLev [$j] > 0) {
				$pred_site_id = $sitesLev [$j];
				break;
			}
		}
		$dataset->sites [0]->gcmd_loc_id = $pred_site_id;
		if ($dataset->sites [0]->gcmd_loc_id != 0) {
			$dataset->sites [0]->gcmd_location_keyword = new gcmd_location_keyword ();
			$dataset->sites [0]->gcmd_location_keyword = $dataset->sites [0]->gcmd_location_keyword->getById ( $dataset->sites [0]->gcmd_loc_id );
		}
		
		$dataset->sites [0]->place_name = $this->exportValue ( 'new_place_0' );
		if (empty ( $dataset->sites [0]->place_name )) {
			$dataset->sites [0]->place_id = - 1;
		} else if (empty ( $dataset->sites [0]->place_id )) {
			$dataset->sites [0]->place_id = 0;
		}
		
		$dataset->sites [0]->gcmd_plat_id = $this->exportValue ( 'gcmd_plat_key_0' );
		if ($dataset->sites [0]->gcmd_plat_id != 0) {
			$dataset->sites [0]->gcmd_plateform_keyword = new gcmd_plateform_keyword ();
			$dataset->sites [0]->gcmd_plateform_keyword = $dataset->sites [0]->gcmd_plateform_keyword->getById ( $dataset->sites [0]->gcmd_plat_id );
		}
		$this->saveFormSiteBoundings ( 0 ); // base_form ok
		                                 
		// SENSORS
		unset ( $dataset->dats_sensors );
		$dataset->dats_sensors = array ();
		for($i = 0; $i < $dataset->nbSensors; $i ++) {
			$dataset->dats_sensors [$i] = new dats_sensor ();
			$dataset->dats_sensors [$i]->nbVars = $this->nbVarsBySensor [$i];
			$dataset->dats_sensors [$i]->sensor = new sensor ();
			
			$sensId = $this->exportValue ( 'sensor_id_' . $i );
			if (isset ( $sensId ) && (strlen ( $sensId ) > 0)) {
				$dataset->dats_sensors [$i]->sensor->sensor_id = $sensId;
			} else {
				$dataset->dats_sensors [$i]->sensor->sensor_id = 0;
			}
			
			$dataset->dats_sensors [$i]->sensor->gcmd_sensor_id = $this->exportValue ( 'sensor_gcmd_' . $i );
			if ($dataset->dats_sensors [$i]->sensor->gcmd_sensor_id != 0) {
				$dataset->dats_sensors [$i]->sensor->gcmd_instrument_keyword = new gcmd_instrument_keyword ();
				$dataset->dats_sensors [$i]->sensor->gcmd_instrument_keyword = $dataset->dats_sensors [$i]->sensor->gcmd_instrument_keyword->getById ( $dataset->dats_sensors [$i]->sensor->gcmd_sensor_id );
			}
			
			$dataset->dats_sensors [$i]->sensor->manufacturer = new manufacturer ();
			$dataset->dats_sensors [$i]->sensor->manufacturer->manufacturer_id = $this->exportValue ( 'manufacturer_' . $i );
			$dataset->dats_sensors [$i]->sensor->manufacturer->manufacturer_name = $this->exportValue ( 'new_manufacturer_' . $i );
			$dataset->dats_sensors [$i]->sensor->manufacturer->manufacturer_url = $this->exportValue ( 'new_manufacturer_url_' . $i );
			
			if (empty ( $dataset->dats_sensors [$i]->sensor->manufacturer->manufacturer_id ) && empty ( $dataset->dats_sensors [$i]->sensor->manufacturer->manufacturer_name )) {
				$dataset->dats_sensors [$i]->sensor->manufacturer->manufacturer_id = - 1;
			}
			
			$dataset->dats_sensors [$i]->sensor->manufacturer_id = & $dataset->dats_sensors [$i]->sensor->manufacturer->manufacturer_id;
			
			$dataset->dats_sensors [$i]->sensor->sensor_url = $this->exportValue ( 'sensor_url_' . $i );
			$dataset->dats_sensors [$i]->sensor->sensor_model = $this->exportValue ( 'sensor_model_' . $i );
			$dataset->dats_sensors [$i]->sensor->sensor_calibration = $this->exportValue ( 'sensor_calibration_' . $i );
			$this->saveFormResolution ( $i ); // base_form ok
			                               			
			$dataset->dats_sensors [$i]->sensor->sensor_elevation = $this->exportValue ( 'sensor_altitude_' . $i );
			$lat = $this->exportValue ( 'sensor_latitude_' . $i );
			$lon = $this->exportValue ( 'sensor_longitude_' . $i );
			$dataset->dats_sensors [$i]->sensor->boundings = new boundings ();
			if (isset ( $lon ) && strlen ( $lon ) > 0) {
				$dataset->dats_sensors [$i]->sensor->boundings->west_bounding_coord = $lon;
				$dataset->dats_sensors [$i]->sensor->boundings->east_bounding_coord = $lon;
			} else
				$dataset->dats_sensors [$i]->sensor->bound_id = - 1;
			if (isset ( $lat ) && strlen ( $lat ) > 0) {
				$dataset->dats_sensors [$i]->sensor->boundings->north_bounding_coord = $lat;
				$dataset->dats_sensors [$i]->sensor->boundings->south_bounding_coord = $lat;
			} else
				$dataset->dats_sensors [$i]->sensor->bound_id = - 1;
			$dataset->dats_sensors [$i]->sensor->sensor_environment = $this->exportValue ( 'sensor_environment_' . $i );
			
			// Variables
			
			for($j = 0; $j < $dataset->dats_sensors [$i]->nbVars; $j ++) {
				$var_id = $this->exportValue ( 'var_id_' . $i . '_' . $j );
				$var_name = $this->exportValue ( 'new_variable_' . $i . '_' . $j );
				
				$gcmd_ids = $this->exportValue ( 'gcmd_science_key_' . $i . '_' . $j );
				$gcmd_id = 0;
				for($k = 3; $k >= 0; $k --) {
					if (isset ( $gcmd_ids [$k] ) && $gcmd_ids [$k] > 0) {
						$gcmd_id = $gcmd_ids [$k];
						break;
					}
				}
				if ($gcmd_id > 0 || ! empty ( $var_name )) {
					$dataset->dats_sensors [$i]->sensor->sensor_vars [$j] = new sensor_var ();
					$dataset->dats_sensors [$i]->sensor->sensor_vars [$j]->variable = new variable ();
					$dataset->dats_sensors [$i]->sensor->sensor_vars [$j]->variable->var_id = $var_id;
					$dataset->dats_sensors [$i]->sensor->sensor_vars [$j]->variable->var_name = $var_name;
					$dataset->dats_sensors [$i]->sensor->sensor_vars [$j]->variable->gcmd = new gcmd_science_keyword ();
					$dataset->dats_sensors [$i]->sensor->sensor_vars [$j]->variable->gcmd->getById ( $gcmd_id );
					$dataset->dats_sensors [$i]->sensor->sensor_vars [$j]->variable->gcmd_id = $gcmd_id;
					$dataset->dats_sensors [$i]->sensor->sensor_vars [$j]->unit = new unit ();
					$dataset->dats_sensors [$i]->sensor->sensor_vars [$j]->unit->unit_name = $this->exportValue ( 'new_unit_' . $i . '_' . $j );
					$dataset->dats_sensors [$i]->sensor->sensor_vars [$j]->unit->unit_code = $this->exportValue ( 'new_unit_code_' . $i . '_' . $j );
					$dataset->dats_sensors [$i]->sensor->sensor_vars [$j]->unit->unit_id = $this->exportValue ( 'unit_' . $i . '_' . $j );
					$dataset->dats_sensors [$i]->sensor->sensor_vars [$j]->methode_acq = $this->exportValue ( 'methode_acq_' . $i . '_' . $j );
					$dataset->dats_sensors [$i]->sensor->sensor_vars [$j]->sensor_precision = $this->exportValue ( 'sensor_precision_' . $i . '_' . $j );
				}
			}
		}
		
		// PERIOD
		$dataset->period = new period ();
		$dataset->period->period_id = $this->exportValue ( 'period' );
		
		if (isset ( $dataset->period->period_id ) && $dataset->period->period_id != 0) {
			$dataset->period = $dataset->period->getById ( $dataset->period->period_id );
		}
		$dataset->period_id = & $dataset->period->period_id;
	}
	function displayForm() {
		global $project_name;
		$this->addValidationRules ();
		
		$this->initForm ();
		
		// Affichage des erreurs
		if (! empty ( $this->_errors )) {
			foreach ( $this->_errors as $error ) {
				if (strpos ( $error, 'General' ) === 0) {
					echo '<a href="' . $_SERVER ['REQUEST_URI'] . '#a_general"><font size="3" color="red">' . $error . '</font></a><br>';
				} else if (strpos ( $error, 'Contact' ) === 0) {
					echo '<a href="' . $_SERVER ['REQUEST_URI'] . '#a_contact"><font size="3" color="red">' . $error . '</font></a><br>';
				} else if (strpos ( $error, 'Instru' ) === 0) {
					echo '<a href="' . $_SERVER ['REQUEST_URI'] . '#a_instru"><font size="3" color="red">' . $error . '</font></a><br>';
				} else if (strpos ( $error, 'Site' ) === 0) {
					echo '<a href="' . $_SERVER ['REQUEST_URI'] . '#a_site"><font size="3" color="red">' . $error . '</font></a><br>';
				} else if (strpos ( $error, 'Measured' ) === 0) {
					echo '<a href="' . $_SERVER ['REQUEST_URI'] . '#a_param"><font size="3" color="red">' . $error . '</font></a><br>';
				} else if (strpos ( $error, 'Derived' ) === 0) {
					echo '<a href="' . $_SERVER ['REQUEST_URI'] . '#a_param_calcul"><font size="3" color="red">' . $error . '</font></a><br>';
				} else if (strpos ( $error, 'Data' ) === 0) {
					echo '<a href="#a_use"><font size="3" color="red">' . $error . '</font></a><br>';
				} else {
					echo '<font size="3" color="red">' . $error . '</font><br>';
				}
			}
		}
		echo '<div id="errors" color="red"></div><br>';
		
		echo '<style>
				select {
					max-width:120px;
				}
				</style>';
		
		if (strpos ( $_SERVER ['REQUEST_URI'], '&datsId' )) {
			$reqUri = substr ( $_SERVER ['REQUEST_URI'], 0, strpos ( $_SERVER ['REQUEST_URI'], '&datsId' ) );
		} else if (strpos ( $_SERVER ['REQUEST_URI'], '?datsId' )) {
			$reqUri = substr ( $_SERVER ['REQUEST_URI'], 0, strpos ( $_SERVER ['REQUEST_URI'], '?datsId' ) );
		} else {
			$reqUri = $_SERVER ['REQUEST_URI'];
		}
		
		echo '<form action="' . $reqUri . '" method="post" name="frmsite" id="frmsite" enctype="multipart/form-data">';
		echo '<SCRIPT LANGUAGE="Javascript" SRC="/js/functions.js"> </SCRIPT>';
		echo $this->getElement ( 'dats_id' )->toHTML ();
		
		echo '<table class="metadata-form"><tr><th class="top" colspan="4" align="left"><font color="#467AA7">Required fields are in blue</font></th></tr>';
		echo '<tr><td colspan="4" align="center"><a href="' . $reqUri . '?datsId=-10">Reset</a></td></tr>';
		
		/**
		 * *** General info ****
		 */
		
		echo '<tr><th colspan="4" align="center"><a name="a_general" ></a><b>General information</b></th></tr>';
		$this->displayErrorsGeneralInfo ();
		echo '<tr><td><font color="#467AA7">' . $this->getElement ( 'dats_title' )->getLabel () . '</font></td><td colspan="3">' . $this->getElement ( 'dats_title' )->toHTML () . '</td></tr>';
		echo '<tr><td><font>' . $this->getElement ( 'dats_doi' )->getLabel () . '</font></td><td colspan="3">' . $this->getElement ( 'dats_doi' )->toHTML () . '</td></tr>';
		$this->displayPeriodForm (); // base_form ok
		
		for($i = 0; $i < $this->dataset->nbProj; $i ++) {
			echo '<tr>';
			if ($i == 0) {
				echo '<td rowspan="' . ($this->dataset->nbProj + 1) . '">Project' . (($this->dataset->nbProj > 1) ? 's' : '') . '</td>';
			}
			echo '<td colspan="3">' . $this->getElement ( 'project_' . $i )->toHTML () . '</td></tr>';
		}
		echo '<tr><td colspan="3" align="center">' . $this->getElement ( 'bouton_add_projet' )->toHTML () . '</td></tr>';
		
		echo '<tr><td>' . $this->getElement ( 'dats_abstract' )->getLabel () . '</td><td colspan="3">' . $this->getElement ( 'dats_abstract' )->toHTML () . '</td></tr>';
		echo '<tr><td>' . $this->getElement ( 'dats_purpose' )->getLabel () . '</td><td colspan="3">' . $this->getElement ( 'dats_purpose' )->toHTML () . '</td></tr>';
		echo '<tr><td>' . $this->getElement ( 'dats_reference' )->getLabel () . '</td><td colspan="3">' . $this->getElement ( 'dats_reference' )->toHTML () . '</td></tr>';
		
		/**
		 * *** Contacts ****
		 */
		
		echo '<tr><th colspan="4" align="center"><a name="a_contact" ></a><b>Contact information</b></td></tr><tr>';
		for($i = 0; $i < $this->dataset->nbPis; $i ++) {
			echo '<tr><td colspan="4" align="center"><b>Contact ' . ($i + 1) . '</b><br>'; // </td></tr>';
			$this->displayErrorsContact ( $i ); // base_form ok
			$this->displayPersonForm ( $i ); // base_form ok
		}
		echo '<tr><td colspan="4" align="center">' . $this->getElement ( 'bouton_add_pi' )->toHTML () . '</td></tr>';
		
		/**
		 * *** Site ****
		 */
		echo '<tr><th colspan="4" align="center"><a name="a_site" ></a><b>Site information</b></td></tr>';
		$this->displayErrorsSite ( 0 );
		echo '<tr><td>' . $this->getElement ( 'locationByLev0' )->getLabel () . '</td><td colspan="3">' . $this->getElement ( 'locationByLev0' )->toHTML () . '</td></tr>';
		echo '<tr><td>' . $this->getElement ( 'new_place_0' )->getLabel () . '</td><td>' . $this->getElement ( 'new_place_0' )->toHTML () . '</td>';
		echo '<td>' . $this->getElement ( 'gcmd_plat_key_0' )->getLabel () . '</td><td>' . $this->getElement ( 'gcmd_plat_key_0' )->toHTML () . '</td></tr>';
		
		$this->displaySiteBoundingsForm ( 0 ); // base_form ok
		echo '<tr><td>' . $this->getElement ( 'upload_image' )->getLabel () . '</td>';
		if (isset ( $this->dataset->image ) && ! empty ( $this->dataset->image )) {
			echo '<td><a href="' . $this->dataset->image . '" target=_blank><img src="' . $this->dataset->image . '" width="50" /></a>';
			echo $this->getElement ( 'delete' )->toHTML () . '</td><td colspan="2" />';
		} else {
			echo '<td/><td colspan=2><input type="hidden" name="MAX_FILE_SIZE" value="2000000" />' . $this->getElement ( 'upload_image' )->toHTML ();
			echo $this->getElement ( 'upload' )->toHTML () . '</td>';
		}
		echo '</tr>';
		
		/**
		 * *** Instruments ****
		 */
		for($i = 0; $i < $this->dataset->nbSensors; $i ++) {
			echo '<tr><th colspan="4" align="center">';
			if ($i == $this->dataset->nbSensors - 1) {
				echo '<a name="a_sensor_last" ></a>';
			}
			echo '<b>Instrument ' . ($i + 1) . '</b></th></tr>';
			$this->displayErrorsInstru ( $i );
			echo '<tr><td>' . $this->getElement ( 'sensor_gcmd_' . $i )->getLabel () . '</td><td colspan="3">' . $this->getElement ( 'sensor_gcmd_' . $i )->toHTML () . '</td></tr>';
			
			echo '<tr><td>' . $this->getElement ( 'manufacturer_' . $i )->getLabel () . '</td><td colspan="3">' . $this->getElement ( 'manufacturer_' . $i )->toHTML ();
			echo '&nbsp;&nbsp;or add ' . $this->getElement ( 'new_manufacturer_' . $i )->getLabel () . '' . $this->getElement ( 'new_manufacturer_' . $i )->toHTML () . '</td></tr>';
			echo '<tr><td>' . $this->getElement ( 'new_manufacturer_url_' . $i )->getLabel () . '</td><td colspan="3">' . $this->getElement ( 'new_manufacturer_url_' . $i )->toHTML () . '</td></tr>';
			echo '<tr><td>' . $this->getElement ( 'sensor_model_' . $i )->getLabel () . '</td><td>' . $this->getElement ( 'sensor_model_' . $i )->toHTML () . '</td>';
			echo '<td>' . $this->getElement ( 'sensor_calibration_' . $i )->getLabel () . '</td><td>' . $this->getElement ( 'sensor_calibration_' . $i )->toHTML () . '</td></tr>';
			echo '<tr><td>' . $this->getElement ( 'sensor_resol_temp_' . $i )->getLabel () . '</td><td>' . $this->getElement ( 'sensor_resol_temp_' . $i )->toHTML () . '</td><td colspan="2"></td></tr>';
			
			echo '<tr><td>' . $this->getElement ( 'sensor_lat_resolution_' . $i )->getLabel () . '</td><td>' . $this->getElement ( 'sensor_lat_resolution_' . $i )->toHTML () . '</td>';
			echo '<td>' . $this->getElement ( 'sensor_vert_resolution_' . $i )->getLabel () . '</td><td>' . $this->getElement ( 'sensor_vert_resolution_' . $i )->toHTML () . '</td></tr>';
			echo '<tr><td>' . $this->getElement ( 'sensor_longitude_' . $i )->getLabel () . '</td><td>' . $this->getElement ( 'sensor_longitude_' . $i )->toHTML () . '</td>';
			echo '<td>' . $this->getElement ( 'sensor_latitude_' . $i )->getLabel () . '</td><td>' . $this->getElement ( 'sensor_latitude_' . $i )->toHTML () . '</td></tr>';
			echo '<tr><td>' . $this->getElement ( 'sensor_altitude_' . $i )->getLabel () . '</td><td>' . $this->getElement ( 'sensor_altitude_' . $i )->toHTML () . '</td>';
			echo '<td>' . $this->getElement ( 'sensor_url_' . $i )->getLabel () . '</td><td>' . $this->getElement ( 'sensor_url_' . $i )->toHTML () . '</td></tr>';
			echo '<tr><td>' . $this->getElement ( 'sensor_environment_' . $i )->getLabel () . '</td><td colspan="3">' . $this->getElement ( 'sensor_environment_' . $i )->toHTML () . '</td></tr>';
			
			// Variables
			for($j = 0; $j < $this->dataset->dats_sensors [$i]->nbVars; $j ++) {
				echo '<tr><td colspan="4" align="center">';
				if ($j == $this->dataset->dats_sensors [$i]->nbVars - 1) {
					echo "<a name='a_param_$i' ></a>";
				}
				echo '<b>Parameter ' . ($j + 1) . ' measured by instrument ' . ($i + 1) . '</b>' . $this->getElement ( 'var_id_' . $i . '_' . $j )->toHTML () . '</td></tr>';
				$this->displayErrorsParamsSensor ( $i, $j ); // déplacé ici
				$this->displayParamSensorForm ( $i, $j ); // déplacé ici
			}
			echo '<tr><td colspan="4" align="center">' . $this->getElement ( 'bouton_add_variable_' . $i )->toHTML () . '</td></tr>';
		}
		
		echo '<tr><td colspan="4" align="center">' . $this->getElement ( 'bouton_add_sensor' )->toHTML () . '</td></tr>';
		
		/**
		 * *** Data Use information *****
		 */
		
		echo '<tr><th colspan="4" align="center"><a name="a_use" ></a><b>Data use information</b></td></tr>';
		$this->displayErrorsUseInfo (); // base_form ok
		echo '<tr><td>' . $this->getElement ( 'dats_use_constraints' )->getLabel () . '</td><td colspan="3">' . $this->getElement ( 'dats_use_constraints' )->toHTML () . '</td></tr>';
		echo '<tr><td>' . $this->getElement ( 'data_policy' )->getLabel () . '</td><td colspan="3">' . $this->getElement ( 'data_policy' )->toHTML ();
		echo '&nbsp;&nbsp;or add ' . $this->getElement ( 'new_data_policy' )->getLabel () . '&nbsp;' . $this->getElement ( 'new_data_policy' )->toHTML () . '</td></tr>';
		echo '<tr><td>' . $this->getElement ( 'database' )->getLabel () . '</td><td colspan="3">' . $this->getElement ( 'database' )->toHTML ();
		echo '&nbsp;&nbsp;or add ' . $this->getElement ( 'new_database' )->getLabel () . '&nbsp;' . $this->getElement ( 'new_database' )->toHTML () . '</td></tr>';
		echo '<td>' . $this->getElement ( 'new_db_url' )->getLabel () . '</td><td>' . $this->getElement ( 'new_db_url' )->toHTML () . '</td><td colspan="2"></td></tr>';
		
		for($i = 0; $i < $this->dataset->nbFormats; $i ++) {
			echo '<tr>';
			if ($i == 0) {
				echo '<td rowspan="' . ($this->dataset->nbFormats + 1) . '"><a name="a_data_format" ></a>Data formats' . (($this->dataset->nbFormats > 1) ? 's' : '') . '</td>';
			}
			echo '<td colspan="3">' . $this->getElement ( 'data_format_' . $i )->toHTML ();
			echo '&nbsp;&nbsp;or add ' . $this->getElement ( 'new_data_format_' . $i )->getLabel () . '' . $this->getElement ( 'new_data_format_' . $i )->toHTML () . '</td></tr>';
		}
		echo '<tr><td colspan="3" align="center">' . $this->getElement ( 'bouton_add_format' )->toHTML () . '</td></tr>';
		
		echo '<tr>';
		
		echo '<th colspan="4" align="center">' . $this->getElement ( 'bouton_save' )->toHTML () . '</th></tr>';
		echo '</table></form>';
		
		$proj = new project ();
		$project = $proj->getIdByProjectName ( $project_name );
		echo '<script>
		function init_project(){
			$("#frmsite select[name=\'project_0[0]\']").val(' . $project->project_id . ').attr("selected",true).change();
		}
		window.onload = init_project();
					</script>';
	}
	function saveDatsVars() {
		$dataset = & $this->dataset;
		unset ( $dataset->dats_vars );
		$dataset->dats_vars = array ();
		$ids = array ();
		$indice = 0;
		for($i = 0; $i < $dataset->nbSensors; $i ++) {
			for($j = 0; $j < $dataset->dats_sensors [$i]->nbVars; $j ++) {
				if (isset ( $dataset->dats_sensors [$i]->sensor->sensor_vars [$j] )) {
					$dataset->dats_variables [$indice] = new dats_var ();
					$dataset->dats_variables [$indice]->variable = & $dataset->dats_sensors [$i]->sensor->sensor_vars [$j]->variable;
					$dataset->dats_variables [$indice]->var_id = & $dataset->dats_sensors [$i]->sensor->sensor_vars [$j]->variable->var_id;
					$dataset->dats_variables [$indice]->unit = & $dataset->dats_sensors [$i]->sensor->sensor_vars [$j]->unit;
					
					if (empty ( $dataset->dats_variables [$indice]->unit->unit_id ) && empty ( $dataset->dats_variables [$indice]->unit->unit_name )) {
						$dataset->dats_variables [$indice]->unit->unit_id = - 1;
					}
					$dataset->dats_variables [$indice]->unit_id = & $dataset->dats_variables [$indice]->unit->unit_id;
					
					$dataset->dats_variables [$indice]->flag_param_calcule = 0;
					$dataset->dats_variables [$indice]->variable->sensor_precision = & $dataset->dats_sensors [$i]->sensor->sensor_vars [$j]->sensor_precision;
					$indice ++;
				}
			}
		}
	}
	private function displayParamSensorForm($i, $j) {
		echo '<tr><td>' . $this->getElement ( 'gcmd_science_key_' . $i . '_' . $j )->getLabel () . '</td><td colspan="3">' . $this->getElement ( 'gcmd_science_key_' . $i . '_' . $j )->toHTML () . '</td></tr>';
		echo '<tr><td colspan="2">' . $this->getElement ( 'new_variable_' . $i . '_' . $j )->getLabel () . '</td><td colspan="2">' . $this->getElement ( 'new_variable_' . $i . '_' . $j )->toHTML () . '</td></tr>';
		echo '<tr><td>' . $this->getElement ( 'unit_' . $i . '_' . $j )->getLabel () . '</td><td colspan="3">' . $this->getElement ( 'unit_' . $i . '_' . $j )->toHTML ();
		echo '&nbsp;&nbsp;or add ' . $this->getElement ( 'new_unit_' . $i . '_' . $j )->getLabel () . '' . $this->getElement ( 'new_unit_' . $i . '_' . $j )->toHTML ();
		echo $this->getElement ( 'new_unit_code_' . $i . '_' . $j )->getLabel () . '' . $this->getElement ( 'new_unit_code_' . $i . '_' . $j )->toHTML () . '</td></tr>';
		echo '<tr><td>' . $this->getElement ( 'methode_acq_' . $i . '_' . $j )->getLabel () . '</td><td colspan="3">' . $this->getElement ( 'methode_acq_' . $i . '_' . $j )->toHTML () . '</td></tr>';
		echo '<tr><td>' . $this->getElement ( 'sensor_precision_' . $i . '_' . $j )->getLabel () . '</td><td>' . $this->getElement ( 'sensor_precision_' . $i . '_' . $j )->toHTML () . '</td><td colspan="2"></td></tr>';
	}
	private function displayErrorsParamsSensor($i, $j) {
		$suffix = '_' . $i . '_' . $j;
		$this->displayErrors ( array (
				'new_variable' . $suffix,
				'unit' . $suffix,
				'new_unit' . $suffix,
				'new_unit_code' . $suffix,
				'sensor_precision' . $suffix,
				'methode_acq' . $suffix
		) );
	}
	private function displayErrorsInstru($i) {
		$this->displayErrors ( array (
				'sensor_gcmd_' . $i,
				'new_manufacturer_' . $i,
				'new_manufacturer_url_' . $i,
				'sensor_model_' . $i,
				'sensor_calibration_' . $i,
				'sensor_resol_temp_' . $i,
				'sensor_vert_resolution_' . $i,
				'sensor_lat_resolution_' . $i,
				'sensor_url_' . $i,
				'sensor_latitude_' . $i,
				'sensor_longitude_' . $i,
				'sensor_altitude_' . $i,
				'sensor_environment_' . $i 
		) );
	}
}

?>
