<?php
include "conf/conf.php"; 	
	
if (! isset ( $_SESSION ))
	session_start ();
// on récupère le nom du projet à partir de l'url
$project_name = explode ( '.', $_SERVER['SERVER_NAME'] )[0]; //"#MainProject";
$project_url = "/";
$titreMilieu = '';
ob_start ();
include ("lstDataByPlat.php");
?>
<?php

$milieu = ob_get_clean ();
include ("template.php");
?>
