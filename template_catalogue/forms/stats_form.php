<?php

require_once("bd/journal.php");
require_once("conf/conf.php");
require_once("ldap/constants.php");
require_once("countries.php");
require_once('forms/graph_utils.php');
require_once('scripts/filtreProjets.php');

class stats_form extends login_form{

	var $statsUsers;

	var $projectName;
	var $filtreProjets;

	function createForm($projectName){
		$this->projectName = $projectName;
		
		if (isset($_SESSION['loggedUser'])){
			$this->user = unserialize($_SESSION['loggedUser']);
		}

		if ((($projectName != MainProject) && $this->isProjectAdmin() )
		        || (($projectName == MainProject) && $this->isPortalAdmin()) ){
			
			$this->statsUsers = $this->getNbEnregistrements();
			$this->filtreProjets = 'and dats_id in (select distinct dats_id from dats_proj where project_id in ('.get_filtre_projets($projectName).'))';
		}else{
			$this->createLoginForm('Mail');
		}
	}

	private function toDate($str){
		if (isset($str) && !empty($str)){
			list($day, $month, $year) = sscanf($str, '%04d%02d%02d');
			return new DateTime("$year-$month-$day");	
		}
		return new DateTime('1900-01-01');
	}

	function displayNbEnregistrements(){
		echo '<h1>User registrations ('.$this->statsUsers[0][0].')</h1><br><br>';
		
		if ($this->statsUsers[0][0] > 0) {
			if(isset($_REQUEST['stype']) && !empty($_REQUEST['stype']))
				$stype = $_REQUEST['stype'];
			else 
				$stype = null;
			$selectedStyle = 'style="font-size:110%;font-weight:bold;"';
			echo '<table><tr><th>';
			if ($stype == 0) echo "<font $selectedStyle >By date</font>";
			else echo '<a href="'.self::getUrl(1,0).'">By date</a>';
			echo '</th><th>';
			
			if ($this->projectName != MainProject){
			if ($stype == 1) echo "<font $selectedStyle>By role</font>";
			else echo '<a href="'.self::getUrl(1,1).'">By role</a>';
			echo '</th><th>';
			}
			
			if ($stype == 2) echo "<font $selectedStyle>By country</font>";
			else echo '<a href="'.self::getUrl(1,2).'">By country</a>';
			echo '</th></tr>';
			echo '</table><br>';
	
			switch ($stype) {
				case 1:
					$graph = getGraphRoles($this->statsUsers);
					displayGraph($graph,"graph_roles_$this->projectName.png");
					//$this->displayUsersByRole();
					break;
				case 2:
					$graph = getGraphCountries($this->statsUsers);
					displayGraph($graph,"graph_pays_$this->projectName.png");
					//$this->displayUsersByCountry();
					break;
				default:
					$graph = getGraphUsers($this->statsUsers, $this->projectName);
					displayGraph($graph,"graph_users_$this->projectName.png");
					//$this->displayByMonth($this->statsUsers);
			}
		}
	}

	function displayUsersByRole(){
		echo '<table>';
		foreach($this->statsUsers['r'] as $r => $nb){
			echo "<tr><td>$r</td><td>$nb</td></tr>";
		}
		echo '</table>';
	}

	function displayUsersByCountry(){
		echo '<table>';
		foreach($this->statsUsers['c'] as $c => $nb){
			$cName = countries::getDisplayName($c);
			echo "<tr><td>$cName</td><td>$nb</td></tr>";
		}
		echo '</table>';
	}

