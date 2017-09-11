<?php

require_once ('bd/dataset.php');

define('RBV_WEB_SERVICE','http://portailrbvws.sedoo.fr/rest/Integration/');
define('WS_UPDATE_METHOD','addOrUpdateWithId');
define('WS_XML_FORMAT','RBV');
define('WS_LOGIN','ohmcv');
define('WS_PASSWD','sedooohmcv');

define('WS_EXIST_METHOD','exist');
define('XML_TEMPLATE','http://mistralstest.sedoo/xml/rbv.xml');
define('UUID','74bbe692-584b-11e3-%1$04d-ce3f5508acd9');


$default_basin = 'Cevennes-Vivarais';
$basins = array('Cevennes-Vivarais','Ardèche','Gardons');

$dats = new dataset();

//656 993 994 996
//DSD: 321 680 681 682 740 733 743 679 735 745 436 744 739 742 737 738 736 734
//ESPACE: 895 988 986 987
//EXC : 846 887

$dats = $dats->getById(740);

//$idHex = dechex($dats->datsId);

$xml = simplexml_load_file(XML_TEMPLATE);

$xml->identification->title = $dats->dats_title;
$xml->identification->abstract = $dats->dats_abstract;

if (isset($dats->dats_doi) && !empty($dats->dats_doi)){
	$xml->identification->identifiers->identifier[0] = '';
	$xml->identification->identifiers->identifier[0]['code'] = $dats->dats_doi;
	$xml->identification->identifiers->identifier[0]['nameSpace'] = 'DOI';
}

$i = 0;
foreach ($dats->originators as $originator){
	//<contact email="jean.breille@ird.fr" person="Jean Breille" organisation="IRD" role="AUTHOR" />
	$xml->identification->contacts->contact[$i] = '';
	$xml->identification->contacts->contact[$i]['email'] = $originator->pers_email_1;
	$xml->identification->contacts->contact[$i]['person'] = $originator->pers_name;
	if (isset($originator->organism) && !empty($originator->organism)){
		$xml->identification->contacts->contact[$i]['organisation'] = $originator->organism->org_sname;
	}
	if ($originator->isPI()){
		$xml->identification->contacts->contact[$i]['role'] = 'principalInvestigator';
	}else{
		$xml->identification->contacts->contact[$i]['role'] = 'pointOfContact';
	}		
	$i++;
}

//TODO
$xml->identification->urls->url[1] = '';
$xml->identification->urls->url[1]['link'] = 'http://mistrals.sedoo.fr/HyMeX/?editDatsId='.$dats->dats_id.'&project_name=HyMeX';
$xml->identification->urls->url[1]['label'] = 'HyMeX database';

//TODO uuid
$uuid = sprintf(UUID,$dats->dats_id);
$xml->metametadata->uuid = $uuid;

//TODO $xml->localisation->box>
//<box north="1.0" south="3.0" east="2.0" west="4.0" />
/*
$xml->localisation->box[0] = '';
$xml->localisation->box[0]['south'] = '44.562';
$xml->localisation->box[0]['north'] = '44.579';
$xml->localisation->box[0]['west'] = '4.478';
$xml->localisation->box[0]['east'] = '4.494';
*/
$xml->localisation->observatory = 'OHMCV';
//$xml->localisation->basin = 'Claduègne';

$basin = $default_basin;

