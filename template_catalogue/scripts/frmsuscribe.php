<?php

require_once ("forms/suscribe_form.php");

$sform = new suscribe_form();
$sform->createForm();

if ($sform->isPortalUser()){
	$datsId = $_REQUEST['datsId'];
	$rubId = $_REQUEST['rubriqueId'];
	if (isset($datsId) && !empty($datsId)){
		if ($sform->addAbo($datsId))
			echo '<p><font size="2" color="green">You will be informed by email when new data are available for this dataset.</font></p>';
		else
			echo '<p><font size="2" color="red">We were unable to proceed this request.</font></p>';
	}
}else{
	$sform->displayLGForm("",false);
}


?>
