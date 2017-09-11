<?php
require_once ("forms/Contact_Users_form.php");
$ContactUsersform = new Contact_Users_form();
$ContactUsersform->createForm($project_name);
if (isset ( $_POST ['bouton_send'] )){
	if ($ContactUsersform->validate()){
		$ContactUsersform->sendMessageToAllUsers($project_name);
		echo "<h1 color='green'><font color='green'>Your message has been sent successfully</font></h1><br>";
	}else{
		echo "<h1>Contact all $project_name users</h1><br>";
		$ContactUsersform->display();
	}
}else{
	echo "<h1>Contact all $project_name users</h1><br>";
	$ContactUsersform->display();
}
?>