<?php
require_once('forms/download_form.php');
$file = DATA_PATH_DL.'/'.$_REQUEST['file'].'.zip';
//echo $file;
if (isset($file) && !is_dir($file) && file_exists($file)){
	header('Content-disposition: attachment; filename='.basename($file));
	header('Content-MD5: '.base64_encode(md5_file($file)));
	//header("Content-Type: application/force-download");
	//header("Content-Transfer-Encoding: application/octet-stream\n");
	header("Content-Type: application/octetstream"); 
	header("Content-Length: ".filesize($file));
	header("Pragma: no-cache");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0, public");
	header("Expires: 0");
	readfile($file);
	exit;
}
?>
