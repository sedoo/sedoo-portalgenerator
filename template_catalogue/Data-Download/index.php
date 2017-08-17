<?php
require_once ("conf/conf.php");
if (! isset ( $_SESSION ))
	session_start ();
$project_name = $_REQUEST ['project_name'];
if ($project_name != MainProject)
	$project_url = "/" . $project_name;
else
	$project_url = "/";
$titreMilieu = "";
ob_start ();
// echo "<b><font color=red>Part of the database will be down for maintenance between 22/10/2012 and 26/10/2012. As a result, radar, satellite and model data currently stored in the database will not be accessible during this period.
// We apologize for the inconvenience.</font></b><br>";
include ("loginAdm.php");
include ("frmdownload.php");
?>
<?php

$milieu = ob_get_clean ();
include ("template.php");
?>
