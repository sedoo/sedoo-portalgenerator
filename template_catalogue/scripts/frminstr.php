<?php
require_once ("bd/dataset.php");
require_once ("bd/personne.php");
require_once ("forms/instrument_form.php");
require_once ("editDataset.php");
require_once ('/sites/kernel/#MainProject/conf.php');
require_once ("forms/validation.php");
require_once ("forms/doi_form.php");
require_once ('xml/DoiXml.php');
require_once ('conf/doi.conf.php');
require_once ('scripts/doiUtils.php');
require_once ('mail.php');

function not_void($elements, $values) {
	foreach ( $elements as $elt ) {
		echo "- elt: " . $elt;
	}
	foreach ( $values as $val ) {
		echo "- val: " . $val;
	}
	
	return true;
}

/*
 * $elements : nom, type, west, east, south, north, alt min, alt max, env
 */
function validSite($element_names, $element_values) {
	$cpt = 0;
	foreach ( $element_values as $val ) {
		if (! empty ( $val ))
			$cpt ++;
	}
	
	if (validInterval ( array (), array_slice ( $element_values, 6, 2 ) ) && validBoundings ( array (), array_slice ( $element_values, 2, 4 ) )) {
	}
}
function distinct($element_names, $element_values) {
	sort ( $element_values );
	$valPrec = '';
	$distinct = true;
	
	foreach ( $element_values as $val ) {
		echo '- ' . $val . '.<br>';
		if (! empty ( $val )) {
			if (! empty ( $valPrec ) && ($valPrec == $val)) {
				$distinct = false;
				break;
			}
		}
		$valPrec = $val;
	}
	return $distinct;
}

/*
 * Teste si des entrées existent déjà dans la base value: valeurs à tester (séparées par des '; args : 0 -> table, 1 -> colonne
 */
function existInDb($element, $value, $args) {
	$values = split ( ";", $value );
	$result = true;
	foreach ( $values as $val ) {
		if (! empty ( $val ))
			$result = $result && existe ( $element, $val, $args );
	}
}

/*
 * Teste qu'un champ texte est saisi si une option a été choisie dans un select element: element liste sur lequel s'applique la regle value: valeur choisie dans la liste (0 => rien) args: array(0 => formulaire, 1 => champ texte à considérer
 */
function required_if_not_void($element, $value, $args) {

	$arg_value = $args [0]->exportValue ( $args [1] );
		
	if (empty ( $arg_value ) && $value != 0) {
		return false;
	} else
		return true;
}

/*
 * Teste qu'une option a été choisie dans un select si un champ texte n'est pas vide element: element liste sur lequel s'applique la regle value: valeur choisie dans la liste (0 => rien) args: array(0 => formulaire, 1 => champ texte à considérer
 */
function required_if_not_void2($element, $value, $args) {
	$arg_value = $args [0]->exportValue ( $args [1] );
		
	if (! empty ( $arg_value ) && $value == 0) {
		return false;
	} else
		return true;
}

/*
 * Teste qu'un champ texte est saisi si un
 * element: element liste sur lequel s'applique la regle
 * value: valeur saisie dans le champ texte
 * args: array(0 => formulaire, 1 => champ texte à considérer
 */
function required_if_not_void3($element, $value, $args) {
	$arg_value = $args[0]->exportValue($args[1]);
	if (empty($arg_value) && !empty($value)){
		return false;
	}else{
		return true;
	}
}

require_once 'upload.php';

$form = new instrument_form ();
$form->createLoginForm ();
if(isset($_REQUEST['requested']) && !empty($_REQUEST['requested']))
	$requested = $_REQUEST['requested'];
if(isset($_REQUEST ['datsId']) && !empty($_REQUEST ['datsId']))
	$datsId = $_REQUEST ['datsId'];
if (! isset ( $datsId ) || empty ( $datsId )) {
	$datsId = $_SESSION ['datsId_tmp'];
	$_SESSION ['datsId_tmp'] = null;
}

