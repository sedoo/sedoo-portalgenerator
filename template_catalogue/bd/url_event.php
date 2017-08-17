<?php
/*
 * Created on 8 juil. 2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 	require_once("bd/bdConnect.php");
 	
 	class url_event{
 		
 		var $url_event_id;
 		var $event_id;
 		var $url_event;
 		var $url_descript;
		var $event; 		
 		
 		function new_url_event($tab)
 		{
 			$this->url_event_id = $tab[0];
 			$this->event_id = $tab[1];
 			$this->url_event = $tab[2];
 			$this->url_descript = $tab[3];
			if (isset($this->event_id) && !empty($this->event_id)) {
                                $dts = new event;
                                $this->event = $dts->getById($this->event_id);
                        }
 		}
 		
 		function getAll()
 		{
 			$query = "select * from url_event order by event_id";
      		return $this->getByQuery($query);
 		}

		function getById($id){
	                if (!isset($id) || empty($id))
       		                return new url_event;
	
			$query = "select * from url_event where url_event_id = $id";
                
			$bd = new bdConnect;
	                if ($resultat = $bd->get_data($query)) {
	                        $per = new url_event;
        	                $per->new_url_event($resultat[0]);
                	}
                	return $per;
 		
 		}
 		
/* 		function getHttpByDataset($datsId){
 			$query = "select * from url where dats_id = $datsId and url_type = 'http'"; 			
      		return $this->getByQuery($query);
 		}
    
	function getFtpByDataset($datsId){
 			$query = "select * from url where dats_id = $datsId and url_type = 'ftp'"; 			
      		return $this->getByQuery($query);
 		}
*/	
    	function getByQuery($query)
    	{
      		$bd = new bdConnect;
      		$liste = array();
      		if ($resultat = $bd->get_data($query))
      		{
        		for ($i=0; $i<count($resultat);$i++)
        		{
          			$liste[$i] = new url_event;
          			$liste[$i]->new_url_event($resultat[$i]);
        		}
      		}
      		return $liste;
    	}

    	 
    	
    	
 	}
?>
