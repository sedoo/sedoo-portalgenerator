<?php
/*
 * Created on 8 juil. 2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 	require_once("bd/bdConnect.php");
 	
 	class type_journal
 	{
 		var $id;
 		var $name;
 		
 		function new_type_journal($tab)
 		{
 			$this->id = $tab[0];
 			$this->name = $tab[1];
 		}
 			
 		
 		function getAll(){
 			$query = "select * from type_journal order by type_journal_name";
      		$bd = new bdConnect;
      		$liste = array();
      		if ($resultat = $bd->get_data($query))
      		{
        		for ($i=0; $i<count($resultat);$i++)
        		{
          			$liste[$i] = new type_journal;
          			$liste[$i]->new_type_journal($resultat[$i]);
        		}
      		}
      		return $liste;
 		}
 		
 		function getById($id){
      		if (!isset($id) || empty($id))
        		return new type_journal;

      		$query = "select * from type_journal where type_journal_id = ".$id;
      		$bd = new bdConnect;
      		if ($resultat = $bd->get_data($query))
      		{
        		$type = new type_journal;
          		$type->new_type_journal($resultat[0]);
      		}
      		return $type;
    	}
    
	function getByIds($ids){
                if (!isset($ids) || empty($ids))
                        return array();

		$tjIds = implode(',',$ids);
                $query = "select * from type_journal where type_journal_id in ($tjIds)";
		return $this->getByQuery($query);
        }

	
	static function getIdByName($name){
      		if (!isset($name) || empty($name))
        		return 0;

      		$query = "select * from type_journal where type_journal_name ilike '$name'";
      		$bd = new bdConnect;
      		if ($resultat = $bd->get_data($query))
      		{
        		$type = new type_journal;
          		$type->new_type_journal($resultat[0]);
      		}
      		return $type->id;
    	}
    	
    	function getByQuery($query)
    	{
      		$bd = new bdConnect;
      		$liste = array();
      		if ($resultat = $bd->get_data($query))
      		{
        		for ($i=0; $i<count($resultat);$i++)
        		{
          			$liste[$i] = new type_journal;
          			$liste[$i]->new_type_journal($resultat[$i]);
        		}
      		}
      		return $liste;
    	}

    	//creer element select pour formulaire
    	function chargeForm($form,$label,$titre,$ids)
    	{

      		//$liste = $this->getAll();
		$liste = $this->getByIds($ids);
      		//$array[0] = "";
      		for ($i = 0; $i < count($liste); $i++)
        	{
          		$j = $liste[$i]->id;
          		$array[$j] = $liste[$i]->name;
        	}
      		$s = & $form->createElement('select',$label,$titre);
      		$s->loadArray($array);
      		return $s;
    	}
 	}
?>
