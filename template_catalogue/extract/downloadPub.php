<?php
	require_once ("conf/conf.php");
	if (! isset ( $_SESSION ))
		session_start ();
	$project_name = $_REQUEST ['project_name'];
	if ($project_name != strtolower(MainProject))
		$project_url = "/" . $project_name;
	else
		$project_url = "/";
	$titreMilieu = "";
	ob_start ();
	include ("loginPub.php");
	include ("extract/frmdownload-extract-pub.php");
?>
<?php
	$milieu = ob_get_clean ();
	include ("template.php");
?>
