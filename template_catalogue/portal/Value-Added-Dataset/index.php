<?php
if (!isset($_SESSION))
	session_start();
$project_name = explode ( '.', $_SERVER['SERVER_NAME'] )[0]; //"#MainProject";
$project_url="/";
$titreMilieu="Value Added Dataset";
ob_start();
include("loginCat.php");
/*  include("frmva.php");*/
include("frmvadataset.php");
?>
<?php
$milieu = ob_get_clean();
include("template.php");
?>