	function getNbEnregistrements(){
		global $MainProjects;				
		$resultat = array();
		$resultat[0][0] = 0;
		$resultat['c'] = array();
		$resultat['r'] = array();
		if ($this->projectName == MainProject) {
			$userClass = strtolower(MainProject).'User';
			$roles = array (
					strtolower(MainProject) 
			);
			$regDateAttr = strtolower(MainProject)."RegistrationDate";
			$statusAttr = strtolower(MainProject)."Status";
		} else if ($this->projectName != MainProject) {
			foreach ( $MainProjects as $project ) {
				if ($this->projectName == $project) {
					$userClass = strtolower ( $project ) . 'User';
					$roles = array (
							strtolower ( $project ) . 'Asso',
							strtolower ( $project ) . 'Core' 
					);
					$regDateAttr = strtolower ( $project ) . "RegistrationDate";
					$statusAttr = strtolower ( $project ) . "Status";
				}
			}
		}else return $resultat;
		
		
		$ldapconn = ldap_connect(LDAP_HOST, LDAP_PORT);
		if ($ldapconn){
			$ldapbind = ldap_bind($ldapconn, $this->user->dn,$this->user->userPassword);
			if ($ldapbind) {
				$entries = ldap_search($ldapconn, PEOPLE_BASE, '(&(objectClass='.REGISTERED_USER_CLASS.')(objectClass='.$userClass.")($statusAttr=".STATUS_ACCEPTED.'))',array("mail", $regDateAttr,"memberOf","c"));
				$cpt=1;
                                $entry = ldap_first_entry($ldapconn,$entries);
                                while ($entry){
                                        $attrs = ldap_get_attributes($ldapconn, $entry);
                                        //$dn = ldap_get_dn($ldapconn, $entry);
					$mail = $attrs["mail"][0];
					$c = $attrs["c"][0];
					$regDate = $this->toDate($attrs[$regDateAttr][0]);
					$year = $regDate->format('Y');
					$month = $regDate->format('n');
					if (!array_key_exists($year,$resultat)) $resultat[$year][0] = 0;
					if (!array_key_exists($month,$resultat[$year])) $resultat[$year][$month] = 0;
					if (!array_key_exists($c,$resultat['c'])) $resultat['c'][$c] = 0;
					if (stripos(EXCLUDE_USERS,$mail) === false){
						$resultat[$year][0] += 1;
						$resultat[$year][$month] += 1;
						$resultat[0][0] += 1;
						$resultat['c'][$c] += 1;
						foreach($attrs['memberOf'] as $role){
							if (in_array($role,$roles)){
							//if ($key != 'count'){
								if (!array_key_exists($role,$resultat['r']))
									$resultat['r'][$role] = 0;
								$resultat['r'][$role] += 1;
							}
						}
					}
					//echo '<br>'.$mail.': '.$appDate->format('Y m d').' - '.$regDate->format('Y m d');
                                        $entry = ldap_next_entry($ldapconn,$entry);
                                }
				arsort($resultat['c']);
			}else{
				$errorCode = ldap_errno($ldapconn);
				echo "Error code: $errorCode<br>";
			}
			ldap_close($ldapconn);
		}else{
			//Impossible de se connecter au serveur LDAP
		}
		
		
		return $resultat;	
	}

	function getNbRequetesByMonth(){
		$query = 'select extract(year from date) as year,extract(month from date) as month,count(*) from journal where type_journal_id = '.TYPE_DL.' and contact not in ('.EXCLUDE_USERS.") $this->filtreProjets group by year,month;";
		//echo "$query<br>";
		$bd = new bdConnect;
		$requetes = array();
		$requetes[0][0] = 0; 
		if ($resultat = $bd->get_data($query)){
			for ($i=0; $i<count($resultat);$i++){
				$year = $resultat[$i][0];
				$month = $resultat[$i][1];
				$nbRequetes = $resultat[$i][2];
				if (!array_key_exists($year,$requetes)) $requetes[$year][0] = 0;
				$requetes[$year][$month] = $nbRequetes;
				$requetes[$year][0] += $nbRequetes;
				$requetes[0][0] += $nbRequetes;
			}
		}
		return $requetes;
	}

