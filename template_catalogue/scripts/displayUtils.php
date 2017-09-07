<?php

require_once("scripts/lstDataUtils.php");

class displayUtils{
	
	public static function displayContacts(& $pis){
		foreach ($pis as $pi){
			if ($pi->personne->pers_email_1){
				$mail = explode('@',strtolower($pi->personne->pers_email_1));
				$mail2 = explode('.',$mail[1]);
				$i = strrpos($mail[1],'.');
				$tld = substr($mail[1],$i+1);
				$d = substr($mail[1],0,$i);
				$label = ucwords(strtolower($pi->personne->pers_name)).' - '.$pi->personne->organism->getName().' ('.$pi->contact_type->contact_type_name.')';

				$tldIds = array(
						'com' => 0,
						'org' => 1,
						'net' => 2,
						'ws' => 3,
						'info' => 4,
						'int' => 5,
						'edu' => 6,
						'gov' => 7,
						'uk' => 10,
						'fr' => 14,
						'es' => 15,
						'de' => 16,
						'at' => 17,
						'it' => 18,
						'cat' => 19,
						'ch' => 20,
						'hr' => 21,
						'ro' => 22,
						'il' => 23,
						'nl' => 24,
						'gr' => 25);

				if (array_key_exists($tld,$tldIds)){
					$tldId = $tldIds[$tld];
					echo '<script>mail2("'.$mail[0].'","'.$d.'",'.$tldId.',"","'.$label.'")</script><BR/>';
				}else{
					echo "<a href='mailto:".$pi->personne->pers_email_1."'>";
					echo $label;
					echo "</a><BR/>";
				}
			}
		}
	}
	
	public static function getContacts(& $pis){
		$infos = null;
		foreach ($pis as $pi){
			if ($pi->personne->pers_email_1){
				$mail = explode('@',strtolower($pi->personne->pers_email_1));
				$mail2 = explode('.',$mail[1]);
				$i = strrpos($mail[1],'.');
				$tld = substr($mail[1],$i+1);
				$d = substr($mail[1],0,$i);
				$label = ucwords(strtolower($pi->personne->pers_name)).' - '.$pi->personne->organism->getName().' ('.$pi->contact_type->contact_type_name.')';
	
				$tldIds = array(
						'com' => 0,
						'org' => 1,
						'net' => 2,
						'ws' => 3,
						'info' => 4,
						'int' => 5,
						'edu' => 6,
						'gov' => 7,
						'uk' => 10,
						'fr' => 14,
						'es' => 15,
						'de' => 16,
						'at' => 17,
						'it' => 18,
						'cat' => 19,
						'ch' => 20,
						'hr' => 21,
						'ro' => 22,
						'il' => 23,
						'nl' => 24,
						'gr' => 25);
	
				if (array_key_exists($tld,$tldIds)){
					$tldId = $tldIds[$tld];
					$infos .= '<script>mail2("'.$mail[0].'","'.$d.'",'.$tldId.',"","'.$label.'")</script><BR/>';
					$infos .= $label;
				}else{
					$infos .= "<a href='mailto:".$pi->personne->pers_email_1."'>";
					$infos .= $label;
					$infos .= "</a><BR/>";
				}
			}
		}
		return $infos;
	}
	
	public static function displayDOI($doi){
		if (isset($doi) && !empty($doi)){
			$f = fopen(DATACITE_CITATION.$doi,'r');
			if ($f){
				$cit = fgets($f);
			}else{
				$cit = "doi:$doi";
			}
			echo "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Dataset DOI</b></td><td colspan='3'><a href='".DATACITE_WEB.$doi."' target='_blank'>$doi</a>";
			echo '<a class="lightblue_tag" href="'.DATACITE_CITATION.$doi.'" style="color: white;margin-right:0px;" title="How to cite">Citation</a>';
			echo '<a class="lightblue_tag" href="'.DATACITE_BIBTEX.$doi.'" style="color: white;" title="Export to BibTeX">BibTeX</a>';
			echo "</td></tr>";
		}
	}
	
