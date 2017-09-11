<?php

	require_once ("bd/dataset.php");
	require_once ("bd/personne.php");
	require_once ("forms/site_form.php");
	require_once ("editDataset.php");
	require_once ("forms/validation.php");

	
	function not_void($elements, $values){
		
		foreach ($elements as $elt){
			echo "- elt: ".$elt;
		}
		foreach ($values as $val){
			echo "- val: ".$val;
		}
		
		
			return true;
					
	}
		
		
		/*
		 * $elements : nom, type, west, east, south, north, alt min, alt max, env
		 */
		function validSite($element_names, $element_values){
			$cpt = 0;
			foreach ($element_values as $val){
				if (!empty($val))
					$cpt++;
			}
												 
			if ( validInterval(array(),array_slice($element_values,6,2)) && 	validBoundings(array(),array_slice($element_values,2,4)) ){
				
			}
			
		}
		
		function distinct($element_names, $element_values){
			sort($element_values);
			$valPrec = '';
			$distinct = true;
					
			foreach ($element_values as $val){
				echo '- '.$val.'.<br>';
				if (!empty($val)){
					if (!empty($valPrec) && ($valPrec == $val) ){
						$distinct = false;
						break;
					}
				}
				$valPrec = $val;
			}
			return $distinct;
		}
		
		/*
		 * Teste si des entrées existent déjà dans la base
		 * value: valeurs à tester (séparées par des ';'
		 * args : 0 -> table, 1 -> colonne
		 */
		function existInDb($element, $value, $args){
			$values = split(";",$value);
			$result = true;
			foreach ($values as $val){
				if (!empty($val))
					$result = $result && existe($element,$val,$args);
			}
		}
	
	/*
	 * Teste qu'un champ texte est saisi si une option a été choisie dans un select
	 * element: element liste sur lequel s'applique la regle
	 * value: valeur choisie dans la liste (0 => rien) 
	 * args: array(0 => formulaire, 1 => champ texte à considérer 
	 */
	function required_if_not_void($element, $value, $args)
	{
			
		$arg_value = $args[0]->exportValue($args[1]);
				
		if (empty($arg_value) && $value != 0){
			return false;
		}else
			return true;
	}
	
	/*
	 * Teste qu'une option a été choisie dans un select si un champ texte n'est pas vide
	 * element: element liste sur lequel s'applique la regle
	 * value: valeur choisie dans la liste (0 => rien) 
	 * args: array(0 => formulaire, 1 => champ texte à considérer 
	 */
	function required_if_not_void2($element, $value, $args)
	{	
		$arg_value = $args[0]->exportValue($args[1]);
				
		if (!empty($arg_value) && $value == 0){
			return false;
		}else
			return true;
	}
	
	require_once 'upload.php';
	
  	$form = new site_form;
 	$form->createLoginForm();

