<?php
/*
 * Created on 8 juil. 2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 	require_once("bd/bdConnect.php");
 	
 	class gcmd_instrument_keyword
 	{
 		var $gcmd_sensor_id;
 		var $gcmd_sensor_name;
 		var $gcm_gcmd_id;
 		var $gcmd_level;
 		var $thesaurus_id;
 		var $uid;
 		
 		var $gcmd_parent;
 		var $enfants;
 		
 		function new_gcmd_instrument_keyword($tab)
 		{
 			$this->gcmd_sensor_id = $tab[0];
 			$this->gcmd_sensor_name = $tab[1];
 			$this->gcm_gcmd_id = $tab[2];
 			$this->gcmd_level = $tab[3];
 			$this->thesaurus_id = $tab[4];
 			$this->uid = $tab[5];
 			
 			if (isset($this->gcm_gcmd_id) && !empty($this->gcm_gcmd_id)){
 				$this->gcmd_parent = $this->getById($this->gcm_gcmd_id);
 			}
 		}
 		
 		function getAll()
 		{
 			$query = "select * from gcmd_instrument_keyword order by gcmd_sensor_name";
      		$bd = new bdConnect;
      		$liste = array();
      		if ($resultat = $bd->get_data($query))
      		{
        		for ($i=0; $i<count($resultat);$i++)
        		{
          			$liste[$i] = new gcmd_instrument_keyword;
          			$liste[$i]->new_gcmd_instrument_keyword($resultat[$i]);
        		}
      		}
      		return $liste;
 		}
 		
 		
 		function getChildren($recursive = false){
 			$liste = array();
 			$this->readChildren($liste, $recursive);
 			return $liste;
 		}
 		
 		
 		private function readChildren(&$liste,$recursive = false){
 			$query = "SELECT * FROM gcmd_instrument_keyword WHERE gcm_gcmd_id = $this->gcmd_sensor_id ORDER BY gcmd_sensor_name";
 			$tmp = $this->getByQuery($query);
 			if ($recursive && isset($tmp)){
 				foreach($tmp as $child){
 					$liste[] = $child;
 					$child->readChildren($liste,$recursive);
 				}
 			}
 		}
 		
 		
 		function getById($id)
    	{
      		if (!isset($id) || empty($id))
        		return new gcmd_instrument_keyword;

      		$query = "select * from gcmd_instrument_keyword where gcmd_sensor_id = ".$id;
      		$bd = new bdConnect;
      		if ($resultat = $bd->get_data($query))
      		{
        		$gcmd_instrument_keyword = new gcmd_instrument_keyword;
        		$gcmd_instrument_keyword->new_gcmd_instrument_keyword($resultat[0]);
      		}
      		      		      		
      		return $gcmd_instrument_keyword;
    	}
    	
    	function getByName($name){
    		$query = "select * from gcmd_instrument_keyword where lower(gcmd_sensor_name) = '".strtolower($name)."'";
    		
    		//echo $query.'<br>';
    		$gcmd_sensor_keyword = null;
      		$bd = new bdConnect;
      		if ($resultat = $bd->get_data($query))
      		{
        		$gcmd_sensor_keyword = new gcmd_instrument_keyword;
        		$gcmd_sensor_keyword->new_gcmd_instrument_keyword($resultat[0]);
      		}
      		return $gcmd_sensor_keyword;
    	}
    	
    	function getByQuery($query)
    	{
      		$bd = new bdConnect;
      		$liste = array();
      		if ($resultat = $bd->get_data($query))
      		{
        		for ($i=0; $i<count($resultat);$i++)
        		{
          			$liste[$i] = new gcmd_instrument_keyword;
          			$liste[$i]->new_gcmd_instrument_keyword($resultat[$i]);
        		}
      		}
      		return $liste;
    	}

    	function existe()
    	{
        	$query = "select * from gcmd_instrument_keyword where " .
        			"lower(gcmd_sensor_name) = lower('".(str_replace("'","\'",$this->gcmd_sensor_name))."')";
        	//echo $query."<br>";
        	$bd = new bdConnect;
        	if ($resultat = $bd->get_data($query))
        	{
          		$this->gcmd_sensor_id = $resultat[0][0];
          		return true;
        	}
        	return false;
    	}

    	function idExiste()
    	{
        	$query = "select * from gcmd_instrument_keyword where gcmd_sensor_id = ".$this->gcmd_sensor_id;
        	//echo $query."<br>";
        	$bd = new bdConnect;
        	if ($resultat = $bd->get_data($query))
        	{
          		$this->gcmd_sensor_name = $resultat[0][1];
          		return true;
        	}
        	return false;
    	}
    	
    	function insert()
    	{
     	 	$query = "insert into gcmd_instrument_keyword ('gcmd_sensor_name') " .
     	 			"values ('".str_replace("'","\'",$this->gcmd_sensor_name)."')";
      		$bd = new bdConnect;
      		$this->gcmd_sensor_id = $bd->insert($query);
    	}

    	//creer element select pour formulaire
    	function chargeForm($form,$label,$titre)
    	{

      		$liste = $this->getAll();
      		$array[0] = "";
      		for ($i = 0; $i < count($liste); $i++)
        	{
          		$j = $liste[$i]->gcmd_sensor_id;
          		$array[$j] = $liste[$i]->gcmd_sensor_name;
          		//echo 'array['.$j.'] = '.$array[$j].'<br>';
        	}
      		$s = & $form->createElement('select',$label,$titre);
      		$s->loadArray($array);
      		return $s;
    	}
    	
    	function chargeFormVadataset($form,$label,$titre)
    	{

      		$liste = $this->getAll();
      		for ($i = 0; $i < count($liste); $i++)
        	{
          		$j = $liste[$i]->gcmd_sensor_id;
          		$array[$j] = $liste[$i]->gcmd_sensor_name;
        	}
      		$s = & $form->createElement('select',$label,$titre);
      		$s->loadArray($array);
      		return $s;
    	}
    	
    	
    	function chargeFormInstr($form, $label, $titre) {
    		$array_topic [0] = "-- Topic --";
    		$array_categorie [0] [0] = "-- Term --";
    		$array_variable [0] [0] [0] = "-- Var_level1 --";
    		 
    		$query = "select * from gcmd_instrument_keyword where gcmd_level = 2 or gcmd_level is null order by gcmd_sensor_name";
    		$liste_topic = $this->getByQuery ( $query );
    		 
    		 
    		for($i = 0; $i < count ( $liste_topic ); $i ++) {
    			$j = $liste_topic [$i]->gcmd_sensor_id;
    			 
    			$array_topic [$j] = $liste_topic [$i]->gcmd_sensor_name;
    			 
    			$query2 = "select * from gcmd_instrument_keyword where gcm_gcmd_id = " . $j . " order by gcmd_sensor_name";
    			$liste_categ = $this->getByQuery ( $query2 );
    			$array_categorie [$j] [0] = "-- Term --";
    			 
    			for($k = 0; $k < count ( $liste_categ ); $k ++) {
    				 
    				$l = $liste_categ [$k]->gcmd_sensor_id;
    				$array_categorie [$j] [$l] = $liste_categ [$k]->gcmd_sensor_name;
    				 
    				$query3 = "select * from gcmd_instrument_keyword where gcm_gcmd_id = " . $l . " order by gcmd_sensor_name";
    				$liste_instr = $this->getByQuery ( $query3 );
    				$array_variable [$j] [$l] [0] = "-- Var_level1 --";
    				for($m = 0; $m < count ( $liste_instr ); $m ++) {
    					$n = $liste_instr [$m]->gcmd_sensor_id;
    					$array_variable [$j] [$l] [$n] = $liste_instr [$m]->gcmd_sensor_name;

    				}
    			}
    		}
    		 
    		$s = & $form->createElement ( 'hierselect', $label, $titre );
    		$s->setOptions ( array (
    				$array_topic,
    				$array_categorie,
    				$array_variable,
    		) );
    		return $s;
    	}
 	}
 	
?>
