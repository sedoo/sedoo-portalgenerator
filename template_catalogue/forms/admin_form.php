<?php
require_once ("/sites/kernel/#MainProject/conf.php");
require_once ("forms/login_form.php");
require_once ("countries.php");
require_once ("ldap/constants.php");
require_once ("ldap/ldapUtils.php");
require_once ("logger.php");
define ( 'USERS_PER_PAGE', 20 );

class admin_form extends login_form {
	var $pendingRequests;
	var $rejectedRequests;
	var $registeredUsers;
	var $registeredUsersByProject;
	var $participants;
	function createForm() {
		global $project_name, $MainProjects;
		if (isset ( $_SESSION ['loggedUser'] )) {
			$this->user = unserialize ( $_SESSION ['loggedUser'] );
		}
		if ($this->isProjectAdmin ()) {
			$this->initGroups ();
			if (! isset ( $_SESSION [strtolower ( $project_name ) . 'pendingRequests'] ) && empty ( $_SESSION [strtolower ( $project_name ) . 'pendingRequests'] )) {
				$this->readPendingRequestsList ();
				$this->createPendingRequestsForm ();
			} else {
				$this->pendingRequests = unserialize ( $_SESSION [strtolower ( $project_name ) . 'pendingRequests'] );
				$this->createPendingRequestsForm ();
			}
			if (! isset ( $_SESSION [strtolower ( $project_name ) . 'rejectedRequests'] ) && empty ( $_SESSION [strtolower ( $project_name ) . 'rejectedRequests'] )) {
				$this->readRejectedRequestsList ();
				$this->createRejectedRequestsForm ();
			} else {
				$this->rejectedRequests = unserialize ( $_SESSION [strtolower ( $project_name ) . 'rejectedRequests'] );
				$this->createRejectedRequestsForm ();
			}
			if (! isset ( $_SESSION [strtolower ( $project_name ) . 'registeredUsers'] ) && empty ( $_SESSION [strtolower ( $project_name ) . 'registeredUsers'] )) {
				$this->readRegisteredUsersList ();
				$this->createRegisteredUsersForm ();
			} else {
				$this->registeredUsers = unserialize ( $_SESSION [strtolower ( $project_name ) . 'registeredUsers'] );
				$this->createRegisteredUsersForm ();
			}
			if ($project_name == MainProject) {
				foreach ( $MainProjects as $pro ) {
					if (! isset ( $_SESSION [strtolower($pro) . 'registeredUsers'] ) && empty ( $_SESSION [strtolower($pro) . 'registeredUsersByProject'] )) {
						$this->readRegisteredUsersListByProject ( $pro );
						$this->createRegisteredUsersForm ();
					} else {
						if(isset($_SESSION [strtolower($pro) . 'registeredUsersByProject']) && !empty($_SESSION [strtolower($pro) . 'registeredUsersByProject']))
							$this->registeredUsersByProject [$pro] = unserialize ( $_SESSION [strtolower($pro) . 'registeredUsersByProject'] );
						$this->createRegisteredUsersForm ();
					}
				}
			}
		} else {
			$this->createLoginForm ( 'Mail' );
		}
	}
	
