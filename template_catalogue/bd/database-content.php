<?php
require_once ('utils/phpwkhtmltopdf/WkHtmlToPdf.php');
require_once ('scripts/lstDataUtils.php');
require_once ("dataset.php");
require_once ("scripts/filtreProjets.php");
require_once ("/sites/kernel/#MainProject/conf.php");

// Database Identifiants
$db_name = DB_NAME;
$hote = DB_HOST;
$db_user = DB_USER;
$db = '';
$Dats_Projects = array ();
// Projects Ids
$Projects = array ();
$Dats_Projects = array ();
foreach ( $MainProjects as $pro ) {
	$Projects [$pro] = get_filtre_projets ( $pro );
}
foreach ( $OtherProjects as $pro ) {
	$Projects [$pro] = get_filtre_projets ( $pro );
}
class _Dataset {
	var $dats_id;
	var $url;
	var $dats_title;
	var $uids;
	var $sgbd;
	var $role;
	var $dd;
	var $df;
	var $ins_date;
	var $last_update;
	function _Dataset($url, $id, $name, $dateDeb, $dateFin, $uids, $sgbd, $role, $ins_date, $last_update) {
		$this->dats_id = $id;
		$this->url = '/' . $url;
		$this->dats_title = $name;
		$this->uids = $uids;
		$this->sgbd = $sgbd;
		$this->role = $role;
		$this->dd = $dateDeb;
		$this->df = $dateFin;
		$this->ins_date = $ins_date;
		$this->last_update = $last_update;
	}
}
class Plateforme {
	var $id; // map titre du domaine
	var $name; // Nom du domaine
	var $count;
	function Plateforme($id, $name) {
		$this->name = $name;
		$this->id = $id;
		$this->count = 0;
	}
	function addDataset($url, $id, $name, $dateDeb, $dateFin, $uids, $sgbd, $role, $ins_date, $last_update) {
		$this->ds [$this->count] = new _Dataset ( $url, $id, $name, $dateDeb, $dateFin, $uids, $sgbd, $role, $ins_date, $last_update );
		$this->count += 1;
	}
}
function searchPlateforme($plateformes, $id) {
	for($i = 0; $i < count ( $plateformes ); $i ++)
		if ($plateformes [$i]->id == $id)
			return $plateformes [$i];
}
function loadInPlateForme($db, $plateformes, $url, $result, $uids, $sgbd, $role, $ins_date, $last_update) {
	$req = "SELECT DISTINCT place.gcmd_plat_id 
			FROM place, dats_place, gcmd_plateform_keyword
			WHERE dats_place.dats_id=" . $result [0] . " 
			AND dats_place.place_id=place.place_id
 			AND  gcmd_plateform_keyword.gcmd_plat_id=place.gcmd_plat_id;";
	if (($res1 = pg_query ( $db, $req )) && ($count = pg_num_rows ( $res1 ))) {
		for($i = 0; $i < $count; $i ++) {
			$ar = pg_fetch_row ( $res1 );
			
			if ($count <= 1)
				$plateforme = searchPlateforme ( $plateformes, $ar [0] );
			else {
				if (! isset ( $first ))
					$first = $ar [0];
				if (($ar [0] != "1") && 	// "Geographic Regions")
				($ar [0] != "14") && 		// "Ground networks")
				($ar [0] != "23") && 		// "Fixed Observation Stations")
				($ar [0] != "22") && 		// "Ocean sites")
				($ar [0] != "16")) 			// "Hydrometeorological sites") )
					$plateforme = searchPlateforme ( $plateformes, $ar [0] );
			}
		}
		if (! isset ( $plateforme ))
			$plateforme = searchPlateforme ( $plateformes, $first );
	} else
		$plateforme = $plateformes [count ( $plateformes ) - 1];
	$plateforme->addDataset ( $url, $result [0], $result [1], $result [2], $result [3], $uids, $sgbd, $role, $ins_date, $last_update );
}
function initRoles($db) {
	$roles = array ();
	if ($res = pg_query ( $db, "SELECT * FROM role;" )) {
		for($i = 0; $i < pg_num_rows ( $res ); $i ++) {
			$ar = pg_fetch_row ( $res );
			$roles [$ar [0]] = $ar [1];
		}
	}
	return $roles;
}
function ecrireEtat($db, $plateformes, $isPDF = false) {
	global $project_name;
	$roles = initRoles ( $db );
	$server_response = "";
	
	foreach ( $plateformes as $p ) {
		if ($p->count) {
			if ($project_name == MainProject || $isPDF == true) {
				$server_response .= "<div class='panel panel-info' style = 'page-break-inside: auto;'>" . "<div class='panel-heading' style = 'page-break-inside: avoid;'><h3 class='panel-title' style = 'page-break-inside: avoid;'>" . $p->name . "</h3></div>" . "<div class='panel-body' style = 'page-break-inside: auto;'>" . "<table class='table table-striped' style = 'page-break-inside: auto;'>";
				$server_response .= "<thead style = 'page-break-inside: avoid;'>" . "<tr style = 'page-break-inside: avoid;'>" . "<th style = 'page-break-inside: avoid;'>Dataset Name</th>" . "<th style = 'page-break-inside: avoid;'>PIs Name</th>" . "<th style = 'page-break-inside: avoid;'>Period Begin</th>" . "<th style = 'page-break-inside: avoid;'>Period End</th>" . "<th style = 'page-break-inside: avoid;'></th>" . "</tr>" . "</thead>" . "<tbody style = 'page-break-inside: auto;'>";
			} else {
				$server_response .= "<br><center  style='border-width:1px;border-style:dashed;border-color:#31708f;'><h1>" . $p->name . "</h1><br></center><br>" . "<div style='border-width:1px;border-style:dashed;border-color:#31708f;'><table>";
				$server_response .= "<thead>" . "<tr>" . "<th max-width = 30% width = 30% >Name</th>" . "<th width = 5% >ID</th>" . "<th width = 15% >Insertion/last update</th>" . "<th width = 10% >PIs Name</th>" . "<th width = 10% >PIs Role</th>" . "<th width = 10% >Period Begin</th>" . "<th width = 10% >Period End</th>" . "<th width = 10% ></th>" . "</tr>" . "</thead>" . "<tbody>";
			}
			foreach ( $p->ds as $ds ) {
				if ($project_name == MainProject || $isPDF == true) {
					$server_response .= "<tr style = 'page-break-inside: avoid;'><td style = 'page-break-inside: avoid;' width = 40%><h5 style='color: #31708f;page-break-inside: avoid;'>" . $ds->dats_title . "</h5></td>" . "<td style = 'page-break-inside: avoid;' width = 25%><h5 style = 'page-break-inside: avoid;'>" . $ds->uids [0] . "</h5></td>" . "<td style = 'page-break-inside: avoid;' width = 10%><h5 style = 'page-break-inside: avoid;'>" . $ds->dd . "</h5></td>" . "<td style = 'page-break-inside: avoid;' width = 10%><h5 style = 'page-break-inside: avoid;'>" . $ds->df . "</h5></td>" . "<td style = 'page-break-inside: avoid;' width = 15%><h5 style = 'page-break-inside: avoid;'><a href=\"http://$_SERVER[HTTP_HOST]?editDatsId=$ds->dats_id&datsId=$ds->dats_id\">View metadata</a></h5></td></tr>";
				} else {
					$server_response .= "<tr><td style='color: #31708f;' max-width = 30% width = 30% >" . $ds->dats_title . "</td>" . "<td width = 5%>" . $ds->dats_id . "</td>";
					if ($ds->ins_date != $ds->last_update)
						$server_response .= "<td width = 15%>" . $ds->ins_date . "/ " . $ds->last_update . "</td>";
					else
						$server_response .= "<td width = 15%>" . $ds->ins_date . "</td>";
					
					$server_response .= "<td width = 10%>" . $ds->uids [0] . "</td><td width = 10%>";
					if(isset($roles [$ds->role [0]]) && !empty($roles [$ds->role [0]]))
						$server_response .= $roles [$ds->role [0]];
					$server_response .= "</td><td width = 10%>" . $ds->dd . "</td>" . "<td width = 10%>" . $ds->df . "</td>" . "<td width = 10%><a href=\"http://$_SERVER[HTTP_HOST]?editDatsId=$ds->dats_id&datsId=$ds->dats_id\">View metadata</a></td></tr>";
				}
			}
			$server_response .= "</tbody>" . "</table></div><br>";
			if ($project_name == MainProject || $isPDF == true) {
				$server_response .= "</div>";
			}
		}
	}
	return $server_response;
}
function ecrire($db, $result) {
	$req = "SELECT DISTINCT gcmd_plat_name 
			FROM place, dats_place, gcmd_plateform_keyword
			WHERE dats_place.dats_id=" . $result [0] . " 
			AND dats_place.place_id=place.place_id
 			AND  gcmd_plateform_keyword.gcmd_plat_id=place.gcmd_plat_id;";
	if ($res1 = pg_query ( $db, $req )) {
		$count = pg_num_rows ( $res1 );
		for($i = 0; $i < $count; $i ++) {
			$ar = pg_fetch_row ( $res1 );
			
			if ($count <= 1)
				echo "$count : $ar[0] : $result[0] : Name: $result[1] : $result[2] : $result[3]<br>";
			else {
				if (($ar [0] != "Geographic Regions") && ($ar [0] != "Ground networks") && ($ar [0] != "Fixed Observation Stations") && ($ar [0] != "Hydrometeorological sites"))
					echo "$count : $ar[0] : $result[0] : Name: $result[1] : $result[2] : $result[3]<br>";
			}
		}
	} else
		echo "Id:  $result[0]  Name: $result[1] $result[2] $result[3]<br>";
}
function lirePlateforme($db) {
	$plateformes = array ();
	if ($res = pg_query ( $db, "SELECT * FROM gcmd_plateform_keyword;" )) {
		for($i = 0; $i < pg_num_rows ( $res ); $i ++) {
			$ar = pg_fetch_row ( $res );
			$plateformes [$i] = new Plateforme ( $ar [0], $ar [1] );
		}
	}
	$plateformes [$i] = new Plateforme ( "9999", "Other" );
	return $plateformes;
}
function getDatasetsByProject($Project, $isPDF = false) {
	global $Projects, $project_name, $hote, $db_user, $db_name, $db;
	$server_resp = '';
	$elements = $Projects;
	while ( $element = current ( $elements ) ) {
		if ($element == $Project) {
			$ProjectName = key ( $elements ) . "\n";
		}
		next ( $elements );
	}
	reset ( $elements );
	$plateformes = lirePlateforme ( $db );
	$requete = "SELECT dats_id, dats_title, dats_date_begin, dats_date_end FROM dataset WHERE dats_id IN (SELECT DISTINCT dats_id FROM url WHERE url_type !='map') AND dats_id IN (SELECT DISTINCT dats_id FROM dats_proj WHERE project_id IN (" . $Project . "));";
	if ($res = pg_query ( $db, $requete )) {
		for($i = 0; $i < pg_num_rows ( $res ); $i ++) {
			$result = pg_fetch_row ( $res );
			$res1 = pg_query ( $db, "SELECT pers_name FROM dats_originators, personne" . " WHERE dats_id = $result[0] AND
  	             dats_originators.pers_id =personne.pers_id" );
			$uids = pg_fetch_row ( $res1 );
			$res1 = pg_query ( $db, "SELECT ins_dats_id FROM dats_data" . " WHERE dats_id = $result[0]" );
			$sgbd = pg_fetch_row ( $res1 );
			$res1 = pg_query ( $db, "SELECT role_id FROM dats_role" . " WHERE dats_id = $result[0]" );
			$role = pg_fetch_row ( $res1 );
			$res1 = pg_query ( $db, "SELECT url FROM url" . " WHERE dats_id = $result[0] AND url like '%Data-Download%'" );
			$url = pg_fetch_row ( $res1 );
			$res1 = pg_query ( $db, "SELECT date_insertion FROM inserted_dataset LEFT JOIN dats_data using (ins_dats_id)" . " WHERE dats_id = $result[0] " );
			$ins_date = pg_fetch_row ( $res1 );
			$res1 = pg_query ( $db, "SELECT date_last_update FROM inserted_dataset LEFT JOIN dats_data using (ins_dats_id)" . " WHERE dats_id = $result[0] " );
			$last_update = pg_fetch_row ( $res1 );
			loadInPlateForme ( $db, $plateformes, trim ( $url [0] ), $result, $uids, $sgbd, $role, $ins_date [0], $last_update [0] );
		}
		if ($project_name == MainProject || $isPDF == true) {
			$server_resp = ecrireEtat ( $db, $plateformes, true );
		} else {
			$server_resp = ecrireEtat ( $db, $plateformes );
		}
	}
	$res = pg_query ( $db, "SELECT count(*) FROM dataset " );
	$ds = pg_fetch_row ( $res );
	if ($i == 0)
		$server_resp = " nothing found for $ProjectName <br>";
	else
		$server_resp .= "found $i dataset(s) with Url on $ds[0] for $ProjectName<br>";
	
	return $server_resp;
}
function genPDF($Project_Name = null) {
	global $Dats_Projects, $project_name, $Projects, $root;
	$root = $_SERVER ['DOCUMENT_ROOT'];
	ob_end_clean ();
	$stylesheet = file_get_contents ( 'Bootstrap-Style/css/bootstrap.css', FILE_USE_INCLUDE_PATH );
	$pro_name = $Project_Name;
	if ($pro_name == null)
		$pro_name = $project_name;
	$project_content = getDatasetsByProject ( $Projects [$pro_name], true );
	$Content = <<<EOD
	              <html>
					<head>
						<title>".$pro_name." Database Content</title>
						<style type ="text/css">
							$stylesheet
						</style>
					</head>
					<body>
						<div class='container'>
							<br>
							<p class='navbar-text navbar-left'><h2 style='color: #31708f; text-align : center;'>$pro_name Database Content</h2></p><br>
							<br><br>	
							$project_content
	                    </div>
					</body>
				</html>
EOD;
	$pdf = new WkHtmlToPdf ( array (
			'encoding' => 'UTF-8',
			'zoom' => '0.75',
			'page-size' => 'A3',
			'orientation' => 'landscape',
			'binPath' => WKHTML_BIN_PATH,
			'margin-top' => 10,
			'margin-right' => 10,
			'margin-bottom' => 10,
			'margin-left' => 10,
			'no-background',
			'outline-depth' => '2' 
	) );
	$pdf->addPage ( $Content );
	$pdf->send ( $pro_name . "_database_content_" . date ( "Y-m-d H:i:s" ) . ".pdf", 'D' );
}
function fillProjectsTab($Project_Name = null) {
	global $Projects, $project_name, $Dats_Projects;
	reset ( $Projects );
	if ($project_name == MainProject) {
		while ( $element = current ( $Projects ) ) {
			$Dats_Projects [key ( $Projects )] = getDatasetsByProject ( $element );
			next ( $Projects );
		}
		reset ( $Projects );
	} else {
		$Dats_Projects [$project_name] = getDatasetsByProject ( $Projects [$project_name] );
	}
}
function displayPageByProject() {
	global $Dats_Projects, $project_name, $Projects, $root;
	$root = '';
	$Dats_Projects [$project_name] = getDatasetsByProject ( $Projects [$project_name] );
	echo "<div class='container'>
			<center>
				<p class='navbar-text navbar-left'>
				<h2 style='color: #31708f;'>$project_name Database Content</h2>
				</p>
			</center>
			<br>
		 <div id='content'><br>";
	echo "<form action='' method='post'>
		 		 <button name ='button_" . key ( $Dats_Projects ) . "' title='Export " . key ( $Dats_Projects ) . " database content to pdf' style='background :transparent;float: right;margin-right:10px;border:0px;'  ><img src='/img/pdf-icone-32.png' style='border:0px;' /></button><br>" . "</form>" . "<br>" . $Dats_Projects [$project_name];
	reset ( $Dats_Projects );
	echo "</div></div>";
}
function displayPage() {
	global $Dats_Projects, $project_name, $root;
	$root = '';
	$pro = $_REQUEST ['project'];
	reset ( $Dats_Projects );
	fillProjectsTab ();
	echo "<!DOCTYPE html>
	<html>
	<head>
    <meta charset='utf-8' />
	<title>Database Content</title>
	<link rel='stylesheet' href='/Bootstrap-Style/css/bootstrap.css'>
	<link rel='stylesheet' href='/Bootstrap-Style/css/bootstrap.min.css'>
	</head>
	<body>
	<div class='container'>
		<nav class='navbar navbar-default' style='background: transparent;'
			role='navigation'>
			<center>
				<p class='navbar-text navbar-left'>
				<h2 style='color: #31708f;'>".MainProject." Database Content</h2>
				</p>
			</center>
			<br>
		</nav>
		<div id='content'>
			
			<ul id='tabs' class='nav nav-tabs nav-justified' data-tabs='tabs'>";
	while ( $proj = current ( $Dats_Projects ) ) {
		if ($project_name == key ( $Dats_Projects ))
			echo "<li id ='tab" . key ( $Dats_Projects ) . "' class='active'>";
		else
			echo "<li id ='tab" . key ( $Dats_Projects ) . "'>";
		echo "<a href='#" . key ( $Dats_Projects ) . "' data-toggle='tab'>
								<h4>" . key ( $Dats_Projects ) . "</h4>
							</a>
						</li>";
		next ( $Dats_Projects );
	}
	reset ( $Dats_Projects );
	echo "</ul><br>";
	echo "<div id='my-tab-content' class='tab-content'>";
	while ( $proj = current ( $Dats_Projects ) ) {
		if ($project_name == key ( $Dats_Projects ))
			echo "<div class='tab-pane active' id='" . key ( $Dats_Projects ) . "'>";
		else
			echo "<div class='tab-pane' id='" . key ( $Dats_Projects ) . "'>";
		echo "<form action='' method='post'>
		 		 <button name ='button_" . key ( $Dats_Projects ) . "' title='Export " . key ( $Dats_Projects ) . " database content to pdf' style='border:0px;float: right; margin-right:10px;background:transparent;'  ><img src='/img/pdf-icone-32.png' style='border:0px;float: right; margin-right:10px;' /></button><br>" . "</form>
			   <br>
			   <div>$proj</div>
			   </div>";
		next ( $Dats_Projects );
	}
	
	echo "</div>
		 </div>
		 </div>
		 <script src='/utils/jquery-ui-1.9.2/jquery-1.8.3.js'></script>
		 <script type='text/javascript'>
		 jQuery(document).ready(function ($) {
		 	$('#tabs').tab();
		 });
		 </script>
		 <script src='/Bootstrap-Style/js/bootstrap.min.js'></script>
		 <script src='/Bootstrap-Style/js/bootstrap.js'></script>
		 <script type='text/javascript'>
			$('.nav-tabs a[href=\"#" . $pro . "\"]').tab('show');
		 </script>
		 </body> 
		 </html>";
}

?>
