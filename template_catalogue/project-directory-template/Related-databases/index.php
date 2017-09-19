<?php
if (! isset ( $_SESSION ))
	session_start ();
require_once ('conf/conf.php');
$project_name = "#project";
$project_url = "/#project";
$titreMilieu = "Related databases";
ob_start ();
?>
<br>
<br>
<a href="<?php echo 'http://'.$_SERVER['HTTP_HOST']; ?>">Other
<?php echo MainProject; ?> Databases</a>
<?php
$milieu = ob_get_clean ();
include ("template.php");
?>
