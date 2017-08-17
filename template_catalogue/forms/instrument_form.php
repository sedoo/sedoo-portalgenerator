<?php
/*
 * Created on 12 juil. 2010
 *
 * Formulaire pour un instrument -> on suppose un seul instrument par dataset, avec plusieurs sites et variables possibles
 * C'est pas top, mais répond au besoin urgent. Je ferai mieux en rentrant de vacances
 */
require_once("forms/base_form.php");

class instrument_form extends base_form{

	function createForm(){
		global $project_name;
		$this->createFormBase();
		$this->addElement('reset','reset','Reset');			
		$this->addElement('checkbox','dats_date_end_not_planned','not planned');
		$this->addElement('submit','bouton_add_projet','Add a project',array('onclick' => "document.getElementById('frminstr').action += '#a_general'"));
		$this->addElement('submit','bouton_add_format','Add a data format',array('onclick' => "document.getElementById('frminstr').action += '#a_data_format'"));
		$this->addElement('submit','bouton_add_pi','Add a contact',array('onclick' => "document.getElementById('frminstr').action += '#a_contact'"));
		$this->addElement('submit','bouton_add_variable','Add a measured parameter',array('onclick' => "document.getElementById('frminstr').action += '#a_param'"));
		$this->createFormPeriod($project_name);
		$this->createFormSensor();
		$this->addElement('file','upload_doc','Attached document');
		$this->addElement('submit','upload_doc_button','Upload');
		$this->addElement('submit','delete_doc_button','Delete');
		
		for ($i = 0; $i < $this->dataset->nbSites; $i++){
			$this->createFormSite($i);
		}
		$this->addElement('submit','bouton_add_site','Add a site',array('onclick' => "document.getElementById('frminstr').action += '#a_site'"));
			
		for ($i = 0; $i < $this->dataset->nbCalcVars; $i++){
			$this->createFormVariable($i,'calcul');
		}
		$this->addElement('submit','bouton_add_variable_calcul','Add a derived parameter',array('onclick' => "document.getElementById('frminstr').action += '#a_param_calcul'"));
			
		$this->addElement('submit', 'bouton_save', 'Save');
	}

	function createFormManufacturer()
	{
		$man = new manufacturer;
		$man_select = $man->chargeForm($this,'manufacturer','Manufacturer');
		$this->addElement($man_select);
		$this->addElement('text','new_manufacturer','new manufacturer: ');
		$this->addElement('text','new_manufacturer_url','Manufacturer web site');
	}
	
	function createFormSite($i){

		$location = new gcmd_location_keyword();
		$loc_select = $location->chargeFormLoc($this, 'locationByLev'.$i, 'Location Keyword');
		$this->addElement($loc_select);
		
		/*$place = new place;
		$levels_select = $place->chargeFormSiteLevels($this,'placeByLev_'.$i,'Predefined site (if relevant)');
		$this->addElement($levels_select);*/
		
		
		$key = new gcmd_plateform_keyword;
		$key_select = $key->chargeForm($this,'gcmd_plat_key_'.$i,'Platform type');
		$this->addElement($key_select);
		
		$k = new gcmd_plateform_keyword;
		$k_select = $k->chargeFormPlat($this, 'platByLev'.$i, 'Platform Keyword');
		$this->addElement($k_select);
			
		/*$place = new place;
		$place_select = $place->chargeForm($this,'place_'.$i,'Site',$i);
		$this->addElement($place_select);*/
			
		$this->addElement('text','new_place_'.$i,'Exact location', array('size'=>50));
				
		$this->createFormSiteBoundings($i);
		$this->addElement('textarea','sensor_environment_'.$i,'Instrument environment',array('cols'=>60, 'rows'=>5));
		$this->applyFilter('sensor_environment_'.$i,'trim');

	}

	function createFormSensor(){
		$this->createFormManufacturer();
		//$this->createFormSensorKeyword();
		$this->createFormSensorKeywords();
		
		$this->addElement('hidden','sensor_id');
			
		$this->addElement('text','sampler','Sampler (if relevant)', array('size'=>50));
		$this->addElement('text','sensor_model','Model');
		$this->applyFilter('sensor_model','trim');	
		
		$this->addElement('text','sensor_url','Reference (URL or paper)');
		$this->applyFilter('sensor_url','trim');
		$this->addElement('text','nb_sensor','Number of instruments');
		$this->applyFilter('nb_sensor','Number of instruments must be numeric','numeric');
		$this->addElement('textarea','sensor_calibration','Instrument features / Calibration');
		$this->applyFilter('sensor_calibration','trim');
		
		$this->createFormResolution();
		$this->getElement('sensor_resol_temp')->setLabel("Observation frequency");
		$this->getElement('sensor_vert_resolution')->setLabel("Vertical coverage");
		$this->getElement('sensor_lat_resolution')->setLabel("Horizontal coverage");
		
		//$this->addElement('text','sensor_precision','Precision');
		$this->addElement('text','sensor_latitude','Latitude (°)');
		$this->addElement('text','sensor_longitude','Longitude (°)');
		$this->addElement('text','sensor_altitude','Height above ground (m)');

		$this->addElement('file','upload_image','Photo');
		//$this->addElement('hidden','sensor_image');
		$this->addElement('submit','upload','Upload');//,array('onclick' => "uploadImage('upload_image')"));
		$this->addElement('submit','delete','Delete');
	}


