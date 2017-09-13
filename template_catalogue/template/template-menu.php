<?php
if ($project_name == MainProject) {
	// require("inc-colonne.html");
	$root_path_menu = "portal";
	$subscribe_url= "".$_SERVER['HTTP_HOST']."/User-Account-Creation";

}
else {
	// require("inc-colonne-project.html");
	$root_path_menu = $project_name;
	$subscribe_url= "".$_SERVER['HTTP_HOST']."/".$project_name."/Register";
	echo $project_name ."\n";
	echo MainProject . "\n";
}
?>

<!-- <h2><?php echo $project_name; ?></h2> -->

<section>
	<a href="https://<?php echo $subscribe_url;?>" class="tag"><span>Subscribe</span></a>

	<?php
	if(( (defined(''.strtolower($project_name).'DataPolicy') ) && (constant(strtolower($project_name).'DataPolicy') != '')) || ( (defined('Portal_DataPolicy') ) && (constant('Portal_DataPolicy') == 'true')) ){

		echo "<a href='/".$root_path_menu."/Data-Policy' class=\"tag\"><span>Data policy</span></a>";
	}


	?>
	<!-- <h2>Data users</h2> -->
	<a href="/<?php echo $root_path_menu; ?>/News" class="tag"><span>News</span></a>
</section>

<section>
	<h2>Search</h2>
	<?php require("inc-search-menu.php");?>
	<!-- <a href="/<?php echo $root_path_menu; ?>/Thematic-search">Thematic search</a> -->
	<a href="/Data-Search">Advanced search</a>
	<?php
	if(constant(strtolower($project_name).'_HasParameterSearch') == 'true'){
		echo "<a href='/".$root_path_menu."/Parameter-search'>By parameters</a>";
	}
	if(constant(strtolower($project_name).'_HasInstrumentSearch') == 'true'){
		echo "<a href='/".$root_path_menu."/Instrument-search'>By instruments</a>";
	}
	if(constant(strtolower($project_name).'_HasCountrySearch') == 'true'){
		echo "<a href='/".$root_path_menu."/Country-search'>By countries</a>";
	}
	if(constant(strtolower($project_name).'_HasPlatformSearch') == 'true'){
		echo "<a href='/".$root_path_menu."/Plateform-search'>By platform types</a>";
	}
	if(constant(strtolower($project_name).'_HasProjectSearch') == 'true'){
		echo "<a href='/".$root_path_menu."/Project-search'>By projects</a>";
	}
	if(constant(strtolower($project_name).'_HasEventSearch') == 'true'){
		echo "<a href='/".$root_path_menu."/Events'>By events</a>";
	}
	if(constant(strtolower($project_name).'_HasCampaignSearch') == 'true'){
		echo "<a href='/".$root_path_menu."/Campaign-search'>By campaigns</a>";
	}

	if(constant(strtolower($project_name).'_HasModelRequest') == 'true' || constant(strtolower($project_name).'_HasSatelliteRequest') == 'true' || constant(strtolower($project_name).'_HasInsituRequest') == 'true'){
	?>
	<hr>
	<a href='/<?php echo $root_path_menu;?>/Request-data'>Request more datasets</a>

	<?php
	}

	if(constant(strtolower($project_name).'_HasModelRequest') == 'true'){
		echo "<a href='/".$root_path_menu."/Model-outputs-request' class=\"subitem\">Model outputs</a>";
	}
	
	if(constant(strtolower($project_name).'_HasSatelliteRequest') == 'true'){
		echo "<a href='/".$root_path_menu."/Satellite-products-request' class=\"subitem\">Satellite products</a>";
	}
	if(constant(strtolower($project_name).'_HasInsituRequest') == 'true'){
		echo "<a href='/".$root_path_menu."/In-situ-data-request' class=\"subitem\">In situ data</a>";
	}		
    ?>			
</section>

<section>	
	<h2>Data providers</h2>
	
	<a href="/<?php echo $root_path_menu; ?>/Provide-metadata">Provide metadata</a>
	
	<?php
	if(constant(strtolower($project_name).'_HasModelOutputs') == 'true'){
		echo "<a href='/".$root_path_menu."/Model-Data/?datsId=-10' class=\"subitem\">Model outputs</a>";
	}
	if(constant(strtolower($project_name).'_HasSatelliteProducts') == 'true'){
		echo "<a href='/".$root_path_menu."/Satellite-Data/?datsId=-10' class=\"subitem\">Satellite products</a>";
	}
	if(constant(strtolower($project_name).'_HasInsituProducts') == 'true'){
		echo "<a href='/".$root_path_menu."/In-Situ-Instrument-Registration/?datsId=-10' class=\"subitem\">Instrument</a>";
	}
	if(constant(strtolower($project_name).'_HasMultiInsituProducts') == 'true'){
		echo "<a href='/".$root_path_menu."/In-Situ-Site-Registration/?datsId=-10' class=\"subitem\">Multi-instrumented platform</a>";
	}
	if(constant(strtolower($project_name).'_HasValueAddedProducts') == 'true'){
		echo "<a href='/".$root_path_menu."/Value-Added-Dataset/?datsId=-10' class=\"subitem\">Value-added dataset</a>";
	}
	?>

<?php
if ($project_name == MainProject) {
	echo "</section>";
	if(isset($MainProjects) && !empty($MainProjects)){
		echo "<section>";

		if(isset($MainProjects[0]) && !empty($MainProjects[0])) {
				echo "<h2>".MainProject." programs</h2>";
			}
			foreach($MainProjects as $proj){
				if(isset($proj) && !empty($proj)) {
	    			echo "<a href='/".$proj."'>".$proj."</a>";
				}
			}

		echo "</section>";
	}
	if(isset($OtherProjects) && !empty($OtherProjects)){
		echo "<section>";
		if(isset($OtherProjects[0]) && !empty($OtherProjects[0]))
			echo "<h2>More projects</h2>";
		foreach($OtherProjects as $proj){
			if(isset($proj) && !empty($proj))
    			echo "<a href='/".$proj."'>".$proj."</a>";
		}
		echo "</section>";
	}
}
else {
?>
	<hr>
	<a href="/<?php echo $root_path_menu; ?>/Provide-data">Provide data</a>
	</section>
	<section>
		<a href="<?php 'http://'.$_SERVER['HTTP_HOST']?>/Database-Content?project=<?php echo $project_name; ?>"	target=blank>Database Content</a>
	</section>
<?php
}
?>


