<?php

require_once ("forms/base_form.php");
require_once ("forms/validation.php");
require_once ("bd/sensor_place.php");

class va_dataset_form extends base_form {
	
	var $dats_sensors;
	var $sites;
	var $nbModForm;
	var $nbSatForm;
	var $nbInstruFrom;
	
	//================================================================= Creation Functions ===========================================================	
	
	function createForm(){
		$this->createFormBase();
		$this->addElement('reset','reset','Reset');			
		$tab['seeker_0'] = '0';
		$this->setDefaults($tab);
		$this->addElement('submit','bouton_add_pi','Add a contact',array('onclick' => "document.getElementById('frmvadataset').action += '#a_contact'"));
		$this->createVaFormResolution();
		$this->createFormGeoCoverage();
		$this->createFormGrid();	
		//Required format
		$dformat = new data_format;
		$dformat_select = $dformat->chargeFormDestFormat($this,'required_data_format','Required data format','NetCDF');
		$this->addElement($dformat_select);
		$this->getElement('organism_0')->setLabel("Organism short name");
		$this->getElement('project_0')->setLabel("Useful in the framework of");
		$this->getElement('dats_abstract')->setLabel("Abstract ");
		$this->getElement('dats_purpose')->setLabel("Purpose");
		$this->getElement('database')->setLabel("Data center");
		$this->getElement('new_db_url')->setLabel("Data center url");
		$this->getElement('dats_use_constraints')->setLabel("Access and use constraints");			
		$this->getElement('sensor_resol_tmp')->setLabel('Temporal (hh:mm:ss)');			
		$this->getElement('place_alt_min_0')->setLabel("Altitude min");
		$this->getElement('place_alt_max_0')->setLabel("Altitude max");
		$this->getElement('data_format_0')->setLabel("Original data format");
		$this->addElement('file','upload_doc','Attached document');
		$this->addElement('submit','upload','Upload');
		$this->addElement('submit','delete','Delete');
		
		for ($i = 0; $i < $this->dataset->nbVars; $i++){
			$this->getElement('methode_acq_'.$i)->setLabel("Parameter processing related information");
		}
		$this->addElement('submit','bouton_add_variable','Add a parameter',array('onclick' => "document.getElementById('frmvadataset').action += '#a_param'"));
		
		$this->addElement('submit','bouton_add_projet','Add a project',array('onclick' => "document.getElementById('frmvadataset').action += '#a_general'"));
		$option = array();
		$option['default'] = "";
		$option['model'] = "Model";
		$option['instrument'] = "Instrument";
		$option['satellite'] = "Satellite";
		$this->addElement('select','source_type','source type :',$option,array('onchange' => "DeactivateButtonAddSource()",'onclick' => "DeactivateButtonAddSource();",'onmouseover' => 'DeactivateButtonAddSource();' ));
		$this->addElement('submit','bouton_add_source','Add a source',array('disabled' => 'true','onclick' => "document.getElementById('frmvadataset').action += '#a_source'",'onmouseout' => 'DeactivateButtonAddSource();'));
		
		if (isset ( $this->dataset->dats_sensors ) && ! empty ( $this->dataset->dats_sensors )) {
			$this->dats_sensors = array();
			$this->dats_sensors = $this->dataset->dats_sensors ;
		}
		if (isset ( $this->dataset->sites ) && ! empty ( $this->dataset->sites )) {
			$this->sites = array();
			$this->sites = $this->dataset->sites;
		}
		
		if ( isset ( $this->dataset->dats_id ) && $this->dataset->dats_id > 0) {
			if (isset ( $this->dataset->nbModFormSensor )){
				if($this->dataset->nbModForm <= $this->dataset->nbModFormSensor ){
					$this->dataset->nbModForm = $this->dataset->nbModFormSensor;
				}
			}
			if (isset ( $this->dataset->nbSatFormSensor )){
				if($this->dataset->nbSatForm <= $this->dataset->nbSatFormSensor){
					$this->dataset->nbSatForm = $this->dataset->nbSatFormSensor;
				}
			}
			if (isset ( $this->dataset->nbInstruFormSensor )){
				if($this->dataset->nbInstruForm <= $this->dataset->nbInstruFormSensor){
					$this->dataset->nbInstruForm = $this->dataset->nbInstruFormSensor;
				}
			}
		}
		if($this->dataset->nbModForm > 0)
			$this->addMod();
		if($this->dataset->nbInstruForm > 0)
			$this->addInstru();	
		if($this->dataset->nbSatForm > 0)
			$this->addSat();
		
		$this->dataset->dats_sensors = null ;
		$this->dataset->sites = null;
	}
	
