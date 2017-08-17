<?php

require_once("forms/va_form.php");
require_once("forms/validation.php");
require_once("editDataset.php");

$form = new va_form;
$form->createLoginForm();

if ($form->isCat()){
	if(isset($_REQUEST['datsId']) && !empty($_REQUEST['datsId']))
		$datsId = $_REQUEST ['datsId'];
	if(isset($_REQUEST['requested']) && !empty($_REQUEST['requested']))
		$requested = $_REQUEST['requested'];
	if (!isset($datsId) || empty($datsId)){
        	$datsId = $_SESSION['datsId_tmp'];
	        $_SESSION['datsId_tmp'] = null;
	}

	if (isset($datsId) && !empty($datsId)){
		echo 'charge le dataset '.$datsId.'<br>';
		$form->dataset = new dataset;
		$form->dataset = $form->dataset->getById($datsId);
		$_SESSION['datasetVa'] = serialize($form->dataset);
	}else if (isset($_SESSION['datasetVa'])){
		echo 'dataset trouvé dans la session<br>';
		$form->dataset = unserialize($_SESSION['datasetVa']);
	}

	if (!isset($form->dataset)){
		echo 'creation dataset<br>';
		$form->dataset = new dataset;
		$form->dataset = $form->dataset->getById(0);

		$form->dataset->nbPis = 1;
		$form->dataset->nbSites = 1;
		$form->dataset->nbCalcVars = 0;
		$form->dataset->nbVars = 1;
		$form->dataset->nbFormats = 1;
		$form->dataset->nbProj = 1;
		$form->dataset->dats_id = 0;
	}

	$form->dataset->dataset_types = array();
	$form->dataset->dataset_types[0] = new dataset_type;
	$form->dataset->dataset_types[0] = $form->dataset->dataset_types[0]->getByType('VALUE-ADDED DATASET');

	$form->createForm();

	if (isset($_POST['bouton_add_insitu'])){
                $form->saveForm();
                $form->addInSitu();
                $form->dataset->nbSites++;
                $form->displayForm();
                $_SESSION['datasetVa'] = serialize($form->dataset);
        }else if (isset($_POST['bouton_add_sat'])){
                $form->saveForm();
                $form->addSat();
                $form->dataset->nbSites++;
                $form->displayForm();
                $_SESSION['datasetVa'] = serialize($form->dataset);
        }else if (isset($_POST['bouton_add_mod'])){
		$form->saveForm();
		$form->addMod();
		$form->dataset->nbSites++;
		$form->displayForm();
		$_SESSION['datasetVa'] = serialize($form->dataset);
	}else if (isset($_POST['bouton_add_variable'])){
  		$form->saveForm();
  		$form->dataset->nbVars++;
  		$form->addVariable($form->dataset->nbVars);
  		$form->displayForm();
  		$_SESSION['datasetVa'] = serialize($form->dataset);
	}else  if (isset($_POST['bouton_add_projet'])){
        	$form->saveForm();
	        $form->dataset->nbProj++;
        	$form->addProjet();
	        $form->displayForm();
        	$_SESSION['datasetVa'] = serialize($form->dataset);
	}else if (isset($_POST['bouton_add_pi'])){
		$form->saveForm();
		$form->dataset->nbPis++;
		$form->addPi($form->dataset->nbPis);
		$form->displayForm();
		$_SESSION['datasetVa'] = serialize($form->dataset);
	}else if (isset($_POST['bouton_save'])){
		$form->saveForm();
		$form->addValidationRules();
		if ($form->validate()){
			$form->dataset->set_requested($requested);

			if ($form->dataset->dats_id == 0){
				//Insert
				 $insertionOk = false;
			}else{
				//Update
				 $insertionOk = false;
			}
			
			$insertionOk = true;

			if ($insertionOk){
				$form->dataset->set_requested($requested);
				echo "<font size=\"3\" color='green'><b>The dataset has been succesfully inserted in the database</b></font><br>";
				$_SESSION['datasetVa'] = null;
				editDataset($form->dataset->dats_id,$project_name);
			}else{
				echo "<font size=\"3\" color='red'><b>An error occured during the insertion process.</b></font><br>";
				
				$dts = new dataset;
				$dts->dats_id = $form->dataset->dats_id;
				if ( !$dts->idExiste()){
					$form->dataset->dats_id = 0;
				}
				$form->displayForm();
				$_SESSION['datasetVa'] = serialize($form->dataset);
			}

		}else{
			$form->displayForm();
			$_SESSION['datasetVa'] = serialize($form->dataset);
		}	
	}else{
		$form->displayForm();
	}


}else $form->displayLGForm("",true);


?>
