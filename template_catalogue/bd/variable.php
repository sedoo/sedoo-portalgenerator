<?php
/*
 * Created on 8 juil. 2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 	require_once ("bd/bdConnect.php");
 	require_once ("bd/gcmd_science_keyword.php");
 	
 	class variable
 	{
 		var $var_id;
 		var $gcmd_id;
 		var $var_name;
 		var $gcmd;

 		var $sensor_precision;
 		
 		function new_variable($tab)
 		{
 			$this->var_id = $tab[0];
 			$this->gcmd_id = $tab[1];
 			$this->var_name = $tab[2];
 			
 			if (isset($this->gcmd_id) && !empty($this->gcmd_id))
 			{
 				$gcmd = new gcmd_science_keyword;
 				$this->gcmd = $gcmd->getById($this->gcmd_id);
 			}
 		}
 		
 		
 		
 		function getAll()
 		{
 			$query = "select * from variable order by var_name";
      		return $this->getByQuery($query);
 		}
 		
 		function getById($id)
    	{
      		if (!isset($id) || empty($id))
        		return new variable;

      		$query = "select * from variable where var_id = ".$id;
      		$bd = new bdConnect;
      		if ($resultat = $bd->get_data($query))
      		{
        		$var = new variable;
        		$var->new_variable($resultat[0]);
      		}
      		return $var;
    	}
    	
    	function getByQuery($query)
    	{
      		$bd = new bdConnect;
      		$liste = array();
      		if ($resultat = $bd->get_data($query))
      		{
        		for ($i=0; $i<count($resultat);$i++)
        		{
          			$liste[$i] = new variable;
          			$liste[$i]->new_variable($resultat[$i]);
        		}
      		}
      		return $liste;
    	}

    	function existe()
    	{
        	$query = "select * from variable where " .
        			"lower(var_name) = lower('".(str_replace("'","\'",$this->var_name))."')";

		if ($this->gcmd_id > 0){
    			$query .= " and gcmd_id = ".$this->gcmd_id;
    		}else{
    			$query .= " and gcmd_id is null";
    		}

			$bd = new bdConnect;
        	if ($resultat = $bd->get_data($query))
        	{
          		$this->new_variable($resultat[0]);
          		return true;
        	}
        	return false;
    	}

    	function idExiste()
    	{
        	$query = "select * from variable where var_id = ".$this->var_id;
        	$bd = new bdConnect;
        	if ($resultat = $bd->get_data($query))
        	{
          		$this->new_variable($resultat[0]);
          		return true;
        	}
        	return false;
    	}
    	
    	function insert(& $bd)
    	{
     	 	$query_insert = "insert into variable (var_name";
     	 	$query_values = "values ('".str_replace("'","\'",$this->var_name)."'";
     	 	
    		if ($this->gcmd_id > 0){
    			$query_insert .= ",gcmd_id";
    			$query_values .= ",".$this->gcmd_id;
   			}

     	 	$query = $query_insert.") ".$query_values.")";			
     	 	     	 	
     	 	$bd->exec($query);
      		$this->var_id = $bd->getLastId("variable_var_id_seq");
      		
      		return $this->var_id;
    	}
    	
 		function update(& $bd)
    	{
    		
     	 	$query = "update variable set var_name='".str_replace("'","\'",$this->var_name)."'";

    		if ($this->gcmd_id > 0){
    			$query .= ",gcmd_id=".$this->gcmd_id;
   			}

     	 	$query .= " where var_id=".$this->var_id;			
     	 	     	 	
     	 	$bd->exec($query);
      		      		
    	}
    	
    	//creer element select pour formulaire
    	function chargeForm($form,$label,$titre,$indice,$type)
    	{

      		$liste = $this->getAll();
      		$array[0] = "";
      		for ($i = 0; $i < count($liste); $i++)
        	{
          		$j = $liste[$i]->var_id;
          		$array[$j] = $liste[$i]->var_name;
        	}
        	
        	$s = & $form->createElement('select',$label,$titre,$array,array('onchange' => "updateParam('".$label."','".$type.$indice."');"));

      		return $s;
    	}
 	}
?>
