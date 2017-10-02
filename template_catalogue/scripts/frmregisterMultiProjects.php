<?php
require_once ("forms/user_form_multi_projects.php");

/*
 * Teste si element n'est pas vide qu'un 2e champ est rempli. element: element sur lequel s'applique la regle value: valeur saisie args: array(0 => formulaire, 1 => champ texte à vérifier)
 */
function valid_xor($element, $value, $args) {
	$arg_value = $args [0]->exportValue ( $args [1] );
	if (empty ( $value ) xor empty ( $arg_value )) {
		return false;
	} else {
		return true;
	}
}

$formReg = new user_form_new ();
$formReg->project [0] = $project_name;
$formReg->createForm ();

if (isset ( $_SESSION ['loggedUser'] ) && ! empty ( $_SESSION ['loggedUser'] )) {
	$user = unserialize ( $_SESSION ['loggedUser'] );
}
if (isset ( $_SESSION ['loggedUser'] ) && ! empty ( $_SESSION ['loggedUser'] )) {
	if ($project_name == strtolower(MainProject))
		header ( 'Location: http://' . $_SERVER ['HTTP_HOST'] . '/Your-Account/?p&pageId=11' );
	else if (in_array ( $project_name, $MainProjects )){
		$Project_pageId = 11;
		while ($project = current($MainProjects)) {
			if ($project == $project_name) {
				$Project_pageId = key($MainProjects)+15;
			}
			next($MainProjects);
		}
		header ( 'Location: http://' . $_SERVER ['HTTP_HOST'] . '/Your-Account/?p&pageId='.$Project_pageId );
	}
} else {
	if (isset ( $user ) && ! empty ( $user )) {
		$formReg->getElement ( 'mail' )->setValue ( $user->mail );
		$formReg->check ();
	} else if (isset ( $_POST ['bouton_check'] )) {
		if ($formReg->validate ()) {
			$formReg->check ();
		}
	} else if (isset ( $_POST ['bouton_save'] )) {
		if (in_array($project_name, $MainProjects)) {
			$formReg->saveForm ( true );
			if ($formReg->validate () && $formReg->validateChart ()) {
				if ($formReg->addUser ( true )) {
					$formReg->addProjectUser ( true );
					echo "<font size=\"3\" color='green'><b>\n\nYour portal account was created.\n</b><br><b>Your access privileges will be temporarily limited to public datasets until your identity is verified and approved by the administrator.\n</b>" . "<br><b>Once your registration to access $project_name data will be approved, you will receive a confirmation mail.\n</b></font><br>";
					return;
				}
			}
		} else {
			$formReg->saveForm ();
			if ($formReg->validate () && $formReg->validateChart ( true )) {
				if ($formReg->addUser ()) {
					echo "<font size=\"3\" color='green'><b>\n\nYour portal account was created.\n</b><br><b>Your access privileges will be temporarily limited to public datasets until your identity is verified and approved by the administrator.\n</b>" . "<br><b>Once your registration to access $project_name data will be approved, you will receive a confirmation mail.\n</b></font><br>";
					return;
				}
			}
		}
	} else if (isset ( $_POST ['bouton_update'] )) {
		if (in_array($project_name, $MainProjects)) {
			$formReg->saveForm ( true );
			if ($formReg->validate () && $formReg->validateChart ()) {
				if ($formReg->updateUser ()) {
					echo "<font size=\"3\" color='green'><b>The request has been registered.</b></font><br>";
					return;
				}
			}
		} else {
			$formReg->saveForm ();
			if ($formReg->validate () && $formReg->validateChart ( true )) {
				if ($formReg->updateUser ()) {
					echo "<font size=\"3\" color='green'><b>The request has been registered.</b></font><br>";
					return;
				}
			}
		}
	} else if (isset ( $_POST ['bouton_login_reg'] )) {
		if ($formReg->validate ()) {
			$formReg->doLogin ();
		}
	} else if (isset ( $_POST ['bouton_forgot'] )) {
		if ($formReg->doForgot ()) {
			echo "<font size=\"3\" color='green'><b>A new password has been generated and sent to you by email.</b></font><br>";
		}
	}
	global $project_name;
	if (in_array($project_name, $MainProjects)) {
		$formReg->displayForm ( true );
	}else
		$formReg->displayForm ( false );
}

?>
