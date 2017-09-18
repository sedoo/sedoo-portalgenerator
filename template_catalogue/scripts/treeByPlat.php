<?php
require_once ('bd/bdConnect.php');
require_once ('bd/gcmd_plateform_keyword.php');
require_once ('bd/dataset.php');
require_once ('TreeMenu.php');
require_once ('filtreProjets.php');
require_once ('lstDataUtils.php');
class treeByPlat {
	var $treeMenu;
	var $withDataOnly;
	var $dataType;
	var $filter;
	var $search;
	var $project_name;
	var $projects;
	var $cptDats;
	var $datsSat;
	var $EmptyTab;
	function treeByPlat($withDataOnly = false, $dataType = 0, $filter = null, $search = 0) {
		$this->withDataOnly = $withDataOnly;
		$this->dataType = $dataType;
		$this->filter = $filter;
		$this->search = $search;
	}
	function setFilter($filter) {
		$this->filter = $filter;
	}
	function isEmpty() {
		return $this->cptDats == 0;
	}
	private function addDatasets(&$node, $platId, $siteId = 0) {
		if ($this->dataType > 0) {
			$whereDataType = "and dats_type_id = $this->dataType";
		} else if ($this->dataType == - 1) {
			$whereDataType = '';
		} else {
			$whereDataType = 'and dats_type_id is null';
		}
		
		if ($this->withDataOnly)
			$whereDataOnly = 'and dats_id in (select distinct dats_id from url)';
		else
			$whereDataOnly = '';
		
		if ($this->filter)
			$whereFilter = "and ($this->filter)";
		else
			$whereFilter = '';
		
		if ($siteId > 0)
			$query_dp = "select distinct dats_id from dats_place where place_id in (select place_id from place where place_id = $siteId or (pla_place_id = $siteId and place_level is null))";
		else
			$query_dp = "select distinct dats_id from dats_place where place_id in (select place_id from place where gcmd_plat_id = $platId and (pla_place_id is null or pla_place_id not in (select place_id from place where place_level is not null and gcmd_plat_id in ($platId,14))) and place_level is null)";
		
		$query = "select dats_id, dats_title from dataset left join dats_type using (dats_id) where dats_id in (select distinct dats_id from dats_proj where project_id in ($this->projects)) and dats_id in ($query_dp) and is_requested is null $whereDataOnly $whereDataType $whereFilter order by dats_title";
				
		$dts = new dataset ();
		$dts_list = $dts->getOnlyTitles ( $query );
		
		$cptLocal = 0;
		foreach ( $dts_list as $dt ) {
			if ($this->dataType == 1) {
				if (! in_array ( $dt->dats_id, $this->datsSat )) {
					addDataset ( $node, $dt, $this->project_name, $this->search );
					$this->datsSat [] = $dt->dats_id;
					$cptLocal ++;
				}
			} else {
				addDataset ( $node, $dt, $this->project_name, $this->search );
				$cptLocal ++;
			}
			
			$this->cptDats ++;
		}

		return $cptLocal;
	}
	
