<?php
require_once ("/sites/kernel/#MainProject/conf.php");
require_once ("ldap/portalUser.php");
require_once ("ldap/constants.php");
class projectUser extends portalUser {
	
	// TODO ajout attribut status (pending, registered, rejected)
	var $proj_name;
	var $status;
	var $applicationDate;
	var $registrationDate;
	var $associatedProject;
	var $wg;
	var $abstract;
	function __construct($dn = null, $attrs = null, $proj) {
		parent::__construct ( $dn, $attrs );
		$this->proj_name = $proj;
		if (isset ( $attrs ))
			$this->initProjectUser ( $attrs );
	}
	function initProjectUser($attrs) {
		$this->applicationDate = $attrs [strtolower ( $this->proj_name ) . "ApplicationDate"] [0];
		if ($attrs [strtolower ( $this->proj_name ) . "Status"]) {
			$this->status = $attrs [strtolower ( $this->proj_name ) . "Status"] [0];
		}
		if ($attrs [strtolower ( $this->proj_name ) . "AssociatedProject"]) {
			$this->associatedProject = $attrs [strtolower ( $this->proj_name ) . "AssociatedProject"] [0];
		}
		if ($attrs [strtolower ( $this->proj_name ) . "RegistrationDate"]) {
			$this->registrationDate = $attrs [strtolower ( $this->proj_name ) . "RegistrationDate"] [0];
		}
		if ($attrs [strtolower ( $this->proj_name ) . "Abstract"]) {
			$this->abstract [strtolower ( $this->proj_name ) . "Abstract"] [0] = $attrs [strtolower ( $this->proj_name ) . "Abstract"] [0];
		}
		if ($attrs [strtolower ( $this->proj_name ) . "Wg"]) {
			for($i = 0; $i < $attrs [strtolower ( $this->proj_name ) . "Wg"] ["count"]; $i ++) {
				$this->wg [$i] = $attrs [strtolower ( $this->proj_name ) . "Wg"] [$i];
			}
		}
	}
	
	/*
	 * Retourne un tableau contenant tous les attributs d'un utilisateur non enregistré.
	 */
	function getProjectUserEntry() {
		$entree = $this->getUserEntry ();
		$entree ["objectClass"] [] = strtolower ( $this->proj_name ) . 'User';
		$entree [strtolower ( $this->proj_name ) . "Status"] = $this->status;
		$entree [strtolower ( $this->proj_name ) . "ApplicationDate"] = $this->applicationDate;
		if (isset ( $this->abstract ) && ! empty ( $this->abstract )) {
			$entree [strtolower ( $this->proj_name ) . "Abstract"] = $this->abstract;
		}
		if (isset ( $this->wg ) && ! empty ( $this->wg )) {
			$entree [strtolower ( $this->proj_name ) . "Wg"] = $this->wg;
		}
		if (isset ( $this->associatedProject ) && ! empty ( $this->associatedProject )) {
			$entree [strtolower ( $this->proj_name ) . "AssociatedProject"] = $this->associatedProject;
		}
		return $entree;
	}
	
	/*
	 * Retourne un tableau contenant tous les attributs d'un utilisateur enregistré.
	 */
	function getProjectRegisteredUserEntry() {
		$entree = $this->getRegisteredUserEntry ();
		$entree ["objectClass"] [] = strtolower ( $this->proj_name ) . 'User';
		$entree [strtolower ( $this->proj_name ) . "ApplicationDate"] = $this->applicationDate;
		$entree [strtolower ( $this->proj_name ) . "Status"] = $this->status;
		$entree [strtolower ( $this->proj_name ) . "RegistrationDate"] = $this->registrationDate;
		$entree [strtolower ( $this->proj_name ) . "AssociatedProject"] = $this->associatedProject;
		$entree [strtolower ( $this->proj_name ) . "Wg"] = $this->wg;
		$entree [strtolower ( $this->proj_name ) . "Abstract"] = $this->abstract;
		return $entree;
	}
	function toString($withDn = false) {
		$result = parent::toString ( $withDn );
		if (isset ( $this->wg ) && ! empty ( $this->wg )) {
			$result .= "WG:\n";
			foreach ( $this->wg as $group ) {
				$result .= "- $group.\n";
			}
		}
		$result .= "Project: $this->associatedProject\n";
		$result .= "Application Date: $this->applicationDate\nStatus: $this->status\n";
		if (isset ( $this->registrationDate ) && ! empty ( $this->registrationDate )) {
			$result .= "Registration Date: $this->registrationDate\n";
		}
		if (isset ( $this->abstract ) && ! empty ( $this->abstract )) {
			$result .= strtolower ( $this->proj_name ) . " abstract: $this->abstract\n";
		}
		return $result;
	}
	function isProjectAdmin() {
		global $project_name,$MainProjects;
		if(in_array($project_name,$MainProjects))
			return $this->isMemberOf ( array (
					strtolower($project_name).'Adm',
					'root'
			) );
		else
			return $this->isRoot();
	}
}

?>
