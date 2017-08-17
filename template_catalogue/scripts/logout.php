<?php
require_once('forms/logout_form.php');
require_once('forms/login_form.php');
require_once('ldap/portalUser.php');
require_once('ldap/user.php');

if (isset($_SESSION['loggedUser']) ) 	$user = unserialize($_SESSION['loggedUser']);

//echo get_class($user);

$form_logout = new logout_form;
$form_logout->createForm();
if (isset($_POST['logout']))
{
	//unset($_SESSION['loggedUser']);
	session_destroy();
	$form_login = new login_form;
	$form_login->createLoginForm();
	$form_login->displayLoginButton();
}
else if (isset($user) && !empty($user))
{
	$form_logout->displayForm($user,$project_name);
}else {
	include 'loginGeneral.php';
}
?>

