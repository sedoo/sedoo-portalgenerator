<?php

require_once ("bd/bdConnect.php");
require_once ("bd/variable.php");
require_once ("bd/unit.php");

class param{
		
	var $var_id;
	var $unit_id;
	var $param_code;
	var $standard_name;

	var $var;
	var $unit;
	
	var $hasData = false;

	function new_param($tab) {
	        $this->var_id = $tab[0];
        	$this->unit_id = $tab[1];
		$this->param_code = $tab[2];
                $this->standard_name = $tab[3];
		if (isset($this->var_id) && !empty($this->var_id)){
 				$v = new variable;
 				$this->var = $v->getById($this->var_id);
 		}
 		
		if (isset($this->unit_id) && !empty($this->unit_id)){
 				$u = new unit;
 				$this->unit = $u->getById($this->unit_id);
 		}
		if (isset($tab[4]) && !empty($tab[4])){
			$this->hasData = true;
		}
	}	
	
	function getAll(){
 			$query = "select * from param order by param_code";
      		$bd = new bdConnect;
            $liste = array();
            if ($resultat = $bd->get_data($query)) {
            	for ($i=0; $i<count($resultat);$i++){
            		$liste[$i] = new param;
            		$liste[$i]->new_param($resultat[$i]);
            	}
            }
            return $liste;
 	}

	function getByProjects($projects){
		$query = "SELECT q1.*, ins_dats_id FROM (SELECT * FROM param) AS q1 LEFT JOIN (SELECT DISTINCT var_id,ins_dats_id FROM inserted_dataset JOIN dats_data USING (ins_dats_id) JOIN dats_proj USING (dats_id) JOIN data_availability USING (ins_dats_id) JOIN param USING (var_id) WHERE project_id in ($projects)) AS q2 USING (var_id) ORDER BY param_code";
		return $this->getByQuery($query);
	}
	
	function getById($id){
      		if (!isset($id) || empty($id))
        		return null;

      		$query = "SELECT * FROM param WHERE var_id = ".$id;
      		$bd = new bdConnect;
      		if ($resultat = $bd->get_data($query))
      		{
        		$var = new param;
        		$var->new_param($resultat[0]);
      		}
      		return $var;
    	}
 
	function getByGcmdId($ids){
 		$query = "SELECT * FROM param WHERE var_id in (SELECT var_id FROM variable WHERE gcmd_id in ($ids));";
 		return $this->getByQuery($query);
 	}

	function getByDatsId($id){
 		$query = "SELECT * FROM param WHERE var_id in (SELECT DISTINCT var_id FROM data_availability WHERE ins_dats_id IN (SELECT ins_dats_id FROM inserted_dataset WHERE ins_dats_id in (SELECT ins_dats_id FROM dats_data WHERE dats_id IN ($id))));";
 		return $this->getByQuery($query);
 	}


	function getByQuery($query){
			$bd = new bdConnect;
      		$liste = array();
      		if ($resultat = $bd->get_data($query)){
        		for ($i=0; $i<count($resultat);$i++){
          			$liste[$i] = new param;
            		$liste[$i]->new_param($resultat[$i]);
        		}
      		}
      		return $liste;
		}
	
	
}

?>
