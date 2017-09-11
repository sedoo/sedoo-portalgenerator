<?php
require_once ('bd/journal.php');
require_once ('ldap/ldapConnect.php');

$j = new journal;
$liste = $j->getByQuery("select distinct on (contact,dats_id) * from journal where date > '2013-06-18' and type_journal_id = 3 and contact not in (".EXCLUDE_USERS.",'veronique.ducrocq@meteo.fr') order by dats_id");
$cpt = 0;
$ldap = new ldapConnect();
$ldap->openAdm();
foreach($liste as $l){
	foreach ($l->dataset->dats_originators as $pi){
		if ($pi->contact_type->contact_type_name == 'PI or Lead scientist'){
	                $user = $ldap->getEntry("mail=$l->contact,".PEOPLE_BASE);
			echo $pi->personne->pers_email_1.' '.$l->dataset->dats_title." ($user->cn, $user->mail) $l->contact\n";
			$cpt++;
		}
	}
}
$ldap->close();
echo "$cpt\n";




?>
