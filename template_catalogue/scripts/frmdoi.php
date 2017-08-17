<?php
require_once("bd/dataset.php");
require_once('filtreProjets.php');

require_once('conf/doi.conf.php');

if (isset($_REQUEST['newDoi'])){
	require_once('forms/doi_form.php');
	$form = new doi_form;
	$form->createForm($project_name);
	if ($form->isRoot()){
		if (isset($_POST['bouton_ok'])){
			if ($form->validate()){
				$form->registerDoi($project_name);
			}
		}
		$form->displayDoiForm();	
	}
}else{
echo "<h1>DOI list</h1><p/>";


$projects = 'SELECT DISTINCT dats_id FROM dats_proj WHERE project_id IN ('.get_filtre_projets($project_name).')';

$query = "SELECT * FROM dataset WHERE dats_id IN ($projects) AND dats_doi IS NOT NULL ORDER BY dats_title";
$dts = new dataset;
$datasets = $dts->getByQuery($query);

echo '<table><tr><th align="center">Dataset</th><th align="center">Doi</th><th align="center">Url</th><th></th></tr>';

foreach($datasets as $dataset){
	$handler = fopen(DOI_RESOLVER.$dataset->dats_doi,'r');
	echo "<tr><td>$dataset->dats_title</td><td>$dataset->dats_doi</td>";
	if ($handler){
		$doiExists = true;
		$f = fopen(SERVICE_DOI_URL.$dataset->dats_doi,'r');
		if ($f){
			$url = fgets($f);
		        echo "<td><a href='$url' target='_blank'>$url</a></td>";
			echo "<td><img src='/img/modifier-icone-16.png' title='Edit'></td>";
		        fclose($f);
		}else{
			echo "<td></td><td><img src='/img/avertissement-icone-16.png' title='Error retrieving URL from datacite web service' /></td>";
		}
		fclose($handler);
	}else{
		$doiExists = false;
		echo "<td></td><td><img src='/img/avertissement-icone-16.png' title='DOI does not exist' /></td>";
	}
	echo "</tr>";
}
echo '</table>';
}
?>
