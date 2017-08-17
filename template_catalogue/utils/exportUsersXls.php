<?php
/*
 * Export au format excel de la liste des utilisateurs d'un projet.
 *
 */

require_once('Spreadsheet/Excel/Writer.php');
require_once("forms/admin_form.php");
require_once("countries.php");

session_start();
$form = new admin_form;
$form->createForm();

if ($form->isAdmin($project_name)){
	$workbook = new Spreadsheet_Excel_Writer();
	$workbook->setVersion(8);
	$workbook->send(strtolower($project_name).'Users.xls');

	#Format pour pouvoir écrire sur plusieurs lignes dans une cellule
	$fmt = $workbook->addFormat(array('TextWrap' => 1));

	$worksheet =& $workbook->addWorksheet($project_name.' users');
	$worksheet->setInputEncoding('UTF-8');

	#Header
	$fmt_header =& $workbook->addFormat();
	$fmt_header->setBold();
	$worksheet->writeRow(0, 0, array('Mail','Name','Role','Affiliation','Address','Phone','Abstract','WGs','Associated project','Supervisor'),$fmt_header);

	#Largeur des colonnes
	$worksheet->setColumn(0,0,30);
	$worksheet->setColumn(1,1,20);
	$worksheet->setColumn(2,2,10);
	$worksheet->setColumn(3,4,30);
	$worksheet->setColumn(5,5,20);
	$worksheet->setColumn(6,6,90);
	$worksheet->setColumn(7,7,50);
	$worksheet->setColumn(8,9,30);

	$i = 1;
	foreach($form->registeredUsers as $user){
        	$address = array($user->street,$user->zipCode,$user->city,countries::getDisplayName($user->country));
	        if (isset($user->supervisor_name) && !empty($user->supervisor_name)){
        	    $sup = $user->supervisor_name."\n".$user->supervisor_affiliation;
	        }else{
        	        $sup = '';
        	}
		$abstract = str_replace("\r\n","\n",$user->abstract);
		$abstract = preg_replace("/\n+/","\n",$abstract);
		$worksheet->writeRow($i, 0,array($user->mail,$user->cn,$user->editableGroup->id,$user->affiliation,implode("\n",$address)),$fmt);
		$worksheet->writeString($i,5,$user->phoneNumber);
		$worksheet->writeRow($i, 6,array($abstract,implode("\n",$user->wg),$user->associatedProject,$sup),$fmt);
		$i++;
	}
	$workbook->close();
}else{
	$url = $_SERVER['HTTP_REFERER'];
	if ( !isset($url) || empty($url) ){
		if($project_name != MainProject)
			$url = 'http://'.$_SERVER['HTTP_HOST'].'/'.$project_name;
		else
			$url = 'http://'.$_SERVER['HTTP_HOST'].'/';
	}
	header("Location: $url");
	exit;
}

?>
