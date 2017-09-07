<?php
require_once ("/sites/kernel/#MainProject/conf.php");
require_once ('bd/database-content.php');

if (! $db = pg_connect ( "host=" . $hote . " user=" . $db_user . " dbname=" . $db_name )) {
	echo "Cannot connect to database.\n";
}

displayPage ();

foreach ( $MainProjects as $pro ) {
	if (isset ( $_POST ['button_' . $pro] )) {
		genPDF ( $pro );
	}
}

if (! @pg_close ( $db ))
	echo "Error during database closure !";

?>
