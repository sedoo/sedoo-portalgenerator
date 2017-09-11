<?php
/*
 * Created on 8 juil. 2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

	require_once("bd/base_dataset.php");
		
	class satellite_dataset extends base_dataset {
						
		var $dataType;
		
		//sites ne contient que la zone (geoCoverage)
		var $sats;
					
		/* ***** INIT ***** */
		
		//TODO tester que le type est bien SAT ? le forcer ?
		
		public function init($tab){
			$this->init_base_dataset($tab);
			$this->get_dats_sensors();
			
			$this->get_geoCoverage();
			$this->get_dataType();
			$this->get_sats();
			
			$this->get_sensor_vars();
						
			$this->init_cpt();
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
		
		private function get_dataType(){
			$query = "select * from place where place_id in (select place_id from dats_place where dats_id = ".$this->dats_id
			.") and place_level = 1";
			
			$place = new place;
			$places = $place->getByQuery($query);
			
			if (isset($places) && !empty($places)){
				$this->dataType =  $places[0];
			}
		}
		
		private function get_sats(){
			$this->sats = array();
						
			for ($i = 0; $i < count($this->dats_sensors); $i++){
				$this->dats_sensors[$i]->sensor->get_sensor_places();
				$place = new place;
				$this->sats[$i] = $place->getById($this->dats_sensors[$i]->sensor->sensor_places[0]->place->place_id);
			}
			$this->nbSites = count($this->sats);
		}
		
		/* ***** INSERT ***** */
				
		protected function insert_others(){
				$this->insert_dataType();
				$this->insert_sats();
				$this->insert_geoCoverage();
								
				$this->insert_sensor_places_sat();
		}
		
		protected function update_before_base(){
			if (isset($this->dats_sensors[0]->sensor->sensor_id) && !empty($this->dats_sensors[0]->sensor->sensor_id)){
				$this->bdConn->exec("delete from sensor_place where sensor_id = ".$this->dats_sensors[0]->sensor->sensor_id);
				for ($i = 0; $i < count($this->dats_variables); $i++){
					if (isset($this->dats_variables[$i]->variable->var_id) && !empty($this->dats_variables[$i]->variable->var_id)){
						$this->bdConn->exec("delete from sensor_var where var_id = ".$this->dats_variables[$i]->variable->var_id." and sensor_id = ".$this->dats_sensors[0]->sensor->sensor_id);
					}
				}
			}
		}
		
		protected function update_after_base(){
			$this->insert_dataType();
			$this->insert_sats();
			$this->insert_geoCoverage();
			
			$this->insert_sensor_places_sat();
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
		
		private function insert_sats(){
			for ($i = 0; $i < count($this->sats); $i++) {
				if ($this->sats[$i]->place_id == 0){
					$this->sats[$i]->insert($this->bdConn);
				}
		       	if ($this->sats[$i]->place_id != -1){
		       		$do = new dats_place();
					$do->dats_id = $this->dats_id;
					$do->place_id = $this->sats[$i]->place_id;
					$do->insert($this->bdConn);
				}else if ($this->sats[$i]->pla_place_id > 0){
					$do = new dats_place();
					$do->dats_id = $this->dats_id;
					$do->place_id = $this->sats[$i]->pla_place_id;
					$do->insert($this->bdConn);
				}
			}
		}
		
		private function insert_sensor_places_sat(){
			for ($i = 0; $i < count($this->sats); $i++){
				if ($this->sats[$i]->place_id != -1 && $this->dats_sensors[$i]->sensor->sensor_id != -1 ){
					$this->dats_sensors[$i]->sensor->sensor_places[0] = new sensor_place();
					$this->dats_sensors[$i]->sensor->sensor_places[0]->sensor_id = $this->dats_sensors[$i]->sensor->sensor_id;
					$this->dats_sensors[$i]->sensor->sensor_places[0]->place_id = $this->sats[$i]->place_id;
					$this->dats_sensors[$i]->sensor->sensor_places[0]->insert($this->bdConn);
				}
				 
			}
		}
		
		public function toString(){
			$result = $this->base_dataset_to_string();
			
			$result .= 'Data type: '.$this->dataType->place_name."\n";
			$result .= 'Area: '.$this->sites[0]->place_name."\n";
			
			
			for ($i = 0; $i < count($this->dats_sensors);$i++){
				$result .= $this->sats[$i]->toString()."\n";
				$result = '\nSat: '.$this->sats[$i]->place_name;
												
				if (isset($this->sensor->sensor_model) && !empty($this->sensor->sensor_model)){
					$result .= "\nInstru: ".$this->dats_sensors[$i]->sensor->sensor_model."\nGCMD: ";
				}
				
				if (isset($this->dats_sensors[$i]->sensor->gcmd_instrument_keyword) ){
					$result .= $this->dats_sensors[$i]->sensor->gcmd_instrument_keyword->gcmd_sensor_name;
				}else{
					$result .= $this->dats_sensors[$i]->sensor->gcmd_sensor_id;
				}
				
				if (isset($this->sensor->sensor_url) && !empty($this->sensor->sensor_url)){
					$result .= "\nURL: ".$this->dats_sensors[$i]->sensor->sensor_url."\n";
				}
				
			}
					 
			return $result;
		}
		
		//TODO affichage différent pour les requested dataset
		public function display($project_name){

			echo '<table style = \'page-break-inside: auto;\'><tr style = \'page-break-inside: avoid;\'><th colspan="4" align="center"><b>General information</b></th></tr>';
			echo "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Dataset name</b></td><td colspan='3'>".$this->dats_title."</td></tr>";
			echo "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Data type</b></td><td colspan='3'>".$this->dataType->place_name."</td></tr>";
			displayUtils::displayDOI($this->dats_doi);
			echo "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Created on</b></td><td colspan='3'>".$this->dats_pub_date."</td></tr>";
			if ($this->dats_version){
				echo "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Version</b></td><td colspan='3'>".$this->dats_version."</td></tr>";
			}
			if (isset($this->projects) && ! empty($this->projects)){
				echo "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Useful in the framework of</b></td><td colspan='3'>";
				foreach ($this->projects as $proj){
					echo $proj->toString()."<br>";
				}
				echo "</td></tr>";
			}
		
			echo "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Dataset Contact(s)</b></td><td colspan='3'>";
			displayUtils::displayContacts($this->dats_originators);
			echo '</td></tr>';
		
			displayUtils::displayDataAvailability($this,$project_name);
		
			if ($this->dats_purpose){
				echo "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Purpose</b></td><td colspan='3'>".$this->dats_purpose."</td></tr>";
			}
			if ($this->dats_reference){
				echo "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>References</b></td><td colspan='3'>".$this->dats_reference."</td></tr>";
			}
							
			if (isset($this->dats_sensors) && ! empty($this->dats_sensors)){
				echo '</td></tr><tr style = \'page-break-inside: avoid;\'><th colspan="4" align="center"><b>Instrument'.((count($this->dats_sensors) > 1)?'s':'').'</b></th></tr>';
				for ($i = 0; $i < count($this->dats_sensors); $i++){
					if (count($this->dats_sensors) > 1){
						echo '<tr style = \'page-break-inside: avoid;\'><td colspan="4" align="center"><b>Instrument '.($i+1).'</b></td></tr>';
					}
					$this->dats_sensors[$i]->sensor->get_sensor_places();
					echo "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Satellite</b></td><td colspan='3'>".$this->dats_sensors[$i]->sensor->sensor_places[0]->place->place_name."</td></tr>";
					echo "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Instrument</b></td><td colspan='3'>".$this->dats_sensors[$i]->sensor->sensor_model."</td></tr>";
					echo "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Instrument type</b></td><td colspan='3'>".$this->dats_sensors[$i]->sensor->gcmd_instrument_keyword->gcmd_sensor_name."</td></tr>";
					if (isset($this->dats_sensors[$i]->sensor->sensor_url) && !empty($this->dats_sensors[$i]->sensor->sensor_url)){
						echo "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Reference</b></td><td colspan='3'>".$this->dats_sensors[$i]->sensor->sensor_url."</td></tr>";
					}
				}
			}
				
			if (isset($this->dats_variables) && ! empty($this->dats_variables)){
				echo '</td></tr><tr style = \'page-break-inside: avoid;\'><th colspan="4" align="center"><b>Parameters</b></th></tr>';
				$cpt = 1;
				foreach($this->dats_variables as $dats_var){
					if (count($this->dats_variables) > 1){
						echo '<tr style = \'page-break-inside: avoid;\'><td colspan="4" align="center"><b>Parameter '.($cpt++).'</b></td></tr>';
					}
					displayUtils::displayParameter($dats_var,false,false,true);
				}
			}
				
			echo '</td></tr><tr style = \'page-break-inside: avoid;\'><th colspan="4" align="center"><b>Coverage</b></th></tr>';
			
			if ($this->dats_date_begin || $this->dats_date_end){
				echo '<tr style = \'page-break-inside: avoid;\'><td colspan="4" align="center"><b>Temporal Coverage</b></td></tr>';
				echo "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Date begin</b></td><td style = \'page-break-inside: avoid;\'>".$this->dats_date_begin."</td>";
				echo "<td style = \'page-break-inside: avoid;\'><b>Date end</b></td><td style = \'page-break-inside: avoid;\'>".$this->dats_date_end."</td></tr>";
			}
			echo '<tr style = \'page-break-inside: avoid;\'><td colspan="4" align="center"><b>Geographic Coverage</b></td></tr>';
			if ( isset($this->sites) && isset($this->sites[0]) && !empty($this->sites[0])){
				echo "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Area name</b></td><td colspan='3'>".$this->sites[0]->place_name."</td></tr>";
				displayUtils::displaySiteBoundings($this->sites[0]);
			}
		
			if ( !$this->is_requested){
				displayUtils::displayGrid($this->dats_sensors[0]);
					
				displayUtils::displayDataUse($this);
			}
			echo "</td></tr><td colspan=\"4\" align=\"center\"><input type=\"submit\" value=\"Update this dataset\" onclick=\"location.href='".$rubrique_cible."?datsId=".$this->dats_id."'\"/>";
			echo "</td></tr></table>";
		}
		
	
	public function getSatelliteDatasetInfos($project_name){
		    $dataset_infos = null;
			$rubrique_cible = "/$project_name/Satellite-Data";
			if ($this->is_requested){
				if ($project_name == 'HyMeX'){
					$rubrique_cible = "/HyMeX/Satellite-products-request";
				}else{
					$rubrique_cible = "/$project_name/Satellite-data-request";
				}
			}
				
			$dataset_infos .= '<table style = \'page-break-inside: auto;\'><tr style = \'page-break-inside: avoid;\'><th colspan="4" align="center"><b>General information</b></th></tr>';
			$dataset_infos .= "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Dataset name</b></td><td colspan='3'>".$this->dats_title."</td></tr>";
			$dataset_infos .= "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Data type</b></td><td colspan='3'>".$this->dataType->place_name."</td></tr>";
			$dataset_infos .= displayUtils::getDOI($this->dats_doi);
			$dataset_infos .= "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Created on</b></td><td colspan='3'>".$this->dats_pub_date."</td></tr>";
			if ($this->dats_version){
				$dataset_infos .= "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Version</b></td><td colspan='3'>".$this->dats_version."</td></tr>";
			}
			if (isset($this->projects) && ! empty($this->projects)){
				$dataset_infos .= "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Useful in the framework of</b></td><td colspan='3'>";
				foreach ($this->projects as $proj){
					$dataset_infos .= $proj->toString()."<br>";
				}
				$dataset_infos .= "</td></tr>";
			}
		
			$dataset_infos .= "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Dataset Contact(s)</b></td><td colspan='3'>";
			$dataset_infos .= displayUtils::getContacts($this->dats_originators);
			$dataset_infos .= '</td></tr>';
		
			$dataset_infos .= displayUtils::getDataAvailability($this,$project_name);
		
			if ($this->dats_purpose){
				$dataset_infos .= "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Purpose</b></td><td colspan='3'>".$this->dats_purpose."</td></tr>";
			}
			if ($this->dats_reference){
				$dataset_infos .= "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>References</b></td><td colspan='3'>".$this->dats_reference."</td></tr>";
			}
							
			if (isset($this->dats_sensors) && ! empty($this->dats_sensors)){
				$dataset_infos .= '</td></tr><tr style = \'page-break-inside: avoid;\'><th colspan="4" align="center"><b>Instrument'.((count($this->dats_sensors) > 1)?'s':'').'</b></th></tr>';
				for ($i = 0; $i < count($this->dats_sensors); $i++){
					if (count($this->dats_sensors) > 1){
						$dataset_infos .= '<tr style = \'page-break-inside: avoid;\'><td colspan="4" align="center"><b>Instrument '.($i+1).'</b></td></tr>';
					}
					$this->dats_sensors[$i]->sensor->get_sensor_places();
					$dataset_infos .= "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Satellite</b></td><td colspan='3'>".$this->dats_sensors[$i]->sensor->sensor_places[0]->place->place_name."</td></tr>";
					$dataset_infos .= "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Instrument</b></td><td colspan='3'>".$this->dats_sensors[$i]->sensor->sensor_model."</td></tr>";
					$dataset_infos .= "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Instrument type</b></td><td colspan='3'>".$this->dats_sensors[$i]->sensor->gcmd_instrument_keyword->gcmd_sensor_name."</td></tr>";
					if (isset($this->dats_sensors[$i]->sensor->sensor_url) && !empty($this->dats_sensors[$i]->sensor->sensor_url)){
						$dataset_infos .= "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Reference</b></td><td colspan='3'>".$this->dats_sensors[$i]->sensor->sensor_url."</td></tr>";
					}
				}
			}
				
			if (isset($this->dats_variables) && ! empty($this->dats_variables)){
				$dataset_infos .= '</td></tr><tr style = \'page-break-inside: avoid;\'><th colspan="4" align="center"><b>Parameters</b></th></tr>';
				$cpt = 1;
				foreach($this->dats_variables as $dats_var){
					if (count($this->dats_variables) > 1){
						$dataset_infos .= '<tr style = \'page-break-inside: avoid;\'><td colspan="4" align="center"><b>Parameter '.($cpt++).'</b></td></tr>';
					}
					$dataset_infos .= displayUtils::getParameter($dats_var,false,false,true);
				}
			}
				
			$dataset_infos .= '</td></tr><tr style = \'page-break-inside: avoid;\'><th colspan="4" align="center"><b>Coverage</b></th></tr>';
			
			if ($this->dats_date_begin || $this->dats_date_end){
				$dataset_infos .= '<tr style = \'page-break-inside: avoid;\'><td colspan="4" align="center"><b>Temporal Coverage</b></td></tr>';
				$dataset_infos .= "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Date begin</b></td><td style = \'page-break-inside: avoid;\'>".$this->dats_date_begin."</td>";
				$dataset_infos .= "<td style = \'page-break-inside: avoid;\'><b>Date end</b></td><td style = \'page-break-inside: avoid;\'>".$this->dats_date_end."</td></tr>";
			}
			$dataset_infos .= '<tr style = \'page-break-inside: avoid;\'><td colspan="4" align="center"><b>Geographic Coverage</b></td></tr>';
			if ( isset($this->sites) && isset($this->sites[0]) && !empty($this->sites[0])){
				$dataset_infos .= "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Area name</b></td><td colspan='3'>".$this->sites[0]->place_name."</td></tr>";
				$dataset_infos .= displayUtils::getSiteBoundings($this->sites[0]);
			}
		
			if ( !$this->is_requested){
				$dataset_infos .= displayUtils::getGrid($this->dats_sensors[0]);
					
				$dataset_infos .= displayUtils::getDataUse($this);
			}
			$dataset_infos .= "</td></tr></tr></table>";
			
			return $dataset_infos;
		}
	}
            
?>
