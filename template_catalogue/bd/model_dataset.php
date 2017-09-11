<?php

require_once ("bd/base_dataset.php");

class model_dataset extends base_dataset {

	const GCMD_CATEG = "Models/Analyses";
	
	var $dataType;

	//sites ne contient que la zone (geoCoverage)
	var $model;
	
	//Simulation : dats_sensor[0]
		
	public function init($tab){
		$this->init_base_dataset($tab);
			
		$this->get_dats_sensors();
			
		$this->get_geoCoverage();
		$this->get_dataType();
		$this->get_model();
			
		$this->get_sensor_vars();
				
		$this->init_cpt();
	}
	
	private function get_dataType(){
		$query = "select * from place where place_id in (select place_id from dats_place where dats_id = ".$this->dats_id
		.") and place_level >= 1";
			
		$place = new place;
		$places = $place->getByQuery($query);
			
		if (isset($places) && !empty($places)){
			$this->dataType =  $places[0];
		}
	}
	
	private function get_geoCoverage(){
		$query = "select * from place where place_id in (select place_id from dats_place where dats_id = ".$this->dats_id
		.") and gcmd_plat_id in (select gcmd_plat_id from gcmd_plateform_keyword where gcmd_plat_name ilike 'Geographic Regions')";
			
		$place = new place;
		$places = $place->getByQuery($query);
			
		if (isset($places) && !empty($places)){
			$this->sites[0] = $places[0];
		}
	}
	
	private function get_model(){
		$query = 'SELECT place.* FROM dats_place JOIN place USING (place_id) JOIN gcmd_plateform_keyword USING (gcmd_plat_id)' 
						.'WHERE dats_id = '.$this->dats_id." AND gcmd_plat_name = '".self::GCMD_CATEG."' AND place_level is null";
		
		$place = new place;
		$places = $place->getByQuery($query);
			
		if (isset($places) && !empty($places)){
			$this->model = $places[0];
		}
	}
	
	/* ***** INSERT ***** */
	
	protected function insert_others(){
		$this->insert_dataType();
		$this->insert_model();
		$this->insert_geoCoverage();
	
		$this->insert_sensor_places_mod();
	}
	
	protected function update_before_base(){
		
	}
	
	protected function update_after_base(){
		$this->insert_dataType();
		$this->insert_model();
		$this->insert_geoCoverage();
			
		$this->insert_sensor_places_mod();
	}
	
	private function insert_dataType(){
		$do = new dats_place();
		$do->dats_id = $this->dats_id;
		$do->place_id = $this->dataType->place_id;
		$do->insert($this->bdConn);
	}
	
	private function insert_geoCoverage(){
		if ($this->sites[0]->place_id == 0){
			$this->sites[0]->insert($this->bdConn);
		}
			
		$do = new dats_place();
		$do->dats_id = $this->dats_id;
		$do->place_id = $this->sites[0]->place_id;
		$do->insert($this->bdConn);
	}
	
	private function insert_model(){
		if ($this->model->place_id == 0){
			
			$this->model->gcmd_plateform_keyword = new gcmd_plateform_keyword;
			$this->model->gcmd_plateform_keyword = $this->model->gcmd_plateform_keyword->getByName(self::GCMD_CATEG);
			$this->model->gcmd_plat_id = & $this->model->gcmd_plateform_keyword->gcmd_plat_id;
			$this->model->insert($this->bdConn);
		}
		if ($this->model->place_id != -1){
			$do = new dats_place();
			$do->dats_id = $this->dats_id;
			$do->place_id = $this->model->place_id;
			$do->insert($this->bdConn);
		}
	}
	
	private function insert_sensor_places_mod(){
		if ($this->model->place_id != -1 && $this->dats_sensors[0]->sensor->sensor_id != -1 ){
			$this->dats_sensors[0]->sensor->sensor_places[0] = new sensor_place();
			$this->dats_sensors[0]->sensor->sensor_places[0]->sensor_id = $this->dats_sensors[0]->sensor->sensor_id;
			$this->dats_sensors[0]->sensor->sensor_places[0]->place_id = $this->model->place_id;
			$this->dats_sensors[0]->sensor->sensor_places[0]->insert($this->bdConn);
		}
	}
	
	public function toString(){
		$result = $this->base_dataset_to_string();
			
		$result .= 'Data type: '.$this->dataType->place_name."\n";
		$result .= 'Area: '.$this->sites[0]->place_name."\n";
			
			
		$result .= $this->model->toString()."\n";
		$result = '\nModel: '.$this->model->place_name;
		$result .= "\nSimu: ".$this->dats_sensors[0]->sensor->sensor_model."\nGCMD: ";				
		$result .= "\nURL: ".$this->dats_sensors[0]->sensor->sensor_url."\n";
	
		return $result;
	}
	
