<?php
if (! isset ( $_SESSION ))
	session_start ();
require_once ('conf/conf.php');
$project_name = explode ( '.', $_SERVER['SERVER_NAME'] )[0]; //"#MainProject";;
$project_url = "/";
$titreMilieu = "Request more datasets";
ob_start ();
?>
<div class="column1-unit">
	<br>
	<br>
	<div class="">
		<p>
			If you did not find needed datasets in the #MainProject database, you can
			fill in the forms below to detail the data you expect.
			<br>
		</p>
		<ul>
			<li>
				<a href="/portal/In-situ-data-request">
					<i>In situ</i>
					data form
				</a>
			</li>
			<li>
				<a href="/portal/Model-outputs-request">Model outputs form</a>
			</li>
			<li>
				<a href="/portal/Satellite-products-request">Satellite products
					form</a>
			</li>
		</ul>
	</div>
</div>
<?php
include ("lstinstrreq.php");
  $milieu = ob_get_clean();
  include("template.php");
?>
