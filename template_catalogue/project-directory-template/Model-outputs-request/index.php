<?php
if (! isset ( $_SESSION ))
	session_start ();
$project_name = "#project";
$project_url = "/#project";
require_once ('/sites/kernel/#MainProject/conf.php');
$titreMilieu = "Model outputs request";
ob_start ();
$_REQUEST ['requested'] = true;
include ("loginCat.php");
include ("frmmod.php");
?>
<?php

$milieu = ob_get_clean ();
include ("template.php");
?>
