<?php 
	
require_once('forms/db_requests_forms.php');

echo '<h1>Database requests</h1>';

$dbform = new db_requests_forms;
$dbform->createForm(isset($_REQUEST['adm']));

if ($dbform->isLogged()){
	
	foreach (array_keys($_POST) as $key){
		//echo $key.'<br>';
		if (strpos($key,'bouton__kill_') === 0){
			$id = str_replace('bouton__kill_','',$key);
			$dbform->kill($id);
		}
	if (strpos($key,'bouton_launch_') === 0){
			$id = str_replace('bouton_launch_','',$key);
			$dbform->send($id);
		}
	}
	
	$dbform->display();
	
}else{
	$dbform->displayLGForm("");
}
	
	

?>
