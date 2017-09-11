<?php
/*
 * Created on 15 juil. 2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

 require_once("bd/bdConnect.php");
 
  	class data_policy
	{
		var $data_policy_id;
		var $data_policy_name;
		
		function new_data_policy($tab)
		{
			$this->data_policy_id = $tab[0];
			$this->data_policy_name = $tab[1];
		}
		
		function getAll()
 		{
 			$query = "select * from data_policy order by data_policy_name";
      		$bd = new bdConnect;
      		$liste = array();
      		if ($resultat = $bd->get_data($query))
      		{
        		for ($i=0; $i<count($resultat);$i++)
        		{
          			$liste[$i] = new data_policy;
          			$liste[$i]->new_data_policy($resultat[$i]);
        		}
      		}
      		return $liste;
 		}
 				
 		function getById($id)
    	{
      		if (!isset($id) || empty($id))
        		return new data_policy;

      		$query = "select * from data_policy where data_policy_id = ".$id;
      		$bd = new bdConnect;
      		if ($resultat = $bd->get_data($query))
      		{
        		$per = new data_policy;
        		$per->new_data_policy($resultat[0]);
      		}
      		return $per;
    	}
    	
    	function getByQuery($query)
    	{
      		$bd = new bdConnect;
      		$liste = array();
      		if ($resultat = $bd->get_data($query))
      		{
        		for ($i=0; $i<count($resultat);$i++)
        		{
          			$liste[$i] = new data_policy;
          			$liste[$i]->new_data_policy($resultat[$i]);
        		}
      		}
      		return $liste;
    	}

    	function existe()
    	{
        	$query = "select * from data_policy where " .
        			"lower(data_policy_name) = lower('".(str_replace("'","\'",$this->data_policy_name))."')";
        	$bd = new bdConnect;
        	if ($resultat = $bd->get_data($query))
        	{
          		$this->data_policy_id = $resultat[0][0];
          		return true;
        	}
        	return false;
    	}

    	function insert(& $bd)
    	{
    		if (!$this->existe()){
    			$query = "insert into data_policy (data_policy_name) values ('".str_replace("'","\'",$this->data_policy_name)."')";
    			$bd->exec($query);
    			$this->data_policy_id = $bd->getLastId("data_policy_data_policy_id_seq");
    		}
    		return $this->data_policy_id;
    	}
    	 
		function insertOld()
    	{
     	 	$query = "insert into data_policy (data_policy_name) values ('".str_replace("'","\'",$this->data_policy_name)."')";
     	 	     	 	     	 	     	 
     	 	$bd = new bdConnect;
      		$bd->insert($query);
      		$this->data_policy_id = $bd->getLastId("data_policy_data_policy_id_seq");
      		
      		return $this->data_policy_id;
    	}
    	
    	function idExiste()
    	{
        	$query = "select * from data_policy where data_policy_id = ".$this->data_policy_id;
        	$bd = new bdConnect;
        	if ($resultat = $bd->get_data($query))
        	{
          		$this->data_policy_name = $resultat[0][1];
          		return true;
        	}
        	return false;
    	}
    	
    	//creer element select pour formulaire
    	function chargeForm($form,$label,$titre)
    	{

      		$liste = $this->getAll();
      		$array[0] = "";
      		for ($i = 0; $i < count($liste); $i++)
        	{
          		$j = $liste[$i]->data_policy_id;
          		$array[$j] = $liste[$i]->data_policy_name;
        	}
        	$s = & $form->createElement('select',$label,$titre,$array,array('onchange' => "fillBox('".$label."','new_data_policy','data_policy','data_policy_name');"));
      		return $s;
    	}
	}
?>
