<?php
if (! isset ( $_SESSION ))
	session_start ();
require_once ('/sites/kernel/#MainProject/conf.php');
$project_name = "#project";
$project_url = "/#project";
$titreMilieu = "Browse catalogue";
ob_start ();
?>
<div class="column1-unit">
	<br>
	<br>


	<div class="">
		<p>
			The #project database offers different tools to browse its metadata
			catalogue:
			<br>
		</p>
		<ul>
			<li>
				<a href="/#project/Search-tool">Search Tool</a>
				: browse the catalogue using thematic, geographic and or temporal
				criteria.
			</li>
			<li>
				<a href="/#project/Thematic-search">By thematic keywords</a>
				: look for datasets corresponding to one and / or several keywords
				of your choice.
			</li>
			<li>
				<a href="/#project/Parameter-search">By parameter</a>
				: a list of the #project datasets ordered by measured parameters.
				The parameters are named following the Global Change Master
				Directory Science keywords.
			</li>
			<li>
				<a href="/#project/Instrument-search">By instrument</a>
				: a list of the #project datasets ordered by instrument types.
			</li>
			<li>
				<a href="/#project/Plateform-search">By platform types</a>
				: a list of the #project datasets ordered by platform types.
			</li>
		</ul>
		<p>To access metadata corresponding to a particular dataset, click on the dataset title. The flags next to some of the datasets titles means that corresponding data is available to download:
<?php
include('legende.php');
?>
</p>
	</div>
</div>
<?php
  $milieu = ob_get_clean();
  include("template.php");
?>
