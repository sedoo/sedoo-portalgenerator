<?php
/*
 * Created on 8 juil. 2010
*
* To change the template for this generated file go to
* Window - Preferences - PHPeclipse - PHP - Code Templates
*/

require_once("bd/bdConnect.php");
require_once("scripts/logger.php");
require_once("bd/status_final.php");
require_once("bd/status_progress.php");
require_once("bd/boundings.php");
require_once("bd/organism.php");
require_once("bd/period.php");
require_once("bd/dataset_type.php");
require_once("bd/dats_originator.php");
require_once("bd/dats_place.php");
require_once("bd/dats_sensor.php");
require_once("bd/dats_var.php");
require_once("bd/dats_type.php");
require_once("bd/dats_data_format.php");
require_once("bd/dats_proj.php");
require_once("bd/data_format.php");
require_once("bd/data_policy.php");
require_once("bd/database.php");
require_once("bd/sensor.php");
require_once("bd/unit.php");
require_once("bd/sensor_place.php");
require_once("bd/place.php");
require_once("bd/variable.php");
require_once("bd/sensor_var.php");
require_once("scripts/mail.php");
require_once("sortie/fiche2pdf_functions.php");
require_once("bd/idataset.php");
require_once("scripts/displayUtils.php");
require_once ("utils/elastic/ElasticClient.php");

abstract class base_dataset implements iDataset{
	
	var $dats_id;
	var $status_final_id;
	var $status_progress_id;
	var $database_id;
	var $period_id;
	var $data_policy_id;
	var $bound_id;
	var $org_id;
	var $dats_title;
	var $dats_pub_date;
	var $dats_version;
	var $dats_process_level;
	var $dats_other_cit;
	var $dats_abstract;
	var $dats_purpose;
	var $dats_elevation_min;
	var $dats_elevation_max;
	var $dats_date_begin;
	var $dats_date_end;
	var $dats_use_constraints;
	var $dats_access_constraints;
	var $dats_reference;
	var $dats_quality;
	var $status_final;
	var $status_progress;
	var $dats_doi;
	var $boundings;
	var $organism;
	var $period;
	var $database;
	var $data_policy;
	var $originators;
	var $dats_originators;
	var $data_formats;
	var $required_data_formats;
	var $dataset_types;
	var $dats_sensors;
	var $dats_variables;
	var $sites;
	var $dats_creator;

	var $projects;
	var $dats_date_end_not_planned;

	var $is_requested;

	var $is_archived;
	
	var $dats_funding;
	var $dats_dmetmaj;
	var $code;
	var $dats_uuid;

	//Pour l'affichage
	var $nbPis;
	var $nbSites;
	var $nbVars;
	var $nbCalcVars;
	var $nbVarsReel;
	var $nbCalcVarsReel;
	var $nbFormats;
	var $nbProj;
	//add by lolo
	var $nbSensors;

	var $image;
	var $attFile;

	//Connexion bd
	var $bdConn;


	abstract public function init($tab);
	
	abstract protected function insert_others();
	abstract protected function update_before_base();
	abstract protected function update_after_base();
	
	
	
	/* ***** INIT ***** */

	/*
	 * Initialise un jeu sans les sites et les sensors.
	*/
	protected function init_base_dataset($tab){
		$this->dats_id = $tab[0];
		$this->status_final_id = $tab[1];
		$this->database_id = $tab[2];
		$this->period_id = $tab[3];
		$this->status_progress_id = $tab[4];
		$this->bound_id = $tab[5];
		$this->data_policy_id = $tab[6];
		$this->org_id = $tab[7];
		$this->dats_title = $tab[8];
		$this->dats_pub_date = $tab[9];
		$this->dats_version = $tab[10];
		$this->dats_process_level = $tab[11];
		$this->dats_other_cit = $tab[12];
		$this->dats_abstract = $tab[13];
		$this->dats_purpose = $tab[14];
		$this->dats_elevation_min = $tab[15];
		$this->dats_elevation_max = $tab[16];
		$this->dats_date_begin = $tab[17];
		$this->dats_date_end = $tab[18];
		$this->dats_use_constraints = $tab[19];
		$this->dats_access_constraints = $tab[20];
		$this->dats_reference = $tab[21];
		$this->dats_quality = $tab[22];
		$this->image = $tab[23];
		$this->dats_doi = $tab[27];
			
		$this->dats_date_end_not_planned = $tab[24];

		$this->is_requested = $tab[26];

		$this->attFile = $tab[28];
			
		$this->dats_creator = $tab[29];

		$this->is_archived = $tab[30];
		
		$this->dats_funding = $tab [31];
		$this->dats_dmetmaj = $tab [32];
		$this->code = $tab [33];
		$this->dats_uuid = $tab [34];

		if (isset($this->status_final_id) && !empty($this->status_final_id))
		{
			$status = new status_final;
			$this->status_final = $status->getById($this->status_final_id);
		}
		if (isset($this->database_id) && !empty($this->database_id))
		{
			$db = new database;
			$this->database = $db->getById($this->database_id);
		}
		if (isset($this->data_policy_id) && !empty($this->data_policy_id))
		{
			$db = new data_policy;
			$this->data_policy = $db->getById($this->data_policy_id);
		}
		if (isset($this->status_progress_id) && !empty($this->status_progress_id))
		{
			$status = new status_progress;
			$this->status_progress = $status->getById($this->status_progress_id);
		}
		if (isset($this->bound_id) && !empty($this->bound_id))
		{
			$bound = new boundings;
			$this->boundings = $bound->getById($this->bound_id);
		}
		if (isset($this->org_id) && !empty($this->org_id))
		{
			$org = new organism;
			$this->organism = $org->getById($this->org_id);
		}
		if (isset($this->period_id) && !empty($this->period_id)){
			$per = new period;
			$this->period = $per->getById($this->period_id);
		}

		$this->get_dataset_types();
			
		$this->get_data_formats();
		$this->get_required_data_formats();

		$this->get_originators();
		$this->get_dats_originators();

		$this->get_projects();

		$this->get_dats_variables();
	}
	
