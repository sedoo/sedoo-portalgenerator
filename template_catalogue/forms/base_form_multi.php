<?php

require_once("forms/login_form.php");
require_once("common.php");
require_once("bd/dataset.php");
require_once("bd/project.php");
require_once("bd/period.php");
require_once("bd/personne.php");
require_once("bd/contact_type.php");
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
require_once("bd/gcmd_location_keyword.php");

class base_form_multi extends login_form{

	var $dataset;

	function createLoginForm(){
                //User déjà loggé sur le site hymex.org
                if ($_SERVER['HTTP_REFERER'] == 'http://www.hymex.org/private/catalog/index.php'){
                        //$hymexUser = new mistralsUser();
                        //echo 'arrive de hymex.org<br>';

                        $hymexUser = new user();
                        $hymexUser->cn = 'hymex';
                        $_SESSION['loggedUser'] = serialize($hymexUser);
                        //return;
                }

                if (isset($_SESSION['loggedUser'])){
                        $this->user = unserialize($_SESSION['loggedUser']);
                        //echo 'loggedUser trouvé dans la session<br>';
                        //echo 'type: '.get_class($this->user).'<br>';
                }

                if (!$this->isCat($this->dataset)){
                        parent::createLoginForm('Login');


                }
        }

	function disableElement($elementName){
                $this->getElement($elementName)->setAttribute('onfocus','blur()');
                $this->getElement($elementName)->setAttribute('style','background-color: transparent;');
        }

