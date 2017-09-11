<?php

require_once ("forms/base_form.php");

class mod_va_form extends base_form{

	function createForm(){
		$this->createFormBase();
		$this->addElement('reset','reset','Reset');			
		$tab['seeker_0'] = '0';
		$this->setDefaults($tab);
		
		$this->addElement('submit','bouton_add_pi','Add a contact',array('onclick' => "document.getElementById('frmmodva').action += '#a_contact'"));
		
		$place = new place;
		$mod_select = $place->chargeFormMod($this,'model','Model name');
		$this->addElement($mod_select);
		$this->addElement('text','new_model','Model name');
		$this->applyFilter('new_model','trim');

		$modType = new gcmd_plateform_keyword;
		$modType_select = $modType->chargeFormMod($this,'model_type','Model type');
		$this->addElement($modType_select);
		
		$array = array();
		$array[0] = "";
		if ( isset($this->dataset->sites[1]) && !empty($this->dataset->sites[1]) ){
			if ($this->dataset->sites[1]->place_id > 0){
				$sensor = new sensor;
				$listeInstrus = $sensor->getByPlace($this->dataset->sites[1]->place_id);
				foreach ($listeInstrus as $instru){
					$array[$instru->sensor_id] = $instru->sensor_model;
				}
			}
		}
			
		$boxesNames = "['new_simu']";
		$columnsNames = "['sensor_model']";
		 
		$this->addElement('select',"simu","Simulation",$array,array('onchange' => "fillBoxes('simu',".$boxesNames.",'sensor',".$columnsNames.");"));
		$this->addElement('text','new_simu','Simulation name', array('onchange' => "updateDatasetTitle('new_simu');"));
		$this->applyFilter('new_simu','trim');

		$this->createFormResolution();
		$this->createFormGeoCoverage();
		$this->createFormGrid();
		
		//Required format
		$dformat = new data_format;
		$dformat_select = $dformat->chargeFormDestFormat($this,'required_data_format','Required data format','NetCDF');
		$this->addElement($dformat_select);
		
		$this->getElement('organism_0')->setLabel("Organism short name");
		$this->getElement('project_0')->setLabel("Useful in the framework of");
			
		$this->getElement('dats_abstract')->setLabel("Dataset description ");
		$this->getElement('dats_purpose')->setLabel("Purpose");
		$this->getElement('database')->setLabel("Data center");
		$this->getElement('new_db_url')->setLabel("Data center url");
		$this->getElement('dats_use_constraints')->setLabel("Access and use constraints");
				
		$this->getElement('sensor_resol_temp')->setLabel('Temporal (yyyy-mm-dd hh:mm:ss)');
				
		$this->getElement('place_alt_min_0')->setLabel("Altitude min");
		$this->getElement('place_alt_max_0')->setLabel("Altitude max");
		
		$this->getElement('data_format_0')->setLabel("Original data format");
	
		$this->addElement('file','upload_doc','Attached document');
                $this->addElement('submit','upload','Upload');
                $this->addElement('submit','delete','Delete');
	
		for ($i = 0; $i < $this->dataset->nbVars; $i++){
			$this->getElement('methode_acq_'.$i)->setLabel("Parameter processing related information");
		}
		$this->addElement('submit','bouton_add_variable','Add a parameter',array('onclick' => "document.getElementById('frmmodva').action += '#a_param'"));

		$this->addElement('submit','bouton_add_projet','Add a project',array('onclick' => "document.getElementById('frmmodva').action += '#a_general'"));
	}

	function addProjet(){
             $this->createFormProject($this->dataset->nbProj-1);
    }

	function addVariableMod($nb_variable){
		$this->addVariable($nb_variable);
		$this->getElement('methode_acq_'.($nb_variable-1))->setLabel("Parameter processing related information");
	}
	
	function initForm(){
		$this->initFormBase();
			
		$this->initFormResolution();
		$this->initFormGeoCoverage();

		if (isset($this->dataset->sites) && !empty($this->dataset->sites)){
			//Modele
			if ( isset($this->dataset->sites[1]) && !empty($this->dataset->sites[1]) ){
				$this->getElement('model')->setSelected($this->dataset->sites[1]->place_id);
				$this->getElement('new_model')->setValue($this->dataset->sites[1]->place_name);
				$this->getElement('model_type')->setSelected($this->dataset->sites[1]->gcmd_plat_id);
			}

		}
		
		if (isset($this->dataset->dats_sensors[0]->sensor->gcmd_instrument_keyword) && !empty($this->dataset->dats_sensors[0]->sensor->gcmd_instrument_keyword)){
			$this->getElement('sensor_gcmd')->setSelected($this->dataset->dats_sensors[0]->sensor->gcmd_instrument_keyword->gcmd_sensor_id);
		}
			
		//Instrument			
		$this->getElement('simu')->setSelected($this->dataset->dats_sensors[0]->sensor->sensor_id);
		$this->getElement('new_simu')->setValue($this->dataset->dats_sensors[0]->sensor->sensor_model);

		$this->initFormGrid();
				
		//Parameters
		for ($i = 0; $i < $this->dataset->nbVars; $i++){
			$this->initFormVariable($i,$i);
		}
		
		//REQ DATA FORMATS
			if (isset($this->dataset->required_data_formats[0]) && !empty($this->dataset->required_data_formats[0])){
				$this->getElement('required_data_format')->setSelected($this->dataset->required_data_formats[0]->data_format_id);
			}else
				$this->getElement('required_data_format')->setSelected(0);
	}

