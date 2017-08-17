<?php

require_once("bd/dataset.php");

function getCouleurScore($cpt, $max){
	if ($max == 0){
		return 'red';
	}else{
		if ($cpt / $max >= 0.75){
			return 'green';
		}else if ($cpt / $max >= 0.5){
			return 'orange';
		}else{
			return 'red';
		}
	}
}

function printTd($cpt, $max, $comment = ''){
	$color = getCouleurScore($cpt, $max);
	echo "<td title='$comment' ><font color='$color'>$cpt / $max</font></td>";
}

function printTh($cpt, $max){
	$color = getCouleurScore($cpt, $max);
	echo "<th><font color='$color'>$cpt / $max</font></th>";
}

class dats_quality{

	var $cptCore;
	var $cptCoreMax;
	var $commentCore;

	var $cptInfo;
	var $cptInfoMax;
	var $commentInfo;

	var $cptDates;
	var $cptDatesMax;
	var $commentDates;

	var $cptUse;
	var $cptUseMax;
	var $commentUse;

	var $cptSensor;
	var $cptSensorMax;
	var $commentSensor;

	var $cptSite;
	var $cptSiteMax;
	var $commentSite;

	var $cptVar;
	var $cptVarMax;
	var $commentVar;

	function init($dats){
		$this->dats = $dats;

		$this->initCore($dats);
		$this->initInfos($dats);
		$this->initDates($dats);
		$this->initUse($dats);
		$this->initSites($dats);
		$this->initInstruments($dats);
		$this->initParams($dats);
	}

	function getScore(){
		$score = 0;
			
		$score += $this->getScoreCore();
		$score += $this->getScoreInfo();
		$score += $this->getScoreDates();
		$score += $this->getScoreUse();
		$score += $this->getScoreSensor();
		$score += $this->getScoreSite();
		$score += $this->getScoreVar();

		return round($score);
	}

	function getScoreVar(){
		//Params => 20
		return (($this->cptVarMax == 0)?0:($this->cptVar * 20 / $this->cptVarMax));
	}

	function getScoreSite(){
		//Sites => 15
		return (($this->cptSiteMax == 0)?0:($this->cptSite * 15 / $this->cptSiteMax));
	}

	function getScoreUse(){
		//Use constraints => 10
		return $this->cptUse * 10 / $this->cptUseMax;
	}

	function getScoreSensor(){
		//Instruments => 15
		return (($this->cptSensorMax == 0)?0:($this->cptSensor * 15 / $this->cptSensorMax));
	}

	function getScoreDates(){
		//Dates => 15
		return $this->cptDates * 15 / $this->cptDatesMax;
	}

	function getScoreCore(){
		//Core info => 15
		return $this->cptCore * 15 / $this->cptCoreMax;
	}

	function getScoreInfo(){
		//Opt info => 10
		return $this->cptInfo * 10 / $this->cptInfoMax;
	}

	function initParams($dats){
		$nbParams = count($dats->dats_variables);
		$this->commentVar = "Info: $nbParams variable(s)\n";
		$this->cptVar = $this->cptVarMax = $nbParams * 4;
		if ($nbParams == 0){
			//$dats_var_comment .= "No parameter\n";
		}else{
			foreach($dats->dats_variables as $dats_var){
				if ( !isset($dats_var->variable->gcmd) ) {
					$this->commentVar .= "Missing: GCMD science keyword\n";
					$this->cptVar--;
				}
				if ( !isset($dats_var->unit) ) {
					$this->commentVar .= "Missing: Unit\n";
					$this->cptVar--;
				}
				if ( !isset($dats_var->methode_acq) || empty($dats_var->methode_acq) ) {
					$this->commentVar .= "Missing: Acquisition methodology and quality\n";
					$this->cptVar -= 0.5;
				}
				if ( !isset($dats_var->variable->sensor_precision) || empty($dats_var->variable->sensor_precision) ) {
					$this->commentVar .= "Missing: Precision / incertainty\n";
					$this->cptVar -= 0.5;
				}
				if ( !isset($dats_var->date_min) || empty($dats_var->date_min) ) {
					$this->commentVar .= "Missing: Date min\n";
					$this->cptVar -= 0.5;
				}
				if ( !isset($dats_var->date_max) || empty($dats_var->date_max) ) {
					$this->commentVar .= "Missing: Date max\n";
					$this->cptVar -= 0.5;
				}
			}
		}
	}

