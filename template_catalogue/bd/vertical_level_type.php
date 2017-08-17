<?php
/*
 * Created on 1 oct. 2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 require_once("bd/bdConnect.php");
 
 	class vertical_level_type
 	{
 		var $vert_level_type_id;
 		var $vert_level_type_name;
 		
 		function new_vertical_level_type($tab)
 		{
 			$this->vert_level_type_id = $tab[0];
 			$this->vert_level_type_name = $tab[1];
 		}
 		
 		function getAll()
 		{
 			$query = "select * from vertical_level_type order by vert_level_type_name";
      		$bd = new bdConnect;
      		$liste = array();
      		if ($resultat = $bd->get_data($query))
      		{
        		for ($i=0; $i<count($resultat);$i++)
        		{
          			$liste[$i] = new vertical_level_type;
          			$liste[$i]->new_vertical_level_type($resultat[$i]);
        		}
      		}
      		return $liste;
 		}
 		
 		function getById($id)
    	{
      		if (!isset($id) || empty($id))
        		return new vertical_level_type;

      		$query = "select * from vertical_level_type where vert_level_type_id = ".$id;
      		$bd = new bdConnect;
      		if ($resultat = $bd->get_data($query))
      		{
        		$country = new vertical_level_type;
        		$country->new_vertical_level_type($resultat[0]);
      		}
      		return $country;
    	}
    	
    	function getByQuery($query)
    	{
      		$bd = new bdConnect;
      		$liste = array();
      		if ($resultat = $bd->get_data($query))
      		{
        		for ($i=0; $i<count($resultat);$i++)
        		{
          			$liste[$i] = new vertical_level_type;
          			$liste[$i]->new_vertical_level_type($resultat[$i]);
        		}
      		}
      		return $liste;
    	}

    	function existe()
    	{
        	$query = "select * from vertical_level_type where " .
        			"lower(vert_level_type_name) = lower('".(str_replace("'","\'",$this->vert_level_type_name))."')";
        	//echo $query."<br>";
        	$bd = new bdConnect;
        	if ($resultat = $bd->get_data($query))
        	{
          		$this->vert_level_type_id = $resultat[0][0];
          		return true;
        	}
        	return false;
    	}

    	function idExiste()
    	{
        	$query = "select * from vertical_level_type where vert_level_type_id = ".$this->vert_level_type_id;
        	//echo $query."<br>";
        	$bd = new bdConnect;
        	if ($resultat = $bd->get_data($query))
        	{
          		$this->vert_level_type_name = $resultat[0][1];
          		return true;
        	}
        	return false;
    	}
    	
    	function insert()
    	{
     	 	$query = "insert into vertical_level_type ('vert_level_type_name') " .
     	 			"values ('".str_replace("'","\'",$this->vert_level_type_name)."')";
      		$bd = new bdConnect;
      		$this->vert_level_type_id = $bd->insert($query);
    	}

    	//creer element select pour formulaire
    	function chargeForm($form,$label,$titre,$indice,$type)
    	{

      		$liste = $this->getAll();
      		$array[0] = "";
      		for ($i = 0; $i < count($liste); $i++)
        	{
          		$j = $liste[$i]->vert_level_type_id;
          		$array[$j] = $liste[$i]->vert_level_type_name;
          		//echo 'array['.$j.'] = '.$array[$j].'<br>';
        	}
        	$s = & $form->createElement('select',$label,$titre,$array,array('onchange' => "fillBox('".$label."','new_level_type_".$type.$indice."','vertical_level_type','vert_level_type_name');"));
      		//$s = & $form->createElement('select',$label,$titre);
      		//$s->loadArray($array);
      		return $s;
    	}
 	}
?>
