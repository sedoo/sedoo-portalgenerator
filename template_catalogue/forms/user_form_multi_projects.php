<?php
require_once ("forms/login_form.php");
require_once ("ldap/ldapConnect.php");
require_once ("ldap/projectUser.php");
require_once ("ldap/portalUser.php");
require_once ("countries.php");
require_once ("mail.php");

// class user_form_new extends HTML_QuickForm{
class user_form_new extends login_form {
	var $demande;
	var $project;
	var $projects;
	var $msg;
	function createForm($testMail = true) {
		$this->addElement ( 'hidden', 'check_result' );
		$this->addElement ( 'hidden', 'user' );
		$this->addElement ( 'text', 'mail', 'Mail', array (
				'size' => 40,
				'id' => 'mail' 
		) );
		$this->applyFilter ( 'mail', 'trim' );
		if ($testMail) {
			$this->addRule ( 'mail', 'Mail is required', 'required' );
			$this->addRule ( 'mail', 'Mail is incorrect', 'email' );
		}
		$this->addElement ( 'submit', 'bouton_check', 'Ok' );
		$this->addElement ( 'password', 'password', 'Password' );
		$this->applyFilter ( 'password', 'trim' );
		$this->addElement ( 'text', 'login', 'Mail' );
		$this->addElement ( 'text', 'email_forgot', 'Mail' );
		$this->addElement ( 'submit', 'bouton_login_reg', 'Login' );
		$this->addElement ( 'submit', 'bouton_forgot', 'Forgotten password', array (
				'style' => 'background-color: #e1e1e1;border:none;cursor:pointer;color: #467aa7;' 
		) );
		$this->addElement ( 'button', 'bouton_sign', 'Sign Data Policy', array (
				'onclick' => 'displayDatapolicy();' 
		) );
		$this->addElement ( 'submit', 'bouton_update', 'Update' );
		$this->addElement ( 'submit', 'bouton_modify', 'Modify' );
		$this->createFormRegister ();
		$chk = $this->getElement ( 'check_result' )->getValue ();
		if ($chk) {
			if ($chk == 'log') {
				$this->addRule ( 'password', 'Password is required', 'required' );
				$this->initUser ();
			} else {
				if ($chk != 'new')
					$this->initUser ();
				$this->addValidationRules ( $chk == 'new' );
			}
		}
	}
	function doLogin() {
		$this->getElement ( 'login' )->setValue ( $this->getElement ( 'mail' )->getValue () );
		$result = $this->loginAdmin ( $this->project );
		if ($result) {
			$this->getElement ( 'check_result' )->setValue ( 'sign' );
			unset ( $_SESSION ['loggedUser'] );
		}
		return $result;
	}
	function doForgot() {
		$this->getElement ( 'email_forgot' )->setValue ( $this->getElement ( 'mail' )->getValue () );
		return $this->forgottenPassword ( $this->project );
	}
	function addValidationRules() {
		$this->registerRule ( 'valid_xor', 'function', 'valid_xor' );
		$this->registerRule ( 'not_in_directory', 'function', 'not_in_directory' );
		$this->addRule ( 'lastname', 'Family Name is required', 'required' );
		$this->addRule ( 'firstname', 'First Name is required', 'required' );
		$this->addRule ( 'affiliation', 'Affiliation is required', 'required' );
		$this->addRule ( 'street', 'Address is required', 'required' );
		$this->addRule ( 'city', 'City is required', 'required' );
		$this->addRule ( 'zip', 'Zip Code is required', 'required' );
		$this->addRule ( 'country', 'Country is required', 'required' );
		$this->addRule ( 'country', 'Country is required', 'minlength', 2 );
		$this->addRule ( 'phone', 'Phone Number is required', 'required' );
		$this->addRule ( 'abstract', 'Description of Work is required', 'required' );
		$this->addRule ( 'abstract', 'Description of Work must contain at least 350 characters', 'minlength', 350 );
		$this->addRule ( 'supervisor_name', 'Supervisor: affiliation is required', 'valid_xor', array (
				$this,
				'supervisor_affiliation' 
		) );
		$this->addRule ( 'supervisor_affiliation', 'Supervisor: name is required', 'valid_xor', array (
				$this,
				'supervisor_name' 
		) );
	}
	private function createFormRegister() {
		$this->addElement ( 'text', 'lastname', 'Family Name' );
		$this->applyFilter ( 'lastname', 'trim' );
		$this->addElement ( 'text', 'firstname', 'First Name' );
		$this->applyFilter ( 'firstname', 'trim' );
		$this->addElement ( 'text', 'affiliation', 'Affiliation', array (
				'size' => 40 
		) );
		$this->applyFilter ( 'affiliation', 'trim' );
		$this->addElement ( 'textarea', 'street', 'Address', array (
				'cols' => 50,
				'rows' => 3 
		) );
		$this->applyFilter ( 'street', 'trim' );
		$this->addElement ( 'text', 'city', 'City' );
		$this->applyFilter ( 'city', 'trim' );
		$this->addElement ( 'text', 'zip', 'Zip Code', array (
				'size' => 10 
		) );
		$this->applyFilter ( 'zip', 'trim' );
		$country_select = & $this->createElement ( 'select', 'country', 'Country', countries::$countries ); // ,array('onchange' => $onchange));
		$this->addElement ( $country_select );
		$this->addElement ( 'textarea', 'abstract', 'Description of work, including intended publication(s)  (more than 350 characters, the text will be sent to the data provider(s) upon data downloading)', array (
				'cols' => 50,
				'rows' => 8 
		) );
		$this->applyFilter ( 'abstract', 'trim' );
		$this->addElement ( 'text', 'phone', 'Phone Number' );
		$this->applyFilter ( 'phone', 'trim' );
		$this->addElement ( 'text', 'supervisor_name', 'Supervisor Name',array ( ));
		$this->applyFilter ( 'supervisor_name', 'trim' );
		$this->addElement ( 'text', 'supervisor_affiliation', 'Supervisor Affiliation',array( ));
		$this->applyFilter ( 'supervisor_affiliation', 'trim' );
		$this->addElement ( 'select', 'project_data_policy', '' );
		for($i = 0; $i < PortalNbSignDataPolicy; $i ++) {
			$obj_charte [] = &HTML_QuickForm::createElement ( 'checkbox', 'chart_' . $i, null, '&nbsp;' . constant('PortalSignDataPolicy' . $i)); 
		}
		$this->addGroup ( $obj_charte, 'chkChart', null, '<br />' );
		$obj_charte = null;
		$this->addProjectDataPolicy ();
		$this->addElement ( 'submit', 'bouton_save', 'Apply' );
	}
	function addProjectDataPolicy() {
		if (isset ( $this->project )) {
			foreach ( $this->project as $proj ) {
				if (isset ( $proj ) && ! empty ( $proj )) {
					
					// for portal form
					$this->addElement ( 'textarea', $proj . '_abstract', 'Description of Work (more information if any) ', array (
							'cols' => 50,
							'rows' => 8 
					) );
					$this->applyFilter ( $proj . '_abstract', 'trim' );
					
					// for project form
					$this->addElement ( 'textarea', $proj . '_abstract_1', 'Description of Work (more than 350 characters, The text will be sent to the data provider upon data downloading) ', array (
							'cols' => 50,
							'rows' => 8 
					) );
					$this->applyFilter ( $proj . '_abstract_1', 'trim' );
					
					try {
						$ldap = new ldapConnect ();
						$ldap->openAdm ();
						$projectList = $ldap->listEntries ( PROJECT_BASE, "(parentProject=$proj)", 'projet', 'cn' );
					} catch ( Exception $e ) {
						$this->mailAdmin ( 'ERREUR', 'Exception lors de la récupération des projets.', $e );
					}
					
					for($i = 1; $i <= count ( $projectList ); $i ++) {
						$this->projects [$proj] [$i] = $projectList [$i]->cn . ' - ' . $projectList [$i]->description;
						$this->addElement ( 'checkbox', $proj . '_wg_' . $i, $this->projects [$proj] [$i] );
					}
					if (defined (strtolower ( $proj ) . 'SubProjects') ) {
						$this->addElement ( 'text', $proj . '_sub_project', "Do you participate in one of the $proj subprojects ?" );
						$this->applyFilter ( $proj . '_sub_project', 'trim' );
					}
					// Charte
					if ( defined( strtolower ( $proj ) . 'NbSignDataPolicy') ) {
						$i = 0;
						for($i = 0; $i < constant(strtolower ( $proj ) . 'NbSignDataPolicy'); $i ++) {
							$obj_charte [] = &HTML_QuickForm::createElement ( 'checkbox', $proj . '_chart_' . $i, null, '&nbsp;' . (constant(strtolower ( $proj ) . 'SignDataPolicy' . $i)) );
						}
						$this->addGroup ( $obj_charte, $proj . '_chkChart', null, '<br />' );
						$obj_charte = null;
					}
				}
			}
		}
	}
	function displayForm($isPortalForm) {
		$chk = $this->getElement ( 'check_result' )->getValue ();
		// Affichage des erreurs
		if (! empty ( $this->_errors )) {
			foreach ( $this->_errors as $error ) {
				echo '<font size="3" color="red">' . $error . '</font><br>';
			}
		} else if ($this->msg)
			echo "<font size=\"3\" color='green'>$this->msg</font><br>";
		
		$reqUri = $_SERVER ['REQUEST_URI'];
		echo '<form action="' . $reqUri . '" method="post" name="frmuser" id="frmuser" >';
		echo '<SCRIPT LANGUAGE="Javascript" SRC="/js/functions.js"> </SCRIPT>';
		echo "<script type='text/javascript'>
    				$(function (){UseDialogForm();})
				</script>";
		echo $this->getElement ( 'check_result' )->toHTML ();
		echo $this->getElement ( 'user' )->toHTML ();
		
		if ($chk) {
			if ($chk == 'rien') {
				// Rien à afficher
			} else if ($chk == 'log') {
				$this->disableElement ( 'mail' );
			} else {
				if ($chk == 'new') {
					$this->disableElement ( 'mail' );
					$this->displayFormRegister ( false, $isPortalForm );
				} else {
					$this->displayFormRegister ( true, $isPortalForm );
				}
			}
		} else
			$this->displayFormCheck ();
		echo '</form>';
	}
	function addProjectAbstract() {
		global $project_name, $MainProjects;
		$this->user = unserialize ( $_SESSION ['loggedUser'] );
		foreach ( $MainProjects as $proj ) {
			$this->addElement ( 'textarea', $proj . '_abstract', 'Description of Work (more information if any) ', array (
					'cols' => 50,
					'rows' => 4 
			) );
			$this->applyFilter ( $proj . '_abstract', 'trim' );
		}
	}
	function displayPortalDataPolicy($isPortalForm = false) {
		$this->getUserFromLdap ();
		$user = $this->user;
		if ($isPortalForm == false) {
			echo '<br><table class ="ui-widget-content">';
			echo '<form id="frmdatapolicy" name="frmdatapolicy">';
			echo '<div id="user_infos">';
			echo $this->getElement ( 'mail' )->toHTML ();
			echo $this->getElement ( 'lastname' )->toHTML ();
			echo $this->getElement ( 'firstname' )->toHTML ();
			echo $this->getElement ( 'affiliation' )->toHTML ();
			echo $this->getElement ( 'country' )->toHTML ();
			echo $this->getElement ( 'city' )->toHTML ();
			echo $this->getElement ( 'zip' )->toHTML ();
			echo $this->getElement ( 'street' )->toHTML ();
			echo $this->getElement ( 'supervisor_name' )->toHTML ();
			echo $this->getElement ( 'abstract' )->toHTML ();
			echo $this->getElement ( 'supervisor_affiliation' )->toHTML ();
			echo $this->getElement ( 'phone' )->toHTML ();
			echo '</div>';
			echo "<script type='text/javascript'>
    				document.getElementById('user_infos').style.display= 'none' ;
				</script>";
		}
		if (isset ( $user->attrs [strtolower ( MainProject ) . 'Status'] ) && ! empty ( $user->attrs [strtolower ( MainProject ) . 'Status'] )) {
			if ($isPortalForm == false) {
				echo '<tr width=600px><td width=600px colspan="3" align="center" class="ui-state-error-text"><h4>' . MainProject . ' data policy already signed</h4></td></tr>';
				if ($user->attrs [strtolower ( MainProject ) . 'Status'] [0] == STATUS_ACCEPTED)
					$msg = "You are already registered to access data.";
				else if ($user->attrs [strtolower ( MainProject ) . 'Status'] [0] == STATUS_REJECTED)
					$msg = "A request has already been rejected for this email address.";
				else if ($user->attrs [strtolower ( MainProject ) . 'Status'] [0] == STATUS_PENDING)
					$msg = "You have already submitted a request. You will get an answer by email soon.";
				echo "<tr width=600px><td width=600px colspan='3' align='center' class='ui-state-highlight'><font style='color:green;'>$msg</font></td></tr>";
			}
		} else {
			if (! isset ( $user->abstract ) && ! isset ( $this->user->abstract ) && ! isset ( $this->user->abstract )) {
				if ($isPortalForm == false) {
					echo '<tr><td><font color="#467AA7">' . $this->getElement ( 'abstract' )->getLabel () . '</font></td><td colspan="2">' . $this->getElement ( 'abstract' )->toHTML () . '</td></tr>';
					$this->addRule ( 'abstract', 'Description of Work is required', 'required' );
					$this->addRule ( 'abstract', 'Description of Work must contain at least 350 characters', 'minlength', 350 );
				}
			} else
				echo '<tr><td><font>' . $this->getElement ( 'abstract' )->getLabel () . '</font></td><td colspan="2">' . $this->getElement ( 'abstract' )->toHTML () . '</td></tr>';
			echo '<tr><td colspan="3" align="center"><font color="#467AA7">Data and Publication Policy</font></td></tr>';
			echo '<tr><td colspan="3" >';
			echo 'With this form, I fully accept the <a href="/portal/Data-Policy" target="_blank" >data and publication policy</a><br/><br/>In particular :<br>';
			echo $this->getElement ( 'chkChart' )->toHTML ();
			echo '</td></tr>';
		}
		if ($isPortalForm == false) {
			echo '<tr><td colspan="3" align="center">' . $this->getElement ( 'bouton_save' )->toHTML () . '</td></tr>';
			echo '</form></table>';
		}
	}
	function displayFormRegister($update = false, $isPortalForm = false) {
		global $MainProjects;
		echo '<table><tr><td colspan="3" align="center"><font color="#467AA7">Mandatory fields are in blue</font></td></tr>';
		echo '<tr><td><font color="#467AA7">' . $this->getElement ( 'firstname' )->getLabel () . '</font></td><td colspan="2">' . $this->getElement ( 'firstname' )->toHTML () . '</td></tr>';
		echo '<tr><td><font color="#467AA7">' . $this->getElement ( 'lastname' )->getLabel () . '</font></td><td colspan="2">' . $this->getElement ( 'lastname' )->toHTML () . '</td></tr>';
		
		echo '<tr><td><font color="#467AA7">' . $this->getElement ( 'affiliation' )->getLabel () . '</font></td><td colspan="2">' . $this->getElement ( 'affiliation' )->toHTML () . '</td></tr>';
		echo '<tr><td colspan="3" align="center"><b>Place of Work</b></td></tr>';
		echo '<tr><td><font color="#467AA7">' . $this->getElement ( 'street' )->getLabel () . '</font></td><td colspan="2">' . $this->getElement ( 'street' )->toHTML () . '</td></tr>';
		echo '<tr><td><font color="#467AA7">' . $this->getElement ( 'zip' )->getLabel () . '</font></td><td colspan="2">' . $this->getElement ( 'zip' )->toHTML () . '</td></tr>';
		echo '<tr><td><font color="#467AA7">' . $this->getElement ( 'city' )->getLabel () . '</font></td><td colspan="2">' . $this->getElement ( 'city' )->toHTML () . '</td></tr>';
		echo '<tr><td><font color="#467AA7">' . $this->getElement ( 'country' )->getLabel () . '</font></td><td colspan="2">' . $this->getElement ( 'country' )->toHTML () . '</td></tr>';
		echo '<tr><td colspan="3" align="center"><b>Contact</b></td></tr>';
		echo '<tr><td><font color="#467AA7">' . $this->getElement ( 'phone' )->getLabel () . '</font></td><td colspan="2">' . $this->getElement ( 'phone' )->toHTML () . '</td></tr>';
		echo '<tr><td><font color="#467AA7">' . $this->getElement ( 'mail' )->getLabel () . '</font></td><td colspan="2">' . $this->getElement ( 'mail' )->toHTML () . '</td></tr>';
		echo '<tr><td colspan="3" align="center"><b>Planned Work</b></font></td></tr>';
		echo '<tr><td><font color="#467AA7">' . $this->getElement ( 'abstract' )->getLabel () . '</font></td><td colspan="2">' . $this->getElement ( 'abstract' )->toHTML () . '</td></tr>';
		if ($update == true) {
			foreach ( $MainProjects as $proj ) {
				if (array_key_exists ( strtolower ( $proj ) . 'ApplicationDate', $this->user->attrs [strtolower ( $proj )] ) || array_key_exists ( strtolower ( $proj ) . 'ApplicationDate', $this->user->attrs )) {
					echo '<tr><td><font> Work in ' . $proj . ' (more information if any)  </font></td><td colspan="2">' . $this->getElement ( $proj  . '_abstract' )->toHTML () . '</td></tr>';
				}
			}
		}
		echo '<tr><td colspan="3" align="center"><b>Supervisor</b></td></tr>';
		echo '<tr><td colspan="3" >If you are a student, please indicate the name and the affiliation of your supervisor.</td></tr>';
		echo '<tr><td>' . $this->getElement ( 'supervisor_name' )->getLabel () . '</td><td colspan="2">' . $this->getElement ( 'supervisor_name' )->toHTML () . '</td></tr>';
		echo '<tr><td>' . $this->getElement ( 'supervisor_affiliation' )->getLabel () . '</td><td colspan="2">' . $this->getElement ( 'supervisor_affiliation' )->toHTML () . '</td></tr>';
		// Data policy
		if ($update != true) {
			if ($isPortalForm == false) {
				$this->displayPortalDataPolicy ( true );
			} else {
				$this->displayProjectDataPolicy ( true );
			}
		}
		if ($update) {
			echo '<tr><td colspan="3" align="center">' . $this->getElement ( 'bouton_update' )->toHTML () . '</td></tr></table>';
		} else {
			echo '<tr><td colspan="3" align="center">' . $this->getElement ( 'bouton_save' )->toHTML () . '</td></tr></table>';
		}
	}
	function displayProjectDataPolicy($isPortalForm = false) {
		// Affichage des erreurs
		$this->getUserFromLdap ();
		$user = $this->user;
		if (isset ( $this->project )) {
			if ($isPortalForm == false) {
				echo '<br><table class ="ui-widget-content">';
				echo '<form id="frmdatapolicy" name="frmdatapolicy">';
				echo '<div id="user_infos">';
				echo $this->getElement ( 'mail' )->toHTML ();
				echo $this->getElement ( 'lastname' )->toHTML ();
				echo $this->getElement ( 'firstname' )->toHTML ();
				echo $this->getElement ( 'affiliation' )->toHTML ();
				echo $this->getElement ( 'country' )->toHTML ();
				echo $this->getElement ( 'city' )->toHTML ();
				echo $this->getElement ( 'zip' )->toHTML ();
				echo $this->getElement ( 'street' )->toHTML ();
				echo $this->getElement ( 'supervisor_name' )->toHTML ();
				echo $this->getElement ( 'abstract' )->toHTML ();
				echo $this->getElement ( 'supervisor_affiliation' )->toHTML ();
				echo $this->getElement ( 'phone' )->toHTML ();
				echo '</div>';
				echo "<script type='text/javascript'>
    				document.getElementById('user_infos').style.display= 'none' ;
				</script>";
			}
			foreach ( $this->project as $proj ) {
				
				if (isset ( $proj )) {
					if ($isPortalForm == false) {
						echo "<tr ><td colspan='3' style='background:white;'></td></tr><tr><td colspan='3' align='center' ><h2>$proj Data Policy</h2></td></tr>";
					}
					//en cours de développement
					if (isset ( $user->attrs [strtolower ( $proj ) . 'Status'] ) && ! empty ( $user->attrs [strtolower ( $proj ) . 'Status'] )) {
						if ($isPortalForm == false) {
							echo '<tr width=600px><td width=600px colspan="3" align="center" class="ui-state-error-text"><h4>' . $proj . ' data policy already signed</h4></td></tr>';
							if ($user->attrs [strtolower ( $proj ) . 'Status'] [0] == STATUS_ACCEPTED)
								$msg = "You are already registered to access data.";
							else if ($user->attrs [strtolower ( $proj ) . 'Status'] [0] == STATUS_REJECTED)
								$msg = "A request has already been rejected for this email address.";
							else if ($user->attrs [strtolower ( $proj ) . 'Status'] [0] == STATUS_PENDING)
								$msg = "You have already submitted a request. You will get an answer by email soon.";
							echo "<tr width=600px><td width=600px colspan='3' align='center' class='ui-state-highlight'><font style='color:green;'>$msg</font></td></tr>";
						}
					} else {
						if ($isPortalForm == false) {
							echo '<tr><td colspan="3" align="center" class="ui-state-highlight"><b>Planned Work</b></font></td></tr>';
							
							if (! isset ( $user->abstract ) && ! isset ( $user->attrs [strtolower ( $proj ) . 'Abstract'] [0] )) {
								echo '<tr  class="ui-state-highlight"><td><font color="#467AA7">' . $this->getElement ( $proj . '_abstract' )->getLabel () . '</font></td><td colspan="2">' . $this->getElement ( $proj . '_abstract' )->toHTML () . '</td></tr>';
								$this->addRule ( $proj . '_abstract', 'Description of Work is required', 'required' );
								$this->addRule ( $proj . '_abstract', 'Description of Work must contain at least 350 characters', 'minlength', 350 );
							} else
								echo '<tr  class="ui-state-highlight"><td><font>' . $this->getElement ( $proj . '_abstract' )->getLabel () . '</font></td><td colspan="2">' . $this->getElement ( $proj . '_abstract' )->toHTML () . '</td></tr>';
						}
						echo "<tr class='ui-state-highlight'><td colspan='2'><font color='black'>Do you participate in one of the $proj working groups ?</font></td><td><font color='black'>";
						for($i = 1; $i <= count ( $this->projects [$proj] ); $i ++) {
							echo '<br>' . $this->getElement ( $proj . '_wg_' . $i )->toHTML () . '&nbsp;' . $this->getElement ( $proj . '_wg_' . $i )->getLabel ();
						}
						echo '</font></td></tr>';
						
						if (constant(strtolower ( $proj ) .'SubProjects') != '') {
							echo '<tr  class="ui-state-highlight"><td colspan="2"><font color="black">' . $this->getElement ( $proj . '_sub_project' )->getLabel () . '</font></td>';
							echo '<td>' . $this->getElement ( $proj . '_sub_project' )->toHTML () . '</td></tr>';
						}
						echo '<tr class=""><td class="ui-state-highlight" colspan="3" align="center"><font color="#467AA7">Data and Publication Policy</font></td></tr>';
						echo '<tr class="ui-state-highlight"><td colspan="3" ><font color="black">';
						echo "With this form, I fully accept the $proj data and publication policy (<a href='/$proj/Data-Policy/" . $proj . "_DataPolicy.pdf' target='_blank'>click here to access pdf file</a>). In particular:<br>";
						echo $this->getElement ( $proj . '_chkChart' )->toHTML ();
						//En commentaire car je n'ai pas pu le rendre générique pour l'instant - Nizar
						/*
						echo "<br/><br/>In the case of any publication$tmp using datasets or products obtained in the database:<br>";
						echo "<br/><br/>If I process a dataset or product based on data received under the $proj data policy:<br>";
						*/
						echo '</font>';
					}
					echo '</td></tr>';
				}
			}
			if ($isPortalForm == false) {
				echo '<tr><td colspan="3" align="center">' . $this->getElement ( 'bouton_save' )->toHTML () . '</td></tr></table>';
				echo '</form></table>';
			}
		}
	}
	private function displayFormCheck() {
		echo '<br/><font size ="3.5">Please enter your email address:</font>';
		echo '<table>';
		echo '<tr><td><font color="#467AA7">' . $this->getElement ( 'mail' )->getLabel () . '</font></td><td>' . $this->getElement ( 'mail' )->toHTML () . '</td></tr>';
		echo '<tr><td colspan="2" align="center">' . $this->getElement ( 'bouton_check' )->toHTML () . '</td></tr>';
		echo '</table>';
	}
	function displayModifyButton() {
		echo '<form method="post" action="/Your-Account/?p&pageId=9" >';
		echo "&nbsp;" . $this->getElement ( 'bouton_modify' )->toHTML ();
		echo '</form>';
	}
	private function displayFormLogin() {
		echo '<table>';
		echo '<tr><td><font color="#467AA7">' . $this->getElement ( 'mail' )->getLabel () . '</font></td><td>' . $this->getElement ( 'mail' )->toHTML () . '</td></tr>';
		echo '<tr><td><font color="#467AA7">' . $this->getElement ( 'password' )->getLabel () . '</font></td><td>' . $this->getElement ( 'password' )->toHTML () . '</td></tr>';
		echo '<tr><td colspan="2" align="center">' . $this->getElement ( 'bouton_login_reg' )->toHTML () . '</td></tr>';
		echo '<tr><td colspan="2" align="center">' . $this->getElement ( 'bouton_forgot' )->toHTML () . '</td></tr>';
		echo '</table>';
	}
	function saveForm($isPortalForm = false) {
		$this->demande = new portalUser ();
		$this->demande->lastname = $this->exportValue ( 'lastname' );
		$this->demande->firstname = $this->exportValue ( 'firstname' );
		$this->demande->cn = $this->demande->firstname . ' ' . $this->demande->lastname;
		$this->demande->lastname = ucfirst ( strtolower ( $this->demande->lastname ) );
		$this->demande->mail = $this->exportValue ( 'mail' );
		$this->demande->affiliation = $this->exportValue ( 'affiliation' );
		$this->demande->street = $this->exportValue ( 'street' );
		$this->demande->zipCode = $this->exportValue ( 'zip' );
		$this->demande->city = $this->exportValue ( 'city' );
		$this->demande->country = $this->exportValue ( 'country' );
		$this->demande->phoneNumber = $this->exportValue ( 'phone' );
		$this->demande->supervisor_name = $this->exportValue ( 'supervisor_name' );
		$this->demande->supervisor_affiliation = $this->exportValue ( 'supervisor_affiliation' );
		$this->demande->applicationDate = date ( FORMAT_DATE );
		$this->demande->abstract = $this->exportValue ( 'abstract' );
		$this->demande->status = STATUS_PENDING;
		if ($isPortalForm != false) {
			$this->saveDataPolicyForm ( true );
		}
		$_SESSION ['username'] = $this->demande->cn;
	}
	function savePortalDataPolicyForm() {
		if (! isset ( $this->demande ))
			$this->demande = unserialize ( $_SESSION ['loggedUser'] );
		$this->demande->abstract = $this->exportValue ( 'abstract' );
		$_SESSION ['username'] = $this->demande->cn;
	}
	function saveDataPolicyForm($isPortalForm = false) {
		if (! isset ( $this->demande ))
			$this->demande = unserialize ( $_SESSION ['loggedUser'] );
		if (isset ( $this->project )) {
			foreach ( $this->project as $proj ) {
				
				$this->demande->otherUser [$proj] = new projectUser ();
				if ($isPortalForm == false) {
					$this->demande->otherUser [$proj]->abstract = $this->exportValue ( $proj . '_abstract' );
				} else {
					$this->demande->otherUser [$proj]->abstract = $this->exportValue ( 'abstract' );
				}
				for($i = 1; $i <= count ( $this->projects [$proj] ); $i ++) {
					if ($this->getElement ( $proj . '_wg_' . $i )->getChecked ()) {
						$this->demande->otherUser [$proj]->wg [] = $this->projects [$proj] [$i];
					}
					if (constant(strtolower ( $proj ) .'SubProjects') != '') {
						$this->demande->otherUser [$proj]->subProject = $this->exportValue ( $proj . '_sub_project' );
					}
				}
			}
		}
		$_SESSION ['username'] = $this->demande->cn;
	}
	function mailAdmin($sujet, $msg, $e = null, $user = null) {
		if (isset ( $this->project )) {
			foreach ( $this->project as $proj ) {
				$texte = $msg;
				if (isset ( $e )) {
					$texte .= "\n\nCause: " . $e;
				}
				if (isset ( $user )) {
					$texte .= "\n\User: " . $user->toString ();
				}
				sendMailSimple ( ROOT_EMAIL, "[$proj] $sujet", $texte );
			}
		} else {
			$texte = $msg;
			if (isset ( $e )) {
				$texte .= "\n\nCause: " . $e;
			}
			if (isset ( $user )) {
				$texte .= "\n\User: " . $user->toString ();
			}
			sendMailSimple ( ROOT_EMAIL, "$sujet", $texte );
		}
	}
	function addUser($isPortalForm) {
		if ($this->addPortalUser ( $isPortalForm ))
			return true;
		else
			return false;
	}
	function addProjectUser($isPortalForm = false) {
		if (isset ( $_SESSION ['loggedUser'] ))
			$this->user = unserialize ( $_SESSION ['loggedUser'] );
		$ldap = new ldapConnect ();
		$ldap->openAdm ();
		// pour tester si le champ description existe ou pas
		if (isset ( $this->demande->mail ) && ! empty ( $this->demande->mail ))
			$entry = $ldap->getEntry ( 'mail=' . $this->demande->mail . ',' . PEOPLE_BASE, $retClass = strtolower ( MainProject ) . 'User' );
		elseif (isset ( $this->user->mail ) && ! empty ( $this->user->mail )) {
			$entry = $ldap->getEntry ( 'mail=' . $this->user->mail . ',' . PEOPLE_BASE, $retClass = strtolower ( MainProject ) . 'User' );
		}
		$entry = $entry->getUserEntry ();
		
		try {
			
			if (isset ( $this->project )) {
				foreach ( $this->project as $proj ) {
					$entree = array (
							'objectClass' => strtolower ( $proj ) . 'User',
							strtolower ( $proj ) . 'ApplicationDate' => date ( FORMAT_DATE ),
							strtolower ( $proj ) . 'Status' => STATUS_PENDING 
					);
					if (isset ( $this->demande->otherUser [$proj]->abstract ) && ! empty ( $this->demande->otherUser [$proj]->abstract )) {
						$entree [strtolower ( $proj ) . 'Abstract'] = $this->demande->otherUser [$proj]->abstract;
					}
					
					if (! array_key_exists ( 'description', $entry )) {
						if (isset ( $this->demande->otherUser [$proj]->abstract ) && ! empty ( $this->demande->otherUser [$proj]->abstract )) {
							$entree ['description'] = $this->demande->otherUser [$proj]->abstract;
							$this->demande->abstract = $this->demande->otherUser [$proj]->abstract;
						}
					}
					if (isset ( $this->demande->otherUser [$proj]->wg ) && ! empty ( $this->demande->otherUser [$proj]->wg )) {
						$entree [strtolower ( $proj ) . 'Wg'] = $this->demande->otherUser [$proj]->wg;
					}
					if (constant(strtolower ( $proj ) . 'SubProjects') != '') {
						if (isset ( $this->demande->otherUser [$proj]->subProject ) && ! empty ( $this->demande->otherUser [$proj]->subProject )) {
							$entree [strtolower ( $proj ) . 'AssociatedProject'] = $this->demande->otherUser [$proj]->subProject;
						}
					}
					
					if ($ldap->addAttributes ( $this->demande->getUserDn (), $entree )) {
						$this->mailAdmin ( "NEW $proj USER", "Un utilisateur a demandé un accès aux données $proj.", null, $this->demande->otherUser [$proj] );
						$this->user->attrs [strtolower ( $proj )] = $entree;
						if ($isPortalForm == false) {
							$_SESSION ['loggedUser'] = serialize ( $this->user );
						}
						return true;
					} else {
						$this->mailAdmin ( "ERROR NEW $proj USER", "Echec de l'enregistrement de l'utilisateur suivant :", null, $this->demande->otherUser [$proj] );
						return false;
					}
				}
			} else
				return false;
		} catch ( Exception $e ) {
			echo "<font size=\"3\" color='red'><b>The user directory is temporarily unavailable. Please contact the administrator.</b></font><br>";
			$this->mailAdmin ( 'ERREUR', "Erreur lors de l'enregistrement d'1 utilisateur.", $e, $this->demande [$proj] );
			return false;
		}
	}
	function requestPortalDataAccess() {
		if (isset ( $_SESSION ['loggedUser'] ))
			$this->user = unserialize ( $_SESSION ['loggedUser'] );
		$ldap = new ldapConnect ();
		$ldap->openAdm ();
		try {
			$entry ["objectClass"] [] = strtolower ( MainProject ) . 'User';
			$entry [strtolower ( MainProject ) . 'ApplicationDate'] = date ( FORMAT_DATE );
			$entry [strtolower ( MainProject ) . 'Status'] = STATUS_PENDING;
			if (isset ( $this->demande->abstract )) {
				$desc ['description'] = $this->demande->abstract;
				if ($ldap->modifyAttributes ( $this->demande->getUserDn (), $desc )) {
					$this->user = $ldap->getEntry ( $this->demande->getUserDn () );
					$_SESSION ['loggedUser'] = serialize ( $this->user );
				}
			}
			if ($ldap->addAttributes ( $this->user->getUserDn (), $entry )) {
				$this->mailAdmin ( "NEW " . MainProject . " USER", "Un nouvel utilisateur " . MainProject . " s'est enregistré.", null, $this->demande );
				$this->user->attrs [strtolower ( MainProject )] = $entry;
				$_SESSION ['loggedUser'] = serialize ( $this->user );
				return true;
			} else {
				$this->mailAdmin ( "ERROR NEW " . MainProject . " USER", "Echec de l'enregistrement de l'utilisateur suivant :", null, $this->demande );
				return false;
			}
		} catch ( Exception $e ) {
			echo "<font size=\"3\" color='red'><b>The user directory is temporarily unavailable. Please contact the administrator.</b></font><br>";
			$this->mailAdmin ( 'ERREUR', "Erreur lors de l'enregistrement d'1 utilisateur.", $e, $this->demande [$proj] );
			return false;
		}
	}
	private function addPortalUser($isPortalForm = false) {
		$ldap = new ldapConnect ();
		$ldap->openAdm ();
		$this->user = unserialize ( $_SESSION ['loggedUser'] );
		try {
			$entry = $this->demande->getUserEntry ();
			$passwd = $this->genPassword ( time (), 6 );
			$hashMd5 = md5 ( $passwd );
			$entry ["objectClass"] [] = REGISTERED_USER_CLASS;
			$entry ["userPassword"] = ldap_md5 ( $passwd );
			$entry ["homeDirectory"] = PORTAL_DEPOT;
			$entry ["RegistrationDate"] = date ( FORMAT_DATE );
			// new
			if ($isPortalForm == false) {
				$entry ["objectClass"] [] = strtolower ( MainProject ) . 'User';
				$entry [strtolower ( MainProject ) . 'ApplicationDate'] = date ( FORMAT_DATE );
				$entry [strtolower ( MainProject ) . 'Status'] = STATUS_PENDING;
			}
			if ($ldap->addEntry ( $this->demande->getUserDn (), $entry )) {
				$this->mailAdmin ( "NEW " . MainProject . " USER", "Un nouvel utilisateur " . MainProject . " s'est enregistré.", null, $this->demande );
				$this->sendMailRegistration ( $entry ['mail'], $passwd, ucfirst ( strtolower (MainProject) ) );
				return true;
			} else {
				$this->mailAdmin ( "ERROR NEW " . MainProject . " USER", "Echec de l'enregistrement de l'utilisateur suivant :", null, $this->demande );
				return false;
			}
		} catch ( Exception $e ) {
			echo "<font size=\"3\" color='red'><b>The user directory is temporarily unavailable. Please contact the administrator.</b></font><br>";
			$this->mailAdmin ( 'ERREUR', "Erreur lors de l'enregistrement d'1 utilisateur.", $e, $this->demande [$proj] );
			return false;
		}
	}
	function getUserFromLdap() {
		$ldap = new ldapConnect ();
		$ldap->openAdm ();
		$this->saveForm ();
		try {
			$this->user = unserialize ( $_SESSION ['loggedUser'] );
			if (isset ( $this->user ) && ! empty ( $this->user )) {
				$this->user = $ldap->getEntry ( $this->user->getUserDn () );
				$_SESSION ['loggedUser'] = serialize ( $this->user );
			}
			return true;
		} catch ( Exception $e ) {
			echo "<font size=\"3\" color='red'><b>The user directory is temporarily unavailable. Please contact the administrator.</b></font><br>";
			return false;
		}
	}
	/*
	 * Ajoute une demande d'accès à un user existant.
	 */
	function updateUser() {
		global $MainProjects;
		$ldap = new ldapConnect ();
		$ldap->openAdm ();
		$this->saveForm ();
		if (isset ( $this->demande->mail ) && ! empty ( $this->demande->mail ))
			$entr = $ldap->getEntry ( 'mail=' . $this->demande->mail . ',' . PEOPLE_BASE, $retClass = strtolower ( MainProject ) . 'User' );
		elseif (isset ( $this->user->mail ) && ! empty ( $this->user->mail )) {
			$entr = $ldap->getEntry ( 'mail=' . $this->user->mail . ',' . PEOPLE_BASE, $retClass = strtolower ( MainProject ) . 'User' );
		}
		$entr = $entr->getUserEntry ();
		try {
			$entry = $this->demande->getUserEntry ();
			if (isset ( $this->demande->supervisor_name ) && ! empty ( $this->demande->supervisor_name ) && ! empty ( $this->demande->supervisor_affiliation ) && isset ( $this->demande->supervisor_affiliation )) {
				if (! array_key_exists ( 'studentSupervisorName', $entr ) && ! array_key_exists ( 'studentSupervisorAffiliation', $entr )) {
					$entree = array (
							'objectClass' => STUDENT_CLASS,
							'studentSupervisorName' => $this->demande->supervisor_name,
							'studentSupervisorAffiliation' => $this->demande->supervisor_affiliation 
					);
					$ldap->addAttributes ( $this->demande->getUserDn (), $entree );
				}
			} else {
				$attrs = array (
						'objectClass' => STUDENT_CLASS,
						'studentSupervisorName' => $this->user->supervisor_name,
						'studentSupervisorAffiliation' => $this->user->supervisor_affiliation 
				);
				$ldap->deleteAttributes ( $this->demande->getUserDn (), $attrs );
				unset ( $entry ['studentSupervisorName'] );
				unset ( $entry ['studentSupervisorAffiliation'] );
			}
			$this->user = $ldap->getEntry ( $this->user->getUserDn () );
			foreach ($MainProjects as $proj){
				if (isset ( $this->user->attrs [strtolower($proj).'Abstract'] ) || isset ( $this->user->attrs [strtolower($proj)] [strtolower($proj).'Abstract'] )) {
					$entry [strtolower($proj).'Abstract'] = $this->exportValue ( $proj.'_abstract' );
					$ent = array ();
					$ent [strtolower($proj).'Abstract'] = $this->user->attrs [strtolower($proj).'Abstract'] [0];
				}
				$projectAbstract = $this->exportValue ( $proj.'_abstract' );
				if ($projectAbstract == null) {
					$ldap->deleteAttributes ( $this->demande->getUserDn (), $ent );
					unset ( $entry [strtolower($proj).'Abstract'] );
				} else {
					$entree = array (
							strtolower($proj).'Abstract' => $projectAbstract
					);
					$ldap->addAttributes ( $this->demande->getUserDn (), $entree );
				}
			}
			if ($ldap->modifyAttributes ( $this->demande->getUserDn (), $entry )) {
				$this->mailAdmin ( "UPDATE " . MainProject . " USER", "Un utilisateur " . MainProject . " a effectué une mise à jour de son profil.", null, $this->demande );
				$this->user = $ldap->getEntry ( $this->demande->getUserDn () );
				$_SESSION ['loggedUser'] = serialize ( $this->user );
				return true;
			} else {
				$this->mailAdmin ( "ERROR NEW " . MainProject . " USER", "Echec de l'enregistrement de l'utilisateur suivant :", null, $this->demande );
				return false;
			}
		} catch ( Exception $e ) {
			echo "<font size=\"3\" color='red'><b>The user directory is temporarily unavailable. Please contact the administrator.</b></font><br>";
			$this->mailAdmin ( 'ERREUR', "Erreur lors de la mise d'1 utilisateur.", $e, $this->demande );
			return false;
		}
	}
	private function updateProjectUser($ldap, $proj) {
		$entree = $this->demande->otherUser [$proj]->getProjectUserEntry ();
		array_shift ( $entree ["objectClass"] ); // Supprime 'user'
		foreach ( $this->exclAttrs as $exclAttr ) {
			unset ( $entree [$exclAttr] );
		}
		if (in_array ( 'studentSupervisorName', $this->exclAttrs ) || in_array ( 'studentSupervisorAffiliation', $this->exclAttrs )) {
			unset ( $entree ["objectClass"] );
			$entree ["objectClass"] = strtolower($proj).'User';
		}	
	}
	
