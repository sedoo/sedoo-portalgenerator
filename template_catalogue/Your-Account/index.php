<?php
if (!isset($_SESSION))
        session_start();
   $project_name= explode ( '.', $_SERVER['SERVER_NAME'] )[0];
   $project_url="/";
   $titreMilieu="";
  ob_start();
  include("loginCat.php");
  include("frmprofile.php");
?>
<?php
  $milieu = ob_get_clean();
  include("template-account.php");
?>
