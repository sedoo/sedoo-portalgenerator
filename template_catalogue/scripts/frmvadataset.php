<?php

require_once("forms/va_dataset_form.php");
require_once("forms/validation.php");
require_once("editDataset.php");

require_once("scripts/upload.php");

if ($project_name != MainProject) {
	if ($_SERVER ['HTTP_REFERER'] == constant(strtolower ( $project_name ) .'WebSite')){
		$_SESSION ['username'] = strtolower($project_name);
	}
} else {
	if ($_SERVER ['HTTP_REFERER'] == PORTAL_WebSite){
		$_SESSION ['username'] = strtolower($project_name);
	}
}

//include 'login.php';
$form = new va_dataset_form;
$form->createLoginForm();
//user loggé

	if(isset($_REQUEST['datsId']) && !empty($_REQUEST['datsId']))
		$datsId = $_REQUEST ['datsId'];
	if(isset($_REQUEST['requested']) && !empty($_REQUEST['requested']))
		$requested = $_REQUEST['requested'];
	
	if (!isset($datsId) || empty($datsId)){
		$datsId = $_SESSION['datsId_tmp'];
		$_SESSION['datsId_tmp'] = null;
	}
	//Creation et affichage du formulaire
	if (isset($datsId) && !empty($datsId)){
		//echo 'charge le dataset '.$datsId.'<br>';
		$form->dataset = new dataset;
		$form->dataset = $form->dataset->getById($datsId);
		$_SESSION['vadataset'] = serialize($form->dataset);
	}else if (isset($_SESSION['vadataset'])){
		//echo 'dataset trouvé dans la session<br>';
		$form->dataset = unserialize($_SESSION['vadataset']);
	}

if ($form->isCat ( $form->dataset,$project_name )) {
	if (!isset($form->dataset))
	{
		//echo 'creation dataset<br>';
		$form->dataset = new dataset;
		$form->dataset = $form->dataset->getById(0);
		$form->dataset->nbPis = 1;
		$form->dataset->nbSites = 2;
		$form->dataset->nbCalcVars = 0;
		$form->dataset->nbVars = 1;
		$form->dataset->nbFormats = 1;
		$form->dataset->nbProj = 1;
		$form->dataset->nbSensors = 1;
		$form->dataset->dats_id = 0;
		$form->dataset->nbInstruForm = 0;
		$form->dataset->nbModForm = 0;
		$form->dataset->nbSatForm = 0;
			
	}
	$form->dataset->dataset_types = array();
	$form->dataset->dataset_types[0] = new dataset_type;
	$form->dataset->dataset_types[0] = $form->dataset->dataset_types[0]->getByType('VALUE-ADDED DATASET');
	$form->createForm();
	if (strpos($_SERVER['REQUEST_URI'],'&datsId')){
		$reqUri = substr($_SERVER['REQUEST_URI'],10,strpos($_SERVER['REQUEST_URI'],'&datsId'));
	}else{
		$reqUri = substr($_SERVER['REQUEST_URI'],10);
	}
	
	for($i = 0; $i < $form->dataset->nbModForm; $i ++) {
		if (isset ( $_POST ['mod_button_delete_' . $i] )) {
			$form->saveForm ();
			$form->deleteModForm ( $i );
			//$form->displayForm ();
			$_SESSION ['vadataset'] = serialize ( $form->dataset );
		}
	}
	for($i = 0; $i < $form->dataset->nbSatForm; $i ++) {
		if (isset ( $_POST ['sat_button_delete_' . $i] )) {
			$form->saveForm ();
			$form->deleteSatForm ( $i );
			//$form->displayForm ();
			$_SESSION ['vadataset'] = serialize ( $form->dataset );
		}
	}
	for($i = 0; $i < $form->dataset->nbInstruForm; $i ++) {
		if (isset ( $_POST ['instru_button_delete_' . $i] )) {
			$form->saveForm ();
			$form->deleteInstruForm ( $i );
			//$form->displayForm ();
			$_SESSION ['vadataset'] = serialize ( $form->dataset );
		}
	}
	if( isset($_POST['upload']) ){
		$form->saveForm();
		$form->dataset->attFile = uploadDoc("upload_doc");
		$form->displayForm();
		$_SESSION['vadataset'] = serialize($form->dataset);
	}else if( isset($_POST['delete']) ){
		$form->saveForm();
		if (isset($form->dataset->attFile) && !empty($form->dataset->attFile)){
			unlink(ATT_FILES_PATH.'/'.$form->dataset->attFile);
			$form->dataset->attFile = null;
		}
		$form->displayForm();
		$_SESSION['vadataset'] = serialize($form->dataset);
	}else if (isset($_POST['bouton_add_source'])){
		$form->saveForm();
		//$form->dataset->nbInstruForm = 0;
		//$form->dataset->nbModForm = 0;
		$form->addFormAccordingOnSourceType();
		$form->displayForm();
		$_SESSION['vadataset'] = serialize($form->dataset);				
	}else if (isset($_POST['bouton_add_variable'])){
		$form->saveForm();
		$form->dataset->nbVars++;
		$form->addVariableMod($form->dataset->nbVars);
		$form->displayForm();
		$_SESSION['vadataset'] = serialize($form->dataset);
	}else if (isset($_POST['bouton_add_pi'])){
		$form->saveForm();
		$form->dataset->nbPis++;
		$form->addPi($form->dataset->nbPis);
		$form->displayForm();
		$_SESSION['vadataset'] = serialize($form->dataset);
	}else if (isset($_POST['bouton_add_projet'])){
		$form->saveForm();
		$form->dataset->nbProj++;
		$form->addProjet();
		$form->displayForm();
		$_SESSION['vadataset'] = serialize($form->dataset);
	}else if (isset($_POST['bouton_save'])){
		$form->saveForm();
		$form->addValidationRules();
		
		if ($form->validate()){
			if ($form->dataset->dats_id == 0){
				$insertionOk = $form->dataset->insert();
				$form->dataset->set_requested($requested);
			}else{
				$insertionOk = $form->dataset->update();
				$form->dataset->set_requested($requested);
			}
			//$insertionOk = 0;
			if ($insertionOk){
				echo "<font size=\"3\" color='green'><b>The dataset has been succesfully inserted in the database</b></font><br>";
				
				$_SESSION['vadataset'] = null;
				editDataset($form->dataset->dats_id,$project_name);
			}else{
				echo "<font size=\"3\" color='red'><b>An error occured during the insertion process.</b></font><br>";
				
				$dts = new dataset;
				$dts->dats_id = $form->dataset->dats_id;
				if ( !$dts->idExiste()){
					$form->dataset->dats_id = 0;
				}
				$form->displayForm();
				$_SESSION['vadataset'] = serialize($form->dataset);
			}
		}else{
			$form->displayForm($nb_pi,$nb_site,$nb_variable,$nb_variable_calcul);
			$_SESSION['vadataset'] = serialize($form->dataset);
		}
	}else{
		$form->displayForm(true);
	}
}else if ($form->isLogged ()) {
	echo "<a href='/$project_name/'>&lt;&lt;&nbsp;Return</a><br/>";
	echo "<center><img src='/img/interdit.png' heigth='50' width='50' /></center>";
	echo "<br/><font size=\"3\" color='red'><center><b>You cannot modify this dataset.</b></center></font><br/>";
} else {
	$form->displayLGForm ( "", true );
}

?>
