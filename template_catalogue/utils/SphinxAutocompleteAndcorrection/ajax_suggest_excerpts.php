<?php

require_once 'bd/bdConnect.php';
require_once 'common.php';
require_once 'functions.php';

$indexes = 'simplecompletefull_#MainProject';
$arr =array();
$q = trim($_GET['term']);
//LIMIT 0,10 OPTION ranker=sph04
$stmt = $ln_sph->prepare("SELECT * FROM $indexes WHERE MATCH(:match)");
$aq = explode(' ',$q);
if(strlen($aq[count($aq)-1])<3){
	$query = $q;
}else{
	$query = '^'.$q.'*';
}

$stmt->bindValue(':match', mb_convert_encoding($query, "UTF-8", mb_detect_encoding($query)),PDO::PARAM_STR);
$stmt->execute();

$docs = array();
$title = "";
$stmsnp = $ln_sph->prepare("CALL SNIPPETS(:doc,':index',:query)");
$stmsnp->bindValue(':query',utf8_encode($query),PDO::PARAM_STR);
$stmsnp->bindValue(':index',$indexes,PDO::PARAM_STR);
$stmsnp->bindParam(':doc',$title,PDO::PARAM_STR);

foreach($stmt->fetchAll() as $r){
	$arr[] = array('id' => mb_convert_encoding($r['id'], "UTF-8", mb_detect_encoding($r['id'])),'label' => mb_convert_encoding($r['title'], "UTF-8", mb_detect_encoding($r['title'])));
}
echo json_encode($arr);
?>
