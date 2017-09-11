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
	var $multipleDatasets;
	var $search;
	var $minValidDate = null;
	var $maxValidDate = null;
	function createForm($projectName, $dats_id = null, $search = 0) {
		$this->projectName = $projectName;
		$this->search = $search;
		if (isset ( $_SESSION ['loggedUser'] )) {
			$this->user = unserialize ( $_SESSION ['loggedUser'] );
			$this->requete = new requeteXml ( $this->user, $projectName );
			
			$withBbox = false;
			
			if ($search) {

				if ( array_key_exists('bbox',$_REQUEST) ){
					if (!empty($_REQUEST['bbox'])){
						$coords = explode(',', $_REQUEST['bbox']);
						$this->requete->latMin = $coords[1];
						$this->requete->latMax = $coords[3];
						$this->requete->lonMin = $coords[0];
						$this->requete->lonMax = $coords[2];
						$withBbox = true;
					}
				}

				if ( array_key_exists('dtstart',$_REQUEST) ){
					if (!empty($_REQUEST['dtstart'])){
						$this->requete->dateMin = $_REQUEST['dtstart'];
					}
				}
				
				if ( array_key_exists('dtend',$_REQUEST) ){
					if (!empty($_REQUEST['dtend'])){
						$this->requete->dateMax = $_REQUEST['dtend'];
					}
				}
				
			}
			if (isset ( $dats_id )) {
				$dats_ids = $dats_id;
			} else if (!empty($_REQUEST['searchDatsIds']) ) {
				$dats_ids = $_REQUEST['searchDatsIds']; 
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
			
			if ($withBbox){
				$this->createFormMap ($this->requete->latMin, $this->requete->latMax,
					$this->requete->lonMin,	$this->requete->lonMax);
			}else{
				$this->createFormMap ();
			}
			$this->addValidationRulesMap ();

			$this->createFormPeriod ($dats_ids);
			
			$this->addElement ( 'submit', 'bouton_submit', 'Submit' );
		} else {
			$this->createLoginForm ( 'Mail' );
		}
	}
	function createFormDatasets($dats_ids) {
		if (isset ( $dats_ids ) && ! empty ( $dats_ids )) {
			$query = "SELECT dats_id, dats_title FROM dataset where dats_id IN ($dats_ids) ORDER BY dats_title;";
		} else {
			$query = "SELECT dats_id, dats_title FROM dataset where dats_id IN (SELECT dats_id FROM dats_data) ORDER BY dats_title;";
		}
		$dts = new dataset ();
		$liste = $dts->getOnlyTitles ( $query );
		$array = array ();
		for($i = 0; $i < count ( $liste ); $i ++) {
			$id = $liste [$i]->dats_id;
			$array [$id] = $liste [$i]->dats_title;
		}
		if ( !isset ( $this->datsId ) && count($liste) == 1){
			$this->datsId = $liste[0]->dats_id;
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
	function createFormPeriod( $dats_ids ) {

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

		
		$this->minValidDate = null;
		$this->maxValidDate = null;
		$insD = new inserted_dataset ();
		$insDatasets = $insD->getByDatsIds ( $dats_ids );
		foreach ( $insDatasets as $insDataset ) {
			if (($this->minValidDate == null) || ($insDataset->date_min < $this->minValidDate)) {
				$this->minValidDate = $insDataset->date_min;
			}
			if (($this->maxValidDate == null) || ($insDataset->date_max > $this->maxValidDate)) {
				$this->maxValidDate = $insDataset->date_max;
			}
		}

		$defaultValues ['date_min'] = null;
		$defaultValues ['date_max'] = null;
			
		if (isset (  $this->requete->dateMin ) && !empty( $this->requete->dateMin) ) {
			$defaultValues ['date_min'] = $this->requete->dateMin;
		}else{
			$defaultValues ['date_min'] = $this->minValidDate;
		}
		if (isset (  $this->requete->dateMax ) && !empty( $this->requete->dateMax) ) {
			$defaultValues ['date_max'] = $this->requete->dateMax;
		}else{
			$defaultValues ['date_max'] = $this->maxValidDate;
		}
		
		$this->setDefaults ( $defaultValues );
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
		$options [] = & HTML_QuickForm::createElement ( 'radio', null, null, '&nbsp;netCdf', 'netcdf');
		$this->addGroup ( $options, 'format', 'Format', '&nbsp;&nbsp;' );
		$defaultValues ['format'] = $this->requete->format;
		$this->setDefaults ( $defaultValues );
	}
	function createFormFormatOption() {
		if (isset ( $this->datsId ) && $this->datsId > 0) {
			// Requete sur un seul jeu
			$options [] = & HTML_QuickForm::createElement ( 'radio', null, null, '&nbsp;yes', '1001m' );
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
		$this->requete->format_version = '1001m';
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
		DatePickerUtils::addScriptPeriod ( 'date_min', 'date_max', $this->minValidDate, $this->maxValidDate );
		
		echo '<h1>Data selection</h1>';
		if ($this->search) {
			ElasticSearchUtils::addBackToSearchResultLink();
		}
		foreach ( $this->_errors as $error ) {
			echo '<font size="3" color="red">' . $error . '</font><br>';
		}
		if (isset ( $this->msg ))
			echo "<font size=\"3\" color='green'><b>$this->msg</b></font><br>";
		echo '<form action="" method="post" name="frmmap" id="frmmap" >';
		echo "<table>";
		echo "<tr><th colspan='4' align='center'>Data</th></tr>";
		echo '<tr><td><b>' . $this->getElement ( 'dataset' )->getLabel () . '</b></td><td colspan="3">' . $this->getElement ( 'dataset' )->toHTML () . '</td></tr>';
		echo '<tr><td><b>' . $this->getElement ( 'param' )->getLabel () . '</b></td><td colspan="3">' . $this->getElement ( 'param' )->toHTML () . '</td></tr>';
		echo '<tr><td><b>' . $this->getElement ( 'flag' )->getLabel () . '</b></td><td>' . $this->getElement ( 'flag' )->toHTML () . '</td>';
		echo '<td><b>' . $this->getElement ( 'delta' )->getLabel () . '</b></td><td>' . $this->getElement ( 'delta' )->toHTML () . '</td></tr>';
		echo "<tr><th colspan='4' align='center'>Period</th></tr>";
		echo '<tr><td><b>' . $this->getElement ( 'date_min' )->getLabel () . '</b></td><td align="right">' . $this->getElement ( 'date_min' )->toHTML () . '</td>';
		echo '<td><b>' . $this->getElement ( 'date_max' )->getLabel () . '</b></td><td align="right">' . $this->getElement ( 'date_max' )->toHTML () . '</td></tr>';
		echo "<tr><th colspan='4' align='center'>Zone</th></tr>";
		echo "<tr><td colspan='4'>";
		$this->displayFormMap ();
		echo '</td></tr>';
		echo "<tr><th colspan='4' align='center'>Options</th></tr>";
		echo '<tr><td><b>' . $this->getElement ( 'format' )->getLabel () . '</b></td><td colspan="3">' . $this->getElement ( 'format' )->toHTML () . '</td></tr>';
		echo '<tr><td><b>' . $this->getElement ( 'compression' )->getLabel () . '</b></td><td colspan="3">' . $this->getElement ( 'compression' )->toHTML () . '</td></tr>';
		echo '<tr><th colspan="4" align="center">' . $this->getElement ( 'bouton_submit' )->toHTML () . '</th></tr>';
		echo "</table>";
		echo '</form>';
	}
}

?>