	protected function init_cpt(){
		//TODO revoir ces compteurs
		$this->nbCalcVarsReel = $this->nbCalcVars;
		$this->nbVarsReel = $this->nbVars;
			
		if ($this->nbSites == 0)
			$this->nbSites = 1;
		if ($this->nbPis == 0)
			$this->nbPis = 1;
		if ($this->nbCalcVars == 0)
			$this->nbCalcVars = 1;
		if ($this->nbVars == 0)
			$this->nbVars = 1;
		if ($this->nbFormats == 0)
			$this->nbFormats = 1;
		if ($this->nbProj == 0)
			$this->nbProj = 1;
		// add by lolo
		if ($this->nbSensors == 0)
			$this->nbSensors = 1;
	}


	private function get_dats_originators(){
		$pers = new dats_originator;
		$this->dats_originators = $pers->getByDataset($this->dats_id);

		//$this->nbPis = count($this->originators);

	}

	private function get_originators(){
		$query = "select * from personne inner join dats_originators using (pers_id) where dats_id = ".$this->dats_id;

		$pers = new personne;
		$this->originators = $pers->getByQuery($query);

		$this->nbPis = count($this->originators);
	}

	private function get_required_data_formats(){
		$query = "select * from data_format where data_format_id in " .
				"(select distinct data_format_id from dats_required_data_format where dats_id = ".$this->dats_id.")";
		$dformat = new data_format;
		$this->required_data_formats = $dformat->getByQuery($query);

	}

	private function get_data_formats(){
		$query = "select * from data_format where data_format_id in " .
				"(select distinct data_format_id from dats_data_format where dats_id = ".$this->dats_id.")";
		$dformat = new data_format;
		$this->data_formats = $dformat->getByQuery($query);

		$this->nbFormats = count($this->data_formats);
	}

	private function get_dataset_types(){
		$query = "select * from dataset_type where dats_type_id in " .
				"(select distinct dats_type_id from dats_type where dats_id = ".$this->dats_id.")";
		$dtype = new dataset_type;
		$this->dataset_types = $dtype->getByQuery($query);
	}

	private function get_dats_variables(){
		$query = "select * from dats_var where dats_id = ".$this->dats_id;
		$dats_var = new dats_var;
		$this->dats_variables = $dats_var->getByQuery($query);
		for ($i = 0; $i < count($this->dats_variables);$i++){
			$this->dats_variables[$i]->getUnit();
			$this->dats_variables[$i]->getVariable();
			$this->dats_variables[$i]->getVerticalLevelType();

			if ($this->dats_variables[$i]->flag_param_calcule == 1){
				$this->nbCalcVars++;
			}else{
				$this->nbVars++;
			}
		}
	}

	private function get_projects(){

		$query = "select * from project where project_id in " .
				"(select distinct project_id from dats_proj where dats_id = ".$this->dats_id.")";
		$proj = new project();
		$this->projects = $proj->getByQuery($query);

		$this->nbProj = count($this->projects);
	}

	/*
	 * A appeler après avoiir initialisé les sensors
	*/
	protected function get_sensor_vars(){
		for ($i = 0; $i < count($this->dats_sensors);$i++){
			if ($this->dats_sensors[$i]->sensor->sensor_id > 0){
				$this->dats_sensors[$i]->sensor->sensor_vars = array();
				$nbVars = 0;
				for ($j = 0; $j < count($this->dats_variables); $j++){
					if ($this->dats_variables[$j]->variable->var_id > 0){
						$sv = new sensor_var;
						$sv = $sv->getByIds($this->dats_variables[$j]->var_id,$this->dats_sensors[$i]->sensor_id);
						if (isset($sv) && !empty($sv)){
							$this->dats_variables[$j]->variable->sensor_precision = $sv->sensor_precision;
							$this->dats_sensors[$i]->sensor->sensor_vars[$nbVars] = $sv;
							$this->dats_sensors[$i]->sensor->sensor_vars[$nbVars]->variable = & $this->dats_variables[$j]->variable;
							$gcmd = new gcmd_science_keyword;
							$this->dats_sensors[$i]->sensor->sensor_vars[$nbVars]->variable->gcmd = & $gcmd->getById($this->dats_variables[$j]->variable->gcmd_id);
							$this->dats_sensors[$i]->sensor->sensor_vars[$nbVars]->var_id = $sv->var_id;
							$this->dats_sensors[$i]->sensor->sensor_vars[$nbVars]->unit = & $this->dats_variables[$j]->unit;
							$this->dats_sensors[$i]->sensor->sensor_vars[$nbVars]->date_min = & $this->dats_variables[$j]->date_min;
							$this->dats_sensors[$i]->sensor->sensor_vars[$nbVars]->date_max = & $this->dats_variables[$j]->date_max;
							$this->dats_sensors[$i]->sensor->sensor_vars[$nbVars]->flag_param_calcule = & $this->dats_variables[$j]->flag_param_calcule;
							$nbVars++;
						}
					}
				}
			}
		}
	}
	
	
	protected function get_dats_sensors(){
		$query = "SELECT * FROM dats_sensor WHERE dats_id = ".$this->dats_id;
		$dats_sensor = new dats_sensor;
		$this->dats_sensors = $dats_sensor->getByQuery($query);

		$this->nbSensors = count($this->dats_sensors);
	}
	
