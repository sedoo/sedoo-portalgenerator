<?php
if (! isset ( $_SESSION ))
	session_start ();
require_once ('conf/conf.php');
$project_name = "#project";
$project_url = "/#project";
$titreMilieu = "Data policy";
ob_start ();
?>
<div class="column1-unit">
	<br>
	<br>

	<div class="">
		<p>
			Download the #project Data Policy
			<a href="#project_DataPolicy.pdf" type='application/pdf'>here</a>
			.
		</p>
	</div>
</div>
<?php
$milieu = ob_get_clean();
  include("template.php");
?>