	function initForm($Modif=false){
		$dataset = & $this->dataset;
		$this->initFormBase();	
		$this->initFormGrid();
		$this->initFormGeoCoverageVaDataset();
		if($Modif == true) $this->initVaFormResolution();
		//Parameters
		for ($i = 0; $i < $this->dataset->nbVars; $i++){
			$this->initFormVariable($i,$i);
		}
		//REQ DATA FORMATS
		if (isset($this->dataset->required_data_formats[0]) && !empty($this->dataset->required_data_formats[0])){
			$this->getElement('required_data_format')->setSelected($this->dataset->required_data_formats[0]->data_format_id);
		}else
			$this->getElement('required_data_format')->setSelected(0);

		
		if($this->dataset->nbModForm > 0){
			$this->addMod();
			if($Modif == true) $this->initModForm();
		}
		if($this->dataset->nbSatForm > 0){
			$this->addSat();
			if($Modif == true) $this->initSatForm();
		}
		if($this->dataset->nbInstruForm > 0){
			$this->addInstru();
			if($Modif == true) $this->initInstruForm();
		}		
	}
	function initModForm() {
		if ($this->dataset->dats_id > 0) {
			if (isset ( $this->sites ) && ! empty ( $this->sites )) {
				for($i = $this->dataset->nbSatFormSensor + 1; $i < ($this->dataset->nbModFormSensor + $this->dataset->nbSatFormSensor + 1); $i ++) {
					if (isset ( $this->sites [$i] ) && ! empty ( $this->sites [$i] )) {
						$this->getElement ( 'model_' . ($i - $this->dataset->nbSatFormSensor - 1) )->setSelected ( $this->sites [$i]->place_id );
						$this->getElement ( 'new_model_' . ($i - $this->dataset->nbSatFormSensor - 1) )->setValue ( $this->sites [$i]->place_name );
						if (isset ( $this->sites [$i]->pla_place_id ) && ! empty ( $this->sites [$i]->pla_place_id )) {
							$categ [0] = $this->sites [$i]->gcmd_plat_id;
							$categ [1] = $this->sites [$i]->pla_place_id;
							$this->getElement ( 'model_categ_' . ($i - $this->dataset->nbSatFormSensor - 1) )->setValue ( $categ );
						}
						if (isset ( $this->dats_sensors [$i] ) && ! empty ( $this->dats_sensors [$i] )) {
							// Instrument
							$this->dats_sensors [$i]->sensor = new sensor ();
							$this->dats_sensors [$i]->sensor = $this->dats_sensors [$i]->sensor->getById ( $this->dats_sensors [$i]->sensor_id );
							$array = array();
							//$array[0] = "";
							if ($this->sites[$i]->place_id > 0){
								$sensor = new sensor;
								$listeInstrus = $sensor->getByPlace($this->sites[$i]->place_id);
								foreach ($listeInstrus as $instru){
									$array[$instru->sensor_id] = $instru->sensor_model;
								}
							}
							$this->getElement ( 'simu_' . ($i - $this->dataset->nbSatFormSensor - 1) )->loadArray($array);
							$this->getElement ( 'simu_' . ($i - $this->dataset->nbSatFormSensor - 1) )->setSelected ( $this->dats_sensors [$i]->sensor->sensor_id );
							$this->getElement ( 'new_simu_' . ($i - $this->dataset->nbSatFormSensor - 1) )->setValue ( $this->dats_sensors [$i]->sensor->sensor_model );
							$this->getElement ( 'sensor_resol_temp__' . ($i - $this->dataset->nbSatFormSensor - 1) )->setValue ( $this->dats_sensors [$i]->sensor_resol_temp );
						}
					}
				}
			}
		}
	}
	function initSatForm() {
		if ($this->dataset->dats_id > 0) {
			if (isset ( $this->sites ) && ! empty ( $this->sites )) {
				for($i = 1; $i < $this->dataset->nbSatFormSensor + 1; $i ++) {
					$this->getElement ( 'satellite_' . ($i - 1) )->setSelected ( $this->sites [$i]->place_id );
					$this->getElement ( 'new_satellite_' . ($i - 1) )->setValue ( $this->sites [$i]->place_name );
					if (isset ( $this->dats_sensors ) && ! empty ( $this->dats_sensors )) {
						$this->dats_sensors [$i]->sensor = new sensor ();
						$this->dats_sensors [$i]->sensor = $this->dats_sensors [$i]->sensor->getById ( $this->dats_sensors [$i]->sensor_id );
						$this->dats_sensors [$i]->sensor->gcmd_instrument_keyword = new gcmd_instrument_keyword;
						$this->dats_sensors [$i]->sensor->gcmd_instrument_keyword = $this->dats_sensors [$i]->sensor->gcmd_instrument_keyword->getById ( $this->dats_sensors [$i]->sensor->gcmd_sensor_id );
						$this->dats_sensors [$i]->sensor->gcmd_sensor_id = &$this->dats_sensors [$i]->sensor->gcmd_instrument_keyword->gcmd_sensor_id;
						if (isset ( $this->dats_sensors [$i]->sensor->gcmd_instrument_keyword ) && ! empty ( $this->dats_sensors [$i]->sensor->gcmd_instrument_keyword )) {
							$this->getElement ( 'sensor_gcmd_' . ($i - 1) )->setSelected ( $this->dats_sensors [$i]->sensor->gcmd_instrument_keyword->gcmd_sensor_id );
						}
						$array = array();
						if ( $this->sites [$i]->place_id > 0 ){
								$sensor = new sensor;
								$listeInstrus = $sensor->getByPlace($this->sites [$i]->place_id);
								foreach ($listeInstrus as $instru){
									$array[$instru->sensor_id] = $instru->sensor_model;
								}
						}
						$this->getElement ( 'instrument_' . ($i - 1) )->loadArray($array);
						$this->getElement ( 'instrument_' . ($i - 1) )->setSelected ( $this->dats_sensors [$i]->sensor->sensor_id );
						$this->getElement ( 'new_instrument_' . ($i - 1) )->setValue ( $this->dats_sensors [$i]->sensor->sensor_model );
						$this->getElement ( 'sat_sensor_url_' . ($i - 1) )->setValue ( $this->dats_sensors [$i]->sensor->sensor_url );
						$this->getElement ( 'sensor_resol_temp' . ($i - 1) )->setValue ( $this->dats_sensors [$i]->sensor_resol_temp );
					}
				}
			}
		}
	}
	function initInstruForm() {
		if ($this->dataset->dats_id > 0) {
			$ind=$this->dataset->nbModFormSensor + $this->dataset->nbSatFormSensor+1;
			if (isset ( $this->sites ) && ! empty ( $this->sites )) {
				for($i = $ind; $i < $ind+$this->dataset->nbInstruFormSensor; $i ++) {
					if (isset ( $this->dats_sensors ) && ! empty ( $this->dats_sensors )) {
						
						if (isset ( $this->dats_sensors [$i]->sensor->gcmd_instrument_keyword ) && ! empty ( $this->dats_sensors [$i]->sensor->gcmd_instrument_keyword )) {
							$this->getElement ( 'sensor_gcmd'.($i-$ind) )->setSelected ( $this->dats_sensors [$i]->sensor->gcmd_instrument_keyword->gcmd_sensor_id );
						}
						$this->getElement ( 'sensor_model_'.($i-$ind) )->setValue ( $this->dats_sensors [$i]->sensor->sensor_model );
						$this->getElement ( 'sensor_url_'.($i-$ind) )->setValue ( $this->dats_sensors [$i]->sensor->sensor_url );
					}
					
					if (isset ( $this->sites [$i]->gcmd_plateform_keyword ) && ! empty ( $this->sites [$i]->gcmd_plateform_keyword )) {
						$this->getElement ( 'gcmd_plat_key_' . ($i-$ind) )->setSelected ( $this->sites [$i]->gcmd_plateform_keyword->gcmd_plat_id );
					}
					$this->getElement ( 'new_place_' . ($i-$ind) )->setValue ( $this->sites [$i]->place_name );
					$this->getElement ( 'sensor_environment_' . ($i-$ind) )->setValue ( $this->sites [$i]->sensor_environment );
					$this->getElement ( 'sensor_resol_temp_' . ($i - $ind) )->setValue ( $this->dats_sensors [$i]->sensor_resol_temp );
				}
			}
		}
	}

	
	function initFormGeoCoverageVaDataset() {
		if (isset ( $this->sites ) && ! empty ( $this->sites )) {
			if (isset ( $this->sites [0] ) && ! empty ( $this->sites [0] )) {
				$this->getElement ( 'area' )->setSelected ( $this->sites [0]->place_id );
				$this->getElement ( 'new_area' )->setValue ( $this->sites [0]->place_name );
				$this->initFormSiteBoundings ( 0 );
			}
		}
	}
	
	function initFormSiteBoundings($i) {
		if (isset ( $this->sites [$i]->boundings ) && ! empty ( $this->sites [$i]->boundings )) {
			$this->getElement ( 'west_bound_' . $i )->setValue ( $this->sites [$i]->boundings->west_bounding_coord );
			$this->getElement ( 'east_bound_' . $i )->setValue ( $this->sites [$i]->boundings->east_bounding_coord );
			$this->getElement ( 'north_bound_' . $i )->setValue ( $this->sites [$i]->boundings->north_bounding_coord );
			$this->getElement ( 'south_bound_' . $i )->setValue ( $this->sites [$i]->boundings->south_bounding_coord );
		}
		$this->getElement ( 'place_alt_min_' . $i )->setValue ( $this->sites [$i]->place_elevation_min );
		$this->getElement ( 'place_alt_max_' . $i )->setValue ( $this->sites [$i]->place_elevation_max );
	}
	
	function initVaFormResolution(){
		$this->getElement('sensor_resol_tmp')->setValue($this->dats_sensors[0]->sensor_resol_temp);
		$this->getElement('sensor_vert_res')->setValue($this->dats_sensors[0]->sensor_vert_resolution);
		$this->getElement('sensor_lat_res')->setValue($this->dats_sensors[0]->sensor_lat_resolution);
		$this->getElement('sensor_lon_res')->setValue($this->dats_sensors[0]->sensor_lon_resolution);
	}
	
	function addProjet(){
		$this->createFormProject($this->dataset->nbProj-1);
	}
	
	function addVariableMod($nb_variable){
		$this->addVariable($nb_variable);
		$this->getElement('methode_acq_'.($nb_variable-1))->setLabel("Parameter processing related information");
	}
	
	function addMod(){
		for($i=0; $i< $this->dataset->nbModForm; $i++)
			$this->createFormMod($i);
	}
	
	function addSat(){
		for($i=0; $i< $this->dataset->nbSatForm; $i++)
			$this->createFormSat($i);
	}
	
	function addInstru(){
		for($i=0; $i< $this->dataset->nbInstruForm; $i++)
			$this->createFormSensor($i);	
	}
	