	/*
	 * A appeler, si nécessaire, après avoiir initialisé les sites, les sensors et les sensors_places
	*/
	protected function get_sensor_environments(){
		for ($i = 0; $i < count($this->dats_sensors);$i++){
			if ($this->dats_sensors[$i]->sensor->sensor_id > 0){
				for ($j = 0; $j < count($this->sites); $j++){
					//echo "sensor: ".$this->dats_sensors[$i]->sensor->sensor_id.', site: '.$this->sites[$j]->place_id.', parent: '.$this->sites[$j]->parent_place->place_id.'<br/>';
					if ($this->sites[$j]->place_id > 0){
						$sp = new sensor_place;
						$sp = $sp->getByIds($this->sites[$j]->place_id,$this->dats_sensors[$i]->sensor->sensor_id);
						if (isset($sp) && !empty($sp)){
							$this->sites[$j]->sensor_environment = $sp->environment;
							$this->dats_sensors[$i]->sensor->sensor_environment = $sp->environment;
						}
					}else if($this->sites[$j]->parent_place->place_id > 0){
						$sp = new sensor_place;
						$sp = $sp->getByIds($this->sites[$j]->parent_place->place_id,$this->dats_sensors[$i]->sensor->sensor_id);
						if (isset($sp) && !empty($sp)){
							$this->sites[$j]->sensor_environment = $sp->environment;
							$this->dats_sensors[$i]->sensor->sensor_environment = $sp->environment;
						}
					}
				}
			}
		}
	}



	function newDatasetOnlyTitle($tab)
	{
		$this->dats_id = $tab[0];
		$this->dats_title = $tab[1];
	}

	/*function getOnlyTitles($query)
	{
		$bd = new bdConnect;

		$liste = array();
		if ($resultat = $bd->get_data($query))
		{
			for ($i=0; $i<count($resultat);$i++)
			{
				$liste[$i] = new dataset;
				$liste[$i]->newDatasetOnlyTitle($resultat[$i]);
			}
		}
		return $liste;

	}

	//utilisé dans bd2xml
	function getAll(){
		$query = "select dataset.* from dataset left join dats_type using (dats_id) left join dataset_type using (dats_type_id) order by dats_type_title desc,dats_title asc";

		return $this->getByQuery($query);
	}



	function getByQuery($query){
		$bd = new bdConnect;

		$liste = array();
		if ($resultat = $bd->get_data($query))
		{
			for ($i=0; $i<count($resultat);$i++)
			{
				$liste[$i] = new dataset;
				$liste[$i]->new_dataset($resultat[$i]);
			}
		}
		return $liste;
	}

	function existe()
	{
		$query = "select * from dataset where " .
				"lower(dats_title) = lower('".(str_replace("'","\'",$this->dats_title))."')";
		//echo 'existe'.$query."<br>";
		$bd = new bdConnect;
		if ($resultat = $bd->get_data($query))
		{
			//echo '=>oui<br>';
			$this->new_dataset($resultat[0]);
			return true;
		}
		return false;
	}

	function idExiste()
	{
		$query = "select * from dataset where dats_id = ".$this->dats_id;
		//echo $query."<br>";
		$bd = new bdConnect;
		if ($resultat = $bd->get_data($query))
		{
			$this->new_dataset($resultat[0]);
			return true;
		}
		return false;
	}
*/




	/* ***** INSERT ***** */

	public function insert(){
		//echo "insert()<br>";
		$this->bdConn = new bdConnect;
		$this->bdConn->db_open();
		try{
			$this->bdConn->beginTransaction();
	
			$this->insert_base_dataset();
	
			$this->insert_others();
	
			$this->bdConn->commitTransaction();
			$this->bdConn->db_close();
			$this->sendMailDataset();
			//error_log('[DEBUG] dataset.insert - Insertion réussie, dats_id = '.$this->dats_id."\n",3,LOG_FILE);
			log_debug('dataset.insert - Insertion réussie, dats_id = '.$this->dats_id);
			
			try{
				$client = new ElasticClient();
				$client->indexDataset($this);
			}catch(Exception $ex){
				log_error ( 'dataset index update - ' . $ex->getMessage () );
			}
						
			return true;
		}catch(Exception $e){
			//echo 'Dataset.insert() - Exception reçue : ',  $e->getMessage(), "<br>";
			//error_log('[ERROR] dataset.insert - '.$e->getMessage()."\n",3,LOG_FILE);
			log_error('dataset.update - '.$e->getMessage());
			echo '<h1> ERROR INSERTION : </h1>'.$e->getMessage();
			//$this->sendMailErreur($e);
			try{
				$this->bdConn->rollbackTransaction();
				$this->bdConn->db_close();
			}catch(Exception $e){
				//echo 'Dataset.update() - Exception reçue : ',  $e->getMessage(), "<br>";
				//error_log('[ERROR] dataset.insert - '.$e->getMessage()."\n",3,LOG_FILE);
				log_error('dataset.update - '.$e->getMessage());
				//$this->sendMailErreur($e);
			}
			return false;
		}
	}

