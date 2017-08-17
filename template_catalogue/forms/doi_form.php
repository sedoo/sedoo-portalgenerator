<?php
require_once ('forms/login_form.php');
require_once ("bd/dataset.php");
require_once ('scripts/doiUtils.php');
require_once ('xml/DoiXml.php');
require_once ('scripts/filtreProjets.php');
class doi_form extends login_form {
	var $datasetxml;
	var $datasetxmlerror;
	function createForm($project_name) {
		if (isset ( $_SESSION ['loggedUser'] )) {
			$this->user = unserialize ( $_SESSION ['loggedUser'] );
			// echo 'loggedUser trouvé dans la session<br>';
			// echo 'type: '.get_class($this->user).'<br>';
		}
		
		if ($this->isRoot ()) {
			$this->createDoiForm ( $project_name );
		} else {
			$this->createLoginForm ( 'Mail' );
		}
	}
	function isNotValid($xml, $project_name, $id) {
		$xmle = simplexml_load_string ( $xml );
		
		$var = "";
		
		$count = 0;
		foreach ( $xmle->relatedIdentifiers->relatedIdentifier as $RI ) {
			if ($RI != '') {
				$count ++;
			}
		}
		if ($count == 0)
			$var .= "- Program website is missing \n";
		
		$count = 0;
		foreach ( $xmle->titles->title as $title ) {
			if ($title [0] != '') {
				$count ++;
			}
		}
		if ($count == 0)
			$var .= "- Dataset name is missing \n";
		
		$count = 0;
		foreach ( $xmle->subjects->subject as $subject ) {
			if ($subject [0] != '') {
				$count ++;
			}
		}
		if ($count == 0)
			$var .= "- Parameters names are missing \n";
		
		$count = 0;
		foreach ( $xmle->contributors->contributor as $contrib ) {
			if ($contrib [0] != '') {
				$count ++;
			}
		}
		if ($count == 0)
			$var .= "- E-mail contact is missing \n";
		
		$count = 0;
		foreach ( $xmle->rightsList->rights as $rights ) {
			if ($rights [0] != '') {
				$count ++;
			}
		}
		if ($count == 0)
			$var .= "- Use constraints are missing \n";
		
		$count = 0;
		foreach ( $xmle->geoLocations->geoLocation as $geoLoc ) {
			if ($geoLoc [0] != '') {
				$count ++;
			}
		}
		
		if ($count == 0)
			$var .= "- Location name or location point is missing \n";
		
		if ($xmle->identifier [0] == '') {
			$var .= "- identifier is missing \n";
		}
		
		if ($xmle->publicationYear [0] == '') {
			$var .= "- Year supply data is missing \n";
		}
		
		return $var;
	}
	
	
function displaydoixml($dats_id,$xmlstr, $project_name){
		$projects = getProjects($dats_id);
		$Doi ['projects'] = getProjectsName($projects);
        $varxml = createDoiXml ( $dats_id, $xmlstr, $project_name );
        $liste =
        $dom = new DOMDocument ( '1.0', 'UTF-8' );
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML ( $varxml->asXML () );       
        $test = $dom->saveXML ();
        $Doi['xml']= $test;
        $Doi['xml_error'] = $this->isNotValid ( $Doi['xml'], $project_name, $dats_id );
        return $Doi;
    }
	
	
	function createDoiForm($project_name) {
		$dts = new dataset ();
		$projects = 'SELECT DISTINCT dats_id FROM dats_proj WHERE project_id IN (' . get_filtre_projets ( $project_name ) . ')';
		
		$query = "SELECT dats_id, dats_title FROM dataset WHERE dats_id IN ($projects) AND dats_doi IS NULL ORDER BY dats_title";
		$liste = $dts->getOnlyTitles ( $query );
		$datasetxml [0] = "";
		$this->datasetxmlerror = array ();
		$varxml;
		$this->datasetxml [0] = "";
		$this->projet=array();
		$array [0] = "------------------ Dataset List ------------------";
		for($i = 0; $i < count ( $liste ); $i ++) {
			$j = $liste [$i]->dats_id;
			$array [$j] = $liste [$i]->dats_title;	
		}

		$select = $this->createElement ( 'select', 'dataset', 'Dataset', $array, array (
				'style' => 'width:400px;',
				'onchange' => "ShowDoiXML('metadata','error', this.form.dataset.value,'doi','$project_name');"
		) );
		
		$this->addElement ( $select );
		
		
		$text = $this->addElement ( 'text', 'doi', 'DOI', array (
				'size' => 50 
		) );
		$text->setValue ( '' );
		$this->addRule ( 'doi', 'DOI is required', 'required' );
		
		$metadata = $this->addElement ( 'textarea', 'metadata', 'Metadata (xml)', array (
				'cols' => 80,
				'rows' => 20 
		) );
		$metadata->setValue ( '' );
		$this->addRule ( 'metadata', 'saisir doi', 'required' );
		$this->addRule ( 'metadata', 'Metadata is required', 'required' );
		
		$error = $this->addElement ( 'textarea', 'error', 'Error', array (
				'cols' => 80,
				'rows' => 10 
		) );
		$error->setValue ( '' );
		//$this->addRule ( 'error', 'Metadata is required' );
		
		$this->addElement ( 'submit', 'bouton_ok', 'Create DOI' );
	}
	function registerDoi($project_name) {
		
		// print_r($_POST);
		$doi = DOI_PREFIX . $_POST ['doi'];
		$dats_id = $_POST ['dataset'];
		$metadata = $_POST ['metadata'];
		$url = "http://".$_SERVER['HTTP_HOST']."/" . $project_name . "/?editDatsId=$dats_id";
		// echo "<br/><br/>";
		// echo "$metadata<br/>";
		// error_log($metadata);
		// echo "<br/><br/>";
		
		$xml = simplexml_load_string ( $metadata );
		if ($xml === false) {
			echo 'Erreur lors de l\'analyse du document';
			return;
		}
		
		// echo "<br/>";
		
		// print_r($xml);
		
		$domElt = dom_import_simplexml ( $xml );
		if (! $domElt) {
			echo 'Erreur lors de la conversion du XML';
			return;
		}
		$dom = new DOMDocument ( '1.0', 'UTF-8' );
		$domElt = $dom->importNode ( $domElt, true );
		$domElt = $dom->appendChild ( $domElt );
		// Validation du document XML
		libxml_disable_entity_loader ( false );
		$validate = $dom->schemaValidate ( "http://schema.datacite.org/meta/kernel-3/metadata.xsd" );
		
		if ($validate) {
			echo '<br/>' . '<br/>';
			echo "Le fichier respecte le schema XML<br/>";
		} else {
			echo '<br/>' . '<br/>';
			echo "Le fichier ne respecte pas le schema XML<br/>";
			return;
		}
		
		echo "REGISTER: $doi => $url";
		// $dom->formatOutput = true;
		// $metadata = $xml->asXML();
		
		// Enregistrement dans la base datacite
		createDoi ( $doi, $url, $metadata );
		
		// Enregistrement dans la base mistrals
		$bd = new bdConnect ();
		$bd->db_open ();
		$query = "UPDATE dataset SET dats_doi = '$doi' WHERE dats_id = $dats_id;";
		echo $query . '<br>';
		$bd->exec ( $query );
		$bd->db_close ();
		
		$element = & $this->getElement ( 'dataset' );
		$element->setSelected ( 0 );
		$options = & $element->_options;
		foreach ( $options as $indice => $option ) {
			if ($option ['attr'] ['value'] == $dats_id) {
				unset ( $options [$indice] );
				break;
			}
		}
	}
	function displayDoiForm() {
		$reqUri = $_SERVER ['REQUEST_URI'];
		
		echo '<SCRIPT LANGUAGE="Javascript" SRC="/js/functions.js"> </SCRIPT>';
		
		echo '<form action="' . $reqUri . '" method="post" id="frmdoi" name="frmdoi" >';
		echo '<table>';
		echo '<tr><td><b>' . $this->getElement ( 'dataset' )->getLabel () . '</b></td><td>' . $this->getElement ( 'dataset' )->toHTML () . '</td></tr>';
		echo '<tr><td><b>' . $this->getElement ( 'doi' )->getLabel () . '</b></td><td>' . DOI_PREFIX . $this->getElement ( 'doi' )->toHTML () . '</td></tr>';
		echo '<tr><td><b>' . $this->getElement ( 'metadata' )->getLabel () . '</b></td><td>' . $this->getElement ( 'metadata' )->toHTML () . '</td></tr>';
		echo '<tr><td><b>' . $this->getElement ( 'error' )->getLabel () . '</b></td><td>' . $this->getElement ( 'error' )->toHTML () . '</td></tr>';
		echo '<tr><td colspan="2" align="center">' . $this->getElement ( 'bouton_ok' )->toHTML () . '</td></tr>';
		echo '</table>';
		echo '</form>';
	}
}
?>
