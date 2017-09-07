<?php
if (! isset ( $_SESSION ))
	session_start ();
require_once ('/sites/kernel/#MainProject/conf.php');
$project_name = "#project";
$project_url = "/#project";
$titreMilieu = "<span style='font-style: italic;'>In situ</span> instrument registration";
ob_start ();
include ("loginCat.php");
include ("frminstr.php");
?>
<?php

$milieu = ob_get_clean ();
include ("template.php");
?>
