<?php
if (! isset ( $_SESSION ))
	session_start ();
require_once ('/sites/kernel/#MainProject/conf.php');
$project_name = "#project";
$project_url = "/#project";
$titreMilieu = "";
ob_start ();
include ("loginCat.php");
include ("frmsite_simple.php");
?>
<?php

$milieu = ob_get_clean ();
include ("template.php");
?>
