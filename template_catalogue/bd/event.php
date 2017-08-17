<?php
/*
 * Created on 12 juil. 2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 require_once("bd/bdConnect.php");
 
	class event
	{
		var $event_id;
		var $event_name;
		var $event_date_begin;
		var $event_date_end;
		
		function new_event($tab)
		{
			$this->event_id = $tab[0];
			$this->event_name = $tab[1];
			$this->event_date_begin = $tab[2];
			$this->event_date_end = $tab[3];
		}
		
		function getAll()
 		{
 			$query = "select * from event order by event_date_begin";
      		$bd = new bdConnect;
      		$liste = array();
      		if ($resultat = $bd->get_data($query))
      		{
        		for ($i=0; $i<count($resultat);$i++)
        		{
          			$liste[$i] = new event;
          			$liste[$i]->new_event($resultat[$i]);
        		}
      		}
      		return $liste;
 		}
 		
 		function getById($id){
      		if (!isset($id) || empty($id))
        		return new event;

      		$query = "select * from event where event_id = ".$id;
      		$bd = new bdConnect;
      		if ($resultat = $bd->get_data($query))
      		{
        		$per = new event;
        		$per->new_event($resultat[0]);
      		}
      		return $per;
    	}
    }	
?>
