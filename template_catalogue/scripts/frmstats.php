<?php
require_once ("conf/conf.php");
require_once ("forms/stats_form.php");

if ( isset($_REQUEST['datsId']) && !empty($_REQUEST['datsId']) ){
	$datsId = $_REQUEST['datsId'];
}else{
	$datsId = 0;
}
$stform = new stats_form();
$stform->createForm($project_name);

if ( ( in_array($project_name, $MainProjects ) && $stform->isProjectAdmin() )
		|| (($project_name == strtolower(MainProject)) && $stform->isPortalAdmin())){
	$stform->display($datsId);
}

?>
