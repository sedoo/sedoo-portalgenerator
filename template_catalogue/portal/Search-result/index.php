<?php
   if (!isset($_SESSION))
        session_start();
   $project_name="#MainProject";
   $project_url="/";
   $titreMilieu="";
  ob_start();
  include("frmresult.php");
?>
<?php
  $milieu = ob_get_clean();
  include("template_map.php");
?>
