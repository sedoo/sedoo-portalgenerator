<?php
	if (!isset($_SESSION))
		session_start();
   $project_name="#MainProject";
   $project_url="/";
   $titreMilieu="";
  ob_start();
  include("loginCat.php");
//  include("frmsite.php");
include("frmsite_simple.php");
?>
<?php
  $milieu = ob_get_clean();
  include("template.php");
?>