	public function display($project_name){
		$rubrique_cible = "/$project_name/Model-Data";
		if ($this->is_requested){
			if ($project_name == 'HyMeX'){
				$rubrique_cible = "/HyMeX/Model-outputs-request";
			}else{
				$rubrique_cible = "/$project_name/Model-Data-Request";
			}
		}
		
		echo '<table><tr><th colspan="4" align="center"><b>General information</b></th></tr>';
		echo "<tr><td><b>Dataset name</b></td><td colspan='3'>".$this->dats_title."</td></tr>";
		displayUtils::displayDOI($this->dats_doi);
		echo "<tr><td><b>Created on</b></td><td colspan='3'>".$this->dats_pub_date."</td></tr>";
		if ($this->dats_version){
			echo "<tr><td><b>Version</b></td><td colspan='3'>".$this->dats_version."</td></tr>";
		}
		
		displayUtils::displayProjects($this->projects);
		
		echo "<tr><td><b>Dataset Contact(s)</b></td><td colspan='3'>";
		displayUtils::displayContacts($this->dats_originators);
		echo '</td></tr>';
		
		displayUtils::displayDataAvailability($this,$project_name);
		
		echo '</td></tr><tr><th colspan="4" align="center"><b>Model information</b></th></tr>';
		echo "<tr><td><b>Type</b></td><td colspan='3'>".$this->dataType->place_name."</td></tr>";
		echo "<tr><td><b>Model</b></td><td colspan='3'>".$this->model->place_name."</td></tr>";
		echo "<tr><td><b>Simulation</b></td><td colspan='3'>".$this->dats_sensors[0]->sensor->sensor_model."</td></tr>";
		
		if ($this->dats_abstract){
			echo "<tr><td><b>Model / simulation description</b></td><td colspan='3'>".$this->dats_abstract."</td></tr>";
		}
		if ($this->dats_purpose){
			echo "<tr><td><b>Purpose</b></td><td colspan='3'>".$this->dats_purpose."</td></tr>";
		}
		if ($this->dats_reference){
			echo "<tr><td><b>References</b></td><td colspan='3'>".$this->dats_reference."</td></tr>";
		}
		
		if (isset($this->attFile) && !empty($this->attFile)){
			echo "<tr><td><b>Attached document</b></td><td colspan='3'>";
			echo "<a href='/downAttFile.php?file=".$this->attFile."' >".$this->attFile."</a>";
			echo "</td></tr>";
		}

		if (isset($this->dats_variables) && ! empty($this->dats_variables)){
			echo '</td></tr><tr><th colspan="4" align="center"><b>Parameters</b></th></tr>';
			$cpt = 1;
			foreach($this->dats_variables as $dats_var){
				if (count($this->dats_variables) > 1){
					echo '<tr><td colspan="4" align="center"><b>Parameter '.($cpt++).'</b></td></tr>';
				}
				displayUtils::displayParameter($dats_var,false,false,true);
			}
		}
		
		echo '</td></tr><tr><th colspan="4" align="center"><b>Coverage</b></th></tr>';
			
		if ($this->dats_date_begin || $this->dats_date_end){
			echo '<tr><td colspan="4" align="center"><b>Temporal Coverage</b></td></tr>';
			echo "<tr><td><b>Date begin</b></td><td>".$this->dats_date_begin."</td>";
			echo "<td><b>Date end</b></td><td>".$this->dats_date_end."</td></tr>";
		}
			
		echo '<tr><td colspan="4" align="center"><b>Geographic Coverage</b></td></tr>';
		if ( isset($this->sites) && isset($this->sites[0]) && !empty($this->sites[0])){
			echo "<tr><td><b>Area name</b></td><td colspan='3'>".$this->sites[0]->place_name."</td></tr>";
			displayUtils::displaySiteBoundings($this->sites[0]);
		}
		
		if ( !$this->is_requested){
			displayUtils::displayGrid($this->dats_sensors[0]);
				
			displayUtils::displayDataUse($this);
		}
		
		echo "</td></tr><td colspan=\"4\" align=\"center\"><input type=\"submit\" value=\"Update this dataset\" onclick=\"location.href='".$rubrique_cible."?datsId=".$this->dats_id."'\"/>";
		echo "</td></tr></table>";
	}

}


?>