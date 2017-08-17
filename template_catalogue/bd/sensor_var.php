<?php
/*
 * Created on 8 juil. 2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 	require_once("bd/bdConnect.php");
 	require_once("bd/variable.php");
 	require_once("bd/sensor.php");
 	
 	class sensor_var
 	{
 		var $var_id;
 		var $sensor_id;
 		var $sensor_precision;
 		var $variable;
 		var $sensor;
 		//add by lolo
 		var $unit;
 		var $methode_acq;
 		var $date_min;
 		var $date_max;
 		var $flag_param_calcule;
 		
 		function new_sensor_var($tab)
 		{
 			$this->sensor_id = $tab[0];
 			$this->var_id = $tab[1];
 			$this->sensor_precision = $tab[2];
			$this->methode_acq = $tab[3];

 			if (isset($this->sensor_id) && !empty($this->sensor_id))
 			{
 				$sensor = new sensor;
 				$this->sensor = $sensor->getById($this->sensor_id);
 			}
 			if (isset($this->var_id) && !empty($this->var_id))
 			{
 				$var = new variable;
 				$this->variable = $var->getById($this->var_id);
 			}
 		}
 		
 		function getAll()
 		{
 			$query = "select * from sensor_var";
      		return $this->getByQuery($query);
 		}
 		
 		function getByIds($v_id,$s_id)
 		{
 			$query = "select * from sensor_var where var_id = ".$v_id." and sensor_id = ".$s_id;
 			$liste = $this->getByQuery($query);
 		if ($liste){
 				return $liste[0];
 			}else{
 				return null;
 			}
 		}
 		
    	function getByQuery($query)
    	{
      		$bd = new bdConnect;
      		$liste = array();
      		if ($resultat = $bd->get_data($query))
      		{
        		for ($i=0; $i<count($resultat);$i++)
        		{
          			$liste[$i] = new sensor_var;
          			$liste[$i]->new_sensor_var($resultat[$i]);
        		}
      		}
      		return $liste;
    	}

    	function existe()
    	{
        	$query = "select * from sensor_var where " .
        			"sensor_id = ".$this->sensor_id." and var_id = ".$this->var_id;
        	//echo $query."<br>";
        	$bd = new bdConnect;
        	if ($resultat = $bd->get_data($query))
        	{
          		$this->new_sensor_var($resultat[0]);
          		return true;
        	}
        	return false;
    	}
    	
    	//modif by lolo
    	function insert(& $bd){
    		if (isset($this->sensor_id) && isset($this->var_id) && !$this->existe()){
    			$query_insert = "insert into sensor_var (sensor_id,var_id";
    			$query_values = "values (".$this->sensor_id.",".$this->var_id;

			if (!isset($this->sensor_precision) || empty($this->sensor_precision))
    			{
				$this->sensor_precision=' ';
			}
    				$query_insert .= ",sensor_precision";
    				$query_values .= ",'".str_replace("'","\'",$this->sensor_precision)."'";

			if ( isset($this->methode_acq) && ! empty($this->methode_acq) ){
				$query_insert .= ",methode_acq";
				$query_values .= ",'".str_replace("'","\'",$this->methode_acq)."'";
			}

    			$query = $query_insert.") ".$query_values.")";

    			$bd->exec($query);
    		}else if ($this->existe()){
			if (isset($this->sensor_precision)){
				$query = "update sensor_var set sensor_precision = '".$this->sensor_precision."' where sensor_id = ".$this->sensor_id." and var_id = ".$this->var_id;
				$bd->exec($query);
			}
			if (isset($this->methode_acq)){
                                $query = "update sensor_var set methode_acq = '".$this->methode_acq."' where sensor_id = ".$this->sensor_id." and var_id = ".$this->var_id;
                                $bd->exec($query);
                        }
		}
    	}
    	
    	//add by lolo
    	function getVariable()
    	{
    		$var = new variable;
    		return $var->getById($this->var_id);
    	}
 	}
?>
