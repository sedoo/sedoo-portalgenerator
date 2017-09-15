<?php
if (! isset ( $_SESSION ))
	session_start ();
require_once ('conf/conf.php');
$project_name = "#project";
$project_url = "/#project";
$titreMilieu = "";
ob_start ();
// $projects = '10,11,12,13,14,15';
include ("lstDataByParam.php");
?>
<?php

$milieu = ob_get_clean ();
include ("template.php");
?>
