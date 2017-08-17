<?php
if (!isset($_SESSION))
        session_start();
   $project_name="#project";
   $project_url="/#project";
   $titreMilieu="Access data";
  ob_start();
?>
<div class="column1-unit">
<br><br>
<div class=""><p>The #project Database offers you full public access to its metadata catalogue through the <a href="/#project/Browse-Catalogue">"Browse catalogue"</a> section.<br class="autobr">
However, access to data is restricted to #project registered users, as described in the <a href="/#project/Data-Policy">#project data policy section</a>.</p>

<p>If you are not a registered user of the #project Database, you can ask for access by filling the <a href="/#project/Data-Access-Registration">on-line registration form</a>.</p>

<p>If you are a registered user, you can click on the dataset title to access metadata, or on the blue flag <span>
<img src="/img/dataOk.gif" alt="" style="" height="16" width="15"></span> next to it to access data.</p>

</div>
<?php
  $milieu = ob_get_clean();
  include("template.php");
?>
