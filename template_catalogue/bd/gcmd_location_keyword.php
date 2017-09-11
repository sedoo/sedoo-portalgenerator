<?php
require_once ("bd/bdConnect.php");
require_once ("bd/conf.php");
require_once ("bd/thesaurus.php");
require_once ("bd/project.php");


class gcmd_location_keyword {
	var $gcmd_loc_id;
	var $gcm_gcmd_id;
	var $gcmd_loc_name;
	var $gcmd_level;
	var $thesaurus_id;
	var $thesaurus;
	var $uid;
	var $gcmd_parent;
	var $enfants;


	function new_gcmd_location_keyword($tab) {
		$this->gcmd_loc_id = $tab [0];
		$this->gcm_gcmd_id = $tab [1];
		$this->gcmd_loc_name = $tab [2];
		$this->gcmd_level = $tab [3];
		$this->thesaurus_id = $tab [4];
		$this->uid = $tab [5];

		if (isset ( $this->gcm_gcmd_id ) && ! empty ( $this->gcm_gcmd_id )) {
			$this->gcmd_parent = $this->getById ( $this->gcm_gcmd_id );
		}
		
		if (isset($this->thesaurus_id) && !empty($this->thesaurus_id)){
			$thesaurus = new thesaurus();
			$this->thesaurus = $thesaurus->getById($this->thesaurus_id);
		}
	}
	function toString() {
		if (isset ( $this->gcmd_parent ) && ! empty ( $this->gcmd_parent )) {
			return $this->gcmd_parent->toString () . ' > ' . $this->gcmd_loc_name;
		} else
			return $this->gcmd_loc_name;
	}
	function getAll() {
		$query = "select * from gcmd_location_keyword order by gcmd_loc_name";
		return $this->getByQuery ( $query );
	}
	function getChildren($recursive = false) {
		$liste = array ();
		$this->readChildren ( $liste, $recursive );
		return $liste;
	}
	private function readChildren(&$liste, $recursive = false) {
		$query = "SELECT * FROM gcmd_location_keyword WHERE gcm_gcmd_id = $this->gcmd_id ORDER BY gcmd_loc_name";
		$tmp = $this->getByQuery ( $query );
		if ($recursive && isset ( $tmp )) {
			foreach ( $tmp as $child ) {
				$liste [] = $child;
				$child->readChildren ( $liste, $recursive );
			}
		}
	}
	function getByQuery($query) {
		$bd = new bdConnect ();
		$liste = array ();
		if ($resultat = $bd->get_data ( $query )) {
			for($i = 0; $i < count ( $resultat ); $i ++) {
				$liste [$i] = new gcmd_location_keyword ;
				$liste [$i]->new_gcmd_location_keyword ( $resultat [$i] );
			}
		}
		return $liste;
	}
	function getById($id) {
		if (! isset ( $id ) || empty ( $id ))
			return new gcmd_location_keyword();
		$gcmd = null;
		$query = "select * from gcmd_location_keyword where gcmd_loc_id = " . $id;
		$bd = new bdConnect ();
		if ($resultat = $bd->get_data ( $query )) {
			$gcmd = new gcmd_location_keyword ;
			$gcmd->new_gcmd_location_keyword ( $resultat [0] );
		}
		return $gcmd;
	}

	function chargeFormTest($form, $label, $titre) {
		$array_topic [0] = "-- Topic --";
		$array_topic [1] = "Topic 1";
		$array_topic [5] = "Topic 2";
		$array_categorie [0] [0] = "-- Term --";
		$array_categorie [1] [0] = "Term 11";
		$array_categorie [1] [8] = "Term 12";
		$array_categorie [5] [0] = "Term 21";
		$array_variable [0] [0] [0] = "-- Var_level1 --";
		$array_variable [1] [0] [0] = "Var 111";
		$array_variable [1] [1] [0] = "Var 121";
		$array_variable [1] [1] [1] = "Var 122";
		$array_variable [5] [0] [0] = "Var 211";
		$array_variable2 [0] [0] [0] [0] = "-- Var_level2 --";
		$array_variable2 [1] [1] [1] [0] = "Var 1221";

		$s = & $form->createElement ( 'hierselect', $label, $titre );
		$s->setOptions ( array (
				$array_topic,
				$array_categorie,
				$array_variable,
				$array_variable2
		) );
		return $s;
	}

