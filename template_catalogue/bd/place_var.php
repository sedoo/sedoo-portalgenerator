<?php
/*
 * Created on 8 juil. 2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 	require_once("bd/bdConnect.php");
 	require_once("bd/place.php");
 	require_once("bd/variable.php");
 	
 	class place_var
 	{
 		var $var_id;
 		var $place_id;
 		var $variable;
 		var $place;
 		
 		function new_place_var($tab)
 		{
 			$this->place_id = $tab[1];
 			$this->var_id = $tab[0];
 			if (isset($this->place_id) && !empty($this->place_id))
 			{
 				$place = new place;
 				$this->place = $place->getById($this->place_id);
 			}
 			if (isset($this->var_id) && !empty($this->var_id))
 			{
 				$var = new variable;
 				$this->variable = $var->getById($this->var_id);
 			}
 		}
 		
 		function getAll()
 		{
 			$query = "select * from place_var";
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
          			$liste[$i] = new place_var;
          			$liste[$i]->new_place_var($resultat[$i]);
        		}
      		}
      		return $liste;
    	}

    	function existe()
    	{
        	$query = "select * from place_var where " .
        			"place_id = ".$this->place_id." and var_id = ".$this->var_id;
        	//echo $query."<br>";
        	$bd = new bdConnect;
        	if ($resultat = $bd->get_data($query))
        	{
          		$this->new_place_var($resultat[0]);
          		return true;
        	}
        	return false;
    	}
    	
    	function insert()
    	{
     	 	$query = "insert into place_var ('place_id','var_id') " .
     	 			"values (".$this->place_id.",".$this->var_id.")";
      		$bd = new bdConnect;
      		$bd->insert($query);
    	}
 	}
?>
