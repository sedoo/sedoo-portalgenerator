<?php
	if (! isset ( $_SESSION ))
		session_start ();
	$project_name = "#MainProject";

	ob_start ();
	include ("database-content-user.php");
?>