	public static function getDOI($doi){
		$infos = null;
		if (isset($doi) && !empty($doi)){
			$f = fopen(DATACITE_CITATION.$doi,'r');
			if ($f){
				$cit = fgets($f);
			}else{
				$cit = "doi:$doi";
			}
			$infos .= "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Dataset DOI</b></td><td colspan='3'><a href='".DATACITE_WEB.$doi."' target='_blank'>$doi</a>";
			$infos .= '<a class="lightblue_tag" href="'.DATACITE_CITATION.$doi.'" style="color: white;margin-right:0px;" title="How to cite">Citation</a>';
			$infos .= '<a class="lightblue_tag" href="'.DATACITE_BIBTEX.$doi.'" style="color: white;" title="Export to BibTeX">BibTeX</a>';
			$infos .= "</td></tr>";
		}
		return $infos;
	}
	
	public static function displayParameter(& $dats_var,$withPrecision = true, $withDates = true, $withLevelType = false){
		if (isset($dats_var->variable->var_name) && !empty($dats_var->variable->var_name))
			echo "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Parameter name</b></td><td colspan='3'>".$dats_var->variable->var_name."</td></tr>";
		else
			echo "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Parameter name</b></td><td colspan='3'>".$dats_var->variable->gcmd->gcmd_name."</td></tr>";
		echo "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Parameter keyword</b></td><td colspan='3'>".printGcmdScience($dats_var->variable->gcmd)."</td></tr>";
		echo "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Unit</b></td><td colspan='3'>".((isset($dats_var->unit)  && !empty($dats_var->unit))?$dats_var->unit->toString():"")."</td></tr>";
		if ($dats_var->methode_acq){
			echo "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Acquisition methodology and quality</b></td><td colspan='3'>".$dats_var->methode_acq."</td></tr>";
		}
		if ($withDates){
			echo "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Date begin</b></td><td style = \'page-break-inside: avoid;\'>".$dats_var->date_min."</td>";
			echo "<td style = \'page-break-inside: avoid;\'><b>Date end</b></td><td style = \'page-break-inside: avoid;\'>".$dats_var->date_max."</td></tr>";
		}
		if ($withPrecision && $dats_var->variable->sensor_precision){
			echo "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Sensor precision / incertainty</b></td><td colspan='3'>".$dats_var->variable->sensor_precision."</td></tr>";
		}
		if ($withLevelType && $dats_var->level_type){
			echo "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Vertical level type</b></td><td colspan='3'>".$dats_var->level_type."</td></tr>";

		}
	}
	
	public static function getParameter(& $dats_var,$withPrecision = true, $withDates = true, $withLevelType = false){
		$infos = null;
		if (isset($dats_var->variable->var_name) && !empty($dats_var->variable->var_name))
			$infos .= "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Parameter name</b></td><td colspan='3'>".$dats_var->variable->var_name."</td></tr>";
		else
			$infos .= "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Parameter name</b></td><td colspan='3'>".$dats_var->variable->gcmd->gcmd_name."</td></tr>";
		$infos .= "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Parameter keyword</b></td><td colspan='3'>".printGcmdScience($dats_var->variable->gcmd)."</td></tr>";
		$infos .= "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Unit</b></td><td colspan='3'>".((isset($dats_var->unit)  && !empty($dats_var->unit))?$dats_var->unit->toString():"")."</td></tr>";
		if ($dats_var->methode_acq){
			$infos .= "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Acquisition methodology and quality</b></td><td colspan='3'>".$dats_var->methode_acq."</td></tr>";
		}
		if ($withDates){
			$infos .= "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Date begin</b></td><td style = \'page-break-inside: avoid;\'>".$dats_var->date_min."</td>";
			$infos .= "<td style = \'page-break-inside: avoid;\'><b>Date end</b></td><td style = \'page-break-inside: avoid;\'>".$dats_var->date_max."</td></tr>";
		}
		if ($withPrecision && $dats_var->variable->sensor_precision){
			$infos .= "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Sensor precision / incertainty</b></td><td colspan='3'>".$dats_var->variable->sensor_precision."</td></tr>";
		}
		if ($withLevelType && $dats_var->level_type){
			$infos .= "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Vertical level type</b></td><td colspan='3'>".$dats_var->level_type."</td></tr>";
		}
		return $infos;
	}

	public static function displayProjects(& $projects){
		if (isset($projects) && ! empty($projects)){
			echo "<tr><td><b>Useful in the framework of</b></td><td colspan='3'>";
			foreach ($projects as $proj){
				echo $proj->toString()."<br>";
			}
			echo "</td></tr>";
		}
	}

