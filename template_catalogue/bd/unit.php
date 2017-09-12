<?php
/*
 * Created on 15 juil. 2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
 require_once ("bd/bdConnect.php");

	class unit
	{
		var $unit_id;
		var $unit_code;
		var $unit_name;
		
		function new_unit($tab)
		{
			$this->unit_id = $tab[0];
			$this->unit_code = $tab[1];
			$this->unit_name = $tab[2];
		}
		
		function getAll()
 		{
 			$query = "select * from unit order by unit_name";
      		$bd = new bdConnect;
      		$liste = array();
      		if ($resultat = $bd->get_data($query))
      		{
        		for ($i=0; $i<count($resultat);$i++)
        		{
          			$liste[$i] = new unit;
          			$liste[$i]->new_unit($resultat[$i]);
        		}
      		}
      		return $liste;
 		}
 		
 		function getById($id)
    	{
      		if (!isset($id) || empty($id))
        		return new unit;

      		$query = "select * from unit where unit_id = ".$id;
      		$bd = new bdConnect;
      		if ($resultat = $bd->get_data($query))
      		{
        		$per = new unit;
        		$per->new_unit($resultat[0]);
      		}
      		return $per;
    	}
    	
    	function getByCode($code)
    	{
    		$query = "select * from unit where lower(unit_code) = '".strtolower($code)."'";
    		$unit = null;
    		$bd = new bdConnect;
    		if ($resultat = $bd->get_data($query))
    		{
    			$unit = new unit;
    			$unit->new_unit($resultat[0]);
    		}
    		return $unit;
    	}
    	
    	function getByQuery($query)
    	{
      		$bd = new bdConnect;
      		$liste = array();
      		if ($resultat = $bd->get_data($query))
      		{
        		for ($i=0; $i<count($resultat);$i++)
        		{
          			$liste[$i] = new unit;
          			$liste[$i]->new_unit($resultat[$i]);
        		}
      		}
      		return $liste;
    	}

    	function existe()
    	{
        	$query = "select * from unit where " .
        			"lower(unit_name) = lower('".(str_replace("'","\'",$this->unit_name))."')";
        	$bd = new bdConnect;
        	if ($resultat = $bd->get_data($query))
        	{
          		$this->unit_id = $resultat[0][0];
          		return true;
        	}
        	return false;
    	}

    	function idExiste()
    	{
        	$query = "select * from unit where unit_id = ".$this->unit_id;
        	$bd = new bdConnect;
        	if ($resultat = $bd->get_data($query))
        	{
          		$this->unit_name = $resultat[0][1];
          		return true;
        	}
        	return false;
    	}
    	
    	function toString()
    	{
    		return $this->unit_name.(($this->unit_code)?' - '.$this->unit_code:'');	
    	}
    	
    	function insert(& $bd){
    			
    		if (!$this->existe()){
    			$query_insert = "insert into unit (unit_name";
    			$query_values =	"values ('".str_replace("'","\'",$this->unit_name)."'";

    			if (isset($this->unit_code) && !empty($this->unit_code))
    			{
    				$query_insert .= ",unit_code";
    				$query_values .= ",'".$this->unit_code."'";
    			}
     	 	
    			$query = $query_insert.") ".$query_values.")";
    			$bd->exec($query);
    			$this->unit_id = $bd->getLastId("unit_unit_id_seq");
    		}
    		return $this->unit_id;
    	}
    	
    	//creer element select pour formulaire
    	function chargeForm($form,$label,$titre,$indice,$type)
    	{

      		$liste = $this->getAll();
      		$array[0] = "";
      		for ($i = 0; $i < count($liste); $i++)
        	{
          		$j = $liste[$i]->unit_id;
          		$array[$j] = $liste[$i]->toString();//unit_name;
        	}
        	
        	$s = & $form->createElement('select',$label,$titre,$array,array('onchange' => "fillBoxes('".$label."',['new_unit_".$type.$indice."','new_unit_code_".$type.$indice."'],'unit',['unit_name','unit_code']);"));

      		return $s;
    	}
	}
?>
