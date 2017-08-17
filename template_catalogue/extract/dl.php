<?php
require_once('extract/conf.php');
require_once('extract/fichierResultat.php');
require_once("bd/mails_new.php");
session_start();
if (isset($_SESSION['loggedUser'])){
	$user = unserialize($_SESSION['loggedUser']);
	if (isset($_SESSION[$_REQUEST['file']])){
		$result = unserialize($_SESSION[$_REQUEST['file']]);
		//print_r($result);
		$file = $result->filename;
		$project_name = $result->project_name;
		$mails = array();
		//echo "<br>".$file;
		//echo "<br>".$project_name."<br>";
		foreach ($result->contacts as $c){
			//echo $c->datsTitle."<br>";
			foreach ($c->contacts as $contact){
				if (EXTRACT_INFORM_PI && $contact->isPI()){
					mails::sendMailPi($contact->mail,$c->datsTitle,$user,ROOT_EMAIL,ROOT_EMAIL,$project_name);
					//echo "<br>- ".$contact->mail;
				}
			}
		}
ob_end_clean();
if (isset($file) && !is_dir($file) && file_exists($file)){
header('Content-disposition: attachment; filename='.basename($file));
header('Content-MD5: '.base64_encode(md5_file($file)));
//header("Content-Type: application/force-download");
//header("Content-Transfer-Encoding: application/octet-stream\n");
header("Content-Type: application/octet-stream");
header("Content-Length: ".filesize($file));
header("Pragma: no-cache");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0, public");
header("Expires: 0");
//error_log('ob lev:'.ob_get_level());
readfile($file);
exit;
}
}
}
?>
