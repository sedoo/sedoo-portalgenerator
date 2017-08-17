<?php
/*
 * Created on 8 juil. 2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 	require_once("bd/bdConnect.php");
 	require_once("bd/dataset.php");
 	require_once("bd/sensor.php");
 	
 	class dats_sensor
 	{
 		var $dats_id;
 		var $sensor_id;
 		var $nb_sensor;
 		var $sensor_resol_temp;
 		var $sensor_lat_resolution;
 		var $sensor_lon_resolution;
 		var $sensor_vert_resolution;
 		var $grid_original;
 		var $grid_process;
 		var $dataset;
 		var $sensor;
 		// add by lolo
 		var $nbVars;
 		var $nbCalcVars;
 		
 		function new_dats_sensor($tab)
 		{
 			$this->dats_id = $tab[1];
 			$this->sensor_id = $tab[0];
 			$this->nb_sensor = $tab[2];
 			$this->sensor_resol_temp = $tab[3];
 			$this->sensor_lat_resolution = $tab[4];
 			$this->sensor_lon_resolution = $tab[5];
 			$this->sensor_vert_resolution = $tab[6];
 			$this->grid_original = $tab[7];
 			$this->grid_process = $tab[8];
 			$this->getSensor();
 			// add by lolo
 			$this->getSensorVars();
 			$this->getSensorCalcVars();
 			if ( !isset($this->nbVars) || $this->nbVars == 0)
 				$this->nbVars = 1;
 			if (!isset($this->nbCalcVars) || $this->nbCalcVars == 0)
 				$this->nbCalcVars = 1;
 		}
 		
 		function getSensor()
 		{
 			if (isset($this->sensor_id) && !empty($this->sensor_id))
 			{
 				$sensor = new sensor;
 				$this->sensor = $sensor->getById($this->sensor_id);
 			}
 		}
 		
 	// add by lolo
 		function getSensorVars()
 		{
 			if (isset($this->sensor_id) && !empty($this->sensor_id) && isset($this->dats_id) && !empty($this->dats_id))
 			{
	 			$query = 'select * from sensor_var where sensor_id = '.$this->sensor_id.' ' .
	 					'and var_id in (select distinct var_id from dats_var where dats_id = '.$this->dats_id.' and flag_param_calcule = 0)';
	 			$sensor_var = new sensor_var;
	 			$vars = $sensor_var->getByQuery($query);
	 			$this->sensor->sensor_vars = $vars;
	 			$this->nbVars = count($vars);
 			}
 		}
 		
 		// add by lolo
 		function getSensorCalcVars()
 		{
 			if (isset($this->sensor_id) && !empty($this->sensor_id) && isset($this->dats_id) && !empty($this->dats_id))
 			{
	 			$query = 'select * from sensor_var where sensor_id = '.$this->sensor_id.' ' .
	 					'and var_id in (select distinct var_id from dats_var where dats_id = '.$this->dats_id.' and flag_param_calcule = 1)';
	 			$sensor_var = new sensor_var;
	 			$calcVars = $sensor_var->getByQuery($query);
	 			$this->sensor->sensor_vars[] = $calcVars;
	 			$this->nbCalcVars = count($calcVars);
 			}
 		}
 		
 		function toString(){
 			$result = 'GCMD: ';
 			if (isset($this->sensor->gcmd_instrument_keyword) ){
     	 		$result .= $this->sensor->gcmd_instrument_keyword->gcmd_sensor_name;
     	 	}else
     	 		$result .= $this->sensor->gcmd_sensor_id;
    	 		
     	 	if (isset($this->sensor->manufacturer)){
     	 		$result .= "\nManufacturer: ".$this->sensor->manufacturer->toString();
     	 	}
     	 	if (isset($this->sensor->sensor_model) && !empty($this->sensor->sensor_model)){
     	 		$result .= "\nModel: ".$this->sensor->sensor_model;
     	 	}
     	 	     	 	
     	 	if (isset($this->sensor->sensor_calibration) && !empty($this->sensor->sensor_calibration)){
     	 		$result .= "\nCalibration: ".$this->sensor->sensor_calibration;
     	 	}
     	 	if (isset($this->sensor_resol_temp) && !empty($this->sensor_resol_temp)){
     	 		$result .= "\nObservation frequency: ".$this->sensor_resol_temp;
     	 	}
     	 	if (isset($this->sensor_vert_resolution) && !empty($this->sensor_vert_resolution)){
     	 		$result .= "\nVertical coverage: ".$this->sensor_vert_resolution;
     	 	}
     	 	if (isset($this->sensor_lat_resolution) && !empty($this->sensor_lat_resolution)){
     	 		$result .= "\nLatitude coverage: ".$this->sensor_lat_resolution;
     	 	}
 			if (isset($this->sensor_lon_resolution) && !empty($this->sensor_lon_resolution)){
     	 		$result .= "\nLongitude coverage: ".$this->sensor_lon_resolution;
     	 	}
     	 	if (isset($this->sensor->sensor_url) && !empty($this->sensor->sensor_url)){
     	 		$result .= "\nURL: ".$this->sensor->sensor_url;
     	 	}
 			if (isset($this->sensor->sensor_date_begin) && !empty($this->sensor->sensor_date_begin)){
     	 		$result .= "\nDate beginl: ".$this->sensor->sensor_date_begin;
     	 	}
     	 	if (isset($this->sensor->sensor_date_end) && !empty($this->sensor->sensor_date_end)){
     	 		$result .= "\nDate end: ".$this->sensor->sensor_date_end;
     	 	}
 			if (isset($this->sensor->boundings)){
     	 		$result .= "\nCoordinates: ".$this->sensor->boundings->toString();
     	 	}
     	 	if (isset($this->sensor->sensor_elevation) && strlen($this->sensor->sensor_elevation) > 0){
     	 		$result .= "\nAltitude: ".doubleAlt2int($this->sensor->sensor_elevation);
     	 	}
 			if (isset($this->grid_original) && !empty($this->grid_original)){
     	 		$result .= "\nOriginal grid: ".$this->grid_original;
     	 	}
     	 	if (isset($this->grid_process) && !empty($this->grid_process)){
     	 		$result .= "\nGrid processing: ".$this->grid_process;
     	 	}
 			return $result;
 		}
 		
 		function getDataset()
 		{
 			if (isset($this->dats_id) && !empty($this->dats_id))
 			{
 				$dts = new dataset;
 				$this->dataset = $dts->getById($this->dats_id);
 			}
 		}
 		
 		function getAll()
 		{
 			$query = "select * from dats_sensor order by dats_id";
      		return $this->getByQuery($query);
 		}
 		
 		function getByIds($d_id,$s_id)
 		{
 			$query = "select * from dats_sensor where dats_id = ".$d_id." and sensor_id = ".$s_id;
 			$liste = $this->getByQuery($query);
 			return $liste[0];
 		}
    	
    	function getByQuery($query)
    	{
      		$bd = new bdConnect;
      		$liste = array();
      		if ($resultat = $bd->get_data($query))
      		{
        		for ($i=0; $i<count($resultat);$i++)
        		{
          			$liste[$i] = new dats_sensor;
          			$liste[$i]->new_dats_sensor($resultat[$i]);
        		}
      		}
      		return $liste;
    	}

    	function existe()
    	{
        	$query = "select * from dats_sensor where " .
        			"dats_id = ".$this->dats_id." and sensor_id = ".$this->sensor_id;
        	//echo $query."<br>";
        	$bd = new bdConnect;
        	if ($resultat = $bd->get_data($query))
        	{
          		$this->new_dats_sensor($resultat[0]);
          		return true;
        	}
        	return false;
    	}
    	
    	function insert(& $bd)
    	{
     	 	$query_insert = "insert into dats_sensor (dats_id,sensor_id";
     	 	$query_values = "values (".$this->dats_id.",".$this->sensor_id;
     	 	
    		if (isset($this->nb_sensor) && !empty($this->nb_sensor))
     	 	{
     	 		$query_insert .= ",nb_sensor";
     	 		$query_values .= ",".$this->nb_sensor;
     	 	}
    		if (isset($this->sensor_resol_temp) && !empty($this->sensor_resol_temp))
     	 	{
     	 		$query_insert .= ",sensor_resol_temp";
     	 		$query_values .= ",'".str_replace("'","\'",$this->sensor_resol_temp)."'";
     	 		$vide = false;
     	 	}
     	 	if (isset($this->sensor_vert_resolution) && !empty($this->sensor_vert_resolution))
     	 	{
     	 		$query_insert .= ",sensor_vert_resolution";
     	 		$query_values .= ",'".str_replace("'","\'",$this->sensor_vert_resolution)."'";
     	 		$vide = false;
     	 	}
     	 	if (isset($this->sensor_lat_resolution) && !empty($this->sensor_lat_resolution))
     	 	{
     	 		$query_insert .= ",sensor_lat_resolution";
     	 		$query_values .= ",'".str_replace("'","\'",$this->sensor_lat_resolution)."'";
     	 		$vide = false;
     	 	}
    		if (isset($this->sensor_lon_resolution) && !empty($this->sensor_lon_resolution))
     	 	{
     	 		$query_insert .= ",sensor_lon_resolution";
     	 		$query_values .= ",'".str_replace("'","\'",$this->sensor_lon_resolution)."'";
     	 		$vide = false;
     	 	}
    		if (isset($this->grid_original) && !empty($this->grid_original))
     	 	{
     	 		$query_insert .= ",grid_original";
     	 		$query_values .= ",'".str_replace("'","\'",$this->grid_original)."'";
     	 		$vide = false;
     	 	}
     	 	if (isset($this->grid_process) && !empty($this->grid_process))
     	 	{
     	 		$query_insert .= ",grid_process";
     	 		$query_values .= ",'".str_replace("'","\'",$this->grid_process)."'";
     	 		$vide = false;
     	 	}
     	 	$query = $query_insert.") ".$query_values.")";
     	 	      		
      		$bd->exec($query);
    	}
    	/*
 		function insertOld()
    	{
     	 	$query_insert = "insert into dats_sensor (dats_id,sensor_id";
     	 	$query_values = "values (".$this->dats_id.",".$this->sensor_id;
     	 	
    		if (isset($this->nb_sensor) && !empty($this->nb_sensor))
     	 	{
     	 		$query_insert .= ",nb_sensor";
     	 		$query_values .= ",".$this->nb_sensor;
     	 	}
     	 	$query = $query_insert.") ".$query_values.")";
     	 	
      		$bd = new bdConnect;
      		$bd->insert($query);
    	}*/
 	}
?>
