<?php
if (! isset ( $_SESSION ))
	session_start ();
require_once('/sites/kernel/#MainProject/conf.php');
$project_name = "#project";
$project_url = "/#project";
$titreMilieu = "";
ob_start ();
include ("loginAdm.php");
include ("frmadmin.php");
$milieu = ob_get_clean ();
include ("template-admin.php");
?>
