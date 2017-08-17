<?php

require_once("bd/bdConnect.php");
require_once("bd/dataset.php");

class inserted_dataset {

	var $ins_dats_id;
	var $ins_dats_name;
	var $date_insertion;
	var $date_last_update;

	var $datasets;

	var $date_min;
	var $date_max;

	var $vars;
	var $places;

	function new_inserted_dataset($tab,$varId = null,$placeId = null) {
		$this->ins_dats_id = $tab[0];
        $this->ins_dats_name = $tab[1];
        $this->date_insertion = $tab[2];
        $this->date_last_update = $tab[3];

		$this->get_datasets();
		$this->get_periode($varId,$placeId);
		$this->get_variables($placeId);
		$this->get_places($varId);
	}

	function getAll() {
                $query = "select * from inserted_dataset order by date_last_update desc;";
                return $this->getByQuery($query);
	}

	function getByProjects($projectIds) {
		$query = "SELECT inserted_dataset.* FROM inserted_dataset JOIN dats_data USING (ins_dats_id) JOIN dats_proj USING (dats_id) WHERE project_id in ($projectIds) ORDER BY date_last_update DESC";
                return $this->getByQuery($query);
	}

	function getByDatsId($id) {
		 $query = "SELECT * FROM inserted_dataset WHERE ins_dats_id in (SELECT ins_dats_id FROM dats_data WHERE dats_id = $id);";
         return $this->getByQuery($query);
	}

	function existsForDatsId($id) {
		 $query = "SELECT * FROM dats_data WHERE dats_id = $id LIMIT 1";
//		echo $query.'<br>';
		 $bd = new bdConnect;
		 $resultat = $bd->get_data($query);
		 return ($resultat = $bd->get_data($query));
	}
	
	function getByQuery($query) {
                $bd = new bdConnect;
                $liste = array();
                if ($resultat = $bd->get_data($query)) {
                        for ($i=0; $i<count($resultat);$i++)
                        {
                                $liste[$i] = new inserted_dataset;
                                $liste[$i]->new_inserted_dataset($resultat[$i]);
                        }
                }
                return $liste;
	}
	
	function getByVarId($varId){
		$query = "SELECT * FROM inserted_dataset WHERE ins_dats_id in (SELECT DISTINCT ins_dats_id FROM data_availability WHERE var_id = $varId);";
		//echo $query.'<br>';
		$bd = new bdConnect;
		$liste = array();
		if ($resultat = $bd->get_data($query)) {
			for ($i=0; $i<count($resultat);$i++)
			{
				$liste[$i] = new inserted_dataset;
				$liste[$i]->new_inserted_dataset($resultat[$i],$varId);
			}
		}
		return $liste;
		
	}
	
	function getById($id,$varId = null,$placeId = null) {
                if (!isset($id) || empty($id))
                        return null;

                $query = "select * from inserted_dataset where ins_dats_id = ".$id;
                $bd = new bdConnect;
                if ($resultat = $bd->get_data($query)) {
                        $dts = new inserted_dataset;
                        $dts->new_inserted_dataset($resultat[0],$varId,$placeId);
                }
                return $dts;
        }
        
      
	private function get_datasets(){
		$query = "select * from dataset where dats_id in " .
                                "(select distinct dats_id from dats_data where ins_dats_id = ".$this->ins_dats_id.")";
                $d = new dataset;
                $this->datasets = $d->getByQuery($query);
	}

	private function get_periode($varId = null, $placeId = null){
		$wherePlace = '';
                if ($placeId)
                        $wherePlace = "and place_id = $placeId";
		$whereVar = '';
                if ($varId)
                        $whereVar = "and var_id = $varId";

		$query = "select min(date_begin),max(date_end) from data_availability  where ins_dats_id = $this->ins_dats_id $wherePlace $whereVar;";
		$bd = new bdConnect;
		if ($resultat = $bd->get_data($query)) {
			if ($resultat[0]){
				$this->date_min = $resultat[0][0];
				$this->date_max = $resultat[0][1];
			}
        }
	}

	private function get_variables($placeId = null){
		$wherePlace = '';
		if ($placeId)
			$wherePlace = "and place_id = $placeId";

		$query = "select * from variable where var_id in (select distinct var_id from data_availability where ins_dats_id = $this->ins_dats_id $wherePlace);";
		//echo $query;
		$v = new variable;
		$this->vars = $v->getByQuery($query);
	}

	private function get_places($varId = null){
		$whereVar = '';
                if ($varId)
                        $whereVar = "and var_id = $varId";

                $query = "select * from place where place_id in (select distinct place_id from data_availability  where ins_dats_id = $this->ins_dats_id $whereVar) order by place_name";
		//echo $query;
                $p = new place;
                $this->places = $p->getByQuery($query);
        }
}

?>
