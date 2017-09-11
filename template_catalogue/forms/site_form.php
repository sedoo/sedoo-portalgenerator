<?php
/*
 * Created on 20 août 2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 require_once("forms/base_site_form.php");

class site_form extends base_site_form{

	function createForm($projectName){
		global $project_name;
		if(!isset($projectName))
			$projectName=$project_name;
		$this->createFormBase();
		$this->addElement('reset','reset','Reset');
			
		$this->addElement('checkbox','dats_date_end_not_planned','not planned');
		$this->addElement('submit','bouton_add_projet','Add a project',array('onclick' => "document.getElementById('frmsite').action += '#a_general'"));
		$this->addElement('submit','bouton_add_format','Add a data format',array('onclick' => "document.getElementById('frmsite').action += '#a_data_format'"));
		$this->addElement('submit','bouton_add_pi','Add a contact',array('onclick' => "document.getElementById('frmsite').action += '#a_contact'"));

		$this->createFormPeriod($projectName);
		$this->createFormSite();
		for ($i = 0; $i < $this->dataset->nbSensors; $i++){
			$this->createFormSensor($i);
			
		}
		$this->addElement('submit','bouton_add_sensor','Add an instrument',array('onclick' => "document.getElementById('frmsite').action += '#a_sensor'"));
			
		$this->addElement('submit', 'bouton_save', 'Save');
	}

	function createFormManufacturer($i)
	{
		$man = new manufacturer;
		$man_select = $man->chargeForm($this,'manufacturer_'.$i,'Manufacturer','_'.$i); 
		$this->addElement($man_select);
		$this->addElement('text','new_manufacturer_'.$i,'new manufacturer: ');
		$this->addElement('text','new_manufacturer_url_'.$i,'Manufacturer web site');
	}
	
	function createFormSite(){

		$place = new place;
                $levels_select = $place->chargeFormSiteLevels($this,'placeByLev','Predefined site (if relevant)');
                $this->addElement($levels_select);
		
		$key = new gcmd_plateform_keyword;
		$key_select = $key->chargeForm($this,'gcmd_plat_key','Platform type');
		$this->addElement($key_select);
		$this->addElement('text','new_place','Exact Location');
				
		$this->createFormSiteBoundings();
		$this->addElement('file','upload_image','Photo');
		$this->addElement('submit','upload','Upload');
		$this->addElement('submit','delete','Delete');
	}

	function createFormSensor($i){
		$this->createFormManufacturer($i);
		$this->createFormSensorKeyword($i);
		
		$this->addElement('hidden','sensor_id_'.$i);
			
		$this->addElement('text','sensor_model_'.$i,'Model');
		$this->applyFilter('sensor_model_'.$i,'trim');	
		
		$this->addElement('text','sensor_url_'.$i,'Reference (URL or paper)');
		$this->applyFilter('sensor_url_'.$i,'trim');
		$this->addElement('text','nb_sensor_'.$i,'Number of instruments');
		$this->applyFilter('nb_sensor_'.$i,'Number of instruments must be numeric','numeric');
		$this->addElement('textarea','sensor_calibration_'.$i,'Instrument features / Calibration');
		$this->applyFilter('sensor_calibration_'.$i,'trim');
		
		$this->createFormResolution($i);
		$this->getElement('sensor_resol_temp_'.$i)->setLabel("Observation frequency");
		$this->getElement('sensor_vert_resolution_'.$i)->setLabel("Vertical coverage");
		$this->getElement('sensor_horiz_resolution_'.$i)->setLabel("Horizontal coverage");
		
		$this->addElement('text','sensor_latitude_'.$i,'Latitude (°)');
		$this->addElement('text','sensor_longitude_'.$i,'Longitude (°)');
		$this->addElement('text','sensor_altitude_'.$i,'Height above ground (m)');
		$this->addElement('textarea','sensor_environment_'.$i,'Instrument environment',array('cols'=>70, 'rows'=>5));
		$this->applyFilter('sensor_environment_'.$i,'trim');

		for ($j = 0; $j < $this->dataset->dats_sensors[$i]->nbVars; $j++)
		{
			$this->createFormVariable($i,$j);
		}
		$this->addElement('submit','bouton_add_variable_'.$i,'Add a parameter',array('onclick' => "document.getElementById('frmsite').action += '#a_param_".$i."'")); 
		for ($j = 0; $j < $this->dataset->dats_sensors[$i]->nbCalcVars; $j++)
		{
			$this->createFormVariable($i,$j,'calcul');
		}
		$this->addElement('submit','bouton_add_variable_calcul_'.$i,'Add a derived parameter',array('onclick' => "document.getElementById('frmsite').action += '#a_param_calcul_".$i."'"));
	}


	/*
	 * Ajoute aux éléments du formulaires des attributs et/ou des regles de validation en fonction de la valeur d'autres champs.
	 */
	function addValidationRules(){
		
		$this->addvalidationRulesBase();
		
		
		$this->registerRule('required_if_not_void','function','required_if_not_void');
		$this->registerRule('required_if_not_void2','function','required_if_not_void2');
		
		
		$this->registerRule('existe2','function','existInDb');
		
		$this->registerRule('validBoundings','function','validBoundings');
		$this->registerRule('completeBoundings','function','completeBoundings');
			
		$this->registerRule('contact_organism_required','function','contact_organism_required');
		$this->registerRule('contact_email_required','function','contact_email_required');
			
		$this->registerRule('distinct','function','distinct');
		$this->registerRule('not_void','function','not_void');

		
					
		//Sensors
		for ($i = 0; $i < $this->dataset->nbSensors; $i++)
		{
			$this->addRule('sensor_latitude_'.$i,'Instrument: Latitude must be numeric','numeric');
			$this->addRule('sensor_latitude_'.$i,'Instrument: Latitude is incorrect','number_range',array(-90,90));
			$this->addRule('sensor_longitude_'.$i,'Instrument: Longitude must be numeric','numeric');
			$this->addRule('sensor_longitude_'.$i,'Instrument: Longitude is incorrect','number_range',array(-180,180));
			$this->addRule(array('sensor_longitude_'.$i, 'sensor_longitude','sensor_latitude', 'sensor_latitude'), 'Instrument: Incomplete coordinates', 'completeBoundings');
			$this->addRule('sensor_altitude_'.$i,'Instrument: Height above ground must be numeric','numeric');
			$this->addRule('sensor_model_'.$i,'Instrument: Model exceeds the maximum length allowed (100 characters)','maxlength',100);
			$this->addValidationRulesResolution();
			$this->addRule('sensor_calibration_'.$i,'Instrument: Calibration exceeds the maximum length allowed (250 characters)','maxlength',250);
				
			$this->addRule('new_manufacturer_'.$i,'Instrument: Manufacturer name exceeds the maximum length allowed (250 characters)','maxlength',250);
			$this->addRule('new_manufacturer_url_'.$i,'Instrument: Manufacturer url exceeds the maximum length allowed (250 characters)','maxlength',250);
	
			if (isset($this->dataset->dats_sensors[$i]->sensor->manufacturer) && !empty($this->dataset->dats_sensors[$i]->sensor->manufacturer) && $this->dataset->dats_sensors[$i]->sensor->manufacturer->manufacturer_id > 0){
				$this->getElement('new_manufacturer_'.$i)->setAttribute('onfocus','blur()');
				$this->getElement('new_manufacturer_url_'.$i)->setAttribute('onfocus','blur()');
			}else {
			}
			//Variables
			$indiceTableVar = 0;
			$indiceTableVarCalc = 0;
			if (isset($this->dataset->dats_sensors[$i]->sensor->sensor_vars) && !empty($this->dataset->dats_sensors[$i]->sensor->sensor_vars)){
				for ($j = 0; $j < count($this->dataset->dats_sensors[$i]->sensor->sensor_vars);$j++){

					$suffix = "";
					$prefixMsg = "";
	
					if ($this->dataset->dats_sensors[$i]->sensor->sensor_vars[$j]->flag_param_calcule == 1){
						$suffix = "calcul".$i.'_'.$indiceTableVarCalc;
						$indiceTableVarCalc++;
						$prefixMsg = "Derived parameter ".$indiceTableVarCalc;
					}else{
						$suffix = $i.'_'.$indiceTableVar;
						$indiceTableVar++;
						$prefixMsg = "Measured parameter ".$indiceTableVar;
					}
					$this->addValidationRulesVariable($i,$j,$suffix,$prefixMsg);
				}
			}
		}
			
		//Sites
		$this->addRule('gcmd_plat_key','Site name is required when a platform type is selected','required_if_not_void',array($this,'new_place'));
			$this->addRule('gcmd_plat_key','Platform type is required','required_if_not_void2',array($this,'new_place'));
			$this->addRule('new_place','Name exceeds the maximum length allowed (100 characters)','maxlength',100);
			$this->addValidationRulesSiteBoundings('Site ');
	}

	function initForm(){
		$dataset = & $this->dataset;
			
		$this->initFormBase();

		$this->getElement('dats_date_end_not_planned')->setChecked($this->dataset->dats_date_end_not_planned);

		//PERIOD
		$this->getElement('period')->setSelected($this->dataset->period_id);
		
		//SITE, 1 seul par dataset
		$this->initFormSiteBoundings();

		if (isset($dataset->sites[0]->parent_place)  && !empty($dataset->sites[0]->parent_place)){
                	$table = array();
                        $predSite = $dataset->sites[0]->parent_place;
                        $type = 0;
                        for ($j=3;$j >0;$j--){
                        	if ($predSite->place_level == $j){
                                	$table[$j] = $predSite->place_id;
                                        $type = $predSite->gcmd_plat_id;
                                        $predSite = $predSite->parent_place;
                                 }else
                                        $table[$j] = 0;
                         }
                         $table[0] = $type;
                         ksort($table);
                         $this->getElement('placeByLev')->setValue($table);
                 }



		if (isset($dataset->sites[0]->gcmd_plateform_keyword) && !empty($dataset->sites[0]->gcmd_plateform_keyword)){
					$this->getElement('gcmd_plat_key')->setSelected($dataset->sites[0]->gcmd_plateform_keyword->gcmd_plat_id);
		}

		$this->getElement('new_place')->setValue($dataset->sites[0]->place_name);
		
		
		//SENSOR, plusieurs par dataset
		if (isset($dataset->dats_sensors) && !empty($dataset->dats_sensors))
		{
			for ($i = 0; $i < $dataset->nbSensors; $i++)
			{
				if (isset($dataset->dats_sensors[$i]) && !empty($dataset->dats_sensors[$i])){
					if (isset($dataset->dats_sensors[$i]->sensor->manufacturer) && !empty($dataset->dats_sensors[$i]->sensor->manufacturer)){
						$this->getElement('manufacturer_'.$i)->setSelected($dataset->dats_sensors[$i]->sensor->manufacturer->manufacturer_id);
						$this->getElement('new_manufacturer_'.$i)->setValue($dataset->dats_sensors[$i]->sensor->manufacturer->manufacturer_name);
						$this->getElement('new_manufacturer_url_'.$i)->setValue($dataset->dats_sensors[$i]->sensor->manufacturer->manufacturer_url);
					}
		
					if (isset($dataset->dats_sensors[$i]->sensor->gcmd_instrument_keyword) && !empty($dataset->dats_sensors[$i]->sensor->gcmd_instrument_keyword)){
						$this->getElement('sensor_gcmd_'.$i)->setSelected($dataset->dats_sensors[$i]->sensor->gcmd_instrument_keyword->gcmd_sensor_id);
					}

					$this->getElement('sensor_id_'.$i)->setValue($dataset->dats_sensors[$i]->sensor->sensor_id);
					$this->getElement('sensor_model_'.$i)->setValue($dataset->dats_sensors[$i]->sensor->sensor_model);
					$this->getElement('sensor_calibration_'.$i)->setValue($dataset->dats_sensors[$i]->sensor->sensor_calibration);
					$this->initFormResolution($i);
						
					$this->getElement('sensor_url_'.$i)->setValue($dataset->dats_sensors[$i]->sensor->sensor_url);
		
					if (isset($dataset->dats_sensors[$i]->nb_sensor) && !empty($dataset->dats_sensors[$i]->nb_sensor)){
						$this->getElement('nb_sensor_'.$i)->setValue($dataset->dats_sensors[$i]->nb_sensor);
					}
					if (isset($dataset->dats_sensors[$i]->sensor->boundings) && !empty($dataset->dats_sensors[$i]->sensor->boundings)){
						//pour les instruments fixes seulement, lt_min = lat_max et lon_min = lon_max
						$this->getElement('sensor_longitude_'.$i)->setValue($dataset->dats_sensors[$i]->sensor->boundings->west_bounding_coord);
						$this->getElement('sensor_latitude_'.$i)->setValue($dataset->dats_sensors[$i]->sensor->boundings->north_bounding_coord);
					}
					if (isset($dataset->dats_sensors[$i]->sensor->sensor_elevation))
						$this->getElement('sensor_altitude_'.$i)->setValue($dataset->dats_sensors[$i]->sensor->sensor_elevation);
					if (isset($dataset->dats_sensors[$i]->sensor->sensor_environment))
						$this->getElement('sensor_environment_'.$i)->setValue($dataset->dats_sensors[$i]->sensor->sensor_environment);
				}
				//VARIABLES
				$indiceTableVar = 0;
				$indiceTableVarCalc = 0;
				if (isset($dataset->dats_sensors[$i]->sensor->sensor_vars) && !empty($dataset->dats_sensors[$i]->sensor->sensor_vars))
				{
					for ($j = 0; $j < count($dataset->dats_sensors[$i]->sensor->sensor_vars);$j++){
		
						$suffix = "";
						echo 'flag_param_calcule = '.$dataset->dats_sensors[$i]->sensor->sensor_vars[$j]->flag_param_calcule.'<br>';
		
						if ($dataset->dats_sensors[$i]->sensor->sensor_vars[$j]->flag_param_calcule == 1){
							$nb = $indiceTableVarCalc++;
							$suffix = "calcul";
							$this->initFormVariable($i,$j,$nb,$suffix);
						}else{
							$nb = $indiceTableVar++;
							$suffix = "";
							$this->initFormVariable($i,$j,$nb,$suffix);
						}
					}

				}
			}
		}
	}

	

	function displayErrorsInstru($i){
		$this->displayErrors(array('sensor_gcmd_'.$i,'new_manufacturer_'.$i,'new_manufacturer_url_'.$i,'sensor_model_'.$i,'sensor_calibration_'.$i,
  			'sensor_resol_temp_'.$i,'sensor_vert_resolution_'.$i,'sensor_horiz_resolution_'.$i,'sensor_url_'.$i,'sensor_latitude_'.$i,
			'sensor_longitude_'.$i,'sensor_altitude_'.$i,'sensor_environment_'.$i));
	}

	function displayErrorsSite(){
		$this->displayErrors(array('gcmd_plat_key','place','new_place','west_bound','east_bound','north_bound',
  			'south_bound','place_alt_min','place_alt_max'));
	}

	

	function displayForm($nb_pi,$nb_sensors,$tab_nbVars,$tab_nbCalcVars){

		$this->addValidationRules();

		$this->initForm();

		// Affichage des erreurs
		if ( !empty($this->_errors) ){
			foreach ($this->_errors as $error) {
				if (strpos($error,'General') === 0){
					echo '<a href="'.$_SERVER['REQUEST_URI'].'#a_general"><font size="3" color="red">'.$error.'</font></a><br>';
				}else if (strpos($error,'Contact') === 0){
					echo '<a href="'.$_SERVER['REQUEST_URI'].'#a_contact"><font size="3" color="red">'.$error.'</font></a><br>';
				}else if (strpos($error,'Instru') === 0){
					echo '<a href="'.$_SERVER['REQUEST_URI'].'#a_instru"><font size="3" color="red">'.$error.'</font></a><br>';
				}else if (strpos($error,'Site') === 0){
					echo '<a href="'.$_SERVER['REQUEST_URI'].'#a_site"><font size="3" color="red">'.$error.'</font></a><br>';
				}else if (strpos($error,'Measured') === 0){
					echo '<a href="'.$_SERVER['REQUEST_URI'].'#a_param"><font size="3" color="red">'.$error.'</font></a><br>';
				}else if (strpos($error,'Derived') === 0){
					echo '<a href="'.$_SERVER['REQUEST_URI'].'#a_param_calcul"><font size="3" color="red">'.$error.'</font></a><br>';
				}else if (strpos($error,'Data') === 0){
					echo '<a href="#a_use"><font size="3" color="red">'.$error.'</font></a><br>';
				}else{
					echo '<font size="3" color="red">'.$error.'</font><br>';
				}
			}
		}
		echo '<div id="errors" color="red"></div><br>';


		if (strpos($_SERVER['REQUEST_URI'],'&datsId')){
                        $reqUri = substr($_SERVER['REQUEST_URI'],0,strpos($_SERVER['REQUEST_URI'],'&datsId'));
                }else  if (strpos($_SERVER['REQUEST_URI'],'?datsId')){
                        $reqUri = substr($_SERVER['REQUEST_URI'],0,strpos($_SERVER['REQUEST_URI'],'?datsId'));
                }else{
		       $reqUri = $_SERVER['REQUEST_URI'];
		}
		
		echo '<form action="'.$reqUri.'" method="post" name="frmsite" id="frmsite" enctype="multipart/form-data">'; 
		
		echo '<SCRIPT LANGUAGE="Javascript" SRC="/js/functions.js"> </SCRIPT>';
		echo $this->getElement('dats_id')->toHTML();
		echo '<table><tr><th class="top" colspan="3" align="left"><font color="#467AA7">Required fields are in blue</font></td><th class="top" align="right">';
		echo $this->getElement('reset')->toHTML().'</td></tr>';
		
		echo '<tr><th colspan="4" align="center"><a name="a_general" ></a><b>General information</b></th></tr>';
		$this->displayErrorsGeneralInfo();
		echo '<tr><td><font color="#467AA7">'.$this->getElement('dats_title')->getLabel().'</font></td><td colspan="3">'.$this->getElement('dats_title')->toHTML().'</td></tr>';

		echo '<tr><td rowspan="2">'.$this->getElement('period')->getLabel().'</td><td rowspan="2">'.$this->getElement('period')->toHTML().'</td><td>'.$this->getElement('dats_date_begin')->getLabel().'</td><td>'.$this->getElement('dats_date_end')->getLabel()."</td></tr>";
		echo '<tr><td>'.$this->getElement('dats_date_begin')->toHTML().'</td><td>'.$this->getElement('dats_date_end')->toHTML().'<br>'.$this->getElement('dats_date_end_not_planned')->toHTML().'&nbsp;'.$this->getElement('dats_date_end_not_planned')->getLabel().'</td></tr>';

		for ($i = 0; $i < $this->dataset->nbProj; $i++){
			echo '<tr>';
			if ($i == 0){
				echo '<td rowspan="'.($this->dataset->nbProj+1).'">Project'.(($this->dataset->nbProj > 1)?'s':'').'</td>';
			}
			echo '<td colspan="3">'.$this->getElement('project_'.$i)->toHTML().'</td></tr>';
		}
		echo '<tr><td colspan="3" align="center">'.$this->getElement('bouton_add_projet')->toHTML().'</td></tr>';

		echo '</tr><tr>';
		echo '<tr><th colspan="4" align="center"><a name="a_contact" ></a><b>Contact information</b></td></tr><tr>';
		for ($i = 0; $i < $this->dataset->nbPis; $i++){
			echo '<tr><td colspan="4" align="center"><b>Contact '.($i+1).'</b><br>';//</td></tr>';
			$this->displayErrorsContact($i);
   			$this->displayPersonForm($i);
		}		
		echo '<tr><td colspan="4" align="center">'.$this->getElement('bouton_add_pi')->toHTML().'</td></tr>';
		$this->displayDataDescrForm();
		
		echo '<tr><th colspan="4" align="center"><a name="a_site" ></a><b>Site information</b></td></tr>';
		
		$this->displayErrorsSite();
		echo '<tr><td>'.$this->getElement('placeByLev')->getLabel().'</td><td colspan="3">'.$this->getElement('placeByLev')->toHTML().'</td></tr>';

		echo '<tr><td>'.$this->getElement('new_place')->getLabel().'</td><td>'.$this->getElement('new_place')->toHTML().'</td>';
		echo '<td>'.$this->getElement('gcmd_plat_key')->getLabel().'</td><td>'.$this->getElement('gcmd_plat_key')->toHTML().'</td></tr>';

		$this->displaySiteBoundingsForm();
		echo '<tr><td>'.$this->getElement('upload_image')->getLabel().'</td>';
		if (isset($this->dataset->image) && !empty($this->dataset->image)){
			echo '<td><a href="'.$this->dataset->image.'" target=_blank><img src="'.$this->dataset->image.'" width="50" /></a>';
			echo $this->getElement('delete')->toHTML().'</td><td colspan="2" />';
		}else{
			echo '<td/><td colspan=2><input type="hidden" name="MAX_FILE_SIZE" value="2000000" />'.$this->getElement('upload_image')->toHTML();
			echo $this->getElement('upload')->toHTML().'</td>';
		}
		echo '</tr>';
		
		echo '<tr><th colspan="4" align="center"><a name="a_instru" ></a><b>Instrument information</b></td></tr>';
		for ($i = 0; $i < $nb_sensors; $i++)
		{
			echo '<tr><td colspan="4" align="center"><b>Instrument '.($i+1).' </b></td></tr>';
			$this->displayErrorsInstru($i);
			echo '<tr><td>'.$this->getElement('sensor_gcmd_'.$i)->getLabel().'</td><td colspan="3">'.$this->getElement('sensor_gcmd_'.$i)->toHTML().'</td></tr>';
	
			echo '<tr><td>'.$this->getElement('manufacturer_'.$i)->getLabel().'</td><td colspan="3">'.$this->getElement('manufacturer_'.$i)->toHTML();
			echo '&nbsp;&nbsp;or add '.$this->getElement('new_manufacturer_'.$i)->getLabel().''.$this->getElement('new_manufacturer_'.$i)->toHTML().'</td></tr>';
			echo '<tr><td>'.$this->getElement('new_manufacturer_url_'.$i)->getLabel().'</td><td colspan="3">'.$this->getElement('new_manufacturer_url_'.$i)->toHTML().'</td></tr>';
			echo '<tr><td>'.$this->getElement('sensor_model_'.$i)->getLabel().'</td><td>'.$this->getElement('sensor_model_'.$i)->toHTML().'</td>';
			echo '<td>'.$this->getElement('sensor_calibration_'.$i)->getLabel().'</td><td>'.$this->getElement('sensor_calibration_'.$i)->toHTML().'</td></tr>';
			echo '<tr><td>'.$this->getElement('sensor_resol_temp_'.$i)->getLabel().'</td><td>'.$this->getElement('sensor_resol_temp_'.$i)->toHTML().'</td><td colspan="2"></td></tr>';
	
			echo '<tr><td>'.$this->getElement('sensor_horiz_resolution_'.$i)->getLabel().'</td><td>'.$this->getElement('sensor_horiz_resolution_'.$i)->toHTML().'</td>';
			echo '<td>'.$this->getElement('sensor_vert_resolution_'.$i)->getLabel().'</td><td>'.$this->getElement('sensor_vert_resolution_'.$i)->toHTML().'</td></tr>';
			echo '<tr><td>'.$this->getElement('sensor_longitude_'.$i)->getLabel().'</td><td>'.$this->getElement('sensor_longitude_'.$i)->toHTML().'</td>';
			echo '<td>'.$this->getElement('sensor_latitude_'.$i)->getLabel().'</td><td>'.$this->getElement('sensor_latitude_'.$i)->toHTML().'</td></tr>';
			echo '<tr><td>'.$this->getElement('sensor_altitude_'.$i)->getLabel().'</td><td>'.$this->getElement('sensor_altitude_'.$i)->toHTML().'</td>';
			echo '<td>'.$this->getElement('sensor_url_'.$i)->getLabel().'</td><td>'.$this->getElement('sensor_url_'.$i)->toHTML().'</td></tr>';
			echo '<tr><td>'.$this->getElement('sensor_environment_'.$i)->getLabel().'</td><td colspan="3">'.$this->getElement('sensor_environment_'.$i)->toHTML().'</td></tr>';
			echo '<tr><th colspan="4" align="center"><a name="a_param_'.$i.'" ></a><b>Measured parameters</b></td></tr>';

			for ($j = 0; $j < $tab_nbVars[$i]; $j++){
				echo '<tr><td colspan="4" align="center"><b>Measured parameter '.($j+1).' by instrument '.($i+1).'</b>'.$this->getElement('var_id_'.$i.'_'.$j)->toHTML().'</td></tr>';
				$this->displayErrorsParams($i,$j);
				$this->displayParamForm($i,$j,true);		
			}
			echo '<tr><td colspan="4" align="center">'.$this->getElement('bouton_add_variable_'.$i)->toHTML().'</td></tr>';
			echo '<tr><th colspan="4" align="center"><a name="a_param_calcul_'.$i.'" ></a><b>Derived parameters (if relevant)</b></td></tr>';
			for ($j = 0; $j < $tab_nbCalcVars[$i]; $j++){
				echo '<tr><td colspan="4" align="center"><b>Derived parameter '.($j+1).' for instrument '.($i+1).'</b>'.$this->getElement('var_id_calcul'.$i.'_'.$j)->toHTML().'</td></tr>';
				$this->displayErrorsParams('calcul'.$i,$j);
				$this->displayParamForm('calcul'.$i,$j,true);		
			}
			echo '<tr><td colspan="4" align="center">'.$this->getElement('bouton_add_variable_calcul_'.$i)->toHTML().'</td></tr>';
		}
		echo '<tr><td colspan="4" align="center">'.$this->getElement('bouton_add_sensor')->toHTML().'</td></tr>';
		
		
		echo '<tr><th colspan="4" align="center"><a name="a_use" ></a><b>Data use information</b></td></tr>';
		$this->displayErrorsUseInfo();
		echo '<tr><td>'.$this->getElement('dats_use_constraints')->getLabel().'</td><td colspan="3">'.$this->getElement('dats_use_constraints')->toHTML().'</td></tr>';
		echo '<tr><td>'.$this->getElement('data_policy')->getLabel().'</td><td colspan="3">'.$this->getElement('data_policy')->toHTML();
		echo '&nbsp;&nbsp;or add ' .$this->getElement('new_data_policy')->getLabel().'&nbsp;'.$this->getElement('new_data_policy')->toHTML().'</td></tr>';
		echo '<tr><td>'.$this->getElement('database')->getLabel().'</td><td colspan="3">'.$this->getElement('database')->toHTML();
		echo '&nbsp;&nbsp;or add '.$this->getElement('new_database')->getLabel().'&nbsp;'.$this->getElement('new_database')->toHTML().'</td></tr>';
		echo '<td>'.$this->getElement('new_db_url')->getLabel().'</td><td>'.$this->getElement('new_db_url')->toHTML().'</td><td colspan="2"></td></tr>';

		for ($i = 0; $i < $this->dataset->nbFormats; $i++){
			echo '<tr>';
			if ($i == 0){
				echo '<td rowspan="'.($this->dataset->nbFormats+1).'"><a name="a_data_format" ></a>Data formats'.(($this->dataset->nbFormats > 1)?'s':'').'</td>';
			}
			echo '<td colspan="3">'.$this->getElement('data_format_'.$i)->toHTML();
			echo '&nbsp;&nbsp;or add '.$this->getElement('new_data_format_'.$i)->getLabel().''.$this->getElement('new_data_format_'.$i)->toHTML().'</td></tr>';
		}
		echo '<tr><td colspan="3" align="center">'.$this->getElement('bouton_add_format')->toHTML().'</td></tr>';

		echo '<tr>';
		echo '<th colspan="4" align="center">'.$this->getElement('bouton_save')->toHTML().'</th></tr></table>';
		echo '</form>';
	}
	

	function saveForm($nb_pi,$nb_sensors,$tab_nbVars,$tab_nbCalcVars){
		$dataset = & $this->dataset;

		$this->saveFormBase();
		
		$dataset->dats_date_end_not_planned = $this->getElement('dats_date_end_not_planned')->getChecked();
		
		//SITE
		$dataset->sites = array();
		$dataset->sites[0] = new place;
		
		$sitesLev = $this->exportValue('placeByLev');
		$pred_site_id = 0;
		for ($j = 3;$j >= 0;$j--){
			if (isset($sitesLev[$j]) && $sitesLev[$j] > 0){
				$pred_site_id = $sitesLev[$j];
				break;
			}
		}
        $dataset->sites[0]->pla_place_id = $pred_site_id;
		
		$dataset->sites[0]->place_name = $this->exportValue('new_place');
		if (empty($dataset->sites[0]->place_name)){
			$dataset->sites[0]->place_id = -1;
		}
				
		$dataset->sites[0]->gcmd_plat_id = $this->exportValue('gcmd_plat_key');
		if ($dataset->sites[0]->gcmd_plat_id != 0)	{
			$dataset->sites[0]->gcmd_plateform_keyword = new gcmd_plateform_keyword;
			$dataset->sites[0]->gcmd_plateform_keyword = $dataset->sites[0]->gcmd_plateform_keyword->getById($dataset->sites[0]->gcmd_plat_id);
		}
		$this->saveFormSiteBoundings();
		
		// SENSOR
		unset($dataset->dats_sensors);
		$dataset->dats_sensors = array();

		for ($i = 0; $i < $nb_sensors; $i++)
		{
			$dataset->dats_sensors[$i] = new dats_sensor();
			$dataset->dats_sensors[$i]->nbVars = $tab_nbVars[$i];
			$dataset->dats_sensors[$i]->nbCalcVars = $tab_nbCalcVars[$i];
			$dataset->dats_sensors[$i]->sensor = new sensor;
	
			$sensId = $this->exportValue('sensor_id_'.$i);
			if ( isset($sensId) && ( strlen($sensId) > 0 ) ){
				$dataset->dats_sensors[$i]->sensor->sensor_id = $sensId;
			}else{
				$dataset->dats_sensors[$i]->sensor->sensor_id = 0;
			}
	
			$dataset->dats_sensors[$i]->sensor->gcmd_sensor_id = $this->exportValue('sensor_gcmd_'.$i);
	
			if ($dataset->dats_sensors[$i]->sensor->gcmd_sensor_id != 0)
			{
				$dataset->dats_sensors[$i]->sensor->gcmd_instrument_keyword = new gcmd_instrument_keyword;
				$dataset->dats_sensors[$i]->sensor->gcmd_instrument_keyword = $dataset->dats_sensors[$i]->sensor->gcmd_instrument_keyword->getById($dataset->dats_sensors[$i]->sensor->gcmd_sensor_id);
			}
	
			$dataset->dats_sensors[$i]->sensor->manufacturer = new manufacturer;
			$dataset->dats_sensors[$i]->sensor->manufacturer->manufacturer_id = $this->exportValue('manufacturer_'.$i);
			$dataset->dats_sensors[$i]->sensor->manufacturer->manufacturer_name = $this->exportValue('new_manufacturer_'.$i);
			$dataset->dats_sensors[$i]->sensor->manufacturer->manufacturer_url = $this->exportValue('new_manufacturer_url_'.$i);
	
			if (empty($dataset->dats_sensors[$i]->sensor->manufacturer->manufacturer_id) && empty($dataset->dats_sensors[$i]->sensor->manufacturer->manufacturer_name)){
				$dataset->dats_sensors[$i]->sensor->manufacturer->manufacturer_id = -1;
			}
	
			$dataset->dats_sensors[$i]->sensor->manufacturer_id = & $dataset->dats_sensors[$i]->sensor->manufacturer->manufacturer_id;
	
			$dataset->dats_sensors[$i]->sensor->sensor_url = $this->exportValue('sensor_url_'.$i);
			$dataset->dats_sensors[$i]->sensor->sensor_model = $this->exportValue('sensor_model_'.$i);
			$dataset->dats_sensors[$i]->sensor->sensor_calibration = $this->exportValue('sensor_calibration_'.$i);
			$this->saveFormResolution($i);
			
			$dataset->dats_sensors[$i]->nb_sensor = $this->exportValue('nb_sensor_'.$i);
			$dataset->dats_sensors[$i]->sensor->sensor_elevation = $this->exportValue('sensor_altitude_'.$i);
			$lat = $this->exportValue('sensor_latitude_'.$i);
			$lon = $this->exportValue('sensor_longitude_'.$i);
			$dataset->dats_sensors[$i]->sensor->boundings = new boundings;
			if ( isset($lon) && strlen($lon) > 0 ){
				$dataset->dats_sensors[$i]->sensor->boundings->west_bounding_coord = $lon;
				$dataset->dats_sensors[$i]->sensor->boundings->east_bounding_coord = $lon;
			}else
			$dataset->dats_sensors[$i]->sensor->bound_id = -1;
			if ( isset($lat) && strlen($lat) > 0 ){
				$dataset->dats_sensors[$i]->sensor->boundings->north_bounding_coord = $lat;
				$dataset->dats_sensors[$i]->sensor->boundings->south_bounding_coord = $lat;
			}else
			$dataset->dats_sensors[$i]->sensor->bound_id = -1;
			$dataset->dats_sensors[$i]->sensor->sensor_environment = $this->exportValue('sensor_environment_'.$i);
			$dataset->dats_sensors[$i]->sensor->sensor_vars = array();
			$incr = $this->saveFormVariables($i,$tab_nbVars[$i]);
			$this->saveFormVariables($i,$tab_nbCalcVars[$i],1,'calcul',$incr);
		}

		//PERIOD
		$dataset->period = new period;
		$dataset->period->period_id = $this->exportValue('period');

		if (isset($dataset->period->period_id) && $dataset->period->period_id != 0){
			$dataset->period = $dataset->period->getById($dataset->period->period_id);
		}
		$dataset->period_id = & $dataset->period->period_id;
		
	}

	function addPi(){
		$this->createFormPersonne($this->dataset->nbPis-1);
	}

	function addFormat(){
		$this->createFormDataFormat($this->dataset->nbFormats-1);
	}

	function addProjet(){
		$this->createFormProject($this->dataset->nbProj-1);
	}

	function addSensor(){
		$this->createFormSensor($this->dataset->nbSensors-1);
	}

	function addVariable($i){
		$this->createFormVariable($i,$this->dataset->dats_sensors[$i]->nbVars-1,'');
	}

	function addVariableCalcul($i){
		$this->createFormVariable($i,$this->dataset->dats_sensors[$i]->nbCalcVars-1,'calcul');
	}

}
?>
