<?php
/*
 * Created on 8 juil. 2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 	require_once("bd/bdConnect.php");
 	require_once("bd/dataset.php");
 	require_once("bd/personne.php");
 	require_once("bd/contact_type.php");
 	
 	class dats_originator
 	{
 		var $dats_id;
 		var $pers_id;
 		//var $seeker;
		var $contact_type_id;
 		var $dataset;
 		var $personne;
 		var $contact_type;
 		
 		function new_dats_originator($tab)
 		{
 			$this->dats_id = $tab[0];
 			$this->pers_id = $tab[1];
 			//$this->seeker = $tab[2];
 			$this->contact_type_id = $tab[2];
 			/*if (isset($this->dats_id) && !empty($this->dats_id))
 			{
 				$dts = new dataset;
 				$this->dataset = $dts->getById($this->dats_id);
 			}*/
 			if (isset($this->pers_id) && !empty($this->pers_id))
 			{
 				$pers = new personne;
 				$this->personne = $pers->getById($this->pers_id);
 			}
 			if (isset($this->contact_type_id) && !empty($this->contact_type_id))
 			{
 				$ct = new contact_type;
 				$this->contact_type = $ct->getById($this->contact_type_id);
 			}
 		}
 		
 		function getAll()
 		{
 			$query = "select * from dats_originators order by dats_id";
      		return $this->getByQuery($query);
 		}
 		
    	
 		function getByDataset($datsId){
			return $this->getByQuery("select dats_originators.* from dats_originators join personne using (pers_id) where dats_id = $datsId order by contact_type_id,pers_name;");
// 			 return $this->getByQuery("select * from dats_originators where dats_id = $datsId");	
 		}
 		
    	function getByQuery($query)
    	{
      		$bd = new bdConnect;
      		$liste = array();
      		if ($resultat = $bd->get_data($query))
      		{
        		for ($i=0; $i<count($resultat);$i++)
        		{
          			$liste[$i] = new dats_originator;
          			$liste[$i]->new_dats_originator($resultat[$i]);
        		}
      		}
      		return $liste;
    	}

    	function existe()
    	{
    		   		
        	$query = "select * from dats_originators where " .
        			"dats_id = ".$this->dats_id." and pers_id = ".$this->pers_id;
        	//echo $query."<br>";
        	$bd = new bdConnect;
        	if ($resultat = $bd->get_data($query))
        	{
          		$this->new_dats_originator($resultat[0]);
          		return true;
        	}
        	return false;
    	}
    	
    	function insert(& $bd)
    	{
    		if (!$this->existe()){
    			
    			$query_insert = "insert into dats_originators (dats_id,pers_id,contact_type_id)";
     	 		$query_values =	"values (".$this->dats_id.",".$this->pers_id.",".$this->contact_type_id.')';
    			
    			//$query = "insert into dats_originators (dats_id,pers_id,seeker) " .
     	 		//	"values (".$this->dats_id.",".$this->pers_id.",".$this->seeker.")";
    			
    			/*if (isset($this->seeker) && !empty($this->seeker))
    			{
    				$query_insert .= ",seeker";
    				$query_values .= ",'".$this->seeker."'";
    			}*/
    			
    			//$query = $query_insert.") ".$query_values.")";
    			$query = $query_insert." ".$query_values;
    			$bd->exec($query);
    		}
    	}
    	
    	
 	}
?>
