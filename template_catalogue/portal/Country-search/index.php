<?php
if (! isset ( $_SESSION ))
	session_start ();
require_once ('/sites/kernel/#MainProject/conf.php');
$project_name = "#MainProject";
$project_url = "/";
$titreMilieu = "";
ob_start ();
include ("lstDataByCountry.php");
?>
<?php

$milieu = ob_get_clean ();
include ("template.php");
?>
