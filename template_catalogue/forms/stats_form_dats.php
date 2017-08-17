<?php

require_once("bd/dats_quality.php");
require_once("bd/journal.php");
require_once('forms/graph_utils.php');
require_once("editDataset.php");

class stats_form_dats extends login_form{
	
	var $dats;
	var $projectName;
	
	function createForm($datsId, $projectName){
		if (isset($_SESSION['loggedUser'])){
			$this->user = unserialize($_SESSION['loggedUser']);
			$this->projectName = $projectName;
			$this->dats = new dataset();
			$this->dats = $this->dats->getById($datsId);

		}else{
			$this->createLoginForm('Mail');
		}
	}
	
	private static function getUrl($type, $datsId){
		return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)."?p&pageId=10&type=$type&datsId=$datsId";
	}

	function display(){
		if ( $this->isAdmin($this->projectName) || $this->isPi($this->dats) ) {
			echo '<h1>'.$this->dats->dats_title.'</h1><br><br>';
			$type = $_REQUEST['type'];
			$selectedStyle = 'style="font-size:110%;font-weight:bold;"';
			echo '<table><tr><th>';
			if ( $type == 0 ) echo "<font $selectedStyle >Metadata</font>";
			else echo '<a href="'.self::getUrl(0,$this->dats->dats_id).'">Metadata</a>';
			echo '</th><th>';
			if ($type == 1) echo "<font $selectedStyle >Updates</font>";
			else echo '<a href="'.self::getUrl(1,$this->dats->dats_id).'">Updates</a>';
			echo '</th><th>';
			if ($type == 2) echo "<font $selectedStyle>Downloads</font>";
			else echo '<a href="'.self::getUrl(2,$this->dats->dats_id).'">Downloads</a>';
			echo '</th><th>';
			if ($type == 3) echo "<font $selectedStyle>Subscriptions</font>";
			else echo '<a href="'.self::getUrl(3,$this->dats->dats_id).'">Subscriptions</a>';
			echo '</th><th>';
			if ($type == 4) echo "<font $selectedStyle>Metadata quality</font>";
			else echo '<a href="'.self::getUrl(4,$this->dats->dats_id).'">Metadata quality</a>';
			echo '</th></tr>';
			echo '</table><br>';

			switch ($type) {
				case 1:
					$this->displayUpdates();
					break;
				case 2:
					$this->displayDownloadsHistory();
					break;
				case 3:
					$this->displaySubscriptions();
					break;
				case 4:
					$qual = new dats_quality();
					$qual->init($this->dats);
					$qual->display();
					break;
				default:
					editDataset($this->dats->dats_id,$this->projectName);
					
			}
		}else{
			echo "<font size=\"3\" color='red'><b>You cannot access this page.</b></font><br>";
		}
	}
	
	function displaySubscriptions(){
		$journal = new journal();
		$journal = $journal->getAbosByDats($this->dats->dats_id);
		if (isset($journal) && !empty($journal)){
			echo '<table>';
			echo '<tr><th>The following users asked to be informed of any change in this dataset:</th></tr>';
			foreach ($journal as $jEntry){
				echo '<tr><td>'.$jEntry->contact.'</td></tr>';
			}
			echo '</table>';
		}else{
			echo 'No user asked to be informed about this dataset';
		}
	}
	
	
	function displayUpdates(){
		//echo '<h2>Updates</h2>';
			
		$journal = new journal();
		$journal = $journal->getByDataset($this->dats->dats_id,TYPE_NEW.','.TYPE_UPDATE);
		echo '<table>';
		foreach ($journal as $jEntry){
			echo '<tr><td style="white-space:nowrap;"><b>'.$jEntry->date->format('Y-m-d').'</b></td><td>'.$jEntry->comment.'</td></tr>';
		}
		echo '</table>';
	}

	
	function displayDownloadsHistory(){
		//echo '<h2>Downloads</h2>';	
		$query = 'select date from journal where type_journal_id = '.TYPE_NEW.' and dats_id = '.$this->datsId.";";
		$bd = new bdConnect;
		$startI = 0;
		if ($resultat = $bd->get_data($query)){
			$date = $resultat[0][0];
			$datax[0] = strtotime($date);
			$datay[0] = 0;
			echo '<font size="3">Online since: '.substr($date,0,10).'</font><br/><br/>';
			$startI = 1;
		}
		$query = 'select date from journal inner join dataset using (dats_id) where type_journal_id = '.TYPE_DL.' and dats_id = '.$this->dats->dats_id." order by date;";
		//echo $query;
		$bd = new bdConnect;
		if ($resultat = $bd->get_data($query)){
			for ($i=$startI; $i < count($resultat) + $startI;$i++){
				$date = $resultat[$i-$startI][0];
				//echo "<br/>- $i ".substr($date, 0, 10);
				$datax[$i] = strtotime($date);
				$datay[$i] = $i + 1 - $startI;
			}
			$total = $i - $startI;
			echo "<font size='3'>Total downloads: $total</font><br/><br/>";
			$datax[$i] = time();
			$datay[$i] = $total;
			$graph = getGraphRequetesByDataset($datax,$datay);
			displayGraph($graph,"graph_dl_$datsId.png");
		}
		
		$this->displayDownloadsByUsers();
	}
	
	function displayDownloadsByUsers(){
		$query = 'select contact, count(*) from journal inner join dataset using (dats_id) where type_journal_id = '.TYPE_DL.' and dats_id = '.$this->dats->dats_id." group by dats_id,dats_title,contact order by count desc, contact;";
		$bd = new bdConnect;
		if ($resultat = $bd->get_data($query)){
			$liste = '<table>';
			for ($i=0; $i<count($resultat);$i++){
				$contact = $resultat[$i][0];
				$nbRequetes = $resultat[$i][1];
				$liste .= "<tr><td>$contact</td><td>$nbRequetes</td></tr>";
			}
			$liste .= '</table>';
			
			echo '<h2>Downloads by user</h2>';
			echo $liste;
		}
	}

}

?>