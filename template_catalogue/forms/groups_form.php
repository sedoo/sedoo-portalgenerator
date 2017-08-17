<?php
require_once ("forms/login_form.php");
class groups_form extends login_form {
	var $group;
	var $list;
	function createForm() {
		if (isset ( $_SESSION ['loggedUser'] )) {
			$this->user = unserialize ( $_SESSION ['loggedUser'] );
		}
		if ($this->isRoot ()) {
			if (isset ( $_SESSION ["group_list"] )) {
				$this->list = unserialize ( $_SESSION ["group_list"] );
			}
			$groupes = $this->listGroups ();
			$group_select = & $this->createElement ( 'select', 'group', 'group', $groupes );
			$this->addElement ( $group_select );
			$this->addElement ( 'submit', 'bouton_ok', 'Ok' );
			if (session_is_registered ( "group_obj" )) {
				$this->group = unserialize ( $_SESSION ["group_obj"] );
				$this->getElement ( "group" )->setValue ( $this->group->id );
			}
		}
	}
	function createAdminGroupForm() {
		$this->addElement ( 'text', "mail_new", '', array (
				'size' => 50 
		) );
		$this->addRule ( 'mail_new', 'Mail is required', 'required' );
		$this->addRule ( 'mail_new', 'Not a valid email address', 'email' );
		$this->addElement ( 'submit', 'add', '', array (
				'style' => "border:none; color:#fff; background: transparent url('/img/ajouter.png') no-repeat top left; width:16px;height:16px;",
				'title' => 'Add' 
		) );
	}
	function createNewGroupForm() {
		$this->addElement ( 'text', "new_group_name", 'Group name' );
		$this->addRule ( 'new_group_name', 'Group name is required', 'required' );
		$this->addElement ( 'text', "new_group_id", 'Group id' );
		$this->addRule ( 'new_group_id', 'Group id is required', 'required' );
		$this->addRule ( 'new_group_id', 'Group id cannot exceed 16 characters', 'maxlength', 16 );
		$this->addRule ( 'new_group_id', 'Group id is incorrect', 'regex', "/^[a-zA-Z_][a-zA-Z0-9_-]*[$]?$/" );
		$this->addElement ( 'text', "new_group_gid", 'gidNumber' );
		$this->registerRule ( 'gid', 'function', 'validGid' );
		$this->addRule ( 'new_group_gid', 'gid is incorrect', 'gid' );
		$this->addElement ( 'submit', 'bouton_new_group', 'Create' );
	}
	function createGroup() {
		global $project_name;
		$ldap = new ldapConnect ();
		$ldap->open ( $this->user->dn, $this->user->userPassword );
		$id = $this->exportValue ( 'new_group_id' );
		$name = $this->exportValue ( 'new_group_name' );
		$gid = $this->exportValue ( 'new_group_gid' );
		$dn = "groupId=$id," . GROUP_BASE;
		if ($ldap->exists ( $dn )) {
			$this->_errors [] = "A group with this id already exists.";
		} else {
			echo "Create group $id<br>";
			$attrs ["objectClass"] = "group";
			$attrs ["parentProject"] = $project_name;
			$attrs ["isAdmin"] = "TRUE";
			$attrs ["modifiable"] = "TRUE";
			$attrs ["groupId"] = $id;
			$attrs ["cn"] = $name;
			if (isset ( $gid ) && ! empty ( $gid ))
				$attrs ["gidNumber"] = $gid;
			$ldap->addEntry ( $dn, $attrs );
		}
		$ldap->close ();
	}
	function listGroups() {
		global $project_name;
		$ldap = new ldapConnect ();
		$ldap->open ( $this->user->dn, $this->user->userPassword );
		$list = $ldap->listEntries ( GROUP_BASE, "(&(parentProject=$project_name)(modifiable=TRUE))", 'groupeFtp', 'cn' );
		$groupes = array ();
		foreach ( $list as $group ) {
			$groupes [$group->id] = $group->cn;
		}
		$ldap->close ();
		return $groupes;
	}
	function listUsers() {
		$groupId = $this->exportValue ( 'group' );
		$ldap = new ldapConnect ();
		$ldap->open ( $this->user->dn, $this->user->userPassword );
		$list = $ldap->listEntries ( PEOPLE_BASE, "(&(memberOf=$groupId))", MainProject . 'User', 'mail' );
		$listEmails = array ();
		foreach ( $list as $user ) {
			$listEmails [] = $user->mail;
		}
		$this->group = $ldap->getEntry ( "groupId=$groupId," . GROUP_BASE, "groupeFtp" );
		$this->getElement ( "group" )->setValue ( $this->group->id );
		$this->list = array_intersect ( $this->group->memberUid, $listEmails );
		sort ( $this->list );
		$_SESSION ["group_list"] = serialize ( $this->list );
		$_SESSION ["group_obj"] = serialize ( $this->group );
		$ldap->close ();
	}
	function remove($i) {
		if ($this->list) {
			echo "Remove $i, " . $this->list [$i] . '<br>';
			$ldap = new ldapConnect ();
			$ldap->open ( $this->user->dn, $this->user->userPassword );
			$attrsUser ["memberOf"] = $this->group->id;
			$attrsGroup ['memberUid'] = $this->list [$i];
			$ldap->deleteAttributes ( 'groupId=' . $this->group->id . ',' . GROUP_BASE, $attrsGroup );
			$ldap->deleteAttributes ( $ldap->getUserDn ( $this->list [$i] ), $attrsUser );
			$ldap->close ();
			unset ( $this->list [$i] );
			$_SESSION ["group_list"] = serialize ( $this->list );
		}
	}
	function add() {
		$newMember = $this->exportValue ( 'mail_new' );
		if (isset ( $this->group )) {
			if (isset ( $newMember ) && ! empty ( $newMember )) {
				if (in_array ( $newMember, $this->list ))
					$this->_errors [] = "Already in this group.";
				else {
					$ldap = new ldapConnect ();
					$ldap->open ( $this->user->dn, $this->user->userPassword );
					if ($ldap->exists ( $ldap->getUserDn ( $newMember ) )) {
						echo "Add $newMember<br>";
						$nvAttrsUser ["memberOf"] = $this->group->id;
						$nvAttrsGroup ['memberUid'] = $newMember;
						$ldap->addAttributes ( 'groupId=' . $this->group->id . ',' . GROUP_BASE, $nvAttrsGroup );
						$ldap->addAttributes ( "mail=$newMember," . PEOPLE_BASE, $nvAttrsUser );
						$this->list [] = $newMember;
						sort ( $this->list );
						$_SESSION ["group_list"] = serialize ( $this->list );
					} else {
						$this->_errors [] = "This user is not registered.";
					}
					$ldap->close ();
				}
			}
		}
		$this->getElement ( "mail_new" )->setValue ( '' );
	}
	function displayUsers() {
		
		// Affichage des erreurs
		if (! empty ( $this->_errors )) {
			foreach ( $this->_errors as $error ) {
				echo '<font size="3" color="red">' . $error . '</font><br>';
			}
		}
		if ($this->group) {
			echo '<form action="' . $reqUri . '" method="post" name="frmusers" id="frmusers" >';
			echo '<table><tr><th colspan="3" align="center">' . $this->group->cn . ' (' . count ( $this->list ) . ')</th></tr>';
			echo "<tr><td>$i</td><td>" . $this->getElement ( "mail_new" )->toHTML () . '</td><td>' . $this->getElement ( "add" )->toHTML () . '</td></tr>';
			if ($this->list) {
				foreach ( $this->list as $i => $mail ) {
					$this->addElement ( 'text', "mail_$i", '', array (
							'size' => 50 
					) );
					$this->getElement ( "mail_$i" )->setValue ( $mail );
					$this->getElement ( "mail_$i" )->freeze ();
					
					$this->addElement ( 'submit', 'bouton_ok', 'Ok' );
					$this->addElement ( 'submit', "rem_$i", '', array (
							'style' => "border:none; color:#fff; background: transparent url('/img/supprimer.png') no-repeat top left; width:16px;height:16px;",
							'title' => 'Remove' 
					) );
					echo "<tr><td>$i</td><td>" . $this->getElement ( "mail_$i" )->toHTML () . '</td><td>' . $this->getElement ( "rem_$i" )->toHTML () . '</td></tr>';
				}
			}
			$reste = array_diff ( $this->group->memberUid, $this->list );
			foreach ( $reste as $mail ) {
				echo "<tr><td></td><td>$mail</td><td></td></tr>";
			}
			echo '</table></form>';
		}
	}
	function displayNewGroupForm() {
		// Affichage des erreurs
		if (! empty ( $this->_errors )) {
			foreach ( $this->_errors as $error ) {
				echo '<font size="3" color="red">' . $error . '</font><br>';
			}
		}
		$reqUri = $_SERVER ['REQUEST_URI'];
		echo '<form action="' . $reqUri . '" method="post" name="frmnewgroup" id="frmnewgroup" >';
		echo '<table>';
		echo '<tr><td>' . $this->getElement ( 'new_group_id' )->getLabel () . '</td><td>' . $this->getElement ( 'new_group_id' )->toHTML () . '</td></tr>';
		echo '<tr><td>' . $this->getElement ( 'new_group_name' )->getLabel () . '</td><td>' . $this->getElement ( 'new_group_name' )->toHTML () . '</td></tr>';
		echo '<tr><td>' . $this->getElement ( 'new_group_gid' )->getLabel () . '</td><td>' . $this->getElement ( 'new_group_gid' )->toHTML () . '</td></tr>';
		echo '<tr><td colspan="2" align="center">' . $this->getElement ( 'bouton_new_group' )->toHTML () . '</td></tr>';
		echo '</table>';
		echo '</form>';
	}
	function displayForm() {
		// Affichage des erreurs
		$reqUri = $_SERVER ['REQUEST_URI'];
		echo '<form action="' . $reqUri . '" method="post" name="frmgroups" id="frmgroups" >';
		echo '<table>';
		echo '<tr><td colspan="2" align="center">' . $this->getElement ( 'group' )->toHTML () . '</td></tr>';
		echo '<tr><td colspan="2" align="center">' . $this->getElement ( 'bouton_ok' )->toHTML () . '</td></tr>';
		echo '</table>';
		echo '</form>';
	}
}

?>
