<?php
require_once("bd/bdConnect.php");
require_once("scripts/filtreProjets.php");
require_once("scripts/lstDataUtils.php");
require_once 'utils/SphinxAutocompleteAndcorrection/common.php';
require_once 'utils/SphinxAutocompleteAndcorrection/functions.php';

function keyword_exists_suggest($keyword){
	global $ln;
	$ln->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$row = $ln->prepare ("SELECT id FROM suggest WHERE keyword ILIKE '%".mb_convert_encoding(str_ireplace("'","''",$keyword), "UTF-8", mb_detect_encoding($keyword))."%'");
	$row->execute();
	$res = $row->fetchAll();
	if (count($res) > 1 || count($res) == 1)
		return true;
	else
		return false;
}

function keyword_exists_docs($keyword){
	global $ln;
	$ln->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	//$row = $ln->prepare ("SELECT id FROM docs WHERE title ILIKE '%".mb_convert_encoding(str_ireplace("'","''",$keyword), "utf8", "ascii")."%'");
	$row = $ln->prepare ("SELECT id FROM docs WHERE title ILIKE '%".mb_convert_encoding(str_ireplace("'","''",$keyword), "UTF-8", mb_detect_encoding($keyword))."%'");
	$row->execute();
	$res = $row->fetchAll();
	if (count($res) > 1 || count($res) == 1)
		return true;
	else
		return false;
}

function insert_keyword($kwd){
	global $ln,$ln_sph;
	if(keyword_exists_suggest($kwd) == false){
		$trig = BuildTrigrams($kwd);
		//$trig = BuildTrigrams($lc);
		$ln->query ("insert into suggest (keyword,trigrams,freq) values ('".mb_convert_encoding(str_ireplace("'","''",$kwd), "UTF-8", mb_detect_encoding($kwd))."','".$trig."',4000)");
		//$ln->query ("insert into docs (title) values ('température')");
	}
}

function update_sphinx_indexes(){
	global $ln_sph;
	if (SPHINX_LOG){
		echo "Update Sphinx index<br/>";
	}
	$ln_sph = null;
	stop_sphinx_daemon();
	/*exec(SPHINX_DAEMON.' --stop',$out);
	if (SPHINX_LOG){
		foreach($out as $line) {
			echo $line."<br/>";
		}
	}
	sleep(5);*/
	exec(SPHINX_INDEXER.' --config '.SPHINX_CONFIG.' --all',$out);
	if (SPHINX_LOG){
		foreach($out as $line) {
			echo $line."<br/>";
		}
	}
	
	start_sphinx_daemon();
	/*exec(SPHINX_DAEMON,$out);
	if (SPHINX_LOG){
		foreach($out as $line) {
			echo $line."<br/>";
		}
	}*/
	$ln_sph = new PDO( 'mysql:host=127.0.0.1;port=9306;');
}

