<?php
require_once ("bd/bdConnect.php");
require_once ("bd/project.php");
require_once ("bd/gcmd_location_keyword.php");

class proj_loc_keyword{
	
	var $gcmd_loc_id;
	var $project_id;
	var $project;
	var $gcmd_location_keyword;
	
	
	function new_proj_loc_keyword($tab){
		$this->gcmd_loc_id = $tab[0];
		$this->project_id = $tab[1];
		
		if (isset($this->gcmd_loc_id) && !empty($this->gcmd_loc_id)){
			$loc = new gcmd_location_keyword();
			$this->gcmd_location_keyword = $loc->getById($this->gcmd_loc_id);
		}
		
		if (isset($this->project_id) && !empty($this->project_id)){
			$proj = new project();
			$this->project = $proj->getById($this->project_id);
		}
	}
	
	
	function getByQuery($query) {
		$bd = new bdConnect ();
		$liste = array ();
		if ($resultat = $bd->get_data ( $query )) {
			for($i = 0; $i < count ( $resultat ); $i ++) {
				$liste [$i] = new proj_loc_keyword ();
				$liste [$i]->new_proj_loc_keyword ( $resultat [$i] );
			}
		}
		return $liste;
	}
	
	function getAll(){
		$query = "select * from proj_loc_keyword order by project_id";
		return $this->getByQuery($query);
	}
	
	function existe() {
		$query = "select * from proj_loc_keyword where " . "gcmd_loc_id = " . $this->gcmd_loc_id . " and project_id = " . $this->project_id;
		$bd = new bdConnect ();
		if ($resultat = $bd->get_data ( $query )) {
			$this->new_dats_loc ( $resultat [0] );
			return true;
		}
		return false;
	}
	
	function insert(& $bd) {
		$query = "insert into proj_loc_keyword (gcmd_loc_id,project_id) " . "values (" . $this->gcmd_loc_id . "," . $this->project_id . ")";
		$bd->exec ( $query );
	}
}