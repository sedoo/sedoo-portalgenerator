<?php

require_once ("utils/elastic/ElasticSearchUtils.php");
require_once("forms/download_form.php");

ini_set("max_execution_time", "0");
session_start();

//print_r($_POST);

$type = $_REQUEST['type'];
$project_name = $_REQUEST['project_name'];

$queryString = '';
$search = 0;
if ( array_key_exists('terms',$_REQUEST) ){
	$search = 1;
	$queryString = ElasticSearchUtils::getQueryString();
}

$form = new download_form();
$form->createForm($project_name, $queryString);

if (isset($_POST['bouton_public'])){
if ($form->validate()){
	$form->loginPublic();
}
$form->saveErrors();

}


if (isset($form->user)){		
	if (isset($_SESSION['selection'])){
		$form->selection = unserialize($_SESSION['selection']);
	}else{
		$form->selection = array();
	}
	if (isset($_SESSION['mailNotif'])){
		$form->mailNotif = unserialize($_SESSION['mailNotif']);
	}

	if (count($_POST) > 0) $form->mailNotif = $_POST['email_notif_hidden'];
	
	if ($form->initForm())
        {
	  echo '<div id="aide"></div>';		
	    echo '<h1>'.$form->getTitle().'</h1><br/>';
		echo '<br/><p>'.$form->getReadme().'</p>';

		if ($search){
			ElasticSearchUtils::addBackToSearchResultLink();
		}
		
		if (isset($_POST['bouton_down'])){
			$msg = $form->downloadCGI();
			
			echo $msg;

		}else{
			$archive = null;
//			print_r($_POST);
			foreach(array_keys($_POST) as $key){
                        	if ( strpos($key,'bouton_rem_') === 0){
					$mod = substr($key,11);		
					$form->removeItemFromSelection($mod);
				}else if ( strpos($key,'bouton_add_') === 0){
					$mod = substr($key,11);
					$form->addItemToSelection($mod);
				}
			}

			if (isset($_POST['bouton_reset'])){
				$form->clearSelection();
			}else if (isset($_POST['bouton_addAll'])){
				$form->addAllToSelection();
			}
			$form->displayForm($archive);
		}
		
		$_SESSION['selection'] = serialize($form->selection);
		$_SESSION['mailNotif'] = serialize($form->mailNotif);
	}else{

	}

}else $form->displayLGForm("",true);


?>