	/* *** Actions **** */
	function registerUser($i) {
		global $project_name;
		$ldapConn = new ldapConnect ();
		$user = $this->pendingRequests [$i];
		$group = $this->exportValue ( 'group_' . $i );
		if ($group) {
			if ($group == - 1) {
				$this->_errors [] = "Please select a group in the list";
				echo "<font size=\"3\" color='red'><b>Please select a group in the list.</b></font><br>";
				return true;
			}
			$nvAttrs ["memberOf"] = $group;
			if (isset ( $user->userPassword ) && ! empty ( $user->userPassword )) {
				$passwd = null;
			} else {
				$passwd = $this->genPassword ( time (), 6 );
				$hashMd5 = md5 ( $passwd );
				$nvAttrs ["objectClass"] = REGISTERED_USER_CLASS;
				$nvAttrs ["userPassword"] = ldap_md5 ( $passwd );
				$nvAttrs ["homeDirectory"] = strtoupper ( $project_name ) . _DEPOT;
			}
			$nvAttrs [$project_name . "RegistrationDate"] = date ( FORMAT_DATE );
			try {
				$ldapConn->openAdm ();
				if ($project_name != MainProject) {
					if ((! isset ( $user->memberOf ) && $group == strtolower ( $project_name ) . 'Core') || (isset ( $user->memberOf ) && in_array ( strtolower ( $project_name ), $user->memberOf ) == false && $group == strtolower ( $project_name ) . 'Core')) {
						if (array_key_exists ( strtolower ( MainProject ) . 'ApplicationDate', $user->attrs )) {
							$Attrs ["objectClass"] [] = strtolower ( MainProject ) . 'User';
							if(isset($user->applicationDate) && !empty($user->applicationDate))
								$Attrs [strtolower ( MainProject ) . 'ApplicationDate'] = $user->applicationDate;
							else 
								$Attrs [strtolower ( MainProject ) . 'ApplicationDate'] = $user->attrs[strtolower ( $project_name ) . 'ApplicationDate'];
							$Attrs [strtolower ( MainProject ) . 'Status'] = STATUS_ACCEPTED;
							$ldapConn->modifyAttributes ( $user->dn, $Attrs );
							$nvAttrs [strtolower ( MainProject ) . 'RegistrationDate'] = date ( FORMAT_DATE );
							array_push($nvAttrs ['memberOf'], strtolower ( MainProject ));
						} else {
							$nvAttrs ["objectClass"] [] = strtolower ( MainProject ) . 'User';
							if(isset($user->applicationDate) && !empty($user->applicationDate))
								$nvAttrs [strtolower ( MainProject ) . 'ApplicationDate'] = $user->applicationDate;
							else 
								$Attrs [strtolower ( MainProject ) . 'ApplicationDate'] = $user->attrs[strtolower ( $project_name ) . 'ApplicationDate'];
							$nvAttrs [strtolower ( MainProject ) . 'Status'] = STATUS_ACCEPTED;
							$nvAttrs [strtolower ( MainProject ) . 'RegistrationDate'] = date ( FORMAT_DATE );
							array_push($nvAttrs ['memberOf'], strtolower ( MainProject ));
						}
					}
				}
				if ($ldapConn->addAttributes ( $user->dn, $nvAttrs )) {
					$ldapConn->modifyAttribute ( $user->dn, strtolower ( $project_name ) . "Status", STATUS_ACCEPTED );
					$this->sendMailRegistration ( $user->mail, $passwd, $project_name );
					if ($passwd) {
						// Envoi d'un mail à Laurent Labatut
						$infos = "$user->mail\nName:$user->cn\nAffiliation:$user->affiliation\nCountry:" . countries::getDisplayName ( $user->country ) . "\nmd5: $hashMd5 \nmd5 (ldap): " . $nvAttrs ["userPassword"] . "\nmdp: $passwd";
						sendMailSimple ( 'guillaume.brissebrat@obs-mip.fr', '[' . $project_name . '] New Database User', $infos, ROOT_EMAIL );
					}
					
					// Ajout memberUid aux groupes
					$attrs ['memberUid'] = $user->mail;
					$ldapConn->addAttributes ( 'groupId=' . $group . ',' . GROUP_BASE, $attrs );
					if ($project_name != MainProject) {
						if ($group == strtolower ( $project_name ) . 'Core') {
							$ldapConn->addAttributes ( 'groupId=' . strtolower ( $project_name ) . 'Asso,' . GROUP_BASE, $attrs );
						}
						$this->readPendingRequestsList ();
						$this->readRegisteredUsersList ();
						echo "<font size=\"3\" color='green'><b>The user has been registered successfully.</b></font><br>";
						return true;
					} else {
						log_error ( 'Echec modif' );
						return false;
					}
					$ldapConn->close ();
				}
			} catch ( Exception $e ) {
				$this->mailAdmin ( 'ERREUR', "Erreur lors de l'enregistrement d'un utilisateur.", $e, $user );
				return false;
			}
		} else {
			return $this->rejectUser ( $i );
		}
	}
	function sendMailRejet($userEmail, $adminEmail) {
		global $project_name;
		// Envoi du mail
		$texte = "Dear Colleague,\n\n" . "We have received your request for an access to the $project_name database. Considering the details you provided, your request has been rejected.\n\n" . "If you think that your request should have been agreed or if you would like to collaborate with some $project_name scientists, please contact $adminEmail.\n\n" . "Best regards\nThe ".MainProject." database team";
		sendMailSimple ( $userEmail, "[$project_name-DATABASE] Registration: Rejected Request", $texte, ROOT_EMAIL );
		sendMailSimple ( ROOT_EMAIL, "[$project_name-DATABASE] Rejected User", "User $userEmail has been rejected", ROOT_EMAIL );
	}

	function  getProjectAdminEmail ( $project_name ){
		//TODO
		return ROOT_EMAIL;
	}

