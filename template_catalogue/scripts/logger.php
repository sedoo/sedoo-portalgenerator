<?php
require_once("conf/conf.php");
$LOG_DEBUG = 1;
$LOG_INFO = 2;
$LOG_ERROR = 3;
$prefix = array($LOG_DEBUG => '[DEBUG] ', $LOG_INFO => '[INFO] ', $LOG_ERROR => '[ERROR] ');
$a = 1;
function ecrire_log($message, $level){
	global $prefix, $a;
	error_log($prefix[$level].$message."\n",3,LOG_FILE);
}

function log_error($message){
	global $LOG_ERROR;
	ecrire_log($message,$LOG_ERROR);
}

function log_info($message){
	global $LOG_INFO;
	ecrire_log($message,$LOG_INFO);
}

function log_debug($message){
	global $LOG_DEBUG;
	ecrire_log($message,$LOG_DEBUG);
}

?>
