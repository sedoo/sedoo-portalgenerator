<?php
if (! isset ( $_SESSION ))
	session_start ();
require_once ('conf/conf.php');
$project_name = "#project";
$project_url = "/#project";
$titreMilieu = "Model outputs registration";
ob_start ();
include ("loginCat.php");
include ("frmmod.php");
?>
<?php

$milieu = ob_get_clean ();
include ("template.php");
?>