	function isEmptyTypeTab() {
		$gcmd = new gcmd_plateform_keyword ();
		$query = 
		"SELECT * 
		FROM gcmd_plateform_keyword 
		WHERE gcmd_plat_id != 1 
		AND gcmd_plat_id != 23 
		AND ( gcmd_plat_id IN (
			SELECT DISTINCT gcmd_plat_id FROM place WHERE place_id IN (
				SELECT DISTINCT place_id FROM dats_place WHERE dats_id IN (
					SELECT DISTINCT dats_id FROM dats_proj WHERE project_id IN ($this->projects))))) 
		OR gcmd_plat_id IN (15,16,22,14)
		ORDER BY gcmd_plat_name";
		$plat_list = $gcmd->getByQuery ( $query );
		foreach ( $plat_list as $plat ) {
			$ids [] = $plat->gcmd_plat_id;
			$p = new place ();
			if (stripos ( $gcmd->gcmd_plat_name, 'buoys' ) === false)
				$sites1 = $p->getByLevel ( 1, 0, $plat->gcmd_plat_id );
			else
				$sites1 = $p->getByLevel ( 3, 0, $plat->gcmd_plat_id );
			foreach ( $sites1 as $site1 ) {
				$site1_ids [] = $site1->place_id;
				$sites2 = $p->getChildrenSites ( $site1->place_id );
				foreach ( $sites2 as $site2 ) {
					$site2_ids [] = $site2->place_id;
					$sites3 = $p->getChildrenSites ( $site2->place_id );
					foreach ( $sites3 as $site3 ) {
						$site3_ids [] = $site3->place_id;
						$sites4 = $p->getChildrenSites ( $site3->place_id );
						foreach ( $sites4 as $site4 ) {
							$site4_ids [] = $site4->place_id;
						}
					}
				}
			}
		}
		if (isset ( $site1_ids ) && ! empty ( $site1_ids ))
			$sites_ids = implode ( ",", $site1_ids ) . ',';
		if (isset ( $site2_ids ) && ! empty ( $site2_ids ))
			$sites_ids .= implode ( ",", $site2_ids ) . ',';
		if (isset ( $site3_ids ) && ! empty ( $site3_ids ))
			$sites_ids .= implode ( ",", $site3_ids ) . ',';
		if (isset ( $site4_ids ) && ! empty ( $site4_ids ))
			$sites_ids .= implode ( ",", $site4_ids );
		$plat_ids = implode ( ",", $ids );
		$plat_ids = rtrim ( $plat_ids, "," );
		$sites_ids = rtrim ( $sites_ids, "," );
		$dats_type_ids = array (
				0,
				1,
				2,
				3 
		);
		
		foreach ( $dats_type_ids as $type_id ) {
			if ($type_id > 0)
				$whereDataType = "and dats_type_id = $type_id ";
			else
				$whereDataType = "and dats_type_id is null";
			if ($this->withDataOnly)
				$whereDataOnly = 'and dats_id in (select distinct dats_id from url)';
			else
				$whereDataOnly = '';
			if ($this->filter)
				$whereFilter = "and ($this->filter)";
			else
				$whereFilter = '';
			
			$query_dp1 = "select distinct dats_id from dats_place where place_id in (select place_id from place where place_id in ( $sites_ids ) or (pla_place_id in ( $sites_ids ) and place_level is null))";
			$query_dp2 = "select distinct dats_id from dats_place where place_id in (select place_id from place where gcmd_plat_id in ($plat_ids) and (pla_place_id is null or pla_place_id not in (select place_id from place where place_level is not null and gcmd_plat_id in ($plat_ids,14))) and place_level is null)";
			$query = "select dats_id, dats_title from dataset left join dats_type using (dats_id) where dats_id in (select distinct dats_id from dats_proj where project_id in ($this->projects)) and ( dats_id in ($query_dp1) or dats_id in ($query_dp2) ) and is_requested is null $whereDataOnly $whereDataType $whereFilter order by dats_title";
			$dts = new dataset ();
			$dts_list = $dts->getOnlyTitles ( $query );
			if (isset ( $dts_list ) && ! empty ( $dts_list )) {
				if ($type_id == 0) {
					$this->EmptyTab [0] = false;
				} elseif ($type_id == 1) {
					$this->EmptyTab [1] = false;
				} elseif ($type_id == 2) {
					$this->EmptyTab [2] = false;
				} elseif ($type_id == 3) {
					$this->EmptyTab [3] = false;
				}
			} else {
				if ($type_id == 0) {
					$this->EmptyTab [0] = true;
				} elseif ($type_id == 1) {
					$this->EmptyTab [1] = true;
				} elseif ($type_id == 2) {
					$this->EmptyTab [2] = true;
				} elseif ($type_id == 3) {
					$this->EmptyTab [3] = true;
				}
			}
			$dts_list = null;
		}
	}
	
