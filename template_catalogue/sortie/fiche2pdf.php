<?php

require_once('sortie/fiche2pdf_functions.php');

$datsId = $_REQUEST['datsId'];

if ( isset($datsId) && !empty($datsId) ){
	fiche2pdf($datsId);
}
	

?>
