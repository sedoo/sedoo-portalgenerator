<?php
/*
 * Created on 8 juil. 2010 To change the template for this generated file go to Window - Preferences - PHPeclipse - PHP - Code Templates
 */
require_once ("bd/bdConnect.php");
require_once ("bd/dataset.php");
require_once ("bd/type_journal.php");
require_once ("scripts/mail.php");

define ( 'TYPE_ARCHIVE', 6 );
define ( 'TYPE_UNARCHIVE', 7 );
define ( 'TYPE_DL', 3 );
define ( 'TYPE_ABO', 1 );
define ( 'TYPE_NEW', 2 );
define ( 'TYPE_UPDATE', 4 );
define ( 'TYPE_CHANGES', 5 );

define ( 'EXCLUDE_USERS', "'laurence.mastrorillo@obs-mip.fr','guillaume.brissebrat@obs-mip.fr','karim.ramage@ipsl.polytechnique.fr','Karim.Ramage@ipsl.polytechnique.fr','laurence.fleury@obs-mip.fr','sbcipsl@ipsl.jussieu.fr','laurent.labatut@meteo.fr','arnaud.miere@obs-mip.fr','helene.ferre@obs-mip.fr','brissebr@sedoo.fr','nizar.belmahfoud@obs-mip.fr'" );
class journal {
	var $id;
	var $date;
	var $type_id;
	var $type;
	var $contact;
	var $dats_id;
	var $dataset;
	var $comment;
	var $publier;
	function new_journal($tab) {
		$this->id = $tab [0];
		$this->date = new DateTime ( $tab [1] );
		$this->type_id = $tab [2];
		if (isset ( $this->type_id ) && ! empty ( $this->type_id )) {
			$tj = new type_journal ();
			$this->type = $tj->getById ( $this->type_id );
		}
		$this->contact = $tab [3];
		$this->dats_id = $tab [4];
		if (isset ( $this->dats_id ) && ! empty ( $this->dats_id )) {
			$dts = new dataset ();
			$this->dataset = $dts->getById ( $this->dats_id );
		}
		$this->comment = $tab [5];
		$this->publier = $tab [6];
		if (! isset ( $this->publier ) || ! $this->publier) {
			$this->publier = false;
		}
	}
	static function test() {
		echo "Test OK";
	}
	static function deleteAbo($id, $contact) {
		$bd = new bdConnect ();
		$bd->db_open ();
		$query = "delete from journal where journal_id = $id and contact = '$contact' and type_journal_id = " . TYPE_ABO . ';';
		$ret = $bd->exec ( $query );
		$bd->db_close ();
		
		return $ret;
	}
	static function addDownloadEntry($user, $datsId, $files, $follow) {
		$entry = new journal ();
		foreach ( $files as $file ) {
			$entry->comment .= $file . "\n";
		}
		$entry->contact = $user;
		
		$entry->type_id = type_journal::getIdByName ( 'Download' );
		$entry->dats_id = $datsId;
		$entry->publier = false;
		if ($entry->insert () && $follow)
			journal::addAboEntry ( $user, $datsId );
	}
	static function addAboEntry($user, $datsId) {
		$entry = new journal ();
		$entry->comment = null;
		$entry->contact = $user;
		$entry->type_id = TYPE_ABO;
		$entry->dats_id = $datsId;
		$entry->publier = false;
		if (! $entry->existe ())
			return $entry->insert ();
		else
			return 0;
	}
	static function getNews($interval = '1 mon', $projets) {
		$journal = new journal ();
		$types = TYPE_NEW . ',' . TYPE_UPDATE;
		$orderBy = 'order by date desc';
		$query = "select * from journal where type_journal_id in ($types) and dats_id in (select distinct dats_id from dats_proj where project_id in ($projets)) and publier and age(date) < '$interval' order by date desc";
		return $journal->getByQuery ( $query );
	}
	static function sendMailAbonnes($entry, $projectName = MainProject) {
		$liste = new journal ();
		$liste = $liste->getAbosByDats ( $entry->dats_id );
		
		$sujet = "[$projectName-DATABASE] Data Update";
		
		if (isset ( $liste ) && (count ( $liste ) > 0)) {
			$corps = "Dear database user,\n\n" . 'New data are available for the dataset "' . $liste [0]->dataset->dats_title . '":' . "\n\n" . $entry->comment . "\n\nMetadata: http://" . $_SERVER ['HTTP_HOST'] . "/$projectName/?editDatsId=" . $liste [0]->dataset->dats_id . "&project_name=$projectName" . "\n\nIf you don't want to receive these email notifications in the future, you can unsuscribe on this page:" . "\nhttp://" . $_SERVER ['HTTP_HOST'] . "/Your-Account?p&pageId=5&type=1" . "\n\nBest regards,\n" . "The $projectName database service";
			
			foreach ( $liste as $abo ) {
				sendMailSimple ( $abo->contact, $sujet, $corps, ROOT_EMAIL );
				echo 'Send mail to ' . $abo->contact . '<br>';
			}
			sendMailSimple ( ROOT_EMAIL, $sujet, $corps, ROOT_EMAIL );
		}
	}
	static function addNews($user, $datsId, $comment, $type = TYPE_NEW, $public = true, $projectName = MainProject) {
		$entry = new journal ();
		$entry->comment = $comment;
		$entry->contact = $user;
		$entry->type_id = $type;
		$entry->dats_id = $datsId;
		$entry->publier = $public;
		if ($entry->insert ()) {
			if (($entry->type_id == TYPE_NEW) || ($entry->type_id == TYPE_UPDATE)) {
				journal::sendMailAbonnes ( $entry, $projectName );
			}
			return true;
		}
		return false;
	}
	static function deleteNews($bd, $dats_id) {
		$types = TYPE_NEW . ',' . TYPE_UPDATE;
		$query = "update journal set publier = 'false' where dats_id = $dats_id and type_journal_id in ($types);";
		echo $query . '<br>';
		$bd->exec ( $query );
		return true;
	}
	static function compareDatsTitle($a, $b) {
		return strnatcasecmp ( $a->dataset->dats_title, $b->dataset->dats_title );
	}
	function insert() {
		$bd = new bdConnect ();
		$bd->db_open ();
		
		$query_insert = 'date,type_journal_id,contact,dats_id';
		$query_values = 'now(),' . $this->type_id . ",'" . $this->contact . "'," . $this->dats_id;
		
		if (isset ( $this->comment ) && ! empty ( $this->comment )) {
			$query_insert .= ",comment";
			$query_values .= ",'" . $this->comment . "'";
		}
		if ($this->publier) {
			$query_insert .= ",publier";
			$query_values .= ",'true'";
		}
		
		$query = "insert into journal ($query_insert) VALUES ($query_values)";
				
		$bd->exec ( $query );
		$this->id = $bd->getLastId ( "journal_journal_id_seq" );
		$bd->db_close ();
		return $this->id;
	}
	function getAll() {
		$query = "select * from journal order by date desc";
		return $this->getByQuery ( $query );
	}
	function getAbosByDats($datsId) {
		$query = "select * from journal where dats_id = $datsId and type_journal_id = " . TYPE_ABO . ';';
		return $this->getByQuery ( $query );
	}
	
