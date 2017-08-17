<?php

require_once("bd/dataset.php");
require_once("bd/journal.php");
require_once('filtreProjets.php');
require_once('editDataset.php');

class archive_form extends login_form{
	
	var $projectName;
	var $projects;
	
	function createForm($projectName){
		if (isset($_SESSION['loggedUser'])){
			$this->user = unserialize($_SESSION['loggedUser']);
			//echo 'loggedUser trouvé dans la session<br>';
			//echo 'type: '.get_class($this->user).'<br>';
		}
				
		$this->projectName = $projectName;
		
		if ($this->isLogged()){
			
			$this->projects = 'SELECT DISTINCT dats_id FROM dats_proj WHERE project_id IN ('.get_filtre_projets($projectName).')';
			
			$dts = new dataset;
			$liste = $dts->getOnlyTitles("select dats_id,dats_title FROM dataset WHERE dats_id IN ($this->projects) AND (is_archived IS NULL OR NOT is_archived) ORDER BY dats_title");
			for ($i = 0; $i < count($liste); $i++){
				$j = $liste[$i]->dats_id;
				$array[$j] = $liste[$i]->dats_title;
			}
			$select = $this->createElement('select','dataset','Dataset', $array,array('style' => 'width:400px;'));
			$this->addElement($select);
						
			$this->addElement('textarea','comment','Comment',array('cols'=>50, 'rows'=>4));
			
			$this->addElement('submit', 'bouton_add', 'Archive');
			
		}else{
			$this->createLoginForm('Mail');
		}
				
	}
	
	function archive(){
		$datsId = $_POST['dataset'];
		$comment = $_POST['comment'];
		if (journal::archiveDataset($this->user->mail,$datsId,$comment)){
			$bd = new bdConnect;
			$bd->db_open();
			$query = "update dataset set is_archived = true where dats_id = ".$datsId;
			echo $query.'<br>';
			$bd->exec($query);
			$bd->db_close();
			return true;
		}
		return false;
	}
			
	function displayArchivedDataset($datsId){
		echo "<h1>Archived dataset</h1><p/>";
		
		$reqUri = $_SERVER['REQUEST_URI'];
		$reqUri = substr($reqUri,0,strpos($reqUri,'&datsId'));
		
		echo "<a href='$reqUri' >&lt;&lt; Back</a><br/>";
		
		editDataset($datsId, $this->projectName, true);
	}
	
	function displayArchiveList(){
		
		$reqUri = $_SERVER['REQUEST_URI'];
				
		echo '<h2>Archived datasets</h2>';
						
		$dts = new dataset;
		$jnl = new journal;
		$query = "";
		$liste_dats = $dts->getByQuery("SELECT * FROM dataset WHERE dats_id IN ($this->projects) AND is_archived ORDER BY dats_title");
		
		echo '<table><tr><th align="center">Dataset</th><th align="center">Date</th><th align="center">Comment</th></tr>';
		foreach($liste_dats as $dats){
			echo "<tr><td><a href='$reqUri&datsId=$dats->dats_id'>$dats->dats_title</a></td>";
			
			$tmp = $jnl->getByDataset($dats->dats_id, TYPE_ARCHIVE);
			if ($tmp){
				echo "<td>".$tmp[0]->date->format('Y-m-d')."</td><td>".nl2br($tmp[0]->comment)."</td></tr>"; 
			}else{
				echo "<td></td><td></td></tr>";
			}
					
		}
		echo '</table>';
	}
	
	
	function reset(){
		$this->getElement('comment')->setValue(null);
	}
	
	function display(){
		echo "<h1>Archives</h1><p/>";
		$reqUri = $_SERVER['REQUEST_URI'];
				
		echo '<form action="'.$reqUri.'" method="post" id="frmarc" name="frmarc" >';
							
		echo '<table>';
		echo '<tr><td><b>'.$this->getElement('dataset')->getLabel().'</b></td><td>'.$this->getElement('dataset')->toHTML().'</td></tr>';
		echo '<tr><td><b>'.$this->getElement('comment')->getLabel().'</b></td><td>'.$this->getElement('comment')->toHTML().'</td></tr>';
		echo '<tr><td colspan="2" align="center">'.$this->getElement('bouton_add')->toHTML().'</td></tr>';
		
		echo '</table>';
		echo '</form>';
		
				
		$this->displayArchiveList();
	}
	
	
	
}

?>