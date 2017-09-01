<?php
require_once ('forms/site_form_simple.php');
require_once ('forms/validation_multi.php');
// require_once('editDataset.php');
require_once 'upload.php';

require_once ("bd/dataset_factory.php");

$form = new site_form_simple ();
$form->createLoginForm ();

echo "<h1>Multi-instrumented platform registration</h1>";

$datsId = $_REQUEST['datsId'];
if (!isset($datsId) || empty($datsId)){
	$datsId = $_SESSION['datsId_tmp'];
	$_SESSION['datsId_tmp'] = null;
}
if (isset($datsId) && !empty($datsId)){
	//echo 'charge le dataset '.$datsId.'<br>';
	$form->dataset = dataset_factory::createMultiInstrumentDatasetById($datsId);
	$_SESSION['dataset_multi'] = serialize($form->dataset);
}else if (isset($_SESSION['dataset_multi'])){
	//echo 'dataset trouvé dans la session<br>';
	//print_r($_SESSION['dataset_multi']);
	$form->dataset = unserialize($_SESSION['dataset_multi']);
}

if ($form->isCat ( $form->dataset,$project_name )) {
	if (!isset($form->dataset)){
		//echo 'creation dataset<br>';
		$form->dataset = new multi_instru_dataset ();
		//$form->dataset = new dataset;
		//$form->dataset = $form->dataset->getById(0);
		$form->dataset->nbPis = 1;
		$form->dataset->nbSensors = 1;
		$form->dataset->dats_sensors[0] = new dats_sensor;
		$form->dataset->dats_sensors[0]->nbVars = 1;
		$form->dataset->nbFormats = 1;
		$form->dataset->nbProj = 1;
		$form->dataset->dats_id = 0;
		//$_SESSION['dataset_multi'] = serialize($form->dataset);
	}
	
	$form->dataset->dataset_types = array ();
	$form->dataset->dataset_types [0] = new dataset_type ();
	$form->dataset->dataset_types [0] = $form->dataset->dataset_types [0]->getByType ( dataset_type::TYPE_MULTI_INSTRU );

	$form->createForm($project_name);

	for ($i = 0; $i <  $form->dataset->nbSensors; $i ++){

		//		echo "$i: ".$form->dataset->dats_sensors[$i]->nbVars.'<br/>';

		$bouton_add_var_pressed = false;
		if (isset($_POST['bouton_add_variable_'.$i])){
			$form->saveForm();
			//echo "$i: ".$form->dataset->dats_sensors[$i]->nbVars.'<br/>';
			$form->dataset->dats_sensors[$i]->nbVars++;
			$form->nbVarsBySensor[$i] = $form->dataset->dats_sensors[$i]->nbVars;
			//echo "$i: ".$form->dataset->dats_sensors[$i]->nbVars.'<br/>';
			$form->addVariableSensor($i);
			$form->displayForm();
			$_SESSION['dataset_multi'] = serialize($form->dataset);
			$bouton_add_var_pressed = true;
			break;
		}
	}
	
	if (! $bouton_add_var_pressed) {
		
		if (isset ( $_POST ['upload'] )) {
			$form->saveForm ();
			$form->dataset->image = uploadImg ( "upload_image" );
			$form->displayForm ();
			$_SESSION ['dataset_multi'] = serialize ( $form->dataset );
		} else if (isset ( $_POST ['delete'] )) {
			$form->saveForm ();
			if (isset ( $form->dataset->image ) && ! empty ( $form->dataset->image )) {
				unlink ( $form->dataset->image );
				$form->dataset->image = null;
			}
			$form->displayForm ();
			$_SESSION ['dataset_multi'] = serialize ( $form->dataset );
		} else if (isset ( $_POST ['bouton_add_pi'] )) {
			$form->saveForm ();
			$form->dataset->nbPis ++;
			$form->addPi ();
			$form->displayForm ();
			$_SESSION ['dataset_multi'] = serialize ( $form->dataset );
		} else if (isset ( $_POST ['bouton_add_format'] )) {
			$form->saveForm ();
			$form->dataset->nbFormats ++;
			$form->addFormat ();
			$form->displayForm ();
			$_SESSION ['dataset_multi'] = serialize ( $form->dataset );
		} else if (isset ( $_POST ['bouton_add_projet'] )) {
			$form->saveForm ();
			$form->dataset->nbProj ++;
			$form->addProjet ();
			$form->displayForm ();
			$_SESSION ['dataset_multi'] = serialize ( $form->dataset );
		} else if (isset ( $_POST ['bouton_add_sensor'] )) {
			$form->saveForm ();
			$form->dataset->nbSensors ++;
			$form->dataset->dats_sensors [$form->dataset->nbSensors - 1] = new dats_sensor ();
			$form->dataset->dats_sensors [$form->dataset->nbSensors - 1]->nbVars = 1;
			$form->addSensor ();
			$form->displayForm ();
			$_SESSION ['dataset_multi'] = serialize ( $form->dataset );
		} else if (isset ( $_POST ['bouton_save'] )) {
			$form->saveForm ();
			$form->saveDatsVars ();
			$form->addValidationRules ();
			if ($form->validate ()) {
				// Formulaire OK
				if ($form->dataset->dats_id == 0) {
					$insertionOk = $form->dataset->insert ();
				} else {
					$insertionOk = $form->dataset->update ();
				}
				if ($insertionOk) {
					echo "<font size=\"3\" color='green'><b>The dataset has been succesfully inserted in the database</b></font><br>";

					$_SESSION['dataset_multi'] = null;
					//editDataset($form->dataset->dats_id,$project_name);
					$dts = dataset_factory::createMultiInstrumentDatasetById($form->dataset->dats_id);
					$dts->display($project_name);
				}else{
					echo "<font size=\"3\" color='red'><b>An error occured during the insertion process.</b></font><br>";
					
					$dts = new dataset ();
					$dts->dats_id = $form->dataset->dats_id;
					if (! $dts->idExiste ()) {
						$form->dataset->dats_id = 0;
					}
					
					$form->displayForm ();
					$_SESSION ['dataset_multi'] = serialize ( $form->dataset );
				}
			} else {
				// Erreurs dans le formulaire
				$form->displayForm ();
				$_SESSION ['dataset_multi'] = serialize ( $form->dataset );
			}
		} else {
			$form->displayForm ();
		}
	}
} else if ($form->isLogged ()) {
	echo "<a href='/$project_name/'>&lt;&lt;&nbsp;Return</a><br/>";
	echo "<center><img src='/img/interdit.png' heigth='50' width='50' /></center>";
	echo "<br/><font size=\"3\" color='red'><center><b>You cannot modify this dataset.</b></center></font><br/>";
} else {
	$form->displayLGForm ( "", true );
}

?>
