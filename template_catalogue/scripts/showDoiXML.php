<?php
require_once ("forms/doi_form.php");

if(isset($_REQUEST['dats_id']) && !empty($_REQUEST['dats_id']) && isset($_REQUEST['project']) && !empty($_REQUEST['project']) ){
	$doi_form= new doi_form;
	$doi = $doi_form->displaydoixml($_REQUEST['dats_id'],$xmlstr, $_REQUEST['project']);
	$doi_json = json_encode($doi);
	echo $doi_json;

}else{
	echo "<h1 style='color:red;'> Error: Please set the project's name and id in the url.</h1>";
}
?>