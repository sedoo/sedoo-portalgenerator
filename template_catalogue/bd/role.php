<?php
/*
 * Created on 8 juil. 2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 	require_once("bd/bdConnect.php");
 	
 	class role
 	{
 		var $role_id;
 		var $role_name;
 		
 		function new_role($tab)
 		{
 			$this->role_id = $tab[0];
 			$this->role_name = $tab[1];
 		}
 		
 		function getAll()
 		{
 			$query = "select * from role order by role_name";
      		$bd = new bdConnect;
      		$liste = array();
      		if ($resultat = $bd->get_data($query))
      		{
        		for ($i=0; $i<count($resultat);$i++)
        		{
          			$liste[$i] = new role;
          			$liste[$i]->new_role($resultat[$i]);
        		}
      		}
      		return $liste;
 		}
 		
 		function getById($id)
    	{
      		if (!isset($id) || empty($id))
        		return new role;

      		$query = "select * from role where role_id = ".$id;
      		$bd = new bdConnect;
      		if ($resultat = $bd->get_data($query))
      		{
        		$role = new role;
        		$role->new_role($resultat[0]);
      		}
      		return $role;
    	}
    	
    	function getByQuery($query)
    	{
      		$bd = new bdConnect;
      		$liste = array();
      		if ($resultat = $bd->get_data($query))
      		{
        		for ($i=0; $i<count($resultat);$i++)
        		{
          			$liste[$i] = new role;
          			$liste[$i]->new_role($resultat[$i]);
        		}
      		}
      		return $liste;
    	}

    	function existe()
    	{
        	$query = "select * from role where " .
        			"lower(role_name) = lower('".(str_replace("'","\'",$this->role_name))."')";
        	$bd = new bdConnect;
        	if ($resultat = $bd->get_data($query))
        	{
          		$this->role_id = $resultat[0][0];
          		return true;
        	}
        	return false;
    	}

    	function idExiste()
    	{
        	$query = "select * from role where role_id = ".$this->role_id;
        	$bd = new bdConnect;
        	if ($resultat = $bd->get_data($query))
        	{
          		$this->role_name = $resultat[0][1];
          		return true;
        	}
        	return false;
    	}
    	
    	function insert()
    	{
     	 	$query = "insert into role ('role_name') " .
     	 			"values ('".str_replace("'","\'",$this->role_name)."')";
      		$bd = new bdConnect;
      		$this->role_id = $bd->insert($query);
    	}

    	//creer element select pour formulaire
    	function chargeForm($form,$label,$titre){

      		$liste = $this->getAll();
      		$array[0] = "";
      		for ($i = 0; $i < count($liste); $i++)
        	{
          		$j = $liste[$i]->role_id;
          		$array[$j] = $liste[$i]->role_name;
        	}
      		$s = & $form->createElement('select',$label,$titre);
      		$s->loadArray($array);
      		return $s;
    	}
 }
?>
