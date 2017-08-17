<?php
/*
 * Created on 8 juil. 2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 	require_once("bd/bdConnect.php");
 
 	class country
 	{
 		var $country_id;
 		var $country_name;
 		
 		function new_country($tab)
 		{
 			$this->country_id = $tab[0];
 			$this->country_name = $tab[1];
 		}
 		
 		function getAll()
 		{
 			$query = "select * from country order by country_name";
      		$bd = new bdConnect;
      		$liste = array();
      		if ($resultat = $bd->get_data($query))
      		{
        		for ($i=0; $i<count($resultat);$i++)
        		{
          			$liste[$i] = new country;
          			$liste[$i]->new_country($resultat[$i]);
        		}
      		}
      		return $liste;
 		}
 		
 		function getById($id)
    	{
      		if (!isset($id) || empty($id))
        		return new country;

      		$query = "select * from country where country_id = ".$id;
      		$bd = new bdConnect;
      		if ($resultat = $bd->get_data($query))
      		{
        		$country = new country;
        		$country->new_country($resultat[0]);
      		}
      		return $country;
    	}
    	
    	function getByQuery($query)
    	{
      		$bd = new bdConnect;
      		$liste = array();
      		if ($resultat = $bd->get_data($query))
      		{
        		for ($i=0; $i<count($resultat);$i++)
        		{
          			$liste[$i] = new country;
          			$liste[$i]->new_country($resultat[$i]);
        		}
      		}
      		return $liste;
    	}

    	function existe()
    	{
        	$query = "select * from country where " .
        			"lower(country_name) = lower('".(str_replace("'","\'",$this->country_name))."')";
        	//echo $query."<br>";
        	$bd = new bdConnect;
        	if ($resultat = $bd->get_data($query))
        	{
          		$this->country_id = $resultat[0][0];
          		return true;
        	}
        	return false;
    	}

    	function idExiste()
    	{
        	$query = "select * from country where country_id = ".$this->country_id;
        	//echo $query."<br>";
        	$bd = new bdConnect;
        	if ($resultat = $bd->get_data($query))
        	{
          		$this->country_name = $resultat[0][1];
          		return true;
        	}
        	return false;
    	}
    	
    	function insert()
    	{
     	 	$query = "insert into country ('country_name') " .
     	 			"values ('".str_replace("'","\'",$this->country_name)."')";
      		$bd = new bdConnect;
      		$this->country_id = $bd->insert($query);
    	}

    	//creer element select pour formulaire
    	function chargeForm($form,$label,$titre)
    	{

      		$liste = $this->getAll();
      		$array[0] = "";
      		for ($i = 0; $i < count($liste); $i++)
        	{
          		$j = $liste[$i]->country_id;
          		$array[$j] = $liste[$i]->country_name;
          		//echo 'array['.$j.'] = '.$array[$j].'<br>';
        	}
      		$s = & $form->createElement('select',$label,$titre);
      		$s->loadArray($array);
      		return $s;
    	}
 	}
?>
