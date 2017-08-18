<?php

require_once ("utils/elastic/ElasticSearchUtils.php");
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
    /*if (isset($nodeConf['quicklooksLink'])){
      	$liste[] = '<a href="'.$nodeConf['quicklooksLink'].'" ><img width="15" height="16" class="text" src="'.$nodeConf['quicklooksIcon'].'" />&nbsp;'.$nodeConf['quicklooksTitle'].'</a>';
    }
    if (isset($nodeConf['calLink'])){
       	$liste[] = '<a href="'.$nodeConf['calLink'].'" ><img width="15" height="16" class="text" src="'.$nodeConf['calIcon'].'" />&nbsp;'.$nodeConf['calTitle'].'</a>';
    }*/
	return $liste;
}

/*
 * $queryArgs: arguments à ajouter à l'url du jeu
 */
function printDataset($dts, $queryArgs = array(),$withTitle = false){
	global $project_name;
	return ElasticSearchUtils::printDataset($dts->dats_id, $dts->dats_title, $dts->isInsertedDataset(), $project_name, $queryArgs, $withTitle);
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

function getDataNodeConf($dts, $projectName, $queryArgs = array()){
	return ElasticSearchUtils::getDataNodeConf($dts->dats_id, $dts->dats_title, $dts->isInsertedDataset(), $projectName, $queryArgs);
}

function addDataset(&$node, $dts, $projectName){
	$nodeConf = getDataNodeConf($dts, $projectName);
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