	function createFormMod($i){
		$place = new place;
		$mod_select = $place->chargeFormMod($this,'model_'.$i,'Model name',"updateModIndex(".$i.");");
		$this->addElement($mod_select);
		$this->addElement('text','new_model_'.$i,'Model name');
		$this->applyFilter('new_model_'.$i,'trim');
		$categ_select = $place->chargeFormModelCategs($this,'model_categ_'.$i,'Model type');
		$this->addElement($categ_select);
		$array = array();
		$array[0] = "";
		if ( isset($this->dataset->sites[$i+1]) && !empty($this->dataset->sites[$i+1]) ){
			if ($this->dataset->sites[$i+1]->place_id > 0){
				$sensor = new sensor;
				$listeInstrus = $sensor->getByPlace($this->dataset->sites[$i+1]->place_id);
				foreach ($listeInstrus as $instru){
					$array[$instru->sensor_id] = $instru->sensor_model;
				}
			}
		}
		$boxesNames = "['new_simu_".$i."']";
		$columnsNames = "['sensor_model']";
		$this->addElement('select','simu_'.$i,"Simulation",$array,array('onchange' => "fillBoxes('simu_".$i."',".$boxesNames.",'sensor',".$columnsNames.");",'onload' => "fillBoxes('simu_".$i."',".$boxesNames.",'sensor',".$columnsNames.");"));
		$this->addElement('text','new_simu_'.$i,'Simulation name', array('onchange' => "updateDatasetTitle('new_simu_".$i."');",'onload' => "updateDatasetTitle('new_simu_".$i."');"));
		$this->applyFilter('new_simu_'.$i,'trim');
		$this->addElement('text','sensor_resol_temp__'.$i,'Temporal');
		$this->applyFilter('sensor_resol_temp__'.$i,'trim');
		$this->getElement('sensor_resol_temp__'.$i)->setLabel("Temporal Resolution ");
		$this->addElement('submit','mod_button_delete_'.$i,' X ',array('onclick' => "document.getElementById('frmvadataset').action += '#a_source'",'style' => 'position:relative;right:-280px;background:transparent;color:rgb(80,80,80);','onmouseover' => "document.getElementsByName('mod_button_delete_$i')[0].style.background='#CCFFFF';", 'onmouseout' => "document.getElementsByName('mod_button_delete_$i')[0].style.background='transparent';"));
	}
	
	function createFormSat($i){
		$this->addElement('text','new_satellite_'.$i,'Satellite name', array('onchange' => "updateSat(".$i.");"));
		$this->applyFilter('new_satellite_'.$i,'trim');
		$this->dataset->sites[$i+1]= new place;
		$place = & $this->dataset->sites[$i+1];
		$sat_select = $place->chargeFormSatvadataset($this,$i);
		$this->addElement($sat_select);
		$this->createFormSensorKeyword('_'.$i);
		$this->addElement('text','sat_sensor_url_'.$i,'Reference (URL or paper)');
		$this->applyFilter('sat_sensor_url_'.$i,'trim');
		$this->createFormInstru($i);
		$this->addElement('text','sensor_resol_temp'.$i,'Temporal');
		$this->applyFilter('sensor_resol_temp'.$i,'trim');
		$this->getElement('sensor_resol_temp'.$i)->setLabel("Temporal Resolution ");
		$this->addElement('submit','sat_button_delete_'.$i,' X ',array('onclick' => "document.getElementById('frmvadataset').action += '#a_source'",'style' => 'position:relative;right:-280px;background:transparent;color:rgb(80,80,80);','onmouseover' => "document.getElementsByName('sat_button_delete_$i')[0].style.background='#CCFFFF';", 'onmouseout' => "document.getElementsByName('sat_button_delete_$i')[0].style.background='transparent';"));
	}
	
	function createFormInstru($i){ 
		if ( isset($this->dataset->sites[$i+1]) && !empty($this->dataset->sites[$i+1]) && $this->dataset->sites[$i+1]->place_id > 0){
			$satId = $this->dataset->sites[$i+1]->place_id;
		}else{
			$satId = $this->exportValue('satellite_'.$i);
		}
		$array = array();
		if ( isset($satId) && !empty($satId) ){
			if ( $satId > 0 ){
				$sensor = new sensor;
				$listeInstrus = $sensor->getByPlace($satId);
				foreach ($listeInstrus as $instru){
					$array[$instru->sensor_id] = $instru->sensor_model;
				}
			}
		}
		$boxesNames = "['new_instrument_".$i."']";
		$columnsNames = "['sensor_model','sensor_url','gcmd_sensor_id']"; 
		$this->addElement('select','instrument_'.$i,"Instrument",$array,array('onchange' => "fillBoxes('instrument_".$i."',".$boxesNames.",'sensor',".$columnsNames.");"));
		$this->addElement('text','new_instrument_'.$i,'Instrument name');
		$this->applyFilter('new_instrument_'.$i,'trim');
		$array = null;
	}
	
	function createFormSite($i){
			
		$this->dataset->sites[$i+1]= new place;
		$place = & $this->dataset->sites[$i+1];
		$key = new gcmd_plateform_keyword;
		$key_select = $key->chargeFormvadataset($this,'gcmd_plat_key_'.$i,'Platform type');
		$this->addElement($key_select);
		$this->addElement('text','new_place_'.$i,'Exact location');	
		$this->addElement('textarea','sensor_environment_'.$i,'Instrument environment',array('cols'=>30, 'rows'=>3));
		$this->applyFilter('sensor_environment_'.$i,'trim');
	}
	
	function createFormSensor($i){
		$this->createFormSensorKeywordVaDataset($i);
		$this->addElement('hidden','sensor_id_'.$i);
		$this->addElement('text','sensor_model_'.$i,'Model');
		$this->applyFilter('sensor_model_'.$i,'trim');	
		$this->addElement('text','sensor_url_'.$i,'Reference (URL or paper)');
		$this->applyFilter('sensor_url_'.$i,'trim');
		$this->addElement('text','sensor_resol_temp_'.$i,'Temporal');
		$this->applyFilter('sensor_resol_temp_'.$i,'trim');
		$this->getElement('sensor_resol_temp_'.$i)->setLabel("Temporal Resolution ");
		$this->createFormSite($i);
		$this->addElement('submit','instru_button_delete_'.$i,' X ',array('onclick' => "document.getElementById('frmvadataset').action += '#a_source'",'style' => 'position:relative;right:-260px;background:transparent;color:rgb(80,80,80);','onmouseover' => "document.getElementsByName('instru_button_delete_$i')[0].style.background='#CCFFFF';", 'onmouseout' => "document.getElementsByName('instru_button_delete_$i')[0].style.background='transparent';"));
	}
	
	function createVaFormResolution(){
		$this->addElement('text','sensor_resol_tmp','Temporal');
		$this->applyFilter('sensor_resol_tmp','trim');
		$this->addElement('text','sensor_vert_res','Vertical');
		$this->applyFilter('sensor_vert_res','trim');
		$this->addElement('text','sensor_lat_res','Horizontal');
		$this->applyFilter('sensor_lat_res','trim');
		$this->addElement('text','sensor_lon_res','Horizontal');
		$this->applyFilter('sensor_lon_res','trim');
	}
	
	function createFormInstruManufacturer($i){
		$man = new manufacturer;
		$man_select = $man->chargeForm($this,'manufacturer_'.$i,'Manufacturer');
		$this->addElement($man_select);
		$this->addElement('text','new_manufacturer_'.$i,'new manufacturer: ');
		$this->addElement('text','new_manufacturer_url_'.$i,'Manufacturer web site');
	}
	
	function addFormAccordingOnSourceType(){
		$Source = $this->exportValue('source_type');
		switch ($Source){
			case 'model':
				$this->dataset->nbModForm++;
				break;
			case 'satellite':
				$this->dataset->nbSatForm++;
				break;
			case 'instrument':
				$this->dataset->nbInstruForm++;
				break;
			default:
				break;
		}
		
	}
	function deleteModForm($j) {
		if ($this->dataset->nbModForm > 0) {
			for($i = $this->dataset->nbSatForm+1; $i < ($this->dataset->nbModForm + $this->dataset->nbSatForm+1); $i ++) {
				if (($i-$this->dataset->nbSatForm-1) == $j) {
					unset($this->sites [$i]);
					unset($this->dats_sensors [$i]);
					$var = $i;
				}
			}
			$this->dataset->nbModForm --;
			if(isset($this->dataset->nbModFormSensor) && $this->dataset->nbModFormSensor > 0 )
				$this->dataset->nbModFormSensor --;
			if (isset ( $var)) {
				for($i = $var; $i < (count($this->dataset->sites)-1); $i ++) {
					$this->sites [$i] = $this->sites [$i + 1];
					$this->dats_sensors [$i] = $this->dats_sensors [$i + 1];
				}
				unset($this->sites [$i+1]);
				unset($this->dats_sensors [$i+1]);
				$this->dataset->sites = $this->sites ;
				$this->dataset->dats_sensors  = $this->dats_sensors ;
			}
		}
	}
	function deleteSatForm($j) {
		if ($this->dataset->nbSatForm > 0) {
			for($i = 1; $i < ($this->dataset->nbModForm + $this->dataset->nbSatForm+1); $i ++) {
				if (($i - 1) == $j) {
					unset($this->sites [$i]);
					unset($this->dats_sensors [$i]);
					$var = $i;
				}
			}
			$this->dataset->nbSatForm --;
			if(isset($this->dataset->nbSatFormSensor) && $this->dataset->nbSatFormSensor > 0 )
				$this->dataset->nbSatFormSensor --;
			if (isset ( $var )) {
				for($i = $var ; $i < (count($this->dataset->sites)-1); $i ++) {
					$this->sites [$i] = $this->sites [$i + 1];
					$this->dats_sensors [$i] = $this->dats_sensors [$i + 1];
				}
				unset($this->sites [$i+1]);
				unset($this->dats_sensors [$i+1]);
				$this->dataset->sites = $this->sites ;
				$this->dataset->dats_sensors  = $this->dats_sensors ;
			}
		}
		
	}
	
