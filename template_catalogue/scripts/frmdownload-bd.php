<?php
require_once("forms/extraction_form.php");

$dats_id = $_REQUEST['datsId'];
$search = $_REQUEST['search'];

//echo "$dats_id $search<br>";

$form = new extraction_form();
$form->createForm($project_name,$dats_id,$search);

if (isset($form->user)){
	if (isset($_POST['bouton_submit'])){
		if ($form->validate()){
			$form->saveRequete();
			$form->send();
		}
	}
	if (isset($form->requete)){
		$form->displayForm();
	}else{
		include 'frmresult.php';
	}

}else
	$form->displayLGForm ( "", true );



?>