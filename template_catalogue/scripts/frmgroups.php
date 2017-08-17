<?php

require_once("forms/groups_form.php");
require_once("forms/validation.php");

$grform = new groups_form();
$grform->createForm();

if ($grform->isRoot()){

	echo "<h1>Group management</h1><br><br>";

	if (isset($_REQUEST['create'])){
		echo "<h2>New group</h2>";
		$grform->createNewGroupForm();
		if (isset($_POST['bouton_new_group'])){
			if ($grform->validate())
				$grform->createGroup();
		}
		$grform->displayNewGroupForm();
	}else {
		$grform->createAdminGroupForm();
//	$grform->displayForm();
	if (isset($_POST['bouton_ok'])){
		$grform->listUsers();
        }else if (isset($_POST['add'])){
		if ($grform->validate())
			$grform->add();
	}else{
                foreach(array_keys($_POST) as $key){
                        if ( strpos($key,'rem_') === 0){
                                $id = substr($key,4);
                                $grform->remove($id);
                        }
                }
	}

	$grform->displayForm();
	$grform->displayUsers();
	}
}
?>
