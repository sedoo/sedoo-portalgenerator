<?php
/*
 * Created on 8 juil. 2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 	require_once("bd/bdConnect.php");
 	require_once("bd/dataset.php");
 	require_once("bd/dataset_type.php");
 	
 	class dats_type
 	{
 		var $dats_id;
 		var $dats_type_id;
 		var $dataset;
 		var $dataset_type;
 		
 		function new_dats_place($tab)
 		{
 			$this->dats_id = $tab[1];
 			$this->dats_type_id = $tab[0];
 			if (isset($this->dats_id) && !empty($this->dats_id))
 			{
 				$dts = new dataset;
 				$this->dataset = $dts->getById($this->dats_id);
 			}
 			if (isset($this->dats_type_id) && !empty($this->dats_type_id))
 			{
 				$dtype = new dataset_type;
 				$this->dataset_type = $dtype->getById($this->dats_type_id);
 			}
 		}
 		
 		function getAll()
 		{
 			$query = "select * from dats_type order by dats_id";
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
          			$liste[$i] = new dats_type;
          			$liste[$i]->new_dats_type($resultat[$i]);
        		}
      		}
      		return $liste;
    	}

    	function existe()
    	{
        	$query = "select * from dats_type where " .
        			"dats_id = ".$this->dats_id." and dats_type_id = ".$this->dats_type_id;
        	//echo $query."<br>";
        	$bd = new bdConnect;
        	if ($resultat = $bd->get_data($query))
        	{
          		$this->new_dats_type($resultat[0]);
          		return true;
        	}
        	return false;
    	}
    	
    	function insert(& $bd)
    	{
     	 	$query = "insert into dats_type (dats_id,dats_type_id) " .
     	 			"values (".$this->dats_id.",".$this->dats_type_id.")";
      		$bd->exec($query);
    	}
 	}
?>
