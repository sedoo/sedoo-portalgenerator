<?php
require_once ('forms/login_form.php');

class elastic_form extends login_form {
	
	function createForm() {
		if (isset ( $_SESSION ['loggedUser'] )) {
			$this->user = unserialize ( $_SESSION ['loggedUser'] );
		}
	
		if ($this->isRoot ()) {
			$this->createElasticForm();
		} else {
			$this->createLoginForm ( 'Mail' );
		}
	}
	
	function createElasticForm() {
		$this->addElement ( 'submit', 'bouton_index_datasets', 'Index Datasets' );
		$this->addElement ( 'submit', 'bouton_index_keywords', 'Index Keywords' );
		$this->addElement ( 'submit', 'bouton_reset_indexes', 'Reset Indexes' );
	}
	function displayElasticForm() {
		$reqUri = $_SERVER ['REQUEST_URI'];
			
		echo '<form action="' . $reqUri . '" method="post" id="frmelastic" name="frmelastic" >';
		echo '<table>';
		echo '<tr><td>' . $this->getElement ( 'bouton_index_datasets' )->toHTML () . '</td>'
				.'<td>' . $this->getElement ( 'bouton_index_keywords' )->toHTML () . '</td>'
				.'<td>' . $this->getElement ( 'bouton_reset_indexes' )->toHTML () . '</td></tr>';
		echo '</table>';
		echo '</form>';
	}
}
?>