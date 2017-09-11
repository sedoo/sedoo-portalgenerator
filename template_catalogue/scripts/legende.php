<?php
$legende = array();
if(constant(strtolower($project_name).'_HasBlueTag') == 'true')
	$legende['Blue']='the dataset provided by the principal investigator.';
if(constant(strtolower($project_name).'_HasGreenTag') == 'true')
	$legende['Green']='the homogenized dataset.';
if(constant(strtolower($project_name).'_HasPurpleTag') == 'true')
	$legende['Purple']='data in another database.';
if(constant(strtolower($project_name).'_HasOrangeTag') == 'true')
	$legende['Orange']='the campaign website quicklook charts.';
?>

<section role="legend">
	<h3>Access to...</h3>
	<ul>
<?php

foreach($legende as $color => $texte){
	?>
	<li><span class="icon-folder-open" data-color="<?php echo $color;?>"></span> <?php echo $texte;?></li>
	
	<?php
}
?>
	</ul>
</section>