<?php
/*
 * Created on 8 juil. 2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 	require_once("bd/bdConnect.php");
 	require_once("bd/dataset.php");
 	require_once("bd/project.php");
 	
 	class dats_proj
 	{
 		var $dats_id;
 		var $project_id;
 		var $dataset;
 		var $project;
 		
 		function new_dats_proj($tab)
 		{
 			$this->dats_id = $tab[1];
 			$this->project_id = $tab[0];
 			if (isset($this->dats_id) && !empty($this->dats_id))
 			{
 				$dts = new dataset;
 				$this->dataset = $dts->getById($this->dats_id);
 			}
 			if (isset($this->project_id) && !empty($this->project_id))
 			{
 				$project = new project;
 				$this->project = $project->getById($this->project_id);
 			}
 		}
 		
 		function getAll()
 		{
 			$query = "select * from dats_proj order by dats_id";
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
          			$liste[$i] = new dats_proj;
          			$liste[$i]->new_dats_proj($resultat[$i]);
        		}
      		}
      		return $liste;
    	}

    	function existe()
    	{
        	$query = "select * from dats_proj where " .
        			"dats_id = ".$this->dats_id." and project_id = ".$this->project_id;
        	$bd = new bdConnect;
        	if ($resultat = $bd->get_data($query))
        	{
          		$this->new_dats_proj($resultat[0]);
          		return true;
        	}
        	return false;
    	}
    	
 		function insert(& $bd)
    	{
    		if (!$this->existe()){
    			$query = "insert into dats_proj (dats_id,project_id) " .
     	 			"values (".$this->dats_id.",".$this->project_id.")";

    			$bd->exec($query);
    		}
    	}
    	
    	function insertOld()
    	{
     	 	$query = "insert into dats_proj (dats_id,project_id) " .
     	 			"values (".$this->dats_id.",".$this->project_id.")";
      		$bd = new bdConnect;
      		$bd->insert($query);
    	}
 	}
?>