	function deleteInstruForm($j) {
		if ($this->dataset->nbInstruForm > 0) {
			$ind = $this->dataset->nbModForm + $this->dataset->nbSatForm +1;
			for($i = $ind; $i < ($this->dataset->nbInstruForm + $ind ); $i ++) {
				if (($i - $ind) == $j) {
					unset($this->sites [$i]);
					unset($this->dats_sensors [$i]);
					$var = $i;
				}
			}
			$this->dataset->nbInstruForm --;
			if(isset($this->dataset->nbInstruFormSensor) && $this->dataset->nbInstruFormSensor > 0 )
				$this->dataset->nbInstruFormSensor --;
			if (isset ( $var )) {
				for($i = $var ; $i < (count($this->dataset->sites)-1); $i ++) {
					$this->sites [$i] = $this->sites [$i + 1];
					$this->dats_sensors [$i] = $this->dats_sensors [$i + 1];
				}
				unset($this->sites [$i+1]);
				unset($this->dats_sensors [$i+1]);
				$this->dataset->sites = $this->sites ;
				$this->dataset->dats_sensors  = $this->dats_sensors ;

			}
		}
	}
	
	//================================================================= Validation Rules Functions ===========================================================
	
	function addValidationRules(){
		$this->registerRule('validDate','function','validDate');
		$this->registerRule('validPeriod','function','validPeriod');
		$this->registerRule('existe','function','existe');
		$this->registerRule('number_range','function','number_range');
		$this->registerRule('validInterval','function','validInterval');
		$this->registerRule('couple_not_null','function','couple_not_null');
		$this->registerRule('validParam','function','validParam');
		$this->registerRule('validUnit_existe','function','validUnit_existe');
		$this->registerRule('validUnit_required','function','validUnit_required');
		$this->addRule('dats_title','Data description: Metadata informative title is required','required');
		$this->addRule('dats_title','Data description: Dataset name exceeds the maximum length allowed (100 characters)','maxlength',100);	
		$this->addRule('dats_date_begin','Temporal coverage: Date begin is not a date','validDate');
		$this->addRule('dats_date_end','Temporal coverage: Date end is not a date','validDate');
		$this->addRule(array('dats_date_begin','dats_date_end'),'Temporal coverage: Date end must be after date begin','validPeriod');
		if ($this->dataset->dats_id == 0){
			$this->addRule('dats_title','Data description: A dataset with the same title exists in the database','existe',array('dataset','dats_title'));
		}
		
		if (isset($this->dataset->data_policy) && !empty($this->dataset->data_policy) && $this->dataset->data_policy->data_policy_id > 0){
			$this->getElement('new_data_policy')->setAttribute('onfocus','blur()');
		}
		$this->addRule('new_data_policy','Data use information: Data policy exceeds the maximum length allowed (100 characters)','maxlength',100);	
		$attrs = array();
		if (isset($this->dataset->database) && !empty($this->dataset->database) && $this->dataset->database->database_id > 0){

			$this->disableElement('new_database');
			$this->disableElement('new_db_url');
		}

		$this->addRule('new_database','Data use information: Database name exceeds the maximum length allowed (250 characters)','maxlength',250);
		$this->addRule('new_db_url','Data use information: Database url exceeds the maximum length allowed (250 characters)','maxlength',250);	
		//Formats
		for ($i = 0; $i < $this->dataset->nbFormats; $i++){
			$this->addRule('data_format_'.$i,'Data use information: Format name '.($i+1).' exceeds the maximum length allowed (100 characters)','maxlength',100);
			if (isset($this->dataset->data_formats[$i]) && !empty($this->dataset->data_formats[$i]) && $this->dataset->data_formats[$i]->data_format_id > 0){
				$this->disableElement('new_data_format_'.$i);
			}

		}
		//Contacts
		$this->addRule('pi_0','Contact 1 is required','couple_not_null',array($this,'pi_name_0'));
		$this->addRule('organism_0','Contact 1: organism is required','couple_not_null',array($this,'org_sname_0'));
		$this->addRule('email1_0','Contact 1: email1 is required','required');	
		for ($i = 0; $i < $this->dataset->nbPis; $i++){
			$this->addRule('pi_name_'.$i,'Contact '.($i+1).': Name exceeds the maximum length allowed (250 characters)','maxlength',250);
			$this->addRule('email1_'.$i,'Contact '.($i+1).': email1 is incorrect','email');
			$this->addRule('email2_'.$i,'Contact '.($i+1).': email2 is incorrect','email');
			$this->addRule('org_fname_'.$i,'Contact '.($i+1).': Organism full name exceeds the maximum length allowed (250 characters)','maxlength',250);
			$this->addRule('org_sname_'.$i,'Contact '.($i+1).': Organism short name exceeds the maximum length allowed (50 characters)','maxlength',50);
			$this->addRule('org_url_'.$i,'Contact '.($i+1).': Organism url exceeds the maximum length allowed (250 characters)','maxlength',250);
			$this->addRule('email1_'.$i,'Contact '.($i+1).': email1 exceeds the maximum length allowed (250 characters)','maxlength',250);
			$this->addRule('email2_'.$i,'Contact '.($i+1).': email2 exceeds the maximum length allowed (250 characters)','maxlength',250);
			if (isset($this->dataset->originators[$i]) && !empty($this->dataset->originators[$i]) && $this->dataset->originators[$i]->pers_id > 0){
				$this->disableElement('pi_name_'.$i);
				$this->disableElement('email1_'.$i);
				$this->disableElement('email2_'.$i);
				$this->disableElement('organism_'.$i);
			}

			if (isset($this->dataset->originators[$i]->organism) && !empty($this->dataset->originators[$i]->organism) && $this->dataset->originators[$i]->organism->org_id > 0){
				$this->disableElement('org_sname_'.$i);
				$this->disableElement('org_fname_'.$i);
				$this->disableElement('org_url_'.$i);
			}
			if ($i != 0){
				$this->addRule('pi_name_'.$i,'Contact '.($i+1).': email1 is required','contact_email_required',array($this,$i));
				$this->addRule('pi_name_'.$i,'Contact '.($i+1).': organism is required','contact_organism_required',array($this,$i));
			}
		}
		
		//Add validation rules
		$this->AddModValidationRules();
		$this->AddSatValidationRules();
		$this->AddInstruValidationRules();
		$this->addVaValidationRulesResolution('Coverage');
		$this->addRule('sensor_resol_tmp','Coverage: temporal resolution is incorrect','regex',"/^[0-9]{2}[:][0-9]{2}[:][0-9]{2}$/");
		$this->addValidationRulesGeoCoverage();
		//PARAMETER
		for ($i = 0; $i < $this->dataset->nbVars; $i++){
			$this->addValidationRulesVariable($i,$i,'Parameter '.($i+1));
		}
		
	}
	
	function addVaValidationRulesResolution($prefixMsg = 'Instrument'){
		$this->addRule('sensor_resol_tmp',$prefixMsg.': Observation frequency exceeds the maximum length allowed (100 characters)','maxlength',100);
		$this->addRule('sensor_vert_res',$prefixMsg.': Vertical coverage exceeds the maximum length allowed (100 characters)','maxlength',100);
		$this->addRule('sensor_lat_res',$prefixMsg.': Latitude coverage exceeds the maximum length allowed (100 characters)','maxlength',100);
		$this->addRule('sensor_lon_res',$prefixMsg.': Longitude coverage exceeds the maximum length allowed (100 characters)','maxlength',100);
	}
	
