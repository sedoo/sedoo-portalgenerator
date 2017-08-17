<?php
require_once ("conf/conf.php");
require_once ("ldap/constants.php");
require_once ("ldap/entry.php");
class portalUser extends entry {
	var $lastname;
	var $firstname;
	var $cn;
	var $mail;
	var $affiliation;
	var $street;
	var $zipCode;
	var $city;
	var $country;
	var $phoneNumber;
	var $abstract;
	var $altMail;
	var $otherUser;
	var $applicationDate;
	var $registrationDate;
	
	// Utilisateurs enregistrés uniquement
	var $memberOf;
	var $userPassword;
	var $userPasswords;
	var $otherGroups;
	var $editableGroup;
	var $supervisor_name;
	var $supervisor_affiliation;
	function __construct($dn = null, $attrs = null) {
		if (isset ( $dn ))
			parent::__construct ( $dn ); // $this->dn=$dn;
		if (isset ( $attrs ))
			$this->initUser ( $attrs );
	}
	var $attrs;
	function initUser($attrs) {
		global $MainProjects,$project_name;
		$this->attrs = $attrs;
		
		// Attributs obligatoires
		$this->mail = $attrs ["mail"] [0];
		$this->cn = $attrs ["cn"] [0];
		
		// Attributs optionels
		if (isset($attrs ["sn"]) && !empty($attrs ["sn"])) {
			$this->lastname = $attrs ["sn"] [0];
		}
		if (isset($attrs ["altMail"]) && !empty($attrs ["altMail"])) {
			$this->altMail = $attrs ["altMail"] [0];
		}
		if (isset($attrs ["o"]) && !empty($attrs ["o"])) {
			$this->affiliation = $attrs ["o"] [0];
		}
		if (isset($attrs ["street"]) && !empty($attrs ["street"])) {
			$this->street = $attrs ["street"] [0];
		}
		if (isset($attrs ["postalCode"]) && !empty($attrs ["postalCode"])) {
			$this->zipCode = $attrs ["postalCode"] [0];
		}
		if (isset($attrs ["l"]) && !empty($attrs ["l"])) {
			$this->city = $attrs ["l"] [0];
		}
		if (isset($attrs ["c"]) && !empty($attrs ["c"])) {
			$this->country = $attrs ["c"] [0];
		}
		if (isset($attrs ["telephoneNumber"]) && !empty($attrs ["telephoneNumber"])) {
			$this->phoneNumber = $attrs ["telephoneNumber"] [0];
		}
		if (isset($attrs ["description"]) && !empty($attrs ["description"])) {
			$this->abstract = $attrs ["description"] [0];
		}
		
		// Students
		if (isset($attrs ["studentSupervisorName"]) && !empty($attrs ["studentSupervisorName"])) {
			$this->supervisor_name = $attrs ["studentSupervisorName"] [0];
		}
		if (isset($attrs ["studentSupervisorAffiliation"]) && !empty($attrs ["studentSupervisorAffiliation"])) {
			$this->supervisor_affiliation = $attrs ["studentSupervisorAffiliation"] [0];
		}
		
		// Registered
		if (isset($attrs ["memberOf"]) && !empty($attrs ["memberOf"])) {
			for($i = 0; $i < $attrs ["memberOf"] ["count"]; $i ++) {
				$this->memberOf [$i] = $attrs ["memberOf"] [$i];
			}
		}
		if (isset($attrs ["userPassword"]) && !empty($attrs ["userPassword"])) {
			$this->userPassword = $attrs ["userPassword"] [0];
			for($i = 0; $i < $attrs ["userPassword"] ["count"]; $i ++) {
				$this->userPasswords [$i] = $attrs ["userPassword"] [$i];
			}
		}
		if (isset($attrs [strtolower(MainProject).'ApplicationDate']) && !empty($attrs [strtolower(MainProject).'ApplicationDate'])) {
			$d=$attrs [strtolower(MainProject).'ApplicationDate'][0];
			$this->applicationDate = $d;
		}else{
			$minDate= new DateTime ("now");
			foreach($MainProjects as $proj){
				if (isset($attrs [strtolower($proj).'ApplicationDate']) && !empty($attrs [strtolower($proj).'ApplicationDate'])) {
					$d=$attrs [strtolower($proj).'ApplicationDate'][0];
					$Date= new DateTime( $d[0].$d[1].$d[2].$d[3].'-'.$d[4].$d[5].'-'.$d[6].$d[7]);
					if($minDate > $Date){
						$minDate = $Date;
					}
					$this->applicationDate = $minDate;
				}
			}
		}
		if (isset($attrs [strtolower(MainProject).'RegistrationDate']) && !empty($attrs [strtolower(MainProject).'RegistrationDate'])) {
			$d=$attrs [strtolower(MainProject).'RegistrationDate'][0];
			$this->registrationDate = $d;
		}else{
			$minDate= new DateTime ("now");
			foreach($MainProjects as $proj){
				if (isset($attrs [strtolower($proj).'RegistrationDate']) && !empty($attrs [strtolower($proj).'RegistrationDate'])) {
					$d=$attrs [strtolower($proj).'RegistrationDate'][0];
					$Date= new DateTime( $d[0].$d[1].$d[2].$d[3].'-'.$d[4].$d[5].'-'.$d[6].$d[7]);
					if($minDate > $Date){
						$minDate = $Date;
					}
					$this->registrationDate = $minDate;
				}
			}
		}
	}
	function toString($withDn = false) {
		if (isset ( $this->memberOf ) && ! empty ( $this->memberOf )) {
			foreach ( $this->memberOf as $group ) {
				$groups .= "\n- $group";
			}
		}
		
		$result = ($withDn) ? parent::toString () : '' . "Name: $this->cn\n" . "Mail: $this->mail\n" . "altMail: $this->altMail\n" . "Affiliation: $this->affiliation\n" . "Address: $this->street\n" . "Zip Code: $this->zipCode\n" . "City: $this->city\n" . "Country: $this->country\n" . "Telephone: $this->phoneNumber\n" . "Abstract: $this->abstract\n";
		
		if (isset ( $groups ) && ! empty ( $groups )) {
			$result .= "Group(s): $groups\n";
		}
		
		if (isset ( $this->userPassword ) && ! empty ( $this->userPassword )) {
			$result .= "Password: $this->userPassword\n";
		}
		
		if (isset ( $this->supervisor_name ) && ! empty ( $this->supervisor_name )) {
			$result .= "Supervisor Name: $this->supervisor_name\n" . "Supervisor Affiliation: $this->supervisor_affiliation\n";
		}
		
		return $result;
	}
	
