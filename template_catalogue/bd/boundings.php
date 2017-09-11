<?php
/*
 * Created on 8 juil. 2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 	require_once ("bd/bdConnect.php");
 	 	
 	class boundings
 	{
 		var $bound_id;
 		var $west_bounding_coord;
 		var $east_bounding_coord;
 		var $north_bounding_coord;
 		var $south_bounding_coord;
 		 		
 		function new_boundings($tab){
 			$this->bound_id = $tab[0];
 			$this->west_bounding_coord = intCoord2double($tab[1]);
 			$this->east_bounding_coord = intCoord2double($tab[2]);
 			$this->north_bounding_coord = intCoord2double($tab[3]);
 			$this->south_bounding_coord = intCoord2double($tab[4]);
 		}
 		
 		function toString(){
 			return 'west: '.$this->west_bounding_coord.', east: '.$this->east_bounding_coord.', north: '.$this->north_bounding_coord.', south: '.$this->south_bounding_coord;
 		}
 		
 		function getAll()
 		{
 			$query = "select * from boundings";
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
          			$liste[$i] = new boundings;
          			$liste[$i]->new_boundings($resultat[$i]);
        		}
      		}
      		return $liste;
    	}
    	
    	function getById($id)
    	{
      		if (!isset($id) || empty($id))
        		return new status_final;

      		$query = "select * from boundings where bound_id = ".$id;
      		$bd = new bdConnect;
      		if ($resultat = $bd->get_data($query))
      		{
        		$boundings = new boundings;
        		$boundings->new_boundings($resultat[0]);
      		}
      		return $boundings;
    	}
    	
    	function existe()
    	{
        	$query = "select * from boundings where " .
        			"west_bounding_coord = ".doubleCoord2int($this->west_bounding_coord)." and " .
        					"east_bounding_coord = ".doubleCoord2int($this->east_bounding_coord)." and " .
        							"north_bounding_coord = ".doubleCoord2int($this->north_bounding_coord)." and " .
        									"south_bounding_coord = ".doubleCoord2int($this->south_bounding_coord);
        	$bd = new bdConnect;
        	if ($resultat = $bd->get_data($query))
        	{
          		$this->bound_id = $resultat[0][0];
          		return true;
        	}
        	return false;
    	}

    	function idExiste()
    	{
        	$query = "select * from boundings where bound_id = ".$this->bound_id;
        	$bd = new bdConnect;
        	if ($resultat = $bd->get_data($query))
        	{
          		$this->west_bounding_coord = $resultat[0][1];
          		$this->east_bounding_coord = $resultat[0][2];
          		$this->north_bounding_coord = $resultat[0][3];
          		$this->south_bounding_coord = $resultat[0][4];
          		return true;
        	}
        	return false;
    	}
    	
    	function insert(& $bd)
    	{
    		if (!$this->existe()){
    			$query = "insert into boundings (west_bounding_coord,east_bounding_coord," .
     	 			"north_bounding_coord,south_bounding_coord) " .
     	 			"values (".doubleCoord2int($this->west_bounding_coord).",".doubleCoord2int($this->east_bounding_coord)."," .
    			doubleCoord2int($this->north_bounding_coord).",".doubleCoord2int($this->south_bounding_coord).")";
    			    			    			
    			$bd->exec($query);
				
    			$this->bound_id = $bd->getLastId('boundings_bound_id_seq');
    			
    		}
    		return $this->bound_id;
    	}
    	
 	function insertOld()
    	{
    		if (!$this->existe()){
    			$query = "insert into boundings (west_bounding_coord,east_bounding_coord," .
     	 			"north_bounding_coord,south_bounding_coord) " .
     	 			"values (".doubleCoord2int($this->west_bounding_coord).",".doubleCoord2int($this->east_bounding_coord)."," .
    			doubleCoord2int($this->north_bounding_coord).",".doubleCoord2int($this->south_bounding_coord).")";
    			    			
    			$bd = new bdConnect;
    			$bd->insert($query);
				
    			$this->bound_id = $bd->getLastId('boundings_bound_id_seq');
    			
    		}
    		return $this->bound_id;
    	}
 		
 	}
?>
