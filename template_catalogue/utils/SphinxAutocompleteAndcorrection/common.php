<?php
define ( "FREQ_THRESHOLD", 40 );
define ( "SUGGEST_DEBUG", 0 );
define ( "LENGTH_THRESHOLD", 2 );
define ( "LEVENSHTEIN_THRESHOLD", 2 );
define ( "TOP_COUNT", 1 );
define ( "SPHINX_20", false );
define ( "SPHINX_DB", 'sphinx_'.strtolower('#MainProject'));
define ( "SPHINX_DAEMON", '/usr/bin/searchd' );
define ( "SPHINX_INDEXER", '/usr/bin/indexer' );
define ( "SPHINX_CONFIG", '/etc/sphinx/sphinx.conf' );
define ( "SPHINX_LOG", true );


function start_sphinx_daemon(){
	exec(SPHINX_DAEMON.' --config '.SPHINX_CONFIG, $out);
	if (SPHINX_LOG){
		foreach($out as $line) {
			echo $line."<br/>";
		}
	}
	sleep(4);
}

function stop_sphinx_daemon(){
	exec(SPHINX_DAEMON.' --stop --config '.SPHINX_CONFIG, $out);
	if (SPHINX_LOG){
		foreach($out as $line) {
			echo $line."<br/>";
		}
	}
	sleep(5);
}



//database PDO
$ln = new PDO( 'pgsql:host='.DB_HOST.';port=5432;dbname='.SPHINX_DB.';', DB_USER, DB_PASS );
//$ln_s = new PDO( 'mysql:host=127.0.0.1;port=3306;dbname=sphinx;', 'root', '2Ghraba' );
//Sphinx PDO
try{
$ln_sph = new PDO( 'mysql:host=127.0.0.1;port=9306;');
/*
if (SPHINX_LOG){
	echo "Sphinx ok<br/>";
}*/

}catch(Exception $e){
	start_sphinx_daemon();
	/*exec(SPHINX_DAEMON,$out);
	if (SPHINX_LOG){
		foreach($out as $line) {
			echo $line."<br/>";
		}
	}
	sleep(4);*/
	//$ln_sph = new PDO( 'mysql:host=127.0.0.1;port=9306;');
}
?>
