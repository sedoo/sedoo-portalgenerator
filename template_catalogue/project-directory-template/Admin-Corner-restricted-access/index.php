<?php
if (! isset ( $_SESSION ))
	session_start ();
$project_name = "#project";
$project_url = "/#project";
require_once ('conf/conf.php');
$titreMilieu = "";
ob_start ();
include ("loginAdm.php");
include ("frmadmin.php");
$milieu = ob_get_clean ();
include ("template-admin.php");
?>
