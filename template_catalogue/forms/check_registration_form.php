<?php

require_once("HTML/QuickForm.php");
require_once("ldap/ldapConnect.php");

class check_registration_form extends HTML_QuickForm{

//var $mail = 'brissebr@sedoo.fr';
//$mail = 'guillaume.brissebrat@sedoo.fr';
//$mail = 'guillaume.brissebrat@obs-mip.fr';

var $msg = null;

var $project;

function createForm($project){
	$this->project = $project;
	
	$this->addElement('text','email_check','Mail',array('size'=>40));
	$this->applyFilter('email_check','trim');
	$this->addRule('email_check','Mail is required','required');

	$this->addElement('submit', 'bouton_check', 'Check');
}

function displayForm(){
	echo "<h2>Status of your request</h2><p/>";

	//Affichage des erreurs
        if ( !empty($this->_errors) ){
	        foreach ($this->_errors as $error) {
        	        echo '<font size="3" color="red">'.$error.'</font><br>';
                }
        }else if ($this->msg){
		echo "<font size=\"3\" color='green'>$this->msg</font><br>";
	}

	$reqUri = $_SERVER['REQUEST_URI'];
        echo '<form action="'.$reqUri.'" method="post" name="frmchk" id="frmchk" >';
	echo '<table>';
	echo '<tr><td><font color="#467AA7">'.$this->getElement('email_check')->getLabel().'</font></td><td>'.$this->getElement('email_check')->toHTML().'</td></tr>';
	echo '<tr><td colspan="2" align="center">'.$this->getElement('bouton_check')->toHTML().'</td></tr>';
	echo '</table>';
        echo '</form>';
}

function check(){
	global $project_name;
	$mail = $this->exportValue('email_check');
	try{
		$userClass = strtolower($project_name)."User";
		$ldap = new ldapConnect();
		$ldap->openAdm();
		$user = $ldap->getEntry($ldap->getUserDn($mail),$userClass);
		if ($user){
			if ($user->status){
				//echo "Status: $user->status\n";
//				return $user->status;
				if ( $user->status == STATUS_ACCEPTED)
					$this->msg = "You are already registered to access $this->project data.";
				else if ( $user->status == STATUS_REJECTED)
					$this->msg = "Your request has been rejected.";
				else if ( $user->status == STATUS_PENDING)
                                        $this->msg = "Your request has been succesfully sent.You will get an answer by email soon.";
				else
					$this->_errors[] = "An error occurred. Please contact the website administrator.";
			}else{
				$this->msg = "You're already registered but need to sign the data policy.\n";
			}
		//	echo "$mail: $user->status\n";
		//foreach ($user->memberOf as $group)
		//	echo "  $group\n";
		}else{
			$this->_errors[] = "No registration request found.";
		}
		$ldap->close();
	}catch(Exception $e){
		$this->mailAdmin('ERREUR',"Erreur lors de la vérification d'une demande d'enregistrement.",$e);
	        $this->_errors[] = "An error occurred. Please contact the website administrator.";
		echo "Erreur: $e\n";
	}
//	return null;
}
}
?>
