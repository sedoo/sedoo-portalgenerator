<?php
/*
 * Created on 8 juil. 2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 	require_once("bd/bdConnect.php");
 	require_once("bd/dataset.php");
 	require_once("bd/place.php");
 	
 	class dats_place
 	{
 		var $dats_id;
 		var $place_id;
 		var $dataset;
 		var $place;
 		
 		function new_dats_place($tab)
 		{
 			$this->dats_id = $tab[0];
 			$this->place_id = $tab[1];
 			if (isset($this->dats_id) && !empty($this->dats_id))
 			{
 				$dts = new dataset;
 				$this->dataset = $dts->getById($this->dats_id);
 			}
 			if (isset($this->place_id) && !empty($this->place_id))
 			{
 				$place = new place;
 				$this->place = $place->getById($this->place_id);
 			}
 		}
 		
 		function getAll()
 		{
 			$query = "select * from dats_place order by dats_id";
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
          			$liste[$i] = new dats_place;
          			$liste[$i]->new_dats_place($resultat[$i]);
        		}
      		}
      		return $liste;
    	}

    	    	
    	function existe()
    	{
        	$query = "select * from dats_place where " .
        			"dats_id = ".$this->dats_id." and place_id = ".$this->place_id;
        	//echo $query."<br>";
        	$bd = new bdConnect;
        	if ($resultat = $bd->get_data($query))
        	{
        		//modif gui : inutile d'initialiser, on cherche juste s'il existe
          		//$this->new_dats_place($resultat[0]);
          		return true;
        	}
        	return false;
    	}
    	
 	function insertOld()
    	{
     	 	$query = "insert into dats_place (dats_id,place_id) " .
     	 			"values (".$this->dats_id.",".$this->place_id.")";
      		$bd = new bdConnect;
      		$bd->insert($query);
    	}
    	 
    	function insert(& $bd)
    	{
    		if (!$this->existe()){
    			$query = "insert into dats_place (dats_id,place_id) " .
     	 			"values (".$this->dats_id.",".$this->place_id.")";

    			$bd->exec($query);
    		}
    	}
 	}
?>