	function rejectUser($i) {
		global $project_name;
		$ldapConn = new ldapConnect ();
		$user = $this->pendingRequests [$i];
		try {
			$ldapConn->openAdm ();
			if ($ldapConn->modifyAttribute ( $user->dn, strtolower ( $project_name ) . "Status", STATUS_REJECTED )) {
				$this->sendMailRejet ( $user->mail, $this->getProjectAdminEmail ( $project_name ) );
				$this->readPendingRequestsList ();
				$this->readRejectedRequestsList ();
				echo "<font size=\"3\" color='green'><b>The request has been rejected successfully.</b></font><br>";
				return true;
			} else {
				return false;
			}
			$ldapConn->close ();
		} catch ( Exception $e ) {
			$this->mailAdmin ( 'ERREUR', "Erreur lors du rejet d'une demande.", $e, $user );
			return false;
		}
	}
	function restoreUser($i) {
		global $project_name;
		$ldapConn = new ldapConnect ();
		$user = $this->rejectedRequests [$i];
		try {
			$ldapConn->openAdm ();
			if ($ldapConn->modifyAttribute ( $user->dn, strtolower ( $project_name ) . "Status", STATUS_PENDING )) {
				$this->readPendingRequestsList ();
				$this->readRejectedRequestsList ();
				echo "<font size=\"3\" color='green'><b>The request has been restored successfully.</b></font><br>";
				return true;
			} else {
				return false;
			}
			$ldapConn->close ();
		} catch ( Exception $e ) {
			$this->mailAdmin ( 'ERREUR', "Erreur lors de la restauration d'une demande.", $e, $user );
			return false;
		}
	}
	function updateUser($i) {
		global $project_name;
		$ldapConn = new ldapConnect ();
		$user = $this->registeredUsers [$i];
		$nvRole = $this->exportValue ( "editable_group_$i" );
		if ($nvRole) {
			if ($nvRole != $user->editableGroup->id) {
				try {
					$ldapConn->openAdm ();
					if ($ldapConn->addAttribute ( $user->dn, "memberOf", $nvRole ) && $ldapConn->deleteAttribute ( $user->dn, "memberOf", $user->editableGroup->id )) {
						$attrsGroup ['memberUid'] = $user->mail;
						$ldapConn->deleteAttributes ( 'groupId=' . $user->editableGroup->id . ',' . GROUP_BASE, $attrsGroup );
						if ($user->editableGroup->id == strtolower ( $project_name ) . 'Core') {
							$ldapConn->deleteAttributes ( 'groupId=' . strtolower ( $project_name ) . 'Asso,' . GROUP_BASE, $attrsGroup );
						}
						$ldapConn->addAttributes ( 'groupId=' . $nvRole . ',' . GROUP_BASE, $attrsGroup );
						if ($nvRole == strtolower ( $project_name ) . 'Core') {
							$ldapConn->addAttributes ( 'groupId=' . strtolower ( $project_name ) . 'Asso,' . GROUP_BASE, $attrsGroup );
						}
						if ((! isset ( $user->memberOf ) && $nvRole == strtolower ( $project_name ) . 'Core') || (isset ( $user->memberOf ) && in_array ( strtolower ( MainProject ), $user->memberOf ) == false && $nvRole == strtolower ( $project_name ) . 'Core')) {
							if (array_key_exists ( MainProject . 'ApplicationDate', $user->attrs )) {
								$Attrs ["objectClass"] [] = strtolower ( MainProject ) . 'User';
								$Attrs [MainProject . 'ApplicationDate'] = $user->applicationDate;
								$Attrs [MainProject . 'Status'] = STATUS_ACCEPTED;
								$ldapConn->modifyAttributes ( $user->dn, $Attrs );
								$nvAttrs = array ();
								$nvAttrs [MainProject . 'RegistrationDate'] = date ( FORMAT_DATE );
								$nvAttrs ['memberOf'] [] = strtolower ( MainProject );
							} else {
								$nvAttrs = array ();
								$nvAttrs ["objectClass"] [] = strtolower ( MainProject ) . 'User';
								$nvAttrs [MainProject . 'ApplicationDate'] = $user->applicationDate;
								$nvAttrs [MainProject . 'Status'] = STATUS_ACCEPTED;
								$nvAttrs [MainProject . 'RegistrationDate'] = date ( FORMAT_DATE );
								$nvAttrs ['memberOf'] [] = strtolower ( MainProject );
							}
							$ldapConn->addAttributes ( $user->dn, $nvAttrs );
						}
						echo "<font size=\"3\" color='green'><b>The user has been updated successfully.</b></font><br>";
						$this->readRegisteredUsersList ();
						return true;
					} else {
						return false;
					}
					$ldapConn->close ();
				} catch ( Exception $e ) {
					$this->mailAdmin ( 'ERREUR', "Erreur lors de la modification d'un user enregistré.", $e, $user );
					return false;
				}
			} else {
				echo "<font size=\"3\" color='red'><b>No modifications to save.</b></font><br>";
				return true;
			}
		} else {
			return false;
		}
	}
	function unregisterUser($i) {
		global $project_name;
		$ldapConn = new ldapConnect ();
		$user = $this->registeredUsers [$i];
		
		try {
			$ldapConn->openAdm ();
			foreach ( $this->groupList as $group ) {
				if (in_array ( $group->id, $user->memberOf ) && ! $group->isAdmin && strnatcasecmp ( $project_name, $group->project ) == 0) {
					$attrs ["memberOf"] = $group->id;
					// Ajout memberUid aux groupes
					$attrsGroup ['memberUid'] = $user->mail;
					$ldapConn->deleteAttributes ( 'groupId=' . $group->id . ',' . GROUP_BASE, $attrsGroup );
					if ($group->id == strtolower ( $project_name ) . 'Core') {
						$ldapConn->deleteAttributes ( 'groupId=' . strtolower ( $project_name ) . 'Asso,' . GROUP_BASE, $attrsGroup );
					}
					break;
				}
			}
			if (isset ( $user->registrationDate ) && ! empty ( $user->registrationDate )) {
				$attrs [strtolower ( $project_name ) . "RegistrationDate"] = $user->registrationDate;
			}
			if ($ldapConn->deleteAttributes ( $user->dn, $attrs )) {
				$ldapConn->modifyAttribute ( $user->dn, strtolower ( $project_name ) . "Status", STATUS_REJECTED );
				$this->readRegisteredUsersList ();
				$this->readPendingRequestsList ();
				$this->readRejectedRequestsList ();
				echo "<font size=\"3\" color='green'><b>The user has been deleted successfully.</b></font><br>";
				return true;
			}
			$ldapConn->close ();
		} catch ( Exception $e ) {
			$this->mailAdmin ( 'ERREUR', "Erreur lors de la suppression d'un user enregistré.", $e, $user );
			return false;
		}
	}
	function deleteUser($i) {
		$ldapConn = new ldapConnect ();
		$user = $this->rejectedRequests [$i];
		try {
			$ldapConn->openAdm ();
			if ($ldapConn->deleteEntry ( $user->dn )) {
				$this->readPendingRequestsList ();
				$this->readRejectedRequestsList ();
				echo "<font size=\"3\" color='green'><b>The request has been deleted successfully.</b></font><br>";
				return true;
			} else {
				return false;
			}
			$ldapConn->close ();
		} catch ( Exception $e ) {
			$this->mailAdmin ( 'ERREUR', "Erreur lors de la suppression d'une demande.", $e, $user );
			return false;
		}
	}
	
