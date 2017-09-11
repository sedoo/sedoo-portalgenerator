<?php
require_once ("forms/extraction_form.php");

$dats_id = $_REQUEST['datsId'];

$search = 0;
if ( array_key_exists('terms',$_REQUEST) ){
	$search = 1;
	$queryString = ElasticSearchUtils::getQueryString();
}

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