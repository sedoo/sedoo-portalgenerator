<?php
/*
 * Created on 8 juil. 2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 	require_once("bd/bdConnect.php");
 	
 	class data_format
 	{
 		var $data_format_id;
 		var $data_format_name;
 		
 		function new_data_format($tab)
 		{
 			$this->data_format_id = $tab[0];
 			$this->data_format_name = $tab[1];
 		}
 		
 		function getAll()
 		{
 			$query = 'select * from data_format where data_format_id not in ('.DATA_FORMAT_EXCLUDE.') order by data_format_name';
      		$bd = new bdConnect;
      		$liste = array();
      		if ($resultat = $bd->get_data($query))
      		{
        		for ($i=0; $i<count($resultat);$i++)
        		{
          			$liste[$i] = new data_format;
          			$liste[$i]->new_data_format($resultat[$i]);
        		}
      		}
      		return $liste;
 		}
 		
 		function getById($id)
    	{
      		if (!isset($id) || empty($id))
        		return new data_format;

      		$query = "select * from data_format where data_format_id = ".$id;
      		
      		$bd = new bdConnect;
      		if ($resultat = $bd->get_data($query))
      		{
        		$data_format = new data_format;
        		$data_format->new_data_format($resultat[0]);
      		}
      		return $data_format;
    	}
    	
    	function getByName($name){
    		$query = "select * from data_format where lower(data_format_name) = '".strtolower($name)."'";
    		
    		//echo $query.'<br>';
    		$format = null;
      		$bd = new bdConnect;
      		if ($resultat = $bd->get_data($query))
      		{
        		$format = new data_format;
        		$format->new_data_format($resultat[0]);
      		}
      		return $format;
    	}
    	
    	function getByQuery($query)
    	{
      		$bd = new bdConnect;
      		$liste = array();
      		if ($resultat = $bd->get_data($query))
      		{
        		for ($i=0; $i<count($resultat);$i++)
        		{
          			$liste[$i] = new data_format;
          			$liste[$i]->new_data_format($resultat[$i]);
        		}
      		}
      		return $liste;
    	}

    	function existe()
    	{
        	$query = "select * from data_format where " .
        			"lower(data_format_name) = lower('".(str_replace("'","\'",$this->data_format_name))."')";
        	//echo $query."<br>";
        	$bd = new bdConnect;
        	if ($resultat = $bd->get_data($query))
        	{
          		$this->data_format_id = $resultat[0][0];
          		return true;
        	}
        	return false;
    	}

    	function idExiste()
    	{
        	$query = "select * from data_format where data_format_id = ".$this->data_format_id;
        	//echo $query."<br>";
        	$bd = new bdConnect;
        	if ($resultat = $bd->get_data($query))
        	{
          		$this->data_format_name = $resultat[0][1];
          		return true;
        	}
        	return false;
    	}
    	
    	function insertOld()
    	{
     	 	$query = "insert into data_format (data_format_name) " .
     	 			"values ('".str_replace("'","\'",$this->data_format_name)."')";
      		$bd = new bdConnect;
      		$bd->insert($query);
      		
      		$this->data_format_id = $bd->getLastId("data_format_data_format_id_seq");
      		
      		return $this->data_format_id;
    	}
    	
    	function insert(& $bd)
    	{
    		if (!$this->existe()){
    			$query = "insert into data_format (data_format_name) " .
     	 			"values ('".str_replace("'","\'",$this->data_format_name)."')";


    			$bd->exec($query);

    			$this->data_format_id = $bd->getLastId("data_format_data_format_id_seq");
    		}
    		return $this->data_format_id;
    	}

    	//creer element select pour formulaire
    	function chargeForm($form,$label,$titre,$indice)
    	{

      		$liste = $this->getAll();
      		$array[0] = "";
      		for ($i = 0; $i < count($liste); $i++)
        	{
          		$j = $liste[$i]->data_format_id;
          		$array[$j] = $liste[$i]->data_format_name;
          		//echo 'array['.$j.'] = '.$array[$j].'<br>';
        	}
        	
        	$s = & $form->createElement('select',$label,$titre,$array,array('onchange' => "fillBox('".$label."','new_data_format_".$indice."','data_format','data_format_name');"));
      		//$s = & $form->createElement('select',$label,$titre);
      		//$s->loadArray($array);
      		return $s;
    	}
    	
 		function chargeFormDestFormat($form,$label,$titre,$format)
    	{

    		$query = "select * from data_format where data_format_name = '".$format."'";
    		
      		$liste = $this->getByQuery($query);
      		$array[0] = "Original data format";
      		for ($i = 0; $i < count($liste); $i++)
        	{
          		$j = $liste[$i]->data_format_id;
          		$array[$j] = $liste[$i]->data_format_name;
        	}
        	
        	$s = & $form->createElement('select',$label,$titre,$array);
      		//$s = & $form->createElement('select',$label,$titre);
      		//$s->loadArray($array);
      		return $s;
    	}
    	
 	}
?>