	public static function displaySiteBoundings(& $site){
		if ( (isset($site->west_bounding_coord) && strlen($site->west_bounding_coord) > 0)
				|| (isset($site->east_bounding_coord) && strlen($site->east_bounding_coord))
				|| (isset($site->north_bounding_coord) && strlen($site->north_bounding_coord))
				|| (isset($site->south_bounding_coord) && strlen($site->south_bounding_coord))
		){
			echo "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>West bounding coordinate (°)</b></td><td style = \'page-break-inside: avoid;\'>".$site->west_bounding_coord."</td>";
			echo "<td style = \'page-break-inside: avoid;\'><b>East bounding coordinate (°)</b></td><td style = \'page-break-inside: avoid;\'>".$site->east_bounding_coord."</td></tr>";
			echo "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>North bounding coordinate (°)</b></td><td style = \'page-break-inside: avoid;\'>".$site->north_bounding_coord."</td>";
			echo "<td style = \'page-break-inside: avoid;\'><b>South bounding coordinate (°)</b></td><td style = \'page-break-inside: avoid;\'>".$site->south_bounding_coord."</td></tr>";
		}
		if ( (isset($site->place_elevation_min) && strlen($site->place_elevation_min) > 0)
				|| (isset($site->place_elevation_max) && strlen($site->place_elevation_max) > 0)
		){
			echo "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Altitude min</b></td><td style = \'page-break-inside: avoid;\'>".$site->place_elevation_min."</td>";
			echo "<td style = \'page-break-inside: avoid;\'><b>Altitude max</b></td><td style = \'page-break-inside: avoid;\'>".$site->place_elevation_max."</td></tr>";
		}
	}
	
	public static function getSiteBoundings(& $site){
		$infos = null;
		if ( (isset($site->west_bounding_coord) && strlen($site->west_bounding_coord) > 0)
		|| (isset($site->east_bounding_coord) && strlen($site->east_bounding_coord))
		|| (isset($site->north_bounding_coord) && strlen($site->north_bounding_coord))
		|| (isset($site->south_bounding_coord) && strlen($site->south_bounding_coord))
		){
			$infos .= "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>West bounding coordinate (°)</b></td><td style = \'page-break-inside: avoid;\'>".$site->west_bounding_coord."</td>";
			$infos .= "<td style = \'page-break-inside: avoid;\'><b>East bounding coordinate (°)</b></td><td style = \'page-break-inside: avoid;\'>".$site->east_bounding_coord."</td></tr>";
			$infos .= "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>North bounding coordinate (°)</b></td><td style = \'page-break-inside: avoid;\'>".$site->north_bounding_coord."</td>";
			$infos .= "<td style = \'page-break-inside: avoid;\'><b>South bounding coordinate (°)</b></td><td style = \'page-break-inside: avoid;\'>".$site->south_bounding_coord."</td></tr>";
		}
		if ( (isset($site->place_elevation_min) && strlen($site->place_elevation_min) > 0)
		|| (isset($site->place_elevation_max) && strlen($site->place_elevation_max) > 0)
		){
			$infos .= "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Altitude min</b></td><td style = \'page-break-inside: avoid;\'>".$site->place_elevation_min."</td>";
			$infos .= "<td style = \'page-break-inside: avoid;\'><b>Altitude max</b></td><td style = \'page-break-inside: avoid;\'>".$site->place_elevation_max."</td></tr>";
		}
		return $infos;
	}
	
	public static function displayGrid(& $ds){
		self::displaySensorResolution($ds, true);
		if ($ds->grid_original || $ds->grid_process){
			echo '<tr style = \'page-break-inside: avoid;\'><td colspan="4" align="center"><b>Grid type</b></td></tr>';
		}
		if ($ds->grid_original){
			echo "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Original Grid type</b></td><td colspan='3'>".$ds->grid_original."</td></tr>";
		}
		if ($ds->grid_process){
			echo "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Grid processing</b></td><td colspan='3'>".$ds->grid_process."</td></tr>";
		}
	
	}
	
	public static function getGrid(& $ds){
		$infos = null;
		$infos .= self::getSensorResolution($ds, true);
		if ($ds->grid_original || $ds->grid_process){
			$infos .= '<tr style = \'page-break-inside: avoid;\'><td colspan="4" align="center"><b>Grid type</b></td></tr>';
		}
		if ($ds->grid_original){
			$infos .= "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Original Grid type</b></td><td colspan='3'>".$ds->grid_original."</td></tr>";
		}
		if ($ds->grid_process){
			$infos .= "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Grid processing</b></td><td colspan='3'>".$ds->grid_process."</td></tr>";
		}
		return $infos;
	}
	
