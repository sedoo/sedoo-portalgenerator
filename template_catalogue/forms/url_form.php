<?php
require_once ("bd/journal.php");
require_once ("bd/url.php");
require_once ("bd/role.php");
require_once ("bd/dats_role.php");
require_once ("conf/conf.php");

define ( "ADD_URL_LOCAL", 1 );
define ( "ADD_URL_IPSL", 2 );
define ( "ADD_URL_MAP", 3 );
define ( "ADD_URL_EXTERNE", 4 );
define ( "ADD_URL_QL", 5 );

class url_form extends login_form {
	var $type;
	var $projectName;
	function createForm($typeUrl, $projectName = MainProject) {
		if (isset ( $_SESSION ['loggedUser'] )) {
			$this->user = unserialize ( $_SESSION ['loggedUser'] );
		} else {
			$this->createLoginForm ( 'Mail' );
		}
		$this->projectName = $projectName;
		$this->type = $typeUrl;
		if ($this->isAdmin ( $projectName )) {
			$this->createAddUrlForm ();
		} else {
			echo "<font size=\"3\" color='red'><b>You cannot view this part of the site.</b></font><br>";
		}
	}
	function createAddUrlForm() {
		$dts = new dataset ();
		$where = '';
		if ($this->type == 0) {
			$where = 'where dats_id in (select distinct dats_id from url)';
		} else if ($this->type == ADD_URL_IPSL) {
			$where = 'where (dats_id in (select distinct dats_id from dats_type where dats_type_id in (1,2,3))' . ' or dats_id in (select dats_id from dats_place where place_id in ((select place_id from place where gcmd_plat_id in (10,13,23)))))' . ' and dats_id not in (select distinct dats_id from url)';
		} else if ($this->type == ADD_URL_MAP) {
			$where = 'where dats_id not in (select distinct dats_id from dats_type where dats_type_id in (1,2))';
		} else if ($this->type == ADD_URL_EXTERNE) {
			$where = "where dats_id not in (select distinct dats_id from url WHERE url_type != 'map') and is_requested is null";
		} else if ($this->type == ADD_URL_QL) {
			$where = "where dats_id not in (select distinct dats_id from url WHERE url_type = 'ql') and is_requested is null";
		} else {
			$where = "where dats_id not in (select distinct dats_id from dats_type where dats_type_id in (1,2)) and dats_id not in (select distinct dats_id from url WHERE url_type != 'map' and url_type != 'ql')";
		}
		$liste = $dts->getOnlyTitles ( "select dats_id,dats_title from dataset $where order by dats_title" );
		$array [0] = "-- Datasets list --";
		for($i = 0; $i < count ( $liste ); $i ++) {
			$j = $liste [$i]->dats_id;
			$array [$j] = $liste [$i]->dats_title;
		}
		$select = $this->createElement ( 'select', 'dataset', 'Dataset', $array, array (
				'style' => 'width:400px;',
				'onchange' => 'AfficheDataPolicy()' 
		) );
		$this->addElement ( $select );
		$this->addElement ( 'text', 'emplacement', 'Emplacement', array (
				'size' => 50 
		) );
		$this->addRule ( 'emplacement', 'Emplacement is required', 'required' );
		$this->addElement ( 'textarea', 'comment', 'Comment (news page)', array (
				'cols' => 50,
				'rows' => 3 
		) );
		$this->createFormRoles ();
		$this->addElement ( 'submit', 'bouton_add', 'Add', array (
				'disabled' => 'true' 
		) );
		$this->addElement ( 'submit', 'bouton_ok', 'Ok' );
		if (isset($_REQUEST ['datsId']) && !empty($_REQUEST ['datsId'])) {
			$this->getElement ( 'dataset' )->setSelected ( $_REQUEST ['datsId'] );
			$this->getElement ( 'dataset' )->freeze ();
		}
	}
	function displayUrls() {
		if(isset($_POST ['dataset']) && !empty($_POST ['dataset'])){
			$dats_id = $_POST ['dataset'];
		}
		if (!isset($dats_id) && empty($dats_id)) {
			if(isset($_REQUEST ['datsId']) && !empty($_REQUEST ['datsId'])){
				$dats_id = $_REQUEST ['datsId'];
			}
		}
		if (isset($dats_id) && !empty($dats_id)) {
			$u = new url ();
			$liste = $u->getByQuery ( "select * from url where dats_id = $dats_id" );
			if (isset ( $liste ) && ! empty ( $liste )) {
				echo '<table><tr><th colspan="3">URL:</th></tr>';
				$withData = false;
				foreach ( $liste as $url ) {
					$this->addElement ( 'text', 'url_' . $url->url_id, 'Url', array (
							'size' => 50 
					) );
					$this->getElement ( 'url_' . $url->url_id )->setValue ( $url->url );
					$isDataLink = false;
					if ($url->url_type == 'http' && strpos ( $url->url, '?jeu' )) {
						$this->getElement ( 'url_' . $url->url_id )->freeze ();
						$this->addElement ( 'submit', 'bouton_update_' . $url->url_id, 'Update', array (
								'disabled' => 'true' 
						) );
						$withData = true;
						$isDataLink = true;
					} else {
						$this->addElement ( 'submit', 'bouton_update_' . $url->url_id, 'Update' );
						if ($url->url_type == 'local file') {
							$this->addElement ( 'submit', 'bouton_delete_' . $url->url_id, 'Delete', array (
									'disabled' => 'true' 
							) );
						} else {
							$this->addElement ( 'submit', 'bouton_delete_' . $url->url_id, 'Delete' );
						}
					}
					echo '<tr><td><b>' . $url->url_type . '</b></td><td>' . $this->getElement ( 'url_' . $url->url_id )->toHTML () . '</td><td>' . $this->getElement ( 'bouton_update_' . $url->url_id )->toHTML () . '&nbsp;&nbsp;';
					if ($isDataLink) {
					} else {
						echo $this->getElement ( 'bouton_delete_' . $url->url_id )->toHTML ();
					}
					echo '</td></tr>';
				}
				echo '</table>';
				$dr = new dats_role ();
				$roles = $dr->getByDataset ( $dats_id );
				if (isset ( $roles ) && ! empty ( $roles )) {
					$this->addElement ( 'submit', 'bouton_update_roles_' . $dats_id, 'Update' );
					$selRoles = array ();
					echo '<table><tr><th colspan=1>Role(s):</th></tr>';
					foreach ( $roles as $role ) {
						$selRoles [] = $role->role->role_id;
					}
					echo '</table>';
					$this->getElement ( 'roles' )->setSelected ( $selRoles );
					echo $this->getElement ( 'roles' )->toHTML ();
					echo $this->getElement ( 'bouton_update_roles_' . $dats_id )->toHTML ();
				}
			} else
				echo '<b>No data for this dataset</b><br>';
		}
	}
	function createFormRoles() {
		$r = new role ();
		$role_select = $r->chargeForm ( $this, 'roles', 'Roles' );
		$role_select->setMultiple ( true );
		$role_select->setSize ( 8 );
		$this->addElement ( $role_select );
	}
	function deleteUrl($id) {
		$bd = new bdConnect ();
		$bd->db_open ();
		url::deleteUrl ( $bd, $id );
		$bd->db_close ();
	}
	function deleteUrls($id) {
		$bd = new bdConnect ();
		$bd->db_open ();
		url::deleteUrls ( $bd, $id );
		dats_role::deleteRoles ( $bd, $id );
		journal::addNews ( $this->user->mail, $id, 'Dataset deleted', TYPE_UPDATE );
		$bd->db_close ();
	}
	function updateUrl($id) {
		$url = $_POST ['url_' . $id];
		if (isset ( $url ) && ! empty ( $url )) {
			$bd = new bdConnect ();
			$bd->db_open ();
			url::updateUrl ( $bd, $id, $url );
			$bd->db_close ();
		}
	}
	function updateRoles() {
		$datsId = $_POST ['dataset'];
		$selectedRoles = $this->getElement ( 'roles' )->getSelected ();
		$bd = new bdConnect ();
		$bd->db_open ();
		$bd->beginTransaction ();
		if (dats_role::deleteRoles ( $bd, $datsId )) {
			foreach ( $selectedRoles as $role ) {
				if (dats_role::addDatsRole ( $bd, $datsId, $role ) === false) {
					$bd->rollbackTransaction ();
					$bd->db_close ();
					return false;
				}
			}
		} else {
			$bd->rollbackTransaction ();
			$bd->db_close ();
			return false;
		}
		$bd->commitTransaction ();
		$bd->db_close ();
		return true;
	}
	function addUrl() {
		$emplacement = $_POST ['emplacement'];
		$dats_id = $_POST ['dataset'];
		$bd = new bdConnect ();
		$bd->db_open ();
		$updateJournal = true;
		if (($this->type == ADD_URL_IPSL) || ($this->type == ADD_URL_LOCAL)) {
			$bd->beginTransaction ();
			$selectedRoles = $this->getElement ( 'roles' )->getSelected ();
			$retour = false;
			if (isset ( $selectedRoles ) && ! empty ( $selectedRoles )) {
				foreach ( $selectedRoles as $selectedRole ) {
					if ($selectedRole) {
						if (dats_role::addDatsRole ( $bd, $dats_id, $selectedRole ) === false) {
							$bd->rollbackTransaction ();
							return false;
						}
					}
				}
			}
			if ($this->type == ADD_URL_IPSL) {
				$url = "/Data-Download-IPSL?LnkFTP=$emplacement";
				if (url::addUrl ( $bd, $url, $dats_id, 'ftp' )) {
					$bd->commitTransaction ();
					$retour = true;
				} else {
					$bd->rollbackTransaction ();
					$retour = false;
				}
			} else if ($this->type == ADD_URL_LOCAL) {
				$url = "/Data-Download?jeu=$dats_id";
				if (substr ( $emplacement, - 1 ) == '/') {
					$emplacement = substr ( $emplacement, 0, strlen ( $emplacement ) - 1 );
				}
				$urlFile = 'file://localhost' . DATA_PATH . "/$emplacement";
				if (url::addUrl ( $bd, $url, $dats_id, 'http' ) && url::addUrl ( $bd, $urlFile, $dats_id, 'local file' )) {
					$bd->commitTransaction ();
					$retour = true;
				} else {
					$bd->rollbackTransaction ();
					$retour = false;
				}
			}
		} else if ($this->type == ADD_URL_MAP) {
			$url = 'file://localhost' . MAP_PATH . "/$emplacement";
			$updateJournal = false;
			$retour = url::addUrl ( $bd, $url, $dats_id, 'map' );
		} else if ($this->type == ADD_URL_EXTERNE) {
			$retour = url::addUrl ( $bd, $emplacement, $dats_id, 'http' );
		} else if ($this->type == ADD_URL_QL) {
			$retour = url::addUrl ( $bd, $emplacement, $dats_id, 'ql' );
			$updateJournal = false;
		} else {
			$retour = false;
		}
		$bd->db_close ();
		if ($retour && $updateJournal) {
			journal::addNews ( $this->user->mail, $dats_id, $_POST ['comment'], TYPE_NEW, true, $this->projectName );
		}
		return $retour;
	}
	function displayAddURLForm() {
		$titre = "New URL";
		if(isset($_SERVER ['REQUEST_URI']) && !empty($_SERVER ['REQUEST_URI']))
			$reqUri = $_SERVER ['REQUEST_URI'];
		if ($this->type == 0) {
			echo '<form action="' . $reqUri . '" method="post" id="frmjnl" name="frmjnl" >';
			echo '<table>';
			echo '<tr><td><b>' . $this->getElement ( 'dataset' )->getLabel () . '</b></td><td>' . $this->getElement ( 'dataset' )->toHTML () . '</td></tr>';
			echo '<tr><td colspan="2" align="center">' . $this->getElement ( 'bouton_ok' )->toHTML () . '</td></tr>';
			echo '</table>';
			$this->displayUrls ();
			echo '</form>';
		} else {
			if ($this->type == ADD_URL_IPSL) {
				$this->getElement ( 'emplacement' )->setLabel ( "FTP link (LnkFtp)" );
			} else if ($this->type == ADD_URL_MAP) {
				$this->getElement ( 'emplacement' )->setLabel ( 'Path (relative to ' . MAP_PATH . ')' );
			} else if ($this->type == ADD_URL_LOCAL) {
				$this->getElement ( 'emplacement' )->setLabel ( 'Path (relative to ' . DATA_PATH . ')' );
			} else if ($this->type == ADD_URL_EXTERNE) {
				$this->getElement ( 'emplacement' )->setLabel ( 'URL' );
			} else if ($this->type == ADD_URL_QL) {
				$titre = "New Quicklook charts url";
				$this->getElement ( 'emplacement' )->setLabel ( 'URL' );
			} else
				return;
			echo "<h1>$titre</h1>";
			// Affichage des erreurs
			if (! empty ( $this->_errors )) {
				foreach ( $this->_errors as $error ) {
					echo '<font size="3" color="red">' . $error . '</font><br>';
				}
			}
			// Pour l'affichage de la datapolicy et des use constraints
			echo '<span name="data_policy_errors" style="color: red;"></span>';
			echo '<form action="' . $reqUri . '" method="post" id="frmjnl" name="frmjnl" >';
			echo '<table>';
			echo '<tr><td><b>' . $this->getElement ( 'dataset' )->getLabel () . '</b></td><td>' . $this->getElement ( 'dataset' )->toHTML () . '</td></tr>';
			echo '<tr><td><b>' . $this->getElement ( 'comment' )->getLabel () . '</b></td><td>' . $this->getElement ( 'comment' )->toHTML () . '</td></tr>';
			echo '<tr><td><b>' . $this->getElement ( 'emplacement' )->getLabel () . '</b></td><td>' . $this->getElement ( 'emplacement' )->toHTML () . '</td></tr>';
			if (($this->type == ADD_URL_LOCAL) || ($this->type == ADD_URL_IPSL)) {
				echo '<tr name="data_policy_tr" ><td><b>Data policy</b></td><td name="data_policy_td">&nbsp;</td></tr>';
				echo '<tr><td><b>' . $this->getElement ( 'roles' )->getLabel () . '</b></td><td>' . $this->getElement ( 'roles' )->toHTML () . '</td></tr>';
			}
			echo '<tr><td colspan="2" align="center">' . $this->getElement ( 'bouton_add' )->toHTML () . '</td></tr>';
			echo '</table>';
			echo '</form>';
		}
	}
}

?>
