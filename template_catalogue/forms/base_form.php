<?php
require_once ("forms/login_form.php");
require_once ("common.php");
require_once ("bd/dataset.php");
require_once ("bd/project.php");
require_once ("bd/period.php");
require_once ("bd/personne.php");
require_once ("bd/contact_type.php");
require_once ("bd/organism.php");
require_once ("bd/dataset_type.php");
require_once ("bd/data_format.php");
require_once ("bd/dats_var.php");
require_once ("bd/dats_sensor.php");
require_once ("bd/sensor.php");
require_once ("bd/gcmd_instrument_keyword.php");
require_once ("bd/manufacturer.php");
require_once ("bd/variable.php");
require_once ("bd/gcmd_science_keyword.php");
require_once ("bd/place.php");
require_once ("bd/boundings.php");
require_once ("bd/gcmd_plateform_keyword.php");
require_once ("bd/unit.php");
require_once ("bd/data_policy.php");
require_once ("bd/database.php");
require_once ("bd/gcmd_location_keyword.php");

// Mod
// class base_form extends HTML_QuickForm{
class base_form extends login_form {
	var $dataset;
	
	// Add
	function createLoginForm() {
		if (isset ( $_SESSION ['loggedUser'] )) {
			$this->user = unserialize ( $_SESSION ['loggedUser'] );
		}
		if (! $this->isCat ( $this->dataset )) {
			parent::createLoginForm ( 'Login' );
		}
	}
	function createFormBase($withVars = true) {
		$this->addElement ( 'hidden', 'dats_id' );
		$this->addElement ( 'text', 'dats_title', 'Dataset name' );
		
		$this->addElement ( 'textarea', 'dats_abstract', 'Abstract', array (
				'cols' => 60,
				'rows' => 5 
		) );
		$this->applyFilter ( 'dats_abstract', 'trim' );
		$this->addElement ( 'textarea', 'dats_purpose', 'Observing strategy', array (
				'cols' => 60,
				'rows' => 5 
		) );
		$this->applyFilter ( 'dats_purpose', 'trim' );
		$this->addElement ( 'textarea', 'dats_reference', 'References', array (
				'cols' => 60,
				'rows' => 5 
		) );
		$this->applyFilter ( 'dats_reference', 'trim' );
		$this->addElement ( 'textarea', 'dats_use_constraints', 'Use constraints / Acknowledgment', array (
				'cols' => 60,
				'rows' => 5 
		) );
		$this->applyFilter ( 'dats_use_constraints', 'trim' );
		
		$this->addElement ( 'text', 'dats_version', 'Version' );
		$this->applyFilter ( 'dats_version', 'trim' );
		
		$this->addElement ( 'text', 'dats_date_begin', 'Date begin', array (
				'size' => 10,
				'placeholder' => 'yyyy-mm-dd' 
		) );
		$this->addElement ( 'text', 'dats_date_end', 'Date end', array (
				'size' => 10,
				'placeholder' => 'yyyy-mm-dd' 
		) );
		
		$this->addElement ( 'text', 'dats_doi', 'DOI', array (
				'size' => 30 
		) );
		
		for($i = 0; $i < $this->dataset->nbProj; $i ++) {
			$this->createFormProject ( $i );
		}
		$this->createFormDataPolicy ();
		$this->createFormDatabase ();
		for($i = 0; $i < $this->dataset->nbFormats; $i ++) {
			$this->createFormDataFormat ( $i );
		}
		for($i = 0; $i < $this->dataset->nbPis; $i ++) {
			$this->createFormPersonne ( $i );
		}
		
		if ($withVars) {
			for($i = 0; $i < $this->dataset->nbVars; $i ++) {
				$this->createFormVariable ( $i );
			}
		}
		
		$this->addElement ( 'submit', 'bouton_save', 'Save' );
	}
	
	/* Variables */
	