	/*
	 * Teste si toutes les checkboxes du groupe sont cochées.
	 */
	function validateCbs($groupName) {
		foreach ( $this->getElement ( $groupName )->getElements () as $box ) {
			$value = $box->getValue ();
			if (empty ( $value ))
				return false;
		}
		return true;
	}
	function validateCb($CBname) {
		$value = $this->getElement ( $CBname )->getValue ();
		if (empty ( $value ))
			return false;
		else
			return true;
	}
	function validateChart($forPortal = false) {
		if ($forPortal == false) {
			$bool = 0;
			foreach ( $this->project as $proj ) {
				if ($this->validateCbs ( $proj . '_chkChart' )) {
					$bool ++;
				} else {
					$this->_errors [] = "Please read and sign $proj data and publication policy";
				}
			}
			if ($bool == count ( $this->project ))
				return true;
			else
				return false;
		} else if ($forPortal == true) {
			if ($this->validateCbs ( 'chkChart' )) {
				return true;
			} else {
				$this->_errors [] = "Please read and sign " . MainProject . " data and publication policy";
				return false;
			}
		}
	}
	function check() {
		$chk = $this->checkMail ();
		$this->getElement ( 'check_result' )->setValue ( $chk );
		if (isset ( $chk ) && $chk != 'new')
			$this->initUser ();
	}
	private function checkMail() {
		$mail = $this->exportValue ( 'mail' );
		try {
			$ldap = new ldapConnect ();
			$ldap->openAdm ();
			$user = $ldap->getEntry ( $ldap->getUserDn ( $mail ) );
			if ($user) {
				$this->getElement ( 'user' )->setValue ( serialize ( $user ) );
				if ($user->userPassword) {
					// Déjà un mdp => demander de se logguer
					$roles = '';
					foreach ( $user->memberOf as $role )
						$roles .= ", $role";
					
					if (! empty ( $roles )) {
						$roles = ' (' . substr ( $roles, 2 ) . ')';
					}
					if (isset ( $_SESSION ['loggedUser'] )) {
						$this->msg = "<br>You are logged now, if you want to register with a new account please logout first.";
					} else {
						$this->msg = "<br> You're already registered$roles. please use your IDs to login.";
					}
					
					return 'log';
				} else {
					// Demande en attente pour un autre projet
					$this->msg = "Please sign the data policy.";
					$this->initForm ( $user );
					return 'sign';
				}
			} else {
				// Nouvelle demande
				$this->getElement ( 'mail' )->setValue ( $mail );
				return 'new';
			}
			$ldap->close ();
		} catch ( Exception $e ) {
			$this->mailAdmin ( 'ERREUR', "Erreur lors de la vérification d'une demande d'enregistrement.", $e );
			$this->_errors [] = "An error occurred. Please contact the website administrator.";
			echo "Erreur: $e\n";
		}
	}
	function disableElement($elementName) {
		$this->getElement ( $elementName )->setAttribute ( 'onfocus', 'blur()' );
		$this->getElement ( $elementName )->setAttribute ( 'style', 'background-color: transparent;' );
		$this->disabledElmts [] = $elementName;
	}
	var $disabledElmts = array ();
	var $exclAttrs = array ();
	function initUser($update = false) {
		$this->getUserFromLdap ();
		if (isset ( $this->user ) && ! empty ( $this->user ))
			$this->initForm ( $this->user, $update );
	}
	function updateUserProfile() {
		global $MainProjects;
		$this->getUserFromLdap ();
		$this->user->lastname = $this->exportValue ( 'lastname' );
		$this->user->mail = $this->exportValue ( 'mail' );
		$this->user->affiliation = $this->exportValue ( 'affiliation' );
		$this->user->cn = $this->exportValue ( 'lastname' ) . ' ' . $this->exportValue ( 'firstname' );
		$this->user->country = $this->exportValue ( 'country' );
		$this->user->city = $this->exportValue ( 'city' );
		$this->user->zipCode = $this->exportValue ( 'zip' );
		$this->user->street = $this->exportValue ( 'street' );
		$this->user->supervisor_name = $this->exportValue ( 'supervisor_name' );
		$this->user->supervisor_affiliation = $this->exportValue ( 'supervisor_affiliation' );
		$this->user->phoneNumber = $this->exportValue ( 'phone' );
		$this->user->abstract = $this->exportValue ( 'abstract' );
		foreach($MainProjects as $proj){
			$this->user->attrs [strtolower($proj).'Abstract'] = $this->exportValue ( $proj.'_abstract' );
		}
		$_SESSION ['loggedUser'] = serialize ( $this->user );
	}
	
