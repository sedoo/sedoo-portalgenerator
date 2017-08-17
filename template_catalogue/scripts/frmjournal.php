<?php

require_once("forms/journal_form.php");

if ( isset($_REQUEST['type']) && !empty($_REQUEST['type'])){
	$typeJournal = $_REQUEST['type'];
}else{
	$typeJournal = 0;
}
$jform = new journal_form();
$jform->createForm(false,$typeJournal);
$jform->projectName = $project_name;
if ($jform->isRoot()){
	if ( isset($_REQUEST['add']) && !empty($_REQUEST['add'])){
		if (isset($_POST['bouton_add'])){
			if ($jform->validate()){
				if ($jform->addEntry()){
					echo "<font size=\"3\" color='green'><b>Entry succesfully inserted.</b></font><br>";
					$jform->resetAddForm();
				}else{
					echo "<font size=\"3\" color='red'><b>An error occurred.</b></font><br>";
				}
			}
		}
		$jform->displayAddForm($typeJournal);
	}else{
		$jform->displayList($typeJournal);
	}
}else if ($jform->isLogged()){
	echo "<h1>Admin Corner</h1>";
	echo "<font size=\"3\" color='red'><b>You cannot view this part of the site.</b></font><br>";
}else{
	$jform->displayLGForm("",true);
}


?>