	/*
	 * function printUser(){ echo 'DN: '.$this->dn.'<br>'; echo 'Name: '.$this->cn.'<br>'; echo 'Mail: '.$this->mail.'<br>'; echo 'Affiliation: '.$this->affiliation.'<br>'; echo 'Address: '.$this->street.'<br>'; echo 'Zip Code: '.$this->zipCode.'<br>'; echo 'City: '.$this->city.'<br>'; echo 'Country: '.$this->country.'<br>'; echo 'Telephone: '.$this->phoneNumber.'<br>'; echo 'Abstract: '.$this->abstract.'<br>'; }
	 */
	function printRegisteredUser() {
		$this->printUser ();
		echo 'Password:' . $this->userPassword . '<br>';
		echo 'Group(s): <br>';
		foreach ( $this->memberOf as $group ) {
			echo '- ' . $group . '<br>';
		}
	}
	function getUserDn() {
		return 'mail=' . $this->mail . ',' . PEOPLE_BASE;
	}
	function isRoot() {
		if (isset ( $this->memberOf ) && ! empty ( $this->memberOf )) {
			foreach ( $this->memberOf as $group ) {
				// echo $group.'<br>';
				if ($group == 'root')
					return true;
			}
		}
		return false;
	}
	
	/*
	 * Teste si l'utilisateur est membre d'un des groupes du tableau $groups.
	 */
	function isMemberOf($groups) {
		$i = 0;
		if (isset ( $this->memberOf ) && ! empty ( $this->memberOf )) {
			foreach ( $this->memberOf as $group ) {
				if (false !== array_search ( $group, $groups )) {
					$i ++;
				}
			}
		}
		if ($i > 0)
			return true;
		else
			return false;
	}
	
	function isAdmin() {
		global $project_name,$MainProjects;
		if(in_array($project_name,$MainProjects) || $project_name == MainProject)
			return $this->isMemberOf ( array (
					strtolower($project_name).'Adm',
					'root' 
			) );
		else
			return $this->isRoot();
	}
	
	function isportalAdmin() {
		return $this->isMemberOf ( array (
				strtolower ( MainProject ) . 'Adm',
				'root' 
		) );
	}
		
      /*  Deja codé dans form/login_form 
		function isPortalUser() {
		$roles= explode(',',PASSY_ROLES);
                print_r($roles);
		return ($this->isMemberOf ($roles));
        }
	*/



	function isprojectAdmin() {
		global $project_name;
		if(isset($project_name) && !empty($project_name))
			return $this->isMemberOf ( array (
					strtolower($project_name).'Adm',
					'root'
			) );
		else
			return $this->isRoot();
	}
	
	/*
	 * Retourne un tableau contenant tous les attributs.
	 */
	function getUserEntry() {
		$entree ["objectClass"] [0] = USER_CLASS;
		$entree ["cn"] = $this->cn;
		$entree ["sn"] = $this->lastname;
		$entree ["mail"] = $this->mail;
		$entree ["altMail"] = $this->altMail;
		$entree ["description"] = $this->abstract;
		$entree ["street"] = $this->street;
		$entree ["telephoneNumber"] = $this->phoneNumber;
		$entree ["postalCode"] = $this->zipCode;
		$entree ["o"] = $this->affiliation;
		$entree ["l"] = $this->city;
		$entree ["c"] = $this->country;
		
		if (isset ( $this->supervisor_name ) && ! empty ( $this->supervisor_name )) {
			$entree ["objectClass"] [] = STUDENT_CLASS;
			$entree ["studentSupervisorName"] = $this->supervisor_name;
			$entree ["studentSupervisorAffiliation"] = $this->supervisor_affiliation;
		}
		
		return $entree;
	}
	function getRegisteredUserEntry() {
		$entree = $this->getUserEntry ();
		$entree ["userPassword"] = $this->userPassword;
		for($i = 0; $i < count ( $this->memberOf ); $i ++) {
			$entree ["memberOf"] [$i] = $this->memberOf [$i];
		}
		
		if (isset ( $this->supervisor_name ) && ! empty ( $this->supervisor_name )) {
			$entree ["objectClass"] [] = STUDENT_CLASS;
			$entree ["studentSupervisorName"] = $this->supervisor_name;
			$entree ["studentSupervisorAffiliation"] = $this->supervisor_affiliation;
		}
		
		return $entree;
	}
}

?>