	function AddModValidationRules() {
		if ($this->dataset->nbModForm > 0) {
			for($i = $this->dataset->nbSatForm; $i < ($this->dataset->nbModForm + $this->dataset->nbSatForm); $i ++) {
				$this->addRule ( 'new_model_' . ($i - $this->dataset->nbSatForm), "Model " . ($i - $this->dataset->nbSatForm +1) . ": model name is required", 'required' );
				$this->addRule ( 'model_categ_' . ($i - $this->dataset->nbSatForm), "Model " . ($i - $this->dataset->nbSatForm  +1) . ": Model type is required", 'required' );
				$this->addRule ( 'new_simu_' . ($i - $this->dataset->nbSatForm), "Model " . ($i - $this->dataset->nbSatForm  +1) . ": Simulation is required", 'required' );
				$this->addRule ( 'sensor_resol_temp__' . ($i - $this->dataset->nbSatForm), "Model " . ($i - $this->dataset->nbSatForm  +1) . ": temporal resolution is incorrect", 'regex', "/^[0-9]{2}[:][0-9]{2}[:][0-9]{2}$/" );
			}
		}
	}
	
	function addSatValidationRules() {
		if ($this->dataset->nbSatForm > 0) {
			for($i = 0; $i < $this->dataset->nbSatForm; $i ++) {
				$this->addRule ( 'new_satellite_' . ($i), 'Satellite ' . ($i + 1) . ': Satellite is required', 'required' );
				$this->addRule ( 'new_instrument_' . $i, 'Satellite ' . ($i + 1) . ': Instrument is required', 'required' );
				$this->addRule ( 'sensor_gcmd_' . $i, 'Satellite ' . ($i + 1) . ': Instrument type is required', 'required' );
				$this->addRule ( 'sensor_resol_temp' . $i, 'Satellite ' . ($i + 1) . ': temporal resolution is incorrect', 'regex', "/^[0-9]{2}[:][0-9]{2}[:][0-9]{2}$/" );
			}
		}
	}
	
	function addInstruValidationRules() {
		$ind = $this->dataset->nbModForm + $this->dataset->nbSatForm + 1;
		if ($this->dataset->nbInstruForm > 0) {
			$dataset = & $this->dataset;
			for($i = $ind; $i < ($dataset->nbInstruForm + $ind); $i ++) {
				$this->addRule ( 'sensor_gcmd' . ($i - $ind), 'Instrument ' . ($i - $ind + 1) . ': Instrument type is required', 'required' );
				$this->addRule ( 'sensor_resol_temp_' . ($i - $ind), 'Instrument ' . ($i - $ind + 1) . ': temporal resolution is incorrect', 'regex', "/^[0-9]{2}[:][0-9]{2}[:][0-9]{2}$/" );
				$this->addRule ( 'new_place_' . ($i - $ind), 'Instrument ' . ($i - $ind + 1) . ': Exact location is required', 'required' );
				$this->addRule ( 'gcmd_plat_key_' . ($i - $ind), 'Instrument ' . ($i - $ind + 1) . ': Platform type is required', 'required' );
			}
		}
	}
	
		
	
	//================================================================= Saving Functions ===========================================================
	
	function saveForm(){	
		$this->saveFormBase();
		$this->saveFormGeoCoverage();
		$this->saveVaFormResolution();
		if ($this->dataset->nbModForm >0) $this->saveModForm();
		if ($this->dataset->nbSatForm >0) $this->saveSatForm();
		if ($this->dataset->nbInstruForm >0) $this->saveInstruForm();
		$this->saveFormGrid();
		//Parameter
		$this->saveFormVariables($this->dataset->nbVars);
		//REQ DATA_FORMAT
		$this->dataset->required_data_formats = array();
		$this->dataset->required_data_formats[0] = new data_format;
		$this->dataset->required_data_formats[0]->data_format_id = $this->exportValue('required_data_format');
	}
	function saveVaFormResolution(){
		$this->dataset->dats_sensors = array();
		$this->dataset->dats_sensors[0]= new dats_sensor;
		$this->dataset->dats_sensors[0]->sensor_id = 0;
		$this->dataset->dats_sensors[0]->sensor = new sensor;
		$this->dataset->dats_sensors[0]->sensor->sensor_id = 0; 			
		$this->dataset->dats_sensors[0]->sensor->gcmd_sensor_id = -1;
		$this->dataset->dats_sensors[0]->sensor->manufacturer_id = -1;
		$this->dataset->dats_sensors[0]->sensor->bound_id = -1;
		$this->dataset->dats_sensors[0]->sensor_resol_temp = $this->exportValue('sensor_resol_tmp');
		$this->dataset->dats_sensors[0]->sensor_vert_resolution = $this->exportValue('sensor_vert_res');
		$this->dataset->dats_sensors[0]->sensor_lat_resolution = $this->exportValue('sensor_lat_res');
		$this->dataset->dats_sensors[0]->sensor_lon_resolution = $this->exportValue('sensor_lon_res');
	}
	
	function saveModForm(){
		if($this->dataset->nbModForm > 0){	
			for ($i = $this->dataset->nbSatForm; $i < ($this->dataset->nbModForm+$this->dataset->nbSatForm); $i++){
				//Mod
				$this->dataset->sites[$i+1] = new place;
				$this->dataset->sites[$i+1]->place_id = $this->exportValue('model_'.($i-$this->dataset->nbSatForm));
				if(!isset($this->dataset->sites[$i+1]->place_id)){
					$this->dataset->sites[$i+1]->place_id = 0;
				}
				$this->dataset->sites[$i+1]->place_name = $this->exportValue('new_model_'.($i-$this->dataset->nbSatForm));
				$this->dataset->sites[$i+1]->bound_id = -1;
				
				$categ_modele = $this->exportValue('model_categ_'.($i-$this->dataset->nbSatForm));
				$this->dataset->sites[$i+1]->gcmd_plat_id = $categ_modele[0];
				$this->dataset->sites[$i+1]->pla_place_id = $categ_modele[1];
				if ($this->dataset->sites[$i+1]->gcmd_plat_id != 0) {
					$this->dataset->sites[$i+1]->gcmd_plateform_keyword = new gcmd_plateform_keyword;
					$this->dataset->sites[$i+1]->gcmd_plateform_keyword = $this->dataset->sites[$i+1]->gcmd_plateform_keyword->getById($this->dataset->sites[$i+1]->gcmd_plat_id);
				}	
				//Simu
				$this->dataset->dats_sensors[$i+1] = new dats_sensor;
				$this->dataset->dats_sensors[$i+1]->sensor = new sensor;
				$this->dataset->dats_sensors[$i+1]->sensor->sensor_id = $this->exportValue('simu_'.($i-$this->dataset->nbSatForm));
				$this->dataset->dats_sensors[$i+1]->sensor->sensor_model = $this->exportValue('new_simu_'.($i-$this->dataset->nbSatForm));
				if ( isset($this->dataset->sites[$i+1]) && !empty($this->dataset->sites[$i+1]) ){
					if ($this->dataset->sites[$i+1]->place_id >= 0){
						$sensor = new sensor;
						$listeInstrus = $sensor->getByPlace($this->dataset->sites[$i+1]->place_id);
						foreach ($listeInstrus as $instru){
							$array[$instru->sensor_id] = $instru->sensor_model;
							if (empty($this->dataset->dats_sensors[$i+1]->sensor->sensor_id) && $instru->sensor_model == $this->dataset->dats_sensors[$i+1]->sensor->sensor_model){
								$this->dataset->dats_sensors[$i+1]->sensor->sensor_id = $instru->sensor_id ;
							}						
						}
					}
				}
				$this->dataset->dats_sensors[$i+1]->sensor->gcmd_sensor_id = -1;
				$this->dataset->dats_sensors[$i+1]->sensor->manufacturer_id = -1;
				$this->dataset->dats_sensors[$i+1]->sensor->bound_id = -1;
				$this->dataset->dats_sensors[$i+1]->sensor_resol_temp = $this->exportValue('sensor_resol_temp__'.($i-$this->dataset->nbSatForm));
				
			}	
		}
	}
	
