<?php

require_once('sortie/fiche2pdf_functions_new.php');

$datsId = $_REQUEST['datsId'];

if ( isset($datsId) && !empty($datsId) ){
	echo "test";
	fiche2pdf_new($datsId);
}
	

?>
