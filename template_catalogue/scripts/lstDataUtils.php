<?php

require_once('bd/dataset.php');
require_once("bd/url.php");

/*
 * Retourne une liste des liens vers les données à afficher.
 */
function getAvailableDataLinks($dts,$project_name){
	$nodeConf = getDataNodeConf($dts,$project_name);
	$liste = array();
	if (isset($nodeConf['dataLink'])){
		$liste[] = '<a href="'.$nodeConf['dataLink'].'"><img width="15" height="16" class="text" src="'.$nodeConf['dataIcon'].'" />&nbsp;'.$nodeConf['dataTitle'].'</a>';
	}
	if (isset($nodeConf['extDataLink'])){
		$liste[] = '<a href="'.$nodeConf['extDataLink'].'" target="_blank"><img width="15" height="16" class="text" src="'.$nodeConf['extDataIcon'].'" />&nbsp;'.$nodeConf['extDataTitle'].'</a>';
	}
	if (isset($nodeConf['bdLink'])){
                $liste[] = '<a href="'.$nodeConf['bdLink'].'"><img width="15" height="16" class="text" src="'.$nodeConf['bdIcon'].'" />&nbsp;'.$nodeConf['bdTitle'].'</a>';
        }
	if (isset($nodeConf['qlLink'])){
                $liste[] = '<a href="'.$nodeConf['qlLink'].'" target="_blank"><img width="15" height="16" class="text" src="'.$nodeConf['qlIcon'].'" />&nbsp;'.$nodeConf['qlTitle'].'</a>';
        }
	return $liste;
}

/*
 * $queryArgs: arguments à ajouter à l'url du jeu
 */
function printDataset($dts, $queryArgs = array(),$withTitle = false){
	global $project_name;
	$nodeConf = getDataNodeConf($dts,$project_name);

	//$nodeConf['link'] = "?editDatsId=$dts->dats_id&datsId=$dts->dats_id";

	foreach($queryArgs as $arg => $val){
		$nodeConf['link'] .= "&$arg=$val";
	}
	
	if ($withTitle == false) $result = "<a href='".$nodeConf['link']."'>".$nodeConf['text']."</a>";
	else $result = "<a href='".$nodeConf['link']."'>View</a><br>";
	
	if (isset($nodeConf['dataLink']) ){ 
		$result .= '&nbsp;&nbsp;<a href="'.$nodeConf['dataLink'].'"><img width="15" height="16" class="text" src="'.$nodeConf['dataIcon'].'" title="'.$nodeConf['dataTitle'].'" /></a>';
	}
	if (isset($nodeConf['extDataLink'])){
        $result .= '&nbsp;&nbsp;<a href="'.$nodeConf['extDataLink'].'" target="_blank"><img width="15" height="16" class="text" src="'.$nodeConf['extDataIcon'].'" title="'.$nodeConf['extDataTitle'].'" /></a>';
	}
	if (isset($nodeConf['bdLink'])){
        $result .= '&nbsp;&nbsp;<a href="'.$nodeConf['bdLink'].'"><img width="15" height="16" class="text" src="'.$nodeConf['bdIcon'].'" title="'.$nodeConf['bdTitle'].'" /></a>';
	}
	if (isset($nodeConf['qlLink'])){
		$result .= '&nbsp;&nbsp;<a href="'.$nodeConf['qlLink'].'" target="_blank"><img width="15" height="16" class="text" src="'.$nodeConf['qlIcon'].'" title="'.$nodeConf['qlTitle'].'" /></a>';
	}
    return $result;
}

/*
 * Affiche la liste des fiches d'un projet.
 * $proj: objet project ou nom d'un projet
 */
function lstProjectData($proj, $withTitle = true){
	
	if ($proj instanceof project){
		$projName = $proj->project_name;
		$where = "WHERE project_id = $proj->project_id";
	}else{
		$projName = $proj;
		$where = "WHERE project_name = '$proj'";
	}
	
	
	if ($withTitle){
		echo "<h1>$projName datasets</h1>";
        	include 'legende.php';
	}
    $query = "SELECT dats_id,dats_title FROM dataset JOIN dats_proj USING (dats_id) JOIN project USING (project_id) $where AND (is_archived is null OR NOT is_archived) ORDER BY dats_title";
	lstQueryData($query);
}