	/* *** Création des éléments de formulaire **** */
	function createPendingRequestsForm() {
		for($i = 1; $i <= count ( $this->pendingRequests ); $i ++) {
			$this->createPendingRequestForm ( $i );
		}
	}
	function createRejectedRequestsForm() {
		for($i = 1; $i <= count ( $this->rejectedRequests ); $i ++) {
			$this->createRejectedRequestForm ( $i );
		}
	}
	function createRegisteredUsersForm() {
		for($i = 1; $i <= count ( $this->registeredUsers ); $i ++) {
			$this->createRegisteredUserForm ( $i );
		}
	}
	function createPendingRequestForm($i) {
		global $project_name;
		$groupes = $this->getUsersGroup ( $project_name, true );
		$group_select = & $this->createElement ( 'select', 'group_' . $i, 'group_' . $i, $groupes ); // ,array('onchange' => $onchange));
		$this->addElement ( $group_select );
		$this->addElement ( 'submit', 'bouton_register_' . $i, 'Register' );
		$this->addElement ( 'submit', 'bouton_reject_' . $i, 'Reject' );
	}
	function createRejectedRequestForm($i) {
		$this->addElement ( 'submit', 'bouton_restore_' . $i, 'Restore' );
		$this->addElement ( 'submit', 'bouton_delete_' . $i, 'Delete' );
	}
	function getUsersGroup($project, $noDefault = false) {
		if ($noDefault)
			$groupes [- 1] = "-- Choose a status --";
		foreach ( $this->groupList as $group ) {
			if (! $group->isAdmin && strnatcasecmp ( $project, $group->project ) == 0)
				$groupes [$group->id] = $group->cn;
		}
		return $groupes;
	}
	function createRegisteredUserForm($i) {
		global $project_name;
		$user = $this->registeredUsers [$i];
		$groupes = $this->getUsersGroup ( $project_name );
		$group_select = & $this->createElement ( 'select', 'editable_group_' . $i, '', $groupes );
		$this->addElement ( $group_select );
		$this->addElement ( 'text', 'search', '', array (
				'size' => 10 
		) );
		$this->applyFilter ( 'search', 'trim' );
		$this->addElement ( 'submit', 'bouton_update_' . $i, 'Update' );
		$this->addElement ( 'submit', 'bouton_unregister_' . $i, 'Unregister' );
		$this->addElement ( 'submit', 'bouton_search', 'Search' );
	}
	
