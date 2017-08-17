<?php
require_once("forms/login_form.php");
require_once("conf/conf.php");
require_once("bd/dataset.php");
require_once("bd/dataset.php");
require_once("bd/project.php");
require_once("bd/period.php");
require_once("bd/personne.php");
require_once("bd/organism.php");
require_once("bd/dataset_type.php");
require_once("bd/data_format.php");
require_once("bd/dats_var.php");
require_once("bd/dats_sensor.php");
require_once("bd/sensor.php");
require_once("bd/gcmd_instrument_keyword.php");
require_once("bd/manufacturer.php");
require_once("bd/variable.php");
require_once("bd/gcmd_science_keyword.php");
require_once("bd/place.php");
require_once("bd/boundings.php");
require_once("bd/gcmd_plateform_keyword.php");
require_once("bd/unit.php");
require_once("bd/data_policy.php");
require_once("bd/database.php");

//class base_site_form extends HTML_QuickForm{
class base_site_form extends login_form{

	var $dataset;

	function createLoginForm(){
		global $project_name;
		//User déjà loggé sur le site de référence
	if ($project_name != MainProject) {
			if ($_SERVER ['HTTP_REFERER'] == constant(strtolower ( $project_name ) .'WebSite')){
				$projectUser = new portalUser ();
				$projectUser->cn = strtolower ( $project_name );
				$_SESSION ['loggedUser'] = serialize ( $projectUser );
				// return;
			}
		} else {
			if ($_SERVER ['HTTP_REFERER'] == PORTAL_WebSite){
				$projectUser = new portalUser ();
				$projectUser->cn = strtolower ( $project_name );
				$_SESSION ['loggedUser'] = serialize ( $projectUser );
				// return;
			}
		}

		if (isset($_SESSION['loggedUser'])){
			$this->user = unserialize($_SESSION['loggedUser']);
			//echo 'loggedUser trouvé dans la session<br>';
		}/*else{
			parent::createLoginForm('Username');
		}*/
		
		if (!$this->isCat($this->dataset)){
			parent::createLoginForm('Login');
			
		}
	}

	function createFormBase(){
		
		$this->addElement('hidden','dats_id');
		$this->addElement('text','dats_title','Dataset name');

		$this->addElement('textarea','dats_abstract','Abstract',array('cols'=>70, 'rows'=>5));
		$this->applyFilter('dats_abstract','trim');
		$this->addElement('textarea','dats_purpose','Observing strategy',array('cols'=>70, 'rows'=>5));
		$this->applyFilter('dats_purpose','trim');
		$this->addElement('textarea','dats_reference','References',array('cols'=>70, 'rows'=>1));
		$this->applyFilter('dats_reference','trim');
		$this->addElement('textarea','dats_use_constraints','Use constraints',array('cols'=>70, 'rows'=>5));
		$this->applyFilter('dats_use_constraints','trim');

		$this->addElement('text','dats_version','Version');
		$this->applyFilter('dats_version','trim');

		$this->addElement('text','dats_date_begin','Date begin<br>(yyyy-mm-dd)',array('size'=>10));
		$this->addElement('text','dats_date_end','Date end<br>(yyyy-mm-dd)',array('size'=>10));
		for ($i = 0; $i < $this->dataset->nbProj; $i++){
			$this->createFormProject($i);
		}
		$this->createFormDataPolicy();
		$this->createFormDatabase();
		for ($i = 0; $i < $this->dataset->nbFormats; $i++){
			$this->createFormDataFormat($i);
		}
		for ($i = 0; $i < $this->dataset->nbPis; $i++){
			$this->createFormPersonne($i);
		}
			
		$this->addElement('submit', 'bouton_save', 'Save');

	}

	function disableElement($elementName){
		$this->getElement($elementName)->setAttribute('onfocus','blur()');
		$this->getElement($elementName)->setAttribute('style','background-color: transparent;');
	}