if ($form->isCat()){ 	
  	$datsId = $_REQUEST['datsId'];
  	if (!isset($datsId) || empty($datsId)){
  		$datsId = $_SESSION['datsId_tmp'];
  		$_SESSION['datsId_tmp'] = null;
  	}
  	
  	//Creation et affichage du formulaire
  	if (isset($datsId) && !empty($datsId))
  	{
  		$form->dataset = new dataset;
  		$form->dataset = $form->dataset->getById($datsId);
  		$_SESSION['dataset'] = serialize($form->dataset);
  	}else if (isset($_SESSION['dataset'])){
    	$form->dataset = unserialize($_SESSION['dataset']);
	}
	
  	if (!isset($form->dataset))
  	{
    	$form->dataset = new dataset;
    	$form->dataset = $form->dataset->getById(0);
    	$form->dataset->nbPis = 1;
    	$form->dataset->nbSensors = 1;
    	$form->dataset->dats_sensors = array();
    	$form->dataset->dats_sensors[0] = new dats_sensor;
    	$form->dataset->dats_sensors[0]->nbVars = 1;
    	$form->dataset->dats_sensors[0]->nbCalcVars = 1;
    	$form->dataset->nbFormats = 1;
    	$form->dataset->nbProj = 1;
    	$form->dataset->dats_id = 0;
  	}
  	else
  	{
  		if (! isset($form->dataset->dats_sensors))
  		{
  			$form->dataset->dats_sensors = array();
  			$form->dataset->nbSensors = 1;		
  		}
  		for ($i = 0; $i < $form->dataset->nbSensors; $i++)
  		{
  			if (! isset($form->dataset->dats_sensors[$i]->nbVars) || $form->dataset->dats_sensors[$i]->nbVars == 0)
  				$form->dataset->dats_sensors[$i]->nbVars = 1;
  			if (! isset($form->dataset->dats_sensors[$i]->nbCalcVars) || $form->dataset->dats_sensors[$i]->nbCalcVars == 0)
  				$form->dataset->dats_sensors[$i]->nbCalcVars = 1;
  		}
  	}
  	
  	//TODO nettoyer     	    	
  	
  	$nb_pi = & $form->dataset->nbPis;
  	$nb_sensors = & $form->dataset->nbSensors;
  	$tab_sensors = & $form->dataset->dats_sensors;
  	$tab_nbVars = array();
  	$tab_nbCalcVars = array();
  	for ($i = 0; $i < count($tab_sensors);$i++)
  	{
  		$tab_nbVars[$i] = $tab_sensors[$i]->nbVars;
  		$tab_nbCalcVars[$i] = $tab_sensors[$i]->nbCalcVars;
  	}
  	$bouton_add_var_pressed = false;
  	$form->createForm($project_name);

	echo "<h1>In-Situ Site Registration</h1>";	

  	for ($i = 0; $i < $nb_sensors; $i ++)
	  	{
	  		if (isset($_POST['bouton_add_variable_'.$i]))
		  	{
		  		$form->saveForm($nb_pi,$nb_sensors,$tab_nbVars,$tab_nbCalcVars);
		  		
		  		$tab_nbVars[$i]++;
		  		$form->dataset->dats_sensors[$i]->nbVars = & $tab_nbVars[$i];
		  		$form->addVariable($i);
		  		$form->displayForm($nb_pi,$nb_sensors,$tab_nbVars,$tab_nbCalcVars);
		  		$_SESSION['dataset'] = serialize($form->dataset);
		  		$bouton_add_var_pressed = true;
		  		break;
		  	}
		  	else if (isset($_POST['bouton_add_variable_calcul_'.$i]))
		  	{
		  		$form->saveForm($nb_pi,$nb_sensors,$tab_nbVars,$tab_nbCalcVars);
		  		
		  		$tab_nbCalcVars[$i]++;
		  		$form->dataset->dats_sensors[$i]->nbCalcVars = & $tab_nbCalcVars[$i];
		  		$form->addVariableCalcul($i);
		  		$form->displayForm($nb_pi,$nb_sensors,$tab_nbVars,$tab_nbCalcVars);
		  		$_SESSION['dataset'] = serialize($form->dataset);
		  		$bouton_add_var_pressed = true;
		  		break;
		  	}
	  	}
	
  	if (!$bouton_add_var_pressed)
  	{
	  	if( isset($_POST['upload'])){
	  		$form->saveForm($nb_pi,$nb_sensors,$tab_nbVars,$tab_nbCalcVars);
	  		$form->dataset->image = uploadImg("upload_image");
	  		$form->displayForm($nb_pi,$nb_sensors,$tab_nbVars,$tab_nbCalcVars);
	  		$_SESSION['dataset'] = serialize($form->dataset);
	  	}
	  	else if( isset($_POST['delete']) )
	  	{
	  		$form->saveForm($nb_pi,$nb_sensors,$tab_nbVars,$tab_nbCalcVars);
	  		if (isset($form->dataset->image) && !empty($form->dataset->image)){
	  			unlink($form->dataset->image);
	  			$form->dataset->image = null;
	  		}
	  		$form->displayForm($nb_pi,$nb_sensors,$tab_nbVars,$tab_nbCalcVars);
	  		$_SESSION['dataset'] = serialize($form->dataset);
	  	}
	  	else if (isset($_POST['bouton_add_pi']))
	  	{
	  		$form->saveForm($nb_pi,$nb_sensors,$tab_nbVars,$tab_nbCalcVars);
	  		$form->dataset->nbPis++;
	  		$form->addPi();
	  		$form->displayForm($nb_pi,$nb_sensors,$tab_nbVars,$tab_nbCalcVars);
	  		$_SESSION['dataset'] = serialize($form->dataset);
	  	}
	  	else if (isset($_POST['bouton_add_sensor']))
	  	{
	  		$form->saveForm($nb_pi,$nb_sensors,$tab_nbVars,$tab_nbCalcVars);
	  		$nb_sensors++;
	  		$form->dataset->nbSensors = $nb_sensors;
	  		$form->dataset->dats_sensors[$form->dataset->nbSensors-1] = new dats_sensor;
	  		$form->dataset->dats_sensors[$form->dataset->nbSensors-1]->nbVars = 1;
	  		$form->dataset->dats_sensors[$form->dataset->nbSensors-1]->nbCalcVars = 1;
	  		$tab_nbVars[$form->dataset->nbSensors-1] = 1;
	  		$tab_nbCalcVars[$form->dataset->nbSensors-1] = 1;
	  		$form->addSensor();
	  		$form->displayForm($nb_pi,$nb_sensors,$tab_nbVars,$tab_nbCalcVars);
	  		$_SESSION['dataset'] = serialize($form->dataset);
	  	}
	  	
	  	else if (isset($_POST['bouton_add_format']))
	  	{
	  		$form->saveForm($nb_pi,$nb_sensors,$tab_nbVars,$tab_nbCalcVars);
	  		$form->dataset->nbFormats++;
	  		$form->addFormat();
	  		$form->displayForm($nb_pi,$nb_sensors,$tab_nbVars,$tab_nbCalcVars);
	  		$_SESSION['dataset'] = serialize($form->dataset);
	  	}
	  	else if (isset($_POST['bouton_add_projet']))
	  	{
	  		$form->saveForm($nb_pi,$nb_sensors,$tab_nbVars,$tab_nbCalcVars);
	  		$form->dataset->nbProj++;
	  		$form->addProjet();
	  		$form->displayForm($nb_pi,$nb_sensors,$tab_nbVars,$tab_nbCalcVars);
	  		$_SESSION['dataset'] = serialize($form->dataset);
	  	}
	  	else if (isset($_POST['bouton_save']))
	  	{
	  		$form->saveForm($nb_pi,$nb_sensors,$tab_nbVars,$tab_nbCalcVars);
	  		$form->saveDatsVars($nb_sensors);
	  		$form->addValidationRules();
	  		
	  		if ($form->validate())
	  		{
	
	  			if ($form->dataset->dats_id == 0){
	  		 		$insertionOk = $form->dataset->insert();
	  			 }else{
	  			 	$insertionOk = $form->dataset->update();
	  			 }
				 	  			 
	  			 if ($insertionOk){
	  			 	echo "<font size=\"3\" color='green'><b>The dataset has been succesfully inserted in the database</b></font><br>";
	  			 	
	  			 	$_SESSION['dataset'] = null;
	  			 	editDataset($form->dataset->dats_id,$project_name);
	  			 	
	  			 }else{
	  			 	echo "<font size=\"3\" color='red'><b>An error occured during the insertion process.</b></font><br>";
	  			 	
	  			 	$dts = new dataset;
	  			 	$dts->dats_id = $form->dataset->dats_id;
	  			 	if ( !$dts->idExiste()){
	  			 		$form->dataset->dats_id = 0;	
	  			 	}
	  			 	
	  			 	$form->displayForm($nb_pi,$nb_sensors,$tab_nbVars,$tab_nbCalcVars);
	  			 	$_SESSION['dataset'] = serialize($form->dataset);
	  			 }
	  		}
	  		else
	  		{
	  			$form->displayForm($nb_pi,$nb_sensors,$tab_nbVars,$tab_nbCalcVars);
	  			$_SESSION['dataset'] = serialize($form->dataset);
	  		}
	  	}
	  	else
	  	{
	  		$form->displayForm($nb_pi,$nb_sensors,$tab_nbVars,$tab_nbCalcVars);
	  	}
  	}
}else $form->displayLGForm("",true);
     
       		
?>
