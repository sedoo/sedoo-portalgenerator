<?php
if (! isset ( $_SESSION ))
	session_start ();
require_once ('/sites/kernel/#MainProject/conf.php');
$project_name = "#project";
$project_url = "/#project";
$titreMilieu = "#project Registration";
ob_start ();
include ("frmregisterMultiProjects.php");
?>
<?php
$milieu = ob_get_clean ();
include ("template.php");
?>