<?php
/*
 * Created on 8 juil. 2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 	require_once("bd/bdConnect.php");
 	
 	class status_final
 	{
 		var $status_final_id;
 		var $status_final_name;
 		
 		function new_status_final($tab)
 		{
 			$this->status_final_id = $tab[0];
 			$this->status_final_name = $tab[1];
 		}
 		
 		function getAll()
 		{
 			$query = "select * from status_final order by status_final_name";
      		$bd = new bdConnect;
      		$liste = array();
      		if ($resultat = $bd->get_data($query))
      		{
        		for ($i=0; $i<count($resultat);$i++)
        		{
          			$liste[$i] = new status_final;
          			$liste[$i]->new_status_final($resultat[$i]);
        		}
      		}
      		return $liste;
 		}
 		
 		function getById($id)
    	{
      		if (!isset($id) || empty($id))
        		return new status_final;

      		$query = "select * from status_final where status_final_id = ".$id;
      		$bd = new bdConnect;
      		if ($resultat = $bd->get_data($query))
      		{
        		$status_final = new status_final;
        		$status_final->new_status_final($resultat[0]);
      		}
      		return $status_final;
    	}
    	
    	function getByQuery($query)
    	{
      		$bd = new bdConnect;
      		$liste = array();
      		if ($resultat = $bd->get_data($query))
      		{
        		for ($i=0; $i<count($resultat);$i++)
        		{
          			$liste[$i] = new status_final;
          			$liste[$i]->new_status_final($resultat[$i]);
        		}
      		}
      		return $liste;
    	}

    	function existe()
    	{
        	$query = "select * from status_final where " .
        			"lower(status_final_name) = lower('".(str_replace("'","\'",$this->status_final_name))."')";
        	//echo $query."<br>";
        	$bd = new bdConnect;
        	if ($resultat = $bd->get_data($query))
        	{
          		$this->status_final_id = $resultat[0][0];
          		return true;
        	}
        	return false;
    	}

    	function idExiste()
    	{
        	$query = "select * from status_final where status_final_id = ".$this->status_final_id;
        	//echo $query."<br>";
        	$bd = new bdConnect;
        	if ($resultat = $bd->get_data($query))
        	{
          		$this->status_final_name = $resultat[0][1];
          		return true;
        	}
        	return false;
    	}
    	
    	function insert()
    	{
     	 	$query = "insert into status_final ('status_final_name') " .
     	 			"values ('".str_replace("'","\'",$this->status_final_name)."')";
      		$bd = new bdConnect;
      		$this->status_final_id = $bd->insert($query);
    	}

    	//creer element select pour formulaire
    	function chargeForm($form,$label,$titre)
    	{

      		$liste = $this->getAll();
      		$array[0] = "";
      		for ($i = 0; $i < count($liste); $i++)
        	{
          		$j = $liste[$i]->status_final_id;
          		$array[$j] = $liste[$i]->status_final_name;
          		//echo 'array['.$j.'] = '.$array[$j].'<br>';
        	}
      		$s = & $form->createElement('select',$label,$titre);
      		$s->loadArray($array);
      		return $s;
    	}
 		
 	}
?>
