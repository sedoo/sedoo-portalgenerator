<?php

require_once("forms/stats_form_dats.php");
require_once("bd/dataset.php");

if ( isset($_REQUEST['datsId']) ){
	$datsId = $_REQUEST['datsId'];
}else{
	$datsId = 0;
}

if ($form->isAdmin($project_name) || $form->isPortalUser()){
	if ($datsId > 0){
		$stForm = new stats_form_dats();
		$stForm->createForm($datsId,$project_name);
		$stForm->display();
	}else{
		echo '<h1>Datasets lists</h1><br/><br/><br/>';
		//Liste des datasets du pi
		$query = "SELECT dataset.* FROM dataset join dats_originators USING (dats_id) JOIN personne USING (pers_id) WHERE pers_email_1 = '".$form->user->mail."' ORDER BY dats_title";
		$dats = new dataset();
		$liste = $dats->getByQuery($query);
		
		if (isset($liste) && !empty($liste)){
			echo '<ul>';
			foreach ($liste as $d){
				$url = $_SERVER['REQUEST_URI']."&datsId=$d->dats_id";
				echo "<li><a href='$url'>$d->dats_title</a></li>";
			}
			echo '</ul>';
		}else{
			echo "<font size=\"3\" color='red'><b>No dataset found.</b></font><br>";
		}
		
	}
	
	
}


?>