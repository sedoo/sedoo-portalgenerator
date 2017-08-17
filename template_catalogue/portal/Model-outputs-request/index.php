<?php
if (! isset ( $_SESSION ))
	session_start ();
$project_name = "#MainProject";
$project_url = "/#MainProject";
require_once ('conf/conf.php');
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