	/*
	 * Insère le jeu dans la table dataset dataset
	* + contacts, projets, database, data policy, formats, dataset type, sensors et vars
	*
	*/
	private function insert_base_dataset(){
		if ($this->database_id != -1){
			$this->insert_database();
		}

		if ($this->data_policy_id != -1)
			$this->insert_data_policy();

		$this->insert_originators();


		$query_insert = "insert into dataset (dats_title,dats_pub_date";
		if (!isset($this->dats_pub_date) || empty($this->dats_pub_date) || ($this->dats_pub_date == '--'))
			$query_values = "values ('".str_replace("'","\'",$this->dats_title)."',now()";
		else
			$query_values = "values ('".str_replace("'","\'",$this->dats_title)."','".$this->dats_pub_date."'";
		if (isset($this->bound_id) && !empty($this->bound_id))
		{
			$query_insert .= ",bound_id";
			$query_values .= ",".$this->bound_id;
		}
		if (isset($this->database_id) && !empty($this->database_id) && $this->database_id != -1)
		{
			$query_insert .= ",database_id";
			$query_values .= ",".$this->database_id;
		}
		if (isset($this->data_policy_id) && !empty($this->data_policy_id) && $this->data_policy_id != -1)
		{
			$query_insert .= ",data_policy_id";
			$query_values .= ",".$this->data_policy_id;
		}
		if (isset($this->dats_doi) && !empty($this->dats_doi))
		{
			$query_insert .= ",dats_doi";
			$query_values .= ",'".$this->dats_doi."'";
		}
		if (isset($this->status_final_id) && !empty($this->status_final_id))
		{
			$query_insert .= ",status_final_id";
			$query_values .= ",".$this->status_final_id;
		}
		if (isset($this->status_progress_id) && !empty($this->status_progress_id))
		{
			$query_insert .= ",status_progress_id";
			$query_values .= ",".$this->status_progress_id;
		}
		if (isset($this->org_id) && !empty($this->org_id))
		{
			$query_insert .= ",org_id";
			$query_values .= ",".$this->org_id;
		}
		if (isset($this->period_id) && !empty($this->period_id))
		{
			$query_insert .= ",period_id";
			$query_values .= ",".$this->period_id;
		}
		if (isset($this->dats_version) && !empty($this->dats_version))
		{
			$query_insert .= ",dats_version";
			$query_values .= ",'".str_replace("'","\'",$this->dats_version)."'";
		}
		if (isset($this->dats_process_level) && !empty($this->dats_process_level))
		{
			$query_insert .= ",dats_process_level";
			$query_values .= ",'".str_replace("'","\'",$this->dats_process_level)."'";
		}
		if (isset($this->dats_other_cit) && !empty($this->dats_other_cit))
		{
			$query_insert .= ",dats_other_cit";
			$query_values .= ",'".str_replace("'","\'",$this->dats_other_cit)."'";
		}
		if (isset($this->dats_abstract) && !empty($this->dats_abstract))
		{
			$query_insert .= ",dats_abstract";
			$query_values .= ",'".str_replace("'","\'",$this->dats_abstract)."'";
		}
		if (isset($this->dats_purpose) && !empty($this->dats_purpose))
		{
			$query_insert .= ",dats_purpose";
			$query_values .= ",'".str_replace("'","\'",$this->dats_purpose)."'";
		}
		if (isset($this->dats_date_begin) && !empty($this->dats_date_begin))
		{
			$query_insert .= ",dats_date_begin";
			$query_values .= ",'".$this->dats_date_begin."'";
		}
		if (isset($this->dats_date_end) && !empty($this->dats_date_end))
		{
			$query_insert .= ",dats_date_end";
			$query_values .= ",'".$this->dats_date_end."'";
		}
		if (isset($this->dats_use_constraints) && !empty($this->dats_use_constraints))
		{
			$query_insert .= ",dats_use_constraints";
			$query_values .= ",'".str_replace("'","\'",$this->dats_use_constraints)."'";
		}
		if (isset($this->dats_access_constraints) && !empty($this->dats_access_constraints))
		{
			$query_insert .= ",dats_access_constraints";
			$query_values .= ",'".str_replace("'","\'",$this->dats_access_constraints)."'";
		}
		if (isset($this->dats_reference) && !empty($this->dats_reference))
		{
			$query_insert .= ",dats_reference";
			$query_values .= ",'".str_replace("'","\'",$this->dats_reference)."'";
		}
		if (isset($this->dats_quality) && !empty($this->dats_quality))
		{
			$query_insert .= ",dats_quality";
			$query_values .= ",'".str_replace("'","\'",$this->dats_quality)."'";
		}
		if (isset($this->dats_elevation_min) && !empty($this->dats_elevation_min))
		{
			$query_insert .= ",dats_elevation_min";
			$query_values .= ",".$this->dats_elevation_min;
		}
		if (isset($this->dats_elevation_max) && !empty($this->dats_elevation_max))
		{
			$query_insert .= ",dats_elevation_max";
			$query_values .= ",".$this->dats_elevation_max;
		}
		if (isset($this->image) && !empty($this->image))
		{
			$query_insert .= ",dats_image";
			$query_values .= ",'".str_replace("'","\'",$this->image)."'";
		}
		if (isset($this->dats_date_end_not_planned) && !empty($this->dats_date_end_not_planned)){
			$query_insert .= ",dats_date_end_not_planned";
			$query_values .= ",'".$this->dats_date_end_not_planned."'";
		}
		if (isset($this->attFile) && !empty($this->attFile)){
			$query_insert .= ",dats_att_file";
			$query_values .= ",'".str_replace("'","\'",$this->attFile)."'";
		}
		if (isset($this->dats_creator) && !empty($this->dats_creator)){
			$query_insert .= ", dats_creator";
			$query_values .= ",'".$this->dats_creator."'";
				
		}

		$query = $query_insert.") ".$query_values.")";
		//echo $query;
		$this->bdConn->exec($query);

		$this->dats_id =  $this->bdConn->getLastId('dataset_dats_id_seq');

		//echo 'dats_id:'.$this->dats_id.'<br>';

		$this->insert_dats_originators();
		$this->insert_projects();
		$this->insert_data_formats();
			
		$this->insert_dataset_types();
			
		$this->insert_sensors();
		$this->insert_dats_var();
		$this->insert_sensor_vars();
	}

