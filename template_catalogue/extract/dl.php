<?php
require_once ('extract/conf.php');
require_once ('extract/fichierResultat.php');
require_once ("bd/mails_new.php");
session_start();
if (isset($_SESSION['loggedUser'])){
	$user = unserialize($_SESSION['loggedUser']);
	if (isset($_SESSION[$_REQUEST['file']])){
		$result = unserialize($_SESSION[$_REQUEST['file']]);
		$file = $result->filename;
		$project_name = $result->project_name;
		$mails = array();

		foreach ($result->contacts as $c){
			foreach ($c->contacts as $contact){
				if (EXTRACT_INFORM_PI && $contact->isPI()){
					mails::sendMailPi($contact->mail,$c->datsTitle,$user,ROOT_EMAIL,ROOT_EMAIL,$project_name);
				}
			}
		}
		ob_end_clean();
		if (isset($file) && !is_dir($file) && file_exists($file)){
			header('Content-disposition: attachment; filename='.basename($file));
			header('Content-MD5: '.base64_encode(md5_file($file)));
			header("Content-Type: application/octet-stream");
			header("Content-Length: ".filesize($file));
			header("Pragma: no-cache");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0, public");
			header("Expires: 0");
			readfile($file);
			exit;
		}
	}
}
?>
