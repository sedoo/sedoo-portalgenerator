<?php

require_once ("bd/dats_role.php");
require_once ("bd/dataset.php");
require_once ('filtreProjets.php');
require_once ("conf/conf.php");

echo "<h1>Database Roles</h1><p/>";

$projects = 'SELECT DISTINCT dats_id FROM dats_proj WHERE project_id IN ('.get_filtre_projets($project_name).')';

$r = new role;
$listeRoles = "'".PUBLIC_DATA_ROLE."'";
$roles = explode(',',constant(strtolower($project_name).'ListRoles'));
$string_roles=null;
foreach($roles as $rol){
	$string_roles .= "'".$rol."'";
	$string_roles .= ",";
}
$listeRoles .= ",".substr_replace($string_roles, "", -1);


$roles = $r->getByQuery("SELECT * FROM role WHERE role_name IN ($listeRoles)");

$dts = new dataset;
$query = "";
$liste_dats = $dts->getOnlyTitles("SELECT dats_id, dats_title FROM dataset WHERE dats_id IN ($projects) AND dats_id IN (SELECT DISTINCT dats_id FROM url WHERE url_type != 'map') AND dats_id IN (SELECT DISTINCT dats_id FROM dats_role) ORDER BY dats_title");

echo '<table><tr><th align="center">Dataset</th>';
foreach($roles as $role){
	echo "<th align='center'>$role->role_name</th>";
}
echo '<th></th></tr>';
 $dr = new dats_role();
foreach($liste_dats as $dats){
	echo "<tr><td><a href='$project_url/?editDatsId=$dats->dats_id'>$dats->dats_title</a></td>";
	$cpt = 0;
	$isCore = false;
	$isAsso = false;	
	foreach($roles as $role){
		$liste = $dr->getByQuery("SELECT * FROM dats_role WHERE dats_id = $dats->dats_id AND role_id = $role->role_id");
		if (empty($liste))
			echo '<td></td>';
		else{
			echo '<td align="center">X</td>';
			$cpt++;
			if ( stripos($role->role_name,'Core') > 0){
				$isCore = true;
			}else if (stripos($role->role_name,'Asso') > 0){
				$isAsso = true;
			}
		}
	}

	$comment = '';
	if ($cpt == 0){
		$comment = "No role defined for $project_name users.";
	}else{
		if ($isAsso && !$isCore){
			$comment = 'This dataset should also be accessible to core users.';
		}
	}
	if ($comment){
		echo "<td><a href='$project_url/Admin-Corner?adm&pageId=6&datsId=$dats->dats_id'><img src='/img/avertissement-icone-16.png' title='$comment' /></a></td>";
	}else{
		echo "<td><a href='$project_url/Admin-Corner?adm&pageId=6&datsId=$dats->dats_id'><img src='/img/modifier-icone-16.png' title='Edit'></a></td>";
	}

	echo '</tr>';

}
echo '</table>';


?>
