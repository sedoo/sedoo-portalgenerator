<?php
require_once ("forms/sat_form_simple.php");
require_once ("forms/validation.php");
require_once ("editDataset.php");

if ($project_name != MainProject) {
	if ($_SERVER ['HTTP_REFERER'] == constant ( strtolower ( $project_name ) . 'WebSite' )) {
		$_SESSION ['username'] = strtolower ( $project_name );
	}
} else {
	if ($_SERVER ['HTTP_REFERER'] == PORTAL_WebSite) {
		$_SESSION ['username'] = strtolower ( $project_name );
	}
}
$form = new sat_form_simple ();
$form->createLoginForm ();
if (isset ( $_REQUEST ['datsId'] ) && ! empty ( $_REQUEST ['datsId'] ))
	$datsId = $_REQUEST ['datsId'];
if (isset ( $_REQUEST ['requested'] ) && ! empty ( $_REQUEST ['requested'] ))
	$requested = $_REQUEST ['requested'];
if (! isset ( $datsId ) || empty ( $datsId )) {
	$datsId = $_SESSION ['datsId_tmp'];
	$_SESSION ['datsId_tmp'] = null;
}
// Creation et affichage du formulaire
if (isset ( $datsId ) && ! empty ( $datsId )) {
	$form->dataset = new dataset ();
	$form->dataset = $form->dataset->getById ( $datsId );
	$_SESSION ['datasetSat'] = serialize ( $form->dataset );
} else if (isset ( $_SESSION ['datasetSat'] )) {
	$form->dataset = unserialize ( $_SESSION ['datasetSat'] );
}
// user loggé
if ($form->isCat ( $form->dataset, $project_name )) {
	if (! isset ( $form->dataset )) {
		$form->dataset = new dataset ();
		$form->dataset = $form->dataset->getById ( 0 );
		$form->dataset->nbPis = 1;
		$form->dataset->nbSites = 2;
		$form->dataset->nbCalcVars = 0;
		$form->dataset->nbVars = 1;
		$form->dataset->nbFormats = 1;
		$form->dataset->nbProj = 1;
		$form->dataset->dats_id = 0;
	}
	$form->dataset->dataset_types = array ();
	$form->dataset->dataset_types [0] = new dataset_type ();
	$form->dataset->dataset_types [0] = $form->dataset->dataset_types [0]->getByType ( 'SATELLITE' );
	
	// TODO nettoyer
	
	// $form->dataset = $dataset;
	/*
	 * $nb_pi = & $form->dataset->nbPis; $nb_site = & $form->dataset->nbSites; $nb_variable = & $form->dataset->nbVars; $nb_variable_calcul = & $form->dataset->nbCalcVars;
	 */
	$form->createForm ();
	
	if (strpos ( $_SERVER ['REQUEST_URI'], '&datsId' )) {
		$reqUri = substr ( $_SERVER ['REQUEST_URI'], 10, strpos ( $_SERVER ['REQUEST_URI'], '&datsId' ) );
	} else {
		$reqUri = substr ( $_SERVER ['REQUEST_URI'], 10 );
	}
	
	if (isset ( $_POST ['bouton_add_instru'] )) {
		$form->saveForm ();
		$form->addSat ();
		$form->dataset->nbSites ++;
		$form->displayForm ();
		$_SESSION ['datasetSat'] = serialize ( $form->dataset );
	} else if (isset ( $_POST ['bouton_add_variable'] )) {
		$form->saveForm ();
		$form->dataset->nbVars ++;
		$form->addVariable ( $form->dataset->nbVars );
		$form->displayForm ();
		$_SESSION ['datasetSat'] = serialize ( $form->dataset );
	} else if (isset ( $_POST ['bouton_add_projet'] )) {
		$form->saveForm ();
		$form->dataset->nbProj ++;
		$form->addProjet ();
		$form->displayForm ();
		$_SESSION ['datasetSat'] = serialize ( $form->dataset );
	} else if (isset ( $_POST ['bouton_add_pi'] )) {
		$form->saveForm ();
		$form->dataset->nbPis ++;
		$form->addPi ( $form->dataset->nbPis );
		$form->displayForm ();
		$_SESSION ['datasetSat'] = serialize ( $form->dataset );
	} else if (isset ( $_POST ['bouton_save'] )) {
		$form->saveForm ();
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
				echo "<font size=\"3\" color='green'><b>The dataset has been succesfully inserted in the database</b></font><br>";
				
				$_SESSION ['datasetSat'] = null;
				editDataset ( $form->dataset->dats_id );
			} else {
				echo "<font size=\"3\" color='red'><b>An error occured during the insertion process.</b></font><br>";
				
				$dts = new dataset ();
				$dts->dats_id = $form->dataset->dats_id;
				if (! $dts->idExiste ()) {
					$form->dataset->dats_id = 0;
				}
				$form->displayForm ();
				$_SESSION ['datasetSat'] = serialize ( $form->dataset );
			}
		} else {
			$form->displayForm ();
			$_SESSION ['datasetSat'] = serialize ( $form->dataset );
		}
	} else
		$form->displayForm ();
} else {
	$form->displayLGForm ( "", true );
}

?>
