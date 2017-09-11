<?php
/*
 * Created on 8 juil. 2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 	require_once("bd/bdConnect.php");
 	
 	class dataset_type{
 		
 		const TYPE_SATELLITE = 'SATELLITE';
 		const TYPE_MODEL = 'MODEL';
 		const TYPE_VALUE_ADDED = 'VALUE-ADDED DATASET';
 		const TYPE_INSTRUMENT = 'INSTRUMENT';
 		const TYPE_MULTI_INSTRU = 'MULTI-INSTRUMENT';
 		
 		var $dats_type_id;
 		var $dats_type_title;
 		var $dats_type_desc;
 		
 		function new_dataset_type($tab)
 		{
 			$this->dats_type_id = $tab[0];
 			$this->dats_type_title = $tab[1];
 			$this->dats_type_desc = $tab[2];
 		}
 		
 		function getAll()
 		{
 			$query = "select * from dataset_type order by dats_type_title";
      		$bd = new bdConnect;
      		$liste = array();
      		if ($resultat = $bd->get_data($query))
      		{
        		for ($i=0; $i<count($resultat);$i++)
        		{
          			$liste[$i] = new dataset_type;
          			$liste[$i]->new_dataset_type($resultat[$i]);
        		}
      		}
      		return $liste;
 		}
 		
 		function getById($id)
    	{
      		if (!isset($id) || empty($id))
        		return new dataset_type;

      		$query = "select * from dataset_type where dats_type_id = ".$id;
      		$bd = new bdConnect;
      		if ($resultat = $bd->get_data($query))
      		{
        		$dataset_type = new dataset_type;
        		$dataset_type->new_dataset_type($resultat[0]);
      		}
      		return $dataset_type;
    	}
    	
    	function getByType($name){
      		if (!isset($name) || empty($name))
        		return new dataset_type;

      		$query = "select * from dataset_type where lower(dats_type_title) = lower('".$name."')";
      		$bd = new bdConnect;
      		if ($resultat = $bd->get_data($query)){
        		$dataset_type = new dataset_type;
        		$dataset_type->new_dataset_type($resultat[0]);
      		}
      		return $dataset_type;
    	}
    	
    	function getByQuery($query)
    	{
      		$bd = new bdConnect;
      		$liste = array();
      		if ($resultat = $bd->get_data($query))
      		{
        		for ($i=0; $i<count($resultat);$i++)
        		{
          			$liste[$i] = new dataset_type;
          			$liste[$i]->new_dataset_type($resultat[$i]);
        		}
      		}
      		return $liste;
    	}

    	function existe()
    	{
        	$query = "SELECT * FROM dataset_type WHERE " .
        			"LOWER(dats_type_title) = LOWER('".(str_replace("'","\'",$this->dats_type_title))."')";
        	$bd = new bdConnect;
        	if ($resultat = $bd->get_data($query))
        	{
          		$this->dats_type_id = $resultat[0][0];
          		$this->dats_type_desc = $resultat[0][2];
          		return true;
        	}
        	return false;
    	}

    	function idExiste()
    	{
        	$query = "SELECT * FROM dataset_type WHERE dats_type_id = ".$this->dats_type_id;

        	$bd = new bdConnect;
        	if ($resultat = $bd->get_data($query))
        	{
          		$this->dats_type_title = $resultat[0][1];
          		$this->dats_type_desc = $resultat[0][2];
          		return true;
        	}
        	return false;
    	}
    	
    	function insert(& $bd)
    	{
    		$query = "INSERT INTO dataset_type (dats_type_title,dats_type_desc) " .
     	 			"VALUES ('".str_replace("'","\'",$this->dats_type_title)."'" .
     	 					",'".str_replace("'","\'",$this->dats_type_desc)."')";

    		$bd->exec($query);

    		$this->dats_type_id = $bd->getLastId('dataset_type_dats_type_id_seq');

    		return $this->dats_type_id;

    	}

    	//creer element select pour formulaire
    	function chargeForm($form,$label,$titre)
    	{

      		$liste = $this->getAll();
      		$array[0] = "";
      		for ($i = 0; $i < count($liste); $i++)
        	{
          		$j = $liste[$i]->dats_type_id;
          		$array[$j] = $liste[$i]->dats_type_desc;
        	}
      		$s = & $form->createElement('select',$label,$titre);
      		$s->loadArray($array);
      		return $s;
    	}
 	}
?>
