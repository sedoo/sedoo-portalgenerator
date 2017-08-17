<?php

require_once("bd/journal.php");
require_once("bd/url.php");
require_once('filtreProjets.php');
require_once('scripts/lstDataUtils.php');

echo '<h1>New data</h1>';
include 'legende.php';

$projets = get_filtre_projets($project_name);
$liste = journal::getNews('2 mons',$projets);

//echo '<ul>';
foreach($liste as $ligne){
       	//echo '<li>';
       	echo '<p>';
       	if ($ligne->type_id == TYPE_NEW){
       		echo '<span class="pink_tag" style="font-size: 10px;" >NEW</span>';
       	}else if ($ligne->type_id == TYPE_UPDATE){
       		echo '<span class="lightpink_tag" style="font-size: 10px;" >UPDATE</span>';
       	}
        echo '<b>'.$ligne->date->format('Y-m-d').'</b>:&nbsp;';
	echo printDataset($ligne->dataset);
        echo '<br><dfn>'.nl2br($ligne->comment).'</dfn>';
	//echo '</li>';
       echo '</p>';
}
//echo '</ul>';

?>
