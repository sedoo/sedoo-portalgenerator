<?php

require_once ("forms/login_form.php");
require_once ('extract/reponseXml.php');

class extract_download_form extends login_form{

	var $reponse;
	
	function createForm($resultId = null, $project_name = null){
		if ($_SESSION['loggedUser']){
			$this->user = unserialize($_SESSION['loggedUser']);
			if (get_class($this->user) == 'user'){
                                $this->user = null;
                        }
		}
		if (isset($resultId) && !empty($resultId)){
	                try{
				$this->reponse = new reponseXml($resultId,$project_name);
                	}catch (Exception $e){
                        	echo '<font size="3" color="red">'.$e->getMessage().'</font><br>';
                	}

			if ( ! $this->isLogged() ){
                        	if ( $this->reponse->isPublic() ){
                                	$this->createLoginForm('Mail', true);
                        	}else{
                                	$this->createLoginForm('Mail');
                        	}
			}
                }	
	}

	function initForm($resultId = null,$project_name){
		if (isset($resultId) && !empty($resultId)){
			$this->reponse = new reponseXml($resultId,$project_name);
			echo 'Public: '.$this->reponse->isPublic().'<br>';
		}
	}
	
	function testUser(){
		if ($this->isRoot()){
			return true;
		}else{
			return ( ($this->reponse->isPublic() && $this->isLogged()) || $this->isPortalUser()) && ($this->reponse->mail == $this->user->mail);
		}
	}
	
	function display(){
		if (isset($this->reponse)){
			if ( $this->reponse->id == 0){
				echo '<h1>Download page</h1><br>';
			}else{
				echo '<h1>Download (request id: '.$this->reponse->id.')</h1><br>';
			}
			if ($this->testUser()){
				$this->reponse->toHtml();
			}else{
				echo '<font size="3" color="red">You cannot access this page.</font><br>';
			}
		}
	}
	
}

?>