	function saveForm(){
			
		$this->saveFormBase();
		$this->saveFormGeoCoverage();

		//Mod
		$this->dataset->sites[1] = new place;
		$this->dataset->sites[1]->place_id = $this->exportValue('model');
		$this->dataset->sites[1]->place_name = $this->exportValue('new_model');
		$this->dataset->sites[1]->bound_id = -1;

		$this->dataset->sites[1]->gcmd_plat_id = $this->exportValue('model_type');
		if ($this->dataset->sites[1]->gcmd_plat_id != 0) {
			$this->dataset->sites[1]->gcmd_plateform_keyword = new gcmd_plateform_keyword;
				$this->dataset->sites[1]->gcmd_plateform_keyword = $this->dataset->sites[1]->gcmd_plateform_keyword->getById($this->dataset->sites[1]->gcmd_plat_id);
		}		
	
		//Simu
		$this->dataset->dats_sensors = array();
		$this->dataset->dats_sensors[0] = new dats_sensor();
		$this->dataset->dats_sensors[0]->sensor = new sensor;
		$this->dataset->dats_sensors[0]->sensor->sensor_id = $this->exportValue('simu');
		$this->dataset->dats_sensors[0]->sensor->sensor_model = $this->exportValue('new_simu');
		$this->dataset->dats_sensors[0]->sensor->gcmd_sensor_id = -1;

		$this->dataset->dats_sensors[0]->sensor->manufacturer_id = -1;
		$this->dataset->dats_sensors[0]->sensor->bound_id = -1;

		$this->saveFormResolution();
		$this->saveFormGrid();
		//Parameter
		$this->saveFormVariables($this->dataset->nbVars);
		
		//REQ DATA_FORMAT
			$this->dataset->required_data_formats = array();
			$this->dataset->required_data_formats[0] = new data_format;
			$this->dataset->required_data_formats[0]->data_format_id = $this->exportValue('required_data_format');
	}


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
		}else {
		}
		$this->addRule('new_data_policy','Data use information: Data policy exceeds the maximum length allowed (100 characters)','maxlength',100);
			
		$attrs = array();
		if (isset($this->dataset->database) && !empty($this->dataset->database) && $this->dataset->database->database_id > 0){
			$this->disableElement('new_database');
			$this->disableElement('new_db_url');
		}else {
		}
		$this->addRule('new_database','Data use information: Database name exceeds the maximum length allowed (250 characters)','maxlength',250);
		$this->addRule('new_db_url','Data use information: Database url exceeds the maximum length allowed (250 characters)','maxlength',250);
			
		//Formats
		for ($i = 0; $i < $this->dataset->nbFormats; $i++){
			$this->addRule('data_format_'.$i,'Data use information: Format name '.($i+1).' exceeds the maximum length allowed (100 characters)','maxlength',100);
			if (isset($this->dataset->data_formats[$i]) && !empty($this->dataset->data_formats[$i]) && $this->dataset->data_formats[$i]->data_format_id > 0){
				$this->disableElement('new_data_format_'.$i);
			}else{
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
			}else{
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
		
		$this->addRule('model','Model: model name is required','couple_not_null',array($this,'new_model'));
		$this->addRule('simu','Model: simulation name is required','couple_not_null',array($this,'new_simu'));

		
		$this->addRule('new_model','Model: Name exceeds the maximum length allowed (100 characters)','maxlength',100);
		$this->addRule('new_simu','Model: Name exceeds the maximum length allowed (100 characters)','maxlength',100);

		if (isset($this->dataset->sites[1]) && !empty($this->dataset->sites[1]) && $this->dataset->sites[1]->place_id > 0){
			$this->disableElement('new_model');
			$this->disableElement('model_type');
		}else{
			$this->addRule('new_model','Model: The model name is already present in the database. Select it in the drop-down list or chose another name.','existe',array('place','place_name'));
		}
		
		if (isset($this->dataset->dats_sensors[0]->sensor) && !empty($this->dataset->dats_sensors[0]->sensor) && $this->dataset->dats_sensors[0]->sensor->sensor_id > 0){
			$this->disableElement('new_simu');
		}
			
		$this->addValidationRulesResolution('Coverage');		
		$this->addRule('sensor_resol_temp','Coverage: temporal resolution is incorrect','regex',"/^[0-9]{4}[-][0-9]{2}[-][0-9]{2} [0-9]{2}[:][0-9]{2}[:][0-9]{2}$/");
		$this->addValidationRulesGeoCoverage();

		//PARAMETER
		for ($i = 0; $i < $this->dataset->nbVars; $i++){
			$this->addValidationRulesVariable($i,$i,'Parameter '.($i+1));
		}
	}


	function displayErrorsCoverage(){
		$this->displayErrors(array('dats_date_begin','dats_date_end','area','sensor_resol_temp',
					'west_bound_0','east_bound_0','north_bound_0','south_bound_0','place_alt_min_0','place_alt_max_0'));
	}
	
	function displayErrorsModel(){
		$this->displayErrors(array('model','new_model','simu','new_simu'));
	}
	
	function displayErrorsModDataDescr(){
			$this->displayErrors(array('dats_title'));
		}

	function displayForm(){
		$this->addValidationRules();
		$this->initForm();

		// Affichage des erreurs
		if ( !empty($this->_errors) ){
			foreach ($this->_errors as $error) {
				if (strpos($error,'Data descr') === 0){
					echo '<a href="'.$_SERVER['REQUEST_URI'].'#a_general"><font size="3" color="red">'.$error.'</font></a><br>';
				}else if (strpos($error,'Contact') === 0){
					echo '<a href="'.$_SERVER['REQUEST_URI'].'#a_contact"><font size="3" color="red">'.$error.'</font></a><br>';
				}else if (strpos($error,'Model') === 0){
					echo '<a href="'.$_SERVER['REQUEST_URI'].'#a_model"><font size="3" color="red">'.$error.'</font></a><br>';
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

		$this->displayFormBegin('frmmodva',false,true);		
		echo '<tr><th colspan="4" align="center"><a name="a_contact" ></a><b>Contact information</b><br></th></tr>';

		for ($i = 0; $i < $this->dataset->nbPis; $i++){
			echo '<tr><td colspan="4" align="center"><b>Contact '.($i+1).'</b><br>';//</td></tr>';
			$this->displayErrorsContact($i);
   			$this->displayPersonForm($i);
		}		
		echo '<tr><td colspan="4" align="center">'.$this->getElement('bouton_add_pi')->toHTML().'</td></tr>';
		
		echo '<tr><th colspan="4" align="center"><a name="a_model" ></a><b>Sources</b></td></tr>';
		echo '<tr><td colspan="4" align="center"><b>Model'.'</b></td></tr>';
		$this->displayErrorsModel(); 
		echo '<tr><td><font color="#467AA7">'.$this->getElement('model')->getLabel().'</font></td><td colspan="3">'.$this->getElement('model')->toHTML();
		echo '&nbsp;&nbsp;or add new&nbsp;'.$this->getElement('new_model')->toHTML().'</td></tr>';
		echo '<tr><td>'.$this->getElement('model_type')->getLabel().'</td><td colspan="3">'.$this->getElement('model_type')->toHTML().'</td></tr>';
		echo '<tr><td><font color="#467AA7">'.$this->getElement('simu')->getLabel().'</font></td><td colspan="3">'.$this->getElement('simu')->toHTML();
		echo '&nbsp;&nbsp;or add new&nbsp;'.$this->getElement('new_simu')->toHTML().'</td></tr>';
 

		echo '<tr><th colspan="4" align="center"><a name="a_descr" ><a name="a_general" ></a><b>Data description</b></td></tr>';
		$this->displayErrorsModDataDescr();
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

		echo '<tr><th colspan="4" align="center"><a name="a_param" ></a><b>Parameters</b></td></tr>';
				
		for ($i = 0; $i < $this->dataset->nbVars; $i++){
			echo '<tr><td colspan="4" align="center"><b>Parameter '.($i+1).'</b>'.$this->getElement('var_id_'.$i)->toHTML().'</td></tr>';
			$this->displayErrorsParams($i);
			$this->displayParamForm($i,false,false,true);		
		}
		echo '<tr><td colspan="4" align="center">'.$this->getElement('bouton_add_variable')->toHTML().'</td></tr>';
		
		echo '<tr><th colspan="4" align="center"><a name="a_cover" ></a><b>Coverage</b></td></tr>';
		$this->displayErrorsCoverage();
		echo '<tr><td colspan="4" align="center"><b>Temporal Coverage</b><br></td></tr>';
		echo '<tr><td>'.$this->getElement('dats_date_begin')->getLabel().'</td><td>'.$this->getElement('dats_date_begin')->toHTML()."</td>";
		echo '<td>'.$this->getElement('dats_date_end')->getLabel().'</td><td>'.$this->getElement('dats_date_end')->toHTML().'</td></tr>';

		$this->displayGeoCoverageForm();
		$this->displayDataResolutionForm();
		$this->displayGridForm();


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