function lstQueryData($query, $queryArgs = array()){
	$dts = new dataset;
        $dts_list = $dts->getOnlyTitles($query);

	if (empty($dts_list)){
		echo "<font style='font-size:110%;font-weight:bold;' color='red'>No dataset found</font>";
	}else{
        	echo "<ul>";
	        foreach ($dts_list as $dt){
        	        echo '<li>'.printDataset($dt, $queryArgs).'</li>';
	        }
        	echo "</ul>";
	}
}

function getDataNodeConf($dts,$project_name,$search = 0){
	global $root;
	$nodeConf = array('text' => $dts->dats_title,'link' => "http://".$_SERVER['HTTP_HOST']."?editDatsId=$dts->dats_id&datsId=$dts->dats_id&project_name=$project_name", 'datsId' => $dts->dats_id);
	$u = new url();
        $urls = $u->getByDataset($dts->dats_id);
	foreach ($urls as $url){
		if (strpos($url->url,'/') === 0){
			$nodeConf['dataLink'] = "http://".$_SERVER['HTTP_HOST'].$url->url."&search=$search&project_name=$project_name";
			if ( ($url->url_type == 'http' )
				|| ($url->url_type == 'ftp' && strpos($url->url,'climserv.ipsl') > 0) ){
				//Original dataset (Sedoo ou IPSL)
	                	$nodeConf['dataIcon'] =  $root."/scripts/images/dataBlue.gif";
				$nodeConf['dataTitle'] = 'Original dataset as provided by the Principal Investigator';
			}else if ($url->url_type == 'ftp' && strpos($url->url,'climserv.ipsl') === false) {
				//Jeu ftp pas ipsl
				$nodeConf['dataIcon'] =  $root."/scripts/images/dataPurple.gif";
				$db = new database;
			        $database = $db->getByDatsId($dts->dats_id);
				if (isset($database)){
					$nodeConf['dataLink'] .= "&target_database=$database->database_name";
                                	$nodeConf['dataTitle'] = $database->database_name.' FTP access';
                        	}else{
	                                $nodeConf['dataTitle'] = 'Dataset available in another database';
        	                }
			}
		}else if ($url->url_type == 'ql'){
                        $nodeConf['qlLink'] = $url->url;
                        $nodeConf['qlIcon'] =  $root."/scripts/images/dataOrange.gif";
                        $nodeConf['qlTitle'] = 'Campaign website quicklook charts';
		}else if (strpos($url->url,'http') === 0){
			//Autre centre de données
			$nodeConf['extDataLink'] = $url->url;
                        $nodeConf['extDataIcon'] =  $root."/scripts/images/dataPurple.gif";
			$db = new database;
		        $database = $db->getByDatsId($dts->dats_id);
			if (isset($database)){
				$nodeConf['extDataTitle'] = $database->database_name;
			}else{
				$nodeConf['extDataTitle'] = 'Dataset available in another database';
			}
		}
	}

	if( $dts->isInsertedDataset() ) {
		//Données insérées
                $nodeConf['bdLink'] = "http://".$_SERVER['HTTP_HOST']."/Data-Download-BD/?search=$search&datsId=$dts->dats_id&project_name=$project_name";
                $nodeConf['bdIcon'] =  $root."/scripts/images/dataGreen.gif";
		$nodeConf['bdTitle'] = 'Homogenized dataset';
        }
	//TODO autres urls (opendap, thredds, ...)

	return $nodeConf;
}

function addDataset(&$node,$dts,$project_name,$search = 0){
	$nodeConf = getDataNodeConf($dts,$project_name,$search);
	$subnode = new HTML_TreeNode($nodeConf);
	$node->addItem($subnode);
}

function get_av_datasets(&$node, &$datasets){
        if (isset($node->items) && count($node->items) > 0) {
                for ($i=0; $i<count($node->items); $i++) {
                        get_av_datasets($node->items[$i],$datasets);
                }
        }else{
                if (!empty($node->bdLink))
                        $datasets[] = $node->datsId;

        }
}

?>
