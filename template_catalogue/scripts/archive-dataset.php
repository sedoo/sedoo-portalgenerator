<?php

require_once ("forms/archive_form.php");



$archiveform = new archive_form();
$archiveform->createForm($project_name);

if ($archiveform->isRoot()){
	if(isset($_REQUEST['datsId']) && !empty($_REQUEST['datsId']))
		$datsId = $_REQUEST['datsId'];
	if (isset($datsId) && !empty($datsId)){
		$archiveform->displayArchivedDataset($datsId);
	}else{

		if (isset($_POST['bouton_add'])){
			if ($archiveform->validate()){
				if ($archiveform->archive()){
					echo "<font size=\"3\" color='green'><b>Dataset succesfully archived.</b></font><br>";
					$archiveform->reset();
				}else{
					echo "<font size=\"3\" color='red'><b>An error occurred.</b></font><br>";
				}
			}
		}
		$archiveform->display();
	}


}

?>