	function initSites($dats){
		$nbSites = count($dats->sites);
		$this->commentSite = "Info: $nbSites site(s)\n";;
		$this->cptSite = $this->cptSiteMax = 2 * $nbSites;
		if ( $nbSites == 0 ){
			//$dats_site_comment = "No platform\n";
		}else{
			foreach($dats->sites as $site){
				$site_name = $site->place_name;
				if (isset($site->parent_place) && !empty($site->parent_place)){
					if (empty($site_name)){
						$site_name = $site->parent_place->place_name;
					}
				}else{
					if ( !isset($site->gcmd_plateform_keyword) ) {
						$this->commentSite .= "Missing: Plateform keyword (place: $site_name)\n";
						$this->cptSite--;
					}
				}
				if ( !isset($site->boundings) ) {
					$this->commentSite .= "Missing: Boundings (place: $site_name)\n";
					$this->cptSite--;
				}
			}
		}
	}

	function initInstruments($dats){
		$nbInstrus = count($dats->dats_sensors);
		$this->commentSensor = "Info: $nbInstrus sensors\n";
		$this->cptSensor = $this->cptSensorMax = 5 * $nbInstrus;
		if ( $nbInstrus == 0 ){
			//		echo "<td><font color='red'>0</font></td>";
		}else{
			foreach($dats->dats_sensors as $dats_sensor){
				if ( !isset($dats_sensor->sensor->gcmd_instrument_keyword) ){
					$this->commentSensor .= "Missing: Instrument keyword\n";
					$this->cptSensor--;
				}
				if ( !isset($dats_sensor->sensor->boundings) ){
					$this->commentSensor .= "Missing: Instrument location\n";
					$this->cptSensor--;
				}
				if ( !isset($dats_sensor->sensor->manufacturer) ) {
					$this->commentSensor .= "Missing: Sensor manufacturer\n";
					$this->cptSensor -= 0.5;
				}
				if ( !isset($dats_sensor->sensor->sensor_model) || empty($dats_sensor->sensor->sensor_model) ){
					$this->commentSensor .= "Missing: Sensor model\n";
					$this->cptSensor -= 0.5;
				}
				if ( !isset($dats_sensor->sensor->sensor_calibration) || empty($dats_sensor->sensor->sensor_calibration) ){
					$this->commentSensor .= "Missing: Sensor calibration\n";
					$this->cptSensor -= 0.5;
				}
				if ( !isset($dats_sensor->sensor_resol_temp) || empty($dats_sensor->sensor_resol_temp) ){
					$this->commentSensor .= "Missing: Observation frequency\n";
					$this->cptSensor -= 0.5;
				}
				if ( !isset($dats_sensor->sensor_lat_resolution) || empty($dats_sensor->sensor_lat_resolution) ){
					$this->commentSensor .= "Missing: Horizontal coverage\n";
					$this->cptSensor -= 0.5;
				}
				if ( !isset($dats_sensor->sensor_vert_resolution) || empty($dats_sensor->sensor_vert_resolution) ){
					$this->commentSensor .= "Missing: Vertical coverage\n";
					$this->cptSensor -= 0.5;
				}
			}
		}

	}

	function initUse($dats){

		$this->commentUse = '';
		$this->cptUse = $this->cptUseMax = 2;
		if ( !isset($dats->dats_use_constraints) || empty($dats->dats_use_constraints) ){
			$this->commentUse .= "Missing: Use constraints\n";
			$this->cptUse--;
		}
		if ( !isset($dats->data_policy) ){
			$this->commentUse .= "Missing: Data policy\n";
			$this->cptUse--;
		}
	}

	function initCore($dats){
		$this->commentCore = "";
		$this->cptCore = $this->cptCoreMax = 3;
		if ( !isset($dats->dats_title) || empty($dats->dats_title) ){
			$this->commentCore .= "Missing: Title\n";
			$this->cptCore--;
		}
		if ( !isset($dats->dats_abstract) || empty($dats->dats_abstract) ){
			$this->commentCore .= "Missing: Abstract\n";
			$this->cptCore--;
		}
		if ( !isset($dats->dats_originators) || empty($dats->dats_originators) ){
			$this->commentCore .= "Missing: Contact\n";
			$this->cptCore--;
		}else{
			foreach($dats->dats_originators as $pi){
				if (!filter_var($pi->personne->pers_email_1, FILTER_VALIDATE_EMAIL)){
					$this->commentCore .= "Missing: Contact (incorrect email)\n";
					$this->cptCore--;
					break;
				}
			}
		}
	}

