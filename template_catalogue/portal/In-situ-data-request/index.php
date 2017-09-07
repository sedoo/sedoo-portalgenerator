<?php
if (! isset ( $_SESSION ))
	session_start ();
require_once ('/sites/kernel/#MainProject/conf.php');
$project_name = "#MainProject";
$project_url = "/#MainProject";
$titreMilieu = "";
ob_start ();
$_REQUEST ['requested'] = true;
include ("loginCat.php");
include ("frminstr.php");
?>
<?php

$milieu = ob_get_clean ();
include ("template.php");
?>