	//Renvoie true si qqch a été écrit
	public static function displaySensorResolution(& $ds, $isGrid=false){
		if ($isGrid){
			if ($ds->sensor_resol_temp
					|| $ds->sensor_lat_resolution
					|| $ds->sensor_lon_resolution
					|| $ds->sensor_vert_resolution){
				echo '<tr style = \'page-break-inside: avoid;\'><td colspan="4" align="center"><b>Data resolution</b></td></tr>';
			}
			if ($ds->sensor_resol_temp){
				echo "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Temporal resolution</b></td><td colspan='3'>".$ds->sensor_resol_temp."</td></tr>";
			}
			if ($ds->sensor_lat_resolution){
				echo "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Latitude resolution</b></td><td colspan='3'>".$ds->sensor_lat_resolution."</td></tr>";
			}
			if ($ds->sensor_lon_resolution){
				echo "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Longitude resolution</b></td><td colspan='3'>".$ds->sensor_lon_resolution."</td></tr>";
			}
			if ($ds->sensor_vert_resolution){
				echo "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Vertical resolution</b></td><td colspan='3'>".$ds->sensor_vert_resolution."</td></tr>";
			}
			return true;
		}else{
			$infoTrouve = false;
			if (isset($ds->sensor_resol_temp)){
				echo "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Observation frequency</b></td><td colspan='3'>".$ds->sensor_resol_temp."</td></tr>";
				$infoTrouve = true;
			}
			if (isset($ds->sensor_lat_resolution)){
				echo "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Horizontal coverage</b></td><td colspan='3'>".$ds->sensor_lat_resolution."</td></tr>";
				$infoTrouve = true;
			}
			if (isset($ds->sensor_vert_resolution)){
				echo "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Vertical coverage</b></td><td colspan='3'>".$ds->sensor_vert_resolution."</td></tr>";
				$infoTrouve = true;
			}
			return $infoTrouve;
		}
	}
	
	public static function getSensorResolution(& $ds, $isGrid=false){
		$infos = null;
		if ($isGrid){
			if ($ds->sensor_resol_temp
			|| $ds->sensor_lat_resolution
			|| $ds->sensor_lon_resolution
			|| $ds->sensor_vert_resolution){
				$infos .= '<tr style = \'page-break-inside: avoid;\'><td colspan="4" align="center"><b>Data resolution</b></td></tr>';
			}
			if ($ds->sensor_resol_temp){
				$infos .= "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Temporal resolution</b></td><td colspan='3'>".$ds->sensor_resol_temp."</td></tr>";
			}
			if ($ds->sensor_lat_resolution){
				$infos .= "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Latitude resolution</b></td><td colspan='3'>".$ds->sensor_lat_resolution."</td></tr>";
			}
			if ($ds->sensor_lon_resolution){
				$infos .= "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Longitude resolution</b></td><td colspan='3'>".$ds->sensor_lon_resolution."</td></tr>";
			}
			if ($ds->sensor_vert_resolution){
				$infos .= "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Vertical resolution</b></td><td colspan='3'>".$ds->sensor_vert_resolution."</td></tr>";
			}
		}else{
			if (isset($ds->sensor_resol_temp)){
				$infos .= "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Observation frequency</b></td><td colspan='3'>".$ds->sensor_resol_temp."</td></tr>";
			}
			if (isset($ds->sensor_lat_resolution)){
				$infos .= "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Horizontal coverage</b></td><td colspan='3'>".$ds->sensor_lat_resolution."</td></tr>";
			}
			if (isset($ds->sensor_vert_resolution)){
				$infos .= "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Vertical coverage</b></td><td colspan='3'>".$ds->sensor_vert_resolution."</td></tr>";
			}
		}
		return $infos;
	}
	
	public static function displayDatabase(& $db){
		echo "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Database</b></td><td colspan='3'>";
		if (isset($db->database_url) && !empty($db->database_url)){
			echo '<a target="_blank" href="'.$db->database_url.'">'.$db->database_name."</a>";
		}else{
			echo $db->database_name;
		}
		echo "</td></tr>";
	}
	
