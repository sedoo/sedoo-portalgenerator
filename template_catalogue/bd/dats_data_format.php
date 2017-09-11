<?php
/*
 * Created on 8 juil. 2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 	require_once("bd/bdConnect.php");
 	require_once("bd/dataset.php");
 	require_once("bd/data_format.php");
 	
 	class dats_data_format
 	{
 		var $dats_id;
 		var $data_format_id;
 		var $dataset;
 		var $data_format;
 		
 		
 		function new_dats_data_format($tab)
 		{
 			$this->dats_id = $tab[0];
 			$this->data_format_id = $tab[1];
 			 			
 			if (isset($this->dats_id) && !empty($this->dats_id))
 			{
 				$dts = new dataset;
 				$this->dataset = $dts->getById($this->dats_id);
 			}
 			if (isset($this->data_format_id) && !empty($this->data_format_id))
 			{
 				$format = new data_format;
 				$this->data_format = $format->getById($this->data_format_id);
 			}
 			
 		}
 		
 		function getAll($table = 'dats_data_format')
 		{
 			$query = "select * from ".$table." order by dats_id";
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
          			$liste[$i] = new dats_data_format;
          			$liste[$i]->new_dats_data_format($resultat[$i]);
        		}
      		}
      		return $liste;
    	}

    	function existe($table = 'dats_data_format')
    	{
        	$query = "select * from $table where " .
        			"dats_id = ".$this->dats_id." and data_format_id = ".$this->data_format_id;
        	$bd = new bdConnect;
        	if ($resultat = $bd->get_data($query))
        	{
          		$this->new_dats_data_format($resultat[0]);
          		return true;
        	}
        	return false;
    	}
    	
    	function insert(& $bd, $table = 'dats_data_format')
    	{
    		if (!$this->existe($table)){
    			$query = "insert into ".$table." (dats_id,data_format_id) " .
     	 			"values (".$this->dats_id.",".$this->data_format_id.")";

    		    			
    			$bd->exec($query);
    		}
    	}
    	 
    	
 	}
?>
