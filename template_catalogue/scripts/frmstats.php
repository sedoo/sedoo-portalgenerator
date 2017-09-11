<?php
require_once ("/sites/kernel/#MainProject/conf.php");
require_once ("forms/stats_form.php");

if ( isset($_REQUEST['datsId']) && !empty($_REQUEST['datsId']) ){
	$datsId = $_REQUEST['datsId'];
}else{
	$datsId = 0;
}
$stform = new stats_form();
$stform->createForm($project_name);

if ( ( in_array($project_name, $MainProjects ) && $stform->isProjectAdmin() )
		|| (($project_name == MainProject) && $stform->isPortalAdmin())){
	$stform->display($datsId);
}

?>
