#!/usr/bin/php
<?php
 
 set_include_path('.:/usr/share/pear:/usr/share/php:/www/mistrals:/www/mistrals/scripts');
 require_once("bd/dataset.php");
 require_once("bd/dataset2xml.php");
 require_once("xml/xmlTemplate.php");
 
 function retrieveDatsList()
 {
 	$dts = new dataset;
 	return $dts->getAll();
 }
 //recupère tous les dataset
 $dts_list = retrieveDatsList();
 //pour chaque dataset, créer un flux xml
 foreach ($dts_list as $dataset){
	if ( $dataset->is_requested){
		echo "ignoring dataset: ".$dataset->dats_title."\n";
	}else{
		echo "processing dataset: ".$dataset->dats_id." - ".$dataset->dats_title."\n";
	}
 		dataset2xml($dataset);
 } 
?>
