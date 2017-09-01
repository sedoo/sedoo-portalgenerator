<?php
require_once ("forms/sat_form_simple.php");
require_once ("forms/validation.php");
require_once ("editDataset.php");

$form = new sat_form_simple ();
$form->createLoginForm ();


if ($form->isCat ()) {
	if (array_key_exists('datsId', $_REQUEST)){
		$datsId = $_REQUEST ['datsId'];
	}
	$requested = $_REQUEST ['requested'];
	if (! isset ( $datsId ) || empty ( $datsId )) {
		$datsId = $_SESSION ['datsId_tmp'];
		$_SESSION ['datsId_tmp'] = null;
	}
	
	// Creation et affichage du formulaire
	if (isset ( $datsId ) && ! empty ( $datsId )) {
		// echo 'charge le dataset '.$datsId.'<br>';
		$form->dataset = dataset_factory::createSatelliteDatasetById($datsId);
		$_SESSION ['datasetSat'] = serialize ( $form->dataset );
	} else if (isset ( $_SESSION ['datasetSat'] )) {
		// echo 'dataset trouvé dans la session<br>';
		$form->dataset = unserialize ( $_SESSION ['datasetSat'] );
	}
	
	if (! isset ( $form->dataset )) {
		// echo 'creation dataset<br>';
		$form->dataset = new satellite_dataset ();
		//$form->dataset = $form->dataset->getById ( 0 );
		
		$form->dataset->nbPis = 1;
		$form->dataset->nbSites = 1;
		$form->dataset->nbCalcVars = 0;
		$form->dataset->nbVars = 1;
		$form->dataset->nbFormats = 1;
		$form->dataset->nbProj = 1;
		$form->dataset->dats_id = 0;
	}
	$form->dataset->dataset_types = array ();
	$form->dataset->dataset_types [0] = new dataset_type ();
	$form->dataset->dataset_types [0] = $form->dataset->dataset_types [0]->getByType ( dataset_type::TYPE_SATELLITE );
	
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
		$form->dataset->nbSites ++;
		$form->addSat ();
		$form->displayForm ();
		$_SESSION ['datasetSat'] = serialize ( $form->dataset );
	} else if (isset ( $_POST ['bouton_add_variable'] )) {
		$form->saveForm ();
		$form->dataset->nbVars ++;
		$form->addVariable ( );
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
		$form->addPi ( );
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
				//editDataset ( $form->dataset->dats_id );
				$form->dataset->display($project_name);
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