if (isset($dats->sites) && !empty($dats->sites)){
	$i = 0;
	foreach($dats->sites as $s){
		if ( (strpos($s->place_name,'Disdro') !== false) ){
			$i++;
			continue;
		}
		if ( (strpos($s->place_name,'Limnimeter') !== false) ){
			$i++;
			continue;
		}
		if (isset($s->parent_place) && !empty($s->parent_place)){
			if ( (strpos($s->parent_place->place_name,'Disdro') !== false) ){
				$i++;
				continue;
			}
			if ( (strpos($s->parent_place->place_name,'Limnimeter') !== false) ){
				$i++;
				continue;
			}
		}
		break;	
	}
		
	$site = $dats->sites[$i];
		
	if (isset($site->place_name) && !empty($site->place_name)){
		
	}else{
		if (isset($site->parent_place) && !empty($site->parent_place)){
			$site = $site->parent_place;
		}
	}
	
	if (in_array($site->place_name,$basins)){
		$basin = $site->place_name;
	}else{
		$xml->localisation->site = $site->place_name;
	}
	
	if (isset($site->parent_place) && !empty($site->parent_place)){
		if (in_array($site->parent_place->place_name,$basins)){
			$basin = $site->parent_place->place_name;
		}else if($site->parent_place->place_name == 'Le Pradel'){
			$basin = 'Ardèche';
		}else if($site->parent_place->place_name == 'Valescure'){
                        $basin = 'Gardons';
                }else if($site->parent_place->place_name == 'Tourgueille'){
                        $basin = 'Gardons';
                }
	}
			
	if (isset($site->boundings) && !empty($site->boundings)){
		$xml->localisation->box[0] = '';
		$xml->localisation->box[0]['south'] = $site->boundings->south_bounding_coord;
		$xml->localisation->box[0]['north'] = $site->boundings->north_bounding_coord;
		$xml->localisation->box[0]['west'] = $site->boundings->west_bounding_coord;
		$xml->localisation->box[0]['east'] = $site->boundings->east_bounding_coord;
	}else{
		//On regarder dans le sensor
		if (isset($dats->dats_sensors) && !empty($dats->dats_sensors)){
			$sensor = $dats->dats_sensors[0]->sensor;
			if (isset($sensor->boundings) && !empty($sensor->boundings)){
				$xml->localisation->box[0] = '';
				$xml->localisation->box[0]['south'] = $sensor->boundings->south_bounding_coord;
				$xml->localisation->box[0]['north'] = $sensor->boundings->north_bounding_coord;
				$xml->localisation->box[0]['west'] = $sensor->boundings->west_bounding_coord;
				$xml->localisation->box[0]['east'] = $sensor->boundings->east_bounding_coord;
			}
		}
	}
}

$xml->localisation->basin = $basin;

if ( isset($dats->dats_date_begin) && !empty($dats->dats_date_begin) ){
	$xml->temporal->beginDate = $dats->dats_date_begin;
}
if (isset($dats->dats_date_end) && !empty($dats->dats_date_end)){
	$xml->temporal->endDate = $dats->dats_date_end;
}else{
	if ( isset($dats->dats_date_begin) && !empty($dats->dats_date_begin) ){
		$xml->temporal->endDate = 'now';
	}
}

  
$xml->constraints->useConditions = $dats->dats_use_constraints;


if (isset($dats->data_formats) && !empty($dats->data_formats)){
	$xml->others->format = '';
	$xml->others->format['name'] = $dats->data_formats[0]->data_format_name;
}
$xml->others->creationDate = $dats->dats_pub_date;
		
//$xml->asXml("/export1/mistrals/tmp/rbv_$uuid.xml");

if (isset($_REQUEST['test'])){
	$c = curl_init('http://portailrbvws.sedoo.fr/rest/RBVTo19139/post/text');
	$opts = 'src='.urlencode($xml->asXml());
	curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($c, CURLOPT_POST, true);
	curl_setopt($c, CURLOPT_POSTFIELDS, $opts);
	$result = curl_exec($c);
	if ($result === false){
		echo 'ERROR: '.curl_error($c);
	}else{
		echo $result;
	}
	curl_close($c);
}else{
	$c = curl_init(RBV_WEB_SERVICE.WS_UPDATE_METHOD);	
	$opts = 'src='.urlencode($xml->asXml()).'&format='.WS_XML_FORMAT.'&login='.WS_LOGIN.'&password='.WS_PASSWD;
	curl_setopt($c, CURLOPT_POST, true);
	curl_setopt($c, CURLOPT_POSTFIELDS, $opts);
	
	if (curl_exec($c) === false){
		echo 'ERROR: '.curl_error($c);
	}else{
		echo "$uuid updated successfully";
	}
	curl_close($c);
}
?>