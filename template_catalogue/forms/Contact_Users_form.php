<?php 

require_once ("/sites/kernel/#MainProject/conf.php");
require_once ("HTML/QuickForm.php");
require_once ("ldap/ldapConnect.php");
require_once ("mail.php");

class Contact_Users_form extends HTML_QuickForm{

	function createForm($project) {
		$this->addElement('textarea','EditionArea','Message',array('cols'=>50, 'rows'=>8));
		$this->addRule('EditionArea','You have to write your message first','required');
		$this->addElement('text','Subject','Subject',array('cols'=>50, 'rows'=>8));
		$this->addRule('Subject','The subject is required','required');
		$this->addElement('submit', 'bouton_send', 'Send', array('style'=>'text-align:center;'));
	}
	
	function display(){
		//Affichage des erreurs
		if ( !empty($this->_errors) ){
			foreach ($this->_errors as $error) {
				echo '<font size="3" color="red">'.$error.'</font><br>';
			}
		}
		$reqUri = $_SERVER['REQUEST_URI'];
		
		echo '<form action="'.$reqUri.'" method="post" name="frmContactUsers" id="frmContactUsers" >';
		echo '<SCRIPT LANGUAGE="Javascript" SRC="/js/functions.js"> </SCRIPT>';
		echo '<SCRIPT LANGUAGE="Javascript" SRC="/utils/ckeditor/ckeditor.js"> </SCRIPT>';
		echo '<table><tr><td colspan="3" align="center"><font color="#467AA7">Mandatory fields are in blue</font></td></tr>';
		echo '<tr><td><font color="#467AA7">'.$this->getElement('Subject')->getLabel().'</font></td><td colspan="2">'.$this->getElement('Subject')->toHTML().'</td></tr>';
		echo '<tr><td><font color="#467AA7">'.$this->getElement('EditionArea')->getLabel().'</font></td><td colspan="2">'.$this->getElement('EditionArea')->toHTML().'</td></tr>';
		echo '<tr><td colspan="2" align="center">'.$this->getElement('bouton_send')->toHTML().'</td></tr>';
		echo '</table></form>';
		echo '<SCRIPT LANGAGE="Javascript">'.
				'CKEDITOR.replace("EditionArea");'.
				'</SCRIPT>';	
	}
	function getProjectUsers($project){
		$ldap = new ldapConnect ();
		$ldap->openAdm ();
		$projectUsers = $ldap->listEntries ( PEOPLE_BASE, '(&(objectClass='.strtolower($project).'User)(objectClass=registeredUser)('.strtolower($project).'Status=' . STATUS_ACCEPTED . '))',strtolower($project).'User', 'sn' );
		$ldap->close();
		return $projectUsers;
	}
	
	function sendMessageToAllUsers($project){
		$projectUsers = $this->getProjectUsers($project);
		$Subject = $this->exportValue('Subject');
		$Message = $this->exportValue('EditionArea');
		foreach ($projectUsers as $user){
			sendMailSimple($user->mail,$Subject,$Message,ROOT_EMAIL,true);
		}
		
	}	
}


?>