	/*
	 * TODO Charger en mémoire la liste au lieu d'utiliser la base à chaque fois
	*/
	function chargeFormLoc($form, $label, $titre) {
		global $project_name;
		$array_topic [0] = "-- Level 1 --";
		$array_categorie [0] [0] = "-- Level 2 --";
		$array_variable [0] [0] [0] = "-- Level 3 --";

		$query = "select * from gcmd_location_keyword where gcmd_level = 2  order by gcmd_loc_name";
		$liste_topic = $this->getByQuery ( $query );


		for($i = 0; $i < count ( $liste_topic ); $i ++) {
			$j = $liste_topic [$i]->gcmd_loc_id;

			$array_topic [$j] = $liste_topic [$i]->gcmd_loc_name;

			$query2 = "select * from gcmd_location_keyword where gcm_gcmd_id = " . $j . " order by gcmd_loc_name";
			$liste_categ = $this->getByQuery ( $query2 );
			$array_categorie [$j] [0] = "-- Level 2 --";

			for($k = 0; $k < count ( $liste_categ ); $k ++) {

				$l = $liste_categ [$k]->gcmd_loc_id;
				$array_categorie [$j] [$l] = $liste_categ [$k]->gcmd_loc_name;

				$query3 = "select * from gcmd_location_keyword where gcm_gcmd_id = " . $l . " order by gcmd_loc_name";
				$liste_param = $this->getByQuery ( $query3 );
				$array_variable [$j] [$l] [0] = "-- Level 3 --";
				for($m = 0; $m < count ( $liste_param ); $m ++) {
					$n = $liste_param [$m]->gcmd_loc_id;
					$array_variable [$j] [$l] [$n] = $liste_param [$m]->gcmd_loc_name;
				}
			}
		}

		$s = & $form->createElement ( 'hierselect', $label, $titre );
		$s->setOptions ( array (
				$array_topic,
				$array_categorie,
				$array_variable,
		) );
		return $s;
	}
	//}
	

	function getByIds($ids)
	{
		if (!isset($ids) || empty($ids))
			return new gcmd_location_keyword;
		$query = "select * from gcmd_location_keyword where gcmd_level = 3  and gcmd_loc_id in (select gcmd_loc_id from proj_loc_keyword where project_id=".$ids.") order by gcmd_loc_name";
		return $this->getByQuery($query);
	}

	function getByLevel($level = 1,$parent = 0,$type = 0)
	{
		$where = "where place_level = $level";

		if ($parent > 0)
			$where .= " and pla_place_id = $parent";
		if ($type > 0)
			$where .= " and gcmd_loc_id = $type ";
			
		$query = "select * from place $where order by place_name";

		return $this->getByQuery($query);
	}




	function chargeFormLocLevels($form,$label,$titre){
		global $project_name;
		$array_type[0] = " -- Topic -- ";
		$array_lev1[0][0] = "";
		$array_lev2[0][0][0] = "";
		$gcmd = new gcmd_location_keyword();
		if (constant(strtoupper ( $project_name ) . '_SITES') != '' && constant(strtoupper ( $project_name ) . '_SITES') != null) {
			$types = $gcmd->getByIds ( constant(strtoupper ( $project_name ) . '_SITES' ));
			foreach ( $types as $type ) {
				print_r($type->gcmd_loc_name);
				$array_type [$type->gcmd_loc_id] = $type->gcmd_loc_name;
				$liste1 = $this->getByLevel ( 1, 0, $type->gcmd_loc_id );
				foreach ( $liste1 as $site1 ) {
					$array_lev1 [$type->gcmd_loc_id] [$site1->gcmd_loc_id] = $site1->gcmd_loc_name;
					$array_lev2 [$type->gcmd_loc_id] [$site1->gcmd_loc_id] [0] = '';
					$liste2 = $this->getByLevel ( 2, $site1->gcmd_loc_id );
					foreach ( $liste2 as $site2 ) {
						$array_lev2 [$type->gcmd_loc_id] [$site1->gcmd_loc_id] [$site2->gcmd_loc_id] = $site2->gcmd_loc_name;
						
					}
				}
			}
			$s = & $form->createElement ( 'hierselect', $label, $titre, null, '<br>' );
			$s->setOptions ( array (
					$array_type,
					$array_lev1,
					$array_lev2,
			) );
			return $s;
		}
	}
}
?>