	function getNbRequetesByUser(){
		$query = 'select contact, count(*) from journal where type_journal_id = '.TYPE_DL.' and contact not in ('.EXCLUDE_USERS.") $this->filtreProjets group by contact order by count desc;";
		$bd = new bdConnect;
		$requetes = array();
		if ($resultat = $bd->get_data($query)){
			for ($i=0; $i<count($resultat);$i++){
				$contact = $resultat[$i][0];
				$nbRequetes = $resultat[$i][1];
				$requetes[$contact] = $nbRequetes;
			}
		}
		return $requetes;
	}

	function getNbRequetesByDataType(){
		$query = "select coalesce(dats_type_title,'IN SITU') as type, count(*) from journal inner join dataset using (dats_id) left join dats_type using (dats_id) left join dataset_type using (dats_type_id)  where type_journal_id = ".TYPE_DL.' and contact not in ('.EXCLUDE_USERS.") $this->filtreProjets group by type order by type";
		//echo "$query<br>";
		$bd = new bdConnect;
		$requetes = array();
		if ($resultat = $bd->get_data($query)){
			for ($i=0; $i<count($resultat);$i++){
				$type = $resultat[$i][0];
				$nbRequetes = $resultat[$i][1];
				$requetes[$type] = $nbRequetes;
			}
		}
		return $requetes;
	}

	function getNbRequetesByDataset(){
		$query = 'select dats_id,dats_title, count(*) from journal inner join dataset using (dats_id) where type_journal_id = '.TYPE_DL.' and contact not in ('.EXCLUDE_USERS.") $this->filtreProjets group by dats_id,dats_title order by count desc;";
		$bd = new bdConnect;
		$requetes = array();
		if ($resultat = $bd->get_data($query)){
			for ($i=0; $i<count($resultat);$i++){
				$datsId = $resultat[$i][0];
				$datsTitle = $resultat[$i][1];
				$nbRequetes = $resultat[$i][2];
				$requetes[$datsId]['nbRequetes'] = $nbRequetes;
				$requetes[$datsId]['titre'] = $datsTitle;
			}
		}
		return $requetes;

	}

