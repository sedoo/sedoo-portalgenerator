<?php
if (! isset ( $_SESSION ))
	session_start ();
require_once ('conf/conf.php');
$project_name = explode ( '.', $_SERVER['SERVER_NAME'] )[0]; //"#MainProject";;
$project_url = "/";
$titreMilieu = "Data policy";
ob_start ();
?>
<div class="column1-unit">
	<br>
	<br>

	<div class="">
		<p>
			Download the #MainProject Data Policy
			<a href="#MainProject_DataPolicy.pdf" type='application/pdf'>here</a>
			.
		</p>
	</div>
</div>
<?php
$milieu = ob_get_clean();
  include("template.php");
?>