// Creation et affichage du formulaire
if (isset ( $datsId ) && ! empty ( $datsId )) {
	$form->dataset = new dataset ();
	$form->dataset = $form->dataset->getById ( $datsId );
	$_SESSION ['dataset'] = serialize ( $form->dataset );
} else if (isset ( $_SESSION ['dataset'] )) {
	$form->dataset = unserialize ( $_SESSION ['dataset'] );
}
if ($form->isCat ( $form->dataset,$project_name )) {
	if (isset($form->dataset->dats_doi) && !empty($form->dataset->dats_doi)){
		$xmldoi = createDoiXml($form->dataset->dats_id, $xmlstr, $project_name);
		$doms = new DOMDocument ( '1.0', 'UTF-8' );
		$doms->preserveWhiteSpace = false;
		$doms->formatOutput = true;
		$doms->loadXML ( $xmldoi->asXML () );
		$_SESSION['doi'] = $doms->saveXML ();
	}
	if (! isset ( $form->dataset )) {
		$form->dataset = new dataset ();
		$form->dataset = $form->dataset->getById ( 0 );
		$form->dataset->nbPis = 1;
		$form->dataset->nbSites = 1;
		$form->dataset->nbCalcVars = 1;
		$form->dataset->nbVars = 1;
		$form->dataset->nbFormats = 1;
		$form->dataset->nbProj = 1;
		$form->dataset->dats_id = 0;
	}
	
	// TODO nettoyer
	$nb_pi = & $form->dataset->nbPis;
	$nb_site = & $form->dataset->nbSites;
	$nb_variable = & $form->dataset->nbVars;
	$nb_variable_calcul = & $form->dataset->nbCalcVars;
	$form->createForm ( $project_name );
	
	if (isset ( $_POST ['upload_doc_button'] )) {
		$form->saveForm ($nb_pi, $nb_site, $nb_variable, $nb_variable_calcul);
		$form->dataset->attFile = uploadDoc ( "upload_doc" );
		$form->displayForm ($nb_pi, $nb_site, $nb_variable, $nb_variable_calcul);
		$_SESSION ['dataset'] = serialize ( $form->dataset );
	} else if (isset ( $_POST ['delete_doc_button'] )) {
		$form->saveForm ($nb_pi, $nb_site, $nb_variable, $nb_variable_calcul);
		if (isset ( $form->dataset->attFile ) && ! empty ( $form->dataset->attFile )) {
			unlink ( ATT_FILES_PATH . '/' . $form->dataset->attFile );
			$form->dataset->attFile = null;
		}
		$form->displayForm ($nb_pi, $nb_site, $nb_variable, $nb_variable_calcul);
		$_SESSION ['dataset'] = serialize ( $form->dataset );
	} else if (isset ( $_POST ['upload'] )) {
		$form->saveForm ( $nb_pi, $nb_site, $nb_variable, $nb_variable_calcul );
		$form->dataset->image = uploadImg ( "upload_image" );
		
		$form->displayForm ( $nb_pi, $nb_site, $nb_variable, $nb_variable_calcul );
		$_SESSION ['dataset'] = serialize ( $form->dataset );
	} else if (isset ( $_POST ['delete'] )) {
		$form->saveForm ( $nb_pi, $nb_site, $nb_variable, $nb_variable_calcul );
		if (isset ( $form->dataset->image ) && ! empty ( $form->dataset->image )) {
			unlink ( WEB_PATH . $form->dataset->image );
			$form->dataset->image = null;
		}
		$form->displayForm ( $nb_pi, $nb_site, $nb_variable, $nb_variable_calcul );
		$_SESSION ['dataset'] = serialize ( $form->dataset );
	} else if (isset ( $_POST ['bouton_add_pi'] )) {
		$form->saveForm ( $nb_pi, $nb_site, $nb_variable, $nb_variable_calcul );
		$form->dataset->nbPis ++;
		$form->addPi ( $nb_pi );
		$form->displayForm ( $nb_pi, $nb_site, $nb_variable, $nb_variable_calcul );
		$_SESSION ['dataset'] = serialize ( $form->dataset );
	} else if (isset ( $_POST ['bouton_add_site'] )) {
		$form->saveForm ( $nb_pi, $nb_site, $nb_variable, $nb_variable_calcul );
		$form->dataset->nbSites ++;
		$form->addSite ( $nb_site );
		$form->displayForm ( $nb_pi, $nb_site, $nb_variable, $nb_variable_calcul );
		$_SESSION ['dataset'] = serialize ( $form->dataset );
	} else if (isset ( $_POST ['bouton_add_variable'] )) {
		$form->saveForm ( $nb_pi, $nb_site, $nb_variable, $nb_variable_calcul );
		$form->dataset->nbVars ++;
		$form->addVariable ( $nb_variable );
		$form->displayForm ( $nb_pi, $nb_site, $nb_variable, $nb_variable_calcul );
		$_SESSION ['dataset'] = serialize ( $form->dataset );
	} else if (isset ( $_POST ['bouton_add_variable_calcul'] )) {
		$form->saveForm ( $nb_pi, $nb_site, $nb_variable, $nb_variable_calcul );
		$form->dataset->nbCalcVars ++;
		$form->addVariableCalcul ( $nb_variable_calcul );
		$form->displayForm ( $nb_pi, $nb_site, $nb_variable, $nb_variable_calcul );
		$_SESSION ['dataset'] = serialize ( $form->dataset );
	} else if (isset ( $_POST ['bouton_add_format'] )) {
		$form->saveForm ( $nb_pi, $nb_site, $nb_variable, $nb_variable_calcul );
		$form->dataset->nbFormats ++;
		$form->addFormat ();
		$form->displayForm ( $nb_pi, $nb_site, $nb_variable, $nb_variable_calcul );
		$_SESSION ['dataset'] = serialize ( $form->dataset );
	} else if (isset ( $_POST ['bouton_add_projet'] )) {
		$form->saveForm ( $nb_pi, $nb_site, $nb_variable, $nb_variable_calcul );
		$form->dataset->nbProj ++;
		$form->addProjet ();
		$form->displayForm ( $nb_pi, $nb_site, $nb_variable, $nb_variable_calcul );
		$_SESSION ['dataset'] = serialize ( $form->dataset );
	} else if (isset ( $_POST ['bouton_save'] )) {
		
		$form->saveForm ( $nb_pi, $nb_site, $nb_variable, $nb_variable_calcul );
		$form->addValidationRules ();
		
		if ($form->validate ()) {
			
			if ($form->dataset->dats_id == 0) {
				$insertionOk = $form->dataset->insert ();
				$form->dataset->set_requested ( $requested );
			} else {
				$insertionOk = $form->dataset->update ();
				$form->dataset->set_requested ( $requested );
			}
						
			if ($insertionOk) {
				
				$xml_save = createDoiXml ( $form->dataset->dats_id, $xmlstr, $project_name );
				$dom = new DOMDocument ( '1.0', 'UTF-8' );
				$dom->preserveWhiteSpace = false;
				$dom->formatOutput = true;
				$dom->loadXML ( $xml_save->asXML () );
				$xmld = $dom->saveXML ();

				if ($xmld != $_SESSION ['doi'] && isset($form->dataset->dats_doi) && !empty($form->dataset->dats_doi)) {

					$doi = $form->dataset->dats_doi;
					$dats_id = $form->dataset->dats_id;
					$url = 'http://'.$_SERVER['HTTP_HOST'].'/'. $project_name . "/?editDatsId=$dats_id";
					$xml = simplexml_load_string ( $xmld );
					if ($xml === false) {
						echo 'Erreur lors de l\'analyse du document';
						return;
					}
					
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
					$d = new doi_form();
					if($d->isNotValid($xmld, $project_name, $dats_id) == false){
					// Enregistrement dans la base datacite
					createDoi ( $doi, $url, $xmld );
					}else{
						$messageError = 'http://' . $_SERVER ['HTTP_HOST'] . '/' . $project_name . "/?editDatsId=$dats_id" . "\r\n";
						$messageError.= $d->isNotValid($xmld, $project_name, $dats_id);
						$mailsAdmins = strtolower($project_name)._AdminGroup_Email;
						$sujet = 'DOI XML - Errors were found during the update dataset';
						$text = $messageError;
						$doms->save("/tmp/current_".MainProject."-".$project_name.".".$dats_id.".xml");
						$dom->save("/tmp/new_".MainProject."-".$project_name.".".$dats_id.".xml");
						$attachments = array("/tmp/current_".MainProject."-".$project_name.".".$dats_id.".xml", "/tmp/new_".MainProject."-".$project_name.".".$dats_id.".xml");
						sendMail($mailsAdmins, $sujet, $text, $attachments);
					}
					
				}
				echo "<font size=\"3\" color='green'><b>Registration succesfull</b></font><br>";
				$_SESSION ['dataset'] = null;
				editDataset ( $form->dataset->dats_id, $project_name );
			} else {
				echo "<font size=\"3\" color='red'><b>An error occured during the insertion process.</b></font><br>";
				
				$dts = new dataset ();
				$dts->dats_id = $form->dataset->dats_id;
				if (! $dts->idExiste ()) {
					$form->dataset->dats_id = 0;
				}
				$form->displayForm ( $nb_pi, $nb_site, $nb_variable, $nb_variable_calcul );
				$_SESSION ['dataset'] = serialize ( $form->dataset );
			}
		} else {
			$form->displayForm ( $nb_pi, $nb_site, $nb_variable, $nb_variable_calcul );
			$_SESSION ['dataset'] = serialize ( $form->dataset );
		}
	} else {
		$form->displayForm ( $nb_pi, $nb_site, $nb_variable, $nb_variable_calcul );
	}
} else if ($form->isLogged ()) {
	
	echo "<a href='/$project_name/'>&lt;&lt;&nbsp;Return</a><br/>";
	echo "<center><img src='/img/interdit.png' heigth='50' width='50' /></center>";
	echo "<br/><font size=\"3\" color='red'><center><b>You cannot modify this dataset.</b></center></font><br/>";
} else {
	$form->displayLGForm ( "", true );
}

?>