	function createFormBase(){

                $this->addElement('hidden','dats_id');
                $this->addElement('text','dats_title','Dataset name');

                $this->addElement('textarea','dats_abstract','Abstract',array('cols'=>60, 'rows'=>5));
                $this->applyFilter('dats_abstract','trim');
                $this->addElement('textarea','dats_purpose','Observing strategy',array('cols'=>60, 'rows'=>5));
                $this->applyFilter('dats_purpose','trim');
                $this->addElement('textarea','dats_reference','References',array('cols'=>60, 'rows'=>1));
                $this->applyFilter('dats_reference','trim');
                $this->addElement('textarea','dats_use_constraints','Use constraints',array('cols'=>60, 'rows'=>5));
                $this->applyFilter('dats_use_constraints','trim');

                $this->addElement('text','dats_version','Version');
                $this->applyFilter('dats_version','trim');

                $this->addElement('text','dats_date_begin','Date begin ',array('size'=>10,'placeholder'=>'yyyy-mm-dd'));
                $this->addElement('text','dats_date_end','Date end ',array('size'=>10,'placeholder'=>'yyyy-mm-dd'));
                $this->addElement ( 'text', 'dats_doi', 'DOI', array (
                		'size' => 30
                ) );
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

	function createFormDatabase(){
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

        function createFormOrganisme($indice){
                $org = new organism;
                $org_select = $org->chargeForm($this,'organism_'.$indice,'Organization short name',$indice);
                $this->addElement($org_select);

                $this->addElement('text','org_sname_'.$indice,'New organization short name: ',$indice);
                $this->addElement('text','org_fname_'.$indice,'Organization full name',$indice);
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

	function createFormProject($i){
                $proj = new project;
                $proj_select = $proj->chargeForm($this,'project_'.$i,'Project '.($i+1));
                $this->addElement($proj_select);
        }

        function createFormDataPolicy(){
                $dp = new data_policy;
                $dp_select = $dp->chargeForm($this,'data_policy','Data policy');
                $this->addElement($dp_select);
                $this->addElement('text','new_data_policy','new data policy');

        }

	function createFormPeriod($projectName){
                $per = new period;
                $per_select = $per->chargeForm($this,'period','Period',$projectName);
                $this->addElement($per_select);
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

	function createFormSiteBoundings(){
                $this->addElement('text','west_bound','West bounding coordinate (°)');
                $this->addElement('text','east_bound','East bounding coordinate (°)');
                $this->addElement('text','north_bound','North bounding coordinate (°)');
                $this->addElement('text','south_bound','South bounding coordinate (°)');

                $this->addElement('text','place_alt_min','Altitude min (m)');
                $this->addElement('text','place_alt_max','Altitude max (m)');
        }

	function createFormSensorKeyword($i){
                $key = new gcmd_instrument_keyword;
                $key_select = $key->chargeForm($this,'sensor_gcmd_'.$i,'Instrument type');
                $this->addElement($key_select);
        }

	function createFormManufacturer($i){
                $man = new manufacturer;
                $man_select = $man->chargeForm($this,'manufacturer_'.$i,'Manufacturer','_'.$i);
                $this->addElement($man_select);
                $this->addElement('text','new_manufacturer_'.$i,'new manufacturer ');
                $this->addElement('text','new_manufacturer_url_'.$i,'Manufacturer web site');
        }

	function createFormResolution($i){
                $this->addElement('text','sensor_resol_temp_'.$i,'Temporal');
                $this->applyFilter('sensor_resol_temp_'.$i,'trim');
                $this->addElement('text','sensor_vert_resolution_'.$i,'Vertical');
                $this->applyFilter('sensor_vert_resolution_'.$i,'trim');
                $this->addElement('text','sensor_horiz_resolution_'.$i,'Horizontal');
                $this->applyFilter('sensor_horiz_resolution_'.$i,'trim');
        }

	function createFormVariable($i,$j,$type = ''){

                $key = new gcmd_science_keyword;
                $key_select = $key->chargeForm($this,'gcmd_science_key_'.$type.$i.'_'.$j,'Parameter keyword');
                $this->addElement($key_select);

                //echo "create variable ".$type.$i."_".$j."<br>";
                $this->addElement('hidden','var_id_'.$type.$i.'_'.$j);
                $this->addElement('text','new_variable_'.$type.$i.'_'.$j,'New parameter name (if not in parameter keyword list)');//,array('onchange' => "resetSelect('variable_".$type.$i."')"));
                $this->applyFilter('new_variable_'.$type.$i.'_'.$j,'trim');

                $unit = new unit;
                $unit_select = $unit->chargeForm($this,'unit_'.$type.$i.'_'.$j,'Unit',$i.'_'.$j,$type);
                $this->addElement($unit_select);
                $this->addElement('text','new_unit_'.$type.$i.'_'.$j,'new unit');
                $this->applyFilter('new_unit_'.$type.$i.'_'.$j,'trim');
                $this->addElement('text','new_unit_code_'.$type.$i.'_'.$j,'unit code ');
                $this->applyFilter('new_unit_code_'.$type.$i.'_'.$j,'trim');
                $this->addElement('textarea','methode_acq_'.$type.$i.'_'.$j,'Acquisition methodology and quality',array('cols'=>60, 'rows'=>5));
                $this->applyFilter('methode_acq_'.$type.$i.'_'.$j,'trim');
                $this->addElement('text','sensor_precision_'.$type.$i.'_'.$j,'Sensor precision');
                $this->applyFilter('sensor_precision_'.$type.$i.'_'.$j,'trim');
/*
                // format pour les dates
                $options = array(
                                'language'  => 'en',
                                'format'    => 'Y-M-d',
                );
                $this->addElement('text','var_date_min_'.$type.$i.'_'.$j,'Date begin (yyyy-mm-jj)',$options);
                $this->addElement('text','var_date_max_'.$type.$i.'_'.$j,'Date end (yyyy-mm-jj)',$options);
*/
        }


	function initFormVariable($i,$j,$suffix = ''){
                if (isset($this->dataset->dats_sensors[$i]->sensor->sensor_vars[$j]) && !empty($this->dataset->dats_sensors[$i]->sensor->sensor_vars[$j]) && ($this->dataset->dats_sensors[$i]->sensor->sensor_vars[$j]->var_id > 0) ){
                        //echo "variable : ".$suffix.$i."_".$j."<br>";
                        $this->getElement('var_id_'.$suffix.$i.'_'.$j)->setValue($this->dataset->dats_sensors[$i]->sensor->sensor_vars[$j]->var_id);
                        $this->getElement('new_variable_'.$suffix.$i.'_'.$j)->setValue($this->dataset->dats_sensors[$i]->sensor->sensor_vars[$j]->variable->var_name);
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

                                $this->getElement('gcmd_science_key_'.$suffix.$i.'_'.$j)->setValue($table);
                        }
                        $this->getElement('methode_acq_'.$suffix.$i.'_'.$j)->setValue($this->dataset->dats_sensors[$i]->sensor->sensor_vars[$j]->methode_acq);
//                        $this->getElement('var_date_min_'.$suffix.$i.'_'.$j)->setValue($this->dataset->dats_sensors[$i]->sensor->sensor_vars[$j]->date_min);
//                        $this->getElement('var_date_max_'.$suffix.$i.'_'.$j)->setValue($this->dataset->dats_sensors[$i]->sensor->sensor_vars[$j]->date_max);

                        $this->getElement('sensor_precision_'.$suffix.$i.'_'.$j)->setValue($this->dataset->dats_sensors[$i]->sensor->sensor_vars[$j]->sensor_precision);
                        if (isset($this->dataset->dats_sensors[$i]->sensor->sensor_vars[$j]->unit) && !empty($this->dataset->dats_sensors[$i]->sensor->sensor_vars[$j]->unit) && ($this->dataset->dats_sensors[$i]->sensor->sensor_vars[$j]->unit->unit_id > 0) ){
                        	$this->getElement('unit_'.$suffix.$i.'_'.$j)->setSelected($this->dataset->dats_sensors[$i]->sensor->sensor_vars[$j]->unit->unit_id);
	                        $this->getElement('new_unit_'.$suffix.$i.'_'.$j)->setValue($this->dataset->dats_sensors[$i]->sensor->sensor_vars[$j]->unit->unit_name);
        	                $this->getElement('new_unit_code_'.$suffix.$i.'_'.$j)->setValue($this->dataset->dats_sensors[$i]->sensor->sensor_vars[$j]->unit->unit_code);
                	}
                }
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
                $this->getElement('dats_doi')->setValue($this->dataset->dats_doi);
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

	function initFormResolution($i){
                $this->getElement('sensor_resol_temp_'.$i)->setValue($this->dataset->dats_sensors[$i]->sensor_resol_temp);
                $this->getElement('sensor_vert_resolution_'.$i)->setValue($this->dataset->dats_sensors[$i]->sensor_vert_resolution);
                $this->getElement('sensor_horiz_resolution_'.$i)->setValue($this->dataset->dats_sensors[$i]->sensor_lat_resolution);
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
                $this->dataset->dats_doi = $this->exportValue('dats_doi');

                $this->dataset->dats_version = $this->exportValue('dats_version');

                $this->dataset->dats_creator = $this->user->mail;
                
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

	function saveFormResolution($i){
                $this->dataset->dats_sensors[$i]->sensor_resol_temp = $this->exportValue('sensor_resol_temp_'.$i);
                $this->dataset->dats_sensors[$i]->sensor_vert_resolution = $this->exportValue('sensor_vert_resolution_'.$i);
                $this->dataset->dats_sensors[$i]->sensor_lat_resolution = $this->exportValue('sensor_horiz_resolution_'.$i);
                $this->dataset->dats_sensors[$i]->sensor_lon_resolution = $this->exportValue('sensor_horiz_resolution_'.$i);
        }

	function addValidationRulesBase(){
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
                        $this->addRule('org_fname_'.$i,'Contact '.($i+1).': Organization full name exceeds the maximum length allowed (250 characters)','maxlength',250);
                        $this->addRule('org_sname_'.$i,'Contact '.($i+1).': organization short name exceeds the maximum length allowed (50 characters)','maxlength',50);
                        $this->addRule('org_url_'.$i,'Contact '.($i+1).': organization url exceeds the maximum length allowed (250 characters)','maxlength',250);
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
                                $this->addRule('pi_name_'.$i,'Contact '.($i+1).': organization is required','contact_organism_required',array($this,$i));
                        }
                }
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
                //$this->addRule(array('west_bound', 'east_bound','south_bound', 'north_bound'), $prefixMsg.': Incomplete boundings', 'completeBoundings');
                //$this->addRule(array('east_bound','south_bound', 'north_bound', 'west_bound'), $prefixMsg.': Incomplete boundings', 'completeBoundings');

                               
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

        function displayPersonForm($i){
		$color = "";
		if ($i == 0 || $i == 1){
			$color = 'style="color: #467AA7;"';
		}

                echo "<tr><td $color>".$this->getElement('contact_type_'.$i)->getLabel().'</td><td colspan="3">'.$this->getElement('contact_type_'.$i)->toHTML().'</td></tr>';
                echo "<tr><td $color>".$this->getElement('pi_'.$i)->getLabel().'</td><td colspan="3">'.$this->getElement('pi_'.$i)->toHTML();
                echo '&nbsp;&nbsp;or add '.$this->getElement('pi_name_'.$i)->getLabel().$this->getElement('pi_name_'.$i)->toHTML().'</td></tr>';
                echo "<tr><td $color>".$this->getElement('email1_'.$i)->getLabel().'</td><td>'.$this->getElement('email1_'.$i)->toHTML().'</td>';
		echo '<td>'.$this->getElement('email2_'.$i)->getLabel().'</td><td>'.$this->getElement('email2_'.$i)->toHTML().'</td></tr>';

                echo "<tr><td $color>".$this->getElement('organism_'.$i)->getLabel().'</font></td><td colspan="3">'.$this->getElement('organism_'.$i)->toHTML();
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

	function displayParamForm($i,$j){
                echo '<tr><td>'.$this->getElement('gcmd_science_key_'.$i.'_'.$j)->getLabel().'</td><td colspan="3">'.$this->getElement('gcmd_science_key_'.$i.'_'.$j)->toHTML().'</td></tr>';
                echo '<tr><td colspan="2">'.$this->getElement('new_variable_'.$i.'_'.$j)->getLabel().'</td><td colspan="2">'.$this->getElement('new_variable_'.$i.'_'.$j)->toHTML().'</td></tr>';
                echo '<tr><td>'.$this->getElement('unit_'.$i.'_'.$j)->getLabel().'</td><td colspan="3">'.$this->getElement('unit_'.$i.'_'.$j)->toHTML();
                echo '&nbsp;&nbsp;or add '.$this->getElement('new_unit_'.$i.'_'.$j)->getLabel().' '.$this->getElement('new_unit_'.$i.'_'.$j)->toHTML();
                echo '<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                echo $this->getElement('new_unit_code_'.$i.'_'.$j)->getLabel().' '.$this->getElement('new_unit_code_'.$i.'_'.$j)->toHTML().'</td></tr>';
                echo '<tr><td>'.$this->getElement('methode_acq_'.$i.'_'.$j)->getLabel().'</td><td colspan="3">'.$this->getElement('methode_acq_'.$i.'_'.$j)->toHTML().'</td></tr>';
/*                echo '<tr><td>'.$this->getElement('var_date_min_'.$i.'_'.$j)->getLabel().'</td><td>'.$this->getElement('var_date_min_'.$i.'_'.$j)->toHTML().'</td>';
                echo '<td>'.$this->getElement('var_date_max_'.$i.'_'.$j)->getLabel().'</td><td>'.$this->getElement('var_date_max_'.$i.'_'.$j)->toHTML().'</td></tr>';
*/
                echo '<tr><td>'.$this->getElement('sensor_precision_'.$i.'_'.$j)->getLabel().'</td><td>'.$this->getElement('sensor_precision_'.$i.'_'.$j)->toHTML().'</td><td colspan="2"></td></tr>';
	}
	
	/**
	 * *** Affichage des erreurs ****
	 */
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
	function getHideShow($rowsName, $render = false){
		if ($render)
			return '<a name="'.$rowsName.'_s" style="display: none;" onclick="displayRows(\''.$rowsName.'\')" >&nbsp;[show]</a><a name="'.$rowsName.'" onclick="hideRows(\''.$rowsName.'\')" >&nbsp;[hide]</a>';
		else
			return '';
	}
	function displayErrorsGeneralInfo(){
                $this->displayErrors(array('dats_title','dats_date_begin','dats_date_end'));
        }

	function displayErrorsContact($i){
                $this->displayErrors(array('pi_'.$i,'pi_name_'.$i,'email1_'.$i,'email2_'.$i,'organism_'.$i,'org_fname_'.$i,'org_sname_'.$i,'org_url_'.$i));
        }

	function displayErrorsUseInfo(){
                $elementNames = array('new_data_policy','new_database','new_db_url');
                for ($i = 0; $i < $this->dataset->nbFormats; $i++){
                        $elementNames[] =  'data_format_'.$i;
                }
                $this->displayErrors($elementNames);
        }

	function displayErrorsParams($suffix){
                $this->displayErrors(array('new_variable_'.$suffix,'unit_'.$suffix,'new_unit_'.$suffix,'new_unit_code_'.$suffix,
                        'sensor_precision_'.$suffix,'methode_acq_'.$suffix/*,'var_date_min_'.$suffix,'var_date_max_'.$suffix*/));

        }

}

?>
