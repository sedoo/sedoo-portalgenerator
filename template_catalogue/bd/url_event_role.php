<?php
/*
 * Created on 8 juil. 2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 	require_once("bd/bdConnect.php");
 	require_once("bd/url_event.php");
 	require_once("bd/role.php");
 	
 	class url_event_role
 	{
 		var $url_event_id;
 		var $role_id;
 		var $url_event;
 		var $role;
 		
 		function new_url_event_role($tab)
 		{
 			$this->url_event_id = $tab[0];
 			$this->role_id = $tab[1];
 			if (isset($this->url_event_id) && !empty($this->url_event_id))
 			{
 				$dts = new url_event;
 				$this->url_event = $dts->getById($this->url_event_id);
 			}
 			if (isset($this->role_id) && !empty($this->role_id))
 			{
 				$role = new role;
 				$this->role = $role->getById($this->role_id);
 			}
 		}
 		
 		function getAll()
 		{
 			$query = "select * from url_event_role order by url_event_id";
      		return $this->getByQuery($query);
 		}

	function getByUrlEvent($id){
                        $query = "select * from url_event_role where url_event_id = $id";
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
          			$liste[$i] = new url_event_role;
          			$liste[$i]->new_url_event_role($resultat[$i]);
        		}
      		}
      		return $liste;
    	}
}
?>
