<?php
require_once ("bd/journal.php");
require_once ("mail.php");
require_once ("/sites/kernel/#MainProject/conf.php");
require_once ('scripts/filtreProjets.php');
class journal_form extends login_form {
	var $journal;
	var $filterUser;
	var $projectName;
	function createForm($filterUser = false, $typeJournal = 0) {
		if (isset ( $_SESSION ['loggedUser'] )) {
			$this->user = unserialize ( $_SESSION ['loggedUser'] );
		}
		
		$this->filterUser = $filterUser;
		
		if ($this->isLogged ()) {
			$this->createAddForm ( $typeJournal );
		} else {
			$this->createLoginForm ( 'Mail' );
		}
	}
	function createAddForm($typeJournal = 0) {
		$this->addElement ( 'textarea', 'comment', 'Comment', array (
				'cols' => 40,
				'rows' => 3 
		) );
		$tj = new type_journal ();
		$tj_select = $tj->chargeForm ( $this, 'type_journal', 'Type', array (
				TYPE_NEW,
				TYPE_UPDATE,
				TYPE_CHANGES 
		) );
		$this->addElement ( $tj_select );
		
		if (in_array ( $typeJournal, array (
				TYPE_NEW,
				TYPE_UPDATE,
				TYPE_CHANGES 
		) ))
			$this->getElement ( 'type_journal' )->setSelected ( $typeJournal );
		else
			$this->getElement ( 'type_journal' )->setSelected ( TYPE_UPDATE );
		
		$this->addElement ( 'text', 'contact', 'Contact', array (
				'size' => 32 
		) );
		$this->addRule ( 'contact', 'Contact is required', 'required' );
		$this->addRule ( 'contact', 'Contact: email exceeds the maximum length allowed (250 characters)', 'maxlength', 250 );
		$this->addRule ( 'contact', 'Contact: email is incorrect', 'email' );
		$this->getElement ( 'contact' )->setValue ( $this->user->mail );
		
		$this->getElement ( 'contact' )->freeze ();
		$this->getElement ( 'type_journal' )->freeze ();
		
		$dts = new dataset ();
		$liste = $dts->getOnlyTitles ( 'select dats_id,dats_title from dataset order by dats_title' );
		for($i = 0; $i < count ( $liste ); $i ++) {
			$j = $liste [$i]->dats_id;
			$array [$j] = $liste [$i]->dats_title;
		}
		
		if ($typeJournal == TYPE_CHANGES) {
			$this->addElement ( 'hidden', 'dataset' );
			$this->getElement ( 'dataset' )->setValue ( current ( array_keys ( $array ) ) );
			$this->addRule ( 'comment', 'Comment is required', 'required' );
		} else {
			$select = $this->createElement ( 'select', 'dataset', 'Dataset', $array, array (
					'style' => 'width:300px;' 
			) );
			$this->addElement ( $select );
		}
		
		$this->addElement ( 'submit', 'bouton_add', 'Add' );
	}
	function resetAddForm() {
		$this->getElement ( 'comment' )->setValue ( null );
	}
	function readJournal($type, $filter = false) {
		$this->journal = new journal ();
		if ($filter)
			$this->journal = $this->journal->getByUser ( $this->user->mail, $type );
		else
			$this->journal = $this->journal->getByType ( $type );
	}
	function addEntry() {
		$public = ($_POST ['type_journal'] != TYPE_CHANGES);
		return journal::addNews ( $this->user->mail, $_POST ['dataset'], $_POST ['comment'], $_POST ['type_journal'], $public, $this->projectName );
	}
	function displayAddForm($typeJournal = 0) {
		echo "<h1>Journal - New entry</h1>";
		
		$reqUri = $_SERVER ['REQUEST_URI'];
		
		// Affichage des erreurs
		if (! empty ( $this->_errors )) {
			foreach ( $this->_errors as $error ) {
				echo '<font size="3" color="red">' . $error . '</font><br>';
			}
		}
		
		echo '<form action="' . $reqUri . '" method="post" id="frmjnl" name="frmjnl" >';
		
		if ($typeJournal == TYPE_CHANGES)
			echo $this->getElement ( 'dataset' )->toHTML ();
		
		echo '<table>';
		echo '<tr><td><b>' . $this->getElement ( 'type_journal' )->getLabel () . '</b></td><td>' . $this->getElement ( 'type_journal' )->toHTML () . '</td></tr>';
		echo '<tr><td><b>' . $this->getElement ( 'contact' )->getLabel () . '</b></td><td>' . $this->getElement ( 'contact' )->toHTML () . '</td></tr>';
		if ($typeJournal != TYPE_CHANGES)
			echo '<tr><td><b>' . $this->getElement ( 'dataset' )->getLabel () . '</b></td><td>' . $this->getElement ( 'dataset' )->toHTML () . '</td></tr>';
		echo '<tr><td><b>' . $this->getElement ( 'comment' )->getLabel () . '</b></td><td>' . $this->getElement ( 'comment' )->toHTML () . '</td></tr>';
		echo '<tr><td colspan="2" align="center">' . $this->getElement ( 'bouton_add' )->toHTML () . '</td></tr>';
		
		echo '</table>';
		echo '</form>';
	}
	function addAbo() {
		$dats_id = $_POST ['dataset'];
		journal::addAboEntry ( $this->user->mail, $dats_id );
	}
	function deleteAbo($id) {
		journal::deleteAbo ( $id, $this->user->mail );
	}
	function displayListAbo($proj) {
		echo "<h1>Email notifications</h1>";
		$liste = new journal ();
		
		if ($this->filterUser) {
			$liste = $liste->getByUser ( $this->user->mail, TYPE_ABO );
			uasort ( $liste, array (
					'journal',
					'compareDatsTitle' 
			) );
		} else
			$liste = $liste->getByType ( TYPE_ABO, $proj, true, 'order by contact' );
		
		echo '<form method="post" >';
		echo '<table><tr>';
		if (! $this->filterUser)
			echo '<th>Mail</th>';
		echo '<th>Dataset</th><th></th></tr>';
		
		if ($this->filterUser) {
			$this->addElement ( 'submit', 'add', '', array (
					'style' => "border:none; color:#fff; background: transparent url('/img/ajouter.png') no-repeat top left; width:16px;height:16px;",
					'title' => 'Suscribe' 
			) );
			echo '<tr><td>' . $this->getElement ( 'dataset' )->toHTML () . '</td><td>' . $this->getElement ( 'add' )->toHTML () . '</td></tr>';
		}
		
		foreach ( $liste as $ligne ) {
			$this->addElement ( 'submit', 'del_' . $ligne->id, '', array (
					'style' => "border:none; color:#fff; background: transparent url('/img/supprimer.png') no-repeat top left; width:16px;height:16px;",
					'title' => 'Unsuscribe' 
			) );
			
			echo '<tr>';
			if (! $this->filterUser)
				echo '<td style="white-space: nowrap;">' . $ligne->contact . '</td>';
			echo '<td>' . $ligne->dataset->dats_title . '</td><td>';
			echo $this->getElement ( 'del_' . $ligne->id )->toHTML ();
			echo '</td></tr>';
		}
		
		echo '</table>';
		echo '</form>';
	}
	function displayListDl($proj) {
		echo "<h1>Download history</h1>";
		$liste = new journal ();
		if ($this->filterUser)
			$liste = $liste->getByUser ( $this->user->mail, TYPE_DL );
		else
			$liste = $liste->getByType ( TYPE_DL, $proj );
		
		echo '<table>';
		echo '<tr><th>Date</th>';
		if (! $this->filterUser)
			echo '<th>Mail</th>';
		echo '<th>Dataset</th></tr>';
		foreach ( $liste as $ligne ) {
			$withComment = isset ( $ligne->comment ) && ! empty ( $ligne->comment );
			echo '<tr><td ' . (($withComment && $this->filterUser) ? 'rowspan="2"' : '') . ' title="' . $ligne->date->format ( 'Y-m-d H:i' ) . '">' . $ligne->date->format ( 'Y-m-d' ) . '</td>';
			if (! $this->filterUser)
				echo '<td style="white-space: nowrap;">' . $ligne->contact . '</td>';
			echo '<td>' . $ligne->dataset->dats_title . '</td></tr>';
			if ($withComment && $this->filterUser)
				echo '<tr><td>' . nl2br ( str_replace ( DATA_PATH, '', $ligne->comment ) ) . '</td></tr>';
		}
		echo '</table>';
	}
	function displayChanges() {
		echo "<h1>Changes</h1>";
		$liste = new journal ();
		$liste = $liste->getByType ( TYPE_CHANGES, null, false );
		
		echo '<table>';
		echo '<tr><th>Date</th><th></th></tr>';
		foreach ( $liste as $ligne ) {
			echo '<tr><td title="' . $ligne->date->format ( 'Y-m-d H:i' ) . '">' . $ligne->date->format ( 'Y-m-d' ) . '</td>';
			echo '<td>' . nl2br ( $ligne->comment ) . '</td></tr>';
		}
		echo '</table>';
	}
	function displayListNewData($proj) {
		echo "<h1>Updates</h1>";
		$liste = new journal ();
		$liste = $liste->getByType ( TYPE_NEW . ',' . TYPE_UPDATE, $proj, false );
		
		echo '<table>';
		echo '<tr><th>Date</th>';
		echo '<th>Dataset</th><th>Comment</th></tr>';
		foreach ( $liste as $ligne ) {
			echo '<tr><td title="' . $ligne->date->format ( 'Y-m-d H:i' ) . '">' . $ligne->date->format ( 'Y-m-d' ) . '</td>';
			echo '<td>' . $ligne->dataset->dats_title . '</td>';
			echo '<td>' . nl2br ( $ligne->comment ) . '</td></tr>';
		}
		echo '</table>';
	}
	function displayList($type = 0) {
		if ($type == TYPE_ABO)
			$this->displayListAbo ( get_filtre_projets ( $this->projectName ) );
		if ($type == TYPE_DL)
			$this->displayListDl ( get_filtre_projets ( $this->projectName ) );
		if ($type == TYPE_NEW)
			$this->displayListNewData ( get_filtre_projets ( $this->projectName ) );
		if ($type == TYPE_CHANGES)
			$this->displayChanges ();
	}
}

?>