	public static function getDatabase(& $db){
		$infos = null;
		$infos .= "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Database</b></td><td colspan='3'>";
		if (isset($db->database_url) && !empty($db->database_url)){
			$infos .= '<a target="_blank" href="'.$db->database_url.'">'.$db->database_name."</a>";
		}else{
			$infos .= $db->database_name;
		}
		$infos .= "</td></tr>";
		return $infos;
	}
	
	public static function displayDataUse(& $dataset){
		echo '</td></tr><tr style = \'page-break-inside: avoid;\'><th colspan="4" align="center"><b>Data use information</b></th></tr>';
		echo "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Use constraints</b></td><td colspan='3'>".$dataset->dats_use_constraints."</td></tr>";
		echo "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Data policy</b></td><td colspan='3'>".$dataset->data_policy->data_policy_name."</td></tr>";
		if (isset($dataset->database)){
			self::displayDatabase($dataset->database);
		}

		$lblDF = "Data format(s)";
		
		if ( isset($dataset->required_data_formats) && !empty($dataset->required_data_formats) ){
			echo "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Data format(s)</b></td><td colspan='3'>";
			foreach ($dataset->required_data_formats as $format){
				echo $format->data_format_name."<br>";
			}
			$lblDF = "Original data format(s)";
			echo"</tr>";
		}
		if ( isset($dataset->data_formats) && !empty($dataset->data_formats) ){
			echo "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>$lblDF</b></td><td colspan='3'>";
			foreach ($dataset->data_formats as $format){
				echo $format->data_format_name."<br>";
			}
			echo"</tr>";
		}
	}
	public static function getDataUse(& $dataset) {
		$infos = null;
		$infos .= '</td></tr><tr style = \'page-break-inside: avoid;\'><th colspan="4" align="center"><b>Data use information</b></th></tr>';
		$infos .= "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Use constraints</b></td><td colspan='3'>" . $dataset->dats_use_constraints . "</td></tr>";
		$infos .= "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Data policy</b></td><td colspan='3'>" . $dataset->data_policy->data_policy_name . "</td></tr>";
		if (isset ( $dataset->database )) {
			$infos .= self::getDatabase ( $dataset->database );
		}
		
		$lblDF = "Data format(s)";
		
		if (isset ( $dataset->required_data_formats ) && ! empty ( $dataset->required_data_formats )) {
			$infos .= "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Data format(s)</b></td><td colspan='3'>";
			foreach ( $dataset->required_data_formats as $format ) {
				$infos .= $format->data_format_name . "<br>";
			}
			$lblDF = "Original data format(s)";
			$infos .= "</tr>";
		}
		if (isset ( $dataset->data_formats ) && ! empty ( $dataset->data_formats )) {
			$infos .= "<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>$lblDF</b></td><td colspan='3'>";
			foreach ( $dataset->data_formats as $format ) {
				$infos .= $format->data_format_name . "<br>";
			}
			$infos .= "</tr>";
		}
		return $infos;
	}
	
	public static function displayDataAvailability(& $dataset,$project_name){
		$liens = getAvailableDataLinks($dataset,$project_name);
	
		if ( isset($liens) && !empty($liens) ){
			echo '<tr style = \'page-break-inside: avoid;\'><td rowspan="'.count($liens).'"><b>Data access</b></td>';
			foreach($liens as $lien){
				echo "<td colspan='3'>$lien</td></tr>";
			}

			//Historique du jeu
			$journal = new journal();
			$journal = $journal->getByDataset($dataset->dats_id,TYPE_NEW.','.TYPE_UPDATE);
			if (isset($journal) && !empty($journal)){
				//echo '<tr style = \'page-break-inside: avoid;\'><td rowspan="'.count($journal).'"><b>History</b></td>';
				echo '<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>History</b></td><td colspan="3" style="padding-right:0px;">';
					
				if (count($journal) > 3){
					echo '<div style="overflow:auto;height:150px;">';
				}
				foreach ($journal as $jEntry){
					//echo '<td colspan="3">';
					echo '<p style="font-size: 12px;">';
					if ($jEntry->type_id == TYPE_NEW){
						echo '<span class="pink_tag" style="font-size: 10px;" >ISSUE</span>';
					}else if ($jEntry->type_id == TYPE_UPDATE){
						echo '<span class="lightpink_tag" style="font-size: 10px;">UPDATE</span>';
					}
					echo '<b>'.$jEntry->date->format('Y-m-d').'</b>';
					if (isset($jEntry->comment) && !empty($jEntry->comment)){
						echo '<br/>'.$jEntry->comment;
					}
					echo '</p>';
					//echo '</td></tr>';
				}
				if (count($journal) > 3){
					echo '</div>';
				}
				echo '</td></tr>';
			}
		}else{
			echo '<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>Data availability</b></td>';
			echo '<td colspan="3">No data are currently available for this dataset.&nbsp;';
			$suscribeUrl = '/Your-Account?p&pageId=6&datsId='.$dataset->dats_id;
			if (isset($_SESSION['loggedUserAbos'])){
				$aboIds = unserialize($_SESSION['loggedUserAbos']);
				if ( !isset($aboIds) )
					$aboIds = array();
				if (array_search($dataset->dats_id,$aboIds) === false){
					echo "<a href='$suscribeUrl'>Click here to receive an email when this dataset becomes available</a>";
				}else{
					echo "You will be informed by email when this dataset becomes available.";
				}
			}else{
				echo "<a href='$suscribeUrl'>Click here to receive an email when this dataset becomes available</a>";
			}
			echo '</td></tr>';
		}
	}
	
