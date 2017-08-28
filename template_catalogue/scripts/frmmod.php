<?php
require_once ("forms/mod_form.php");
require_once ("forms/validation.php");
//require_once ("editDataset.php");

require_once ("scripts/upload.php");

require_once ("bd/dataset_factory.php");

// include 'login.php';

$form = new mod_form ();
$form->createLoginForm ();

// user loggé
// if (isset($form->user)){

if (array_key_exists('datsId', $_REQUEST)){
	$datsId = $_REQUEST ['datsId'];
}

if (array_key_exists('requested', $_REQUEST)){
	$requested = $_REQUEST ['requested'];
}else{
	$requested = false;
}

if (! isset ( $datsId ) || empty ( $datsId )) {
	$datsId = $_SESSION ['datsId_tmp'];
	$_SESSION ['datsId_tmp'] = null;
}

// Creation et affichage du formulaire
if (isset ( $datsId ) && ! empty ( $datsId )) {
	// echo 'charge le dataset '.$datsId.'<br>';
	$form->dataset = dataset_factory::createModelDatasetById($datsId);
	$_SESSION ['datasetMod'] = serialize ( $form->dataset );
} else if (isset ( $_SESSION ['datasetMod'] )) {
	// echo 'dataset trouvé dans la session<br>';
	$form->dataset = unserialize ( $_SESSION ['datasetMod'] );
}
if ($form->isCat ( $form->dataset, $project_name )) {
	if (! isset ( $form->dataset )) {
		// echo 'creation dataset<br>';
		//$form->dataset = new dataset ();
		$form->dataset = new model_dataset ();
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
	$form->dataset->dataset_types [0] = $form->dataset->dataset_types [0]->getByType ( dataset_type::TYPE_MODEL );
	
	$form->createForm ();
	
	if (strpos ( $_SERVER ['REQUEST_URI'], '&datsId' )) {
		$reqUri = substr ( $_SERVER ['REQUEST_URI'], 10, strpos ( $_SERVER ['REQUEST_URI'], '&datsId' ) );
	} else {
		$reqUri = substr ( $_SERVER ['REQUEST_URI'], 10 );
	}
	
	if (isset ( $_POST ['upload'] )) {
		$form->saveForm ();
		$form->dataset->attFile = uploadDoc ( "upload_doc" );
		$form->displayForm ();
		$_SESSION ['datasetMod'] = serialize ( $form->dataset );
	} else if (isset ( $_POST ['delete'] )) {
		$form->saveForm ();
		if (isset ( $form->dataset->attFile ) && ! empty ( $form->dataset->attFile )) {
			unlink ( ATT_FILES_PATH . '/' . $form->dataset->attFile );
			$form->dataset->attFile = null;
		}
		$form->displayForm ();
		$_SESSION ['datasetMod'] = serialize ( $form->dataset );
	} else if (isset ( $_POST ['bouton_add_variable'] )) {
		$form->saveForm ();
		$form->dataset->nbVars ++;
		$form->addVariableMod ();
		$form->displayForm ();
		$_SESSION ['datasetMod'] = serialize ( $form->dataset );
	} else if (isset ( $_POST ['bouton_add_pi'] )) {
		$form->saveForm ();
		$form->dataset->nbPis ++;
		$form->addPi ( );
		$form->displayForm ();
		$_SESSION ['datasetMod'] = serialize ( $form->dataset );
	} else if (isset ( $_POST ['bouton_add_projet'] )) {
		$form->saveForm ();
		$form->dataset->nbProj ++;
		$form->addProjet ();
		$form->displayForm ();
		$_SESSION ['datasetMod'] = serialize ( $form->dataset );
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
				
				$_SESSION ['datasetMod'] = null;
				//editDataset ( $form->dataset->dats_id, $project_name );
				$dts = dataset_factory::createModelDatasetById($form->dataset->dats_id);
				$dts->display($project_name);
			} else {
				echo "<font size=\"3\" color='red'><b>An error occured during the insertion process.</b></font><br>";
				
				$dts = new dataset ();
				$dts->dats_id = $form->dataset->dats_id;
				if (! $dts->idExiste ()) {
					$form->dataset->dats_id = 0;
				}
				$form->displayForm ();
				$_SESSION ['datasetMod'] = serialize ( $form->dataset );
			}
		} else {
			$form->displayForm ( $nb_pi, $nb_site, $nb_variable, $nb_variable_calcul );
			$_SESSION ['datasetMod'] = serialize ( $form->dataset );
		}
	} else {
		$form->displayForm ();
	}
} else if ($form->isLogged ()) {
	
	echo "<a href='/$project_name/'>&lt;&lt;&nbsp;Return</a><br/>";
	echo "<center><img src='/img/interdit.png' heigth='50' width='50' /></center>";
	echo "<br/><font size=\"3\" color='red'><center><b>You cannot modify this dataset.</b></center></font><br/>";
} else {
		$form->displayLGForm ( "", true );
}

?>
