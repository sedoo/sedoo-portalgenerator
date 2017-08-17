<?php
	if (!isset($_SESSION))
        session_start();
   $project_name="Historical datasets";
   $project_url="/Historical-datasets";
   $titreMilieu="Historical datasets";
  ob_start();
?>
<?php
  $milieu = ob_get_clean();
  include("template.php");
?>
