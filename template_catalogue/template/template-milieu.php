<?php

if (isset ( $_POST ['loginbutton'] )) {
	require_once ('forms/login_form.php');
	session_destroy ();
	$form_login = new login_form ();
	$form_login->createLoginForm ();
	$form_login->displayLGForm ( "", true );
} else if (isset($_REQUEST['editDatsId']) && !empty($_REQUEST['editDatsId'])){
	//Affichage d'une fiche (argument editDatsId de l'url)
	require_once("editDataset.php");
	echo "<h1>Dataset Edition</h1>";
	$q = $_REQUEST['q'];
	if ( isset($q) ){
		$reqUri = parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
		echo "<br/><a style='font-size:110%;font-weight:bold;' href='$reqUri?q=$q&project_name=$project_name'>&lt;&lt;&nbsp;Back to search result</a><br/>";
	}
	editDataset($_REQUEST['editDatsId'],$project_name);
}else{
	if(isset($titreMilieu) && !empty($titreMilieu))
		echo "<h1>".$titreMilieu."</h1>";
	echo $milieu;
}

?>