	private static function getUrl($type, $stype = 0){
		global $project_name;
		if(isset($_REQUEST['id_rubrique']) && !empty($_REQUEST['id_rubrique']))
			$rubriqueId = $_REQUEST['id_rubrique'];
		else
			$rubriqueId = null;
		if(isset($_REQUEST['page']) && !empty($_REQUEST['page']))
			$page = $_REQUEST['page'];
		else
			$page=null;
		$spipAttrs = '';
		if ( $rubriqueId && $page)
			$spipAttrs = "&page=$page&id_rubrique=$rubriqueId";
		return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)."?adm&pageId=7&type=$type&proj=$project_name&stype=$stype$spipAttrs";
	}

	function display($datsId = 0){
		if(isset($_REQUEST['type']) && !empty($_REQUEST['type']))
			$type = $_REQUEST['type'];
		else
			$type = null;
		switch ($type) {
			case 1:
				$this->displayNbEnregistrements();
				break;
			default:
				$this->displayStatsRequetes();
		}
	}

	function displayStatsRequetes(){
		echo '<h1>Data downloads</h1><br><br>';
		if(isset($_REQUEST['stype']) && !empty($_REQUEST['stype']))
			$stype = $_REQUEST['stype'];
		else 
			$stype = null;
		$selectedStyle = 'style="font-size:110%;font-weight:bold;"';
		echo '<table><tr><th>';
		if ($stype == 0) echo "<font $selectedStyle >By date</font>";
		else echo '<a href="'.self::getUrl(0,0).'">By date</a>';
		echo '</th><th>';
		if ($stype == 3) echo "<font $selectedStyle>By data type</font>";
		else echo '<a href="'.self::getUrl(0,3).'">By data type</a>';
		echo '</th><th>';
		if ($stype == 1) echo "<font $selectedStyle>By user</font>";
		else echo '<a href="'.self::getUrl(0,1).'">By user</a>';
		echo '</th><th>';
		if ($stype == 2) echo "<font $selectedStyle>By dataset</font>";
		else echo '<a href="'.self::getUrl(0,2).'">By dataset</a>';
		echo '</th></tr>';
		echo '</table><br>';

		switch ($stype) {
			case 1:
				$this->displayNbRequetesByUser();
				break;
			case 2:
				$this->displayNbRequetesByDataset();
				break;
			case 3:
				$requetes = $this->getNbRequetesByDataType();
				if (!empty($requetes)){
					$graph = getGraphDataTypes($requetes);
					displayGraph($graph,"graph_req_type_$this->projectName.png");
				}
				break;
			default:
				$this->displayNbRequetesByMonth();
		}
	}

	function displayNbRequetesByDataset(){
                $requetes = $this->getNbRequetesByDataset();
                echo '<table>';//<tr><th colspan="2">Nombre de téléchargements par jeu de données</th></tr>';
                foreach($requetes as $mail => $jeu){
                        echo '<tr><td>'.$jeu['titre'].'</td><td>'.$jeu['nbRequetes'].'</td></tr>';
                }
                echo '</table>';
        }

	function displayNbRequetesByUser(){
		$requetes = $this->getNbRequetesByUser();
		echo '<table>';//<tr><th colspan="2">Nombre de téléchargements par utilisateur</th></tr>';
		echo '<tr><th colspan="2">'.count($requetes).' users have already downloaded data files.</th></tr>';
		foreach($requetes as $mail => $nb){
			echo "<tr><td>$mail</td><td>$nb</td></tr>";
		}
		echo '</table>';
	}

	function displayNbRequetesByMonth(){
		global $project_name;
		$yDeb = STATS_DEFAULT_MIN_YEAR;
		if (constant(strtolower($project_name).'yDeb') != ''){
			$yDeb = constant(strtolower($project_name).'yDeb');
		}
		$requetes = $this->getNbRequetesByMonth();
		$graph = getGraphByYear($requetes, $yDeb);
		displayGraph($graph,"graph_req_year_".$this->projectName.".png");
		echo '<br>';
				
	    $yFin = date('Y');
	    for ($y = $yDeb;$y <= $yFin;$y++){
			$graph = getGraphByMonth($requetes,$y);
			displayGraph($graph,'graph_req_month_'.$y.'_'.$this->projectName.'.png');
		}
	}

	function displayByMonth($requetes){
		$yDeb = 2011;
		$yFin = date('Y');
		//$requetes = $this->getNbRequetesByMonth();
		//print_r($requetes);
		$align = 'align="center"';
		//echo '<table><tr><th></th><th></th><th colspan="3">Nombre de téléchargements</th><tr>';
		echo '<table><tr><th></th><th></th><th>by month</th><th>by year</th><th>total</th><tr>';
		for ($y = $yDeb;$y <= $yFin;$y++){
			$mFin = ($y == $yFin)?date('n'):12;
			//$mFin = 12;
			echo "<tr><td rowspan='$mFin'>$y</td>";
			for ($m = 1;$m <= $mFin;$m++){
				if ($m > 1) echo '<tr>';
				$m2 = sprintf('%1$02d',$m);
				if (array_key_exists($y,$requetes) && array_key_exists($m,$requetes[$y]))
					$nb = $requetes[$y][$m];
				else
					$nb = 0;
				echo "<td>$m2</td><td $align>$nb</td>";
				if ($m == 1){
					echo "<td rowspan='$mFin' $align>".$requetes[$y][0].'</td>';		
					if ($y == $yDeb){
						$nbRow = ($yFin - $yDeb) * 12 + $mFin;
						echo "<td rowspan='$nbRow' $align>".$requetes[0][0].'</td>';
					}
				}
				echo '</tr>';
			}
		}
		echo '</table>';
	}
}


?>