	/*
	 * suffix: _$type$i[_$j]
	 */
	function createFormVariable($i, $type = '', $j = -1) {
		$suffix = '_' . $type . $i;
		if ($j != - 1) {
			$suffix .= '_' . $j;
		}
		
		$key = new gcmd_science_keyword ();
		$key_select = $key->chargeForm ( $this, 'gcmd_science_key' . $suffix, 'Parameter keyword' );
		$this->addElement ( $key_select );
		$this->addElement ( 'hidden', 'var_id' . $suffix );
		$this->addElement ( 'text', 'new_variable' . $suffix, 'New parameter name (if different from the selected parameter keyword)' );
		$this->applyFilter ( 'new_variable' . $suffix, 'trim' );
		
		$unit = new unit ();
		if ($j == - 1) {
			$unit_select = $unit->chargeForm ( $this, 'unit' . $suffix, 'Unit', $i, $type );
		} else {
			$unit_select = $unit->chargeForm ( $this, 'unit' . $suffix, 'Unit', $i . '_' . $j, $type ); // TODO
		}
		$this->addElement ( $unit_select );
		$this->addElement ( 'text', 'new_unit' . $suffix, 'new unit' );
		$this->applyFilter ( 'new_unit' . $suffix, 'trim' );
		$this->addElement ( 'text', 'new_unit_code' . $suffix, ', unit code: ' );
		$this->applyFilter ( 'new_unit_code' . $suffix, 'trim' );
		$this->addElement ( 'textarea', 'methode_acq' . $suffix, 'Acquisition methodology and quality', array (
				'cols' => 60,
				'rows' => 5 
		) );
		$this->applyFilter ( 'methode_acq' . $suffix, 'trim' );
		$this->addElement ( 'text', 'sensor_precision' . $suffix, 'Sensor precision' );
		$this->applyFilter ( 'sensor_precision' . $suffix, 'trim' );
		
		$this->addElement ( 'text', 'level_type' . $suffix, 'Level type' );
		$this->applyFilter ( 'level_type' . $suffix, 'trim' );
		
		// format pour les dates
		$options = array (
				'language' => 'en',
				'format' => 'Y-M-d',
				'placeholder' => 'yyyy-mm-dd' 
		);
		$this->addElement ( 'text', 'var_date_min' . $suffix, 'Date begin', $options );
		$this->addElement ( 'text', 'var_date_max' . $suffix, 'Date end', $options );
	}
	function addVariable() {
		$this->createFormVariable ( $this->dataset->nbVars - 1, '' );
	}
	function addVariableCalcul() {
		$this->createFormVariable ( $this->dataset->nbCalcVars - 1, 'calcul' );
	}
	function disableElement($elementName) {
		if ($this->getElement ( $elementName )) {
			$this->getElement ( $elementName )->setAttribute ( 'onfocus', 'blur()' );
			$this->getElement ( $elementName )->setAttribute ( 'style', 'background-color: transparent;' );
		}
	}
	function addValidationRulesBase() {
		$this->registerRule ( 'validDate', 'function', 'validDate' );
		$this->registerRule ( 'validPeriod', 'function', 'validPeriod' );
		$this->registerRule ( 'existe', 'function', 'existe' );
		$this->registerRule ( 'number_range', 'function', 'number_range' );
		$this->registerRule ( 'validInterval', 'function', 'validInterval' );
		$this->registerRule ( 'couple_not_null', 'function', 'couple_not_null' );
		
		$this->registerRule ( 'validParam', 'function', 'validParam' );
		$this->registerRule ( 'validUnit_existe', 'function', 'validUnit_existe' );
		$this->registerRule ( 'validUnit_required', 'function', 'validUnit_required' );
		
		$this->addRule ( 'dats_title', 'General information: Metadata informative title is required', 'required' );
		$this->addRule ( 'dats_title', 'General information: Dataset name exceeds the maximum length allowed (250 characters)', 'maxlength', 250 );
		
		$this->addRule ( 'dats_date_begin', 'General information: Date begin is not a date', 'validDate' );
		$this->addRule ( 'dats_date_end', 'General information: Date end is not a date', 'validDate' );
		$this->addRule ( array (
				'dats_date_begin',
				'dats_date_end' 
		), 'General information: Date end must be after date begin', 'validPeriod' );
		if (isset ( $this->dataset->dats_id ) && ! empty ( $this->dataset->dats_id )) {
			if ($this->dataset->dats_id == 0) {
				$this->addRule ( 'dats_title', 'General information: A dataset with the same title exists in the database', 'existe', array (
						'dataset',
						'dats_title' 
				) );
			}
		}
		
		if (isset ( $this->dataset->data_policy ) && ! empty ( $this->dataset->data_policy ) && $this->dataset->data_policy->data_policy_id > 0) {
			$this->getElement ( 'new_data_policy' )->setAttribute ( 'onfocus', 'blur()' );
		} else {
		}
		$this->addRule ( 'new_data_policy', 'Data use information: Data policy exceeds the maximum length allowed (100 characters)', 'maxlength', 100 );
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
		$this->addRule ( 'organism_0', 'Contact 1: organization is required', 'couple_not_null', array (
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
	}
	function initFormSiteBoundings($i) {
		if (isset ( $this->dataset->sites [$i]->boundings ) && ! empty ( $this->dataset->sites [$i]->boundings )) {
			$this->getElement ( 'west_bound_' . $i )->setValue ( $this->dataset->sites [$i]->boundings->west_bounding_coord );
			$this->getElement ( 'east_bound_' . $i )->setValue ( $this->dataset->sites [$i]->boundings->east_bounding_coord );
			$this->getElement ( 'north_bound_' . $i )->setValue ( $this->dataset->sites [$i]->boundings->north_bounding_coord );
			$this->getElement ( 'south_bound_' . $i )->setValue ( $this->dataset->sites [$i]->boundings->south_bounding_coord );
		}
		$this->getElement ( 'place_alt_min_' . $i )->setValue ( $this->dataset->sites [$i]->place_elevation_min );
		$this->getElement ( 'place_alt_max_' . $i )->setValue ( $this->dataset->sites [$i]->place_elevation_max );
	}
	function disableSiteBoundings($i) {
		$this->disableElement ( 'west_bound_' . $i );
		$this->disableElement ( 'east_bound_' . $i );
		$this->disableElement ( 'north_bound_' . $i );
		$this->disableElement ( 'south_bound_' . $i );
		$this->disableElement ( 'place_alt_min_' . $i );
		$this->disableElement ( 'place_alt_max_' . $i );
	}
	function saveFormSiteBoundings($i) {
		$this->dataset->sites [$i]->place_elevation_min = $this->exportValue ( 'place_alt_min_' . $i );
		$this->dataset->sites [$i]->place_elevation_max = $this->exportValue ( 'place_alt_max_' . $i );
		
		$this->dataset->sites [$i]->boundings = new boundings ();
		$this->dataset->sites [$i]->bound_id = 0;
		$west = $this->exportValue ( 'west_bound_' . $i );
		if (isset ( $west ) && strlen ( $west ) > 0)
			$this->dataset->sites [$i]->boundings->west_bounding_coord = $west;
		else
			$this->dataset->sites [$i]->bound_id = - 1;
		
		$east = $this->exportValue ( 'east_bound_' . $i );
		if (isset ( $east ) && strlen ( $east ) > 0)
			$this->dataset->sites [$i]->boundings->east_bounding_coord = $east;
		else
			$this->dataset->sites [$i]->bound_id = - 1;
		
		$north = $this->exportValue ( 'north_bound_' . $i );
		if (isset ( $north ) && strlen ( $north ) > 0)
			$this->dataset->sites [$i]->boundings->north_bounding_coord = $north;
		else
			$this->dataset->sites [$i]->bound_id = - 1;
		
		$south = $this->exportValue ( 'south_bound_' . $i );
		if (isset ( $south ) && strlen ( $south ) > 0)
			$this->dataset->sites [$i]->boundings->south_bounding_coord = $south;
		else
			$this->dataset->sites [$i]->bound_id = - 1;
	}
	function addValidationRulesSiteBoundings($i, $prefixMsg) {
		$this->addRule ( 'west_bound_' . $i, $prefixMsg . ': West bounding coordinate must be numeric', 'numeric' );
		$this->addRule ( 'west_bound_' . $i, $prefixMsg . ': West bounding coordinate is incorrect', 'number_range', array (
				- 180,
				180 
		) );
		$this->addRule ( 'east_bound_' . $i, $prefixMsg . ': East bounding coordinate must be numeric', 'numeric' );
		$this->addRule ( 'east_bound_' . $i, $prefixMsg . ': East bounding coordinate is incorrect', 'number_range', array (
				- 180,
				180 
		) );
		$this->addRule ( 'north_bound_' . $i, $prefixMsg . ': North bounding coordinate must be numeric', 'numeric' );
		$this->addRule ( 'north_bound_' . $i, $prefixMsg . ': North bounding coordinate is incorrect', 'number_range', array (
				- 90,
				90 
		) );
		$this->addRule ( 'south_bound_' . $i, $prefixMsg . ': South bounding coordinate must be numeric', 'numeric' );
		$this->addRule ( 'south_bound_' . $i, $prefixMsg . ': South bounding coordinate is incorrect', 'number_range', array (
				- 90,
				90 
		) );
		$this->addRule ( array (
				'west_bound_' . $i,
				'east_bound_' . $i,
				'south_bound_' . $i,
				'north_bound_' . $i 
		), $prefixMsg . ': Incomplete boundings', 'completeBoundings' );
		$this->addRule ( array (
				'west_bound_' . $i,
				'east_bound_' . $i,
				'south_bound_' . $i,
				'north_bound_' . $i 
		), $prefixMsg . ': Incorrect boundings', 'validBoundings' );
		$this->addRule ( 'place_alt_min_' . $i, $prefixMsg . ': Altitude min must be numeric', 'numeric' );
		$this->addRule ( 'place_alt_max_' . $i, $prefixMsg . ': Altitude max must be numeric', 'numeric' );
		$this->addRule ( array (
				'place_alt_min_' . $i,
				'place_alt_max_' . $i 
		), $prefixMsg . ': Altitude max must be greater than altitude min', 'validInterval' );
	}
	
	function addValidationRulesVariable($i, $suffix, $prefixMsg) {
		$this->addRule ( 'sensor_precision_' . $suffix, $prefixMsg . ': Sensor precision exceeds the maximum length allowed (100 characters)', 'maxlength', 100 );
		$this->addRule ( 'new_variable_' . $suffix, $prefixMsg . ': Name exceeds the maximum length allowed (100 characters)', 'maxlength', 100 );
		$this->addRule ( 'new_unit_' . $suffix, $prefixMsg . ': Unit name exceeds the maximum length allowed (50 characters)', 'maxlength', 50 );
		$this->addRule ( 'new_unit_code_' . $suffix, $prefixMsg . ': Unit code exceeds the maximum length allowed (20 characters)', 'maxlength', 20 );
		
		$this->addRule ( 'var_date_min_' . $suffix, $prefixMsg . ': Date begin is not a date', 'validDate' );
		$this->addRule ( 'var_date_max_' . $suffix, $prefixMsg . ': Date end is not a date', 'validDate' );
		$this->addRule ( array (
				'var_date_min_' . $suffix,
				'var_date_max_' . $suffix 
		), $prefixMsg . ': Date end must be after date begin', 'validPeriod' );
		
		if (isset ( $this->dataset->dats_variables [$i]->unit ) && ($this->dataset->dats_variables [$i]->unit->unit_id > 0)) {
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
		
		$this->addRule ( 'var_date_min_' . $suffix, $prefixMsg . ': Keyword or name is required when date begin is specified', 'validParam', array (
				$this,
				$suffix 
		) );
		$this->addRule ( 'var_date_max_' . $suffix, $prefixMsg . ': Keyword or name is required when date end is specified', 'validParam', array (
				$this,
				$suffix 
		) );
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
	function createFormSensorKeywords() {
		$key = new gcmd_instrument_keyword ();
		$key_select = $key->chargeFormInstr ( $this, 'sensor_gcmd_', 'Instrument type' );
		$this->addElement ( $key_select );
	}
	function createFormGrid() {
		$this->addElement ( 'textarea', 'grid_original', 'Original grid type and related information', array (
				'cols' => 60,
				'rows' => 5 
		) );
		$this->applyFilter ( 'grid_original', 'trim' );
		$this->addElement ( 'textarea', 'grid_process', 'Grid processing (re-projection, interpolation...) ', array (
				'cols' => 60,
				'rows' => 5 
		) );
		$this->applyFilter ( 'grid_process', 'trim' );
	}
	function saveFormGrid() {
		$this->dataset->dats_sensors [0]->grid_original = $this->exportValue ( 'grid_original' );
		$this->dataset->dats_sensors [0]->grid_process = $this->exportValue ( 'grid_process' );
	}
	function initFormGrid() {
		if (isset ( $this->dataset->dats_sensors [0]->grid_original ) && ! empty ( $this->dataset->dats_sensors [0]->grid_original ))
			$this->getElement ( 'grid_original' )->setValue ( $this->dataset->dats_sensors [0]->grid_original );
		if (isset ( $this->dataset->dats_sensors [0]->grid_process ) && ! empty ( $this->dataset->dats_sensors [0]->grid_process ))
			$this->getElement ( 'grid_process' )->setValue ( $this->dataset->dats_sensors [0]->grid_process );
	}
	function displayGridForm() {
		echo '<tr><td colspan="4" align="center"><b>Grid type</b><br></td></tr>';
		echo '<tr><td>' . $this->getElement ( 'grid_original' )->getLabel () . '</td><td colspan="3">' . $this->getElement ( 'grid_original' )->toHTML () . '</td></tr>';
		echo '<tr><td>' . $this->getElement ( 'grid_process' )->getLabel () . '</td><td colspan="3">' . $this->getElement ( 'grid_process' )->toHTML () . '</td></tr>';
	}
	function createFormPeriod($projectName) {
		$per = new period ();
		$per_select = $per->chargeForm ( $this, 'period', 'Period', $projectName );
		$this->addElement ( $per_select );
	}
	function createFormProject($i) {
		$proj = new project ();
		$proj_select = $proj->chargeForm ( $this, 'project_' . $i, 'Project ' . ($i + 1) );
		$this->addElement ( $proj_select );
	}
	function createFormDataPolicy() {
		$dp = new data_policy ();
		$dp_select = $dp->chargeForm ( $this, 'data_policy', 'Data policy' );
		$this->addElement ( $dp_select );
		$this->addElement ( 'text', 'new_data_policy', 'new data policy' );
	}
	function createFormDatabase() {
		$db = new database ();
		$db_select = $db->chargeForm ( $this, 'database', 'Database' );
		$this->addElement ( $db_select );
		$this->addElement ( 'text', 'new_database', 'new database' );
		$this->addElement ( 'text', 'new_db_url', 'Database url' );
	}
	function createFormDataFormat($i) {
		$dformat = new data_format ();
		$dformat_select = $dformat->chargeForm ( $this, 'data_format_' . $i, 'Data format ' . ($i + 1), $i );
		$this->addElement ( $dformat_select );
		$this->addElement ( 'text', 'new_data_format_' . $i, 'new data format: ' );
	}
	function createFormOrganisme($indice) {
		$org = new organism ();
		$org_select = $org->chargeForm ( $this, 'organism_' . $indice, 'Contact ' . ($indice + 1) . ' organization', $indice );
		$this->addElement ( $org_select );
		$attrs = '';
		$this->addElement ( 'text', 'org_sname_' . $indice, 'New organization short name: ', $attrs );
		$this->addElement ( 'text', 'org_fname_' . $indice, 'Full name', $attrs );
		$this->addElement ( 'text', 'org_url_' . $indice, 'URL', $attrs );
	}
	function addPi() {
		$this->createFormPersonne ( $this->dataset->nbPis - 1 );
	}
	function addFormat() {
		$this->createFormDataFormat ( $this->dataset->nbFormats - 1 );
	}
	function addProjet() {
		$this->createFormProject ( $this->dataset->nbProj - 1 );
	}
	function createFormPersonne($indice) {
		$pers = new personne ();
		$pers_select = $pers->chargeForm ( $this, 'pi_' . $indice, 'Name', $indice );
		$this->addElement ( $pers_select );
		$attrs = '';
		$this->addElement ( 'text', 'pi_name_' . $indice, 'new (lastname firstname): ' );
		$this->applyFilter ( 'pi_name_' . $indice, 'trim' );
		$this->addElement ( 'text', 'email1_' . $indice, 'email1' );
		$this->addElement ( 'text', 'email2_' . $indice, 'email2' );
		$ct = new contact_type ();
		$ct_select = $ct->chargeForm ( $this, 'contact_type_' . $indice, 'Contact type' );
		$this->addElement ( $ct_select );
		$this->createFormOrganisme ( $indice );
	}
	function createFormSiteBoundings($i) {
		$this->addElement ( 'text', 'west_bound_' . $i, 'West bounding coordinate (°)' );
		$this->addElement ( 'text', 'east_bound_' . $i, 'East bounding coordinate (°)' );
		$this->addElement ( 'text', 'north_bound_' . $i, 'North bounding coordinate (°)' );
		$this->addElement ( 'text', 'south_bound_' . $i, 'South bounding coordinate (°)' );
		$this->addElement ( 'text', 'place_alt_min_' . $i, 'Altitude min (m)' );
		$this->addElement ( 'text', 'place_alt_max_' . $i, 'Altitude max (m)' );
	}
	function createFormGeoCoverage($simpleVersion = false) {
		$area = new place ();
		$area_select = $area->chargeFormRegion ( $this, 'area', 'Area name', $simpleVersion );
		$this->addElement ( $area_select );
		$this->addElement ( 'text', 'new_area', 'Area name' );
		$this->applyFilter ( 'new_area', 'trim' );
		$this->createFormSiteBoundings ( 0 );
	}
	function initFormGeoCoverage() {
		if (isset ( $this->dataset->sites ) && ! empty ( $this->dataset->sites )) {
			if (isset ( $this->dataset->sites [0] ) && ! empty ( $this->dataset->sites [0] )) {
				$this->getElement ( 'area' )->setSelected ( $this->dataset->sites [0]->place_id );
				$this->getElement ( 'new_area' )->setValue ( $this->dataset->sites [0]->place_name );				
				$this->initFormSiteBoundings ( 0 );
			}
		}
	}
	function saveFormGeoCoverage() {
		$this->dataset->sites = array ();
		$this->dataset->sites [0] = new place ();
		
		$this->dataset->sites [0]->place_id = $this->exportValue ( 'area' );
		$this->dataset->sites [0]->place_name = $this->exportValue ( 'new_area' );
		$this->dataset->sites [0]->gcmd_plateform_keyword = new gcmd_plateform_keyword ();
		$this->dataset->sites [0]->gcmd_plateform_keyword = $this->dataset->sites [0]->gcmd_plateform_keyword->getByName ( "Geographic Regions" );
		$this->dataset->sites [0]->gcmd_plat_id = & $this->dataset->sites [0]->gcmd_plateform_keyword->gcmd_plat_id;
		$this->saveFormSiteBoundings ( 0 );
		
		if (empty ( $this->dataset->sites [0]->place_name )) {
			$this->dataset->sites [0]->place_id = - 1;
		}
	}
	function addValidationRulesGeoCoverage() {
		$this->addRule ( 'area', 'Coverage: area name is required', 'couple_not_null', array (
				$this,
				'new_area' 
		) );
		if (isset ( $this->dataset->sites [0] ) && ! empty ( $this->dataset->sites [0] ) && $this->dataset->sites [0]->place_id > 0) {
			$this->disableElement ( 'new_area' );
			$this->disableSiteBoundings ( 0 );
		} else {
			$this->addRule ( 'new_area', 'Coverage: This area name is already present in the database. Select it in the drop-down list or chose another name.', 'existe', array (
					'place',
					'place_name' 
			) );
			$this->addValidationRulesSiteBoundings ( 0, 'Coverage' );
		}
	}
	function createFormResolution($i = -1) {
		if ($i == - 1) {
			$suffix = '';
		} else {
			$suffix = '_' . $i;
		}
		$this->addElement ( 'text', 'sensor_resol_temp' . $suffix, 'Temporal' );
		$this->applyFilter ( 'sensor_resol_temp' . $suffix, 'trim' );
		$this->addElement ( 'text', 'sensor_vert_resolution' . $suffix, 'Vertical' );
		$this->applyFilter ( 'sensor_vert_resolution' . $suffix, 'trim' );
		$this->addElement ( 'text', 'sensor_lat_resolution' . $suffix, 'Latitude' );
		$this->applyFilter ( 'sensor_lat_resolution' . $suffix, 'trim' );
		$this->addElement ( 'text', 'sensor_lon_resolution' . $suffix, 'Longitude' );
		$this->applyFilter ( 'sensor_lon_resolution' . $suffix, 'trim' );
	}
	function saveFormResolution($i = -1) {
		if ($i == - 1) {
			$this->dataset->dats_sensors [0]->sensor_resol_temp = $this->exportValue ( 'sensor_resol_temp' );
			$this->dataset->dats_sensors [0]->sensor_vert_resolution = $this->exportValue ( 'sensor_vert_resolution' );
			$this->dataset->dats_sensors [0]->sensor_lat_resolution = $this->exportValue ( 'sensor_lat_resolution' );
			$this->dataset->dats_sensors [0]->sensor_lon_resolution = $this->exportValue ( 'sensor_lon_resolution' );
		} else {
			$this->dataset->dats_sensors [$i]->sensor_resol_temp = $this->exportValue ( 'sensor_resol_temp_' . $i );
			$this->dataset->dats_sensors [$i]->sensor_vert_resolution = $this->exportValue ( 'sensor_vert_resolution_' . $i );
			$this->dataset->dats_sensors [$i]->sensor_lat_resolution = $this->exportValue ( 'sensor_lat_resolution_' . $i );
			$this->dataset->dats_sensors [$i]->sensor_lon_resolution = $this->exportValue ( 'sensor_lon_resolution_' . $i );
		}
	}
	function initFormResolution($i = -1) {
		if ($i == - 1) {
			if (isset ( $this->dataset->dats_sensors [0] ) && ! empty ( $this->dataset->dats_sensors [0] )) {
				$this->getElement ( 'sensor_resol_temp' )->setValue ( $this->dataset->dats_sensors [0]->sensor_resol_temp );
				$this->getElement ( 'sensor_vert_resolution' )->setValue ( $this->dataset->dats_sensors [0]->sensor_vert_resolution );
				$this->getElement ( 'sensor_lat_resolution' )->setValue ( $this->dataset->dats_sensors [0]->sensor_lat_resolution );
				$this->getElement ( 'sensor_lon_resolution' )->setValue ( $this->dataset->dats_sensors [0]->sensor_lon_resolution );
			}
		} else {
			$this->getElement ( 'sensor_resol_temp_' . $i )->setValue ( $this->dataset->dats_sensors [$i]->sensor_resol_temp );
			$this->getElement ( 'sensor_vert_resolution_' . $i )->setValue ( $this->dataset->dats_sensors [$i]->sensor_vert_resolution );
			$this->getElement ( 'sensor_lat_resolution_' . $i )->setValue ( $this->dataset->dats_sensors [$i]->sensor_lat_resolution );
			$this->getElement ( 'sensor_lon_resolution_' . $i )->setValue ( $this->dataset->dats_sensors [$i]->sensor_lon_resolution );
		}
	}
	function addValidationRulesResolution($prefixMsg = 'Instrument', $i = -1) {
		if ($i == - 1) {
			$suffix = '';
		} else {
			$suffix = '_' . $i;
		}
		$this->addRule ( 'sensor_resol_temp' . $suffix, $prefixMsg . ': Observation frequency exceeds the maximum length allowed (100 characters)', 'maxlength', 100 );
		$this->addRule ( 'sensor_vert_resolution' . $suffix, $prefixMsg . ': Vertical coverage exceeds the maximum length allowed (100 characters)', 'maxlength', 100 );
		$this->addRule ( 'sensor_lat_resolution' . $suffix, $prefixMsg . ': Latitude coverage exceeds the maximum length allowed (100 characters)', 'maxlength', 100 );
		$this->addRule ( 'sensor_lon_resolution' . $suffix, $prefixMsg . ': Longitude coverage exceeds the maximum length allowed (100 characters)', 'maxlength', 100 );
	}
	function createFormManufacturer($suffix = '') {
		$man = new manufacturer ();
		$man_select = $man->chargeForm ( $this, 'manufacturer' . $suffix, 'Manufacturer', $suffix );
		$this->addElement ( $man_select );
		$this->addElement ( 'text', 'new_manufacturer' . $suffix, 'new manufacturer: ' );
		$this->addElement ( 'text', 'new_manufacturer_url' . $suffix, 'Manufacturer web site' );
	}
	function createFormSensorKeyword($suffix = '') {
		$key = new gcmd_instrument_keyword ();
		$key_select = $key->chargeForm ( $this, 'sensor_gcmd' . $suffix, 'Instrument type' );
		$this->addElement ( $key_select );
	}
	function createFormSensorKeywordVaDataset($suffix = '') {
		$key = new gcmd_instrument_keyword ();
		$key_select = $key->chargeFormVadataset ( $this, 'sensor_gcmd' . $suffix, 'Instrument type' );
		$this->addElement ( $key_select );
	}
	function initFormBase() {
		
		// DATASET
		$this->getElement ( 'dats_id' )->setValue ( $this->dataset->dats_id );
		$this->getElement ( 'dats_title' )->setValue ( $this->dataset->dats_title );
		$this->getElement ( 'dats_abstract' )->setValue ( $this->dataset->dats_abstract );
		$this->getElement ( 'dats_purpose' )->setValue ( $this->dataset->dats_purpose );
		$this->getElement ( 'dats_use_constraints' )->setValue ( $this->dataset->dats_use_constraints );
		$this->getElement ( 'dats_reference' )->setValue ( $this->dataset->dats_reference );
		$this->getElement ( 'dats_date_begin' )->setValue ( $this->dataset->dats_date_begin );
		$this->getElement ( 'dats_date_end' )->setValue ( $this->dataset->dats_date_end );
		$this->getElement ( 'dats_doi' )->setValue ( $this->dataset->dats_doi );
		$this->getElement ( 'dats_version' )->setValue ( $this->dataset->dats_version );
		
		// Contacts
		if (isset ( $this->dataset->originators ) && ! empty ( $this->dataset->originators )) {
			for($i = 0; $i < count ( $this->dataset->originators ); $i ++) {
				$this->initFormPersonne ( $i );
			}
		}
		
		// PROJECT
		$indice = 0;
		if (isset ( $this->dataset->projects )) {
			foreach ( $this->dataset->projects as $proj ) {
				if (isset ( $proj )) {
					if (isset ( $proj->pro_project_id ) && ! empty ( $proj->pro_project_id )) {
						$parent = $proj->pro_project_id;
						$tmp_pro = new project ();
						$tmp_pro = $tmp_pro->getById ( $parent );
						if (isset ( $tmp_pro->pro_project_id ) && ! empty ( $tmp_pro->pro_project_id )) {
							$p1 = $tmp_pro->pro_project_id;
							$p2 = $tmp_pro->project_id;
							$p3 = $proj->project_id;
						} else {
							$p1 = $tmp_pro->project_id;
							$p2 = $proj->project_id;
							$p3 = 0;
						}
					} else {
						$p1 = $proj->project_id;
						$p2 = 0;
						$p3 = 0;
					}
					$this->setDefaults ( array (
							'project_' . $indice ++ => array (
									$p1,
									$p2,
									$p3 
							) 
					) );
				}
			}
		}
		
		// DATABASE
		if (isset ( $this->dataset->database ) && ! empty ( $this->dataset->database )) {
			$this->getElement ( 'database' )->setSelected ( $this->dataset->database_id );
			$this->getElement ( 'new_database' )->setValue ( $this->dataset->database->database_name );
			$this->getElement ( 'new_db_url' )->setValue ( $this->dataset->database->database_url );
		}
		
		// DATA POLICY
		if (isset ( $this->dataset->data_policy ) && ! empty ( $this->dataset->data_policy )) {
			$this->getElement ( 'data_policy' )->setSelected ( $this->dataset->data_policy->data_policy_id );
			$this->getElement ( 'new_data_policy' )->setValue ( $this->dataset->data_policy->data_policy_name );
		}
		
		// DATA FORMATS
		if (isset ( $this->dataset->data_formats ) && ! empty ( $this->dataset->data_formats )) {
			for($i = 0; $i < count ( $this->dataset->data_formats ); $i ++) {
				if (isset ( $this->dataset->data_formats [$i] ) && ! empty ( $this->dataset->data_formats [$i] )) {
					$this->getElement ( 'data_format_' . $i )->setSelected ( $this->dataset->data_formats [$i]->data_format_id );
					$this->getElement ( 'new_data_format_' . $i )->setValue ( $this->dataset->data_formats [$i]->data_format_name );
				}
			}
		}
	}
	function initFormPersonne($i) {
		$this->getElement ( 'pi_' . $i )->setSelected ( $this->dataset->originators [$i]->pers_id );
		$this->getElement ( 'pi_name_' . $i )->setValue ( $this->dataset->originators [$i]->pers_name );
		$this->getElement ( 'email1_' . $i )->setValue ( $this->dataset->originators [$i]->pers_email_1 );
		$this->getElement ( 'email2_' . $i )->setValue ( $this->dataset->originators [$i]->pers_email_2 );
		$this->getElement ( 'organism_' . $i )->setSelected ( $this->dataset->originators [$i]->organism->org_id );
		$this->getElement ( 'org_sname_' . $i )->setValue ( $this->dataset->originators [$i]->organism->org_sname );
		$this->getElement ( 'org_fname_' . $i )->setValue ( $this->dataset->originators [$i]->organism->org_fname );
		$this->getElement ( 'org_url_' . $i )->setValue ( $this->dataset->originators [$i]->organism->org_url );
		
		$this->getElement ( 'contact_type_' . $i )->setSelected ( $this->dataset->originators [$i]->contact_type_id );
	}
	
	function initFormVariable($i, $suffix) {
		if (isset ( $this->dataset->dats_variables [$i]->variable ) && ! empty ( $this->dataset->dats_variables [$i]->variable ) && ($this->dataset->dats_variables [$i]->variable->var_id > 0)) {
			$this->getElement ( 'var_id_' . $suffix )->setValue ( $this->dataset->dats_variables [$i]->variable->var_id );
			$this->getElement ( 'new_variable_' . $suffix )->setValue ( $this->dataset->dats_variables [$i]->variable->var_name );
			
			if (isset ( $this->dataset->dats_variables [$i]->variable->gcmd ) && ! empty ( $this->dataset->dats_variables [$i]->variable->gcmd )) {
				$table = array ();
				$gcmd = $this->dataset->dats_variables [$i]->variable->gcmd;
				for($j = 4; $j >= 1; $j --) {
					if ($gcmd->gcmd_level == $j) {
						$table [$j - 1] = $gcmd->gcmd_id;
						$gcmd = $gcmd->gcmd_parent;
					} else
						$table [$j - 1] = 0;
				}
				ksort ( $table );
				
				$this->getElement ( 'gcmd_science_key_' . $suffix )->setValue ( $table );
			}
		}
		
		if (isset ( $this->dataset->dats_variables [$i]->unit ) && ! empty ( $this->dataset->dats_variables [$i]->unit ) && ($this->dataset->dats_variables [$i]->unit->unit_id > 0)) {
			$this->getElement ( 'unit_' . $suffix )->setSelected ( $this->dataset->dats_variables [$i]->unit->unit_id );
			$this->getElement ( 'new_unit_' . $suffix )->setValue ( $this->dataset->dats_variables [$i]->unit->unit_name );
			$this->getElement ( 'new_unit_code_' . $suffix )->setValue ( $this->dataset->dats_variables [$i]->unit->unit_code );
		}
		if (isset ( $this->dataset->dats_variables [$i]->methode_acq ) && ! empty ( $this->dataset->dats_variables [$i]->methode_acq ))
			$this->getElement ( 'methode_acq_' . $suffix )->setValue ( $this->dataset->dats_variables [$i]->methode_acq );
		if (isset ( $this->dataset->dats_variables [$i]->date_min ) && ! empty ( $this->dataset->dats_variables [$i]->date_min ))
			$this->getElement ( 'var_date_min_' . $suffix )->setValue ( $this->dataset->dats_variables [$i]->date_min );
		if (isset ( $this->dataset->dats_variables [$i]->date_max ) && ! empty ( $this->dataset->dats_variables [$i]->date_max ))
			$this->getElement ( 'var_date_max_' . $suffix )->setValue ( $this->dataset->dats_variables [$i]->date_max );
		if (isset ( $this->dataset->dats_variables [$i]->level_type ) && ! empty ( $this->dataset->dats_variables [$i]->level_type ))
			$this->getElement ( 'level_type_' . $suffix )->setValue ( $this->dataset->dats_variables [$i]->level_type );
		if (isset ( $this->dataset->dats_variables [$i]->variable->sensor_precision ) && ! empty ( $this->dataset->dats_variables [$i]->variable->sensor_precision ))
			$this->getElement ( 'sensor_precision_' . $suffix )->setValue ( $this->dataset->dats_variables [$i]->variable->sensor_precision );
	}
	function saveFormBase() {
		$this->dataset->dats_id = $this->exportValue ( 'dats_id' );
		$this->dataset->dats_title = $this->exportValue ( 'dats_title' );
		
		$this->dataset->dats_abstract = $this->exportValue ( 'dats_abstract' );
		$this->dataset->dats_purpose = $this->exportValue ( 'dats_purpose' );
		$this->dataset->dats_use_constraints = $this->exportValue ( 'dats_use_constraints' );
		$this->dataset->dats_reference = $this->exportValue ( 'dats_reference' );
		$this->dataset->dats_date_begin = $this->exportValue ( 'dats_date_begin' );
		$this->dataset->dats_date_end = $this->exportValue ( 'dats_date_end' );
		$this->dataset->dats_doi = $this->exportValue ( 'dats_doi' );
		
		$this->dataset->dats_version = $this->exportValue ( 'dats_version' );
		
		$this->dataset->dats_creator = $this->user->mail;
		
		// CONTACTS
		$this->dataset->originators = array ();
		for($i = 0; $i < $this->dataset->nbPis; $i ++) {
			$this->saveFormPersonne ( $i );
		}
		$this->dataset->organism = & $this->dataset->originators [0]->organism;
		$this->dataset->org_id = & $this->dataset->organism->org_id;
		
		// PROJECT
		$this->dataset->projects = array ();
		$j = 0;
		for($i = 0; $i < $this->dataset->nbProj; $i ++) {
			$proj = $this->exportValue ( 'project_' . $i );
			if ($proj [2] == 0) {
				if ($proj [1] == 0) {
					$projectId = $proj [0];
				} else {
					$projectId = $proj [1];
				}
			} else {
				$projectId = $proj [2];
			}
			
			if (isset ( $projectId ) && $projectId != 0) {
				$this->dataset->projects [$j] = new project ();
				$this->dataset->projects [$j] = $this->dataset->projects [$j]->getById ( $projectId );
				$j ++;
			}
		}
		
		// DATABASE
		$this->dataset->database = new database ();
		$this->dataset->database->database_id = $this->exportValue ( 'database' );
		$this->dataset->database->database_name = $this->exportValue ( 'new_database' );
		$this->dataset->database->database_url = $this->exportValue ( 'new_db_url' );
		if (empty ( $this->dataset->database->database_name )) {
			$this->dataset->database->database_id = - 1;
		}
		
		$this->dataset->database_id = & $this->dataset->database->database_id;
		
		// DATA_POLICY
		$this->dataset->data_policy = new data_policy ();
		$this->dataset->data_policy->data_policy_id = $this->exportValue ( 'data_policy' );
		$this->dataset->data_policy->data_policy_name = $this->exportValue ( 'new_data_policy' );
		if (empty ( $this->dataset->data_policy->data_policy_name )) {
			$this->dataset->data_policy->data_policy_id = - 1;
		}
		$this->dataset->data_policy_id = & $this->dataset->data_policy->data_policy_id;
		
		// DATA_FORMAT
		$this->dataset->data_formats = array ();
		for($i = 0; $i < $this->dataset->nbFormats; $i ++) {
			$this->dataset->data_formats [$i] = new data_format ();
			$this->dataset->data_formats [$i]->data_format_id = $this->exportValue ( 'data_format_' . $i );
			$this->dataset->data_formats [$i]->data_format_name = $this->exportValue ( 'new_data_format_' . $i );
			if (empty ( $this->dataset->data_formats [$i]->data_format_name )) {
				$this->dataset->data_formats [$i]->data_format_id = - 1;
			}
		}
	}
	function saveFormPersonne($i) {
		$this->dataset->originators [$i] = new personne ();
		$pers_id = $this->exportValue ( 'pi_' . $i );
		$pers_name = $this->exportValue ( 'pi_name_' . $i );
		if (empty ( $pers_name )) {
			$pers_id = - 1;
		}
		$this->dataset->originators [$i]->pers_id = $pers_id;
		$this->dataset->originators [$i]->pers_name = $pers_name;
		$this->dataset->originators [$i]->pers_email_1 = $this->exportValue ( 'email1_' . $i );
		$this->dataset->originators [$i]->pers_email_2 = $this->exportValue ( 'email2_' . $i );
		;
		
		$this->dataset->originators [$i]->organism = new organism ();
		$this->dataset->originators [$i]->organism->org_id = $this->exportValue ( 'organism_' . $i );
		$this->dataset->originators [$i]->organism->org_sname = $this->exportValue ( 'org_sname_' . $i );
		$this->dataset->originators [$i]->organism->org_fname = $this->exportValue ( 'org_fname_' . $i );
		$this->dataset->originators [$i]->organism->org_url = $this->exportValue ( 'org_url_' . $i );
		
		$this->dataset->originators [$i]->contact_type_id = $this->exportValue ( 'contact_type_' . $i );
		
		$this->dataset->originators [$i]->org_id = &  $this->dataset->originators [$i]->organism->org_id;
	}
	
	function saveFormVariables($nb, $flag = 0, $suffix = '', $incr = 0) {
		$dataset = & $this->dataset;
		
		for($i = 0; $i < $nb; $i ++) {
			$dataset->dats_variables [$i + $incr] = new dats_var ();
			$dataset->dats_variables [$i + $incr]->variable = new variable ();
			
			$dataset->dats_variables [$i + $incr]->variable->var_id = $this->exportValue ( 'var_id_' . $suffix . $i );
			$dataset->dats_variables [$i + $incr]->variable->var_name = $this->exportValue ( 'new_variable_' . $suffix . $i );
			
			$gcmd_ids = $this->exportValue ( 'gcmd_science_key_' . $suffix . $i );
			$gcmd_id = 0;
			for($j = 3; $j >= 0; $j --) {
				
				if (isset ( $gcmd_ids [$j] ) && $gcmd_ids [$j] > 0) {
					$gcmd_id = $gcmd_ids [$j];
					break;
				}
			}
						
			if ($gcmd_id > 0) {
				$dataset->dats_variables [$i + $incr]->variable->gcmd = new gcmd_science_keyword ();
				$dataset->dats_variables [$i + $incr]->variable->gcmd = $dataset->dats_variables [$i + $incr]->variable->gcmd->getById ( $gcmd_id );
				$dataset->dats_variables [$i + $incr]->variable->gcmd_id = & $dataset->dats_variables [$i + $incr]->variable->gcmd->gcmd_id;
			} else {
				if (empty ( $dataset->dats_variables [$i + $incr]->variable->var_name )) {
					$dataset->dats_variables [$i + $incr]->variable->var_id = - 1;
				}
			}
			
			$dataset->dats_variables [$i + $incr]->var_id = & $dataset->dats_variables [$i + $incr]->variable->var_id;
						
			$dataset->dats_variables [$i + $incr]->unit = new unit ();
			$dataset->dats_variables [$i + $incr]->unit->unit_id = $this->exportValue ( 'unit_' . $suffix . $i );
			$dataset->dats_variables [$i + $incr]->unit->unit_name = $this->exportValue ( 'new_unit_' . $suffix . $i );
			$dataset->dats_variables [$i + $incr]->unit->unit_code = $this->exportValue ( 'new_unit_code_' . $suffix . $i );
			
			if (empty ( $dataset->dats_variables [$i + $incr]->unit->unit_name )) {
				$dataset->dats_variables [$i + $incr]->unit->unit_id = - 1;
			}
			$dataset->dats_variables [$i + $incr]->unit_id = & $dataset->dats_variables [$i + $incr]->unit->unit_id;
			
			$dataset->dats_variables [$i + $incr]->methode_acq = $this->exportValue ( 'methode_acq_' . $suffix . $i );
			$dataset->dats_variables [$i + $incr]->date_min = $this->exportValue ( 'var_date_min_' . $suffix . $i );
			$dataset->dats_variables [$i + $incr]->date_max = $this->exportValue ( 'var_date_max_' . $suffix . $i );
			$dataset->dats_variables [$i + $incr]->flag_param_calcule = $flag;
			
			$dataset->dats_variables [$i + $incr]->level_type = $this->exportValue ( 'level_type_' . $suffix . $i );
			
			$dataset->dats_variables [$i + $incr]->variable->sensor_precision = $this->exportValue ( 'sensor_precision_' . $suffix . $i );
			
			// TODO tester
			$dataset->dats_sensors [0]->sensor->sensor_vars [$i + $incr] = new sensor_var ();
			$dataset->dats_sensors [0]->sensor->sensor_vars [$i + $incr]->sensor_precision = & $dataset->dats_variables [$i + $incr]->variable->sensor_precision;
			$dataset->dats_sensors [0]->sensor->sensor_vars [$i + $incr]->variable = & $dataset->dats_variables [$i + $incr]->variable;
		}
	}
	function getErrorMessage($elementName) {
		$errorMsg = $this->getElementError ( $elementName );
		if (! isset ( $errorMsg ) || empty ( $errorMsg )) {
			return '';
		}
		
		$offset = strpos ( $errorMsg, ':' );
		if ($offset !== false) {
			return substr ( $errorMsg, $offset + 2 ) . '<br>';
		} else {
			return $errorMsg . '<br>';
		}
	}
	function getErrorMessages($elementNames) {
		$result = '';
		foreach ( $elementNames as $elementName ) {
			$result .= $this->getErrorMessage ( $elementName );
		}
		return $result;
	}
	function displayErrors($elementNames) {
		$messages = $this->getErrorMessages ( $elementNames );
		if (isset ( $messages ) && ! empty ( $messages )) {
			echo '<tr><td colspan="4"><font color="red" >' . $messages . '</font></td></tr>';
		}
	}
	function displayErrorsContact($i) {
		$this->displayErrors ( array (
				'pi_' . $i,
				'pi_name_' . $i,
				'email1_' . $i,
				'email2_' . $i,
				'organism_' . $i,
				'org_fname_' . $i,
				'org_sname_' . $i,
				'org_url_' . $i 
		) );
	}
	function displayErrorsParams($suffix) {
		$this->displayErrors ( array (
				'new_variable_' . $suffix,
				'unit_' . $suffix,
				'new_unit_' . $suffix,
				'new_unit_code_' . $suffix,
				'sensor_precision_' . $suffix,
				'var_date_min_' . $suffix,
				'var_date_max_' . $suffix,
				'methode_acq_' . $suffix 
		) );
	}
	function displayErrorsSite($i) {
		$this->displayErrors ( array (
				'gcmd_plat_key_' . $i,
				'place_' . $i,
				'new_place_' . $i,
				'west_bound_' . $i,
				'east_bound_' . $i,
				'north_bound_' . $i,
				'south_bound_' . $i,
				'place_alt_min_' . $i,
				'place_alt_max_' . $i,
				'sensor_environment_' . $i 
		) );
	}
	function displayErrorsGeneralInfo() {
		$this->displayErrors ( array (
				'dats_title',
				'dats_date_begin',
				'dats_date_end' 
		) );
	}
	function displayErrorsUseInfo() {
		$elementNames = array (
				'new_data_policy',
				'new_database',
				'new_db_url' 
		);
		for($i = 0; $i < $this->dataset->nbFormats; $i ++) {
			$elementNames [] = 'data_format_' . $i;
		}
		$this->displayErrors ( $elementNames );
	}
	function getHideShow($rowsName, $render = false) {
		if ($render)
			return '<a name="' . $rowsName . '_s" style="display: none;" onclick="displayRows(\'' . $rowsName . '\')" >&nbsp;[show]</a><a name="' . $rowsName . '" onclick="hideRows(\'' . $rowsName . '\')" >&nbsp;[hide]</a>';
		else
			return '';
	}
	function displayDataDescrForm($withTitle = false, $withPurpose = true) {
		if ($withTitle)
			echo '<tr><th colspan="4" align="center"><a name="a_descr" ></a><b>Data description</b>' . $this->getHideShow ( 'gen_desc' ) . '</td></tr>';
		echo '<tr name="gen_desc"><td>' . $this->getElement ( 'dats_abstract' )->getLabel () . '</td><td colspan="3">' . $this->getElement ( 'dats_abstract' )->toHTML () . '</td></tr>';
		if ($withPurpose)
			echo '<tr name="gen_desc"><td>' . $this->getElement ( 'dats_purpose' )->getLabel () . '</td><td colspan="3">' . $this->getElement ( 'dats_purpose' )->toHTML () . '</td></tr>';
		echo '<tr name="gen_desc"><td>' . $this->getElement ( 'dats_reference' )->getLabel () . '</td><td colspan="3">' . $this->getElement ( 'dats_reference' )->toHTML () . '</td></tr>';
	}
	function displayPeriodForm() {
		echo '<tr><td rowspan="2">' . $this->getElement ( 'period' )->getLabel () . '</td><td rowspan="2">' . $this->getElement ( 'period' )->toHTML () . '</td><td>' . $this->getElement ( 'dats_date_begin' )->getLabel () . '</td><td>' . $this->getElement ( 'dats_date_begin' )->toHTML () . "</td></tr>";
		echo '<tr><td>' . $this->getElement ( 'dats_date_end' )->getLabel () . '</td><td>' . $this->getElement ( 'dats_date_end' )->toHTML () . '<br>' . $this->getElement ( 'dats_date_end_not_planned' )->toHTML () . '&nbsp;' . $this->getElement ( 'dats_date_end_not_planned' )->getLabel () . '</td></tr>';
	}

	function displayPersonForm($i) {
		echo '<tr><td><font color="#467AA7">' . $this->getElement ( 'contact_type_' . $i )->getLabel () . '</font>';
		echo '</td><td colspan="3">' . $this->getElement ( 'contact_type_' . $i )->toHTML ();
		echo '</td></tr>';
		if ($i == 0) {
			echo '<tr><td><font color="#467AA7">' . $this->getElement ( 'pi_' . $i )->getLabel () . '</font></td><td colspan="3">' . $this->getElement ( 'pi_' . $i )->toHTML ();
			echo '<font color="#467AA7">&nbsp;&nbsp;or add ' . $this->getElement ( 'pi_name_' . $i )->getLabel () . '</font>' . $this->getElement ( 'pi_name_' . $i )->toHTML () . '</td></tr>';
			echo '<tr><td><font color="#467AA7">' . $this->getElement ( 'email1_' . $i )->getLabel () . '</font></td><td>' . $this->getElement ( 'email1_' . $i )->toHTML () . '</td>';
		} else {
			echo '<tr><td>' . $this->getElement ( 'pi_' . $i )->getLabel () . '</td><td colspan="3">' . $this->getElement ( 'pi_' . $i )->toHTML ();
			echo '&nbsp;&nbsp;or add ' . $this->getElement ( 'pi_name_' . $i )->getLabel () . '' . $this->getElement ( 'pi_name_' . $i )->toHTML () . '</td></tr>';
			echo '<tr><td>' . $this->getElement ( 'email1_' . $i )->getLabel () . '</td><td>' . $this->getElement ( 'email1_' . $i )->toHTML () . '</td>';
		}
		echo '<td>' . $this->getElement ( 'email2_' . $i )->getLabel () . '</td><td>' . $this->getElement ( 'email2_' . $i )->toHTML () . '</td></tr>';
		
		echo '<tr><td><font color="#467AA7">' . $this->getElement ( 'organism_' . $i )->getLabel () . '</font></td><td colspan="3">' . $this->getElement ( 'organism_' . $i )->toHTML ();
		
		echo '&nbsp;&nbsp;or add ' . $this->getElement ( 'org_sname_' . $i )->getLabel () . '' . $this->getElement ( 'org_sname_' . $i )->toHTML () . '</td></tr>';
		echo '<tr><td>' . $this->getElement ( 'org_fname_' . $i )->getLabel () . '</td><td>' . $this->getElement ( 'org_fname_' . $i )->toHTML () . '</td>';
		echo '<td>' . $this->getElement ( 'org_url_' . $i )->getLabel () . '</td><td>' . $this->getElement ( 'org_url_' . $i )->toHTML () . '</td></tr>';
	}
	function displaySiteBoundingsForm($i, $withAlt = true) {
		echo '<tr name="row_site_' . $i . '"><td>' . $this->getElement ( 'west_bound_' . $i )->getLabel () . '</td><td>' . $this->getElement ( 'west_bound_' . $i )->toHTML () . '</td>';
		echo '<td>' . $this->getElement ( 'east_bound_' . $i )->getLabel () . '</td><td>' . $this->getElement ( 'east_bound_' . $i )->toHTML () . '</td></tr>';
		echo '<tr name="row_site_' . $i . '"><td>' . $this->getElement ( 'north_bound_' . $i )->getLabel () . '</td><td>' . $this->getElement ( 'north_bound_' . $i )->toHTML () . '</td>';
		echo '<td>' . $this->getElement ( 'south_bound_' . $i )->getLabel () . '</td><td>' . $this->getElement ( 'south_bound_' . $i )->toHTML () . '</td></tr>';
		if ($withAlt) {
			echo '<tr name="row_site_' . $i . '"><td>' . $this->getElement ( 'place_alt_min_' . $i )->getLabel () . '</td><td>' . $this->getElement ( 'place_alt_min_' . $i )->toHTML () . '</td>';
			echo '<td>' . $this->getElement ( 'place_alt_max_' . $i )->getLabel () . '</td><td>' . $this->getElement ( 'place_alt_max_' . $i )->toHTML () . '</td></tr>';
		}
	}
	function displayDataResolutionForm($simpleVersion = false) {
		echo '<tr><td colspan="4" align="center"><b>Data resolution</b><br></td></tr>';
		echo '<tr><td>' . $this->getElement ( 'sensor_lon_resolution' )->getLabel () . '</td><td>' . $this->getElement ( 'sensor_lon_resolution' )->toHTML () . '</td>';
		echo '<td>' . $this->getElement ( 'sensor_lat_resolution' )->getLabel () . '</td><td>' . $this->getElement ( 'sensor_lat_resolution' )->toHTML () . '</td></tr>';
		
		echo '<tr>';
		if (! $simpleVersion) {
			echo '<td>' . $this->getElement ( 'sensor_vert_resolution' )->getLabel () . '</td><td>' . $this->getElement ( 'sensor_vert_resolution' )->toHTML () . '</td>';
			echo '<td>' . $this->getElement ( 'sensor_resol_temp' )->getLabel () . '</td><td>' . $this->getElement ( 'sensor_resol_temp' )->toHTML () . '</td>';
		}
		if ($simpleVersion) {
			echo '<td>' . $this->getElement ( 'sensor_resol_temp' )->getLabel ();
			echo "&nbsp;<img src='/img/aide-icone-16.png' onmouseout='kill()' onmouseover=\"javascript:bulle('','monthly, weekly, daily, hourly, ...')\" style='border:0px; margin-right:10px;' />";
			echo '</td><td>' . $this->getElement ( 'sensor_resol_temp' )->toHTML () . '</td><td colspan="2" />';
		}
		echo '</tr>';
	}
	function displayGeoCoverageForm($withAlt = true) {
		echo '<tr><td colspan="4" align="center"><b>Geographic Coverage</b></td></tr>';
		echo '<tr><td><font color="#467AA7">' . $this->getElement ( 'area' )->getLabel () . '</font></td><td colspan="3">' . $this->getElement ( 'area' )->toHTML ();
		echo '&nbsp;&nbsp;or add new&nbsp;' . $this->getElement ( 'new_area' )->toHTML () . '</td></tr>';
		
		$this->displaySiteBoundingsForm ( 0, $withAlt );
	}
	function displayParamForm($i, $withPrecision = true, $withDates = false, $withLevelType = false, $withMethod = true) {
		echo '<tr><td>' . $this->getElement ( 'gcmd_science_key_' . $i )->getLabel () . '</td><td colspan="3">' . $this->getElement ( 'gcmd_science_key_' . $i )->toHTML () . '</td></tr>';
		echo '<tr><td colspan="2">' . $this->getElement ( 'new_variable_' . $i )->getLabel () . '</td><td colspan="2">' . $this->getElement ( 'new_variable_' . $i )->toHTML () . '</td></tr>';
		echo '<tr><td>' . $this->getElement ( 'unit_' . $i )->getLabel () . '</td><td colspan="3">' . $this->getElement ( 'unit_' . $i )->toHTML ();
		echo '&nbsp;&nbsp;or add ' . $this->getElement ( 'new_unit_' . $i )->getLabel () . '' . $this->getElement ( 'new_unit_' . $i )->toHTML ();
		echo $this->getElement ( 'new_unit_code_' . $i )->getLabel () . '' . $this->getElement ( 'new_unit_code_' . $i )->toHTML () . '</td></tr>';
		
		if ($withMethod)
			echo '<tr><td>' . $this->getElement ( 'methode_acq_' . $i )->getLabel () . '</td><td colspan="3">' . $this->getElement ( 'methode_acq_' . $i )->toHTML () . '</td></tr>';
		
		if ($withDates) {
			echo '<tr><td>' . $this->getElement ( 'var_date_min_' . $i )->getLabel () . '</td><td>' . $this->getElement ( 'var_date_min_' . $i )->toHTML () . '</td>';
			echo '<td>' . $this->getElement ( 'var_date_max_' . $i )->getLabel () . '</td><td>' . $this->getElement ( 'var_date_max_' . $i )->toHTML () . '</td></tr>';
		}
		
		if ($withPrecision) {
			echo '<tr><td>' . $this->getElement ( 'sensor_precision_' . $i )->getLabel () . '</td><td>' . $this->getElement ( 'sensor_precision_' . $i )->toHTML () . '</td><td colspan="2"></td></tr>';
		}
		
		if ($withLevelType) {
			echo '<tr><td>' . $this->getElement ( 'level_type_' . $i )->getLabel () . '</td><td>' . $this->getElement ( 'level_type_' . $i )->toHTML () . '</td><td colspan="2"></td></tr>';
		}
	}
	function displayFormBegin($frmname, $simpleVersion = false, $multipart = false) {
		echo '<div id="aide"></div>';
		
		echo '<div id="test"></div>';
		
		echo '<div id="errors" color="red"></div><br>';
		
		if (strpos ( $_SERVER ['REQUEST_URI'], '?datsId' )) {
			$reqUri = substr ( $_SERVER ['REQUEST_URI'], 0, strpos ( $_SERVER ['REQUEST_URI'], '?datsId' ) );
		} else {
			$reqUri = $_SERVER ['REQUEST_URI'];
		}
		
		if ($multipart) {
			echo "<form action='$reqUri' method='post' name='$frmname' id='$frmname' enctype='multipart/form-data' >";
		} else {
			echo '<form action="' . $reqUri . '" method="post" name="' . $frmname . '" id="' . $frmname . '" >';
		}
		
		echo '<SCRIPT LANGUAGE="Javascript" SRC="/js/functions.js"> </SCRIPT>';
		
		echo '<script language="javascript" type="text/javascript">';
		echo 'var adroite = false;';
		echo 'activate_mousemove(get_mouse2);';
		echo '</script>';
		
		echo $this->getElement ( 'dats_id' )->toHTML ();
		echo '<table><tr><td colspan="4"><font color="#467AA7">';
		
		if ($simpleVersion) {
			echo 'If you have interest in specific data not yet inventoried in the database, please describe your request using the following form. Fields in blue are mandatory.';
		} else {
			echo 'Required fields are in blue';
		}
		echo '</font></td></tr>';
		
		echo '<tr><td colspan="4" align="center"><a href="' . $reqUri . '?datsId=-10">Reset</a></td></tr>';
	}
	function displayFormEnd() {
		echo '<tr><td colspan="4" align="center">' . $this->getElement ( 'bouton_save' )->toHTML () . '</td></tr></table>';
		echo '</form>';
	}
}

?>
