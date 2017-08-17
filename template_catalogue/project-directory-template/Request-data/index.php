<?php
if (! isset ( $_SESSION ))
	session_start ();
require_once ('conf/conf.php');
$project_name = "#project";
$project_url = "/#project";
$titreMilieu = "Request more datasets";
ob_start ();
?>
<div class="column1-unit">
	<br>
	<br>
	<div class="">
		<p>
			If you did not find needed datasets in the #project database, you can
			fill in the forms below to detail the data you expect.
			<br>
		</p>
		<ul>
			<li>
				<a href="/#project/In-situ-data-request">
					<i>In situ</i>
					data form
				</a>
			</li>
			<li>
				<a href="/#project/Model-outputs-request">Model outputs form</a>
			</li>
			<li>
				<a href="/#project/Satellite-products-request">Satellite products
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
