<?php
	if (!isset($_SESSION))
		session_start();
   $project_name="#MainProject";
   $project_url="/";
   $titreMilieu="<span style='font-style: italic;'>In situ</span> instrument registration";
  ob_start();
  include("loginCat.php");
  include("frminstr.php");
?>
<?php
  $milieu = ob_get_clean();
  include("template.php");
?>