	function saveSatForm(){
		
		if($this->dataset->nbSatForm > 0){
			for ($i= 0; $i < $this->dataset->nbSatForm; $i++){
				$this->dataset->sites[$i+1] = new place;
				$this->dataset->sites[$i+1]->place_id = $this->exportValue('satellite_'.($i));
				$this->dataset->sites[$i+1]->place_name = $this->exportValue('new_satellite_'.($i));
				if(empty($this->dataset->sites[$i+1]->place_name)){
					$this->dataset->sites[$i+1]->place_name = "Not set";
				}
				$this->dataset->sites[$i+1]->gcmd_plateform_keyword = new gcmd_plateform_keyword;
				$this->dataset->sites[$i+1]->gcmd_plateform_keyword = $this->dataset->sites[$i+1]->gcmd_plateform_keyword->getByName("Satellites");
				$this->dataset->sites[$i+1]->gcmd_plat_id = & $this->dataset->sites[$i+1]->gcmd_plateform_keyword->gcmd_plat_id;
				
				$this->dataset->sites[$i+1]->bound_id = -1;
				if (empty($this->dataset->sites[$i+1]->place_name)){
					$this->dataset->sites[$i+1]->place_id = -1;
				}
				
				// Instrument
				$this->dataset->dats_sensors[$i+1] = new dats_sensor;
				$this->dataset->dats_sensors[$i+1]->sensor = new sensor;
				$this->dataset->dats_sensors[$i+1]->sensor->sensor_id = $this->exportValue('instrument_'.$i);
				if(empty($this->dataset->dats_sensors[$i+1]->sensor->sensor_id)){
					$this->dataset->dats_sensors[$i+1]->sensor->sensor_id = 0;
				}
				
				$this->dataset->dats_sensors[$i+1]->sensor->sensor_model = $this->exportValue('new_instrument_'.$i);
				$this->dataset->dats_sensors[$i+1]->sensor->gcmd_sensor_id = $this->exportValue('sensor_gcmd_'.$i);
				$this->dataset->dats_sensors[$i+1]->sensor_resol_temp = $this->exportValue('sensor_resol_temp'.$i);
							
				if ($this->dataset->dats_sensors[$i+1]->sensor->gcmd_sensor_id != 0){
					$this->dataset->dats_sensors[$i+1]->sensor->gcmd_instrument_keyword = new gcmd_instrument_keyword;
					$this->dataset->dats_sensors[$i+1]->sensor->gcmd_instrument_keyword = $this->dataset->dats_sensors[$i+1]->sensor->gcmd_instrument_keyword->getById($this->dataset->dats_sensors[$i+1]->sensor->gcmd_sensor_id);
				}
				
				$this->dataset->dats_sensors[$i+1]->sensor->sensor_url = $this->exportValue('sat_sensor_url_'.$i);
				$this->dataset->dats_sensors[$i+1]->sensor->manufacturer_id = -1;
				$this->dataset->dats_sensors[$i+1]->sensor->bound_id = -1;
	
			}
		}
	}
	
	function saveInstruForm(){
		
		$ind=$this->dataset->nbModForm+$this->dataset->nbSatForm+1;
		if($this->dataset->nbInstruForm > 0){
			$dataset = & $this->dataset;
			for($i = $ind; $i < ($dataset->nbInstruForm+$ind); $i++){
				
				$dataset->dats_sensors[$i] = new dats_sensor;
				$dataset->dats_sensors[$i]->sensor = new sensor;
				
				$sensId = $this->exportValue('sensor_id_'.($i-$ind));
				if ( isset($sensId) && ( strlen($sensId) > 0 )){
					$dataset->dats_sensors[$i]->sensor->sensor_id = $sensId;
				}else{
					$dataset->dats_sensors[$i]->sensor->sensor_id = 0;
				}
				
				$dataset->dats_sensors[$i]->sensor->gcmd_sensor_id = $this->exportValue('sensor_gcmd'.($i-$ind));
				
				
				
				if ($dataset->dats_sensors[$i]->sensor->gcmd_sensor_id != 0)
				{
					$dataset->dats_sensors[$i]->sensor->gcmd_instrument_keyword = new gcmd_instrument_keyword;
					$dataset->dats_sensors[$i]->sensor->gcmd_instrument_keyword = $dataset->dats_sensors[$i]->sensor->gcmd_instrument_keyword->getById($dataset->dats_sensors[$i]->sensor->gcmd_sensor_id);
				}
				
				$dataset->dats_sensors[$i]->sensor->manufacturer = new manufacturer;
				
				if (empty($dataset->dats_sensors[$i]->sensor->manufacturer->manufacturer_name)){
					$dataset->dats_sensors[$i]->sensor->manufacturer->manufacturer_id = -1;
				}
								
				$dataset->dats_sensors[$i]->sensor->sensor_url = $this->exportValue('sensor_url_'.($i-$ind));
				$dataset->dats_sensors[$i]->sensor->sensor_model = $this->exportValue('sensor_model_'.($i-$ind));
				$this->dataset->dats_sensors[$i]->sensor_resol_temp = $this->exportValue('sensor_resol_temp_'.($i-$ind));	
				$dataset->dats_sensors[$i]->sensor->bound_id = -1;
				
				//SITES
				$dataset->sites[$i] = new place;
				$dataset->sites[$i]->place_name = $this->exportValue('new_place_'.($i-$ind));
				$dataset->sites[$i]->place_id = 0;
				if (empty($dataset->sites[$i]->place_name)){
					$dataset->sites[$i]->place_id = -1;
					$dataset->dats_sensors[$i]->sensor->sensor_id = -1;	
				}
				$dataset->sites[$i]->gcmd_plat_id = $this->exportValue('gcmd_plat_key_'.($i-$ind));
				if ($dataset->sites[$i]->gcmd_plat_id != 0 &&	strlen($dataset->sites[$i]->gcmd_plat_id) > 0 )	{
					$dataset->sites[$i]->gcmd_plateform_keyword = new gcmd_plateform_keyword;
					$dataset->sites[$i]->gcmd_plateform_keyword = $dataset->sites[$i]->gcmd_plateform_keyword->getById($dataset->sites[$i]->gcmd_plat_id);
				}
				
				$dataset->dats_sensors[$i]->sensor_places[0] = new sensor_place;
				$dataset->dats_sensors[$i]->sensor_places[0]->sensor_id = $dataset->dats_sensors[$i]->sensor->sensor_id;
				$dataset->dats_sensors[$i]->sensor_places[0]->place_id = $dataset->sites[$i]->place_id;
				$dataset->dats_sensors[$i]->sensor_places[0]->environment = $this->exportValue('sensor_environment_'.($i-$ind));
				
				//sensor environment
				$sensor_environment = $this->exportValue('sensor_environment_'.($i-$ind));
				if (isset($sensor_environment) && !empty($sensor_environment)){
					$dataset->sites[$i]->sensor_environment = $sensor_environment;
				}
			}
			
		}
	}

	
	//==================================================================== Display Functions =====================================================================
	
	
	function displayErrorsCoverage(){
		$this->displayErrors(array('dats_date_begin','dats_date_end','area','sensor_resol_temp',
			'west_bound_0','east_bound_0','north_bound_0','south_bound_0','place_alt_min_0','place_alt_max_0'));
	}
	
	function displayErrorsMod($i){
		$this->displayErrors(array('model_'.$i,'new_model_'.$i,'simu_'.$i,'new_simu_'.$i,'sensor_resol_temp__'.$i));
	}
	
	function displayErrorsInstru($i){
		$this->displayErrors(array('sensor_gcmd_'.$i,'new_place_'.$i,'sensor_model_'.$i,'gcmd_plat_key_'.$i,'sensor_resol_temp_'.$i,'sensor_url_'.$i));			
	}
	
	function displayErrorsSat($i){
		$this->displayErrors(array('satellite_'.$i,'instrument_'.$i,'new_satellite_'.$i,'new_instrument_'.$i,'sat_sensor_url_'.$i,'sensor_resol_temp'.$i));
	}
	
	function displayErrorsModDataDescr(){
		$this->displayErrors(array('dats_title'));
	}
	
