<?php 

require_once ("forms/base_form.php");
require_once ("conf/conf.php");
require_once ('scripts/filtreProjets.php');
require_once ("bd/dataset.php");
require_once ("bd/bdConnect.php");

class duplicate_dataset_form extends base_form {
	
	var $duplicated_dats_id;
	var $duplicated_dats_title;
	
	function createForm($project_name){
		$dts = new dataset;
		$liste = $dts->getOnlyTitles("select dats_id,dats_title from dataset order by dats_title");
		$array [0] = "-- Datasets list --";
		for ($i = 0; $i < count($liste); $i++){
			$j = $liste[$i]->dats_id;	
			$array[$j] = $liste[$i]->dats_title;
		}
		$this->addElement('select','dataset',"Dataset",$array);
		$this->addElement('text', 'title', 'Duplicated dataset Title');
		$this->applyFilter('title','trim');
		$this->addRule('title','Duplicated dataset title is required','required');
		$this->addElement('submit', 'bouton_duplicate', 'duplicate');
	}
	
	function displayForm(){
		echo '<h1>Duplicate dataset</h1>';
		if ( !empty($this->_errors) ){
			foreach ($this->_errors as $error) {
				echo '<font size="3" color="red">'.$error.'</font><br>';
			}
		}
		echo '<form action="'.$reqUri.'" method="post" id="frmjnl" name="frmjnl" >';
		echo '<table>';
		echo '<tr><td><b>'.$this->getElement('dataset')->getLabel().'</b></td><td>'.$this->getElement('dataset')->toHTML().'</td></tr>';
		echo '<tr><td><b>'.$this->getElement('title')->getLabel().'</b></td><td>'.$this->getElement('title')->toHTML().'</td></tr>';
		echo '<tr><td colspan="2" align="center">'.$this->getElement('bouton_duplicate')->toHTML().'</td></tr>';
		echo '</table>';
		echo '</form>';
	}
	
	function duplicate_dataset(){
		$this->duplicated_dats_title = $this->exportValue('title');
		$bd = new bdConnect;
		$query = "SELECT duplicate_dataset_multi(".$this->exportValue('dataset').",'".$this->exportValue('title')."');" ;
		$res = $bd->get_data($query);
		$this->duplicated_dats_id = $res [0][0];
		if(isset($this->duplicated_dats_id) && !empty($this->duplicated_dats_id))
			return true;
		else
			return false;
	}
	
	function reset_form(){
		unset($this->duplicated_dats_title);
		unset($this->duplicated_dats_id);
	}
	
	function get_id(){
		return $this->duplicated_dats_id;
	}
	
	function get_title(){
		return $this->duplicated_dats_title;
	}
}

?>