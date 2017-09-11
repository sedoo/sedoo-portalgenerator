<?php
require_once ("/sites/kernel/#MainProject/conf.php");
if (! isset ( $_SESSION ))
	session_start ();
$project_name = $_REQUEST ['project_name'];
if ($project_name != MainProject)
	$project_url = "/" . $project_name;
else
	$project_url = "/";
$titreMilieu = "";
ob_start ();
include ("loginAdm.php");
include ("frmdownload-bd.php");
?>
<?php

$milieu = ob_get_clean ();
include ("template_map.php");
?>