	/*
	 * Ajoute aux éléments du formulaires des attributs et/ou des regles de validation en fonction de la valeur d'autres champs.
	 */
	function addValidationRules(){
		
		$this->addvalidationRulesBase();
		
		
		$this->registerRule('required_if_not_void','function','required_if_not_void');
		$this->registerRule('required_if_not_void2','function','required_if_not_void2');
		$this->registerRule('required_if_not_void3','function','required_if_not_void3');
		
		$this->registerRule('existe2','function','existInDb');
		
		$this->registerRule('validBoundings','function','validBoundings');
		$this->registerRule('completeBoundings','function','completeBoundings');
			
		$this->registerRule('contact_organism_required','function','contact_organism_required');
		$this->registerRule('contact_email_required','function','contact_email_required');
			
		$this->registerRule('distinct','function','distinct');
		$this->registerRule('not_void','function','not_void');

		
					
		//Sensor
		$this->addRule('sensor_latitude','Instrument: Latitude must be numeric','numeric');
		$this->addRule('sensor_latitude','Instrument: Latitude is incorrect','number_range',array(-90,90));
		$this->addRule('sensor_longitude','Instrument: Longitude must be numeric','numeric');
		$this->addRule('sensor_longitude','Instrument: Longitude is incorrect','number_range',array(-180,180));
		$this->addRule(array('sensor_longitude', 'sensor_longitude','sensor_latitude', 'sensor_latitude'), 'Instrument: Incomplete coordinates', 'completeBoundings');
		$this->addRule('sensor_altitude','Instrument: Height above ground must be numeric','numeric');
		$this->addRule('sensor_model','Instrument: Model exceeds the maximum length allowed (100 characters)','maxlength',100);
		
		$this->addValidationRulesResolution();
		//$this->addRule('sensor_precision','Sensor precision: max 100 characters','maxlength',100);
		$this->addRule('sensor_calibration','Instrument: Calibration exceeds the maximum length allowed (250 characters)','maxlength',250);
			
		$this->addRule('new_manufacturer','Instrument: Manufacturer name exceeds the maximum length allowed (250 characters)','maxlength',250);
		$this->addRule('new_manufacturer_url','Instrument: Manufacturer url exceeds the maximum length allowed (250 characters)','maxlength',250);

		if (isset($this->dataset->dats_sensors[0]->sensor->manufacturer) && !empty($this->dataset->dats_sensors[0]->sensor->manufacturer) && $this->dataset->dats_sensors[0]->sensor->manufacturer->manufacturer_id > 0){
			/*$this->getElement('new_manufacturer')->setAttribute('onfocus','blur()');
			$this->getElement('new_manufacturer_url')->setAttribute('onfocus','blur()');*/
			$this->disableElement('new_manufacturer');
			$this->disableElement('new_manufacturer_url');
		}else {
			//$this->addRule('manufacturer','Instrument a manufacturer with the same name already exists in the database','existe',array('manufacturer','manufacturer_name'));
		}
			
		//Sites
		$siteNames = array();
		for ($i = 0; $i < $this->dataset->nbSites; $i++){
			$this->addRule('platByLev'.$i,'Site '.($i+1).': Site name is required when a platform type is selected','required_if_not_void',array($this,'new_place_'.$i));
			$this->addRule('platByLev'.$i,'Site '.($i+1).': Platform type is required','required_if_not_void2',array($this,'new_place_'.$i));
			
			$this->addRule('west_bound_'.$i,'Site '.($i+1).': Site name and type are required when boundings are set','required_if_not_void3',array($this,'new_place_'.$i));
			
			
			$this->addRule('new_place_'.$i,'Site '.($i+1).': Name exceeds the maximum length allowed (100 characters)','maxlength',100);
			$this->addValidationRulesSiteBoundings($i,'Site '.($i+1));

			
			//$this->addRule('new_place_'.$i,'Location '.($i+1).': This location name is already present in the database. Please choose another name.','existe',array('place','place_name'));
			/*if (isset($this->dataset->sites[$i]) && !empty($this->dataset->sites[$i]) && $this->dataset->sites[$i]->place_id > 0){

				$this->disableElement('new_place_'.$i);
				$this->disableElement('place_alt_min_'.$i);
				$this->disableElement('place_alt_max_'.$i);
				$this->disableElement('gcmd_plat_key_'.$i);
				$this->disableElement('west_bound_'.$i);
				$this->disableElement('east_bound_'.$i);
				$this->disableElement('north_bound_'.$i);
				$this->disableElement('south_bound_'.$i);				
			}else{
				$this->addRule('new_place_'.$i,'Site '.($i+1).': The site name is already present in the database. Select it in the drop-down list or chose another name.','existe',array('place','place_name'));
				if (!empty($this->dataset->sites[$i]->place_name)){
					$siteNames[] = 'new_place_'.$i;
				}
			}*/
			//TODO sensor_envir => instrument présent
			//$this->addRule(array('sensor_environment_'.$i,''))


		}
		if (count($siteNames) > 0){
			$this->addRule($siteNames,'Site names must be distinct.','distinct');
		}
		
		
		//Variables
		$indiceTableVar = 0;
		$indiceTableVarCalc = 0;
		if (isset($this->dataset->dats_variables) && !empty($this->dataset->dats_variables)){
			for ($i = 0; $i < count($this->dataset->dats_variables);$i++){

				$suffix = "";
				$prefixMsg = "";

				if ($this->dataset->dats_variables[$i]->flag_param_calcule == 1){
					$suffix = "calcul".$indiceTableVarCalc++;
					$prefixMsg = "Derived parameter ".$indiceTableVarCalc;
				}else{
					$suffix = $indiceTableVar++;
					$prefixMsg = "Measured parameter ".$indiceTableVar;
				}
				$this->addValidationRulesVariable($i,$suffix,$prefixMsg);
			}
		}

	}

