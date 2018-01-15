<?php
if (! isset ( $_SESSION ))
	session_start ();
require_once ('conf/conf.php');
$project_name = "#project";
$project_url = "/#project";
$titreMilieu = "Provide metadata";
ob_start ();
?>
<div class="column1-unit">
	<br>
	<br>
	<div class="">
		<p>
			The #project database offers data providers the possibility to add or
			update metadata describing the datasets they are responsible for.
			Sharing information about the data is indeed a very important step to
			foster collaboration. Data access information (url link, procedure…)
			and use constraints are part of the metadata.
			<br>
			<br>
			To
			<b>add new metadata description</b>
			, please fill the relevant online form:
		</p>
		<ul>
		<?php
		
		if (constant(strtolower ( $project_name ) . '_HasInsituProducts') == 'true') {
			echo "<li>
				<a href='/#project/In-Situ-Instrument-Registration'>Instrument form</a>
				if you are responsible for an observation dataset. If you need to
				document several datasets that share information, please contact us.
				We can duplicate the filled forms and avoid you to fill many times
				the same information.
			</li>";
		}
		?>
		<?php
		
		if (constant(strtolower ( $project_name ) . '_HasMultiInsituProducts') == 'true') {
			echo "<li><a href='/#project/In-Situ-Site-Registration'>Site or multi-instrumented platform form</a>: if you have installed several instruments on one single location.</li>
		";
		}
		?>
		<?php
		
		if (constant(strtolower ( $project_name ) . '_HasModelOutputs') == 'true') {
			echo "<li>
				<a href='/#project/Model-Data'>Model outputs</a>
				if you would like to share model outputs
			</li>";
		}
		?>			
		<?php
		
		if (constant(strtolower ( $project_name ) . '_HasSatelliteProducts') == 'true') {
			echo "<li>
				<a href='/#project/Satellite-Data'>Satellite products</a>: describe your satellite data</li>";
		}
		?>
		<?php
		
		if (constant(strtolower ( $project_name ) . '_HasValueAddedProducts') == 'true') {
			echo "<li>
				<a href='/#project/Value-Added-Dataset'>Value-added dataset</a>
				if you would like to share a product, that results from the
				combination of many data sources.
			</li>";
		}
		?>						
			
			
		</ul>
		<p>
			If metadata are already available and can be either automatically
			harvested or delivered in a convenient format (such as xml), please
			contact us.
			<br>
			<br>
			To
			<b>update your metadata</b>
			, browse the #project catalogue, edit your dataset description by
			clicking on its title, and then click on the update button located at
			the bottom of the page.
			<br>

			<br>

			Do not hesitate to contact us in case of any difficulty:
			<b><?php if (defined('Portal_Contact_Email')) echo Portal_Contact_Email; ?></b>
			.
			<br>
		</p>
	</div>
</div>
<?php
$milieu = ob_get_clean ();
include ("template.php");
?>
