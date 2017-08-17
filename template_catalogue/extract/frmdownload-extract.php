<?php

require_once('extract/extract_download_form.php');
require_once('editDataset.php');

$resultId = $_REQUEST['resultId'];

$form = new extract_download_form;
$form->createForm($resultId,$project_name);
//echo "$project_name<br>";


$datsId = $_REQUEST['datsId'];
if (isset($datsId) && !empty($datsId)){
	echo "<h1>Dataset Edition</h1>";
	
	if (isset($resultId) && !empty($resultId))
		echo "<br/><a style='font-size:110%;font-weight:bold;' href='/extract/download.php?project_name=$project_name&resultId=$resultId'>&lt;&lt;&nbsp;Back to download page</a><br/>";
	editDataset($datsId,$project_name);
}else{
	if (isset($resultId) && !empty($resultId)){
        if ($form->isLogged()){
			$form->display();
		}else{
			if (isset($_POST['bouton_forgot'])){
				if ($form->forgottenPassword()){
					echo "<font size=\"3\" color='green'><b>A new password has been generated and sent to you by email.</b></font><br>";
				}
				$form->saveErrors();
				$form->displayLoginForm("Download",true,true);
			}else $form->displayLoginForm("Download",true);
			
		}
	}else{
		echo '<font size="3" color="red">No result to load.</font><br>';
	}
}

?>
