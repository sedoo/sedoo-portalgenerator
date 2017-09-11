<?php
require_once ("forms/login_form.php");

$form = new login_form ();
$form->createLoginForm ( 'username' );

if (isset ( $_SESSION ['loggedUser'] ))
	$form->user = unserialize ( $_SESSION ['loggedUser'] );
	
	// Action logout
if (isset ( $_POST ['logout'] )) {
	session_destroy ();
	$form->user = null;
}

// Action login
if (isset ( $_POST ['bouton_login'] )) {
	if ($form->validate ()) {
		if ($form->loginCat () === false) {
			$titreMilieu = "";
			ob_start ();
			$form->displayLGForm ( '', true );
			$milieu = ob_get_clean ();
		}
	}
	$form->saveErrors ();
}

?>

