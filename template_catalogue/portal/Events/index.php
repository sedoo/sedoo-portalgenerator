<?php
if (! isset ( $_SESSION ))
	session_start ();
require_once ('conf/conf.php');
$project_name = "#MainProject";
$project_url = "/";
$titreMilieu = "";
ob_start ();
include ("lstDataByEvent.php");
?>
<?php

$milieu = ob_get_clean ();
include ("template.php");
?>
