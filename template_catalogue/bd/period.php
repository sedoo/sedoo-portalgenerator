<?php
/*
 * Created on 12 juil. 2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 require_once("bd/bdConnect.php");
 require_once("scripts/filtreProjets.php");
 
	class period
	{
		var $period_id;
		var $period_name;
		var $period_begin;
		var $period_end;
		
		function new_period($tab)
		{
			$this->period_id = $tab[0];
			$this->period_name = $tab[1];
			$this->period_begin = $tab[2];
			$this->period_end = $tab[3];
		}
		
		function getAll()
 		{
 			$query = "select * from period order by period_id";
      		$bd = new bdConnect;
      		$liste = array();
      		if ($resultat = $bd->get_data($query))
      		{
        		for ($i=0; $i<count($resultat);$i++)
        		{
          			$liste[$i] = new period;
          			$liste[$i]->new_period($resultat[$i]);
        		}
      		}
      		return $liste;
 		}
 		
 		function getById($id)
    	{
      		if (!isset($id) || empty($id))
        		return new country;

      		$query = "select * from period where period_id = ".$id;
      		$bd = new bdConnect;
      		if ($resultat = $bd->get_data($query))
      		{
        		$per = new period;
        		$per->new_period($resultat[0]);
      		}
      		return $per;
    	}
    	
    	function getByQuery($query)
    	{
      		$bd = new bdConnect;
      		$liste = array();
      		if ($resultat = $bd->get_data($query))
      		{
        		for ($i=0; $i<count($resultat);$i++)
        		{
          			$liste[$i] = new period;
          			$liste[$i]->new_period($resultat[$i]);
        		}
      		}
      		return $liste;
    	}

	function getByProject($projectName){
		$query = "SELECT * FROM period WHERE period_id IN (SELECT period_id FROM period_project WHERE project_id in (".get_filtre_projets($projectName).")) order by period_id";
		return $this->getByQuery($query);
	}

    	function existe()
    	{
        	$query = "select * from period where " .
        			"lower(period_name) = lower('".(str_replace("'","\'",$this->period_name))."')";
        	$bd = new bdConnect;
        	if ($resultat = $bd->get_data($query))
        	{
          		$this->period_id = $resultat[0][0];
          		return true;
        	}
        	return false;
    	}

    	function idExiste()
    	{
        	$query = "select * from period where period_id = ".$this->period_id;
        	$bd = new bdConnect;
        	if ($resultat = $bd->get_data($query))
        	{
          		$this->period_name = $resultat[0][1];
          		return true;
        	}
        	return false;
    	}
    	
    	//creer element select pour formulaire
    	function chargeForm($form,$label,$titre,$projectName = MainProject)
    	{

			$liste = $this->getByProject($projectName);
      		$array[0] = "";
      		for ($i = 0; $i < count($liste); $i++)
        	{
          		$j = $liste[$i]->period_id;
          		$array[$j] = $liste[$i]->period_name;
        	}
      		$s = & $form->createElement('select',$label,$titre,$array,array('style' => 'width:200px;'));
      		return $s;
    	}
    	
    	function chargeFormWithDates($form,$label,$titre,$projectName = MainProject)
    	{
			$liste = $this->getByProject($projectName);
    		$array[0] = "";
    		for ($i = 0; $i < count($liste); $i++)
    		{
    			$j = $liste[$i]->period_id;
    			$array[$j] = $liste[$i]->period_name;
    		}
    		$boxesNames = "['date_begin','date_end']";
    		$columnsNames = "['period_begin','period_end']";
    		$s = & $form->createElement('select',$label,$titre,$array,array('onchange' => "fillBoxes('".$label."',".$boxesNames.",'period',".$columnsNames.");"));
    		return $s;
    	}
	}
?>