	public function update(){
		//echo "update()<br>";
		$this->bdConn = new bdConnect;
		$this->bdConn->db_open();
	
		try{
			$this->bdConn->beginTransaction();
				
			$this->update_before_base();
	
			$this->update_base_dataset();
	
			$this->update_after_base();
	
			$this->bdConn->commitTransaction();
			$this->bdConn->db_close();
									
			$this->sendMailDataset();
			//error_log('[DEBUG] dataset.update - Maj réussie, dats_id = '.$this->dats_id."\n",3,LOG_FILE);
			log_debug('dataset.update - Maj réussie, dats_id = '.$this->dats_id);
			
			try{
				$client = new ElasticClient();
				$client->indexDataset($this);
			}catch(Exception $ex){
				log_error ( 'dataset index update - ' . $ex->getMessage () );
			}
			
			return true;
				
		}catch(Exception $e){
			//echo 'Dataset.update() - Exception reçue : ',  $e->getMessage(), "<br>";
			//error_log('[ERROR] dataset.update - '.$e->getMessage()."\n",3,LOG_FILE);
			echo 'Update error : '.$e->getMessage();
			log_error('dataset.update - '.$e->getMessage());
			$this->sendMailErreur($e);
			try{
				$this->bdConn->rollbackTransaction();
				$this->bdConn->db_close();
			}catch(Exception $e){
				//echo 'Dataset.update() - Exception reçue : ',  $e->getMessage(), "<br>";
				//error_log('[ERROR] dataset.update - '.$e->getMessage()."\n",3,LOG_FILE);
				log_error('dataset.update - '.$e->getMessage());
				$this->sendMailErreur($e);
			}
			return false;
		}
	}
	