	function displayModForm($i){
		if ($this->dataset->nbModForm > 1)
			echo "<tr onmouseover = 'DeactivateButtonAddSource();'><td id='a_model' colspan='4'  align='center'><b>Model ".($i+1)."</b> ".$this->getElement('mod_button_delete_'.$i)->toHTML()."</td></tr>";
		else 
			echo "<tr onmouseover = 'DeactivateButtonAddSource();'><td id='a_model' colspan='4' align='center'><b>Model</b>".$this->getElement('mod_button_delete_'.$i)->toHTML()."</td></tr>";
		$this->displayErrorsMod($i);
		echo '<tr><td><font color="#467AA7">'.$this->getElement('model_categ_'.$i)->getLabel().'</font></td><td colspan="3">'.$this->getElement('model_categ_'.$i)->toHTML().'</td></tr>';
		echo '<tr><td><font color="#467AA7">'.$this->getElement('model_'.$i)->getLabel().'</font></td><td colspan="3">'.$this->getElement('model_'.$i)->toHTML();
		echo '&nbsp;&nbsp;or add new&nbsp;'.$this->getElement('new_model_'.$i)->toHTML().'</td></tr>';
		echo '<tr><td><font color="#467AA7">'.$this->getElement('simu_'.$i)->getLabel().'</font></td><td colspan="1">'.$this->getElement('simu_'.$i)->toHTML();
		echo '&nbsp;&nbsp;or add new&nbsp;'.$this->getElement('new_simu_'.$i)->toHTML().'</td>';
		echo '<td>'.$this->getElement('sensor_resol_temp__'.$i)->getLabel().'</td><td colspan="1">'.$this->getElement('sensor_resol_temp__'.$i)->toHTML().'</td></tr>';
	}
	
	function displaySatForm($i){
		if ($this->dataset->nbSatForm > 1)
			echo "<tr onmouseover = 'DeactivateButtonAddSource();'><td id='a_satellite' colspan='4' align='center'><b>Satellite ".($i+1)."</b> ".$this->getElement('sat_button_delete_'.$i)->toHTML()."</td></tr>";
		else 
			echo "<tr onmouseover = 'DeactivateButtonAddSource();'><td id='a_satellite' colspan='4' align='center'><b>Satellite</b>".$this->getElement('sat_button_delete_'.$i)->toHTML()."</td></tr>";
		$this->displayErrorsSat($i);
		echo '<tr><td><font color="#467AA7">'.$this->getElement('satellite_'.$i)->getLabel().'</font></td><td colspan="3">'.$this->getElement('satellite_'.$i)->toHTML();
		echo '&nbsp;&nbsp;or add new&nbsp;'.$this->getElement('new_satellite_'.$i)->toHTML().'</td></tr>';
		if ($i == 0){
			echo '<tr><td><font color="#467AA7">'.$this->getElement('instrument_'.$i)->getLabel().'</font></td><td colspan="3">'.$this->getElement('instrument_'.$i)->toHTML();
			echo '&nbsp;&nbsp;or add new&nbsp;'.$this->getElement('new_instrument_'.$i)->toHTML().'</td></tr>';
		}else{
			echo '<tr><td><font color="#467AA7">'.$this->getElement('instrument_'.$i)->getLabel().'</td><td colspan="3">'.$this->getElement('instrument_'.$i)->toHTML();
			echo '&nbsp;&nbsp;or add new&nbsp;'.$this->getElement('new_instrument_'.$i)->toHTML().'</td></tr>';
		}
		echo '<tr><td><font color="#467AA7">'.$this->getElement('sensor_gcmd_'.$i)->getLabel().'</font></td><td colspan="3">'.$this->getElement('sensor_gcmd_'.$i)->toHTML().'</td></tr>';
		echo '<tr name="row_sensor"><td>'.$this->getElement('sensor_resol_temp'.$i)->getLabel().'</td><td>'.$this->getElement('sensor_resol_temp'.$i)->toHTML().'</td>';
		echo '<td>'.$this->getElement('sat_sensor_url_'.$i)->getLabel().'</td><td colspan="4">'.$this->getElement('sat_sensor_url_'.$i)->toHTML().'</td></tr>';	
	}
	
	function displayInstruForm($i){
		if ($this->dataset->nbInstruForm> 1)
			echo "<tr onmouseover = 'DeactivateButtonAddSource();'><td id='a_instrument' colspan='4' align='center'><b>Instrument ".($i+1)."</b> ".$this->getElement('instru_button_delete_'.$i)->toHTML()."</td></tr>";
		else 
			echo "<tr onmouseover = 'DeactivateButtonAddSource();'><td id='a_instrument' colspan='4' align='center'><b>Instrument</b>".$this->getElement('instru_button_delete_'.$i)->toHTML()."</td></tr>";
		$this->displayErrorsInstru($i);
		echo '<tr name="row_sensor"><td><font color="#467AA7">'.$this->getElement('sensor_gcmd'.$i)->getLabel().'</font></td><td colspan="3">'.$this->getElement('sensor_gcmd'.$i)->toHTML().'</td></tr>';
		echo '<tr name="row_sensor"><td><font color="#467AA7">'.$this->getElement('gcmd_plat_key_'.$i)->getLabel().'</font></td><td>'.$this->getElement('gcmd_plat_key_'.$i)->toHTML().'</td>';
		echo '<td><font color="#467AA7">'.$this->getElement('new_place_'.$i)->getLabel().'</font></td><td>'.$this->getElement('new_place_'.$i)->toHTML().'</td></tr>';
		echo '<tr name="row_sensor"><td>'.$this->getElement('sensor_resol_temp_'.$i)->getLabel().'</td><td>'.$this->getElement('sensor_resol_temp_'.$i)->toHTML().'</td>';
		echo '<td>'.$this->getElement('sensor_url_'.$i)->getLabel().'</td><td>'.$this->getElement('sensor_url_'.$i)->toHTML().'</td></tr>';
		echo '<tr><td>'.$this->getElement('sensor_model_'.$i)->getLabel().'</td><td>'.$this->getElement('sensor_model_'.$i)->toHTML().'</td>';
		echo '<td>'.$this->getElement('sensor_environment_'.$i)->getLabel().'</td><td colspan="3">'.$this->getElement('sensor_environment_'.$i)->toHTML().'</td></tr>';
	}
	
	function displayVaResolutionForm($simpleVersion = false){
		echo '<tr><td colspan="4" align="center"><b>Data resolution</b><br></td></tr>';
		echo '<tr><td>'.$this->getElement('sensor_lon_res')->getLabel().'</td><td>'.$this->getElement('sensor_lon_res')->toHTML().'</td>';
		echo '<td>'.$this->getElement('sensor_lat_res')->getLabel().'</td><td>'.$this->getElement('sensor_lat_res')->toHTML().'</td></tr>';
		
		echo '<tr>';
		if (!$simpleVersion){
			echo '<td>'.$this->getElement('sensor_vert_res')->getLabel().'</td><td>'.$this->getElement('sensor_vert_res')->toHTML().'</td>';
		echo '<td>'.$this->getElement('sensor_resol_tmp')->getLabel().'</td><td>'.$this->getElement('sensor_resol_tmp')->toHTML().'</td>';
		}
		if ($simpleVersion){
			echo '<td>'.$this->getElement('sensor_resol_tmp')->getLabel();
			echo "&nbsp;<img src='/img/aide-icone-16.png' onmouseout='kill()' onmouseover=\"javascript:bulle('','monthly, weekly, daily, hourly, ...')\" style='border:0px; margin-right:10px;' />";
			echo '</td><td>'.$this->getElement('sensor_resol_tmp')->toHTML().'</td><td colspan="2" />';
		}		
		echo '</tr>';
	}
	
