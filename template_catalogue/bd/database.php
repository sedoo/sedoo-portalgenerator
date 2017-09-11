<?php
/*
 * Created on 15 juil. 2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

require_once("bd/bdConnect.php");
 
 	class database
	{
		var $database_id;
		var $database_name;
		var $database_url;
		
		function new_database($tab)	{
			$this->database_id = $tab[0];
			$this->database_name = $tab[1];
			$this->database_url = $tab[2];
		}
		
		function toString() {
			return $this->database_name.(($this->database_url)?',url: '.$this->database_url:'');
		}
		
		function insertOld() {
     	 	$query_insert = "INSERT INTO database (database_name";
     	 	$query_values =	"VALUES ('".str_replace("'","\'",$this->database_name)."'";
     	 	
     	 	if (isset($this->database_url) && !empty($this->database_url))
     	 	{
     	 		$query_insert .= ",database_url";
     	 		$query_values .= ",'".$this->database_url."'";
     	 	}
     	 	     	 
     	 	$query = $query_insert.") ".$query_values.")";
      		$bd = new bdConnect;
      		$bd->insert($query);
      		$this->database_id = $bd->getLastId("database_database_id_seq");
      		
      		return $this->database_id;
    	}
		
    	function insert(& $bd) {
    		if (!$this->existe()){
    			$query_insert = "INSERT INTO database (database_name";
    			$query_values =	"VALUES ('".str_replace("'","\'",$this->database_name)."'";

    			if (isset($this->database_url) && !empty($this->database_url))
    			{
    				$query_insert .= ",database_url";
    				$query_values .= ",'".$this->database_url."'";
    			}
     	 	
    			$query = $query_insert.") ".$query_values.")";

    			$bd->exec($query);
    			$this->database_id = $bd->getLastId("database_database_id_seq");
    		}
    		return $this->database_id;
    	}

		function getAll() {
 			$query = "SELECT * FROM database ORDER BY database_name";
      		$bd = new bdConnect;
      		$liste = array();
      		if ($resultat = $bd->get_data($query))
      		{
        		for ($i=0; $i<count($resultat);$i++)
        		{
          			$liste[$i] = new database;
          			$liste[$i]->new_database($resultat[$i]);
        		}
      		}
      		return $liste;
 		}
 		
 		function getById($id) {
      		if (!isset($id) || empty($id))
        		return new database;

      		$query = "SELECT * FROM database WHERE database_id = ".$id;
      		$bd = new bdConnect;
      		if ($resultat = $bd->get_data($query))
      		{
        		$per = new database;
        		$per->new_database($resultat[0]);
      		}
      		return $per;
    	}
    
		function getByDatsId($datsId) {
			$liste = $this->getByQuery("SELECT database.* FROM dataset JOIN database using (database_id) WHERE dats_id = $datsId");
			if (empty($liste)){
				return null;
			}else{
				return $liste[0];
			}
		}
		
		function getByQuery($query) {
			$bd = new bdConnect;
			$liste = array();
			if ($resultat = $bd->get_data($query))
			{
				for ($i=0; $i<count($resultat);$i++)
				{
					$liste[$i] = new database;
					$liste[$i]->new_database($resultat[$i]);
				}
			}
			return $liste;
		}

		function existe() {
			$query = "select * from database where " .
					"lower(database_name) = lower('".(str_replace("'","\'",$this->database_name))."')";
			$bd = new bdConnect;
			if ($resultat = $bd->get_data($query))
			{
				$this->database_id = $resultat[0][0];
				return true;
			}
			return false;
		}

		function idExiste() {
			$query = "select * from database where database_id = ".$this->database_id;
			$bd = new bdConnect;
			if ($resultat = $bd->get_data($query))
			{
				$this->database_name = $resultat[0][1];
				return true;
			}
			return false;
		}
			
		//creer element select pour formulaire
		function chargeForm($form,$label,$titre) {

			$liste = $this->getAll();
			$array[0] = "";
			for ($i = 0; $i < count($liste); $i++)
			{
				$j = $liste[$i]->database_id;
				$array[$j] = $liste[$i]->database_name;
			}
			
			$s = & $form->createElement('select',$label,$titre,$array,array('onchange' => "fillBoxes('".$label."',['new_database','new_db_url'],'database',['database_name','database_url']);"));
			
			return $s;
		}
	}
?>