	private function update_base_dataset(){
		//Supprimer les dats_* du dataset
		$this->bdConn->exec("delete from dats_proj where dats_id = ".$this->dats_id);
		$this->bdConn->exec("delete from dats_originators where dats_id = ".$this->dats_id);
		$this->bdConn->exec("delete from dats_place where dats_id = ".$this->dats_id);
		$this->bdConn->exec("delete from dats_var where dats_id = ".$this->dats_id);
		$this->bdConn->exec("delete from dats_data_format where dats_id = ".$this->dats_id);
		$this->bdConn->exec("delete from dats_required_data_format where dats_id = ".$this->dats_id);
		$this->bdConn->exec("delete from dats_sensor where dats_id = ".$this->dats_id);
		$this->bdConn->exec("delete from dats_type where dats_id = ".$this->dats_id);
			
		if ($this->database_id != -1){
			$this->insert_database();
		}

		if ($this->data_policy_id != -1)
			$this->insert_data_policy();
			
		$this->insert_originators();

		$query = "update dataset set dats_title = '".str_replace("'","\'",$this->dats_title)."',org_id=".$this->org_id;
		if (isset($this->bound_id) && !empty($this->bound_id))
		{
			$query .= ",bound_id=".$this->bound_id;
		}else{
			$query .= ",bound_id=null";
		}
		if (isset($this->dats_doi) && !empty($this->dats_doi)){
			$query .= ",dats_doi='".$this->dats_doi."'";
		}else{
			$query .= ",dats_doi=null";
		}
		if (isset($this->database_id) && !empty($this->database_id) && $this->database_id != -1)
		{
			$query .= ",database_id=".$this->database_id;
		}else{
			$query .= ",database_id=null";
		}
		if (isset($this->data_policy_id) && !empty($this->data_policy_id) && $this->data_policy_id != -1)
		{
			$query .= ",data_policy_id=".$this->data_policy_id;
		}else{
			$query .= ",data_policy_id=null";
		}
		if (isset($this->status_final_id) && !empty($this->status_final_id))
		{
			$query .= ",status_final_id=".$this->status_final_id;
		}else{
			$query .= ",status_final_id=null";
		}
		if (isset($this->status_progress_id) && !empty($this->status_progress_id))
		{
			$query .= ",status_progress_id=".$this->status_progress_id;
		}else{
			$query .= ",status_progress_id=null";
		}
		if (isset($this->period_id) && !empty($this->period_id))
		{
			$query .= ",period_id=".$this->period_id;
		}else{
			$query .= ",period_id=null";
		}
		if (isset($this->dats_version) && !empty($this->dats_version))
		{
			$query .= ",dats_version='".str_replace("'","\'",$this->dats_version)."'";
		}else{
			$query .= ",dats_version=null";
		}
		if (isset($this->dats_process_level) && !empty($this->dats_process_level))
		{
			$query .= ",dats_process_level='".str_replace("'","\'",$this->dats_process_level)."'";
		}else{
			$query .= ",dats_process_level=null";
		}
		if (isset($this->dats_other_cit) && !empty($this->dats_other_cit))
		{
			$query .= ",dats_other_cit='".str_replace("'","\'",$this->dats_other_cit)."'";
		}else{
			$query .= ",dats_other_cit=null";
		}
		if (isset($this->dats_abstract) && !empty($this->dats_abstract))
		{
			$query .= ",dats_abstract='".str_replace("'","\'",$this->dats_abstract)."'";
		}else{
			$query .= ",dats_abstract=null";
		}
		if (isset($this->dats_purpose) && !empty($this->dats_purpose))
		{
			$query .= ",dats_purpose='".str_replace("'","\'",$this->dats_purpose)."'";
		}else{
			$query .= ",dats_purpose=null";
		}
		if (isset($this->dats_date_begin) && !empty($this->dats_date_begin))
		{
			$query .= ",dats_date_begin='".$this->dats_date_begin."'";
		}else{
			$query .= ",dats_date_begin=null";
		}
		if (isset($this->dats_date_end) && !empty($this->dats_date_end))
		{
			$query .= ",dats_date_end='".$this->dats_date_end."'";
		}else{
			$query .= ",dats_date_end=null";
		}
		if (isset($this->dats_use_constraints) && !empty($this->dats_use_constraints))
		{
			$query .= ",dats_use_constraints='".str_replace("'","\'",$this->dats_use_constraints)."'";
		}else{
			$query .= ",dats_use_constraints=null";
		}
		if (isset($this->dats_access_constraints) && !empty($this->dats_access_constraints))
		{
			$query .= ",dats_access_constraints='".str_replace("'","\'",$this->dats_access_constraints)."'";
		}else{
			$query .= ",dats_access_constraints=null";
		}
		if (isset($this->dats_reference) && !empty($this->dats_reference))
		{
			$query .= ",dats_reference='".str_replace("'","\'",$this->dats_reference)."'";
		}else{
			$query .= ",dats_reference=null";
		}
		if (isset($this->dats_quality) && !empty($this->dats_quality))
		{
			$query .= ",dats_quality='".str_replace("'","\'",$this->dats_quality)."'";
		}else{
			$query .= ",dats_quality=null";
		}
		if (isset($this->dats_elevation_min) && !empty($this->dats_elevation_min))
		{
			$query .= ",dats_elevation_min=".$this->dats_elevation_min;
		}else{
			$query .= ",dats_elevation_min=null";
		}
		if (isset($this->dats_elevation_max) && !empty($this->dats_elevation_max))
		{
			$query .= ",dats_elevation_max=".$this->dats_elevation_max;
		}else{
			$query .= ",dats_elevation_max=null";
		}
		if (isset($this->image) && !empty($this->image)){
			$query .= ",dats_image='".str_replace("'","\'",$this->image)."'";
		}else{
			$query .= ",dats_image=null";
		}
		if (isset($this->dats_date_end_not_planned) && !empty($this->dats_date_end_not_planned)){
			$query .= ",dats_date_end_not_planned='".$this->dats_date_end_not_planned."'";
		}else{
			$query .= ",dats_date_end_not_planned=null";
		}
		if (isset($this->attFile) && !empty($this->attFile)){
			$query .= ",dats_att_file='".str_replace("'","\'",$this->attFile)."'";
		}else{
			$query .= ",dats_att_file=null";
		}

		$query .= " where dats_id=".$this->dats_id;
		$this->bdConn->exec($query);

		//echo 'dats_id:'.$this->dats_id.'<br>';

		$this->insert_dats_originators();
		$this->insert_projects();
		$this->insert_data_formats();
			
		$this->insert_dataset_types();
			
		$this->insert_sensors();
		$this->insert_dats_var();
		$this->insert_sensor_vars();

	}


	private function insert_originators(){
		for ($i = 0; $i < count($this->originators); $i++){
			if ($this->originators[$i]->pers_id == 0 ) {
				$this->originators[$i]->insert($this->bdConn);
			}
		}
	}

