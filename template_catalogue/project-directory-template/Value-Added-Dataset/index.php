<?php
if (! isset ( $_SESSION ))
	session_start ();
require_once ('conf/conf.php');
$project_name = "#project";
$project_url = "/#project";
$titreMilieu = "Value-Added-Dataset";
ob_start ();
include ("loginCat.php");
/* include("frmva.php"); */
include ("frmvadataset.php");
?>
<?php

$milieu = ob_get_clean ();
include ("template.php");
?>