	function initInfos($dats){
		$this->commentInfo = "";
		$this->cptInfo = $this->cptInfoMax = 7;
		if ( !isset($dats->dats_doi) || empty($dats->dats_doi) ){
			$this->commentInfo .= "Missing: DOI\n";
			$this->cptInfo--;
		}
		if ( !isset($dats->dats_pub_date) || empty($dats->dats_pub_date) ){
			$this->commentInfo .= "Missing: Creation date\n";
			$this->cptInfo--;
		}
		if ( !isset($dats->projects) || empty($dats->projects) ){
			$this->commentInfo .= "Missing: Project\n";
			$this->cptInfo--;
		}
		if ( !isset($dats->dats_purpose) || empty($dats->dats_purpose) ){
			$this->commentInfo .= "Missing: Observing strategy\n";
			$this->cptInfo--;
		}
		if ( !isset($dats->dats_reference) || empty($dats->dats_reference) ){
			$this->commentInfo .= "Missing: References\n";
			$this->cptInfo--;
		}
		if ( !isset($dats->database) ){
			$this->commentInfo .= "Missing: Database\n";
			$this->cptInfo--;
		}
		if ( !isset($dats->data_formats) || empty($dats->data_formats) ){
			$this->commentInfo .= "Missing: Data format\n";
			$this->cptInfo--;
		}
	}

	function initDates($dats){
		$this->commentDates = '';
		$this->cptDates = $this->cptDatesMax = 5;
		if ( !isset($dats->dats_date_begin) || empty($dats->dats_date_begin) ){
			$this->commentDates .= "Missing: Date begin\n";
			$this->cptDates -= 2;
		}
		if ( !$dats->dats_date_end_not_planned && (!isset($dats->dats_date_end) || empty($dats->dats_date_end) ) ){
			$this->commentDates .= "Missing: Date end\n";
			$this->cptDates -= 2;
		}
		if ( !isset($dats->period) ){
			$this->commentDates .= "Missing: Period name\n";
			$this->cptDates--;
		}

	}
	
	function display($datsType){
		
		echo '<table><tr><th colspan="2" align="center" >';
		$score = $this->getScore();
		$color = getCouleurScore($score,100);
		echo "<font color='$color' size='3' >Score: $score %</font>";
		echo '</th></tr>';
			
		echo '<tr><th>Dataset description</th>';
		printTh(round($this->getScoreCore() + $this->getScoreInfo(),0), 25);
		echo '</tr>';

		echo '<tr><td colspan="2">';
		echo nl2br($this->commentCore.$this->commentInfo);
		echo '</td></tr>';

		echo '<tr><th>Dates</th>';
		printTh(round($this->getScoreDates(),0), 15);
		echo '</tr>';

		echo '<tr><td colspan="2">';
		echo nl2br($this->commentDates);
		echo '</td></tr>';

		echo '<tr><th>Use constraints</th>';
		printTh(round($this->getScoreUse(),0), 10);
		echo '</tr>';

		echo '<tr><td colspan="2">';
		echo nl2br($this->commentUse);
		echo '</td></tr>';
		if (!$datsType == 1 || !$datsType == 2){
		echo '<tr><th>Sensors</th>';
		printTh(round($this->getScoreSensor(),0), 15);
		echo '</tr>';

		echo '<tr><td colspan="2">';
		echo nl2br($this->commentSensor);
		echo '</td></tr>';
		}
		echo '<tr><th>Sites</th>';
		printTh(round($this->getScoreSite(),0), 15);
		echo '</tr>';

		echo '<tr><td colspan="2">';
		echo nl2br($this->commentSite);
		echo '</td></tr>';

		echo '<tr><th>Params</th>';
		printTh(round($this->getScoreVar(),0), 20);
		echo '</tr>';

		echo '<tr><td colspan="2">';
		echo nl2br($this->commentVar);
		echo '</td></tr>';

		echo '</table>';
	}


}

?>