	function initForm(){
		$dataset = & $this->dataset;
			
		$this->initFormBase();

		$this->getElement('dats_date_end_not_planned')->setChecked($this->dataset->dats_date_end_not_planned);

		//PERIOD
		$this->getElement('period')->setSelected($this->dataset->period_id);
		
		
		//SENSOR, on suppose qu'il n'y en un qu'un seul par dataset pour l'instant
		if (isset($dataset->dats_sensors) && !empty($dataset->dats_sensors) && isset($dataset->dats_sensors[0]) && !empty($dataset->dats_sensors[0])){
			if (isset($dataset->dats_sensors[0]->sensor->manufacturer) && !empty($dataset->dats_sensors[0]->sensor->manufacturer)){
				$this->getElement('manufacturer')->setSelected($dataset->dats_sensors[0]->sensor->manufacturer->manufacturer_id);
				$this->getElement('new_manufacturer')->setValue($dataset->dats_sensors[0]->sensor->manufacturer->manufacturer_name);
				$this->getElement('new_manufacturer_url')->setValue($dataset->dats_sensors[0]->sensor->manufacturer->manufacturer_url);
			}

			if (isset($dataset->dats_sensors[0]->sensor->gcmd_instrument_keyword) && !empty($dataset->dats_sensors[0]->sensor->gcmd_instrument_keyword)){
				//$this->getElement('sensor_gcmd')->setSelected($dataset->dats_sensors[0]->sensor->gcmd_instrument_keyword->gcmd_sensor_id);
				$table = array();
				$gcmd = $dataset->dats_sensors[0]->sensor->gcmd_instrument_keyword;
				
				for ($j=4;$j >=1;$j--){
					if ($gcmd->gcmd_level == ($j+1)){
						$table[$j-1] = $gcmd->gcmd_sensor_id;
						$gcmd = $gcmd->gcmd_parent;
					}else
						$table[$j-1] = 0;
				}
				ksort($table);
					
				$this->getElement('sensor_gcmd_')->setValue($table);
			}

			$this->getElement('sensor_id')->setValue($dataset->dats_sensors[0]->sensor->sensor_id);
			$this->getElement('sensor_model')->setValue($dataset->dats_sensors[0]->sensor->sensor_model);
			$this->getElement('sensor_calibration')->setValue($dataset->dats_sensors[0]->sensor->sensor_calibration);
			
			$this->initFormResolution();
				
			$this->getElement('sensor_url')->setValue($dataset->dats_sensors[0]->sensor->sensor_url);

			if (isset($dataset->dats_sensors[0]->nb_sensor) && !empty($dataset->dats_sensors[0]->nb_sensor)){
				$this->getElement('nb_sensor')->setValue($dataset->dats_sensors[0]->nb_sensor);
			}
			if (isset($dataset->dats_sensors[0]->sensor->boundings) && !empty($dataset->dats_sensors[0]->sensor->boundings)){
				//pour les instruments fixes seulement, lt_min = lat_max et lon_min = lon_max
				$this->getElement('sensor_longitude')->setValue($dataset->dats_sensors[0]->sensor->boundings->west_bounding_coord);
				$this->getElement('sensor_latitude')->setValue($dataset->dats_sensors[0]->sensor->boundings->north_bounding_coord);
			}
			if (isset($dataset->dats_sensors[0]->sensor->sensor_elevation))
			$this->getElement('sensor_altitude')->setValue($dataset->dats_sensors[0]->sensor->sensor_elevation);
		}

		//SITES
		if (isset($dataset->sites) && !empty($dataset->sites))
		{
			for ($i = 0; $i < count($dataset->sites);$i++){
				$this->initFormSiteBoundings($i);
				
				if (isset ( $dataset->sites [$i]->gcmd_location_keyword ) && ! empty ( $dataset->sites [$i]->gcmd_location_keyword )) {
					$table = array ();
					$gcmd = $dataset->sites [$i]->gcmd_location_keyword;
				
					for($j = 2; $j >= 0; $j--) {

						if ($gcmd->gcmd_level == ($j+3)) {
							$table [$j] = $gcmd->gcmd_loc_id;
							$gcmd = $gcmd->gcmd_parent;
						} else
							$table [$j ] = 0;
					}
						
					ksort( $table );
					
				$this->getElement( 'locationByLev'.$i )->setValue( $table );
				}
				
				
				/*if (isset($dataset->sites[$i]->boundings) && !empty($dataset->sites[$i]->boundings)){
					$this->getElement('west_bound_'.$i)->setValue($dataset->sites[$i]->boundings->west_bounding_coord);
					$this->getElement('east_bound_'.$i)->setValue($dataset->sites[$i]->boundings->east_bounding_coord);
					$this->getElement('north_bound_'.$i)->setValue($dataset->sites[$i]->boundings->north_bounding_coord);
					$this->getElement('south_bound_'.$i)->setValue($dataset->sites[$i]->boundings->south_bounding_coord);
				}*/
				if (isset($dataset->sites[$i]->gcmd_plateform_keyword) && !empty($dataset->sites[$i]->gcmd_plateform_keyword)){
					//$this->getElement('gcmd_plat_key_'.$i)->setSelected($dataset->sites[$i]->gcmd_plateform_keyword->gcmd_plat_id);
					$table = array();
					$gcmd = $dataset->sites[$i]->gcmd_plateform_keyword;
					
					for($j = 2; $j >= 0; $j --) {
						if ($gcmd->gcmd_level == ($j + 2)) {
							$table [$j] = $gcmd->gcmd_plat_id;
							$gcmd = $gcmd->gcmd_parent;
						}else
							$table [$j ] = 0;
					}
					ksort($table);
					$this->getElement('platByLev'.$i)->setValue($table);
				}

				//$this->getElement('place_'.$i)->setSelected($dataset->sites[$i]->place_id);
				$this->getElement('new_place_'.$i)->setValue($dataset->sites[$i]->place_name);
				//$this->getElement('place_alt_min_'.$i)->setValue($dataset->sites[$i]->place_elevation_min);
				//$this->getElement('place_alt_max_'.$i)->setValue($dataset->sites[$i]->place_elevation_max);
				$this->getElement('sensor_environment_'.$i)->setValue($dataset->sites[$i]->sensor_environment);
			}
		}

		//VARIABLES
		$indiceTableVar = 0;
		$indiceTableVarCalc = 0;
		if (isset($dataset->dats_variables) && !empty($dataset->dats_variables))
		{
			for ($i = 0; $i < count($dataset->dats_variables);$i++){

				$suffix = "";

				if ($dataset->dats_variables[$i]->flag_param_calcule == 1){
					$suffix = "calcul".$indiceTableVarCalc++;
					$this->initFormVariable($i,$suffix);
				}else{
					$suffix = $indiceTableVar++;
					$this->initFormVariable($i,$suffix);
				}
			}

		}
	}

	

	function displayErrorsInstru(){
		$this->displayErrors(array('sensor_gcmd','new_manufacturer','new_manufacturer_url','sensor_model','sensor_calibration',
  			'sensor_resol_temp','sensor_vert_resolution','sensor_lat_resolution','sensor_url','sensor_latitude','sensor_longitude','sensor_altitude'));
	}

	function displayErrorsSite($i){
		$this->displayErrors(array('gcmd_plat_key_'.$i,'place_'.$i,'new_place_'.$i,'west_bound_'.$i,'east_bound_'.$i,'north_bound_'.$i,
  			'south_bound_'.$i,'place_alt_min_'.$i,'place_alt_max_'.$i,'sensor_environment_'.$i));
	}

	

