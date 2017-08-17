<?php
/*
 * Created on 8 juil. 2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 	require_once("bd/bdConnect.php");
 	
 	class manufacturer
 	{
 		var $manufacturer_id;
 		var $manufacturer_name;
 		var $manufacturer_url;
 		
 		function new_manufacturer($tab)
 		{
 			$this->manufacturer_id = $tab[0];
 			$this->manufacturer_name = $tab[1];
 			$this->manufacturer_url = $tab[2];
 		}
 		
 		function toString(){
 			return $this->manufacturer_name.(($this->manufacturer_url)?', url: '.$this->manufacturer_url:'');
 		}
 		
 		function getAll()
 		{
 			$query = 'select * from manufacturer where manufacturer_id <= '.MANUFACTURER_MAX_ID.' and manufacturer_id not in ('.MANUFACTURER_EXCLUDE.') order by manufacturer_name';
      		$bd = new bdConnect;
      		$liste = array();
      		if ($resultat = $bd->get_data($query))
      		{
        		for ($i=0; $i<count($resultat);$i++)
        		{
          			$liste[$i] = new manufacturer;
          			$liste[$i]->new_manufacturer($resultat[$i]);
        		}
      		}
      		return $liste;
 		}
 		
 		function getById($id)
    	{
      		if (!isset($id) || empty($id))
        		return new manufacturer;

      		$query = "select * from manufacturer where manufacturer_id = ".$id;
      		$bd = new bdConnect;
      		if ($resultat = $bd->get_data($query))
      		{
        		$manufacturer = new manufacturer;
        		$manufacturer->new_manufacturer($resultat[0]);
      		}
      		return $manufacturer;
    	}
    	
    	function getByQuery($query)
    	{
      		$bd = new bdConnect;
      		$liste = array();
      		if ($resultat = $bd->get_data($query))
      		{
        		for ($i=0; $i<count($resultat);$i++)
        		{
          			$liste[$i] = new manufacturer;
          			$liste[$i]->new_manufacturer($resultat[$i]);
        		}
      		}
      		return $liste;
    	}

    	function existe()
    	{
        	$query = "select * from manufacturer where " .
        			"lower(manufacturer_name) = lower('".(str_replace("'","\'",$this->manufacturer_name))."')";
        	//echo $query."<br>";
        	$bd = new bdConnect;
        	if ($resultat = $bd->get_data($query))
        	{
          		$this->manufacturer_id = $resultat[0][0];
          		return true;
        	}
        	return false;
    	}

    	function idExiste()
    	{
        	$query = "select * from manufacturer where manufacturer_id = ".$this->manufacturer_id;
        	//echo $query."<br>";
        	$bd = new bdConnect;
        	if ($resultat = $bd->get_data($query))
        	{
          		$this->manufacturer_name = $resultat[0][1];
          		return true;
        	}
        	return false;
    	}
    	
    	function insert(& $bd)
    	{
    		if (!$this->existe()){
    		
    			$query_insert = "insert into manufacturer (manufacturer_name";
    			$query_values = "values ('".str_replace("'","\'",$this->manufacturer_name)."'";
    				

    			if (isset($this->manufacturer_url) && !empty($this->manufacturer_url))
    			{
    				$query_insert .= ",manufacturer_url";
    				$query_values .= ",'".str_replace("'","\'",$this->manufacturer_url)."'";
    			}

    			$query = $query_insert.") ".$query_values.")";
    			$bd->exec($query);


    			$this->manufacturer_id = $bd->getLastId('manufacturer_manufacturer_id_seq');
    		}
    		return $this->manufacturer_id;
    	}
    	 
 	
    	function insertOld()
    	{
    		$query_insert = "insert into manufacturer (manufacturer_name";
     	 	$query_values = "values ('".str_replace("'","\'",$this->manufacturer_name)."')";
    		   		
      		
    		if (isset($this->manufacturer_url) && !empty($this->manufacturer_url))
     	 	{
     	 		$query_insert .= ",manufacturer_url";
     	 		$query_values .= ",'".str_replace("'","\'",$this->manufacturer_url)."'";
     	 	}
      		
     	 	$bd = new bdConnect;
      		$bd->insert($query);
     	 	$query = $query_insert.") ".$query_values.")";
     	 	
      		$this->manufacturer_id = $bd->getLastId('manufacturer_manufacturer_id_seq');
      		return $this->manufacturer_id;
      		
    	}

    	//creer element select pour formulaire
    	function chargeForm($form,$label,$titre,$suffix = '')
    	{

      		$liste = $this->getAll();
      		$array[0] = "";
      		for ($i = 0; $i < count($liste); $i++)
        	{
          		$j = $liste[$i]->manufacturer_id;
          		$array[$j] = $liste[$i]->manufacturer_name;
          		//echo 'array['.$j.'] = '.$array[$j].'<br>';
        	}
        	        	        	
        	$boxesNames = "['new_manufacturer".$suffix."','new_manufacturer_url".$suffix."']";
        	$columnsNames = "['manufacturer_name','manufacturer_url']";
        	
        	$s = & $form->createElement('select',$label,$titre,$array,array('onchange' => "fillBoxes('".$label."',".$boxesNames.",'manufacturer',".$columnsNames.");"));
   
        	
        	//$s = & $form->createElement('select',$label,$titre,$array,array('onchange' => "fillBox('".$label."','new_manufacturer','manufacturer','manufacturer_name');"));
        	/*
      		$s = & $form->createElement('select',$label,$titre);
      		$s->loadArray($array);*/
      		return $s;
    	}
 	}
?>