	/* *** Affichage des listes **** */
	function displayRejectedUser($i, $user) {
		$this->displayUser ( $i, $user, false, true );
	}
	function displayPendingRequest($i, $user) {
		$this->displayUser ( $i, $user );
	}
	function displayRegisteredUser($i, $user, $project=null) {
		$this->displayUser ( $i, $user, true, false, $project );
	}
	function displayDate($dateStr=null) {
		try {
			list ( $year, $month, $day ) = sscanf ( $dateStr, "%4u%2u%2u" );
			$datetime = new DateTime ( "$year-$month-$day", new DateTimeZone ( 'Europe/Paris' ) );
			echo date_format ( $datetime, 'M j Y' );
		} catch ( Exception $e ) {
			echo 'NA';
		}
	}
	function searchUser() {
		$cpt = 1;
		$str = ucfirst ( $_POST ['search'] );
		
		if (isset ( $this->registeredUsers )) {
			foreach ( $this->registeredUsers as $user ) {
				if (strnatcasecmp ( $user->lastname, $str ) >= 0) {
					return $cpt;
				}
				$cpt ++;
			}
		}
		return 1;
	}

	function displayUsersListHeader($first = 1, $nb = USERS_PER_PAGE, $registered = false, $rejected = false, $project = null) {
		if ($project == null) {
			if ($registered) {
				echo '<tr><th colspan="2"><a href="/utils/exportUsersXls.php" ><img src="/img/text.png" style="border:0px;" />Download</a>';
				echo '</th></tr>';
				echo '<tr><th colspan="2" align="center">';
				if ($first == 1) {
					echo '&lt;&lt;&nbsp;&nbsp;&lt;&nbsp;&nbsp;';
				} else {
					echo '<a href="' . $this->getReqUri () . '">&lt;&lt;</a>&nbsp;&nbsp;<a href="' . $this->getReqUri () . '&first=' . max ( $first - $nb, 1 ) . '">&lt;</a>&nbsp;&nbsp;';
				}
				echo $first . ' - ' . (min ( count ( $this->registeredUsers ), $first + $nb - 1 )) . ' / ' . count ( $this->registeredUsers ) . ' users';
				echo '&nbsp;&nbsp;';
				if ($first + $nb > count ( $this->registeredUsers )) {
					// Déjà sur la dernière page
					echo '&gt;';
					;
					echo '&nbsp;&nbsp;';
					echo '&gt;&gt;';
				} else {
					echo '<a href="' . $this->getReqUri () . '&first=' . ($first + $nb) . '">&gt;</a>';
					echo '&nbsp;&nbsp;';
					echo '<a href="' . $this->getReqUri () . '&first=' . (count ( $this->registeredUsers ) - $nb + 1) . '">&gt;&gt;</a>';
				}
				echo '<br><form name="frmsearchuser" id="frmsearchuser" method="post" action="' . $this->getReqUri () . '" >';
				echo $this->getElement ( 'search' )->toHTML () . '&nbsp;' . $this->getElement ( 'bouton_search' )->toHTML ();
				echo '</form></th></tr>';
			} else if ($rejected) {
				echo '<tr><th colspan="2">' . count ( $this->rejectedRequests ) . ' rejected requests</th></tr>';
			} else {
				echo '<tr><th colspan="2">' . count ( $this->pendingRequests ) . ' pending requests</th></tr>';
			}
		} else {
			if ($registered) {
				echo '<tr><th colspan="2"><a href="/utils/exportUsersXls.php" ><img src="/img/text.png" style="border:0px;" />Download</a>';
				echo '</th></tr>';
				echo '<tr><th colspan="2" align="center">';
				if ($first == 1) {
					echo '&lt;&lt;&nbsp;&nbsp;&lt;&nbsp;&nbsp;';
				} else {
					echo '<a href="' . $this->getReqUri () . '">&lt;&lt;</a>&nbsp;&nbsp;<a href="' . $this->getReqUri () . '&first=' . max ( $first - $nb, 1 ) . '">&lt;</a>&nbsp;&nbsp;';
				}
				echo $first . ' - ' . (min ( count ( $this->registeredUsersByProject [$project] ), $first + $nb - 1 )) . ' / ' . count ( $this->registeredUsersByProject [$project] ) . ' users';
				echo '&nbsp;&nbsp;';
				if ($first + $nb > count ( $this->registeredUsersByProject [$project] )) {
					// Déjà sur la dernière page
					echo '&gt;';
					echo '&nbsp;&nbsp;';
					echo '&gt;&gt;';
				} else {
					echo '<a href="' . $this->getReqUri () . '&first=' . ($first + $nb) . '">&gt;</a>';
					echo '&nbsp;&nbsp;';
					echo '<a href="' . $this->getReqUri () . '&first=' . (count ( $this->registeredUsersByProject [$project] ) - $nb + 1) . '">&gt;&gt;</a>';
				}
				echo '<br><form name="frmsearchuser" id="frmsearchuser" method="post" action="' . $this->getReqUri () . '" >';
				echo $this->getElement ( 'search' )->toHTML () . '&nbsp;' . $this->getElement ( 'bouton_search' )->toHTML ();
				echo '</form></th></tr>';
			}
		}
	}
	function displayUser($i, $user, $registered = false, $rejected = false, $project = null) {
		global $MainProjects, $project_name;
		if (isset ( $user )) {
			echo '<tr><td align="left" colspan="2"><b>' . $user->cn . '</b><br/>';
			if ($registered) {
				echo 'Registration Date:&nbsp;';
				$this->displayDate ( $user->registrationDate );
			} else {
				echo 'Application Date:&nbsp;';
				$this->displayDate ( $user->applicationDate );
			}
			echo '</td></tr>';
			echo "<tr>";
			echo '<td valign="top">';
			echo "<b>Affiliation:</b>&nbsp;$user->affiliation<br>";
			echo "<b>Mail:</b>&nbsp;$user->mail<br>";
			echo "<b>Postal Address:</b>&nbsp;$user->street<br>";
			echo "<b>Zip Code:</b>&nbsp;$user->zipCode<br>";
			echo "<b>City:</b>&nbsp;$user->city<br>";
			echo "<b>Country:</b>&nbsp;" . countries::getDisplayName ( $user->country ) . "<br>";
			echo "<b>Telephone:</b>&nbsp;$user->phoneNumber<br>";
			echo '</td><td valign="top">';
			echo "<b>Abstract:</b><br>$user->abstract";
			foreach ( $MainProjects as $pro ) {
				if (isset ( $user->attrs [strtolower ( $pro ) . 'Abstract'] [0] ) && $user->attrs [strtolower ( $pro ) . 'Abstract'] [0] != $user->abstract) {
					echo "<br><b>" . strtolower ( $pro ) . " Abstract:</b><br>" . $user->attrs [strtolower ( $pro ) . 'Abstract'] [0];
				}
			}
			if (isset ( $user->supervisor_name ) && ! empty ( $user->supervisor_name )) {
				echo "<br><b>Supervisor:</b>&nbsp;$user->supervisor_name - $user->supervisor_affiliation";
			}
			if (isset ( $user->wg ) && ! empty ( $user->wg )) {
				echo "<br><b>WG:</b>";
				foreach ( $user->wg as $wg ) {
					echo "<br>-&nbsp;$wg";
				}
			}
			if (isset ( $user->associatedProject ) && ! empty ( $user->associatedProject )) {
				echo "<br><b>Associated Project:</b>&nbsp;$user->associatedProject";
			}
			if (! $registered) {
				if (isset ( $user->memberOf ) && ! empty ( $user->memberOf )) {
					echo '<br><b>Groups:</b>';
					foreach ( $user->memberOf as $group ) {
						echo "<br>- $group";
					}
				}
			}
			echo '</td>';
			echo '</tr>';
			if ($project == null) {
				echo "<tr>" . '<form method="post" action="' . $this->getReqUri () . '&update=' . $i . '" ><td colspan="2" align="center" style="background-color: white;">';
				if ($registered) {
					if (isset ( $user->editableGroup ) && ! empty ( $user->editableGroup )) {
						$this->getElement ( 'editable_group_' . $i )->setSelected ( $user->editableGroup->id );
						echo $this->getElement ( 'editable_group_' . $i )->toHTML ();
						echo $this->getElement ( 'bouton_update_' . $i )->toHTML ();
						echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
						echo $this->getElement ( 'bouton_unregister_' . $i )->toHTML ();
					}
					if (isset ( $user->otherGroups ) && ! empty ( $user->otherGroups )) {
						echo '<br>Other group(s):';
						foreach ( $user->otherGroups as $group ) {
							echo '&nbsp;' . $group->cn;
						}
					}
				} else if ($rejected) {
					echo $this->getElement ( 'bouton_restore_' . $i )->toHTML ();
				} else {
					echo $this->getElement ( 'group_' . $i )->toHTML ();
					echo $this->getElement ( 'bouton_register_' . $i )->toHTML ();
					echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
					echo $this->getElement ( 'bouton_reject_' . $i )->toHTML ();
				}
				echo "</td></form></tr><tr><th colspan='2'>&nbsp;</th></tr>";
			} else {
				echo "<tr><td colspan='2' align='center' style='background-color: white;'>";
				if (isset ( $user->editableGroup ) && ! empty ( $user->editableGroup )) {
					echo '<br>' . $project . ' group(s):' . $user->editableGroup->cn;
				}
				if (isset ( $user->otherGroups ) && ! empty ( $user->otherGroups )) {
					echo '<br>Other group(s):';
					foreach ( $user->otherGroups as $group ) {
						echo '&nbsp;' . $group->cn;
					}
				}
				echo "</td></tr><tr><th colspan='2'>&nbsp;</th></tr>";
			}
		}
	}
	private function getReqUri() {
		$reqUri = $_SERVER ['REQUEST_URI'];
		if (! strpos ( $reqUri, '?adm' )) {
			$reqUri .= '?adm';
		}
		if (strpos ( $reqUri, '&first' )) {
			$reqUri = substr ( $reqUri, 0, strpos ( $reqUri, '&first' ) );
		}
		if (strpos ( $reqUri, '&update' )) {
			return substr ( $reqUri, 0, strpos ( $reqUri, '&update' ) );
		} else {
			return $reqUri;
		}
	}
	function displayParticipantsList() {
		echo '<table>';
		if (isset ( $this->participants )) {
			echo '<tr><th colspan="2">' . count ( $this->participants ) . ' registered participants</th></tr>';
			$cpt = 0;
			foreach ( $this->participants as $participant ) {
				$cpt ++;
				if ($cpt % 2 == 1) {
					$ligne1 = '<tr>';
					$ligne2 = '<tr>';
				}
				$ligne1 .= '<th><b>' . $participant->cn . '</b></th>';
				$ligne2 .= '<td>';
				$ligne2 .= "<b>Mail:</b>&nbsp;$participant->mail<br>";
				$ligne2 .= "<b>Affiliation:</b>&nbsp;$participant->affiliation<br>";
				$ligne2 .= "<b>Country:</b>&nbsp;" . countries::getDisplayName ( $participant->country ) . "</td>";
				if ($cpt % 2 == 0) {
					$ligne1 .= '</tr>';
					$ligne2 .= '</tr>';
					echo $ligne1 . $ligne2;
				}
			}
		} else {
			echo '<tr><th>No user to display</th></tr>';
		}
		echo '</table>';
	}
	function displayPendingRequestsList() {
		echo '<table>';
		if (isset ( $this->pendingRequests )) {
			$this->displayUsersListHeader ();
			for($i = count ( $this->pendingRequests ); $i >= 1; $i --) {
				$this->displayPendingRequest ( $i, $this->pendingRequests [$i] );
			}
		} else {
			echo '<tr><th>No pending request</th></tr>';
		}
		echo '</table>';
	}
	function displayRejectedRequestsList() {
		echo '<table>';
		if (isset ( $this->rejectedRequests )) {
			$this->displayUsersListHeader ( 1, 0, false, true );
			for($i = 1; $i <= count ( $this->rejectedRequests ); $i ++) {
				$this->displayRejectedUser ( $i, $this->rejectedRequests [$i], true );
			}
		} else {
			echo '<tr><th>No rejected request</th></tr>';
		}
		echo '</table>';
	}
	function displayRegisteredUsersList($first = 1, $nb = USERS_PER_PAGE, $project = null) {
		if ($project == null) {
			echo '<table>';
			if (isset ( $this->registeredUsers )) {
				if ($nb == 0) {
					$nb = count ( $this->registeredUsers );
				}
				if ($first > count ( $this->registeredUsers )) {
					$first = 1;
				}
				$this->displayUsersListHeader ( $first, $nb, true );
				for($i = $first; $i <= min ( count ( $this->registeredUsers ), $first + $nb - 1 ); $i ++) {
					$this->displayRegisteredUser ( $i, $this->registeredUsers [$i] );
				}
			} else {
				echo '<tr><th>No registered user</th></tr>';
			}
			echo '</table>';
		} else {
			echo '<table>';
			if (isset ( $this->registeredUsersByProject [$project] )) {
				if ($nb == 0) {
					$nb = count ( $this->registeredUsersByProject [$project] );
				}
				if ($first > count ( $this->registeredUsersByProject [$project] )) {
					$first = 1;
				}
				for($i = $first; $i <= min ( count ( $this->registeredUsersByProject [$project] ), $first + $nb - 1 ); $i ++) {
					$this->displayRegisteredUser ( $i, $this->registeredUsersByProject [$project] [$i], $project );
				}
			} else {
				echo '<tr><th>No registered user</th></tr>';
			}
			echo '</table>';
		}
	}
	function displayRegisteredUsersListByProject($projects) {
		$first = null;
		if (isset ( $projects )) {
			echo '<script>
  						$(function() {
    						$( "#tabs" ).tabs();
 						 });
  				  </script>';
			echo '<div id="tabs"><ul>';
			foreach ( $projects as $project ) {
				echo '<li><a href="#tabs-' . $project . '">' . $project . '</a></li>';
				if ($first > count ( $this->registeredUsersByProject [$project] )) {
					$first = 1;
				}
			}
			echo "</ul>";
			foreach ( $projects as $project ) {
				echo '<div id="tabs-' . $project . '">';
				$this->displayRegisteredUsersList ( $first, USERS_PER_PAGE, $project );
				echo '</div>';
			}
			echo '</div>';
		}
	}
	