	// Nv
	function initForm($user, $update) {
		global $MainProjects;
		$this->getElement ( 'mail' )->setValue ( $user->mail );
		$this->exclAttrs [] = 'altMail';
		$this->exclAttrs [] = 'mail';
		$this->getElement ( 'lastname' )->setValue ( strtoupper ( $user->lastname ) );
		$this->getElement ( 'firstname' )->setValue ( trim ( ucfirst ( str_replace ( strtolower ( $user->lastname ), '', strtolower ( $user->cn ) ) ) ) );
		$this->exclAttrs [] = 'cn';
		$this->exclAttrs [] = 'sn';
		if ($user->affiliation) {
			$this->getElement ( 'affiliation' )->setValue ( $user->affiliation );
			$this->exclAttrs [] = 'o';
		}
		if ($user->country) {
			$this->getElement ( 'country' )->setValue ( $user->country );
			$this->exclAttrs [] = 'c';
		}
		
		if ($user->city) {
			$this->getElement ( 'city' )->setValue ( $user->city );
			$this->exclAttrs [] = 'l';
		}
		if ($user->zipCode) {
			$this->getElement ( 'zip' )->setValue ( $user->zipCode );
			$this->exclAttrs [] = 'postalCode';
		}
		if ($user->street) {
			$this->getElement ( 'street' )->setValue ( $user->street );
			$this->exclAttrs [] = 'street';
		}
		if ($user->abstract) {
			$this->getElement ( 'abstract' )->setValue ( $user->abstract );
			$this->exclAttrs [] = 'description';
		}
		if ($user->supervisor_name) {
			$this->getElement ( 'supervisor_name' )->setValue ( $user->supervisor_name );
			$this->exclAttrs [] = 'studentSupervisorName';
		}
		if ($user->supervisor_affiliation) {
			$this->getElement ( 'supervisor_affiliation' )->setValue ( $user->supervisor_affiliation );
			$this->exclAttrs [] = 'studentSupervisorAffiliation';
		}
		if ($user->phoneNumber) {
			$this->getElement ( 'phone' )->setValue ( $user->phoneNumber );
			$this->exclAttrs [] = 'telephoneNumber';
		}
		if ($update == true) {
			foreach($MainProjects as $proj){
				if (isset ( $this->user->attrs [strtolower($proj).'Abstract'] ) || isset ( $this->user->attrs [strtolower($proj)] [strtolower($proj).'Abstract'] )) {
					if (isset ( $this->user->attrs [strtolower($proj).'Abstract'] )) {
						if (is_array ( $this->user->attrs [strtolower($proj).'Abstract'] ))
							$this->getElement ( $proj.'_abstract' )->setValue ( $this->user->attrs [strtolower($proj).'Abstract'] [0] );
						else
							$this->getElement ( $proj.'_abstract' )->setValue ( $this->user->attrs [strtolower($proj).'Abstract'] );
					} elseif ($this->user->attrs [strtolower($proj)] [strtolower($proj).'Abstract']) {
						$this->getElement ( $proj.'_abstract' )->setValue ( $this->user->attrs [strtolower($proj)] [strtolower($proj).'Abstract'] );
					}
					$this->exclAttrs [] = strtolower($proj).'Abstract';
				}
			}	
		}
	}
}

?>