	function displayForm($nb_pi,$nb_site,$nb_variable,$nb_variable_calcul){
		global $project_name;
		$this->addValidationRules();

		$this->initForm();

		echo '<div id="aide"></div>';

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
					echo '<a href="'.$_SERVER['REQUEST_URI'].'#a_use"><font size="3" color="red">'.$error.'</font></a><br>';
				}else{
					echo '<font size="3" color="red">'.$error.'</font><br>';
				}
			}
		}
		echo '<div id="errors" color="red"></div><br>';
		//echo $_SERVER['REQUEST_URI'].'<br>';
        //echo 'strpos:'.strpos($_SERVER['REQUEST_URI'],'&datsId').'<br>'; 
		
		if (strpos($_SERVER['REQUEST_URI'],'&datsId')){
			//$reqUri = strstr($_SERVER['REQUEST_URI'],'&datsId',true);
			$reqUri = substr($_SERVER['REQUEST_URI'],0,strpos($_SERVER['REQUEST_URI'],'&datsId'));
			//echo $reqUri.'<br>';
		}else  if (strpos($_SERVER['REQUEST_URI'],'?datsId')){
			$reqUri = substr($_SERVER['REQUEST_URI'],0,strpos($_SERVER['REQUEST_URI'],'?datsId'));
		}else{
			$reqUri = $_SERVER['REQUEST_URI'];
		}
		
		//echo '<form action="spip.php?rubrique4" method="post" name="frminstr" id="frminstr" enctype="multipart/form-data">';
		echo '<form action="'.$reqUri.'" method="post" name="frminstr" id="frminstr" enctype="multipart/form-data">';

		echo '<SCRIPT LANGUAGE="Javascript" SRC="/js/functions.js"> </SCRIPT>';
		echo '<SCRIPT LANGUAGE="Javascript" SRC="/js/aide.js"> </SCRIPT>';
		
		echo '<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css">';
		echo '<script src="http://code.jquery.com/jquery-1.9.1.js"></script>';
		echo '<script src="http://code.jquery.com/ui/1.10.4/jquery-ui.js"></script>';
		echo '<link rel="stylesheet" href="http/resources/demos/style.css">';
		
		
		
		
		
		echo $this->getElement('dats_id')->toHTML();
		echo $this->getElement('sensor_id')->toHTML();
		
		echo '<table><tr><th class="top" colspan="3" align="left"><font color="#467AA7">Required fields are in blue</font></td><th class="top" align="right">';
                //echo $this->getElement('reset')->toHTML().'</td></tr>';
	
		echo '</td></tr>';
		
	        echo '<tr><td colspan="4" align="center"><a href="'.$reqUri.'?datsId=-10">Reset</a></td></tr>';

	
	        
	    
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
		
		
		echo '<script>
		$(function() {
			$( "#PersonHelp" ).dialog({dialogClass: "alert"});
	    
			$( "#PersonHelp" ).dialog("close");
		});
		</script>';
		 
		echo '<div id="PersonHelp" title="Help">
		<p>
		- A <b>Principal Investigator</b> or <b>Lead Scientist</b> is the scientist responsible for the in-situ instrument or site, or model simulations or forecasts, or satellite product provided by the dataset. He/She will receive an email every time the dataset is downloaded.'
		.'<br/><br/>- A <b>Dataset Contact</b> is a scientist who may be contacted regarding the dataset, but not necessarily responsible for the dataset.'
		.'<br/><br/>- A <b>Database Contact</b> is just responsible for providing the data to '.$project_name.' users.'
		.'<br/><br/>If you are using the form to put a data request, choose User as contact type.
		</p>
		</div>';
		

		echo '</tr><tr>';
		echo '<tr><th colspan="4" align="center"><a name="a_contact" ></a><b>Contact information</b>';
		echo "&nbsp;<input src='/img/aide-icone-16.png' type='image' onmouseover=\"javascript: $('#PersonHelp').dialog('open');\" onmouseout=\"javascript: $( '#PersonHelp' ).dialog('close');\" />";
		echo '</th></tr><tr>';
		for ($i = 0; $i < $this->dataset->nbPis; $i++){
			echo '<tr><td colspan="4" align="center"><b>Contact '.($i+1).'</b><br>';//</td></tr>';
			$this->displayErrorsContact($i);
   			$this->displayPersonForm($i);
		}		
		echo '<tr><td colspan="4" align="center">'.$this->getElement('bouton_add_pi')->toHTML().'</td></tr>';
