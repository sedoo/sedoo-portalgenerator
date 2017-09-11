<?php
/*
 * Created on 8 juil. 2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 	require_once("bd/bdConnect.php");
 	require_once("bd/dataset.php");
 	require_once("bd/role.php");
 	
 	class dats_role
 	{
 		var $dats_id;
 		var $role_id;
 		var $dataset;
 		var $role;
 		
 		function new_dats_role($tab)
 		{
 			$this->dats_id = $tab[0];
 			$this->role_id = $tab[1];
 			if (isset($this->dats_id) && !empty($this->dats_id))
 			{
 				$dts = new dataset;
 				$this->dataset = $dts->getById($this->dats_id);
 			}
 			if (isset($this->role_id) && !empty($this->role_id))
 			{
 				$role = new role;
 				$this->role = $role->getById($this->role_id);
 			}
 		}
 		
 		function getAll()
 		{
 			$query = "select * from dats_role order by dats_id";
      		return $this->getByQuery($query);
 		}

	function getByDataset($datsId){
                        $query = "select * from dats_role where dats_id = $datsId";
                return $this->getByQuery($query);
        }
 	
	static function deleteRoles(&$bd,$dats_id){
                $query = "delete from dats_role where dats_id = $dats_id;";
                echo $query.'<br>';
                $bd->exec($query);
                return true;
        }	
    	
    	function getByQuery($query)
    	{
      		$bd = new bdConnect;
      		$liste = array();
      		if ($resultat = $bd->get_data($query))
      		{
        		for ($i=0; $i<count($resultat);$i++)
        		{
          			$liste[$i] = new dats_role;
          			$liste[$i]->new_dats_role($resultat[$i]);
        		}
      		}
      		return $liste;
    	}

    	function existe()
    	{
        	$query = "select * from dats_role where " .
        			"dats_id = ".$this->dats_id." and role_id = ".$this->role_id;
        	$bd = new bdConnect;
        	if ($resultat = $bd->get_data($query))
        	{
          		$this->new_dats_role($resultat[0]);
          		return true;
        	}
        	return false;
    	}
    	
    	function insert()
    	{
     	 	$query = "insert into dats_role (dats_id,role_id) " .
     	 			"values (".$this->dats_id.",".$this->role_id.")";
      		$bd = new bdConnect;
      		$bd->insert($query);
    	}

	static function addDatsRole(&$bd,$datsId,$roleId){
                $queryExists = "select * from dats_role where role_id = $roleId and dats_id = $datsId;";
                if ($resultat = $bd->get_data2($queryExists)){
                        echo 'dats_role déjà présente<br>';
                        return false;
                }else{
			$query = "insert into dats_role (dats_id,role_id) values ($datsId,$roleId);";
                        echo $query.'<br>';
                        $bd->exec($query);
                        return true;
                }
        }

 	}
?>