	private function insert_projects(){
		for ($i = 0; $i < count($this->projects); $i++){
			if ($this->projects[$i]->project_id != 0){
				$dp = new dats_proj;
				$dp->dats_id = $this->dats_id;
				$dp->project_id = $this->projects[$i]->project_id;
				$dp->insert($this->bdConn);
			}
		}
	}

	private function insert_data_policy(){
		if (isset($this->data_policy) && $this->data_policy->data_policy_id == 0){
			$this->data_policy->insert($this->bdConn);
			$this->data_policy_id = $this->data_policy->data_policy_id;
		}
	}

	private function insert_database(){
		if (isset($this->database) && $this->database->database_id == 0){
			$this->database->insert($this->bdConn);

			$this->database_id = $this->database->database_id;
		}
	}

	private function insert_dats_originators(){
		for ($i = 0; $i < count($this->originators); $i++){
			$do = new dats_originator;
			$do->dats_id = $this->dats_id;
			$do->pers_id = $this->originators[$i]->pers_id;
			$do->contact_type_id = $this->originators[$i]->contact_type_id;
			if ($do->pers_id != -1)
				$do->insert($this->bdConn);
		}
	}

	private function insert_data_formats(){
		for ($i = 0; $i < count($this->data_formats); $i++) {
			if ($this->data_formats[$i]->data_format_id != -1){
					
				if ($this->data_formats[$i]->data_format_id == 0){
					$this->data_formats[$i]->insert($this->bdConn);
				}
				//echo 'data_format_id:'.$this->data_format_id.'<br>';

				$ddf = new dats_data_format();
				$ddf->dats_id = $this->dats_id;
				$ddf->data_format_id = $this->data_formats[$i]->data_format_id;
					
				$ddf->insert($this->bdConn);
			}
		}

		for ($i = 0; $i < count($this->required_data_formats); $i++) {
			if ($this->required_data_formats[$i]->data_format_id > 0){

				$ddf = new dats_data_format();
				$ddf->dats_id = $this->dats_id;
				$ddf->data_format_id = $this->required_data_formats[$i]->data_format_id;
					
				$ddf->insert($this->bdConn,'dats_required_data_format');
			}
		}
	}

	private function insert_dataset_types(){
		if (isset($this->dataset_types) && !empty($this->dataset_types)){
			for ($i = 0; $i < count($this->dataset_types); $i++) {
				if ($this->dataset_types[$i]->dats_type_id >= 0){

					if ($this->dataset_types[$i]->dats_type_id == 0){
						$this->dataset_types[$i]->insert($this->bdConn);
					}

					$ddt = new dats_type();
					$ddt->dats_id = $this->dats_id;
					$ddt->dats_type_id = $this->dataset_types[$i]->dats_type_id;

					$ddt->insert($this->bdConn);
				}
			}
		}
	}

	private function insert_sensors(){
		for ($i=0;$i < count($this->dats_sensors); $i++){
			if (isset($this->dats_sensors[$i])){
				if (isset($this->dats_sensors[$i]->sensor) && $this->dats_sensors[$i]->sensor->sensor_id == 0){
					$this->dats_sensors[$i]->sensor_id = $this->dats_sensors[$i]->sensor->insert($this->bdConn);
				}else if (isset($this->dats_sensors[$i]->sensor) && $this->dats_sensors[$i]->sensor->sensor_id > 0){
					$this->dats_sensors[$i]->sensor_id = $this->dats_sensors[$i]->sensor->update($this->bdConn);
				}
				if ($this->dats_sensors[$i]->sensor_id != -1 && strlen($this->dats_sensors[$i]->sensor_id) > 0){
					$this->dats_sensors[$i]->dats_id = $this->dats_id;
					$this->dats_sensors[$i]->insert($this->bdConn);
				}
			}
		}

	}

	private function insert_dats_var() 	{
		//		echo 'nb dats_vars : '.count($this->dats_variables).'<br>';
		for ($i = 0; $i < count($this->dats_variables); $i++){
			if (isset($this->dats_variables[$i]->unit) && $this->dats_variables[$i]->unit_id == 0){
				$this->dats_variables[$i]->unit->insert($this->bdConn);
			}
			//  			echo 'unit_id:'.$this->dats_variables[$i]->unit_id.'<br>';
			if (isset($this->dats_variables[$i]->vertical_level_type) && $this->dats_variables[$i]->vert_level_type_id == 0){
				$this->dats_variables[$i]->vertical_level_type->insert($this->bdConn);
			}
			//    			echo $i.'-'.$this->dats_variables[$i]->variable->var_name.'<br>';

			if (isset($this->dats_variables[$i]->variable) && $this->dats_variables[$i]->variable->var_id >= 0 && !$this->dats_variables[$i]->variable->existe()){
				$this->dats_variables[$i]->variable->insert($this->bdConn);
			}

			$this->dats_variables[$i]->var_id = $this->dats_variables[$i]->variable->var_id;
			//			echo 'var_id:'.$this->dats_variables[$i]->var_id.'<br>';

			$this->dats_variables[$i]->dats_id = $this->dats_id;

			if ($this->dats_variables[$i]->var_id > 0 ){
				$this->dats_variables[$i]->insert($this->bdConn);
			}
		}

	}