	function displayForm($Modif = false){
		$this->addValidationRules();
		$this->initForm($Modif);
		// Affichage des erreurs
		if ( !empty($this->_errors) ){
			foreach ($this->_errors as $error) {
				if (strpos($error,'Data descr') === 0){
					echo '<a href="'.$_SERVER['REQUEST_URI'].'#a_general"><font size="3" color="red">'.$error.'</font></a><br>';
				}else if (strpos($error,'Contact') === 0){
					echo '<a href="'.$_SERVER['REQUEST_URI'].'#a_contact"><font size="3" color="red">'.$error.'</font></a><br>';
				}else if (strpos($error,'Model') === 0){
					echo '<a href="'.$_SERVER['REQUEST_URI'].'#a_model"><font size="3" color="red">'.$error.'</font></a><br>';
				}else if (strpos($error,'Satellite') === 0){
					echo '<a href="'.$_SERVER['REQUEST_URI'].'#a_satellite"><font size="3" color="red">'.$error.'</font></a><br>';
				}else if (strpos($error,'Instrument') === 0){
					echo '<a href="'.$_SERVER['REQUEST_URI'].'#a_instrument"><font size="3" color="red">'.$error.'</font></a><br>';
				}else if (strpos($error,'Source') === 0){
					echo '<a href="'.$_SERVER['REQUEST_URI'].'#a_source"><font size="3" color="red">'.$error.'</font></a><br>';
				}else if (strpos($error,'Coverage') === 0){
					echo '<a href="'.$_SERVER['REQUEST_URI'].'#a_cover"><font size="3" color="red">'.$error.'</font></a><br>';
				}else if (strpos($error,'Param') === 0){
					echo '<a href="'.$_SERVER['REQUEST_URI'].'#a_param"><font size="3" color="red">'.$error.'</font></a><br>';
				}else if (strpos($error,'Data') === 0){
					echo '<a href="'.$_SERVER['REQUEST_URI'].'#a_use"><font size="3" color="red">'.$error.'</font></a><br>';
				}else{
					echo '<font size="3" color="red">'.$error.'</font><br>';
				}
			}
		}
		$this->displayFormBegin('frmvadataset',false,true);
		//----------------------------------------------------------Data description's form------------------------------------------------------------------------------------------------
		echo '<tr><th colspan="4" align="center"><a name="a_descr" ><a name="a_general" ></a><b>Data description</b></td></tr>';
		echo '<tr><td><font color="#467AA7">'.$this->getElement('dats_title')->getLabel().'</font></td><td colspan="3">'.$this->getElement('dats_title')->toHTML().'</td></tr>';
		for ($i = 0; $i < $this->dataset->nbProj; $i++){
			echo '<tr>';
			if ($i == 0){
				echo '<td rowspan="'.($this->dataset->nbProj+1).'">Useful in the framework of</td>';
			}
			echo '<td colspan="3">'.$this->getElement('project_'.$i)->toHTML().'</td></tr>';
		}
		echo '<tr><td colspan="3" align="center">'.$this->getElement('bouton_add_projet')->toHTML().'</td></tr>';
		$this->displayDataDescrForm();
		//Document attaché
		echo '<tr><td>'.$this->getElement('upload_doc')->getLabel().'</td><td colspan="3">';		
		if (isset($this->dataset->attFile) && !empty($this->dataset->attFile)){
			echo "<a href='/downAttFile.php?file=".$this->dataset->attFile."' >".$this->dataset->attFile."</a>";
			echo $this->getElement('delete')->toHTML();
		}else{
			echo $this->getElement('upload_doc')->toHTML();
			echo $this->getElement('upload')->toHTML();
		}
		echo '</td></tr>';
		//----------------------------------------------------------------------------------------------------------------------------------------------------------------------------
		//----------------------------------------------------------Coverage's form-----------------------------------------------------------------------------------------------------
		echo '<tr><th colspan="4" align="center"><a name="a_cover" ></a><b>Coverage</b></td></tr>';
		$this->displayErrorsCoverage();
		echo '<tr><td colspan="4" align="center"><b>Temporal Coverage</b><br></td></tr>';
		echo '<tr><td>'.$this->getElement('dats_date_begin')->getLabel().'</td><td>'.$this->getElement('dats_date_begin')->toHTML()."</td>";
		echo '<td>'.$this->getElement('dats_date_end')->getLabel().'</td><td>'.$this->getElement('dats_date_end')->toHTML().'</td></tr>';
		$this->displayGeoCoverageForm();
		$this->displayVaResolutionForm();
		$this->displayGridForm();
		//----------------------------------------------------------------------------------------------------------------------------------------------------------------------------
		//----------------------------------------------------------Contact's form-----------------------------------------------------------------------------------------------------
		echo '<tr><th colspan="4" align="center"><a name="a_contact" ></a><b>Contact information</b><br></th></tr>';

		for ($i = 0; $i < $this->dataset->nbPis; $i++){
			echo '<tr><td colspan="4" align="center"><b>Contact '.($i+1).'</b><br>';//</td></tr>';
			$this->displayErrorsContact($i);
			$this->displayPersonForm($i);
		}		
		echo '<tr><td colspan="4" align="center">'.$this->getElement('bouton_add_pi')->toHTML().'</td></tr>';
		
		//----------------------------------------------------------------------------------------------------------------------------------------------------------------------------
		
		//----------------------------------------------------------Source's form------------------------------------------------------------------------------------------------
		echo '<tr><th colspan="4" align="center"><a name="a_source" ></a><b>Sources</b><br></th></tr>';
		echo'<tr><td colspan="4" align="center"> Source type '.$this->getElement('source_type')->toHTML().' '.
		$this->getElement('bouton_add_source')->toHTML().'</td></tr>';
		
		if($this->dataset->nbModForm > 1){
			for($i = 0; $i < $this->dataset->nbModForm; $i++ ){
				$this->displayModForm($i);
			}
		}
		else if ($this->dataset->nbModForm == 1){
			$this->displayModForm(0);
		}
		if($this->dataset->nbSatForm > 1){
			for($i = 0; $i < $this->dataset->nbSatForm; $i++ ){
				$this->displaySatForm($i);
			}	
		}
		else if ($this->dataset->nbSatForm == 1){
			$this->displaySatForm(0);
		}
		
		
		if($this->dataset->nbInstruForm > 1){
			for($i=0; $i < $this->dataset->nbInstruForm; $i++ ){
				$this->displayInstruForm($i);
			}
		}
		else if ($this->dataset->nbInstruForm == 1){
			$this->displayInstruForm(0);
		}			
		
		//---------------------------------------------------------------------------------------------------------------------------------------------------------------------------
		//----------------------------------------------------------Parameters's form------------------------------------------------------------------------------------------------
		echo '<tr><th colspan="4" align="center"><a name="a_param" ></a><b>Parameters</b></td></tr>';		
		for ($i = 0; $i < $this->dataset->nbVars; $i++){
			echo '<tr><td colspan="4" align="center"><b>Parameter '.($i+1).'</b>'.$this->getElement('var_id_'.$i)->toHTML().'</td></tr>';
			$this->displayErrorsParams($i);
			$this->displayParamForm($i,false,false,true);		
		}
		echo '<tr><td colspan="4" align="center">'.$this->getElement('bouton_add_variable')->toHTML().'</td></tr>';
		
		//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------
		//----------------------------------------------------------Data use form---------------------------------------------------------------------------------------------------
		echo '<tr><th colspan="4" align="center"><a name="a_use" ></a><b>Data use information</b></td></tr>';
		$this->displayErrorsUseInfo();
		echo '<tr><td>'.$this->getElement('dats_use_constraints')->getLabel().'</td><td colspan="3">'.$this->getElement('dats_use_constraints')->toHTML().'</td></tr>';
		echo '<tr><td>'.$this->getElement('data_policy')->getLabel().'</td><td colspan="3">'.$this->getElement('data_policy')->toHTML();
		echo '&nbsp;&nbsp;or add ' .$this->getElement('new_data_policy')->getLabel().'&nbsp;'.$this->getElement('new_data_policy')->toHTML().'</td></tr>';			
		echo '<tr><td>'.$this->getElement('database')->getLabel().'</td><td colspan="3">'.$this->getElement('database')->toHTML();
		echo '&nbsp;&nbsp;or add '.$this->getElement('new_database')->getLabel().'&nbsp;'.$this->getElement('new_database')->toHTML().'</td></tr>';
		echo '<td>'.$this->getElement('new_db_url')->getLabel().'</td><td>'.$this->getElement('new_db_url')->toHTML().'</td><td colspan="2"></td></tr>';
		
		echo '<tr><td><a name="a_data_format" ></a>'.$this->getElement('data_format_0')->getLabel().'</td>';
		echo '<td colspan="3">'.$this->getElement('data_format_0')->toHTML();
		echo '&nbsp;&nbsp;or add '.$this->getElement('new_data_format_0')->getLabel().''.$this->getElement('new_data_format_0')->toHTML().'</td></tr>';
		
		echo '<tr><td>'.$this->getElement('required_data_format')->getLabel().'</td><td colspan="3">'.$this->getElement('required_data_format')->toHTML().'</td></tr>';
		$this->displayFormEnd();
	}
}

?>
