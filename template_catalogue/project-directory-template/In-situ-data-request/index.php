<?php
if (! isset ( $_SESSION ))
	session_start ();
require_once ('conf/conf.php');
$project_name = "#project";
$project_url = "/#project";
$titreMilieu = "";
ob_start ();
$_REQUEST ['requested'] = true;
include ("loginCat.php");
include ("frminstr.php");
?>
<?php

$milieu = ob_get_clean ();
include ("template.php");
?>