	private function insert_sensor_vars(){
		for ($i =0; $i < count($this->dats_sensors);$i++){
			if ($this->dats_sensors[$i]->sensor->sensor_id > 0){
				for ($j = 0; $j < count($this->dats_sensors[$i]->sensor->sensor_vars); $j++){
					if (!empty($this->dats_sensors[$i]->sensor->sensor_vars[$j]->variable->var_id) && $this->dats_sensors[$i]->sensor->sensor_vars[$j]->variable->var_id != -1){
						$this->dats_sensors[$i]->sensor->sensor_vars[$j]->sensor_id = $this->dats_sensors[$i]->sensor->sensor_id;
						$this->dats_sensors[$i]->sensor->sensor_vars[$j]->var_id = $this->dats_sensors[$i]->sensor->sensor_vars[$j]->variable->var_id;
						$this->dats_sensors[$i]->sensor->sensor_vars[$j]->insert($this->bdConn);
					}
				}
			}
		}
	}

	/* ***** DIVERS ***** */

	protected function base_dataset_to_string(){
		//echo "base_dataset_to_string()<br/>";
		$result = "Dataset id: ".$this->dats_id."\n";
		$result .= 'Dataset title: '.$this->dats_title."\n";
		if (isset($this->dats_doi)){
			$result .= 'Dataset doi: '.$this->dats_doi."\n";
		}
		$result .= 'Creator: '.$this->dats_creator."\n";
		$result .= "Projects:\n";
		for ($i = 0; $i < count($this->projects); $i++){
			if (isset($this->projects[$i])){
				$result .= '- '.$this->projects[$i]->toString()."\n";
			}
		}
		if (isset($this->dats_version)){
			$result .= 'Version: '.$this->dats_version."\n";
		}
		if (isset($this->period)){
			$result .= 'Period: '.$this->period->period_name."\n";
		}
		if (isset($this->dats_date_begin)){
			$result .= 'Début: '.$this->dats_date_begin."\n";
		}
		if (isset($this->dats_date_end)){
			$result .= 'Fin: '.$this->dats_date_end."\n";
		}
		if ( $this->dats_date_end_not_planned ){
			$result .= "not planned\n";
		}
		$result .= "\n";
			
		//$result .= 'Contacts: '.'\n';
		for ($i = 0; $i < count($this->originators); $i++){
			$result .= $this->originators[$i]->toString()."\n";
		}
		$result .= "\nAbstract: ".$this->dats_abstract."\n";
		$result .= 'Purpose: '.$this->dats_purpose."\n";
		$result .= 'Reference: '.$this->dats_reference."\n";
		$result .= 'Image: '.$this->image."\n";
		$result .= 'Attached document: '.$this->attFile."\n";

		$result .= "\nUse constraints : ".$this->dats_use_constraints."\n";
		if (isset($this->database)){
			$result .= 'Database: '.$this->database->toString()."\n";
		}
		if (isset($this->data_policy)){
			$result .= 'Data policy: '.$this->data_policy->data_policy_name."\n";
		}
		$result .= "Data formats:\n";
		for ($i = 0; $i < count($this->data_formats);$i++){
			$result .= '- '.$this->data_formats[$i]->data_format_name."\n";
		}
		$result .= "\nRequired data formats:\n";
		for ($i = 0; $i < count($this->required_data_formats);$i++){
			$result .= '- '.$this->required_data_formats[$i]->data_format_name."\n";
		}

		$result .= "\n\n";
		for ($i = 0; $i < count($this->dats_variables); $i++){
			if ($this->dats_variables[$i]->variable->var_id > 0){
				$result .= $this->dats_variables[$i]->toString()."\n";
			}
		}
		
		return $result;
			
	}

	protected function sendMailErreur(Exception $e) {
		sendMail ( Portal_AdminGroup_Email, '[' . MainProject . '] Catalogue - Erreur', $e->getMessage () . "\n\n" . $this->toString (), $this->image );
	}
	
	protected function sendMailDataset() {
		$fichePdf = fiche2pdf ( $this->dats_id, true );
		sendMail ( Portal_AdminGroup_Email, '[' . MainProject . '] Catalogue - Dataset ok', $this->toString (), array (
				$this->image,
				$fichePdf
		) );
	}


	public function isInsertedDataset() {
		$bd = new bdConnect;
		$query = "SELECT * FROM dats_data WHERE dats_id = ".$this->dats_id." LIMIT 1";
		return ($resultat = $bd->get_data($query));
	}

/*
	private function datasetTypeEquals($type){
		if (isset($this->dataset_types) && !empty($this->dataset_types)){
			$dtype = new dataset_type;
			$dtype = $dtype->getByType($type);
			for ($i = 0; $i < count($this->dataset_types); $i++) {
				if ($this->dataset_types[$i]->dats_type_id == $dtype->dats_type_id){
					//log_error('is '.$type.' dataset: true');
					return true;
				}
			}
		}
		//log_error('is '.$type.' dataset: false');
		return false;
	}*/
	
	public function set_requested($requested){
		if ($requested)	{
			$query = "update dataset set is_requested = true where dats_id = ".$this->dats_id;
		}else{
			$query = "update dataset set is_requested = null where dats_id = ".$this->dats_id;
		}
		$this->bdConn = new bdConnect;
		$this->bdConn->db_open();
		$this->bdConn->update($query);
		$this->bdConn->db_close();
	}


}
?>
