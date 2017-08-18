<?php
/*
 * Created on 27 janv. 2011 To change the template for this generated file go to Window - Preferences - PHPeclipse - PHP - Code Templates
 */
require_once ("HTML/QuickForm.php");
require_once ("HTML/QuickForm/radio.php");
require_once ("common.php");
require_once ("bd/period.php");
require_once ("bd/variable.php");
require_once ("bd/gcmd_instrument_keyword.php");
require_once ("bd/gcmd_science_keyword.php");
require_once ("bd/place.php");
require_once ("forms/validation.php");
require_once ("utils/datepicker_utils.php");
require_once ("conf/conf.php");
class search_form extends HTML_QuickForm {
	var $latMin;
	var $latMax;
	var $lonMin;
	var $lonMax;
	var $date_begin;
	var $date_end;
	var $period;
	var $gcmd_sensor;
	var $gcmd_variable;
	var $keywords;
	var $and_or;
	var $order_by;
	var $filter_data;
	var $filter_data_db;
	var $projectName;
	function createForm($projectName) {
		$this->projectName = $projectName;
		$this->addElement ( 'text', 'keywords', "Keywords", array (
				'size' => '50' 
		) );
		$this->applyFilter ( 'keywords', 'trim' );
		$and_or [] = & HTML_QuickForm::createElement ( 'radio', null, null, '&nbsp;'."All of the above keywords", 'and' );
		$and_or [] = & HTML_QuickForm::createElement ( 'radio', null, null, '&nbsp;'."Any of the above keywords", 'or' );
		$this->addGroup ( $and_or, 'and_or', "Search with", '&nbsp;&nbsp;&nbsp;' );
		$defaultValues ['and_or'] = 'or';
		$this->setDefaults ( $defaultValues );
		$this->createFormVariable ();
		$this->createFormInstrumentType ();
		$this->createFormPeriode ();
		$this->createFormMap ();
		$this->createFormOrderBy ();
		$this->createFormFilterData ();
		$this->createFormFilterDataDb ();
		$this->addElement ( 'submit', 'bouton_search', 'search' );
	}
	function createFormOrderBy() {
		$order_by [] = & HTML_QuickForm::createElement ( 'radio', null, null, '&nbsp;'."By instruments", '1' );        
		$order_by [] = & HTML_QuickForm::createElement ( 'radio', null, null, '&nbsp;'."By platform types", '2' );
		$order_by [] = & HTML_QuickForm::createElement ( 'radio', null, null, '&nbsp;'."By dataset name", '3' );
		$this->addGroup ( $order_by, 'order_by', "Sort result:", '&nbsp;&nbsp;&nbsp;' );
		$defaultValues ['order_by'] = '3';
		$this->setDefaults ( $defaultValues );
	}
	function createFormFilterData() {
		$options [] = & HTML_QuickForm::createElement ( 'radio', null, null, '&nbsp;'."yes", 1 );
		$options [] = & HTML_QuickForm::createElement ( 'radio', null, null,'&nbsp;'."no", 0 );
		$this->addGroup ( $options, 'filter_data', "Show only datasets with available data ?", '&nbsp;&nbsp;' );
		$defaultValues ['filter_data'] = 0;
		$this->setDefaults ( $defaultValues );
	}
	function createFormFilterDataDb() {
		$options [] = & HTML_QuickForm::createElement ( 'radio', null, null, '&nbsp;'."yes", 1 );
		$options [] = & HTML_QuickForm::createElement ( 'radio', null, null, '&nbsp;'."no", 0 );
		$this->addGroup ( $options, 'filter_data_db', "Show only datasets with homogenized data?", '&nbsp;&nbsp;' );
		$defaultValues ['filter_data_db'] = 0;
		$this->setDefaults ( $defaultValues );
	}
	function createFormVariable() {
		$key = new gcmd_science_keyword ();
		$key_select = $key->chargeForm ( $this, 'gcmd_science_key', "Parameter keyword" );
		$this->addElement ( $key_select );
	}
	function createFormInstrumentType() {
		$key = new gcmd_instrument_keyword ();
		$key_select = $key->chargeForm ( $this, 'sensor_gcmd', "Instrument type" );
		$this->addElement ( $key_select );
	}
	function createFormPeriode() {
		$key = new period ();
		$key_select = $key->chargeFormWithDates ( $this, 'period', "Period" );
		$this->addElement ( $key_select );
		$this->addElement ( 'text', 'date_begin', "Date begin (yyyy-mm-dd)", array (
				'size' => 10,
				'id' => 'date_begin' 
		) );
		$this->addElement ( 'text', 'date_end', "Date end (yyyy-mm-dd)", array (
				'size' => 10,
				'id' => 'date_end' 
		) );
	}
	function createFormMap() {
		
		$this->addElement ( 'hidden', 'minLat', MAP_DEFAULT_LAT_MIN);
		$this->addElement ( 'hidden', 'maxLat', MAP_DEFAULT_LAT_MAX);
		$this->addElement ( 'hidden', 'minLon', MAP_DEFAULT_LON_MIN );
		$this->addElement ( 'hidden', 'maxLon', MAP_DEFAULT_LON_MAX );
		
		$this->addElement ( 'hidden', 'startMinLat', MAP_DEFAULT_LAT_MIN );
		$this->addElement ( 'hidden', 'startMaxLat', MAP_DEFAULT_LAT_MAX );
		$this->addElement ( 'hidden', 'startMinLon', MAP_DEFAULT_LON_MIN );
		$this->addElement ( 'hidden', 'startMaxLon', MAP_DEFAULT_LON_MAX );
		
		
		$this->addElement ( 'text', 'maxLatDeg', 'Lat: ', array (
				'id' => 'maxLatDeg',
				'size' => 3 
		) );
		$this->addElement ( 'text', 'minLatDeg', 'Lat: ', array (
				'id' => 'minLatDeg',
				'size' => 3 
		) );
		$this->addElement ( 'text', 'maxLonDeg', 'Lon: ', array (
				'id' => 'maxLonDeg',
				'size' => 3 
		) );
		$this->addElement ( 'text', 'minLonDeg', 'Lon: ', array (
				'id' => 'minLonDeg',
				'size' => 3 
		) );
		$this->addElement ( 'text', 'maxLatMin', '', array (
				'id' => 'maxLatMin',
				'size' => 2 
		) );
		$this->addElement ( 'text', 'minLatMin', '', array (
				'id' => 'minLatMin',
				'size' => 2 
		) );
		$this->addElement ( 'text', 'maxLonMin', '', array (
				'id' => 'maxLonMin',
				'size' => 2 
		) );
		$this->addElement ( 'text', 'minLonMin', '', array (
				'id' => 'minLonMin',
				'size' => 2 
		) );
		$this->addElement ( 'text', 'maxLatSec', '', array (
				'id' => 'maxLatSec',
				'size' => 2 
		) );
		$this->addElement ( 'text', 'minLatSec', '', array (
				'id' => 'minLatSec',
				'size' => 2 
		) );
		$this->addElement ( 'text', 'maxLonSec', '', array (
				'id' => 'maxLonSec',
				'size' => 2 
		) );
		$this->addElement ( 'text', 'minLonSec', '', array (
				'id' => 'minLonSec',
				'size' => 2 
		) );
		$this->addElement ( 'submit', 'unzoom', 'UnZoom', array (
				'onclick' => "unZoom()" 
		) );
	}
	function addValidationRules() {
		$this->registerRule ( 'validDate', 'function', 'validDate' );
		$this->registerRule ( 'validPeriod', 'function', 'validPeriod' );
		$this->addRule ( 'date_begin', 'Date begin is not a date', 'validDate' );
		$this->addRule ( 'date_end', 'Date end is not a date', 'validDate' );
		$this->addRule ( array (
				'date_begin',
				'date_end' 
		), 'Date end must be after date begin', 'validPeriod' );
		$this->addRule ( 'maxLatDeg', 'Latitude &deg; must be numeric', 'numeric' );
		$this->addRule ( 'maxLatDeg', 'Latitude &deg; is incorrect', 'number_range', array (
				- 90,
				90 
		) );
		$this->addRule ( 'minLatDeg', 'Latitude &deg; must be numeric', 'numeric' );
		$this->addRule ( 'minLatDeg', 'Latitude &deg; is incorrect', 'number_range', array (
				- 90,
				90 
		) );
		$this->addRule ( 'maxLonDeg', 'Longitude &deg; must be numeric', 'numeric' );
		$this->addRule ( 'maxLonDeg', 'Longitude &deg; is incorrect', 'number_range', array (
				- 180,
				180 
		) );
		$this->addRule ( 'minLonDeg', 'Longitude &deg;  must be numeric', 'numeric' );
		$this->addRule ( 'minLonDeg', 'Longitude &deg; is incorrect', 'number_range', array (
				- 180,
				180 
		) );
		$this->addRule ( 'maxLatMin', 'Latitude \' must be numeric', 'numeric' );
		$this->addRule ( 'maxLatMin', 'Latitude \' is incorrect', 'number_range', array (
				0,
				50 
		) );
		$this->addRule ( 'minLatMin', 'Latitude \' must be numeric', 'numeric' );
		$this->addRule ( 'minLatMin', 'Latitude \' is incorrect', 'number_range', array (
				0,
				59 
		) );
		$this->addRule ( 'maxLonMin', 'Longitude \' must be numeric', 'numeric' );
		$this->addRule ( 'maxLonMin', 'Longitude \'  is incorrect', 'number_range', array (
				0,
				59 
		) );
		$this->addRule ( 'minLonMin', 'Longitude \'  must be numeric', 'numeric' );
		$this->addRule ( 'minLonMin', 'Longitude \' is incorrect', 'number_range', array (
				0,
				59 
		) );
		$this->addRule ( 'maxLatSec', 'Latitude " must be numeric', 'numeric' );
		$this->addRule ( 'maxLatSec', 'Latitude " is incorrect', 'number_range', array (
				0,
				50 
		) );
		$this->addRule ( 'minLatSec', 'Latitude " must be numeric', 'numeric' );
		$this->addRule ( 'minLatSec', 'Latitude " is incorrect', 'number_range', array (
				0,
				59 
		) );
		$this->addRule ( 'maxLonSec', 'Longitude " must be numeric', 'numeric' );
		$this->addRule ( 'maxLonSec', 'Longitude " is incorrect', 'number_range', array (
				0,
				59 
		) );
		$this->addRule ( 'minLonSec', 'Longitude " must be numeric', 'numeric' );
		$this->addRule ( 'minLonSec', 'Longitude " is incorrect', 'number_range', array (
				0,
				59 
		) );
	}
	function displayForm() {
		$this->addValidationRules ();
		DatePickerUtils::addScriptPeriod ( 'date_begin', 'date_end' );
		// affichage des erreurs
		if (! empty ( $this->_errors )) {
			foreach ( $this->_errors as $error ) {
				if (strpos ( $error, 'General' ) === 0) {
					echo '<a href="#a_general"><font size="3" color="red">' . $error . '</font></a><br>';
				} else if (strpos ( $error, 'Contact' ) === 0) {
					echo '<a href="#a_contact"><font size="3" color="red">' . $error . '</font></a><br>';
				} else if (strpos ( $error, 'Instru' ) === 0) {
					echo '<a href="#a_instru"><font size="3" color="red">' . $error . '</font></a><br>';
				} else if (strpos ( $error, 'Site' ) === 0) {
					echo '<a href="#a_site"><font size="3" color="red">' . $error . '</font></a><br>';
				} else if (strpos ( $error, 'Measured' ) === 0) {
					echo '<a href="#a_param"><font size="3" color="red">' . $error . '</font></a><br>';
				} else if (strpos ( $error, 'Derived' ) === 0) {
					echo '<a href="#a_param_calcul"><font size="3" color="red">' . $error . '</font></a><br>';
				} else if (strpos ( $error, 'Data' ) === 0) {
					echo '<a href="#a_use"><font size="3" color="red">' . $error . '</font></a><br>';
				} else {
					echo '<font size="3" color="red">' . $error . '</font><br>';
				}
			}
		}
		echo '<div id="errors" color="red"></div><br>';
		
		$reqUri = $_SERVER ['REQUEST_URI'];
		
		echo '<form action="' . $reqUri . '" method="post" name="frmmap" id="frmmap" enctype="multipart/form-data">';
		echo $this->getElement ( 'minLat' )->toHTML ();
		echo $this->getElement ( 'maxLat' )->toHTML ();
		echo $this->getElement ( 'minLon' )->toHTML ();
		echo $this->getElement ( 'maxLon' )->toHTML ();
		echo $this->getElement ( 'startMinLat' )->toHTML ();
		echo $this->getElement ( 'startMaxLat' )->toHTML ();
		echo $this->getElement ( 'startMinLon' )->toHTML ();
		echo $this->getElement ( 'startMaxLon' )->toHTML ();
		
		echo '<table>';
		echo '<tr><td>' . $this->getElement ( 'keywords' )->getLabel () . '</td><td colspan="3">' . $this->getElement ( 'keywords' )->toHTML () . '</td></tr>';
		echo '<tr><td>' . $this->getElement ( 'and_or' )->getLabel () . '</td><td colspan="3">' . $this->getElement ( 'and_or' )->toHTML () . '</td></tr>';
		
		echo '<tr><td>' . $this->getElement ( 'sensor_gcmd' )->getLabel () . '</td><td colspan="3">' . $this->getElement ( 'sensor_gcmd' )->toHTML () . '</td></tr>';
		
		echo '<tr><td>' . $this->getElement ( 'gcmd_science_key' )->getLabel () . '</td>' . '<td colspan="3">' . $this->getElement ( 'gcmd_science_key' )->toHTML () . '</td></tr>';
		
		echo '<tr><td rowspan="2">Period</td>' . '<td>' . $this->getElement ( 'date_begin' )->getLabel () . '</td>' . '<td>' . $this->getElement ( 'date_end' )->getLabel () . "</td></tr>";
		echo '<tr><td>' . $this->getElement ( 'date_begin' )->toHTML () . '</td>' . '<td>' . $this->getElement ( 'date_end' )->toHTML () . '</td></tr>';
		//echo '<tr><td>' . $this->getElement ( 'order_by' )->getLabel () . '</td><td colspan="3">' . $this->getElement ( 'order_by' )->toHTML () . '</td></tr>';
		echo '<tr><td colspan="4">';
		echo '<div id="line1"></div><div id="line2"></div>
                <table border="0">
                </table><br><br>
                <table>
                <tr>
                        <td width=400 style="text-align: left; vertical-align: top;">
                                <div id="mapContainer">
                                <div id="mapPoints" style="position:absolute;width:400px;"></div>

                                <div id="redPoint" style="position:absolute;"></div>
                                <div id="boxTitle" class="">Zone</div>
                                <div id="map" style="cursor: crosshair;width:400px;height:200px;">
                                <div id="selDraw">
                                <div id="selectionBox" style="position:relative;visibility:hidden;">
                                <div id="boxBorder" style="border-width: 2; position:relative;width:100%;height:100%; border-color: #000000; border-style: solid;">
                                <div id="boxBack" style="background-color: transparent; -moz-opacity:0.5; filter:Alpha(Opacity=50); opacity:0.50; width:100%; height:100%;">
                                </div></div></div></div></div></div>

                        </td>

                        <td valign="top">
                                <div id="mouseCoord" style="position:relative;padding:15px 0px 15px 0px;">
                                <div id="boxTitle">Mouse position</div>
                                <table border="0" width="99%">
                                <tr><td style="vertical-align:middle;font-size:12px;">Lat :<br> <a id="yval"></a><br>Lon :<br> <a id="xval"></a></td>

                                <td style="vertical-align:middle"><img src="/img/mousePos.gif"></td></tr>
                                </table>
                                </div>
                                <div style="position:relative;padding:0px 0px 15px 60px;">' . $this->getElement ( 'unzoom' )->toHTML () . '</div>
                                		
                                <div id="zoom" style="position:relative;">
                                <div id="boxTitle">Zoom</div>
                                <div id="msg" class="INFO" style=""></div>
                                <table width="99%" style="font-size:10px"><tr><td align="left" style="white-space:nowrap;">
                                ' . $this->getElement ( 'maxLatDeg' )->getLabel () . '' . $this->getElement ( 'maxLatDeg' )->toHTML () . '&#176;' . $this->getElement ( 'maxLatMin' )->toHTML () . '\'' . $this->getElement ( 'maxLatSec' )->toHTML () . '" <br>
                                ' . $this->getElement ( 'minLonDeg' )->getLabel () . '' . $this->getElement ( 'minLonDeg' )->toHTML () . '&#176;' . $this->getElement ( 'minLonMin' )->toHTML () . '\'' . $this->getElement ( 'minLonSec' )->toHTML () . '"
                                <br></td></tr><tr><td border="0px"><img style="padding:0px 30px 0px 30px;" src="/img/zoomBox.gif"></td></tr><tr><td align="right" style="white-space:nowrap;">
                                ' . $this->getElement ( 'minLatDeg' )->getLabel () . ' ' . $this->getElement ( 'minLatDeg' )->toHTML () . '&#176;' . $this->getElement ( 'minLatMin' )->toHTML () . '\'' . $this->getElement ( 'minLatSec' )->toHTML () . '" <br>
                                ' . $this->getElement ( 'maxLonDeg' )->getLabel () . ' ' . $this->getElement ( 'maxLonDeg' )->toHTML () . '&#176;' . $this->getElement ( 'maxLonMin' )->toHTML () . '\'' . $this->getElement ( 'maxLonSec' )->toHTML () . '"
                                <br>
                                </td></tr>
                                </table>
                                <br>
                                </td></tr></table>';
		echo '</td></tr>';
		// echo '<tr><td>'.$this->getElement('order_by')->geLabel().'</td><td colspan="4">'.$this->getElement('order_by')->toHTML().'</td></tr>';
		
		echo '<tr><td colspan="2">' . $this->getElement ( 'filter_data' )->getLabel () . '</td><td colspan="2">' . $this->getElement ( 'filter_data' )->toHTML () . '</td></tr>';
		echo '<tr><td colspan="2">' . $this->getElement ( 'filter_data_db' )->getLabel () . '</td><td colspan="2">' . $this->getElement ( 'filter_data_db' )->toHTML () . '</td></tr>';
		
		echo '<th colspan="4" align="center">' . $this->getElement ( 'bouton_search' )->toHTML () . '</td></th></table>';
		echo '</form>';
	}
	function saveForm() {
		$this->keywords = $this->exportValue ( 'keywords' );
		
		$this->and_or = $this->exportValue ( 'and_or' );
		$this->period = $this->exportValue ( 'period' );
		$this->date_begin = $this->exportValue ( 'date_begin' );
		$this->date_end = $this->exportValue ( 'date_end' );
		$this->order_by = $this->exportValue ( 'order_by' );
		$this->gcmd_sensor = $this->exportValue ( 'sensor_gcmd' );
		$gcmd_ids = $this->exportValue ( 'gcmd_science_key' );
		$this->gcmd_variable = 0;
		for($j = 3; $j >= 0; $j --) {
			
			if (isset ( $gcmd_ids [$j] ) && $gcmd_ids [$j] > 0) {
				$this->gcmd_variable = $gcmd_ids [$j];
				break;
			}
		}
		$this->latMin = $this->deg2Double ( $this->exportValue ( 'minLatDeg' ), $this->exportValue ( 'minLatMin' ), $this->exportValue ( 'minLatSec' ) );
		$this->latMax = $this->deg2Double ( $this->exportValue ( 'maxLatDeg' ), $this->exportValue ( 'maxLatMin' ), $this->exportValue ( 'maxLatSec' ) );
		$this->lonMin = $this->deg2Double ( $this->exportValue ( 'minLonDeg' ), $this->exportValue ( 'minLonMin' ), $this->exportValue ( 'minLonSec' ) );
		$this->lonMax = $this->deg2Double ( $this->exportValue ( 'maxLonDeg' ), $this->exportValue ( 'maxLonMin' ), $this->exportValue ( 'maxLonSec' ) );
		$this->order_by = $this->exportValue ( 'order_by' );
		
		$this->filter_data = $this->exportValue ( 'filter_data' );
		$this->filter_data_db = $this->exportValue ( 'filter_data_db' );
		
		//$_SESSION ['requete_search_form'] = serialize ( $this );
	}
	function deg2Double($deg, $min, $sec) {
		if ($deg == "")
			$deg = 0;
		if ($min == "")
			$min = 0;
		if ($sec == "")
			$sec = 0;
		$d = intval ( $deg );
		$m = intval ( $min );
		$s = intval ( $sec );
		if ($d != 0) {
			$sign = $d / abs ( $d );
		} else if ($deg . length > 1 && $deg . substring ( 0, 1 ) == "-") {
			$sign = - 1;
		} else {
			$sign = 1;
		}
		return ($d + $sign * $m / 60 + $sign * $s / 3600);
	}
	function toSearchRequest(){
		$recherche = array();
	
		if ($this->keywords){
			$recherche['keywords'] = $this->keywords;
		}
		$recherche['keywords_all'] = $this->and_or == 'and';
	
		if ($this->date_begin) {
			$recherche['period']['min'] = $this->date_begin;
		}
		if ($this->date_end) {
			$recherche['period']['max'] = $this->date_end;
		}
		if ($this->projectName){
			$recherche['project'] = $this->projectName;
		}
		if (isset ( $this->gcmd_variable ) && $this->gcmd_variable > 0){
			$gcmd = new gcmd_science_keyword();
			$k = $gcmd->getById($this->gcmd_variable);
			$recherche['parameter'] = $k->gcmd_name;
		}
		if (isset ( $this->gcmd_sensor ) && $this->gcmd_sensor > 0){
			$gcmd = new gcmd_instrument_keyword();
			$s = $gcmd->getById($this->gcmd_sensor);
			$recherche['instrument'] = $s->gcmd_sensor_name;
		}
	
		if ( $this->latMin && $this->latMax && $this->lonMin && $this->lonMax) {
			$recherche['zone'] = array(
					'west' => $this->lonMin,
					'south' => $this->latMin,
					'east' => $this->lonMax,
					'north' => $this->latMax
			);
		}
	
		if ($this->filter_data_db) {
			$recherche['availability'] = dataset_json::WITH_INSERTED_DATA;
		} else if ($this->filter_data) {
			$recherche['availability'] = dataset_json::WITH_DATA;
		}
	
		return $recherche;
	}
	function toQueryArray(){
		$query_array = array();
	
		$query_array['terms'] = $this->keywords;
					
		if ($this->and_or == 'and'){
			$query_array['allKeywords'] = 1;
		}
		
		if ($this->date_begin) {
			$query_array['dtstart'] = $this->date_begin;
		}
		if ($this->date_end) {
			$query_array['dtend'] = $this->date_end;
		}
	
		if ($this->projectName){
			$query_array['project'] = $this->projectName;
		}
		
		if (isset ( $this->gcmd_variable ) && $this->gcmd_variable > 0){
			$gcmd = new gcmd_science_keyword();
			$k = $gcmd->getById($this->gcmd_variable);
			$query_array['parameter'] = $k->gcmd_name;
		}
		if (isset ( $this->gcmd_sensor ) && $this->gcmd_sensor > 0){
			$gcmd = new gcmd_instrument_keyword();
			$s = $gcmd->getById($this->gcmd_sensor);
			$query_array['instrument'] = $s->gcmd_sensor_name;
		}
	
		if ( $this->latMin && $this->latMax && $this->lonMin && $this->lonMax) {
			$query_array['bbox'] = "$this->lonMin,$this->latMin,$this->lonMax,$this->latMax";
		}
	
		if ($this->filter_data_db) {
			$query_array['availability'] = dataset_json::WITH_INSERTED_DATA;
		} else if ($this->filter_data) {
			$query_array['availability'] = dataset_json::WITH_DATA;
		}
	
		return $query_array;
	}
}
?>