function insert_keywords_docs_suggest($xml = null) {
	global $ln, $ln_sph;
	$bd = new bdConnect ();
	$liste = array ();
	$ln->setAttribute ( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
	if (isset ( $xml ) && ! empty ( $xml )) {
		$tab = explode ( " ", $xml);	
		foreach ( $tab as $kwd ) {
			// $kwd = preg_replace("'", "\'", $kwd);
			$kwd = preg_replace ( "#[^a-zA-Z0-9éôèà@ç/\-:.'_]#", " ", $kwd );
			$kwd = mb_strtolower ( $kwd, 'UTF-8' );
			$kwd = trim ( $kwd );
			if (substr ( $kwd, - 1, 1 ) == '.' || substr ( $kwd, - 1, 1 ) == '-' || substr ( $kwd, - 1, 1 ) == '_' || substr ( $kwd, - 1, 1 ) == ':') {
				$kwd = substr ( $kwd, 0, - 1 );
			}
			$kwd = trim ( $kwd );
			//echo "keyword= ".$kwd."<br>";
		
			if (! is_numeric ( $kwd ) && $kwd != '' && $kwd != ' ' && strlen ( $kwd ) > 2 && strlen ( $kwd ) < 40) {
				// echo '-'.$kwd.'<br>';
				// insert docs
				if (keyword_exists_docs ( $kwd ) == false) {
					//echo "- " . mb_convert_encoding ( str_ireplace ( "'", "''", $kwd ), "UTF-8", mb_detect_encoding ( $kwd ) ) . "<br>";
					$ln->query ( "insert into docs (title) values ('" . mb_convert_encoding ( str_ireplace ( "'", "''", $kwd ), "UTF-8", mb_detect_encoding ( $kwd ) ) . "')" );
				}
				// insert suggest
				if (keyword_exists_suggest ( $kwd ) == false) {
					//echo "- " . mb_convert_encoding ( str_ireplace ( "'", "''", $kwd ), "UTF-8", mb_detect_encoding ( $kwd ) ) . "<br>";
					$trig = BuildTrigrams ( $kwd );
					$ln->query ( "insert into suggest (keyword,trigrams,freq) values ('" . mb_convert_encoding ( str_ireplace ( "'", "''", $kwd ), "UTF-8", mb_detect_encoding ( $kwd ) ) . "','" . mb_convert_encoding ( str_ireplace ( "'", "''", $trig ), "UTF-8", mb_detect_encoding ( $trig ) ) . "',4000)" );
				}
			}
		}
	} else {
		$query = "SELECT DISTINCT dats_xml FROM dataset";
		$resultat = $bd->get_data ( $query );
		$tab = array ();
		foreach ( $resultat as $el ) {
			$tab [] = explode ( " ", $el [0] );
		}
	
		foreach ( $tab as $el ) {
			foreach ( $el as $kwd ) {
				// $kwd = preg_replace("'", "\'", $kwd);
				$kwd = preg_replace ( "#[^a-zA-Z0-9éôèà@ç/\-:.'_]#", " ", $kwd );
				$kwd = mb_strtolower ( $kwd, 'UTF-8' );
				$kwd = trim ( $kwd );
				if (substr ( $kwd, - 1, 1 ) == '.' || substr ( $kwd, - 1, 1 ) == '-' || substr ( $kwd, - 1, 1 ) == '_' || substr ( $kwd, - 1, 1 ) == ':') {
					$kwd = substr ( $kwd, 0, - 1 );
				}
				$kwd = trim ( $kwd );
				// echo "= ".$kwd."<br>";
				
				if (! is_numeric ( $kwd ) && $kwd != '' && $kwd != ' ' && strlen ( $kwd ) > 2 && strlen ( $kwd ) < 40) {
					// echo '-'.$kwd.'<br>';
					// insert docs
					if (keyword_exists_docs ( $kwd ) == false) {
						//echo "- " . mb_convert_encoding ( str_ireplace ( "'", "''", $kwd ), "UTF-8", mb_detect_encoding ( $kwd ) ) . "<br>";
						$ln->query ( "insert into docs (title) values ('" . mb_convert_encoding ( str_ireplace ( "'", "''", $kwd ), "UTF-8", mb_detect_encoding ( $kwd ) ) . "')" );
					}
					// insert suggest
					if (keyword_exists_suggest ( $kwd ) == false) {
						//echo "- " . mb_convert_encoding ( str_ireplace ( "'", "''", $kwd ), "UTF-8", mb_detect_encoding ( $kwd ) ) . "<br>";
						$trig = BuildTrigrams ( $kwd );
						$ln->query ( "insert into suggest (keyword,trigrams,freq) values ('" . mb_convert_encoding ( str_ireplace ( "'", "''", $kwd ), "UTF-8", mb_detect_encoding ( $kwd ) ) . "','" . mb_convert_encoding ( str_ireplace ( "'", "''", $trig ), "UTF-8", mb_detect_encoding ( $trig ) ) . "',4000)" );
					}
				}
			}
		}
	}
}











?>