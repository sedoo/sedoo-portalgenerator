<?php

require_once ("bd/journal.php");

class suscribe_form extends login_form{

	function createForm(){
                if (isset($_SESSION['loggedUser'])){ 
                        $this->user = unserialize($_SESSION['loggedUser']);
                }

                if ($this->isLogged()){

                }else{
                        $this->createLoginForm('Mail');
                }
        }


	function addAbo($datsId){
		try{
			$aboIds = unserialize($_SESSION['loggedUserAbos']);
			if ( !isset($aboIds) )
                                $aboIds = array();
			if (array_search($datsId,$aboIds) === false){
	                	if ( journal::addAboEntry($this->user->mail,$datsId) ){
					$aboIds[] = $datsId;
        	        	        $_SESSION['loggedUserAbos'] = serialize($aboIds);
					return true;
				}	
			}else return true;
			return false;
		}catch(Exception $e){
			return false;
		}
        }

}

?>