	function addOthers(&$parent) {
		$node = new HTML_TreeNode ( array (
				'text' => 'Other' 
		) );
		if ($this->dataType > 0) {
			$whereDataType = "AND dats_type_id = $this->dataType";
		} else if ($this->dataType == - 1) {
			$whereDataType = '';
		} else {
			$whereDataType = 'AND dats_type_id is null';
		}
		if ($this->filter)
			$whereFilter = "AND ($this->filter)";
		else
			$whereFilter = '';
		
		$query = "SELECT dats_id,dats_title FROM dataset LEFT JOIN dats_type USING (dats_id) WHERE dats_id in (select distinct dats_id from dats_proj where project_id in ($this->projects)) AND dats_id not in (select distinct dats_id from dats_place) AND is_requested is null $whereDataType $whereFilter order by dats_title";
		$dts = new dataset ();
		$dts_list = $dts->getOnlyTitles ( $query );
		foreach ( $dts_list as $dt ) {
			addDataset ( $node, $dt, $projectName );
			$this->cptDats ++;
		}
		if (count ( $dts_list ) > 0) {
			$parent->addItem ( $node );
		} 
	}
	function display($filterData = false) {
		$this->treeMenu->printMenu ( array (
				'filterData' => $filterData 
		) );
	}
	function build($withOthers = true) {
		$this->cptDats = 0;
		$gcmd = new gcmd_plateform_keyword ();
		$query = 'SELECT * FROM gcmd_plateform_keyword WHERE gcmd_plat_id != 1 AND gcmd_plat_id != 23 ' . ' AND ( gcmd_plat_id IN (SELECT distinct gcmd_plat_id FROM place where place_id in (' . 'SELECT distinct place_id FROM dats_place WHERE dats_id IN (' . "SELECT distinct dats_id FROM dats_proj where project_id in ($this->projects)))) or gcmd_plat_id in (15,16,22,14) )" . ' ORDER BY gcmd_plat_name';
		$plat_list = $gcmd->getByQuery ( $query );
		$tree = new HTML_TreeMenu ();
		foreach ( $plat_list as $plat ) {
			$racine = new HTML_TreeNode ( array (
					'text' => '<font style="font-size:110%;">' . $plat->gcmd_plat_name . '</font>' 
			) );
			$cpt = $this->addPlatformsFromType ( $racine, $plat );
			if ($cpt > 0) {
				$tree->addItem ( clone $racine );
			}
		}
		if ($withOthers) {
			$this->addOthers ( $tree );
		}
		$this->treeMenu = new HTML_TreeMenu_DHTML ( $tree, array (
				'images' => '/scripts/images',
				'defaultClass' => 'treeMenuDefault' 
		) );
	}
	private function addPlatformsFromType(&$root, $gcmd) {
		$platId = $gcmd->gcmd_plat_id;
		
		$p = new place ();
		if (stripos ( $gcmd->gcmd_plat_name, 'buoys' ) === false)
			$sites1 = $p->getByLevel ( 1, 0, $platId );
		else
			$sites1 = $p->getByLevel ( 3, 0, $platId );
		
		$cpt0 = 0;
		foreach ( $sites1 as $site1 ) {
			$node1 = new HTML_TreeNode ( array (
					'text' => '<font style="font-size:110%;">' . $site1->place_name . '</font>',
					'expanded' => 'false' 
			) );
			$cpt1 = 0;
			$sites2 = $p->getChildrenSites ( $site1->place_id );
			foreach ( $sites2 as $site2 ) {
				$node2 = new HTML_TreeNode ( array (
						'text' => '<font style="font-size:110%;">' . $site2->place_name . '</font>' 
				) );
				$cpt2 = 0;
				$sites3 = $p->getChildrenSites ( $site2->place_id );
				foreach ( $sites3 as $site3 ) {
					$node3 = new HTML_TreeNode ( array (
							'text' => '<font style="font-size:110%;">' . $site3->place_name . '</font>' 
					) );
					$cpt3 = 0;
					$sites4 = $p->getChildrenSites ( $site3->place_id );
					foreach ( $sites4 as $site4 ) {
						$node4 = new HTML_TreeNode ( array (
								'text' => '<font style="font-size:110%;">' . $site4->place_name . '</font>' 
						) );
						if ($cpt4 > 0) {
							$cpt3 ++;
							$node3->addItem ( clone $node4 );
						}
						$cpt4 = $this->addDatasets ( $node4, $platId, $site4->place_id );
					}
					$cpt3 += $this->addDatasets ( $node3, $platId, $site3->place_id );
					if ($cpt3 > 0) {
						$cpt2 ++;
						$node2->addItem ( clone $node3 );
					}
				}
				$cpt2 += $this->addDatasets ( $node2, $platId, $site2->place_id );
				if ($cpt2 > 0) {
					$cpt1 ++;
					$node1->addItem ( clone $node2 );
				}
			}
			$cpt1 += $this->addDatasets ( $node1, $platId, $site1->place_id );
			if ($cpt1 > 0) {
				$root->addItem ( clone $node1 );
				$cpt0 ++;
			}
		}
				
		if ($cpt0 > 0) {
			if (stripos ( $gcmd->gcmd_plat_name, 'Network' ) === false)
				$others = 'Other sites';
			else
				$others = 'Other networks';
			$node0 = new HTML_TreeNode ( array (
					'text' => "<font style='font-size:110%;'>$others</font>" 
			) );
			$cpt1 = $this->addDatasets ( $node0, $platId, 0 );
			if ($cpt1 > 0) {
				$root->addItem ( $node0 );
			}
		} else {
			$cpt0 = $this->addDatasets ( $root, $platId, 0 );
		}
		return $cpt0;
	}
	static function getUrl($datsType) {
		return parse_url ( $_SERVER ['REQUEST_URI'], PHP_URL_PATH ) . "?datsType=$datsType";
	}
	static function displayByDatsType($project_name, $titre, $withDataOnly = false, $defaultType = 0) {
		$datsType = $_REQUEST ['datsType'];
		if (! isset ( $datsType ) || empty ( $datsType ))
			$datsType = $defaultType;
		$arbre = new treeByPlat ( $withDataOnly, $datsType, "(is_archived is null or not is_archived)" );
		$arbre->project_name = $project_name;
		$arbre->projects = get_filtre_projets ( $project_name );
		echo "<h1>$titre</h1>";
		include 'legende.php';
		
		$selectedStyle = 'style="font-size:110%;font-weight:bold;"';
		$arbre->isEmptyTypeTab ();
		if ($datsType != - 1) {
			echo '<table><tr>';
			if ($arbre->EmptyTab[0] == false) {
				echo '<th>';
				if ($datsType == 0)
					echo "<font $selectedStyle >IN SITU</font>";
				else
					echo '<a href="' . treeByPlat::getUrl ( 0 ) . '">IN SITU</a>';
				echo '</th>';
			}
			if ($arbre->EmptyTab[2] == false) {
				echo '<th>';
				if ($datsType == 2)
					echo "<font $selectedStyle>MODEL</font>";
				else
					echo '<a href="' . treeByPlat::getUrl ( 2 ) . '">MODEL</a>';
				echo '</th>';
			}
			if ($arbre->EmptyTab[1] == false) {
				echo '<th>';
				if ($datsType == 1)
					echo "<font $selectedStyle>SATELLITE</font>";
				else
					echo '<a href="' . treeByPlat::getUrl ( 1 ) . '">SATELLITE</a>';
				echo '</th>';
			}
			if ($arbre->EmptyTab[3] == false) {
				echo '<th>';
				if ($datsType == 3)
					echo "<font $selectedStyle>VALUE-ADDED DATASETS</font>";
				else
					echo '<a href="' . treeByPlat::getUrl ( 3 ) . '">VALUE-ADDED DATASETS</a>';
				echo '</th>';
			}
			echo '</tr>';
			echo '</table><br>';
		}
		$arbre->build ();
		$arbre->display ();
	}
}
?>
