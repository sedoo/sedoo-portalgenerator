<?php

require_once ("bd/journal.php");
require_once ("bd/url.php");
require_once ('filtreProjets.php');
require_once ('scripts/lstDataUtils.php');

echo '<h1>New data</h1>';
include 'legende.php';

$projets = get_filtre_projets($project_name);
$liste = journal::getNews('2 mons',$projets);

foreach($liste as $ligne){
       	echo '<p>';
       	if ($ligne->type_id == TYPE_NEW){
       		echo '<span class="pink_tag">NEW ';
       	}else if ($ligne->type_id == TYPE_UPDATE){
       		echo '<span class="lightpink_tag">UPDATE ';
       	}
        echo $ligne->date->format('Y-m-d').'</span>';
	echo printDataset($ligne->dataset);
        echo '<br><dfn>'.nl2br($ligne->comment).'</dfn>';
       echo '</p>';
}
?>