/*
		echo '<tr><th colspan="4" align="center"><a name="a_descr" ></a><b>Data description</b></td></tr>';
		echo '<tr><td>'.$this->getElement('dats_abstract')->getLabel().'</td><td colspan="3">'.$this->getElement('dats_abstract')->toHTML().'</td></tr>';
		echo '<tr><td>'.$this->getElement('dats_purpose')->getLabel().'</td><td colspan="3">'.$this->getElement('dats_purpose')->toHTML().'</td></tr>';
		echo '<tr><td>'.$this->getElement('dats_reference')->getLabel().'</td><td colspan="3">'.$this->getElement('dats_reference')->toHTML().'</td></tr>';
*/
		//echo '<tr><th colspan="4" align="center"><a name="a_descr" ></a><b>Data description</b></td></tr>';
		$this->displayDataDescrForm(true);
		
		//Document attaché
		echo '<tr><td>'.$this->getElement('upload_doc')->getLabel().'</td><td colspan="3">';
		if (isset($this->dataset->attFile) && !empty($this->dataset->attFile)){
			echo "<a href='/downAttFile.php?file=".$this->dataset->attFile."' >".$this->dataset->attFile."</a>";
			echo $this->getElement('delete_doc_button')->toHTML();
		}else{
			echo $this->getElement('upload_doc')->toHTML();
			echo $this->getElement('upload_doc_button')->toHTML();
		}
		echo '</td></tr>';
		
		
		
		echo '<tr><th colspan="4" align="center"><a name="a_instru" ></a><b>Instrument information</b>'.$this->getHideShow('row_sensor');
		echo "</th></tr>";
		$this->displayErrorsInstru();
		//echo '<tr name="row_sensor"><td>'.$this->getElement('sensor_gcmd')->getLabel().'</td><td colspan="3">'.$this->getElement('sensor_gcmd')->toHTML().'</td></tr>';

		echo '<tr name="row_sensor"><td>'.$this->getElement('sensor_gcmd_')->getLabel().'</td><td colspan="3">'.$this->getElement('sensor_gcmd_')->toHTML().'</td></tr>';
		echo '<tr name="row_sensor"><td>'.$this->getElement('sampler')->getLabel().'</td><td colspan="3">'.$this->getElement('sampler')->toHTML().'</td></tr>';
		echo '<tr name="row_sensor"><td>'.$this->getElement('manufacturer')->getLabel().'</td><td colspan="3">'.$this->getElement('manufacturer')->toHTML();
		echo '&nbsp;&nbsp;or add '.$this->getElement('new_manufacturer')->getLabel().''.$this->getElement('new_manufacturer')->toHTML().'</td></tr>';
		echo '<tr name="row_sensor"><td>'.$this->getElement('new_manufacturer_url')->getLabel().'</td><td colspan="3">'.$this->getElement('new_manufacturer_url')->toHTML().'</td></tr>';
		echo '<tr name="row_sensor"><td>'.$this->getElement('sensor_model')->getLabel().'</td><td>'.$this->getElement('sensor_model')->toHTML().'</td>';
		echo '<td>'.$this->getElement('sensor_calibration')->getLabel().'</td><td>'.$this->getElement('sensor_calibration')->toHTML().'</td></tr>';
		/*echo '<tr><td>'.$this->getElement('sensor_precision')->getLabel().'</td><td>'.$this->getElement('sensor_precision')->toHTML().'</td>';*/
		echo '<tr name="row_sensor"><td>'.$this->getElement('sensor_resol_temp')->getLabel().'</td><td>'.$this->getElement('sensor_resol_temp')->toHTML().'</td><td colspan="2"></td></tr>';

		echo '<tr name="row_sensor"><td>'.$this->getElement('sensor_lat_resolution')->getLabel().'</td><td>'.$this->getElement('sensor_lat_resolution')->toHTML().'</td>';
		echo '<td>'.$this->getElement('sensor_vert_resolution')->getLabel().'</td><td>'.$this->getElement('sensor_vert_resolution')->toHTML().'</td></tr>';
		echo '<tr name="row_sensor"><td>'.$this->getElement('sensor_longitude')->getLabel().'</td><td>'.$this->getElement('sensor_longitude')->toHTML().'</td>';
		echo '<td>'.$this->getElement('sensor_latitude')->getLabel().'</td><td>'.$this->getElement('sensor_latitude')->toHTML().'</td></tr>';
		echo '<tr name="row_sensor"><td>'.$this->getElement('sensor_altitude')->getLabel().'</td><td>'.$this->getElement('sensor_altitude')->toHTML().'</td>';
		echo '<td>'.$this->getElement('sensor_url')->getLabel().'</td><td>'.$this->getElement('sensor_url')->toHTML().'</td></tr>';

		echo '<tr name="row_sensor"><td>'.$this->getElement('upload_image')->getLabel().'</td>';
		if (isset($this->dataset->image) && !empty($this->dataset->image)){
			echo '<td><a href="'.$this->dataset->image.'" target=_blank><img src="'.$this->dataset->image.'" width="50" /></a>';
			echo $this->getElement('delete')->toHTML().'</td><td colspan="2" />';
		}else{
			echo '<td/><td colspan=2><input type="hidden" name="MAX_FILE_SIZE" value="2000000" />'.$this->getElement('upload_image')->toHTML();
			echo $this->getElement('upload')->toHTML().'</td>';
		}
		echo '</tr>';

		
	
		
		
		echo '<tr><th colspan="4" align="center"><a name="a_site" ></a><b>Geographic information</b>';
		echo '</td></tr>';
		for ($i = 0; $i < $nb_site; $i++)
		{
			echo '<tr><td colspan="4" align="center"><b>Location '.($i+1).'</b>'.$this->getHideShow('row_site_'.$i).'</td></tr>';
			$this->displayErrorsSite($i);
			//1 echo '<tr name="row_site_'.$i.'"><td colspan="2">'.$this->getElement('place_'.$i)->getLabel().'&nbsp;&nbsp;'.$this->getElement('place_'.$i)->toHTML();
			//2 echo '<tr name="row_site_'.$i.'"><td>'.$this->getElement('place_'.$i)->getLabel().'</td><td colspan="3">'.$this->getElement('place_'.$i)->toHTML();
			
			//echo '<tr name="row_site_'.$i.'"><td>'.$this->getElement('placeByLev_'.$i)->getLabel().'</td><td colspan="3">'.$this->getElement('placeByLev_'.$i)->toHTML().'</td></tr>';
			echo '<tr name="row_site_'.$i.'"><td>'.$this->getElement('locationByLev'.$i)->getLabel().'</td><td colspan="3">'.$this->getElement('locationByLev'.$i)->toHTML().'</td></tr>';
			echo '<tr name="row_site_'.$i.'"><td>'.$this->getElement('platByLev'.$i)->getLabel().'</td><td colspan="3">'.$this->getElement('platByLev'.$i)->toHTML().'</td></tr>';
			echo '<tr name="row_site_'.$i.'"><td>'.$this->getElement('new_place_'.$i)->getLabel().'</td><td colspan="3">'./*$this->getElement('place_'.$i)->toHTML();
			echo '&nbsp;&nbsp;or add '.$this->getElement('new_place_'.$i)->getLabel().''.*/$this->getElement('new_place_'.$i)->toHTML().'</td>';

			//1 echo '<td name="row_site_'.$i.'">'.$this->getElement('gcmd_plat_key_'.$i)->getLabel().'</td><td>'.$this->getElement('gcmd_plat_key_'.$i)->toHTML().'</td></tr>';
			//2 echo '</tr><tr><td name="row_site_'.$i.'">'.$this->getElement('gcmd_plat_key_'.$i)->getLabel().'</td><td colspan="3">'.$this->getElement('gcmd_plat_key_'.$i)->toHTML().'</td></tr>';
			//echo '<td name="row_site_'.$i.'">'.$this->getElement('gcmd_plat_key_'.$i)->getLabel().'</td><td>'.$this->getElement('gcmd_plat_key_'.$i)->toHTML().'</td></tr>';

			
			
			$this->displaySiteBoundingsForm($i);
			/*echo '<tr><td>'.$this->getElement('west_bound_'.$i)->getLabel().'</td><td>'.$this->getElement('west_bound_'.$i)->toHTML().'</td>';
			echo '<td>'.$this->getElement('east_bound_'.$i)->getLabel().'</td><td>'.$this->getElement('east_bound_'.$i)->toHTML().'</td></tr>';
			echo '<tr><td>'.$this->getElement('north_bound_'.$i)->getLabel().'</td><td>'.$this->getElement('north_bound_'.$i)->toHTML().'</td>';
			echo '<td>'.$this->getElement('south_bound_'.$i)->getLabel().'</td><td>'.$this->getElement('south_bound_'.$i)->toHTML().'</td></tr>';
			echo '<tr><td>'.$this->getElement('place_alt_min_'.$i)->getLabel().'</td><td>'.$this->getElement('place_alt_min_'.$i)->toHTML().'</td>';
			echo '<td>'.$this->getElement('place_alt_max_'.$i)->getLabel().'</td><td>'.$this->getElement('place_alt_max_'.$i)->toHTML().'</td></tr>';*/
			echo '<tr name="row_site_'.$i.'"><td>'.$this->getElement('sensor_environment_'.$i)->getLabel().'</td><td colspan="3">'.$this->getElement('sensor_environment_'.$i)->toHTML().'</td></tr>';
		}
		echo '<tr><td colspan="4" align="center">'.$this->getElement('bouton_add_site')->toHTML().'</td></tr>';
		echo '<tr><th colspan="4" align="center"><a name="a_param" ></a><b>Measured parameters</b></td></tr>';
		
		for ($i = 0; $i < $this->dataset->nbVars; $i++){
			echo '<tr><td colspan="4" align="center"><b>Measured parameter '.($i+1).'</b>'.$this->getElement('var_id_'.$i)->toHTML().'</td></tr>';
			$this->displayErrorsParams($i);
			$this->displayParamForm($i,true,true);		
		}
		echo '<tr><td colspan="4" align="center">'.$this->getElement('bouton_add_variable')->toHTML().'</td></tr>';
		echo '<tr><th colspan="4" align="center"><a name="a_param_calcul" ></a><b>Derived parameters (if relevant)</b></td></tr>';
		for ($i = 0; $i < $this->dataset->nbCalcVars; $i++){
			echo '<tr><td colspan="4" align="center"><b>Derived parameter '.($i+1).'</b>'.$this->getElement('var_id_calcul'.$i)->toHTML().'</td></tr>';
			$this->displayErrorsParams('calcul'.$i);
			$this->displayParamForm('calcul'.$i,true,true);		
		}
		echo '<tr><td colspan="4" align="center">'.$this->getElement('bouton_add_variable_calcul')->toHTML().'</td></tr>';
		
		
		/*for ($i = 0; $i < $nb_variable; $i++)
		{
			echo '<tr><td colspan="4" align="center"><b>Measured parameter '.($i+1).'</b>'.$this->getElement('var_id_'.$i)->toHTML().'</td></tr>';
			$this->displayErrorsParams($i);
			echo '<tr><td>'.$this->getElement('gcmd_science_key_'.$i)->getLabel().'</td><td colspan="3">'.$this->getElement('gcmd_science_key_'.$i)->toHTML().'</td></tr>';
		
			echo '<tr><td colspan="2">'.$this->getElement('new_variable_'.$i)->getLabel().'</td><td colspan="2">'.$this->getElement('new_variable_'.$i)->toHTML().'</td></tr>';
			echo '<tr><td>'.$this->getElement('unit_'.$i)->getLabel().'</td><td colspan="3">'.$this->getElement('unit_'.$i)->toHTML();
			echo '&nbsp;&nbsp;or add '.$this->getElement('new_unit_'.$i)->getLabel().''.$this->getElement('new_unit_'.$i)->toHTML();
			echo $this->getElement('new_unit_code_'.$i)->getLabel().''.$this->getElement('new_unit_code_'.$i)->toHTML().'</td></tr>';
			echo '<tr><td>'.$this->getElement('methode_acq_'.$i)->getLabel().'</td><td colspan="3">'.$this->getElement('methode_acq_'.$i)->toHTML().'</td></tr>';
			echo '<tr><td>'.$this->getElement('var_date_min_'.$i)->getLabel().'</td><td>'.$this->getElement('var_date_min_'.$i)->toHTML().'</td>';
			echo '<td>'.$this->getElement('var_date_max_'.$i)->getLabel().'</td><td>'.$this->getElement('var_date_max_'.$i)->toHTML().'</td></tr>';
			echo '<tr><td>'.$this->getElement('sensor_precision_'.$i)->getLabel().'</td><td>'.$this->getElement('sensor_precision_'.$i)->toHTML().'</td><td colspan="2"></td></tr>';
		}
		echo '<tr><td colspan="4" align="center">'.$this->getElement('bouton_add_variable')->toHTML().'</td></tr>';
		echo '<tr><th colspan="4" align="center"><a name="a_param_calcul" ></a><b>Derived parameters (if relevant)</b></td></tr>';
		for ($i = 0; $i < $nb_variable_calcul; $i++)
		{
			$this->displayErrorsParams('calcul'.$i);
			echo '<tr><td colspan="4" align="center"><b>Derived parameter '.($i+1).'</b>'.$this->getElement('var_id_calcul'.$i)->toHTML().'</td></tr>';
			echo '<tr><td>'.$this->getElement('gcmd_science_key_calcul'.$i)->getLabel().'</td><td colspan="3">'.$this->getElement('gcmd_science_key_calcul'.$i)->toHTML().'</td></tr>';
			echo '<tr><td colspan="2">'.$this->getElement('new_variable_calcul'.$i)->getLabel().'</td><td colspan="2">'.$this->getElement('new_variable_calcul'.$i)->toHTML().'</td></tr>';
			echo '<tr><td>'.$this->getElement('unit_calcul'.$i)->getLabel().'</td><td colspan="3">'.$this->getElement('unit_calcul'.$i)->toHTML();
			echo '&nbsp;&nbsp;or add '.$this->getElement('new_unit_calcul'.$i)->getLabel().''.$this->getElement('new_unit_calcul'.$i)->toHTML();
			echo $this->getElement('new_unit_code_calcul'.$i)->getLabel().''.$this->getElement('new_unit_code_calcul'.$i)->toHTML().'</td></tr>';
			echo '<tr><td>'.$this->getElement('methode_acq_calcul'.$i)->getLabel().'</td><td colspan="3">'.$this->getElement('methode_acq_calcul'.$i)->toHTML().'</td></tr>';
			echo '<tr><td>'.$this->getElement('var_date_min_calcul'.$i)->getLabel().'</td><td>'.$this->getElement('var_date_min_calcul'.$i)->toHTML().'</td>';
			echo '<td>'.$this->getElement('var_date_max_calcul'.$i)->getLabel().'</td><td>'.$this->getElement('var_date_max_calcul'.$i)->toHTML().'</td></tr>';
			echo '<tr><td>'.$this->getElement('sensor_precision_calcul'.$i)->getLabel().'</td><td>'.$this->getElement('sensor_precision_calcul'.$i)->toHTML().'</td><td colspan="2"></td></tr>';
		}
		echo '<tr><td colspan="4" align="center">'.$this->getElement('bouton_add_variable_calcul')->toHTML().'</td></tr>';*/
		
		echo '<script>
		$(function() {
			$( "#UseHelp" ).dialog({dialogClass: "alert"});
		
			$( "#UseHelp" ).dialog("close");
		
		});
		</script>';
			
		echo '<div id="UseHelp" title="Help">
		<p>
		- <b>Use constraint</b> encourages people to mention you, when using your data.<br/><br/><u>For example</u>: <i>"Permission is granted to use these data and images in research and publications when accompanied by the following statement: ... . </i>at you to complete the following"
		</p>
		</div>';
		
		echo '<tr><th colspan="4" align="center"><a name="a_use" ></a><b>Data use information</b>';
		echo '</td></tr>';
		$this->displayErrorsUseInfo();
		echo '<tr><td>'.$this->getElement('dats_use_constraints')->getLabel().'</td><td colspan="3">'.$this->getElement('dats_use_constraints')->toHTML()."&nbsp;<input src='/img/aide-icone-16.png' type='image' onmouseover=\"javascript: $('#UseHelp').dialog('open');\" onmouseout=\"javascript: $( '#UseHelp' ).dialog('close');\" /></td></tr>";
		//echo '<td>'.$this->getElement('dats_access_constraints')->getLabel().'</td><td>'.$this->getElement('dats_access_constraints')->toHTML().'</td></tr>';

		//echo '<td>'.$this->getElement('dats_quality')->getLabel().'</td><td>'.$this->getElement('dats_quality')->toHTML().'</td></tr>';
		/*echo '<tr><td>'.$this->getElement('data_center')->getLabel().'</td><td>'.$this->getElement('data_center')->toHTML().'</td>';
		echo '<td>'.$this->getElement('url')->getLabel().'</td><td>'.$this->getElement('url')->toHTML().'</td></tr>';*/
		echo '<tr><td>'.$this->getElement('data_policy')->getLabel().'</td><td colspan="3">'.$this->getElement('data_policy')->toHTML();
		echo '&nbsp;&nbsp;or add ' .$this->getElement('new_data_policy')->getLabel().'&nbsp;'.$this->getElement('new_data_policy')->toHTML().'</td></tr>';
		echo '<tr><td>'.$this->getElement('database')->getLabel().'</td><td colspan="3">'.$this->getElement('database')->toHTML();
		echo '&nbsp;&nbsp;or add '.$this->getElement('new_database')->getLabel().'&nbsp;'.$this->getElement('new_database')->toHTML().'</td></tr>';
		echo '<td>'.$this->getElement('new_db_url')->getLabel().'</td><td>'.$this->getElement('new_db_url')->toHTML().'</td><td colspan="2"></td></tr>';
		/*echo '<tr><td>'.$this->getElement('data_format')->getLabel().'</td><td>'.$this->getElement('data_format')->toHTML();
		 echo '<td>or add '.$this->getElement('other_data_format')->getLabel().'</td><td name="td_data_formats">'.$this->getElement('other_data_format')->toHTML().'</td></tr>';*/

		for ($i = 0; $i < $this->dataset->nbFormats; $i++){
			echo '<tr>';
			if ($i == 0){
				echo '<td rowspan="'.($this->dataset->nbFormats+1).'"><a name="a_data_format" ></a>Data format'.(($this->dataset->nbFormats > 1)?'s':'').'</td>';
			}
			echo '<td colspan="3">'.$this->getElement('data_format_'.$i)->toHTML();
			echo '&nbsp;&nbsp;or add '.$this->getElement('new_data_format_'.$i)->getLabel().''.$this->getElement('new_data_format_'.$i)->toHTML().'</td></tr>';
		}
		echo '<tr><td colspan="3" align="center">'.$this->getElement('bouton_add_format')->toHTML().'</td></tr>';

		/*echo '<tr><td>'.$this->getElement('data_format')->getLabel().'</td><td>'.$this->getElement('data_format')->toHTML();
		 echo '<td>or add '.$this->getElement('other_data_format')->getLabel().'</td><td name="td_data_formats">'.$this->getElement('other_data_format')->toHTML().'</td></tr>';*/
		echo '<tr>';
		echo '<th colspan="4" align="center">'.$this->getElement('bouton_save')->toHTML().'</td></th></table>';
		echo '</form>';
	}
	

	function saveForm($nb_pi,$nb_site,$nb_variable,$nb_variable_calcul){
		$dataset = & $this->dataset;

		$this->saveFormBase();
		
		$dataset->dats_date_end_not_planned = $this->getElement('dats_date_end_not_planned')->getChecked();
		
		// SENSOR
		// 1 seul par dataset pour l'instant
		$dataset->dats_sensors = array();
		$dataset->dats_sensors[0] = new dats_sensor();
		$dataset->dats_sensors[0]->sensor = new sensor;

		$sensId = $this->exportValue('sensor_id');
		if ( isset($sensId) && ( strlen($sensId) > 0 ) ){
			$dataset->dats_sensors[0]->sensor->sensor_id = $sensId;
		}else{
			$dataset->dats_sensors[0]->sensor->sensor_id = 0;
		}
		$tab_gcmd_id = $this->exportValue("sensor_gcmd_");
		
		for($i=0;$i<count($tab_gcmd_id);$i++){
			$gcmd_id = $tab_gcmd_id[$i];
			if(isset($gcmd_id) && !empty($gcmd_id)){
				$dataset->dats_sensors[0]->sensor->gcmd_sensor_id = $gcmd_id;
			}
		}


		if ($dataset->dats_sensors[0]->sensor->gcmd_sensor_id != 0)
		{
			$dataset->dats_sensors[0]->sensor->gcmd_instrument_keyword = new gcmd_instrument_keyword;
			$dataset->dats_sensors[0]->sensor->gcmd_instrument_keyword = $dataset->dats_sensors[0]->sensor->gcmd_instrument_keyword->getById($dataset->dats_sensors[0]->sensor->gcmd_sensor_id);
		}

		$dataset->dats_sensors[0]->sensor->manufacturer = new manufacturer;
		$dataset->dats_sensors[0]->sensor->manufacturer->manufacturer_id = $this->exportValue('manufacturer');

		$dataset->dats_sensors[0]->sensor->manufacturer->manufacturer_name = $this->exportValue('new_manufacturer');
		$dataset->dats_sensors[0]->sensor->manufacturer->manufacturer_url = $this->exportValue('new_manufacturer_url');

		if (empty($dataset->dats_sensors[0]->sensor->manufacturer->manufacturer_name)){
			$dataset->dats_sensors[0]->sensor->manufacturer->manufacturer_id = -1;
		}

		$dataset->dats_sensors[0]->sensor->manufacturer_id = & $dataset->dats_sensors[0]->sensor->manufacturer->manufacturer_id;

		$dataset->dats_sensors[0]->sensor->sensor_url = $this->exportValue('sensor_url');
		$dataset->dats_sensors[0]->sensor->sensor_model = $this->exportValue('sensor_model');
		$dataset->dats_sensors[0]->sensor->sensor_calibration = $this->exportValue('sensor_calibration');
		
		$this->saveFormResolution();
		
		//$dataset->dats_sensors[0]->sensor->sensor_precision = $this->exportValue('sensor_precision');
		$dataset->dats_sensors[0]->nb_sensor = $this->exportValue('nb_sensor');
		$dataset->dats_sensors[0]->sensor->sensor_elevation = $this->exportValue('sensor_altitude');
		$lat = $this->exportValue('sensor_latitude');
		$lon = $this->exportValue('sensor_longitude');
		$dataset->dats_sensors[0]->sensor->boundings = new boundings;
		if ( isset($lon) && strlen($lon) > 0 ){
			$dataset->dats_sensors[0]->sensor->boundings->west_bounding_coord = $lon;
			$dataset->dats_sensors[0]->sensor->boundings->east_bounding_coord = $lon;
		}else
		$dataset->dats_sensors[0]->sensor->bound_id = -1;
		if ( isset($lat) && strlen($lat) > 0 ){
			$dataset->dats_sensors[0]->sensor->boundings->north_bounding_coord = $lat;
			$dataset->dats_sensors[0]->sensor->boundings->south_bounding_coord = $lat;
		}else
		$dataset->dats_sensors[0]->sensor->bound_id = -1;


		//SITES
		$dataset->sites = array();
		//$dataset->predSites = array();
		for ($i = 0; $i < $nb_site; $i++){
			$dataset->sites[$i] = new place;
				
			
			$sitesLev = $this->exportValue('locationByLev'.$i);
			$pred_site_id = 0;
			for ($j = 3;$j >= 0;$j--){
				if (isset($sitesLev[$j]) && $sitesLev[$j] > 0){
					$pred_site_id = $sitesLev[$j];
					break;
				}
			}
			$dataset->sites[$i]->gcmd_loc_id = $pred_site_id;
			if ($dataset->sites[$i]->gcmd_loc_id != 0)	{
				$dataset->sites[$i]->gcmd_location_keyword = new gcmd_location_keyword;
				$dataset->sites[$i]->gcmd_location_keyword = $dataset->sites[$i]->gcmd_location_keyword->getById($dataset->sites[$i]->gcmd_loc_id);
			}
			
			
			$dataset->sites[$i]->place_name = $this->exportValue('new_place_'.$i);
			if (empty($dataset->sites[$i]->place_name)){
				$dataset->sites[$i]->place_id = -1;
			}else if(empty($dataset->sites[$i]->place_id)) {
                	$dataset->sites[$i]->place_id = 0;
                }
				
			//$dataset->sites[$i]->gcmd_plat_id = $this->exportValue('gcmd_plat_key_'.$i);
			
			
			$sitesPlat = $this->exportValue('platByLev'.$i);
			$pred_plat_id = 0;
			for ($j = 3;$j >= 0;$j--){
				if (isset($sitesPlat[$j]) && $sitesPlat[$j] > 0){
					$pred_plat_id = $sitesPlat[$j];
					break;
				}
			}
			$dataset->sites[$i]->gcmd_plat_id = $pred_plat_id;
			if ($dataset->sites[$i]->gcmd_plat_id != 0)	{
				$dataset->sites[$i]->gcmd_plateform_keyword = new gcmd_plateform_keyword;
				$dataset->sites[$i]->gcmd_plateform_keyword = $dataset->sites[$i]->gcmd_plateform_keyword->getById($dataset->sites[$i]->gcmd_plat_id);
			}
			$this->saveFormSiteBoundings($i);
			
			//sensor environment
			$sensor_environment = $this->exportValue('sensor_environment_'.$i);
			//echo '$sensor_environment:'.$sensor_environment.'<br>';
			if (isset($sensor_environment) && !empty($sensor_environment)){
				$dataset->sites[$i]->sensor_environment = $sensor_environment;
			}
		}

		//PERIOD
		$dataset->period = new period;
		$dataset->period->period_id = $this->exportValue('period');

		if (isset($dataset->period->period_id) && $dataset->period->period_id != 0){
			$dataset->period = $dataset->period->getById($dataset->period->period_id);
		}
		$dataset->period_id = & $dataset->period->period_id;

		
		$this->saveFormVariables($nb_variable);
		$this->saveFormVariables($nb_variable_calcul,1,'calcul',$nb_variable);

		$nb_vars = count($dataset->dats_variables);

	}
	
	function addFormat(){
		$this->createFormDataFormat($this->dataset->nbFormats-1);
	}

	function addProjet(){
		$this->createFormProject($this->dataset->nbProj-1);
	}

	function addSite($nb_site){
		$this->createFormSite($nb_site-1);
	}

	

	function addVariableCalcul($nb_variable){
		$this->createFormVariable($nb_variable-1,'calcul');
	}

}
?>
