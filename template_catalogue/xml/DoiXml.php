<?php
require_once ("bd/dataset.php");
require_once ("bd/project.php");
require_once ("xml/DoiXmlTemplate.php");
require_once ('scripts/doiUtils.php');
/*
function siteProgramme($site){
if ($site == "HyMeX")
                return 'http://www.hymex.org/';
        else if ($site == "ChArMEx")
                return 'https://charmex.lsce.ipsl.fr/';
        else if ($site == "MERMeX")
                return '';
        else if ($site == "MOOSE")
                return 'http://www.moose-network.fr/';
        else if ($site == "EMSO")
                return '';
	else if ($site == "TerMEx")
                return '';
	else if ($site == "CORSiCA")
                return 'http://www2.obs-mip.fr/corsica';
	
}
*/
function parseProjets($projets){
	$result = array();
	foreach($projets as $p){
		if (isset($p->parent_project)){
			$result = array_merge($result,parseProjets(array($p->parent_project)));
		}else{
			$result[$p->project_id] = $p;
		}
	}
	return $result;
}


function getProjectsName($projects){
	$result = array();
	ksort($projects);
	foreach ( $projects as $id => $proj ) {
		if ($id == 0){
			$result[$id] = $proj;
		}else{
			$result[$id] = $proj->project_name;
		}
	}
	return implode('-',$result);
}

function getProjects($id){
	$query = "select * from project where project_id in (select project_id from dats_proj where dats_id = $id)";
	$p = new project();
	$projets = $p->getByQuery($query);
	$result = parseProjets($projets);
	$result[0] = 'MISTRALS';
	//$result = array_unique($result);
	//Pour que MISTRALS soit en premier
	
	//return implode('-',$result);
	return $result;
}

/*
define('TYPE_DL',3);
define('TYPE_ABO',1);
define('TYPE_NEW',2);
define('TYPE_UPDATE',4);
define('TYPE_CHANGES',5);
*/

