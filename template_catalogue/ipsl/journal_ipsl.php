<?php

require_once ('bd/journal.php');
require_once ('bd/mails_new.php');
define('MAIL_SUPPORT_IPSL','HyMeX.Data-Support@ipsl.polytechnique.fr');
function sendMailUserSat($mail, $datsIds){

	$jeux = array();

	foreach ($datsIds as $datsId){
		$dts = new dataset;
		$jeux[] = $dts->getById($datsId);
	}

	$ldap = new ldapConnect();
	$ldap->openAdm();
	$user = $ldap->getEntry($ldap->getUserDn($mail));

	mails::sendMailUser2($user,$jeux,true,ROOT_EMAIL,MAIL_SUPPORT_IPSL);

}
$mail = $_REQUEST['mail'];
$datsId = $_REQUEST['datsId'];

if ( isset($mail) && !empty($mail) && isset($datsId) && !empty($datsId) ){
	echo "mail: $mail\n";
	echo "datsId: $datsId\n";
	$datsIds = explode(',',$datsId);

	mails::sendMailUserSat($mail,$datsIds);

	foreach ($datsIds as $datsId)
	    journal::addDownloadEntry($mail,$datsId,array(),true);

}

?>