	/**
	 * $ids : 1 ou plusieurs types séparés par des ','.
	 * 0 pour tous les types.
	 */
	function getByType($ids, $projects = null, $exclude = true, $orderBy = 'ORDER BY date DESC', $limit = 100) {
		$whereClauses = array ();
		
		if ($ids != 0) {
			$whereClauses [] = "type_journal_id in ($ids)";
		}
		
		if ($exclude) {
			$whereClauses [] = 'contact not in (' . EXCLUDE_USERS . ')';
		}
		
		if ($projects) {
			$whereClauses [] = "dats_id in (select distinct dats_id from dats_proj where project_id in ($projects))";
		}
		
		$where = implode ( ' AND ', $whereClauses );
		
		$query = "SELECT * FROM journal WHERE $where $orderBy LIMIT $limit";
		
		return $this->getByQuery ( $query );
	}
	
	/**
	 * $types : 1 ou plusieurs types séparés par des ','.
	 * 0 pour tous les types.
	 */
	function getByUser($mail, $types = 0, $orderBy = 'order by date desc') {
		if ($types == 0)
			$query = "select * from journal where contact = '$mail' $orderBy;";
		else
			$query = "select * from journal where contact = '$mail' and type_journal_id in ($types) $orderBy;";
		return $this->getByQuery ( $query );
	}
	
	/**
	 * $types : 1 ou plusieurs types séparés par des ','.
	 * 0 pour tous les types.
	 */
	function getByDataset($datsId, $types = 0, $orderBy = 'order by date desc') {
		if ($types == 0)
			$query = "SELECT * FROM journal WHERE dats_id = '$datsId' $orderBy;";
		else
			$query = "SELECT * FROM journal WHERE dats_id = '$datsId' and type_journal_id in ($types) $orderBy;";
		return $this->getByQuery ( $query );
	}
	function existe() {
		$query = 'select * from journal where type_journal_id = ' . $this->type_id . " and contact = '" . $this->contact . "' and dats_id = " . $this->dats_id;
		$bd = new bdConnect ();
		if ($resultat = $bd->get_data ( $query )) {
			$this->new_journal ( $resultat [0] );
			return true;
		}
		return false;
	}
	function getByQuery($query) {
		$bd = new bdConnect ();
		$liste = array ();
		if ($resultat = $bd->get_data ( $query )) {
			for($i = 0; $i < count ( $resultat ); $i ++) {
				$liste [$i] = new journal ();
				$liste [$i]->new_journal ( $resultat [$i] );
			}
		}
		return $liste;
	}
	static function archiveDataset($user, $datsId, $comment){
		$entry = new journal();
		$entry->comment = $comment;
		$entry->contact = $user;
		$entry->type_id = TYPE_ARCHIVE;
		$entry->dats_id = $datsId;
		$entry->publier = false;
		if ($entry->insert()){
			return true;
		}
		return false;
	}
	 
	static function unarchiveDataset($user, $datsId, $comment){
		$entry = new journal();
		$entry->comment = $comment;
		$entry->contact = $user;
		$entry->type_id = TYPE_UNARCHIVE;
		$entry->dats_id = $datsId;
		$entry->publier = false;
		if ($entry->insert()){
			return true;
		}
		return false;
	}
}
?>