	public static function getDataAvailability(& $dataset, $project_name) {
		$infos = null;
		$liens = getAvailableDataLinks ( $dataset, $project_name );
		
		if (isset ( $liens ) && ! empty ( $liens )) {
			$infos .= '<tr style = \'page-break-inside: avoid;\'><td rowspan="' . count ( $liens ) . '"><b>Data access</b></td>';
			foreach ( $liens as $lien ) {
				$infos .= "<td colspan='3'>$lien</td></tr>";
			}
			
			// Historique du jeu
			$journal = new journal ();
			$journal = $journal->getByDataset ( $dataset->dats_id, TYPE_NEW . ',' . TYPE_UPDATE );
			if (isset ( $journal ) && ! empty ( $journal )) {
				// $infos .= '<tr style = \'page-break-inside: avoid;\'><td rowspan="'.count($journal).'"><b>History</b></td>';
				$infos .= '<tr style = \'page-break-inside: avoid;\'><td style = \'page-break-inside: avoid;\'><b>History</b></td><td colspan="3" style="padding-right:0px;">';
				
				if (count ( $journal ) > 3) {
					$infos .= '<div style="overflow:auto;">';
				}
				foreach ( $journal as $jEntry ) {
					// $infos .= '<td colspan="3">';
					$infos .= '<p style="font-size: 12px;">';
					if ($jEntry->type_id == TYPE_NEW) {
						$infos .= '<span class="pink_tag" style="font-size: 10px;" >ISSUE</span>';
					} else if ($jEntry->type_id == TYPE_UPDATE) {
						$infos .= '<span class="lightpink_tag" style="font-size: 10px;">UPDATE</span>';
					}
					$infos .= '<b>' . $jEntry->date->format ( 'Y-m-d' ) . '</b>';
					if (isset ( $jEntry->comment ) && ! empty ( $jEntry->comment )) {
						$infos .= '<br/>' . $jEntry->comment;
					}
					$infos .= '</p>';
					// $infos .= '</td></tr>';
				}
				if (count ( $journal ) > 3) {
					$infos .= '</div>';
				}
				$infos .= '</td></tr>';
			}
		} else {
			$infos .= '<tr><td style = \'page-break-inside: avoid;\'><b>Data availability</b></td>';
			$infos .= '<td colspan="3">No data are currently available for this dataset.&nbsp;';
			$suscribeUrl = '/Your-Account?p&pageId=6&datsId=' . $dataset->dats_id;
			if (isset ( $_SESSION ['loggedUserAbos'] )) {
				$aboIds = unserialize ( $_SESSION ['loggedUserAbos'] );
				if (! isset ( $aboIds ))
					$aboIds = array ();
				if (array_search ( $dataset->dats_id, $aboIds ) === false) {
					$infos .= "<a href='$suscribeUrl'>Click here to receive an email when this dataset becomes available</a>";
				} else {
					$infos .= "You will be informed by email when this dataset becomes available.";
				}
			} else {
				$infos .= "<a href='$suscribeUrl'>Click here to receive an email when this dataset becomes available</a>";
			}
			$infos .= '</td></tr>';
		}
		return $infos;
	}

}

?>