	/* *** Lecture dans l'annuaire **** */
	function readPendingRequestsList() {
		global $project_name;
		try {
			$ldap = new ldapConnect ();
			$ldap->openAdm ();
			$this->pendingRequests = $ldap->listEntries ( PEOPLE_BASE, "(&(objectClass=" . strtolower ( $project_name ) . "User)(" . strtolower ( $project_name ) . "Status=" . STATUS_PENDING . "))", strtolower ( $project_name ) . 'User', strtolower ( $project_name ) . 'ApplicationDate' );
			$_SESSION [strtolower ( $project_name ) . 'pendingRequests'] = serialize ( $this->pendingRequests );
			$ldap->close ();
		} catch ( Exception $e ) {
			$this->mailAdmin ( 'ERREUR', 'Exception lors de la récupération des pendingRequests.', $e );
		}
	}
	function readRejectedRequestsList() {
		global $project_name;
		try {
			$ldap = new ldapConnect ();
			$ldap->openAdm ();
			$this->rejectedRequests = $ldap->listEntries ( PEOPLE_BASE, "(&(objectClass=" . strtolower ( $project_name ) . "User)(" . strtolower ( $project_name ) . "Status=" . STATUS_REJECTED . "))", strtolower ( $project_name ) . 'User', 'sn' );
			$_SESSION [strtolower ( $project_name ) . 'rejectedRequests'] = serialize ( $this->rejectedRequests );
			$ldap->close ();
		} catch ( Exception $e ) {
			$this->mailAdmin ( 'ERREUR', 'Exception lors de la récupération des rejectedRequests.', $e );
		}
	}
	function readParticipantsList() {
		global $project_name;
		try {
			$ldap = new ldapConnect ();
			$ldap->openAdm ();
			$this->participants = $ldap->listEntries ( PEOPLE_BASE, "(&(objectClass=registeredUser)(memberOf=" . strtolower ( $project_name ) . "Participant))", strtolower ( $project_name ) . 'User', 'sn' );
			$_SESSION ['participants'] = serialize ( $this->participants );
			$ldap->close ();
		} catch ( Exception $e ) {
			$this->mailAdmin ( 'ERREUR', 'Exception lors de la récupération des participants.', $e );
		}
	}
	function readRegisteredUsersList() {
		global $project_name;
		try {
			$ldap = new ldapConnect ();
			$ldap->openAdm ();
			$this->registeredUsers = $ldap->listEntries ( PEOPLE_BASE, '(&(objectClass=' . strtolower ( $project_name ) . 'User)(objectClass=registeredUser)(' . strtolower ( $project_name ) . 'Status=' . STATUS_ACCEPTED . '))', strtolower ( $project_name ) . 'User', 'sn' );
			for($i = 1; $i <= count ( $this->registeredUsers ); $i ++) {
				$this->registeredUsers [$i]->otherGroups = array ();
				foreach ( $this->groupList as $group ) {
					if (in_array ( $group->id, $this->registeredUsers [$i]->memberOf )) {
						if (! $group->isAdmin && strnatcasecmp ( strtolower ( $project_name ), $group->project ) == 0) {
							$this->registeredUsers [$i]->editableGroup = $group;
						} else {
							$this->registeredUsers [$i]->otherGroups [$group->id] = $group;
						}
					}
				}
			}
			$_SESSION [strtolower ( $project_name ) . 'registeredUsers'] = serialize ( $this->registeredUsers );
			$ldap->close ();
		} catch ( Exception $e ) {
			$this->mailAdmin ( 'ERREUR', 'Exception lors de la récupération des registeredUsers.', $e );
		}
	}
	function readRegisteredUsersListByProject($project) {
		try {
			$ldap = new ldapConnect ();
			$ldap->openAdm ();
			$this->registeredUsersByProject [$project] = $ldap->listEntries ( PEOPLE_BASE, '(&(objectClass=' . strtolower ( $project ) . 'User)(objectClass=registeredUser)(' . strtolower ( $project ) . 'Status=' . STATUS_ACCEPTED . '))', strtolower ( $project ) . 'User', 'sn' );
			for($i = 1; $i <= count ( $this->registeredUsersByProject [$project] ); $i ++) {
				$this->registeredUsersByProject [$project] [$i]->otherGroups = array ();
				foreach ( $this->groupList as $group ) {
					if (in_array ( $group->id, $this->registeredUsersByProject [$project] [$i]->memberOf )) {
						if (! $group->isAdmin && strnatcasecmp ( $project, $group->project ) == 0) {
							$this->registeredUsersByProject [$project] [$i]->editableGroup = $group;
						} else {
							$this->registeredUsersByProject [$project] [$i]->otherGroups [$group->id] = $group;
						}
					}
				}
			}
			$_SESSION [strtolower ( $project ) . 'registeredUsersByProject'] = serialize ( $this->registeredUsersByProject [$project] );
			$ldap->close ();
		} catch ( Exception $e ) {
			$this->mailAdmin ( 'ERREUR', 'Exception lors de la récupération des registeredUsers ' . strtolower ( $project ) . '.', $e );
		}
	}
}

?>
