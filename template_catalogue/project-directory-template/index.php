<?php
if (! isset ( $_SESSION ))
	session_start ();
require_once ('conf/conf.php');
$project_name = "#project";
$project_url = "/#project";
ob_start ();
if(constant(strtolower($project_name).'_HomePage') == 0)
	include 'home_page.php';
else if(constant(strtolower($project_name).'_HomePage') == 1)
	include 'lstDataByParam.php';
else if(constant(strtolower($project_name).'_HomePage') == 2)
	include 'lstDataByInstru.php';
else if(constant(strtolower($project_name).'_HomePage') == 3)
	include 'lstDataByCountry.php';
else if(constant(strtolower($project_name).'_HomePage') == 4)
	include 'lstDataByPlat.php';
else if(constant(strtolower($project_name).'_HomePage') == 5)
	include 'lstDataByProj.php';
else if(constant(strtolower($project_name).'_HomePage') == 6)
	include 'lstDataByEvent.php';
else if(constant(strtolower($project_name).'_HomePage') == 7)
	include 'lstDataByProj.php';;

$milieu = ob_get_clean();
include("template.php");
?>
