<?php 

class multi_instru_dataset extends base_dataset {

	public function init($tab){
		$this->init_base_dataset($tab);

		//TODO passer dans base ?
		$this->get_dats_sensors();
		$this->get_site();
		$this->get_sensor_vars();

		$this->init_cpt();
	}

	private function get_site(){
		$query = "SELECT place.* FROM dats_place JOIN place USING (place_id) WHERE dats_id = ".$this->dats_id;
			
		$place = new place;
		$places = $place->getByQuery($query);
			
		if (isset($places) && !empty($places)){
			$this->sites[0] = $places[0];
		}
	}

	protected function insert_others(){
		$this->insert_site();

		//TODO sensor_place ?

	}
	protected function update_before_base(){

	}

	protected function update_after_base(){
		$this->insert_site();

		//TODO sensor_place ?

	}

	private function insert_site(){
		if ($this->sites[0]->place_id == 0){
			$this->sites[0]->insert($this->bdConn);
		}
		if ($this->sites[0]->place_id != -1){
			$do = new dats_place();
			$do->dats_id = $this->dats_id;
			$do->place_id = $this->sites[0]->place_id;
			$do->insert($this->bdConn);
		}
	}

	public function toString(){
		$result = $this->base_dataset_to_string();

	}

	public function display($project_name){
		$rubrique_cible = "/$project_name/In-Situ-Site-Registration";

		echo '<table><tr><th colspan="4" align="center"><b>General information</b></th></tr>';
		echo "<tr><td><b>Dataset name</b></td><td colspan='3'>".$this->dats_title."</td></tr>";
		displayUtils::displayDOI($this->dats_doi);
		echo "<tr><td><b>Created on</b></td><td colspan='3'>".$this->dats_pub_date."</td></tr>";

		displayUtils::displayProjects($this->projects);

		echo "<tr><td><b>Period</b></td><td colspan='3'>".$this->period->period_name."</td></tr>";
		echo "<tr><td><b>Date begin</b></td><td>".$this->dats_date_begin."</td>";
		echo "<td><b>Date end</b></td><td>".(($this->dats_date_end_not_planned)?'not planned':$this->dats_date_end)."</td></tr>";

		echo "<tr><td><b>Contact(s)</b></td><td colspan='3'>";
		displayUtils::displayContacts($this->dats_originators);
		echo '</td></tr>';

		displayUtils::displayDataAvailability($this,$project_name);

		echo '</td></tr><tr><th colspan="4" align="center"><b>Site description</b></th></tr>';
		echo "<tr><td><b>Site name</b></td><td colspan='3'>".$this->sites[0]->place_name."</td></tr>";
		echo "<tr><td><b>Plateform type</b></td><td colspan='3'>".$this->sites[0]->gcmd_plateform_keyword->gcmd_plat_name."</td></tr>";
		if (isset($this->sites[0]->parent_place) && !empty($this->sites[0]->parent_place)){
			echo "<tr><td><b>Predefined site</b></td><td colspan='3'>".printPredefinedSite($this->sites[0]->parent_place)."</td></tr>";
			echo "<tr><td><b>Site type</b></td><td colspan='3'>".$this->sites[0]->parent_place->gcmd_plateform_keyword->gcmd_plat_name."</td></tr>";
		}
		if ($this->dats_abstract){
			echo "<tr><td><b>Abstract</b></td><td colspan='3'>".tmp_format_abstract($this->dats_abstract)."</td></tr>";
		}
		if ($this->dats_purpose){
			echo "<tr><td><b>Observing strategy</b></td><td colspan='3'>".tmp_format_abstract($this->dats_purpose)."</td></tr>";
		}
		if ($this->dats_reference){
			echo "<tr><td><b>References</b></td><td colspan='3'>".$this->dats_reference."</td></tr>";
		}

		displayUtils::displaySiteBoundings($this->sites[0]);

		//Carte
		$mapForm = new map_form();
		$url = new url();
		$map = $url->getMapFileByDataset($this->dats_id);
		$mapUrl = null;
		if (isset($map) && !empty($map)){
			$mapUrl =$map[0]->url;
		}

		if ($mapForm->genScriptFromSite($this->sites[0], $mapUrl)){
			echo '<tr><td colspan="4">';
			$mapForm->displayDrawLink('View site location on a map');
			$mapForm->displayMapDiv();
			echo '</td></tr>';
		}

		if (isset($this->image) && !empty($this->image)){
			echo "<tr><td><b>Photo</b></td>";
			echo '<td><a href="'.$this->image.'" target=_blank><img src="'.$this->image.'" width="50" /></a></td><td colspan="2">';
		}

		echo '</td></tr><tr><th colspan="4" align="center"><b>Instrument information</b></th></tr>';
		for ($i = 0; $i < count($this->dats_sensors);$i++){
			$nb = $i+1;
			echo '</td></tr><tr><th colspan="4" align="center"><b>Instrument '.$nb.'</b></th></tr>';
			if (isset($this->dats_sensors[$i]->sensor->gcmd_instrument_keyword)){
				echo "<tr><td><b>Instrument type</b></td><td colspan='3'>".$this->dats_sensors[$i]->sensor->gcmd_instrument_keyword->gcmd_sensor_name."</td></tr>";
			}
			if (isset($this->dats_sensors[$i]->sensor->manufacturer)){
				echo "<tr><td><b>Manufacturer</b></td><td colspan='3'>".$this->dats_sensors[$i]->sensor->manufacturer->manufacturer_name;
			}
			if (isset($this->dats_sensors[$i]->sensor->manufacturer->manufacturer_url) && !empty($this->dats_sensors[$i]->sensor->manufacturer->manufacturer_url) ){
				echo " - <a href=\"".$this->dats_sensors[$i]->sensor->manufacturer->manufacturer_url."\" >".$this->dats_sensors[$i]->sensor->manufacturer->manufacturer_url."</a>";
			}else{
				echo "</td></tr>";
			}
			echo "<tr><td><b>Model</b></td><td colspan='3'>".$this->dats_sensors[$i]->sensor->sensor_model."</td></tr>";
			if($this->dats_sensors[$i]->sensor->sensor_url){
				echo "<tr><td><b>Reference</b></td><td colspan='3'>".$this->dats_sensors[$i]->sensor->sensor_url."</td></tr>";
			}
			if($this->dats_sensors[$i]->sensor->sensor_calibration){
				echo "<tr><td><b>Instrument features / Calibration</b></td><td colspan='3'>".$this->dats_sensors[$i]->sensor->sensor_calibration."</td></tr>";
			}
			displayUtils::displaySensorResolution($this->dats_sensors[$i]);
			if (isset($this->dats_sensors[$i]->sensor->boundings)){
				echo "<tr><td><b>Longitude (°)</b></td><td>".$this->dats_sensors[$i]->sensor->boundings->west_bounding_coord."</td><td><b>Latitude (°)</b></td><td>".$this->dats_sensors[$i]->sensor->boundings->north_bounding_coord."</td></tr>";
			}
			if ($this->dats_sensors[$i]->sensor->sensor_elevation || $this->dats_sensors[$i]->sensor->sensor_height){
				echo "<tr><td><b>Sensor altitude (m)</b></td><td>".$this->dats_sensors[$i]->sensor->sensor_elevation."</td>";
				echo "<td><b>Height above ground (m)</b></td><td>".$this->dats_sensors[$i]->sensor->sensor_height."</td></tr>";
			}
			if ($this->dats_sensors[$i]->sensor->sensor_environment){
				echo "<tr><td><b>Instrument environment</b></td><td colspan='3'>".$this->dats_sensors[$i]->sensor->sensor_environment."</td></tr>";
			}

			
			$cpt = 1;
			foreach($this->dats_sensors[$i]->sensor->sensor_vars as $sensor_var)	{
				if ($sensor_var->flag_param_calcule != 1){
					echo '<tr><td colspan="4" align="center"><b>Instrument '.$nb.', parameter '.($cpt++).'</b></td></tr>';
					displayUtils::displayParameterFromSensorVar($sensor_var);
				}
			}
		}

		displayUtils::displayDataUse($this, false);

		echo "</td></tr><td colspan=\"4\" align=\"center\"><input type=\"submit\" value=\"Update this dataset\" onclick=\"location.href='".$rubrique_cible."?datsId=".$this->dats_id."'\"/>";
		echo "</td></tr></table>";

	}

}


?>