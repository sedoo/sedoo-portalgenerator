<?php
require_once 'extract/requeteXml.php';
require_once 'extract/sortieCGI.php';
require_once 'search_form.php';
require_once 'map_form.php';
require_once 'bd/param.php';
require_once 'bd/dataset.php';
require_once 'bd/inserted_dataset.php';
require_once 'sortie/print_utils.php';
require_once ('lstDataUtils.php');
require_once ("validation.php");
require_once ("utils/datepicker_utils.php");
require_once ("scripts/mail.php");
class extraction_form extends map_form {
	var $projectName;
	var $requete;
	var $msg;
	var $datsId;
	var $search;
	var $minValidDate = null;
	var $maxValidDate = null;
	function createForm($projectName, $dats_id = null, $search = 0) {
		$this->projectName = $projectName;
		$this->search = $search;
		if (isset ( $_SESSION ['loggedUser'] )) {
			$this->user = unserialize ( $_SESSION ['loggedUser'] );
			$this->requete = new requeteXml ( $this->user, $projectName );
			if ($search) {
				if (isset ( $_SESSION ['requete_search_form'] )) {
					$requete = unserialize ( $_SESSION ['requete_search_form'] );
					$this->requete->latMin = round ( $requete->latMin / 10000.0, 5 );
					$this->requete->latMax = round ( $requete->latMax / 10000.0, 5 );
					$this->requete->lonMin = round ( $requete->lonMin / 10000.0, 5 );
					$this->requete->lonMax = round ( $requete->lonMax / 10000.0, 5 );
					$this->requete->dateMin = $requete->date_begin;
					$this->requete->dateMax = $requete->date_end;
					$gcmd_id = $requete->gcmd_variable;
				}
			}
			if (isset ( $dats_id )) {
				$dats_ids = $dats_id;
				$this->datsId = $dats_id;
			} else if (isset ( $_SESSION ['result_search_form_datasets'] )) {
				$datasets = unserialize ( $_SESSION ['result_search_form_datasets'] );
				$dats_ids = implode ( ", ", $datasets );
			}
			$this->registerRule ( 'validDate', 'function', 'validDate' );
			$this->registerRule ( 'validPeriod', 'function', 'validPeriod' );
			$this->registerRule ( 'validInterval', 'function', 'validInterval' );
			$this->createFormParams ( $gcmd_id, $dats_ids );
			$this->createFormDatasets ( $dats_ids );
			$this->createFormCompression ();
			$this->createFormFormat ();
			$this->createFormFormatOption ();
			$this->createFormFlag ();
			$this->createFormDelta ();
			if ($search)
				$this->createFormZone ();
			else {
				$this->createFormMap ();
				$this->addValidationRulesMap ();
			}
			$this->createFormPeriod ();
			
			$this->addElement ( 'submit', 'bouton_submit', 'Submit' );
		} else {
			$this->createLoginForm ( 'Mail' );
		}
	}
	function createFormDatasets($dats_ids) {
		if (isset ( $dats_ids ) && ! empty ( $dats_ids )) {
			$query = "SELECT dats_id, dats_title FROM dataset where dats_id IN ($dats_ids);";
		} else {
			$query = "SELECT dats_id, dats_title FROM dataset where dats_id IN (SELECT dats_id FROM dats_data);";
		}
		$dts = new dataset ();
		$liste = $dts->getOnlyTitles ( $query );
		$array = array ();
		for($i = 0; $i < count ( $liste ); $i ++) {
			$id = $liste [$i]->dats_id;
			$array [$id] = $liste [$i]->dats_title;
		}
		$select = $this->createElement ( 'select', 'dataset', 'Dataset(s)', $array, array (
				'style' => 'width:400px;' 
		) );
		$select->setMultiple ( true );
		$select->setSize ( 5 );
		$this->addElement ( $select );
		$this->requete->datasets = array_keys ( $array );
		if (isset ( $this->datsId ) && $this->datsId > 0) {
			$this->getElement ( 'dataset' )->setValue ( array (
					$this->datsId 
			) );
			$this->getElement ( 'dataset' )->freeze ();
			$this->getElement ( 'dataset' )->setLabel ( "Selected dataset" );
		}
	}
	function createFormParams($gcmd_id, $dats_ids) {
		$p = new param ();
		if (isset ( $gcmd_id ) && $gcmd_id > 0) {
			$gcmd = new gcmd_science_keyword ();
			$gcmd = $gcmd->getById ( $gcmd_id );
			$gcmd_ids = $gcmd_id;
			$liste = $gcmd->getChildren ( true );
			foreach ( $liste as $g ) {
				$gcmd_ids .= ',' . $g->gcmd_id;
			}
			$liste = $p->getByGcmdId ( $gcmd_ids );
		} else if (isset ( $dats_ids ) && ! empty ( $dats_ids ))
			$liste = $p->getByDatsId ( $dats_ids );
		else
			$liste = $p->getAll ();
		$array = array ();
		for($i = 0; $i < count ( $liste ); $i ++) {
			$id = $liste [$i]->var_id;
			$array [$id] = printParamName ( $liste [$i]->var );
		}
		asort ( $array );
		$select = $this->createElement ( 'select', 'param', 'Parameters', $array, array (
				'style' => 'width:400px;' 
		) );
		$select->setMultiple ( true );
		$select->setSize ( 8 );
		$this->addElement ( $select );
		$this->requete->variables = array_keys ( $array );
		if (count ( $array ) == 1) {
			$this->getElement ( 'param' )->setValue ( array (
					$liste [0]->var_id 
			) );
			$this->getElement ( 'param' )->freeze ();
		}
	}
	function createFormPeriod() {
		if ($this->search) {
			$this->addElement ( 'hidden', 'date_min' );
			$this->addElement ( 'hidden', 'date_max' );
		} else {
			$this->addElement ( 'text', 'date_min', 'Date min (yyyy-mm-dd)', array (
					'size' => 10,
					'id' => 'date_min' 
			) );
			$this->addElement ( 'text', 'date_max', 'Date max (yyyy-mm-dd)', array (
					'size' => 10,
					'id' => 'date_max' 
			) );
			$this->addRule ( 'date_min', 'Date min is not a date', 'validDate' );
			$this->addRule ( 'date_max', 'Date max is not a date', 'validDate' );
		}
		if ($this->search) {
			$defaultValues ['date_min'] = $this->requete->dateMin;
			$defaultValues ['date_max'] = $this->requete->dateMax;
		} else {
			$this->minValidDate = null;
			$this->maxValidDate = null;
			if (isset ( $this->datsId ) && $this->datsId > 0) {
				$insD = new inserted_dataset ();
				$insDatasets = $insD->getByDatsId ( $this->datsId );
				foreach ( $insDatasets as $insDataset ) {
					if (($this->minValidDate == null) || ($insDataset->date_min < $this->minValidDate)) {
						$this->minValidDate = $insDataset->date_min;
					}
					if (($this->maxValidDate == null) || ($insDataset->date_max > $this->maxValidDate)) {
						$this->maxValidDate = $insDataset->date_max;
					}
				}
			}
			$defaultValues ['date_min'] = $this->minValidDate;
			$defaultValues ['date_max'] = $this->maxValidDate;
		}
		$this->setDefaults ( $defaultValues );
	}
	function createFormZone() {
		$this->addElement ( 'hidden', 'lon_min', 'West bounding coordinate (°)', array (
				'size' => 3 
		) );
		$this->addElement ( 'hidden', 'lon_max', 'East bounding coordinate (°)', array (
				'size' => 3 
		) );
		$this->addElement ( 'hidden', 'lat_min', 'South bounding coordinate (°)', array (
				'size' => 3 
		) );
		$this->addElement ( 'hidden', 'lat_max', 'North bounding coordinate (°)', array (
				'size' => 3 
		) );
		if (! $this->search) {
			$this->addRule ( 'lat_min', 'South bounding coordinate must be numeric', 'numeric' );
			$this->addRule ( 'lat_min', 'South bounding coordinate is incorrect', 'number_range', array (
					- 90,
					90 
			) );
			$this->addRule ( 'lat_max', 'North bounding coordinate must be numeric', 'numeric' );
			$this->addRule ( 'lat_max', 'North bounding coordinate is incorrect', 'number_range', array (
					- 90,
					90 
			) );
			$this->addRule ( 'lon_min', 'West bounding coordinate must be numeric', 'numeric' );
			$this->addRule ( 'lon_min', 'West bounding coordinate is incorrect', 'number_range', array (
					- 180,
					180 
			) );
			$this->addRule ( 'lon_max', 'East bounding coordinate  must be numeric', 'numeric' );
			$this->addRule ( 'lon_max', 'East bounding coordinate is incorrect', 'number_range', array (
					- 180,
					180 
			) );
			
			$this->addRule ( array (
					'lat_min',
					'lat_max' 
			), 'North bounding coordinate must be greater than South bounding coordinate', 'validInterval' );
			$this->addRule ( array (
					'lon_min',
					'lon_max' 
			), 'East bounding coordinate must be greater than West bounding coordinate', 'validInterval' );
		}
		$defaultValues ['lon_min'] = $this->requete->lonMin;
		$defaultValues ['lon_max'] = $this->requete->lonMax;
		$defaultValues ['lat_min'] = $this->requete->latMin;
		$defaultValues ['lat_max'] = $this->requete->latMax;
		$this->setDefaults ( $defaultValues );
		$this->getElement ( 'lon_min' )->freeze ();
		$this->getElement ( 'lon_max' )->freeze ();
		$this->getElement ( 'lat_min' )->freeze ();
		$this->getElement ( 'lat_max' )->freeze ();
	}
	function createFormFlag() {
		$options [] = & HTML_QuickForm::createElement ( 'radio', null, null, '&nbsp;yes', 1 );
		$options [] = & HTML_QuickForm::createElement ( 'radio', null, null, '&nbsp;no', 0 );
		$this->addGroup ( $options, 'flag', 'Quality flag', '&nbsp;&nbsp;' );
		$defaultValues ['flag'] = $this->requete->withFlag;
		$this->setDefaults ( $defaultValues );
	}
	function createFormDelta() {
		$options [] = & HTML_QuickForm::createElement ( 'radio', null, null, '&nbsp;yes', 1 );
		$options [] = & HTML_QuickForm::createElement ( 'radio', null, null, '&nbsp;no', 0 );
		$this->addGroup ( $options, 'delta', 'Uncertainty', '&nbsp;&nbsp;' );
		$defaultValues ['delta'] = $this->requete->withDelta;
		$this->setDefaults ( $defaultValues );
	}
	function createFormCompression() {
		$options [] = & HTML_QuickForm::createElement ( 'radio', null, null, '&nbsp;zip', 'zip' );
		$options [] = & HTML_QuickForm::createElement ( 'radio', null, null, '&nbsp;gzip', 'gzip' );
		$this->addGroup ( $options, 'compression', 'Compression', '&nbsp;&nbsp;' );
		$defaultValues ['compression'] = $this->requete->compression;
		$this->setDefaults ( $defaultValues );
	}
	function createFormFormat() {
		$options [] = & HTML_QuickForm::createElement ( 'radio', null, null, '&nbsp;Ascii (Nasa Ames)', 'ames' );
		$options [] = & HTML_QuickForm::createElement ( 'radio', null, null, '&nbsp;netCdf (available soon)', 'netcdf', array (
				'disabled' => 'true' 
		) );
		$this->addGroup ( $options, 'format', 'Format', '&nbsp;&nbsp;' );
		$defaultValues ['format'] = $this->requete->format;
		$this->setDefaults ( $defaultValues );
	}
	function createFormFormatOption() {
		if (isset ( $this->datsId ) && $this->datsId > 0) {
			// Requete sur un seul jeu
			$options [] = & HTML_QuickForm::createElement ( 'radio', null, null, '&nbsp;yes', '1001m', array (
					'disabled' => 'true' 
			) );
			$options [] = & HTML_QuickForm::createElement ( 'radio', null, null, '&nbsp;no', '2160' );
			$this->addGroup ( $options, 'format_version', 'Split result by station (available soon) ?', '&nbsp;&nbsp;' );
			$defaultValues ['format_version'] = $this->requete->format_version;
			$this->setDefaults ( $defaultValues );
		} else {
			$options [] = & HTML_QuickForm::createElement ( 'radio', null, null, '&nbsp;yes', '2160' );
			$options [] = & HTML_QuickForm::createElement ( 'radio', null, null, '&nbsp;no', '1001' );
			$this->addGroup ( $options, 'format_version', 'Split result by dataset ?', '&nbsp;&nbsp;' );
			$defaultValues ['format_version'] = $this->requete->format_version;
			$this->setDefaults ( $defaultValues );
		}
	}
	function saveRequete() {
		$this->requete->format = $_POST ['format'];
		$this->requete->format_version = $_POST ['format_version'];
		$this->requete->compression = $_POST ['compression'];
		if (isset ( $_POST ['param'] ) && ! empty ( $_POST ['param'] ))
			$this->requete->variables = $_POST ['param'];
		$this->requete->dateMin = $_POST ['date_min'];
		$this->requete->dateMax = $_POST ['date_max'];
		if (! $this->search) {
			$this->saveFormMap ();
			$this->requete->latMin = $this->latMin / 10000.0;
			$this->requete->latMax = $this->latMax / 10000.0;
			$this->requete->lonMin = $this->lonMin / 10000.0;
			$this->requete->lonMax = $this->lonMax / 10000.0;
		}
		if (isset ( $_POST ['dataset'] ) && ! empty ( $_POST ['dataset'] ))
			$this->requete->datasets = $_POST ['dataset'];
		$this->requete->withFlag = $_POST ['flag'];
		$this->requete->withDelta = $_POST ['delta'];
		$_SESSION ['requete_xml'] = serialize ( $this->requete );
	}
	function send() {
		if (send_to_cgi ( $this->requete->toXml (), $retour )) {
			$elts = explode ( ':', $retour );
			if ($elts [0] == '00') {
				$this->msg = "Request successfully sent. The result will be send to you by email.";
			} else if ($elts [0] == '15') {
				$this->_errors [] = "No data is corresponding to this request.";
			} else {
				$this->_errors [] = 'Your Request was not processed due to technical reasons. Please contact the database administrator (' . ROOT_EMAIL . ').';
				$this->sendMailErreur ( $retour );
			}
		} else {
			$this->_errors [] = 'Your Request was not processed due to technical reasons. Please contact the database administrator (' . ROOT_EMAIL . ').';
			$this->sendMailErreur ( $retour );
		}
	}
	function sendMailErreur($error) {
		sendMailSimple ( ROOT_EMAIL, '[' . MainProject . '-DATABASE] Error', $error );
	}
	function displayForm() {
		if (! $this->search) {
			DatePickerUtils::addScriptPeriod ( 'date_min', 'date_max', $this->minValidDate, $this->maxValidDate );
		}
		echo '<h1>Data selection</h1>';
		if ($this->search) {
			echo "<br /><a href='/$this->projectName/Search-result' style='font-size:110%;font-weight:bold;'>&lt;&lt;&nbsp;Back to search result</a><br /><br />";
		}
		foreach ( $this->_errors as $error ) {
			echo '<font size="3" color="red">' . $error . '</font><br>';
		}
		if (isset ( $this->msg ))
			echo "<font size=\"3\" color='green'><b>$this->msg</b></font><br>";
		echo '<form action="" method="post" name="frmmap" id="frmmap" >';
		echo "<table>";
		echo "<tr><th colspan='4' align='center'>Data</th></tr>";
		if (isset ( $this->datsId ) && $this->datsId > 0)
			echo '<tr><td><b>' . $this->getElement ( 'dataset' )->getLabel () . '</b></td><td colspan="3">' . $this->getElement ( 'dataset' )->toHTML () . '</td></tr>';
		echo '<tr><td><b>' . $this->getElement ( 'param' )->getLabel () . '</b></td><td colspan="3">' . $this->getElement ( 'param' )->toHTML () . '</td></tr>';
		echo '<tr><td><b>' . $this->getElement ( 'flag' )->getLabel () . '</b></td><td>' . $this->getElement ( 'flag' )->toHTML () . '</td>';
		echo '<td><b>' . $this->getElement ( 'delta' )->getLabel () . '</b></td><td>' . $this->getElement ( 'delta' )->toHTML () . '</td></tr>';
		if ($this->search) {
		} else {
			echo "<tr><th colspan='4' align='center'>Period</th></tr>";
			echo '<tr><td><b>' . $this->getElement ( 'date_min' )->getLabel () . '</b></td><td align="right">' . $this->getElement ( 'date_min' )->toHTML () . '</td>';
			echo '<td><b>' . $this->getElement ( 'date_max' )->getLabel () . '</b></td><td align="right">' . $this->getElement ( 'date_max' )->toHTML () . '</td></tr>';
		}
		if ($this->search) {
		} else {
			echo "<tr><th colspan='4' align='center'>Zone</th></tr>";
			echo "<tr><td colspan='4'>";
			$this->displayFormMap ();
			echo '</td></tr>';
		}
		echo "<tr><th colspan='4' align='center'>Options</th></tr>";
		echo '<tr><td><b>' . $this->getElement ( 'format' )->getLabel () . '</b></td><td colspan="3">' . $this->getElement ( 'format' )->toHTML () . '</td></tr>';
		echo '<tr><td><b>' . $this->getElement ( 'format_version' )->getLabel () . '</b></td><td colspan="3">' . $this->getElement ( 'format_version' )->toHTML () . '</td></tr>';
		echo '<tr><td><b>' . $this->getElement ( 'compression' )->getLabel () . '</b></td><td colspan="3">' . $this->getElement ( 'compression' )->toHTML () . '</td></tr>';
		echo '<tr><th colspan="4" align="center">' . $this->getElement ( 'bouton_submit' )->toHTML () . '</th></tr>';
		echo "</table>";
		if ($this->search) {
			echo $this->getElement ( 'date_min' )->toHTML ();
			echo $this->getElement ( 'date_max' )->toHTML ();
			echo $this->getElement ( 'lon_min' )->toHTML ();
			echo $this->getElement ( 'lon_max' )->toHTML ();
			echo $this->getElement ( 'lat_min' )->toHTML ();
			echo $this->getElement ( 'lat_max' )->toHTML ();
		}
		echo '</form>';
	}
}

?>
