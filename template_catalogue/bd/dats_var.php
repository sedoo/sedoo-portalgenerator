<?php
/*
 * Created on 8 juil. 2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 	require_once("bd/bdConnect.php");
 	require_once("bd/dataset.php");
 	require_once("bd/variable.php");
 	require_once("bd/unit.php");
 	require_once("bd/vertical_level_type.php");
 	
 	class dats_var
 	{
 		var $var_id;
 		var $dats_id;
 		var $unit_id;
 		var $vert_level_type_id;
 		var $vertical_level_type;
 		var $unit;
 		var $flag_param_calcule;
 		var $min_value;
 		var $max_value;
 		var $methode_acq;
 		var $date_min;
 		var $date_max;
 		var $dataset;
 		var $variable;
 		
 		var $level_type;
 		
 		function new_dats_var($tab)
 		{
 			$this->var_id = $tab[0];
 			$this->dats_id = $tab[1];
 			$this->unit_id = $tab[2];
 			$this->vert_level_type_id = $tab[3];
 			$this->flag_param_calcule = $tab[4];
 			$this->min_value = $tab[5];
 			$this->max_value = $tab[6];
 			$this->methode_acq = $tab[7];
 			$this->date_min = $tab[8];
 			$this->date_max = $tab[9];
 			$this->level_type = $tab[10];
 		}
 		
 		function toString(){
 			$result = 'Param: '.$this->variable->var_name."\n";
 			if (isset($this->variable->gcmd)){
 				$result .= 'GCMD Keyword: '.$this->variable->gcmd->gcmd_id.' - '.$this->variable->gcmd->toString()."\n";
 			}
 			
 			if (isset($this->unit)){
 				$result .= 'Unit: '.$this->unit->toString()."\n";
 			}
 			if (isset($this->vertical_level_type)){
 				$result .= 'Vertical level type: '.$this->vertical_level_type->vert_level_type_name."\n";
 			}
 			$result .= 'Acquisition methodology and quality: '.$this->methode_acq."\n";
 			$result .= 'Period: '.$this->date_min.' - '.$this->date_max."\n";
 			$result .= 'Sensor Precision: '.$this->variable->sensor_precision."\n";
 			$result .= 'Flag Calcul: '.$this->flag_param_calcule."\n";
 			return $result;
 		}
 		
 		function getUnit()
 		{
 			if (isset($this->unit_id) && !empty($this->unit_id))
 			{
 				$unit = new unit;
 				$this->unit = $unit->getById($this->unit_id);
 			}
 		}
 		
 		function getVerticalLevelType()
 		{
 			if (isset($this->vert_level_type_id) && !empty($this->vert_level_type_id))
 			{
 				$vertical_level_type = new vertical_level_type;
 				$this->vertical_level_type = $vertical_level_type->getById($this->vert_level_type_id);
 			}
 		}
 		
 		function getDataset()
 		{
 			if (isset($this->dats_id) && !empty($this->dats_id))
 			{
 				$dts = new dataset;
 				$this->dataset = $dts->getById($this->dats_id);
 			}
 		}
 		
 		function getVariable()
 		{
 			if (isset($this->var_id) && !empty($this->var_id))
 			{
 				$var = new variable;
 				$this->variable = $var->getById($this->var_id);
 			}
 		}
 		
 		function getAll()
 		{
 			$query = "select * from dats_var";
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
          			$liste[$i] = new dats_var;
          			$liste[$i]->new_dats_var($resultat[$i]);
        		}
      		}
      		return $liste;
    	}
    	
    	function getByIds($d_id,$v_id)
 		{
 			$query = "select * from dats_var where dats_id = ".$d_id." and sensor_id = ".$v_id;
 			$liste = $this->getByQuery($query);
 			return $liste[0];
 		}

    	function existe()
    	{
        	$query = "select * from dats_var where " .
        			"dats_id = ".$this->dats_id." and var_id = ".$this->var_id." and flag_param_calcule = ".$this->flag_param_calcule;
        	$bd = new bdConnect;
        	if ($resultat = $bd->get_data($query))
        	{
          		$this->new_dats_var($resultat[0]);
          		return true;
        	}
        	return false;
    	}
    	
    	function insert(& $bd)
    	{

		if ($this->existe()){
			return;
		}

     	 	$query_insert = "insert into dats_var (dats_id,var_id";
     	 	$query_values = "values (".$this->dats_id.",".$this->var_id;
     	 	if (isset($this->unit_id) && !empty($this->unit_id) && $this->unit_id > 0)
     	 	{
     	 		$query_insert .= ",unit_id";
     	 		$query_values .= ",".$this->unit_id."";
     	 	}
     	 	if (isset($this->vert_level_type_id) && !empty($this->vert_level_type_id) && $this->vert_level_type_id > 0)
     	 	{
     	 		$query_insert .= ",vert_level_type_id";
     	 		$query_values .= ",".$this->vert_level_type_id."";
     	 	}
     	 	if (isset($this->flag_param_calcule) ) 
     	 	{
     	 		$query_insert .= ",flag_param_calcule";
     	 		$query_values .= ",".$this->flag_param_calcule;
     	 	}
     	 	if (isset($this->min_value) && !empty($this->min_value))
     	 	{
     	 		$query_insert .= ",min_value";
     	 		$query_values .= ",".$this->min_value;
     	 	}
     	 	if (isset($this->max_value) && !empty($this->max_value))
     	 	{
     	 		$query_insert .= ",max_value";
     	 		$query_values .= ",".$this->max_value;
     	 	}
     	 	if (isset($this->methode_acq) && !empty($this->methode_acq))
     	 	{
     	 		$query_insert .= ",methode_acq";
     	 		$query_values .= ",'".str_replace("'","\'",$this->methode_acq)."'";
     	 	}
     	 	if (isset($this->date_min) && !empty($this->date_min))
     	 	{
     	 		$query_insert .= ",date_min";
     	 		$query_values .= ",'".$this->date_min."'";
     	 	}
     	 	if (isset($this->date_max) && !empty($this->date_max))
     	 	{
     	 		$query_insert .= ",date_max";
     	 		$query_values .= ",'".$this->date_max."'";
     	 	}
     	 	if (isset($this->level_type) && !empty($this->level_type))
     	 	{
     	 		$query_insert .= ",level_type";
     	 		$query_values .= ",'".$this->level_type."'";
     	 	}
     	 	
     	 	
     	 	$query = $query_insert.") ".$query_values.")";
      		
      		$bd->exec($query);
    	}
 	
 		
 	}
?>
