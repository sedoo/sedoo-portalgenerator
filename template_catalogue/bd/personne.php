<?php
/*
 * Created on 8 juil. 2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 	require_once ("bd/bdConnect.php");
 	require_once ("bd/country.php");
 	require_once ("bd/contact_type.php");
 	require_once ("bd/organism.php");
 	
 	
 	class personne
 	{
 		var $pers_id;
 		var $org_id;
		var $pers_name;
		var $pers_email_1;
		var $pers_email_2;
		var $organism;	
		var $contact_type_id;
		var $contact_type;

	
		function new_contact($name, $mail, $organismName){
			$this->pers_name = $name;
			$this->pers_email_1 = $mail;
			$this->organism = new organism();
			$this->organism->org_sname = $organismName;
		} 
	
		function new_personne($tab)
		{
			$this->pers_id = $tab[0];
			$this->org_id = $tab[1];
			$this->pers_name = $tab[2];
			$this->pers_email_1 = $tab[3];
			$this->pers_email_2 = $tab[4];
			
			if (isset($tab[6]) && !empty($tab[6])){
				$this->contact_type_id = $tab[6];
				$ct = new contact_type;
 				$this->contact_type = $ct->getById($this->contact_type_id);
			}
    		
    		if (!isset($this->org_id) || empty($this->org_id))
    			$this->organism = new organism;
    		else
    		{
    			$org = new organism;
    			$this->organism = $org->getById($this->org_id);
    		}
    		
		}
		
		function toString(){
			$result = "Contact: ".$this->pers_name;
			if (isset($this->pers_email_1) && !empty($this->pers_email_1)){
				$result .= ", email1: ".$this->pers_email_1;
			}
			if (isset($this->pers_email_2) && !empty($this->pers_email_2)){
				$result .= ", email2: ".$this->pers_email_2;
			}
			
			if ( isset($this->organism) ){
				$result .= "\n".$this->organism->toString();
			}
			if ( isset($this->contact_type_id) ){
				$result .= "\nType: ".$this->contact_type_id;
			}
			return $result;
		}
		
		function isPI(){
			return $this->contact_type_id == 1;
		}
		
		function getAll()
		{
			$query = 'select * from personne order by pers_name';
			return $this->getByQuery($query);
		}
		
		function getByQuery($query)
		{
			$bd = new bdConnect;
      		$liste = array();
      		if ($resultat = $bd->get_data($query))
      		{
        		for ($i=0; $i<count($resultat);$i++)
        		{
          			$liste[$i] = new personne;
          			$liste[$i]->new_personne($resultat[$i]);
        		}
      		}
      		return $liste;
		}
		
		function getById($id)
		{
			if (!isset($id) || empty($id))
        		return new personne;

      		$query = "select * from personne where pers_id = ".$id;
      		$bd = new bdConnect;
      		if ($resultat = $bd->get_data($query))
      		{
        		$pers = new personne;
        		$pers->new_personne($resultat[0]);
      		}
      		return $pers;
		}
		
		function existe()
    	{
        	$query = "select * from personne where " .
        			"lower(pers_name) = lower('".str_replace("'","\'",$this->pers_name)."')"; 
        	$bd = new bdConnect;
        	if ($resultat = $bd->get_data($query))
        	{
          		$this->new_personne($resultat[0]);
          		return true;
        	}
        	return false;
    	}

    	function idExiste()
    	{
        	$query = "select * from personne where pers_id = ".$this->pers_id;
        	$bd = new bdConnect;
        	if ($resultat = $bd->get_data($query))
        	{
          		$this->new_personne($resultat[0]);
          		return true;
        	}
        	return false;
    	}
    	
    	//creer element select pour formulaire
    	function chargeForm($form,$label,$titre,$indice)
    	{

      		$liste = $this->getAll();
      		$array[0] = "";
      		for ($i = 0; $i < count($liste); $i++)
        	{
          		$j = $liste[$i]->pers_id;
          		$array[$j] = $liste[$i]->pers_name;
        	}
        	
        	$boxesNames = "['pi_name_".$indice."','email1_".$indice."','email2_".$indice."','organism_".$indice."']";
        	$columnsNames = "['pers_name','pers_email_1','pers_email_2','org_id']";
        	
        	$s = & $form->createElement('select',$label,$titre,$array,array('onchange' => "fillBoxes('".$label."',".$boxesNames.",'personne',".$columnsNames.");"));

      		return $s;
    	}
    	
    	
 	function insert(& $bd)
    	{
    		
    		//Insertion de l'organisme
    		if ($this->organism->org_id == 0){
    			$this->organism->insert($bd);
    		}
    		    	
			$this->pers_name = ucwords(strtolower($this->pers_name));
	
     	 	$query_insert = "insert into personne (pers_name";
     	 	$query_values =	"values ('".str_replace("'","\'",$this->pers_name)."'";

     	 	if (isset($this->org_id) && !empty($this->org_id) && ($this->org_id != 0) )
     	 	{
     	 		$query_insert .= ",org_id";
     	 		$query_values .= ",".$this->org_id;
     	 	}
     	 	if (isset($this->pers_email_1) && !empty($this->pers_email_1))
     	 	{
     	 		$query_insert .= ",pers_email_1";
     	 		$query_values .= ",'".$this->pers_email_1."'";
     	 	}
     	 	if (isset($this->pers_email_2) && !empty($this->pers_email_2))
     	 	{
     	 		$query_insert .= ",pers_email_2";
     	 		$query_values .= ",'".$this->pers_email_2."'";
     	 	}
     	 	
     	 	$query = $query_insert.") ".$query_values.")";
      		      		
      		$bd->exec($query);
      		$this->pers_id = $bd->getLastId("personne_pers_id_seq");
      		
      		return $this->pers_id;
    	}
 	}
?>
