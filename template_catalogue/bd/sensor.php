<?php
/*
 * Created on 8 juil. 2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 	require_once("bd/bdConnect.php");
 	require_once("bd/manufacturer.php");
 	require_once("bd/gcmd_instrument_keyword.php");
 	require_once("bd/boundings.php");
 	require_once("scripts/common.php");
 	
 	class sensor
 	{
 		var $sensor_id;
 		var $manufacturer_id;
 		var $gcmd_sensor_id;
 		var $bound_id;
 		var $sensor_model;
 		var $sensor_calibration;
 		var $sensor_date_begin;
 		var $sensor_date_end;
 		var $sensor_url;
 		var $sensor_elevation;
 		var $manufacturer;
 		var $gcmd_instrument_keyword;
 		var $boundings;
 		var $sensor_places;
 		var $sensor_vars;
 		var $sensor_environment;
 		 		
 		function new_sensor($tab)
 		{
 			$this->sensor_id = $tab[0];
 			$this->manufacturer_id = $tab[1];
 			$this->gcmd_sensor_id = $tab[2];
 			$this->bound_id = $tab[3];
 			$this->sensor_model = $tab[4];
 			$this->sensor_calibration = $tab[5];
 			$this->sensor_date_begin = $tab[6];
 			$this->sensor_date_end = $tab[7];
 			$this->sensor_url = $tab[8];
 			$this->sensor_elevation = intAlt2double($tab[9]);
 			
 			if (isset($this->manufacturer_id) && !empty($this->manufacturer_id))
 			{
 				$man = new manufacturer;
 				$this->manufacturer = $man->getById($this->manufacturer_id);
 			}
 			if (isset($this->gcmd_sensor_id) && !empty($this->gcmd_sensor_id))
 			{
 				$gcmd = new gcmd_instrument_keyword;
 				$this->gcmd_instrument_keyword = $gcmd->getById($this->gcmd_sensor_id);
 			}
 			if (isset($this->bound_id) && !empty($this->bound_id))
 			{
 				$bound = new boundings;
 				$this->boundings = $bound->getById($this->bound_id);
 			}
 		}
 		
 		function toString(){
 			$result = 'GCMD: ';
 			if (isset($this->gcmd_instrument_keyword) ){
     	 		$result .= $this->gcmd_instrument_keyword->gcmd_sensor_name;
     	 	}else
     	 		$result .= $this->gcmd_sensor_id;
    	 		
     	 	if (isset($this->manufacturer)){
     	 		$result .= "\nManufacturer: ".$this->manufacturer->toString();
     	 	}
     	 	if (isset($this->sensor_model) && !empty($this->sensor_model)){
     	 		$result .= "\nModel: ".$this->sensor_model;
     	 	}
     	 	     	 	
     	 	if (isset($this->sensor_calibration) && !empty($this->sensor_calibration)){
     	 		$result .= "\nCalibration: ".$this->sensor_calibration;
     	 	}
     	 	if (isset($this->sensor_url) && !empty($this->sensor_url)){
     	 		$result .= "\nURL: ".$this->sensor_url;
     	 	}
 			if (isset($this->sensor_date_begin) && !empty($this->sensor_date_begin)){
     	 		$result .= "\nDate beginl: ".$this->sensor_date_begin;
     	 	}
     	 	if (isset($this->sensor_date_end) && !empty($this->sensor_date_end)){
     	 		$result .= "\nDate end: ".$this->sensor_date_end;
     	 	}
 			if (isset($this->boundings)){
     	 		$result .= "\nCoordinates: ".$this->boundings->toString();
     	 	}
     	 	if (isset($this->sensor_elevation) && strlen($this->sensor_elevation) > 0){
     	 		$result .= "\nHeight: ".doubleAlt2int($this->sensor_elevation);
     	 	}
 			
 			return $result;
 		}
 		
 		function getAll()
		{
			$query = "select * from sensor order by sensor_model";
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
          			$liste[$i] = new sensor;
          			$liste[$i]->new_sensor($resultat[$i]);
        		}
      		}
      		return $liste;
		}
	
		function getByPlace($placeId){
			return $this->getByQuery("select DISTINCT ON (sensor_model) * from sensor where sensor_id in (select sensor_id from sensor_place where place_id = ".$placeId.")");
		}
		function getById($id)
		{
			if (!isset($id) || empty($id))
        		return new personne;

      		$query = "select * from sensor where sensor_id = ".$id;
      		$bd = new bdConnect;
      		if ($resultat = $bd->get_data($query))
      		{
        		$sensor = new sensor;
        		$sensor->new_sensor($resultat[0]);
      		}
      		return $sensor;
		}
		
		function existe()
    	{
        	$query = "select * from sensor where " .
        			"lower(sensor_model) = lower('".str_replace("'","\'",$this->sensor_model)."')" .
        					" and manufacturer_id = ".$this->manufacturer_id." " .
        							"and gcmd_sensor_id = ".$this->gcmd_sensor_id; 
        	//echo $query."<br>";
        	$bd = new bdConnect;
        	if ($resultat = $bd->get_data($query))
        	{
          		$this->new_sensor($resultat[0]);
          		return true;
        	}
        	return false;
    	}

    	function idExiste()
    	{
        	$query = "select * from sensor where sensor_id = ".$this->sensor_id;
        	//echo $query."<br>";
        	$bd = new bdConnect;
        	if ($resultat = $bd->get_data($query))
        	{
          		$this->new_sensor($resultat[0]);
          		return true;
        	}
        	return false;
    	}
    	
 	function insert(& $bd)
    	{
    		if ($this->manufacturer_id === 0){
    			$this->manufacturer_id =$this->manufacturer->insert($bd);
    			//echo 'manufacturer_id:'.$this->manufacturer_id.'<br>';
    		}
    		    		
    		if (isset($this->boundings) && $this->bound_id != -1){
    			$this->bound_id = $this->boundings->insert($bd);
    			//echo 'bound_id:'.$this->bound_id.'<br>';
    		}
    		
     	 	$query_insert = "insert into sensor (gcmd_sensor_id";
     	 	$query_values =	"values (";
     	 	
     	 	$vide = true;
     	 	
     	 	if (isset($this->gcmd_sensor_id) && !empty($this->gcmd_sensor_id) && ($this->gcmd_sensor_id > 0) ){
     	 		$query_values .= $this->gcmd_sensor_id;
     	 		$vide = false;
     	 	}else{
     	 		$query_values .= "null";
     	 	}
     	 	if (isset($this->manufacturer_id) && !empty($this->manufacturer_id) && ($this->manufacturer_id > 0) )
     	 	{
     	 		$query_insert .= ",manufacturer_id";
     	 		$query_values .= ",".$this->manufacturer_id;
     	 		$vide = false;
     	 	}
     	 	if (isset($this->sensor_model) && !empty($this->sensor_model))
     	 	{
     	 		$query_insert .= ",sensor_model";
     	 		$query_values .= ",'".str_replace("'","\'",$this->sensor_model)."'";
     	 		$vide = false;
     	 	}
     	 	if (isset($this->bound_id) && !empty($this->bound_id) && $this->bound_id > 0)
     	 	{
     	 		$query_insert .= ",bound_id";
     	 		$query_values .= ",".$this->bound_id;
     	 		$vide = false;
     	 	}
     	 	if (isset($this->sensor_calibration) && !empty($this->sensor_calibration))
     	 	{
     	 		$query_insert .= ",sensor_calibration";
     	 		$query_values .= ",'".str_replace("'","\'",$this->sensor_calibration)."'";
     	 		$vide = false;
     	 	}
     	 	if (isset($this->sensor_date_begin) && !empty($this->sensor_date_begin))
     	 	{
     	 		$query_insert .= ",sensor_date_begin";
     	 		$query_values .= ",'".$this->sensor_date_begin."'";
     	 		$vide = false;
     	 	}
     	 	if (isset($this->sensor_date_end) && !empty($this->sensor_date_end))
     	 	{
     	 		$query_insert .= ",sensor_date_end";
     	 		$query_values .= ",'".$this->sensor_date_end."'";
     	 		$vide = false;
     	 	}
     	 
     	 	if (isset($this->sensor_url) && !empty($this->sensor_url))
     	 	{
     	 		$query_insert .= ",sensor_url";
     	 		$query_values .= ",'".$this->sensor_url."'";
     	 		$vide = false;
     	 	}
     	 	if (isset($this->sensor_elevation) && strlen($this->sensor_elevation) > 0)
     	 	{
     	 		$query_insert .= ",sensor_elevation";
     	 		$query_values .= ",".doubleAlt2int($this->sensor_elevation);
     	 		$vide = false;
     	 	}
     	 	
     	 	//if ($vide){
     	 		//return -1;
     	 	//}else{
     	 		$query = $query_insert.") ".$query_values.")";

     	 		$bd->exec($query);

     	 		$this->sensor_id = $bd->getLastId('sensor_sensor_id_seq');

     	 		return $this->sensor_id;
     	 	//}
    	}
    	
 		function update(& $bd)
    	{
    		if ($this->manufacturer_id == 0){
    			$this->manufacturer_id =$this->manufacturer->insert($bd);
    		}
    		    		
    		if ($this->bound_id != -1){
    			$this->bound_id = $this->boundings->insert($bd);
    		}
    		
     	 	$query = "update sensor set gcmd_sensor_id=";
     	 	$vide = true;
     	 	
     	 	if (isset($this->gcmd_sensor_id) && !empty($this->gcmd_sensor_id) && ($this->gcmd_sensor_id > 0) ){
     	 		$query .= $this->gcmd_sensor_id;
     	 		$vide = false;
     	 	}else{
     	 		$query .= "null";
     	 	}
     	 	if (isset($this->manufacturer_id) && !empty($this->manufacturer_id) && ($this->manufacturer_id > 0) )
     	 	{
     	 		$query .= ",manufacturer_id=".$this->manufacturer_id;
     	 		$vide = false;
     	 	}else{
     	 		$query .= ",manufacturer_id=null";
     	 	}
     	 	if (isset($this->sensor_model) && !empty($this->sensor_model))
     	 	{
     	 		$query .= ",sensor_model='".str_replace("'","\'",$this->sensor_model)."'";
     	 		$vide = false;
     	 	}else{
     	 		$query .= ",sensor_model=null";
     	 	}
     	 	if (isset($this->bound_id) && !empty($this->bound_id) && $this->bound_id > 0)
     	 	{
     	 		$query .= ",bound_id=".$this->bound_id;
     	 		$vide = false;
     	 	}else{
     	 		$query .= ",bound_id=null";
     	 	}
     	 	if (isset($this->sensor_calibration) && !empty($this->sensor_calibration))
     	 	{
     	 		$query .= ",sensor_calibration='".str_replace("'","\'",$this->sensor_calibration)."'";
     	 		$vide = false;
     	 	}else{
     	 		$query .= ",sensor_calibration=null";
     	 	}
     	 	if (isset($this->sensor_date_begin) && !empty($this->sensor_date_begin))
     	 	{
     	 		$query .= ",sensor_date_begin='".$this->sensor_date_begin."'";
     	 		$vide = false;
     	 	}else{
     	 		$query .= ",sensor_date_begin=null";
     	 	}
     	 	if (isset($this->sensor_date_end) && !empty($this->sensor_date_end))
     	 	{
     	 		$query .= ",sensor_date_end='".$this->sensor_date_end."'";
     	 		$vide = false;
     	 	}else{
     	 		$query .= ",sensor_date_end=null";
     	 	}
     	 	if (isset($this->sensor_url) && !empty($this->sensor_url))
     	 	{
     	 		$query .= ",sensor_url='".$this->sensor_url."'";
     	 		$vide = false;
     	 	}else{
     	 		$query .= ",sensor_url=null";
     	 	}
     	 	if (isset($this->sensor_elevation) && strlen($this->sensor_elevation) > 0)
     	 	{
     	 		$query .= ",sensor_elevation=".doubleAlt2int($this->sensor_elevation);
     	 		$vide = false;
     	 	}else{
     	 		$query .= ",sensor_elevation=null";
     	 	}
     	 	
     	 	
     	 	/*if ($vide){
     	 		//$bd->exec("delete from sensor where sensor_id=".$this->sensor_id);
     	 		//$this->sensor_id = -1;
     	 	}else{*/
     	 		$query .= " where sensor_id=".$this->sensor_id;
     	 		
     	 		//echo 'update sensor $query'.$query.'<br>';
     	 		
     	 		$bd->exec($query);

     	 	//}
     	 	return $this->sensor_id;
    	}
    	
    	
    	function get_sensor_places()
    	{
    		$query = "select * from sensor_place where sensor_id = ".$this->sensor_id;
    		$sensor_place = new sensor_place;
    		$this->sensor_places = $sensor_place->getByQuery($query);
    		for ($i = 0; $i < count($this->sensor_places); $i++)
    		{
    			$this->sensor_places[$i]->getPlace();
    		}
    	}
    	
    	function getSensorModelBySensorId($id){
    		$query = "select sensor_model from sensor where sensor_id = ".$id;
    		$bd = new bdConnect;
    		$resultat = $bd->get_data($query);
    		return $resultat;
    	}
 	}
?>
