<?php

require_once 'login_form.php';
require_once 'bd/requete.php';
require_once 'extract/requeteXml.php';
require_once 'extract/sortieCGI.php';

class db_requests_forms extends login_form{

	var $requetes;
	var $isAdmin;

	function createForm($admin = false){
		if (isset($_SESSION['loggedUser'])){
			$this->user = unserialize($_SESSION['loggedUser']);
		}


		$this->isAdmin = $admin && $this->isRoot();

		if ($this->isLogged()){
			$this->loadRequetes();						
		}else{
			$this->createLoginForm('Mail');
		}
	}

	private function printStatus($etat, $killed, $values = 0){
		if ($etat == requete::CODE_EN_COURS && !$killed)
			return "<font color='yellow'>Running ($values)</font>";
		else if ($etat == requete::CODE_EN_COURS && $killed)
			return "<font color='orange'>Stop asked</font>";
		else if ($etat == requete::CODE_ECHEC)
			return "<font color='red'>Failed</font>";
		else if ($etat == requete::CODE_SUCCES && !$killed)
			return "<font color='green'>Completed ($values)</font>";
		else if ($etat == requete::CODE_SUCCES && $killed)
			return "<font color='green'>Stopped ($values)</font>";
		else if ($etat == requete::CODE_VIDE)
			return "<font color='red'>Empty result</font>";
	}

	private function printResume($xml){
		$requete = requeteXml::readXml($xml);
		return nl2br($requete->toString());
	}
	
	private function loadRequetes(){
		$r = new requete;
		if ($this->isAdmin)
			$this->requetes = $r->getAll();
		else
			$this->requetes = $r->getByUser($this->user->mail);
	}
	
	function kill($id){
		foreach ($this->requetes as $r){
			if ($r->requeteId == $id){
				$r->kill();
			}
				
		}
	}
	
	function send($id){
		foreach ($this->requetes as $r){
			if ($r->requeteId == $id){
				if (send_to_cgi($r->xml,$retour)){
					$elts = explode(':',$retour);
					if ($elts[0] == '00')
						echo "<font size=\"3\" color='green'>Extraction successfully launched. The result will be send to you by email.</font>";
					else
						echo "$retour<br>";
					$this->loadRequetes();
				}else{
					echo "<font size='3' color='red'>$retour</font>";
				}
			}
		}
	}
	
	function display(){
		echo '<form method="post" ><table>';
		echo '<tr><th>Id</th>';
		if ($this->isAdmin) echo '<th>User</th>';
		echo '<th>Begin / End</th><th>Status (values)</th><th></th></tr>';
		foreach ($this->requetes as $r){
			
			if ($r->isRunning()){
				$this->addElement('submit',"bouton__kill_$r->requeteId",'Kill');
			}else{
				$this->addElement('submit',"bouton__kill_$r->requeteId",'Kill',array('disabled' => 'true'));
			}
			if ($r->isFinished()){
				$this->addElement('submit',"bouton_launch_$r->requeteId",'Run');
			}else{
				$this->addElement('submit',"bouton_launch_$r->requeteId",'Run',array('disabled' => 'true'));
			}
			
			
			echo "<tr><td rowspan='3'>$r->requeteId</td>";
			if ($this->isAdmin) echo "<td rowspan='2' style='white-space : nowrap;'>$r->mail</td>";
			echo "<td>".$r->dateDeb->format('Y-m-d H:i')
				."</td><td rowspan='2'>".$this->printStatus($r->etat,$r->killed,$r->nbValeurs)."</td><td rowspan='2' style='white-space : nowrap;'>"
				.$this->printResume($r->xml)."</td></tr>";



			echo '<tr><td>'.(($r->dateFin)?$r->dateFin->format('Y-m-d H:i'):'').'</td></tr>';
			echo '<tr><td colspan="4" align="center">'.$this->getElement("bouton__kill_$r->requeteId")->toHTML().'&nbsp;&nbsp;'
                                .$this->getElement("bouton_launch_$r->requeteId")->toHTML()."</td></tr>";			
		
		}
		echo '</table></form>';
	}
	
}

?>