	function addvalidationRulesBase(){
		$this->registerRule('validDate','function','validDate');
		$this->registerRule('validPeriod','function','validPeriod');
		$this->registerRule('existe','function','existe');
		$this->registerRule('number_range','function','number_range');
		$this->registerRule('validInterval','function','validInterval');
		$this->registerRule('couple_not_null','function','couple_not_null');

		$this->registerRule('validParam','function','validParam');
		$this->registerRule('validUnit_existe','function','validUnit_existe');
		$this->registerRule('validUnit_required','function','validUnit_required');

		$this->addRule('dats_title','General information: Metadata informative title is required','required');
		$this->addRule('dats_title','General information: Dataset name exceeds the maximum length allowed (100 characters)','maxlength',100);
			
		$this->addRule('dats_date_begin','General information: Date begin is not a date','validDate');
		$this->addRule('dats_date_end','General information: Date end is not a date','validDate');
		$this->addRule(array('dats_date_begin','dats_date_end'),'General information: Date end must be after date begin','validPeriod');

		if ($this->dataset->dats_id == 0){
			$this->addRule('dats_title','General information: A dataset with the same title exists in the database','existe',array('dataset','dats_title'));
		}
			
		if (isset($this->dataset->data_policy) && !empty($this->dataset->data_policy) && $this->dataset->data_policy->data_policy_id > 0){
			$this->getElement('new_data_policy')->setAttribute('onfocus','blur()');
		}else {
			//$this->addRule('new_data_policy','A data policy with the same name already exists in the database','existe',array('data_policy','data_policy_name'));
		}
		$this->addRule('new_data_policy','Data use information: Data policy exceeds the maximum length allowed (100 characters)','maxlength',100);
			
		$attrs = array();
		if (isset($this->dataset->database) && !empty($this->dataset->database) && $this->dataset->database->database_id > 0){
			$this->disableElement('new_database');
			$this->disableElement('new_db_url');
		}else {
			//$this->addRule('new_database','A database with the same title already exists','existe',array('database','database_name'));
		}
		$this->addRule('new_database','Data use information: Database name exceeds the maximum length allowed (250 characters)','maxlength',250);
		$this->addRule('new_db_url','Data use information: Database url exceeds the maximum length allowed (250 characters)','maxlength',250);
			
		//Formats
		for ($i = 0; $i < $this->dataset->nbFormats; $i++){
			$this->addRule('data_format_'.$i,'Data use information: Format name '.($i+1).' exceeds the maximum length allowed (100 characters)','maxlength',100);
			if (isset($this->dataset->data_formats[$i]) && !empty($this->dataset->data_formats[$i]) && $this->dataset->data_formats[$i]->data_format_id > 0){
				$this->disableElement('new_data_format_'.$i);
			}else{
				//$this->addRule('new_data_format_'.$i,'Data format '.($i+1).': This format already exists in the database','existe',array('data_format','data_format_name'));
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
				//$this->addRule('pi_name_'.$i,'Contact '.($i+1).': A contact with the same name is already present in the database. Select it in the drop-down list.','existe',array('personne','pers_name'));
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

	}

	function initFormSiteBoundings(){
		if (isset($this->dataset->sites[0]->boundings) && !empty($this->dataset->sites[0]->boundings)){
			$this->getElement('west_bound')->setValue($this->dataset->sites[0]->boundings->west_bounding_coord);
			$this->getElement('east_bound')->setValue($this->dataset->sites[0]->boundings->east_bounding_coord);
			$this->getElement('north_bound')->setValue($this->dataset->sites[0]->boundings->north_bounding_coord);
			$this->getElement('south_bound')->setValue($this->dataset->sites[0]->boundings->south_bounding_coord);
		}
		$this->getElement('place_alt_min')->setValue($this->dataset->sites[0]->place_elevation_min);
		$this->getElement('place_alt_max')->setValue($this->dataset->sites[0]->place_elevation_max);
	}

	function disableSiteBoundings(){
		$this->disableElement('west_bound');
		$this->disableElement('east_bound');
		$this->disableElement('north_bound');
		$this->disableElement('south_bound');
		$this->disableElement('place_alt_min');
		$this->disableElement('place_alt_max');
	}

	function saveFormSiteBoundings(){
		$this->dataset->sites[0]->place_elevation_min = $this->exportValue('place_alt_min');
		$this->dataset->sites[0]->place_elevation_max =$this->exportValue('place_alt_max');

		$this->dataset->sites[0]->boundings = new boundings;
		$this->dataset->sites[0]->bound_id = 0;
		$west = $this->exportValue('west_bound');
		if (isset($west) && strlen($west) > 0)
		$this->dataset->sites[0]->boundings->west_bounding_coord = $west;
		else
		$this->dataset->sites[0]->bound_id = -1;

		$east = $this->exportValue('east_bound');
		if (isset($east) && strlen($east) > 0)
		$this->dataset->sites[0]->boundings->east_bounding_coord = $east;
		else
		$this->dataset->sites[0]->bound_id = -1;

		$north = $this->exportValue('north_bound');
		if (isset($north) && strlen($north) > 0)
		$this->dataset->sites[0]->boundings->north_bounding_coord = $north;
		else
		$this->dataset->sites[0]->bound_id = -1;

		$south = $this->exportValue('south_bound');
		if (isset($south) && strlen($south) > 0)
		$this->dataset->sites[0]->boundings->south_bounding_coord = $south;
		else
		$this->dataset->sites[0]->bound_id = -1;
	}

	function addValidationRulesSiteBoundings($prefixMsg){
		$this->addRule('west_bound',$prefixMsg.': West bounding coordinate must be numeric','numeric');
		$this->addRule('west_bound',$prefixMsg.': West bounding coordinate is incorrect','number_range',array(-180,180));
		$this->addRule('east_bound',$prefixMsg.': East bounding coordinate must be numeric','numeric');
		$this->addRule('east_bound',$prefixMsg.': East bounding coordinate is incorrect','number_range',array(-180,180));
		$this->addRule('north_bound',$prefixMsg.': North bounding coordinate must be numeric','numeric');
		$this->addRule('north_bound',$prefixMsg.': North bounding coordinate is incorrect','number_range',array(-90,90));
		$this->addRule('south_bound',$prefixMsg.': South bounding coordinate must be numeric','numeric');
		$this->addRule('south_bound',$prefixMsg.': South bounding coordinate is incorrect','number_range',array(-90,90));
		$this->addRule(array('west_bound', 'east_bound','south_bound', 'north_bound'), $prefixMsg.': Incomplete boundings', 'completeBoundings');
		$this->addRule(array('west_bound', 'east_bound','south_bound', 'north_bound'), $prefixMsg.': Incorrect boundings', 'validBoundings');
		$this->addRule('place_alt_min',$prefixMsg.': Altitude min must be numeric','numeric');
		$this->addRule('place_alt_max',$prefixMsg.': Altitude max must be numeric','numeric');
		$this->addRule('place_alt_min',$prefixMsg.': Altitude out of range [-30000:200000]','number_range',array(-30000,200000));
		$this->addRule('place_alt_max',$prefixMsg.': Altitude out of range [-30000:200000]','number_range',array(-30000,200000));
		$this->addRule(array('place_alt_min', 'place_alt_max'), $prefixMsg.': Altitude max must be greater than altitude min', 'validInterval');
		$this->addRule(array('west_bound', 'east_bound'), $prefixMsg.': East bound coordinate must be greater than west bound', 'validInterval');
		$this->addRule(array('south_bound', 'north_bound'), $prefixMsg.': North bound coordinate must be greater than South bound', 'validInterval');
	}
	
	function addValidationRulesResolution($prefixMsg = 'Instrument'){
		$this->addRule('sensor_resol_temp',$prefixMsg.': Observation frequency exceeds the maximum length allowed (100 characters)','maxlength',100);
		$this->addRule('sensor_vert_resolution',$prefixMsg.': Vertical coverage exceeds the maximum length allowed (100 characters)','maxlength',100);
		$this->addRule('sensor_horiz_resolution',$prefixMsg.': Horizontal coverage exceeds the maximum length allowed (100 characters)','maxlength',100);
	}
	
	function addValidationRulesVariable($i,$j,$suffix,$prefixMsg){
		//echo 'adding validation rule to variable '.$suffix.' for i = '.$i.' j = '.$j.'<br>';
		$this->addRule('sensor_precision_'.$suffix,$prefixMsg.': Sensor precision exceeds the maximum length allowed (100 characters)','maxlength',100);
		$this->addRule('new_variable_'.$suffix,$prefixMsg.': Name exceeds the maximum length allowed (100 characters)','maxlength',100);
		$this->addRule('new_unit_'.$suffix,$prefixMsg.': Unit name exceeds the maximum length allowed (50 characters)','maxlength',50);
		$this->addRule('new_unit_code_'.$suffix,$prefixMsg.': Unit code exceeds the maximum length allowed (20 characters)','maxlength',20);

		$this->addRule('var_date_min_'.$suffix,$prefixMsg.': Date begin is not a date','validDate');
		$this->addRule('var_date_max_'.$suffix,$prefixMsg.': Date end is not a date','validDate');
		$this->addRule(array('var_date_min_'.$suffix,'var_date_max_'.$suffix),$prefixMsg.': Date end must be after date begin','validPeriod');

		if ( isset($this->dataset->sensors[$i]->sensor_vars[$j]->unit) && ($this->dataset->sensors[$i]->sensor_vars[$j]->unit->unit_id > 0) ){
			$this->disableElement('new_unit_'.$suffix);
			$this->disableElement('new_unit_code_'.$suffix);
		}else{
			$this->addRule(array('unit_'.$suffix,'new_unit_'.$suffix,'new_unit_code_'.$suffix),$prefixMsg.': Unit name is required','validUnit_required');
			$this->addRule(array('unit_'.$suffix,'new_unit_'.$suffix,'new_unit_code_'.$suffix),$prefixMsg.': this unit is already present in the database','validUnit_existe');
		}

		$this->addRule('var_date_min_'.$suffix,$prefixMsg.': Keyword or name is required when date begin is specified','validParam',array($this,$suffix));
		$this->addRule('var_date_max_'.$suffix,$prefixMsg.': Keyword or name is required when date end is specified','validParam',array($this,$suffix));
		$this->addRule('new_unit_'.$suffix,$prefixMsg.': Keyword or name is required when unit is specified','validParam',array($this,$suffix));
		$this->addRule('methode_acq_'.$suffix,$prefixMsg.': Keyword or name is required when methodology is specified','validParam',array($this,$suffix));
		$this->addRule('sensor_precision_'.$suffix,$prefixMsg.': Keyword or name is required when precision is specified','validParam',array($this,$suffix));
	}

	function createFormSensorKeyword($i){
		$key = new gcmd_instrument_keyword;
		$key_select = $key->chargeForm($this,'sensor_gcmd_'.$i,'Instrument type');
		$this->addElement($key_select);
	}

	function createFormPeriod($projectName){
		$per = new period;
		$per_select = $per->chargeForm($this,'period','Period',$projectName);
		$this->addElement($per_select);
	}

	function createFormProject($i)
	{
		$proj = new project;
		$proj_select = $proj->chargeForm($this,'project_'.$i,'Project '.($i+1));
		$this->addElement($proj_select);
	}

	function createFormDataPolicy()
	{
		$dp = new data_policy;
		$dp_select = $dp->chargeForm($this,'data_policy','Data policy');
		$this->addElement($dp_select);
		$this->addElement('text','new_data_policy','new data policy');		
	}

	function createFormDatabase()
	{
		$db = new database;
		$db_select = $db->chargeForm($this,'database','Database');
		$this->addElement($db_select);

		$this->addElement('text','new_database','new database');
		$this->addElement('text','new_db_url','Database url');
			
	}

	function createFormDataFormat($i){
		$dformat = new data_format;
		$dformat_select = $dformat->chargeForm($this,'data_format_'.$i,'Data format '.($i+1),$i);
		$this->addElement($dformat_select);
		$this->addElement('text','new_data_format_'.$i,'new data format: ');
	}

	function createFormOrganisme($indice)
	{
		$org = new organism;
		$org_select = $org->chargeForm($this,'organism_'.$indice,'Organism short name',$indice);
		$this->addElement($org_select);
			
		$this->addElement('text','org_sname_'.$indice,'New organism short name: ',$indice);
		$this->addElement('text','org_fname_'.$indice,'Organism full name',$indice);
		$this->addElement('text','org_url_'.$indice,'URL',$indice);
	}

	function createFormPersonne($indice){
		$pers = new personne;
		$pers_select = $pers->chargeForm($this,'pi_'.$indice,'Contact Name',$indice);
		$this->addElement($pers_select);

		$this->addElement('text','pi_name_'.$indice,'new (lastname firstname): ');
		$this->applyFilter('pi_name_'.$indice,'trim');
		$this->addElement('text','email1_'.$indice,'email1',$indice);
		$this->addElement('text','email2_'.$indice,'email2',$indice);
			
		$ct = new contact_type;
		$ct_select = $ct->chargeForm($this,'contact_type_'.$indice,'Contact type');
		$this->addElement($ct_select);
		
		$this->createFormOrganisme($indice);
	}

	function createFormSiteBoundings(){
		$this->addElement('text','west_bound','West bounding coordinate (°)');
		$this->addElement('text','east_bound','East bounding coordinate (°)');
		$this->addElement('text','north_bound','North bounding coordinate (°)');
		$this->addElement('text','south_bound','South bounding coordinate (°)');

		$this->addElement('text','place_alt_min','Altitude min (m)');
		$this->addElement('text','place_alt_max','Altitude max (m)');
	}

	function createFormGeoCoverage(){
			$area = new place;
			$area_select = $area->chargeFormRegion($this,'area','Area name');
			$this->addElement($area_select);
			$this->addElement('text','new_area','Area name');
			$this->applyFilter('new_area','trim');
			$this->createFormSiteBoundings(0);
		}

		function initFormGeoCoverage(){
			if (isset($this->dataset->sites) && !empty($this->dataset->sites)){
				if ( isset($this->dataset->sites[0]) && !empty($this->dataset->sites[0]) ){
					$this->getElement('area')->setSelected($this->dataset->sites[0]->place_id);
					$this->getElement('new_area')->setValue($this->dataset->sites[0]->place_name);

					$this->initFormSiteBoundings(0);
				}
			}
			
		}
		
		function saveFormGeoCoverage(){
			$this->dataset->sites = array();
			$this->dataset->sites[0] = new place;

			$this->dataset->sites[0]->place_id = $this->exportValue('area');
			$this->dataset->sites[0]->place_name = $this->exportValue('new_area');
			$this->dataset->sites[0]->gcmd_plateform_keyword = new gcmd_plateform_keyword;
			$this->dataset->sites[0]->gcmd_plateform_keyword = $this->dataset->sites[0]->gcmd_plateform_keyword->getByName("Geographic Region");
			$this->dataset->sites[0]->gcmd_plat_id = & $this->dataset->sites[0]->gcmd_plateform_keyword->gcmd_plat_id;
			$this->saveFormSiteBoundings(0);
				
			if (empty($this->dataset->sites[0]->place_name)){
				$this->dataset->sites[0]->place_id = -1;
			}
		}
		
		function addValidationRulesGeoCoverage(){
			$this->addRule('area','Coverage: area name is required','couple_not_null',array($this,'new_area'));
			if (isset($this->dataset->sites[0]) && !empty($this->dataset->sites[0]) && $this->dataset->sites[0]->place_id > 0){
				$this->disableElement('new_area');
				$this->disableSiteBoundings(0);
			}else{
				$this->addRule('new_area','Coverage: This area name is already present in the database. Select it in the drop-down list or chose another name.','existe',array('place','place_name'));
				$this->addValidationRulesSiteBoundings(0,'Coverage');
			}
				
		}
		
	function createFormResolution($i){
		$this->addElement('text','sensor_resol_temp_'.$i,'Temporal');
		$this->applyFilter('sensor_resol_temp_'.$i,'trim');
		$this->addElement('text','sensor_vert_resolution_'.$i,'Vertical');
		$this->applyFilter('sensor_vert_resolution_'.$i,'trim');
		$this->addElement('text','sensor_horiz_resolution_'.$i,'Horizontal');
		$this->applyFilter('sensor_horiz_resolution_'.$i,'trim');
		/*$this->addElement('text','sensor_lon_resolution_'.$i,'Longitude');
		$this->applyFilter('sensor_lon_resolution_'.$i,'trim');*/
	}
	
	function createFormVariable($i,$j,$type = ''){

		$key = new gcmd_science_keyword;
		$key_select = $key->chargeForm($this,'gcmd_science_key_'.$type.$i.'_'.$j,'Parameter keyword');
		$this->addElement($key_select);

		echo "create variable ".$type.$i."_".$j."<br>";
		$this->addElement('hidden','var_id_'.$type.$i.'_'.$j);
		$this->addElement('text','new_variable_'.$type.$i.'_'.$j,'New parameter name (if not in parameter keyword list)');//,array('onchange' => "resetSelect('variable_".$type.$i."')"));
		$this->applyFilter('new_variable_'.$type.$i.'_'.$j,'trim');

		$unit = new unit;
		$unit_select = $unit->chargeForm($this,'unit_'.$type.$i.'_'.$j,'Unit',$i.'_'.$j,$type); 
		$this->addElement($unit_select);
		$this->addElement('text','new_unit_'.$type.$i.'_'.$j,'new unit');
		$this->applyFilter('new_unit_'.$type.$i.'_'.$j,'trim');
		$this->addElement('text','new_unit_code_'.$type.$i.'_'.$j,', unit code: ');
		$this->applyFilter('new_unit_code_'.$type.$i.'_'.$j,'trim');
		$this->addElement('textarea','methode_acq_'.$type.$i.'_'.$j,'Acquisition methodology and quality',array('cols'=>70, 'rows'=>5));
		$this->applyFilter('methode_acq_'.$type.$i.'_'.$j,'trim');
		$this->addElement('text','sensor_precision_'.$type.$i.'_'.$j,'Sensor precision');
		$this->applyFilter('sensor_precision_'.$type.$i.'_'.$j,'trim');

		// format pour les dates
		$options = array(
          			'language'  => 'en',
          			'format'    => 'Y-M-d',
		);
		$this->addElement('text','var_date_min_'.$type.$i.'_'.$j,'Date begin (yyyy-mm-jj)',$options);
		$this->addElement('text','var_date_max_'.$type.$i.'_'.$j,'Date end (yyyy-mm-jj)',$options);
			
	}

	function initFormBase(){

		//DATASET
		$this->getElement('dats_id')->setValue($this->dataset->dats_id);
		$this->getElement('dats_title')->setValue($this->dataset->dats_title);
		$this->getElement('dats_abstract')->setValue($this->dataset->dats_abstract);
		$this->getElement('dats_purpose')->setValue($this->dataset->dats_purpose);
		$this->getElement('dats_use_constraints')->setValue($this->dataset->dats_use_constraints);
		$this->getElement('dats_reference')->setValue($this->dataset->dats_reference);
		$this->getElement('dats_date_begin')->setValue($this->dataset->dats_date_begin);
		$this->getElement('dats_date_end')->setValue($this->dataset->dats_date_end);
		$this->getElement('dats_version')->setValue($this->dataset->dats_version);

		//Contacts
		if (isset($this->dataset->originators) && !empty($this->dataset->originators)){
			for ($i = 0; $i < count($this->dataset->originators); $i++){
				$this->initFormPersonne($i);
			}
		}
			
		//PROJECT
		$indice = 0;
		if (isset($this->dataset->projects)){
			foreach ($this->dataset->projects as $proj){
				if (isset($proj)){
					if (isset($proj->pro_project_id) && !empty($proj->pro_project_id)){
						$parent = $proj->pro_project_id;
						$p_id = $proj->project_id;
					}else{
						$parent = $proj->project_id;
						$p_id = 0;
					}
					$this->setDefaults(array('project_'.$indice++ => array($parent,$p_id)));
				}
			}
		}
			
		//DATABASE
		if (isset($this->dataset->database) && !empty($this->dataset->database)){
			$this->getElement('database')->setSelected($this->dataset->database_id);
			$this->getElement('new_database')->setValue($this->dataset->database->database_name);
			$this->getElement('new_db_url')->setValue($this->dataset->database->database_url);
		}

		//DATA POLICY
		if (isset($this->dataset->data_policy) && !empty($this->dataset->data_policy)){
			$this->getElement('data_policy')->setSelected($this->dataset->data_policy->data_policy_id);
			$this->getElement('new_data_policy')->setValue($this->dataset->data_policy->data_policy_name);
		}

		//DATA FORMATS
		if (isset($this->dataset->data_formats) && !empty($this->dataset->data_formats)){
			for ($i = 0; $i < count($this->dataset->data_formats);$i++){
				if (isset($this->dataset->data_formats[$i]) && !empty($this->dataset->data_formats[$i])){
					$this->getElement('data_format_'.$i)->setSelected($this->dataset->data_formats[$i]->data_format_id);
					$this->getElement('new_data_format_'.$i)->setValue($this->dataset->data_formats[$i]->data_format_name);
				}
			}
		}

	}

	function initFormPersonne($i){
		$this->getElement('pi_'.$i)->setSelected($this->dataset->originators[$i]->pers_id);
		$this->getElement('pi_name_'.$i)->setValue($this->dataset->originators[$i]->pers_name);
		$this->getElement('email1_'.$i)->setValue($this->dataset->originators[$i]->pers_email_1);
		$this->getElement('email2_'.$i)->setValue($this->dataset->originators[$i]->pers_email_2);
		$this->getElement('organism_'.$i)->setSelected($this->dataset->originators[$i]->organism->org_id);
		$this->getElement('org_sname_'.$i)->setValue($this->dataset->originators[$i]->organism->org_sname);
		$this->getElement('org_fname_'.$i)->setValue($this->dataset->originators[$i]->organism->org_fname);
		$this->getElement('org_url_'.$i)->setValue($this->dataset->originators[$i]->organism->org_url);

		$this->getElement('contact_type_'.$i)->setSelected($this->dataset->originators[$i]->contact_type_id);
	}

	function initFormResolution($i){
		$this->getElement('sensor_resol_temp_'.$i)->setValue($this->dataset->dats_sensors[$i]->sensor_resol_temp);
		$this->getElement('sensor_vert_resolution_'.$i)->setValue($this->dataset->dats_sensors[$i]->sensor_vert_resolution);
		$this->getElement('sensor_horiz_resolution_'.$i)->setValue($this->dataset->dats_sensors[$i]->sensor_lat_resolution);
	}

	function initFormVariable($i,$j,$nb,$suffix){
		if (isset($this->dataset->dats_sensors[$i]->sensor->sensor_vars[$j]) && !empty($this->dataset->dats_sensors[$i]->sensor->sensor_vars[$j]) && ($this->dataset->dats_sensors[$i]->sensor->sensor_vars[$j]->var_id > 0) ){
			echo "variable : ".$suffix.$i."_".$j."<br>";
			$this->getElement('var_id_'.$suffix.$i.'_'.$nb)->setValue($this->dataset->dats_sensors[$i]->sensor->sensor_vars[$j]->var_id);
			$this->getElement('new_variable_'.$suffix.$i.'_'.$nb)->setValue($this->dataset->dats_sensors[$i]->sensor->sensor_vars[$j]->variable->var_name);
			$gcmd = new gcmd_science_keyword;
			$gcmd = $gcmd->getById($this->dataset->dats_sensors[$i]->sensor->sensor_vars[$j]->variable->gcmd_id);
			if (isset($gcmd) && !empty($gcmd))
			{
				$table = array();
				for ($k=4;$k >=1;$k--){
					if ($gcmd->gcmd_level == $k){
						$table[$k-1] = $gcmd->gcmd_id;
						$gcmd = $gcmd->gcmd_parent;
					}else
					$table[$k-1] = 0;

				}
				ksort($table);
					
				$this->getElement('gcmd_science_key_'.$suffix.$i.'_'.$nb)->setValue($table);
			}
			$this->getElement('methode_acq_'.$suffix.$i.'_'.$nb)->setValue($this->dataset->dats_sensors[$i]->sensor->sensor_vars[$j]->methode_acq);
			$this->getElement('var_date_min_'.$suffix.$i.'_'.$nb)->setValue($this->dataset->dats_sensors[$i]->sensor->sensor_vars[$j]->date_min);
			$this->getElement('var_date_max_'.$suffix.$i.'_'.$nb)->setValue($this->dataset->dats_sensors[$i]->sensor->sensor_vars[$j]->date_max);

			$this->getElement('sensor_precision_'.$suffix.$i.'_'.$nb)->setValue($this->dataset->dats_sensors[$i]->sensor->sensor_vars[$j]->sensor_precision);
			if (isset($this->dataset->dats_sensors[$i]->sensor->sensor_vars[$j]->unit) && !empty($this->dataset->dats_sensors[$i]->sensor->sensor_vars[$j]->unit) 
		&& ($this->dataset->dats_sensors[$i]->sensor->sensor_vars[$j]->unit->unit_id > 0) ){
			$this->getElement('unit_'.$suffix.$i.'_'.$nb)->setSelected($this->dataset->dats_sensors[$i]->sensor->sensor_vars[$j]->unit->unit_id);
			$this->getElement('new_unit_'.$suffix.$i.'_'.$nb)->setValue($this->dataset->dats_sensors[$i]->sensor->sensor_vars[$j]->unit->unit_name);
			$this->getElement('new_unit_code_'.$suffix.$i.'_'.$nb)->setValue($this->dataset->dats_sensors[$i]->sensor->sensor_vars[$j]->unit->unit_code);
		}
		}

	}


	function saveFormBase(){
			
		$this->dataset->dats_id = $this->exportValue('dats_id');
		$this->dataset->dats_title = $this->exportValue('dats_title');

		$this->dataset->dats_abstract = $this->exportValue('dats_abstract');
		$this->dataset->dats_purpose = $this->exportValue('dats_purpose');
		$this->dataset->dats_use_constraints = $this->exportValue('dats_use_constraints');
		$this->dataset->dats_reference = $this->exportValue('dats_reference');
		$this->dataset->dats_date_begin = $this->exportValue('dats_date_begin');
		$this->dataset->dats_date_end = $this->exportValue('dats_date_end');

		$this->dataset->dats_version = $this->exportValue('dats_version');

		//CONTACTS
		$this->dataset->originators = array();
		for ($i = 0; $i < $this->dataset->nbPis; $i++){
			$this->saveFormPersonne($i);
		}
		$this->dataset->organism = & $this->dataset->originators[0]->organism;
		$this->dataset->org_id = & $this->dataset->organism->org_id;

		
		//PROJECT
		$this->dataset->projects = array();
		$j = 0;
		for ($i = 0; $i < $this->dataset->nbProj; $i++){
			$proj = $this->exportValue('project_'.$i);
			if ($proj[2] == 0){
				if ($proj[1] == 0){
					$projectId = $proj[0];
				}else {
					$projectId = $proj[1];
				}
			}else{
				$projectId = $proj[2];
			}
		
			if (isset($projectId) && $projectId != 0){
				$this->dataset->projects[$j] = new project;
				$this->dataset->projects[$j] = $this->dataset->projects[$j]->getById($projectId);
				$j++;
			}
		}
		

		//DATABASE
		$this->dataset->database = new database;
		$this->dataset->database->database_id = $this->exportValue('database');
		$this->dataset->database->database_name = $this->exportValue('new_database');
		$this->dataset->database->database_url = $this->exportValue('new_db_url');
		if (empty($this->dataset->database->database_name)){
			$this->dataset->database->database_id = -1;
		}

		$this->dataset->database_id = & $this->dataset->database->database_id;


		//DATA_POLICY
		$this->dataset->data_policy = new data_policy;
		$this->dataset->data_policy->data_policy_id = $this->exportValue('data_policy');
		$this->dataset->data_policy->data_policy_name = $this->exportValue('new_data_policy');
		if (empty($this->dataset->data_policy->data_policy_name)){
			$this->dataset->data_policy->data_policy_id = -1;
		}
		$this->dataset->data_policy_id = & $this->dataset->data_policy->data_policy_id;

		//DATA_FORMAT
		$this->dataset->data_formats = array();
		for ($i = 0; $i < $this->dataset->nbFormats; $i++){
			$this->dataset->data_formats[$i] = new data_format;
			$this->dataset->data_formats[$i]->data_format_id = $this->exportValue('data_format_'.$i);
			$this->dataset->data_formats[$i]->data_format_name = $this->exportValue('new_data_format_'.$i);
			if (empty($this->dataset->data_formats[$i]->data_format_name)){
				$this->dataset->data_formats[$i]->data_format_id = -1;
			}
		}


	}

	function saveFormPersonne($i){
		$this->dataset->originators[$i] = new personne;
		$pers_id = $this->exportValue('pi_'.$i);
		$pers_name = $this->exportValue('pi_name_'.$i);
		if (empty($pers_name)){
			$pers_id = -1;
		}
		$this->dataset->originators[$i]->pers_id = $pers_id;
		$this->dataset->originators[$i]->pers_name = $pers_name;
		$this->dataset->originators[$i]->pers_email_1 = $this->exportValue('email1_'.$i);
		$this->dataset->originators[$i]->pers_email_2 = $this->exportValue('email2_'.$i);;

		$this->dataset->originators[$i]->organism = new organism;
		$this->dataset->originators[$i]->organism->org_id = $this->exportValue('organism_'.$i);
		$this->dataset->originators[$i]->organism->org_sname = $this->exportValue('org_sname_'.$i);
		$this->dataset->originators[$i]->organism->org_fname = $this->exportValue('org_fname_'.$i);
		$this->dataset->originators[$i]->organism->org_url = $this->exportValue('org_url_'.$i);

		$this->dataset->originators[$i]->contact_type_id = $this->exportValue('contact_type_'.$i);
		$this->dataset->originators[$i]->org_id = &  $this->dataset->originators[$i]->organism->org_id;
	}

	function saveFormResolution($i){
		$this->dataset->dats_sensors[$i]->sensor_resol_temp = $this->exportValue('sensor_resol_temp_'.$i);
		$this->dataset->dats_sensors[$i]->sensor_vert_resolution = $this->exportValue('sensor_vert_resolution_'.$i);
		$this->dataset->dats_sensors[$i]->sensor_lat_resolution = $this->exportValue('sensor_horiz_resolution_'.$i);
		$this->dataset->dats_sensors[$i]->sensor_lon_resolution = $this->exportValue('sensor_horiz_resolution_'.$i);
	}

	function saveFormVariables($i,$nb,$flag = 0,$suffix = '',$incr = 0){

		$dataset = & $this->dataset;
		//echo 'in saveFormVariables<br>';
		//echo 'i = '.$i.' nb = '.$nb.' flag = '.$flag.' suffix = '.$suffix.' incr = '.$incr.'<br>';
		$indice = 0;
		for ($j = 0; $j < $nb; $j++)
		{
			$var_id = $this->exportValue('var_id_'.$suffix.$i.'_'.$j);
			$var_name = $this->exportValue('new_variable_'.$suffix.$i.'_'.$j);
			
			$gcmd_ids = $this->exportValue('gcmd_science_key_'.$suffix.$i.'_'.$j);
			$gcmd_id = 0;
			for ($k = 3;$k >= 0;$k--){

				if (isset($gcmd_ids[$k]) && $gcmd_ids[$k] > 0){
					$gcmd_id = $gcmd_ids[$k];
					break;
				}
			}
			//echo '<b>gcmd_id = '.$gcmd_id.'</b><br>';
			if ($gcmd_id > 0 || !empty($var_name))
			{
				//echo '<b>saving sensor_var at indice '.($indice+$incr).'<br></b>';
				$dataset->dats_sensors[$i]->sensor->sensor_vars[$indice+$incr] = new sensor_var;
				$dataset->dats_sensors[$i]->sensor->sensor_vars[$indice+$incr]->variable = new variable;
				$dataset->dats_sensors[$i]->sensor->sensor_vars[$indice+$incr]->variable->var_id = $var_id;
				$dataset->dats_sensors[$i]->sensor->sensor_vars[$indice+$incr]->variable->var_name = $var_name;
				$dataset->dats_sensors[$i]->sensor->sensor_vars[$indice+$incr]->variable->gcmd = new gcmd_science_keyword;
				$dataset->dats_sensors[$i]->sensor->sensor_vars[$indice+$incr]->variable->gcmd->getById($gcmd_id);
				$dataset->dats_sensors[$i]->sensor->sensor_vars[$indice+$incr]->variable->gcmd_id = $gcmd_id;
				$dataset->dats_sensors[$i]->sensor->sensor_vars[$indice+$incr]->unit = new unit;
				$dataset->dats_sensors[$i]->sensor->sensor_vars[$indice+$incr]->unit->unit_name = $this->exportValue('new_unit_'.$suffix.$i.'_'.$j);
				$dataset->dats_sensors[$i]->sensor->sensor_vars[$indice+$incr]->unit->unit_code = $this->exportValue('new_unit_code_'.$suffix.$i.'_'.$j);
				$dataset->dats_sensors[$i]->sensor->sensor_vars[$indice+$incr]->unit->unit_id = $this->exportValue('unit_'.$suffix.$i.'_'.$j);
				$dataset->dats_sensors[$i]->sensor->sensor_vars[$indice+$incr]->methode_acq = $this->exportValue('methode_acq_'.$suffix.$i.'_'.$j);
				$dataset->dats_sensors[$i]->sensor->sensor_vars[$indice+$incr]->date_min = $this->exportValue('var_date_min_'.$suffix.$i.'_'.$j);
				$dataset->dats_sensors[$i]->sensor->sensor_vars[$indice+$incr]->date_max = $this->exportValue('var_date_max_'.$suffix.$i.'_'.$j);
				$dataset->dats_sensors[$i]->sensor->sensor_vars[$indice+$incr]->falg_param_calcule = $flag;
				$dataset->dats_sensors[$i]->sensor->sensor_vars[$indice+$incr]->sensor_precision = $this->exportValue('sensor_precision_'.$suffix.$i.'_'.$j);
				$indice++;
			}
			/*else{
				if (empty($dataset->dats_sensors[$i]->sensor->sensor_vars[$j+$incr]->variable->var_name)){
					$dataset->dats_sensors[$i]->sensor->sensor_vars[$j+$incr]->variable->var_id = -1;
				}
			}*/
			
			
			/*if ($dataset->dats_sensors[$i]->sensor->sensor_vars[$j+$incr]->variable->var_id != -1)
			{
				$start = count($this->dataset->dats_variables);
				$dataset->dats_variables[$j+$start] = new dats_var;
				$dataset->dats_variables[$j+$start]->variable = & $dataset->dats_sensors[$i]->sensor->sensor_vars[$j+$incr]->variable;
				$dataset->dats_variables[$j+$start]->var_id = & $dataset->dats_sensors[$i]->sensor->sensor_vars[$j+$incr]->variable->var_id;
				$dataset->dats_variables[$j+$start]->unit = & $dataset->dats_sensors[$i]->sensor->sensor_vars[$j+$incr]->unit;
	
				if (empty($this->dataset->dats_variables[$j+$start]->unit->unit_id) && empty($this->dataset->dats_variables[$j+$start]->unit->unit_name)){
					$dataset->dats_variables[$j+$start]->unit->unit_id = -1;
				}
				$dataset->dats_variables[$j+$start]->unit_id = & $dataset->dats_variables[$j+$start]->unit->unit_id;
				$dataset->dats_variables[$j+$start]->methode_acq = & $dataset->dats_sensors[$i]->sensor->sensor_vars[$j+$incr]->methode_acq;
				$dataset->dats_variables[$j+$start]->date_min = & $dataset->dats_sensors[$i]->sensor->sensor_vars[$j+$incr]->date_min;
				$dataset->dats_variables[$j+$start]->date_max = & $dataset->dats_sensors[$i]->sensor->sensor_vars[$j+$incr]->date_max;
				$dataset->dats_variables[$j+$start]->flag_param_calcule = $flag;
				$dataset->dats_variables[$j+$start]->variable->sensor_precision = & $dataset->dats_sensors[$i]->sensor->sensor_vars[$j+$incr]->sensor_precision;
			}*/
		}
		return $indice;
	}
	
	function saveDatsVars($nbSensors)
	{
		$dataset = & $this->dataset;
		unset($dataset->dats_vars);
		$dataset->dats_vars = array();
		$indice = 0;
		for ($i = 0; $i < $nbSensors; $i++)
		{
			if (isset($dataset->dats_sensors[$i]->sensor->sensor_vars) && !empty($dataset->dats_sensors[$i]->sensor->sensor_vars))
			{
				for ($j = 0; $j < count($dataset->dats_sensors[$i]->sensor->sensor_vars);$j++)
				{
					if ($dataset->dats_sensors[$i]->sensor->sensor_vars[$j]->variable->var_id != -1)
					{
						$dataset->dats_variables[$indice] = new dats_var;
						$dataset->dats_variables[$indice]->variable = & $dataset->dats_sensors[$i]->sensor->sensor_vars[$j]->variable;
						$dataset->dats_variables[$indice]->var_id = & $dataset->dats_sensors[$i]->sensor->sensor_vars[$j]->variable->var_id;
						$dataset->dats_variables[$indice]->unit = & $dataset->dats_sensors[$i]->sensor->sensor_vars[$j]->unit;
			
						if (empty($this->dataset->dats_variables[$indice]->unit->unit_id) && empty($this->dataset->dats_variables[$j]->unit->unit_name)){
							$dataset->dats_variables[$indice]->unit->unit_id = -1;
						}
						$dataset->dats_variables[$indice]->unit_id = & $dataset->dats_variables[$j]->unit->unit_id;
						$dataset->dats_variables[$indice]->methode_acq = & $dataset->dats_sensors[$i]->sensor->sensor_vars[$j]->methode_acq;
						$dataset->dats_variables[$indice]->date_min = & $dataset->dats_sensors[$i]->sensor->sensor_vars[$j]->date_min;
						$dataset->dats_variables[$indice]->date_max = & $dataset->dats_sensors[$i]->sensor->sensor_vars[$j]->date_max;
						$dataset->dats_variables[$indice]->flag_param_calcule = & $dataset->dats_sensors[$i]->sensor->sensor_vars[$j]->falg_param_calcule;
						$dataset->dats_variables[$indice]->variable->sensor_precision = & $dataset->dats_sensors[$i]->sensor->sensor_vars[$j]->sensor_precision;
						$indice++;
					}
				}
			}
			//else echo '<b>dataset->dats_sensors['.$i.']->sensor_vars is empty</b><br>';
		}
	}

	function getErrorMessage($elementName){
		$errorMsg = $this->getElementError($elementName);
		if (!isset($errorMsg) || empty($errorMsg)){
			return '';
		}

		$offset = strpos($errorMsg,':');
		if ($offset !== false){
			return substr($errorMsg,$offset+2).'<br>';
		}else{
			return $errorMsg.'<br>';
		}
	}

	function getErrorMessages($elementNames){
		$result = '';
		foreach ($elementNames as $elementName){
			$result .= $this->getErrorMessage($elementName);
		}
		return $result;
	}

	function displayErrors($elementNames){
		$messages = $this->getErrorMessages($elementNames);
		if (isset($messages) && !empty($messages)){
			echo '<tr><td colspan="4"><font color="red" >'.$messages.'</font></td></tr>';
		}/*else
		echo '<tr><td colspan="4">toto</td></tr>';*/
	}

	function displayErrorsContact($i){
		$this->displayErrors(array('pi_'.$i,'pi_name_'.$i,'email1_'.$i,'email2_'.$i,'organism_'.$i,'org_fname_'.$i,'org_sname_'.$i,'org_url_'.$i));
	}

	function displayErrorsParams($suffix){
		$this->displayErrors(array('new_variable_'.$suffix,'unit_'.$suffix,'new_unit_'.$suffix,'new_unit_code_'.$suffix,
  			'sensor_precision_'.$suffix,'var_date_min_'.$suffix,'var_date_max_'.$suffix,'methode_acq_'.$suffix));

	}

	function displayErrorsGeneralInfo(){
		$this->displayErrors(array('dats_title','dats_date_begin','dats_date_end'));
	}

	function displayErrorsUseInfo(){
		$elementNames = array('new_data_policy','new_database','new_db_url');
		for ($i = 0; $i < $this->dataset->nbFormats; $i++){
			$elementNames[] =  'data_format_'.$i;
		}
		$this->displayErrors($elementNames);
	}

	function displayDataDescrForm(){
		echo '<tr><th colspan="4" align="center"><a name="a_descr" ></a><b>Site description</b></td></tr>';
		echo '<tr><td>'.$this->getElement('dats_abstract')->getLabel().'</td><td colspan="3">'.$this->getElement('dats_abstract')->toHTML().'</td></tr>';
		echo '<tr><td>'.$this->getElement('dats_purpose')->getLabel().'</td><td colspan="3">'.$this->getElement('dats_purpose')->toHTML().'</td></tr>';
		echo '<tr><td>'.$this->getElement('dats_reference')->getLabel().'</td><td colspan="3">'.$this->getElement('dats_reference')->toHTML().'</td></tr>';
	}

	function displayPersonForm($i){
		echo '<tr><td><font color="#467AA7">'.$this->getElement('contact_type_'.$i)->getLabel().'</font></td><td colspan="3">'.$this->getElement('contact_type_'.$i)->toHTML();
		if ($i == 0){
			echo '<tr><td><font color="#467AA7">'.$this->getElement('pi_'.$i)->getLabel().'</font></td><td colspan="3">'.$this->getElement('pi_'.$i)->toHTML();
			echo '<font color="#467AA7">&nbsp;&nbsp;or add '.$this->getElement('pi_name_'.$i)->getLabel().'</font>'.$this->getElement('pi_name_'.$i)->toHTML().'</td></tr>';
			echo '<tr><td><font color="#467AA7">'.$this->getElement('email1_'.$i)->getLabel().'</font></td><td>'.$this->getElement('email1_'.$i)->toHTML().'</td>';
		}else{
			echo '<tr><td>'.$this->getElement('pi_'.$i)->getLabel().'</td><td colspan="3">'.$this->getElement('pi_'.$i)->toHTML();
			echo '&nbsp;&nbsp;or add '.$this->getElement('pi_name_'.$i)->getLabel().''.$this->getElement('pi_name_'.$i)->toHTML().'</td></tr>';
			echo '<tr><td>'.$this->getElement('email1_'.$i)->getLabel().'</td><td>'.$this->getElement('email1_'.$i)->toHTML().'</td>';
		}
		echo '<td>'.$this->getElement('email2_'.$i)->getLabel().'</td><td>'.$this->getElement('email2_'.$i)->toHTML().'</td></tr>';

		echo '<tr><td><font color="#467AA7">'.$this->getElement('organism_'.$i)->getLabel().'</font></td><td colspan="3">'.$this->getElement('organism_'.$i)->toHTML();

		echo '&nbsp;&nbsp;or add '.$this->getElement('org_sname_'.$i)->getLabel().''.$this->getElement('org_sname_'.$i)->toHTML().'</td></tr>';
		echo '<tr><td>'.$this->getElement('org_fname_'.$i)->getLabel().'</td><td>'.$this->getElement('org_fname_'.$i)->toHTML().'</td>';
		echo '<td>'.$this->getElement('org_url_'.$i)->getLabel().'</td><td>'.$this->getElement('org_url_'.$i)->toHTML().'</td></tr>';
	}

	function displaySiteBoundingsForm(){
		echo '<tr><td>'.$this->getElement('west_bound')->getLabel().'</td><td>'.$this->getElement('west_bound')->toHTML().'</td>';
		echo '<td>'.$this->getElement('east_bound')->getLabel().'</td><td>'.$this->getElement('east_bound')->toHTML().'</td></tr>';
		echo '<tr><td>'.$this->getElement('north_bound')->getLabel().'</td><td>'.$this->getElement('north_bound')->toHTML().'</td>';
		echo '<td>'.$this->getElement('south_bound')->getLabel().'</td><td>'.$this->getElement('south_bound')->toHTML().'</td></tr>';
		echo '<tr><td>'.$this->getElement('place_alt_min')->getLabel().'</td><td>'.$this->getElement('place_alt_min')->toHTML().'</td>';
		echo '<td>'.$this->getElement('place_alt_max')->getLabel().'</td><td>'.$this->getElement('place_alt_max')->toHTML().'</td></tr>';
	}
	function displayDataResolutionForm(){
		echo '<tr><td colspan="4" align="center"><b>Data resolution</b><br></td></tr>';
		echo '<tr><td>'.$this->getElement('sensor_horiz_resolution')->getLabel().'</td><td>'.$this->getElement('sensor_horiz_resolution')->toHTML().'</td>';
		echo '<td>'.$this->getElement('sensor_vert_resolution')->getLabel().'</td><td>'.$this->getElement('sensor_vert_resolution')->toHTML().'</td></tr>';
		echo '<tr><td>'.$this->getElement('sensor_resol_temp')->getLabel().'</td><td>'.$this->getElement('sensor_resol_temp')->toHTML().'</td><td colspan="2"></td></tr>';
	}
	function displayGeoCoverageForm(){
		echo '<tr><td colspan="4" align="center"><b>Geographic Coverage</b></td></tr>';
		echo '<tr><td><font color="#467AA7">'.$this->getElement('area')->getLabel().'</font></td><td colspan="3">'.$this->getElement('area')->toHTML();
		echo '&nbsp;&nbsp;or add new&nbsp;'.$this->getElement('new_area')->toHTML().'</td></tr>';
			
		$this->displaySiteBoundingsForm(0);
	}
	
	function displayParamForm($i,$j, $withDates = false){

		echo '<tr><td>'.$this->getElement('gcmd_science_key_'.$i.'_'.$j)->getLabel().'</td><td colspan="3">'.$this->getElement('gcmd_science_key_'.$i.'_'.$j)->toHTML().'</td></tr>';
		echo '<tr><td colspan="2">'.$this->getElement('new_variable_'.$i.'_'.$j)->getLabel().'</td><td colspan="2">'.$this->getElement('new_variable_'.$i.'_'.$j)->toHTML().'</td></tr>';
		echo '<tr><td>'.$this->getElement('unit_'.$i.'_'.$j)->getLabel().'</td><td colspan="3">'.$this->getElement('unit_'.$i.'_'.$j)->toHTML();
		echo '&nbsp;&nbsp;or add '.$this->getElement('new_unit_'.$i.'_'.$j)->getLabel().''.$this->getElement('new_unit_'.$i.'_'.$j)->toHTML();
		echo $this->getElement('new_unit_code_'.$i.'_'.$j)->getLabel().''.$this->getElement('new_unit_code_'.$i.'_'.$j)->toHTML().'</td></tr>';
		echo '<tr><td>'.$this->getElement('methode_acq_'.$i.'_'.$j)->getLabel().'</td><td colspan="3">'.$this->getElement('methode_acq_'.$i.'_'.$j)->toHTML().'</td></tr>';
		if ($withDates){
			echo '<tr><td>'.$this->getElement('var_date_min_'.$i.'_'.$j)->getLabel().'</td><td>'.$this->getElement('var_date_min_'.$i.'_'.$j)->toHTML().'</td>';
			echo '<td>'.$this->getElement('var_date_max_'.$i.'_'.$j)->getLabel().'</td><td>'.$this->getElement('var_date_max_'.$i.'_'.$j)->toHTML().'</td></tr>';
		}
		echo '<tr><td>'.$this->getElement('sensor_precision_'.$i.'_'.$j)->getLabel().'</td><td>'.$this->getElement('sensor_precision_'.$i.'_'.$j)->toHTML().'</td><td colspan="2"></td></tr>';
	}


	function displayFormBegin($frmname){
		echo '<div id="errors" color="red"></div><br>';
		if (strpos($_SERVER['REQUEST_URI'],'?datsId')){
                        //$reqUri = strstr($_SERVER['REQUEST_URI'],'&datsId',true);
                        $reqUri = substr($_SERVER['REQUEST_URI'],0,strpos($_SERVER['REQUEST_URI'],'?datsId'));
                }else{
                        $reqUri = $_SERVER['REQUEST_URI'];
                }		
		echo '<form action="'.$reqUri.'" method="post" name="'.$frmname.'" id="'.$frmname.'" >';
		echo '<SCRIPT LANGUAGE="Javascript" SRC="/js/functions.js"> </SCRIPT>';
		 
		echo $this->getElement('dats_id')->toHTML();
		echo '<table><tr><td colspan="4"><font color="#467AA7">Required fields are in blue</font></td></tr>';

		echo '<tr><td colspan="4" align="center"><a href="?datsId=-10">Reset</a></td></tr>';
	}

	function displayFormEnd(){
		echo '<tr><td colspan="4" align="center">'.$this->getElement('bouton_save')->toHTML().'</td></tr></table>';
		echo '</form>';
	}
	
}

?>
