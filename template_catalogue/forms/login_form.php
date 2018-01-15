<?php
require_once ("HTML/QuickForm.php");
require_once ("HTML/QuickForm/radio.php");
require_once ("ldap/ldapConnect.php");
require_once ("mail.php");
require_once ("bd/journal.php");
require_once ("ldap/ldapUtils.php");
require_once ("conf/conf.php");
require_once ("ldap/guestuser.php");
class login_form extends HTML_QuickForm {
	var $user;
	var $groups;
	var $groupList;
	
	/**
	 * Test si l'utilisateur connecté est le pi de $dats.
	 */
	function isPi($dats, $projectName) {
		if (isset ( $dats )) {
			foreach ( $dats->dats_originators as $pi ) {
				if ((strtolower ( $pi->personne->pers_email_1 ) == strtolower ( $this->user->mail )) || (strtolower ( $pi->personne->pers_email_2 ) == strtolower ( $this->user->mail ))) {
					return true;
				}
			}
		}
		return false;
	}
	function isCreator($dats, $projectName) {
		if (isset ( $dats )) {
			$this->user = unserialize ( $_SESSION ['loggedUser'] );
			if ($dats->dats_creator == $this->user->mail) {
				return true;
			} else {
				return false;
			}
		}
	}
	function isAdmin($dats = null, $projectName = MainProject) {
		if ($this->isLogged ()) {
			if (! isset ( $dats )) {
				return true;
			} else {
				return ($this->isRoot () || (($projectName == MainProject) && $this->isPortalAdmin ()) || (($projectName != MainProject) && $this->isProjectAdmin ()));
			}
		} else {
			return false;
		}
	}
	function isPortalAdmin() {
		if ($this->isLogged ()) {
			return ($this->user->isPortalAdmin () || $this->user->isRoot ());
		} else {
			return false;
		}
	}
	function isProjectAdmin() {
		global $project_name;
		if ($this->isLogged ()) {
			return ($this->user->isProjectAdmin () || $this->user->isRoot ());
		} else {
			return false;
		}
	}
	function isRoot() {
		if ($this->isLogged ()) {
			return $this->user->isRoot ();
		} else {
			return false;
		}
	}
	function isCat($dats = null, $projectName=null) {
		if ($this->isLogged ()) {
			if (! isset ( $dats )) {
				return true;
			} else {
				return ($this->user->isRoot () || $this->isPi ( $dats, $projectName ) || $this->isCreator ( $dats, $projectName ) || $this->isAdmin ( $dats, $projectName ));
			}
		} else {
			return false;
		}
	}
	function isPortalUser() {
		if ($this->isLogged ()) {
			return ((get_class ( $this->user ) == MainProject . 'User') || $this->user->isRoot ());
		} else {
			return false;
		}
	}
	function isLogged() {
		return isset ( $this->user );
	}
	function genPassword($seed, $length) {
		$alphabet = "azertyuiopqsdfghjkmwxcvbnAWQZXSECDRVFTBGYNHUJKLPM23456789_-";
		$passwd = '';
		for($i = 0; $i < $length; $i ++) {
			$passwd .= $alphabet [mt_rand ( 0, strlen ( $alphabet ) - 1 )];
		}
		return $passwd;
	}
	function sendMailNewPassword($mail, $passwd, $project) {
		$texte = "Dear database user,\n\n" . "A new password has been generated.\n" . "Your username is: $mail";
		$texte .= "\nYour password is: " . $passwd;
		$texte .= "\n\nYou can access ".MainProject." data using the following link: http://".$_SERVER['HTTP_HOST']."/" . "\n\nBest regards,\nThe ".MainProject." database service";
		sendMailSimple ( $mail, MainProject." Database new password", $texte, ROOT_EMAIL );
	}
	function sendMailRegistration($mail, $passwd, $project) {
		// Envoi du mail
		global $project_name;
		if (! isset ( $project ))
			$project = ucfirst ( strtolower ( MainProject ) );
		$username = trim ( $_SESSION ['username'] );
		if (isset ( $username ) && ! empty ( $username ))
			$texte = "Dear " . $_SESSION ['username'] . ",\n\n";
		else
			$texte = "Dear database user,\n\n";
		if ($project == ucfirst ( strtolower ( MainProject ) )) {
			$texte .= "Your " . MainProject . " portal account was created.\n";
			$texte .= "Your access privileges will be temporarily limited to public datasets until your identity is verified and approved by the administrator.";
			$texte .= "\nYour username is $mail";
			if ($passwd) {
				$texte .= "\nYour password is " . $passwd;
			} else {
				$tmp = '';
				if (constant(strtolower ( $project_name ) . WebSite) != '')
					$tmp = ' or on the ' . constant(strtolower ( $project_name ) . WebSite) . ' website';
				
				$texte .= "\nYour password is the one you use to access data from other ".MainProject." portal projects$tmp.";
			}
		} else {
			$texte .= "Your registration to access $project_name data has been approved.\n";
		}
		
		if ($project == ucfirst ( strtolower ( MainProject ) )) {
			if (isset ( $project_name )) {
				$texte .= "\nOnce your registration to access $project_name data will be approved, you will receive a confirmation mail.";
			}
			$texte .= "\nTo access data from any of the " . MainProject . " projects you must fill the corresponding form that is available at your personal page http://".$_SERVER['HTTP_HOST']."/Your-Account/ .\n" . "\n\nBest regards,\nThe $project database service";
		} else if ($project == MainProject) {
			$texte .= "\n\nYou can access $project data using the following link: http://".$_SERVER['HTTP_HOST']."/" . "\n\nBest regards,\nThe $project database service";
		} else {
			$texte .= "\n\nYou can access $project data using the following link: http://".$_SERVER['HTTP_HOST']."/$project" . "\n\nBest regards,\nThe $project database service";
		}
		
		sendMailSimple ( $mail, "$project data portal account", $texte, ROOT_EMAIL );
		sendMailSimple ( ROOT_EMAIL, "[$project-DATABASE] New User", "User $mail has been registered", ROOT_EMAIL );
	}
	function initGroups() {
		if (isset ( $_SESSION ['ldapGroups'] ) && ! empty ( $_SESSION ['ldapGroups'] )) {
			$this->groupList = unserialize ( $_SESSION ['ldapGroups'] );
		} else {
			
			try {
				$ldap = new ldapConnect ();
				$ldap->openAdm ();
				$this->groupList = $ldap->listEntries ( GROUP_BASE, "(objectClass=group)", 'groupe' );
				$this->groups = array ();
				$_SESSION ['ldapGroups'] = serialize ( $this->groupList );
			} catch ( Exception $e ) {
				$this->mailAdmin ( 'ERREUR', 'Exception lors de la récupération des groupes.', $e );
			}
		}
		
		foreach ( $this->groupList as $group ) {
			$this->groups [$group->id] = $group->cn;
		}
	}
	function createLoginForm($labelId='login', $public = false) {
		$this->addElement ( 'submit', 'loginbutton', "Login" );
		$this->addElement ( 'text', 'login', $labelId );
		$this->applyFilter ( 'login', 'trim' );
		$this->addRule ( 'login', "$labelId is required", 'required' );
		if (! $public) {
			$this->addElement ( 'password', 'password', 'Password' );
			$this->applyFilter ( 'password', 'trim' );
			$this->addRule ( 'password', 'Password is required', 'required' );
			$this->addElement ( 'text', 'email_forgot', 'Mail' );
			$this->applyFilter ( 'email_forgot', 'trim' );
			$this->addElement ( 'submit', 'bouton_login', 'Login' );
			$this->addElement ( 'submit', 'bouton_forgot', 'Reset password' );
		} else {
			$this->addRule ( 'login', 'Mail is incorrect', 'email' );
			$this->addElement ( 'submit', 'bouton_public', 'Enter' );
		}
		if (isset ( $_POST ['loginError'] ) && ! empty ( $_POST ['loginError'] )) {
			$this->_errors = $_POST ['loginError'];
		}
	}
	function saveErrors() {
		if (! empty ( $this->_errors )) {
			$_POST ['loginError'] = $this->_errors;
		}
	}
	function forgottenPassword($project) {
		global $project_name;
		if (! isset ( $project ))
			$project = $project_name;
		$mail = $this->exportValue ( 'email_forgot' );
		if (isset ( $mail ) && ! empty ( $mail )) {
			try {
				$ldap = new ldapConnect ();
				$ldap->openAdm ();
				if ($ldap->exists ( $ldap->getUserDn ( $mail ) )) {
					$newpassword = $this->genPassword ( time (), 6 );
					$hashMd5 = md5 ( $newpassword );
					$md5Ldap = ldap_md5 ( $newpassword );
					if ($ldap->modifyAttribute ( $ldap->getUserDn ( $mail ), "userPassword", $md5Ldap )) {
						$this->sendMailNewPassword ( $mail, $newpassword, $project );
						if ($newpassword) {
							$infos = "L'utilisateur $mail a changé son mot de passe.\n\nmd5: $hashMd5 \nmd5 (ldap): " . $md5Ldap . "\nmdp: $newpassword";
							sendMailSimple ( Portal_Contact_Email, "[$project] New Password", $infos, ROOT_EMAIL );
						}
						return true;
					} else {
						$this->mailAdmin ( 'ERREUR', "Erreur dans modifyAttribute (forgotten password). mail: $mail." );
						$this->_errors [] = "The system was unable to modify your password. Please contact the website administrator.";
					}
				} else {
					$this->_errors [] = "No user is registered with this email address.";
				}
			} catch ( Exception $e ) {
				$this->mailAdmin ( 'ERREUR', "Erreur lors de la génération d'un nouveau mot de passe.", $e );
				$this->_errors [] = "An error occurred. Please contact the website administrator.";
			}
		} else {
			$this->_errors [] = "To receive a new password, please enter your email adress.";
		}
		return false;
	}
	function loginCat() {
		$username = $this->exportValue ( 'login' );
		if (strpos ( $username, '@' ))
			return $this->loginAdmin ();
		else
			return $this->loginSimple ();
	}
	function loginPublic() {
		$mail = $this->exportValue ( 'login' );
		if (isset ( $mail ) && ! empty ( $mail )) {
			$this->user = new guestuser ( $mail );
			$_SESSION ['loggedUser'] = serialize ( $this->user );
			return true;
		} else {
			return false;
		}
	}
	function loginSimple() {
		$ldapConn = new ldapConnect ();
		$username = $this->exportValue ( 'login' );
		$password = $this->exportValue ( 'password' );
		try {
			$this->user = $ldapConn->login ( 'cn=' . $username . ',' . LDAP_BASE, $password, 'person', 'user' );
			if (isset ( $this->user )) {
				$_SESSION ['loggedUser'] = serialize ( $this->user );
				return true;
			} else {
				$this->_errors [] = "Invalid username or password";
				return false;
			}
		} catch ( Exception $e ) {
			$this->mailAdmin ( 'ERREUR', "Erreur lors de l'authentification d'1 pi.", $e );
			$this->_errors [] = "The user directory is temporarily unavailable. Please contact the administrator.";
			return false;
		}
	}
	function loginAdmin() {
		$ldapConn = new ldapConnect ();
		$mail = $this->exportValue ( 'login' );
		$password = $this->exportValue ( 'password' );
		try {
			$this->user = $ldapConn->login ( 'mail=' . $mail . ',' . PEOPLE_BASE, $password );
			if (isset ( $this->user )) {
				$this->user->userPassword = $password;
				$_SESSION ['loggedUser'] = serialize ( $this->user );
				$journal = new journal ();
				$liste = $journal->getByUser ( $mail, TYPE_ABO );
				$abosIds = array ();
				foreach ( $liste as $entry )
					$abosIds [] = $entry->dats_id;
				$_SESSION ['loggedUserAbos'] = serialize ( $abosIds );
				return true;
			} else {
				$this->_errors [] = "Invalid username or password";
				return false;
			}
		} catch ( Exception $e ) {
			$this->mailAdmin ( 'ERREUR', "Erreur lors de l'authentification d'1 admin.", $e );
			$this->_errors [] = "The user directory is temporarily unavailable. Please contact the administrator.";
			return false;
		}
	}
	function mailAdmin($sujet, $msg, $e = null, $user = null) {
		global $project_name;
		$texte = $msg;
		if (isset ( $e )) {
			$texte .= "\n\nCause: " . $e;
		}
		if (isset ( $user )) {
			$texte .= "\n\User: " . $user->toString ();
		}
		sendMailSimple ( ROOT_EMAIL, "[" . $project_name . "] $sujet", $texte );
	}
	function displayPublicLogin($titre) {
		echo "<h1>$titre</h1><p/>";
		if (! empty ( $this->_errors )) {
			foreach ( $this->_errors as $error ) {
				echo '<font size="3" color="red">' . $error . '</font><br>';
			}
		}
		$reqUri = $_SERVER ['REQUEST_URI'];
		echo '<form action="' . $reqUri . '" method="post" name="frmlogin" id="frmlogin" >';
		echo '<table>';
		echo '<tr><td><font color="#467AA7">' . $this->getElement ( 'login' )->getLabel () . '</font></td><td>' . $this->getElement ( 'login' )->toHTML () . '</td></tr>';
		echo '<tr><td colspan="2" align="center">' . $this->getElement ( 'bouton_public' )->toHTML ();
		echo '</td></tr></table>';
		echo '</form>';
	}
	function displayLoginButton() {
		echo '<form method="post" action="' . $_SERVER ['REQUEST_URI'] . '" >';
		echo "&nbsp;" . $this->getElement ( 'loginbutton' )->toHTML ();
		echo '</form>';
	}
	function displayLGForm($titre, $withForgot = false, $displayForgot = false, $withForgotPass = false) {
		global $project_name;
		echo "<center style = 'padding-left : 120px ; padding-right: 120px;' >";
		echo "<table>";
		echo "<br><h5>If you are already registered to the ".MainProject." database, please use your ids to login in the following form:</h5>";
		if ($withForgotPass == true) {
			if ($this->forgottenPassword (MainProject)) {
				echo "<h4 style='color:green'> A new password has been generated and sent to you by email, please use it to login.</h4>";
			}
		}
		$this->displayLoginForm ( $titre, $withForgot, $displayForgot );
		$reqUri = '/User-Account-Creation';
		if ($project_name != strToLower(MainProject))
			$reqUri = '/' . $project_name . '/Register';
		echo "<a href='" . $reqUri . "'><h5 style ='color:rgb(70,122,167);'>If you are a new user, please click here to register.</h5></a> <br>";
		echo "</table></center>";
	}
	function displayLoginForm($titre, $withForgot = false, $displayForgot = false) {
		echo '<SCRIPT LANGUAGE="Javascript" SRC="/js/forgot.js"> </SCRIPT>';
		echo "<h1>$titre</h1><p/>";
		// Affichage des erreurs
		if (! empty ( $this->_errors )) {
			foreach ( $this->_errors as $error ) {
				echo '<font size="3" color="red">' . $error . '</font><br>';
			}
		}
		$reqUri = $_SERVER ['REQUEST_URI'];
		if ($reqUri == '/User-Account-Creation/')
			$reqUri = '/';
		echo '<form action="' . $reqUri . '" method="post" name="frmlogin" id="frmlogin" >';
		echo '<table>';
		echo '<tr id="forgot_row_3"><td><font color="#467AA7">login</font></td><td>' . $this->getElement ( 'login' )->toHTML () . '</td></tr>';
		echo '<tr id="forgot_row_4"><td><font color="#467AA7">' . $this->getElement ( 'password' )->getLabel () . '</font></td><td>' . $this->getElement ( 'password' )->toHTML () . '</td></tr>';
		echo '<tr id="forgot_row_5"><td colspan="2" align="center">' . $this->getElement ( 'bouton_login' )->toHTML (); // .'</td></tr>';
		if ($withForgot) {
			echo '<br><a style="cursor:pointer;font-size: 80%;" onclick="showForgotForm()">Forgotten password</a>';
		}
		echo '</td></tr>';
		
		if ($withForgot) {
			if ($displayForgot) {
				$display = '';
			} else {
				$display = 'style="display: none;"';
			}
			$this->getElement ( 'email_forgot' )->setValue ( $this->exportValue ( 'login' ) );
			
			echo '<tr id="forgot_row_1" ' . $display . '><td><font color="#467AA7">' . $this->getElement ( 'email_forgot' )->getLabel () . '</font></td><td>' . $this->getElement ( 'email_forgot' )->toHTML () . '</td></tr>';
			echo '<tr id="forgot_row_2" ' . $display . '><td colspan="2" align="center">' . $this->getElement ( 'bouton_forgot' )->toHTML () . '</td></tr>';
		}
		echo '</table>';
		echo '</form>';
	}
}

?>