function createDoiXml($id, $xmlstr,$project_name) {
	$bd = new bdConnect ();
	$bd->db_open ();

	global $xmlstr;
	$ressource = simplexml_load_string ( $xmlstr );
	
	if (isset ( $id ) && ! empty ( $id )) {
		
		$projects = getProjects($id);
		
		$max=8;	
		$identifier = $ressource->addChild ( 'identifier', DOI_PREFIX.getProjectsName($projects).'.'.$id );
		$identifier->addAttribute ( 'identifierType', 'DOI' );
				
		$relatedIdentifiers = $ressource->addChild ( 'relatedIdentifiers' );
		$relatedIdentifier = $relatedIdentifiers->addChild ( 'relatedIdentifier', 'http://mistrals.sedoo.fr' );
		$relatedIdentifier->addAttribute ( 'relatedIdentifierType', 'URL' );
		$relatedIdentifier->addAttribute ( 'relationType', 'References' );
		
		foreach($projects as $projectId => $project){
			if ($projectId > 0 && isset($project->project_url)){
				$relatedIdentifier = $relatedIdentifiers->addChild ( 'relatedIdentifier', $project->project_url );
				$relatedIdentifier->addAttribute ( 'relationType', 'References' );
				$relatedIdentifier->addAttribute ( 'relatedIdentifierType', 'URL' );
			}
		}
		
	}
	
	$ressourceType = $ressource->addChild ( 'resourceType', 'Dataset' );
	$ressourceType->addAttribute ( 'resourceTypeGeneral', 'Dataset' );
	
	$publisher = $ressource->addChild ( 'publisher', 'SEDOO OMP' );
	
	$creators = $ressource->addChild ( 'creators' );
	
	$query = "select pers_name from personne left join dats_originators using (pers_id) where dats_id=$id";
	$resultat = $bd->exec ( $query );
	while ( $creatName = pg_fetch_assoc ( $resultat ) ) {
		$creator = $creators->addChild ( 'creator' );
		$creatorName = $creator->addChild('creatorName',$creatName ['pers_name']);
	}
	
	$titles = $ressource->addChild('titles');
	$query = "select dats_title from dataset where dats_id=$id";
	$req = $bd->exec ( $query );
	$title = pg_fetch_assoc ( $req );
	$title = $titles->addChild('title',$title['dats_title']);
	
	$type = TYPE_NEW;
	$query = "select extract (year from date) as year from journal where type_journal_id =$type and dats_id = $id";
	$req = $bd->exec ( $query );
	$publicationYear = pg_fetch_assoc ( $req );
	if (!empty($publicationYear['year'])){
		$publicationYear = $ressource->addChild ( 'publicationYear', $publicationYear ['year'] );
	}
	
	$subjects = $ressource->addChild('subjects');
	$query = "select var_name, gcmd_name from dats_var d join variable v on d.var_id=v.var_id join gcmd_science_keyword g on v.gcmd_id=g.gcmd_id where d.dats_id=$id";
	$resultat = $bd->exec ( $query );
	while ( $param = pg_fetch_assoc ( $resultat ) ) {
		if ($param['var_name'] !=""){
		$subject = $subjects->addChild('subject', $param['var_name']);
		$subject->addAttribute('subjectScheme','Parameter');
		}else{
			$subject = $subjects->addChild('subject', $param['gcmd_name']);
			$subject->addAttribute('subjectScheme','Parameter');
		}
	}
	
	$contributors = $ressource->addChild('contributors');
	$query = "select pers_email_1 from personne left join dats_originators using (pers_id) where dats_id=$id";
	$resultat = $bd->exec ( $query );
	while ($contributorName = pg_fetch_assoc ( $resultat )){
		$contributor = $contributors->addChild('contributor');
		$contributor->addAttribute('contributorType','ContactPerson');
		$contributorName = $contributor->addChild('contributorName',$contributorName['pers_email_1']);
	}
	$contributor = $contributors->addChild('contributor');
	$contributor->addAttribute('contributorType','DataManager');
	$contributorName = $contributor->addChild('contributorName','SEDOO-OMP');
	
	$query = "select dats_date_begin, dats_date_end from dataset where dats_id=$id";
	$resultat = $bd->exec ( $query );
	$date = pg_fetch_assoc ( $resultat );
	if (!empty($date['dats_date_begin']) && !empty($date['dats_date_end'])){
		$dates = $ressource->addChild('dates');
		$date = $dates->addChild('date',$date['dats_date_begin']."/ ".$date['dats_date_end']);
		$date->addAttribute('dateType','Collected');
	}
	
	$language = $ressource->addChild('language','en');
	
	$query = "select data_format_name from data_format left join dats_data_format using (data_format_id) where dats_id=$id";
	$resultat = $bd->exec ( $query );
	$format = pg_fetch_assoc ( $resultat );
	if (! empty ( $format ['data_format_name'] )) {
		$formats = $ressource->addChild ( 'formats' );
		$format = $formats->addChild ( 'format', $format ['data_format_name'] );
	}
	
	$rightsList = $ressource->addChild('rightsList');
	$query = "select dats_use_constraints from dataset where dats_id=$id";
	$resultat = $bd->exec ( $query );
	$rights = pg_fetch_assoc ( $resultat );
	if (! empty ( $rights ['dats_use_constraints'] )) {
		$rights = $rightsList->addChild ( 'rights', strip_tags ( $rights ['dats_use_constraints'] ) );
		
		$query = "select data_policy_name, data_policy_url from data_policy left join dataset using(data_policy_id) where dats_id=$id";
		//error_log ( $query );
		$resultat = $bd->exec ( $query );
		$right = pg_fetch_assoc ( $resultat );
		if (! empty ( $right ['data_policy_url'] )) {
			$rights = $rightsList->addChild ( 'rights', $right ['data_policy_name'] );
			$rights->addAttribute ( 'rightsURI', $right ['data_policy_url'] );
		}
	}
	
	$query = "select dats_abstract from dataset where dats_id=$id";
	$resultat = $bd->exec ( $query );
	$desc = pg_fetch_assoc ( $resultat );
			
	if (!empty($desc['dats_abstract'])){
		$descriptions = $ressource->addChild ( 'descriptions' );
		$var = htmlspecialchars ( $desc ['dats_abstract'] );
		$description = $descriptions->addChild ( 'description', $var );
		$description->addAttribute ( 'descriptionType', 'Abstract' );
	}
	
	$geoLocations = $ressource->addChild ( 'geoLocations' );
	
	$query = "select place_name from place left join dats_place using (place_id) where dats_id=$id";
	$resultat = $bd->exec ( $query );
	
	
	while ($geolocP= pg_fetch_assoc ( $resultat )){
		
		$geoLocation = $geoLocations->addChild('geoLocation');
		$geoLocationPlace = $geoLocation->addChild('geoLocationPlace', $geolocP['place_name']);
	}
	
	$query1 = "select west_bounding_coord, east_bounding_coord, north_bounding_coord, south_bounding_coord from boundings left join place using (bound_id) left join dats_place using (place_id) where dats_id=$id";
	$query2 = "select west_bounding_coord, east_bounding_coord, north_bounding_coord, south_bounding_coord from boundings left join sensor using (bound_id) left join dats_sensor using (sensor_id) where dats_id=$id";
	
	$resultat1 = $bd->exec($query1);
	$resultat2 = $bd->exec($query2);
	$geoBox1 = pg_fetch_assoc ( $resultat1 );
	$geoBox2 = pg_fetch_assoc ( $resultat2 );
	if ($geoBox1 != 0){
		
		$geoLocation = $geoLocations->addChild('geoLocation');
		$geoLocationPoint = $geoLocation->addChild('geoLocationPoint', (($geoBox1['west_bounding_coord']+$geoBox1['east_bounding_coord']) /20000)." ".(($geoBox1['north_bounding_coord']+$geoBox1['south_bounding_coord']) /20000));
		$geoLocation = $geoLocations->addChild('geoLocation');
		$geoLocationBox = $geoLocation->addChild('geoLocationBox', $geoBox1['south_bounding_coord']." ".$geoBox1['west_bounding_coord']." ".$geoBox1['north_bounding_coord']." ".$geoBox1['east_bounding_coord']);
	}else
	if ($geoBox2 != 0){
		$geoLocation = $geoLocations->addChild('geoLocation');
		$geoLocationPoint = $geoLocation->addChild('geoLocationPoint', (($geoBox2['west_bounding_coord']+$geoBox2['east_bounding_coord']) /20000)." ".(($geoBox2['north_bounding_coord']+$geoBox2['south_bounding_coord']) /20000));
		$geoLocation = $geoLocations->addChild('geoLocation');
		$geoLocationBox = $geoLocation->addChild('geoLocationBox', $geoBox2['south_bounding_coord']." ".$geoBox2['west_bounding_coord']." ".$geoBox2['north_bounding_coord']." ".$geoBox2['east_bounding_coord']);
	}
	
	return $ressource;
}



?>
