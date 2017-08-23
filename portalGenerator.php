#!/usr/bin/php
<?php
include('config.php');
include("ldapIds.php");

define ( 'REP_LDAP', '/export1/eurequa/ldap' );
define ( 'Duplicated_db_host', $databaseConf['host']);
define ( 'Duplicated_db_user', $databaseConf['db_user']);
define ( 'Duplicated_db_name', $databaseConf['db_name']);
define ( 'Duplicated_db_password', $databaseConf['db_password']);

// Portal generation functions
function comment($com) {
	return "//" . $com . "\n";
}
function ScanDirectory($Directory) {
	global $i, $Files_list;
	$MyDirectory = opendir ( $Directory ) or die ( 'Erreur' );
	
	while ( $Entry = @readdir ( $MyDirectory ) ) {
		if (is_dir ( $Directory . '/' . $Entry ) && $Entry != '.' && $Entry != '..' && $Entry != null) {
			ScanDirectory ( $Directory . '/' . $Entry );
		} else if (! is_dir ( $Directory . '/' . $Entry ) && $Entry != '.' && $Entry != '..' && $Entry != null) {
			$Files_list [$i] = $Directory . '/' . $Entry;
			$i ++;
		}
	}
	closedir ( $MyDirectory );
}
function moveDirectory($Directory, $DestDirectory) {
	exec ( "mv $Directory $DestDirectory" );
}
function duplicateDirectory($Directory, $DestDirectory) {
	exec ( "cp -R $Directory $DestDirectory" );
}
function eraseDirectory($Directory) {
	exec ( "rm -R $Directory" );
}
function changeWordInDirectory($Directory, $wordToModify, $wordToReplace) {
	global $i, $Files_list;
	if (is_dir ( $Directory )) {
		ScanDirectory ( $Directory );
		foreach ( $Files_list as $filename ) {
			if (file_exists ( $filename )) {
				$file_content = file_get_contents ( $filename, true );
				$file_contents = str_replace ( $wordToModify, $wordToReplace, $file_content );
				$file = fopen ( $filename, "w+" ) or die ( "Unable to open file!" );
				fwrite ( $file, $file_contents );
				fclose ( $file );
			}
		}
		$i = 0;
	} else {
		if (file_exists ( $Directory )) {
			$file_content = file_get_contents ( $Directory, true );
			$file_contents = str_replace ( $wordToModify, $wordToReplace, $file_content );
			$file = fopen ( $Directory, "w+" ) or die ( "Unable to open file!" );
			fwrite ( $file, $file_contents );
			fclose ( $file );
		}
	}
	$Files_list = array ();
}
function createProjectsDirectories($projects_names) {
	global $subProjects;
	if (isset ( $projects_names ) && ! empty ( $projects_names )) {
		foreach ( $projects_names as $project ) {
			if (isset ( $project ) && ! empty ( $project )) {
				if (file_exists ( path . '/' . $project )) {
					eraseDirectory ( path . '/' . $project );
				}
				duplicateDirectory ( path . '/project-directory-template', path . '/' . $project );
				changeWordInDirectory ( path . '/' . $project, '#project', $project );
				if (isset ( $subProjects [$project] ) && ! empty ( $subProjects [$project] )) {
					foreach ( $subProjects [$project] as $subProj ) {
						$SP = str_replace ( ' ', '-', $subProj );
						duplicateDirectory ( path . '/project-directory-template/subproject-directory-template', path . '/' . $project . '/' . $SP );
						changeWordInDirectory ( path . '/' . $project . '/' . $SP, '#subproject', $subProj );
						changeWordInDirectory ( path . '/' . $project . '/' . $SP, '#project', $project );
					}
				}
			}
		}
	}
}
function generatePHPFile($filepath, $confFile = 'default') {
	global $subProjects, $ldapProjects, $argv, $result_array;
	$file = fopen ( $filepath, "w" ) or die ( "Unable to open file!" );
	if (isset ( $argv [1] ) && ! empty ( $argv [1] ))
		$xmlFile_path = $argv [1];
	else
		$xmlFile_path = './input/projet-template.xml';
	$xml = simplexml_load_file ( $xmlFile_path );
	$json_string = json_encode ( $xml );
	$content = "<?php \n\n";
	$result_array = json_decode ( $json_string, TRUE );
	// Portal informations
	$content .= comment ( "Variables du portail " . $result_array ['name'] );
	$content .= comment ( "Nom du Portail ou projet principal" );
	if (isset ( $result_array ['name'] ) && ! empty ( $result_array ['name'] ))
		$content .= "define('MainProject','" . $result_array ['name'] . "');\n";
	else
		$content .= "define('MainProject','');\n";
	$content .= comment ( "site web du portail" );
	if (isset ( $result_array ['website'] ) && ! empty ( $result_array ['website'] ))
		$content .= "define('PORTAL_WebSite','" . $result_array ['website'] . "');\n";
	else
		$content .= "define('PORTAL_WebSite','');\n";
	$content .= comment ( "Le compte google analytic du portail" );
	if (isset ( $result_array ['googleAnalyticAccount'] ) && ! empty ( $result_array ['googleAnalyticAccount'] ))
		$content .= "define('PortalGoogleAnalytic','" . $result_array ['googleAnalyticAccount'] . "');\n";
	else
		$content .= "define('PortalGoogleAnalytic','');\n";
	$content .= comment ( "Répertoire pour les dépots ftp" );
	if (isset ( $result_array ['depot'] ) && ! empty ( $result_array ['depot'] ))
		$content .= "define('PORTAL_DEPOT','" . $result_array ['depot'] . "');\n";
	else
		$content .= "define('PORTAL_DEPOT','');\n";
	$content .= comment ( "Favicon du portail" );
	if (isset ( $result_array ['faviconPath'] ) && ! empty ( $result_array ['faviconPath'] ))
		$content .= "define('FavIcon','" . $result_array ['faviconPath'] . "');\n";
	else
		$content .= "define('FavIcon','');\n";
	$content .= comment ( "Hauteur de la bannière" );
	if (isset ( $result_array ['bannerHeight'] ) && ! empty ( $result_array ['bannerHeight'] ))
		$content .= "define('PORTAL_BannerHeight','" . $result_array ['bannerHeight'] . "');\n";
	else
		$content .= "define('PORTAL_BannerHeight','');\n";
	$content .= comment ( "Affichage ou pas du nom du portail" );
	if (isset ( $result_array ['displayBannerTitle'] ) && ! empty ( $result_array ['displayBannerTitle'] ))
		$content .= "define('PORTAL_DisplayBannerTitle','" . $result_array ['displayBannerTitle'] . "');\n";
	else
		$content .= "define('PORTAL_DisplayBannerTitle','');\n";
	$content .= comment ( "Affichage ou pas du logo du portail dans la bannière" );
	if (isset ( $result_array ['displayLogoOnBanner'] ) && ! empty ( $result_array ['displayLogoOnBanner'] ))
		$content .= "define('PORTAL_DisplayLogoOnBanner','" . $result_array ['displayLogoOnBanner'] . "');\n";
	else
		$content .= "define('PORTAL_DisplayLogoOnBanner','');\n";
	$content .= comment ( "Logo du portail" );
	if (isset ( $result_array ['logoPath'] ) && ! empty ( $result_array ['logoPath'] ))
		$content .= "define('PORTAL_LogoPath','" . $result_array ['logoPath'] . "');\n";
	else
		$content .= "define('PORTAL_LogoPath','');\n";
	$content .= comment ( "La barre du haut du portail" );
	if (isset ( $result_array ['topbarPath'] ) && ! empty ( $result_array ['topbarPath'] ))
		$content .= "define('PORTAL_TopBarPath','" . $result_array ['topbarPath'] . "');\n";
	else
		$content .= "define('PORTAL_TopBarPath','');\n";
	$content .= comment ( "Les paramètres du pied du portail" );
	if (isset ( $result_array ['footerTextPeriod'] ) && ! empty ( $result_array ['footerTextPeriod'] ))
		$content .= "define('PORTAL_FooterTextPeriod','" . $result_array ['footerTextPeriod'] . "');\n";
	else
		$content .= "define('PORTAL_FooterTextPeriod','');\n";
	if (isset ( $result_array ['footerTextDeveloper'] ) && ! empty ( $result_array ['footerTextDeveloper'] ))
		$content .= "define('PORTAL_FooterTextDeveloper','" . $result_array ['footerTextDeveloper'] . "');\n";
	else
		$content .= "define('PORTAL_FooterTextDeveloper','');\n";
	if (isset ( $result_array ['footerTextDeveloperWebsite'] ) && ! empty ( $result_array ['footerTextDeveloperWebsite'] ))
		$content .= "define('PORTAL_FooterTextDeveloperWebsite','" . $result_array ['footerTextDeveloperWebsite'] . "');\n";
	else
		$content .= "define('PORTAL_FooterTextDeveloperWebsite','');\n";
	$content .= "define('ATT_IMG_URL_PATH','/att_img/');\n";
	$content .= comment ( "Répertoire où est placée la doc associée aux données d'un jeu." );
	$content .= comment ( "Il est ajouté au résultat de toutes les requetes concernant le jeu." );
	$content .= "define('DOC_DIR','0_Documentation');\n";
	$content .= "define('README_FILE','README');\n";
	
	$content .= comment ( "Répertoire des données" );
	if (isset ( $result_array ['dataPath'] ) && ! empty ( $result_array ['dataPath'] )) {
		if (! file_exists ( $result_array ['dataPath'] )) {
			exec ( "mkdir -p " . $result_array ['dataPath'] );
		}
		$content .= "define('DATA_PATH', '" . $result_array ['dataPath'] . "' ));\n";
	}else {
		$content .= "define('DATA_PATH','');\n";
	}
	
	if (isset ( $result_array ['portalWorkPath'] ) && ! empty ( $result_array ['portalWorkPath'] )) {
		$content .= "define('portalWorkPath','" . $result_array ['portalWorkPath'] . "');\n";
		$content .= comment ( "Répertoire où sont placés les fichiers à télécharger" );
		$content .= "define('DATA_PATH_DL','" . $result_array ['portalWorkPath'] . "/dl');\n";
		$content .= comment ( "Fichier log téléchargement" );
		$content .= "define('LOG_DL','" . $result_array ['portalWorkPath'] . "/log/dl.log');\n";
		$content .= comment ( "Fichier de log utilisé par logger.php (log des requetes sql)" );
		$content .= "define('LOG_FILE','" . $result_array ['portalWorkPath'] . "/log/catalogue.log');\n";
		$content .= comment ( "Répertoire où sont les fichiers permettant de générer les cartes (liste des points à afficher)" );
		$content .= "define('MAP_PATH','" . $result_array ['portalWorkPath'] . "/maps');\n";
		$content .= comment ( "Répertoires des images et fichiers attachés" );
		$content .= "define('ATT_FILES_PATH','" . $result_array ['portalWorkPath'] . "/attached');\n";
	} else {
		$content .= comment ( "Répertoire où sont placés les fichiers à télécharger" );
		$content .= "define('DATA_PATH_DL','');\n";
		$content .= comment ( "Fichier log téléchargement" );
		$content .= "define('LOG_DL','');\n";
		$content .= comment ( "Fichier de log utilisé par logger.php (log des requetes sql)" );
		$content .= "define('LOG_FILE','');\n";
		$content .= comment ( "Répertoire où sont les fichiers permettant de générer les cartes (liste des points à afficher)" );
		$content .= "define('MAP_PATH','');\n";
		$content .= comment ( "Répertoires des images et fichiers attachés" );
		$content .= "define('ATT_FILES_PATH','');\n";
	}
	if (! file_exists ( $result_array ['portalWorkPath'] )) {
		exec ( "mkdir -p " . $result_array ['portalWorkPath'] );
	}
	
	if (! file_exists ( $result_array ['portalWorkPath'] . "/dl" )) {
		exec ( "mkdir -p " . $result_array ['portalWorkPath'] . "/dl" );
	}
	if (! file_exists ( $result_array ['portalWorkPath'] . "/log" )) {
		exec ( "mkdir -p " . $result_array ['portalWorkPath'] . "/log" );
	}
	if (! file_exists ( $result_array ['portalWorkPath'] . "/maps" )) {
		exec ( "mkdir -p " . $result_array ['portalWorkPath'] . "/maps" );
	}
	
	$content .= "define('STATS_DEFAULT_MIN_YEAR', 2015);\n";
	
	$content .= comment ( "répertoire du site web" );
	if (isset ( $result_array ['webPath'] ) && ! empty ( $result_array ['webPath'] ))
		$content .= "define('WEB_PATH','" . $result_array ['webPath'] . "');\n";
	else
		$content .= "define('WEB_PATH','');\n";
	$content .= comment ( "//téléchargements des jeux insérés" );
	if ($confFile == 'default') {
		if (isset ( $result_array ['dns'] ) && ! empty ( $result_array ['dns'] )) {
			$content .= "define('EXTRACT_CGI', '/extract/cgi-bin/extract.cgi');\n";
			$content .= "define('EXTRACT_CGI_FICHIERS', '/extract/cgi-bin/extractFiles.cgi');\n";
		} else {
			$content .= "define('EXTRACT_CGI','');\n";
			$content .= "define('EXTRACT_CGI_FICHIERS','');\n";
		}
		if (isset ( $result_array ['portalWorkPath'] ) && ! empty ( $result_array ['portalWorkPath'] ))
			$content .= "define('EXTRACT_RESULT_PATH','" . $result_array ['portalWorkPath'] . "/dl');\n";
		else
			$content .= "define('EXTRACT_RESULT_PATH','');\n";
		if (isset ( $result_array ['extractInformPi'] ) && ! empty ( $result_array ['extractInformPi'] ))
			$content .= "define('EXTRACT_INFORM_PI','" . $result_array ['extractInformPi'] . "');\n";
		else
			$content .= "define('EXTRACT_INFORM_PI','');\n";
	} else {
		$content .= "define('EXTRACT_CGI','/extract/cgi-bin/extract.cgi');\n";
		$content .= "define('EXTRACT_CGI_FICHIERS','/extract/cgi-bin/extractFiles.cgi');\n";
		$content .= "define('EXTRACT_RESULT_PATH','@extract.result.path@');\n";
		$content .= "define('EXTRACT_INFORM_PI','@extract.mail.pis@');\n";
	}
	$content .= comment ( "Role public pour les données" );
	if (isset ( $result_array ['publicDataRole'] ) && ! empty ( $result_array ['publicDataRole'] ))
		$content .= "define('PUBLIC_DATA_ROLE','" . $result_array ['publicDataRole'] . "');\n";
	else
		$content .= "define('PUBLIC_DATA_ROLE','');\n";
	$content .= comment ( "Répertoire de l'outil permettant la conversion html/pdf" );
	if ($confFile == 'default') {
		if (isset ( $result_array ['wkhtmlBinPath'] ) && ! empty ( $result_array ['wkhtmlBinPath'] ))
			$content .= "define('WKHTML_BIN_PATH','" . $result_array ['wkhtmlBinPath'] . "');\n";
		else
			$content .= "define('WKHTML_BIN_PATH','');\n";
	} else {
		$content .= "define('WKHTML_BIN_PATH','@wkhtml.binPath@');\n";
	}
	$content .= comment ( "Prefixe pour les tests : 10.5072/" );
	if ($confFile == 'default') {
		if (isset ( $result_array ['doiPrefix'] ) && ! empty ( $result_array ['doiPrefix'] ))
			$content .= "define('DOI_PREFIX','" . $result_array ['doiPrefix'] . "');\n";
		else
			$content .= "define('DOI_PREFIX','');\n";
		$content .= comment ( "Si le portail en mode test" );
	} else {
		$content .= "define('DOI_PREFIX','@doi.prefix@');\n";
	}
	if ($confFile == 'default') {
		if (isset ( $result_array ['testMode'] ) && ! empty ( $result_array ['testMode'] ))
			$content .= "define('TEST_MODE','" . $result_array ['testMode'] . "');\n";
		else
			$content .= "define('TEST_MODE','');\n";
	} else {
		$content .= "define('TEST_MODE','@testMode@');\n";
	}
	$content .= comment ( "Site FTP du portail (ex: ftp://sedoo.fr)" );
	if (isset ( $result_array ['ftp'] ) && ! empty ( $result_array ['ftp'] ))
		$content .= "define('Portal_FTP_Site','" . $result_array ['ftp'] . "');\n";
	else
		$content .= "define('Portal_FTP_Site','');\n";
	$content .= comment ( "L'adresse mail du groupe admin (ex mistralsAdmins@sedoo.fr)" );
	if (isset ( $result_array ['adminGroupEmail'] ) && ! empty ( $result_array ['adminGroupEmail'] ))
		$content .= "define('Portal_AdminGroup_Email','" . $result_array ['adminGroupEmail'] . "');\n";
	else
		$content .= "define('Portal_AdminGroup_Email','');\n";
	$content .= comment ( "Une autre adresse du groupe admin mail mistralsdb-admin@sedoo.fr " );
	if (isset ( $result_array ['rootEmail'] ) && ! empty ( $result_array ['rootEmail'] ))
		$content .= "define('ROOT_EMAIL','" . $result_array ['rootEmail'] . "');\n";
	else
		$content .= "define('ROOT_EMAIL','');\n";
	$content .= comment ( "Email du responsable du portail" );
	if (isset ( $result_array ['managerEmail'] ) && ! empty ( $result_array ['managerEmail'] ))
		$content .= "define('Portal_Manager_Email','" . $result_array ['managerEmail'] . "');\n";
	else
		$content .= "define('Portal_Manager_Email','');\n";
	$content .= comment ( "Datapolicy du portail" );
	if (isset ( $result_array ['datapolicy'] ) && ! empty ( $result_array ['datapolicy'] ))
		$content .= "define('Portal_DataPolicy','" . $result_array ['datapolicy'] . "');\n";
	else
		$content .= "define('Portal_DataPolicy','');\n";
	$content .= comment ( "Paramètres pour la connexion avec la BDD" );
	// Roles
	$roles = null;
	$m = 0;
	foreach ( $result_array ['roles'] ['role'] as $role ) {
		$m ++;
		$roles .= $role;
		if ($m < count ( $result_array ['roles'] ['role'] )) {
			$roles .= ",";
		}
	}
	$content .= comment ( "Liste des roles pour le portail" );
	if (isset ( $roles ) && ! empty ( $roles ))
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "ListRoles','" . $roles . "');\n";
	else
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "ListRoles','');\n";
	
	$content .= comment ( "Année et mois du début du portail" );
	if (isset ( $result_array ['yearStart'] ) && ! empty ( $result_array ['yearStart'] ))
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "yDeb','" . $result_array ['yearStart'] . "');\n";
	else
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "yDeb','');\n";
	if (isset ( $result_array ['monthStart'] ) && ! empty ( $result_array ['monthStart'] ))
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "mDeb','" . $result_array ['monthStart'] . "');\n";
	else
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "mDeb','');\n";
		
		// database
	if ($confFile == 'default') {
		if (isset ( $result_array ['database'] ['host'] ) && ! empty ( $result_array ['database'] ['host'] ))
			$content .= "define('DB_HOST','" . $result_array ['database'] ['host'] . "');\n";
		else
			$content .= "define('DB_HOST','');\n";
		if (isset ( $result_array ['database'] ['user'] ) && ! empty ( $result_array ['database'] ['user'] ))
			$content .= "define('DB_USER','" . $result_array ['database'] ['user'] . "');\n";
		else
			$content .= "define('DB_USER','');\n";
		if (isset ( $result_array ['database'] ['name'] ) && ! empty ( $result_array ['database'] ['name'] ))
			$content .= "define('DB_NAME','" . $result_array ['database'] ['name'] . "');\n";
		else
			$content .= "define('DB_NAME','');\n";
		
		if (isset ( $result_array ['database'] ['password'] ) && ! empty ( $result_array ['database'] ['password'] ))
			$content .= "define('DB_PASS','" . $result_array ['database'] ['password'] . "');\n";
		else
			$content .= "define('DB_PASS','');\n";
	} else {
		$content .= "define('DB_HOST','@db.host@');\n";
		$content .= "define('DB_USER','@db.user@');\n";
		$content .= "define('DB_NAME','@db.name@');\n";
		$content .= "define('DB_PASS','@db.passwd@');\n";
	}
	$content .= comment ( "//Paramètres pour la connexion avec LDAP" );
	// ldap
	if ($confFile == 'default') {
		if (isset ( $result_array ['ldap'] ['host'] ) && ! empty ( $result_array ['ldap'] ['host'] ))
			$content .= "define('LDAP_HOST','" . $result_array ['ldap'] ['host'] . "');\n";
		else
			$content .= "define('LDAP_HOST','');\n";
		if (isset ( $result_array ['ldap'] ['port'] ) && ! empty ( $result_array ['ldap'] ['port'] ))
			$content .= "define('LDAP_PORT','" . $result_array ['ldap'] ['port'] . "');\n";
		else
			$content .= "define('LDAP_PORT','');\n";
		if (isset ( $result_array ['ldap'] ['base'] ) && ! empty ( $result_array ['ldap'] ['base'] ))
			$content .= "define('LDAP_BASE','" . $result_array ['ldap'] ['base'] . "');\n";
		else
			$content .= "define('LDAP_BASE','');\n";
		if (isset ( $result_array ['ldap'] ['dn'] ) && ! empty ( $result_array ['ldap'] ['dn'] ))
			$content .= "define('LDAP_DN','" . $result_array ['ldap'] ['dn'] . "');\n";
		else
			$content .= "define('LDAP_DN','');\n";
		if (isset ( $result_array ['ldap'] ['password'] ) && ! empty ( $result_array ['ldap'] ['password'] ))
			$content .= "define('LDAP_PASSWD','" . $result_array ['ldap'] ['password'] . "');\n";
		else
			$content .= "define('LDAP_PASSWD','');\n";
	} else {
		define ( "LDAP_HOST", "@ldap.host@" );
		define ( "LDAP_PORT", 389 );
		define ( "LDAP_BASE", "@ldap.base@" );
		define ( "LDAP_DN", "cn=@ldap.user@," . LDAP_BASE );
		define ( "LDAP_PASSWD", "@ldap.passwd@" );
		$content .= "define('LDAP_HOST','@ldap.host@');\n";
		$content .= "define('LDAP_PORT',389);\n";
		$content .= "define('LDAP_BASE','@ldap.base@');\n";
		$content .= "define('LDAP_DN','cn=@ldap.user@," . LDAP_BASE . "');\n";
		$content .= "define('LDAP_PASSWD','@ldap.passwd@');\n";
	}
	// elastic
	if ($confFile == 'default') {
		if (isset ( $result_array ['elastic'] ['host'] ) && ! empty ( $result_array ['elastic'] ['host'] ))
			$content .= "define('ELASTIC_HOST','" . $result_array ['elastic'] ['host'] . "');\n";
		else
			$content .= "define('ELASTIC_HOST','');\n";
		if (isset ( $result_array ['elastic'] ['index'] ) && ! empty ( $result_array ['elastic'] ['index'] ))
			$content .= "define('ELASTIC_INDEX','" . $result_array ['elastic'] ['index'] . "');\n";
		else
			$content .= "define('ELASTIC_INDEX','');\n";
	} else {
		define ( "ELASTIC_HOST", "@elastic.host@" );
		define ( "ELASTIC_INDEX", "@elastic.index@" );
		$content .= "define('ELASTIC_HOST','@elastic.host@');\n";
		$content .= "define('ELASTIC_INDEX','@elastic.index@');\n";
	}
	$content .= comment ( "Nombre de chartes à signer" );
	// datapolicy
	if (isset ( $result_array ['signDatapolicies'] ['signDatapolicy'] ) && ! empty ( $result_array ['signDatapolicies'] ['signDatapolicy'] ))
		$content .= "define('PortalNbSignDataPolicy','" . count ( $result_array ['signDatapolicies'] ['signDatapolicy'] ) . "');\n";
	else
		$content .= "define('PortalNbSignDataPolicy','');\n";
	$i = 0;
	$content .= comment ( "Chartes de la datapolicy à signer" );
	foreach ( $result_array ['signDatapolicies'] ['signDatapolicy'] as $dp ) {
		if (isset ( $dp ) && ! empty ( $dp ))
			$content .= "define('PortalSignDataPolicy" . $i . "','" . $dp . "');\n";
		else
			$content .= "define('PortalSignDataPolicy" . $i . "','');\n";
		$i ++;
	}
	$content .= comment ( "Le code de la page d'acceuil : 0 si c'est une page d'acceuil normale, et de 1 à 7 pour afficher selon les critères de recherche disponibles (même ordre fichier xml)" );
	if (isset ( $result_array ['homePage'] ) && ! empty ( $result_array ['homePage'] ))
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "_HomePage','" . $result_array ['homePage'] . "');\n";
	else
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "_HomePage','0');\n";

	/****************************************************************************************************************************
	*  MENU DE GAUCHE
	* 
	*/


	$content .= comment ( "Menu du gauche pour le projet, la valeur des paramètres doit être true ou false" );
	// parameters
	$param = $result_array ['parameters'];
	if (isset ( $param ['HasRequestData'] ) && ! empty ( $param ['HasRequestData'] ))
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "_HasRequestData','" . $param ['HasRequestData'] . "');\n";
	else
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "_HasRequestData','');\n";
	if (isset ( $param ['HasProvideData'] ) && ! empty ( $param ['HasProvideData'] ))
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "_HasProvideData','" . $param ['HasProvideData'] . "');\n";
	else
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "_HasProvideData','');\n";
	if (isset ( $param ['HasAdminCorner'] ) && ! empty ( $param ['HasAdminCorner'] ))
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "_HasAdminCorner','" . $param ['HasAdminCorner'] . "');\n";
	else
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "_HasAdminCorner','');\n";
	if (isset ( $param ['HasParameterSearch'] ) && ! empty ( $param ['HasParameterSearch'] ))
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "_HasParameterSearch','" . $param ['HasParameterSearch'] . "');\n";
	else
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "_HasParameterSearch','');\n";
	if (isset ( $param ['HasInstrumentSearch'] ) && ! empty ( $param ['HasInstrumentSearch'] ))
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "_HasInstrumentSearch','" . $param ['HasInstrumentSearch'] . "');\n";
	else
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "_HasInstrumentSearch','');\n";
	if (isset ( $param ['HasCountrySearch'] ) && ! empty ( $param ['HasCountrySearch'] ))
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "_HasCountrySearch','" . $param ['HasCountrySearch'] . "');\n";
	else
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "_HasCountrySearch','');\n";
	if (isset ( $param ['HasPlatformSearch'] ) && ! empty ( $param ['HasPlatformSearch'] ))
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "_HasPlatformSearch','" . $param ['HasPlatformSearch'] . "');\n";
	else
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "_HasPlatformSearch','');\n";
	if (isset ( $param ['HasProjectSearch'] ) && ! empty ( $param ['HasProjectSearch'] ))
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "_HasProjectSearch','" . $param ['HasProjectSearch'] . "');\n";
	else
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "_HasProjectSearch','');\n";
	if (isset ( $param ['HasEventSearch'] ) && ! empty ( $param ['HasEventSearch'] ))
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "_HasEventSearch','" . $param ['HasEventSearch'] . "');\n";
	else
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "_HasEventSearch','');\n";
	if (isset ( $param ['HasCampaignSearch'] ) && ! empty ( $param ['HasCampaignSearch'] ))
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "_HasCampaignSearch','" . $param ['HasCampaignSearch'] . "');\n";
	else
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "_HasCampaignSearch','');\n";
	if (isset ( $param ['HasModelRequest'] ) && ! empty ( $param ['HasModelRequest'] ))
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "_HasModelRequest','" . $param ['HasModelRequest'] . "');\n";
	else
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "_HasModelRequest','');\n";
	if (isset ( $param ['HasSatelliteRequest'] ) && ! empty ( $param ['HasSatelliteRequest'] ))
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "_HasSatelliteRequest','" . $param ['HasSatelliteRequest'] . "');\n";
	else
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "_HasSatelliteRequest','');\n";
	if (isset ( $param ['HasInsituRequest'] ) && ! empty ( $param ['HasInsituRequest'] ))
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "_HasInsituRequest','" . $param ['HasInsituRequest'] . "');\n";
	else
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "_HasInsituRequest','');\n";
	if (isset ( $param ['HasModelOutputs'] ) && ! empty ( $param ['HasModelOutputs'] ))
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "_HasModelOutputs','" . $param ['HasModelOutputs'] . "');\n";
	else
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "_HasModelOutputs','');\n";
	if (isset ( $param ['HasSatelliteProducts'] ) && ! empty ( $param ['HasSatelliteProducts'] ))
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "_HasSatelliteProducts','" . $param ['HasSatelliteProducts'] . "');\n";
	else
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "_HasSatelliteProducts','');\n";
	if (isset ( $param ['HasInsituProducts'] ) && ! empty ( $param ['HasInsituProducts'] ))
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "_HasInsituProducts','" . $param ['HasInsituProducts'] . "');\n";
	else
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "_HasInsituProducts','');\n";
	if (isset ( $param ['HasMultiInsituProducts'] ) && ! empty ( $param ['HasMultiInsituProducts'] ))
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "_HasMultiInsituProducts','" . $param ['HasMultiInsituProducts'] . "');\n";
	else
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "_HasMultiInsituProducts','');\n";
	if (isset ( $param ['HasValueAddedProducts'] ) && ! empty ( $param ['HasValueAddedProducts'] ))
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "_HasValueAddedProducts','" . $param ['HasValueAddedProducts'] . "');\n";
	else
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "_HasValueAddedProducts','');\n";
	$content .= comment ( "Les chemins des images des partenaires dans la page d'acceuil" );
	if (isset ( $param ['HasAssociatedUsers'] ) && ! empty ( $param ['HasAssociatedUsers'] ))
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "_HasAssociatedUsers','" . $param ['HasAssociatedUsers'] . "');\n";
	else
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "_HasAssociatedUsers','');\n";
	$content .= comment ( "Pour l'affichage ou pas des autres projets du portail dans le menu du haut comme pour Hymex" );
	if (isset ( $param ['DisplayOnlyProjectOnTopBar'] ) && ! empty ( $param ['DisplayOnlyProjectOnTopBar'] ))
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "_DisplayOnlyProjectOnTopBar','" . $param ['DisplayOnlyProjectOnTopBar'] . "');\n";
	else
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "_DisplayOnlyProjectOnTopBar','');\n";
	$content .= comment ( "Les tags à afficher" );
	if (isset ( $param ['HasBlueTag'] ) && ! empty ( $param ['HasBlueTag'] ))
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "_HasBlueTag','" . $param ['HasBlueTag'] . "');\n";
	else
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "_HasBlueTag','');\n";
	if (isset ( $param ['HasPurpleTag'] ) && ! empty ( $param ['HasPurpleTag'] ))
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "_HasPurpleTag','" . $param ['HasPurpleTag'] . "');\n";
	else
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "_HasPurpleTag','');\n";
	if (isset ( $param ['HasOrangeTag'] ) && ! empty ( $param ['HasOrangeTag'] ))
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "_HasOrangeTag','" . $param ['HasOrangeTag'] . "');\n";
	else
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "_HasOrangeTag','');\n";
	if (isset ( $param ['HasGreenTag'] ) && ! empty ( $param ['HasGreenTag'] ))
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "_HasGreenTag','" . $param ['HasGreenTag'] . "');\n";
	else
		$content .= "define('" . strtolower ( $result_array ['name'] ) . "_HasGreenTag','');\n";
	
	if (! isset ( $result_array ["mainProjects"] ['project'] [0] ) && empty ( $result_array ["mainProjects"] ['project'] [0] )) {
		$tab_mainProjects = $result_array ["mainProjects"] ['project'];
		unset ( $result_array ["mainProjects"] ['project'] );
		$result_array ["mainProjects"] ['project'] [0] = $tab_mainProjects;
	}
	if (! isset ( $result_array ["otherProjects"] ['project'] [0] ) && empty ( $result_array ["otherProjects"] ['project'] [0] )) {
		$tab_otherProjects = $result_array ["otherProjects"] ['project'];
		unset ( $result_array ["otherProjects"] ['project'] );
		$result_array ["otherProjects"] ['project'] [0] = $tab_otherProjects;
	}
	
	$content .= comment ("Paramètre de la carte dans la recherche avancée");
	if (isset ( $result_array ['map']['MAP_DEFAULT_LAT_MIN'] ) && ! empty ( $result_array ['map']['MAP_DEFAULT_LAT_MIN'] ))
		$content .= "define('MAP_DEFAULT_LAT_MIN',".$result_array ['map']['MAP_DEFAULT_LAT_MIN'].");\n";
	else
		$content .= "define('MAP_DEFAULT_LAT_MIN','');\n";
	if (isset ( $result_array ['map']['MAP_DEFAULT_LAT_MAX'] ) && ! empty ( $result_array ['map']['MAP_DEFAULT_LAT_MAX'] ))
		$content .= "define('MAP_DEFAULT_LAT_MAX',".$result_array ['map']['MAP_DEFAULT_LAT_MAX'].");\n";
	else
		$content .= "define('MAP_DEFAULT_LAT_MAX','');\n";
	if (isset ( $result_array ['map']['MAP_DEFAULT_LON_MIN'] ) && ! empty ( $result_array ['map']['MAP_DEFAULT_LON_MIN'] ))
		$content .= "define('MAP_DEFAULT_LON_MIN',".$result_array ['map']['MAP_DEFAULT_LON_MIN'].");\n";
	else
		$content .= "define('MAP_DEFAULT_LON_MIN','');\n";
	if (isset ( $result_array ['map']['MAP_DEFAULT_LON_MAX'] ) && ! empty ( $result_array ['map']['MAP_DEFAULT_LON_MAX'] ))
		$content .= "define('MAP_DEFAULT_LON_MAX',".$result_array ['map']['MAP_DEFAULT_LON_MAX'].");\n";
	else
		$content .= "define('MAP_DEFAULT_LON_MAX','');\n";
	
	// Projects informations
	$j = 0;
	$compt = 0;
	$mainprojects = null;
	foreach ( array (
			$result_array ["mainProjects"],
			$result_array ["otherProjects"] 
	) as $Projects ) {
		foreach ( $Projects ['project'] as $proj ) {
			if (isset ( $proj ) && ! empty ( $proj )) {
				$j ++;
				if ($compt == 0)
					$mainprojects .= $proj ['name'];
				$content .= "\n";
				$content .= comment ( "Paramètres de " . $proj ['name'] );
				$content .= comment ( "Le compte google analytic de " . $proj ['name'] );
				if (isset ( $proj ['googleAnalyticAccount'] ) && ! empty ( $proj ['googleAnalyticAccount'] ))
					$content .= "define('" . strtolower ( $proj ['name'] ) . "GoogleAnalytic','" . $proj ['googleAnalyticAccount'] . "');\n";
				else
					$content .= "define('" . strtolower ( $proj ['name'] ) . "GoogleAnalytic','');\n";
				$content .= comment ( "Les différents ID des sites que le projet peut avoir" );
				if (isset ( $proj ['sites'] ) && ! empty ( $proj ['sites'] ))
					$content .= "define('" . strtoupper ( $proj ['name'] ) . "_SITES','" . $proj ['sites'] . "');\n";
				else
					$content .= "define('" . strtoupper ( $proj ['name'] ) . "_SITES','');\n";
				$content .= comment ( "Répertoires pour les dépots ftp" );
				if (isset ( $proj ['depot'] ) && ! empty ( $proj ['depot'] ))
					$content .= "define('" . strtoupper ( $proj ['name'] ) . "_DEPOT','" . $proj ['depot'] . "');\n";
				else
					$content .= "define('" . strtoupper ( $proj ['name'] ) . "_DEPOT','');\n";
				$content .= comment ( "Hauteur de la bannière" );
				if (isset ( $proj ['bannerHeight'] ) && ! empty ( $proj ['bannerHeight'] ))
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_BannerHeight','" . $proj ['bannerHeight'] . "');\n";
				else
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_BannerHeight','');\n";
				$content .= comment ( "Affichage ou pas du nom du projet dans la bannière" );
				if (isset ( $proj ['displayBannerTitle'] ) && ! empty ( $proj ['displayBannerTitle'] ))
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_DisplayBannerTitle','" . $proj ['displayBannerTitle'] . "');\n";
				else
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_DisplayBannerTitle','');\n";
				$content .= comment ( "Affichage ou pas du logo du projet dans la bannière" );
				if (isset ( $proj ['displayLogoOnBanner'] ) && ! empty ( $proj ['displayLogoOnBanner'] ))
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_DisplayLogoOnBanner','" . $proj ['displayLogoOnBanner'] . "');\n";
				else
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_DisplayLogoOnBanner','');\n";
				$content .= comment ( "Le chemin du logo du projet" );
				if (isset ( $proj ['logoPath'] ) && ! empty ( $proj ['logoPath'] ))
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_LogoPath','" . $proj ['logoPath'] . "');\n";
				else
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_LogoPath','');\n";
				$content .= comment ( "La barre du haut pour le projet" );
				if (isset ( $proj ['topbarPath'] ) && ! empty ( $proj ['topbarPath'] ))
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_TopBarPath','" . $proj ['topbarPath'] . "');\n";
				else
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_TopBarPath','');\n";
				$content .= comment ( "Année et mois du début du projet" );
				if (isset ( $proj ['yearStart'] ) && ! empty ( $proj ['yearStart'] ))
					$content .= "define('" . strtolower ( $proj ['name'] ) . "yDeb','" . $proj ['yearStart'] . "');\n";
				else
					$content .= "define('" . strtolower ( $proj ['name'] ) . "yDeb','');\n";
				if (isset ( $proj ['monthStart'] ) && ! empty ( $proj ['monthStart'] ))
					$content .= "define('" . strtolower ( $proj ['name'] ) . "mDeb','" . $proj ['monthStart'] . "');\n";
				else
					$content .= "define('" . strtolower ( $proj ['name'] ) . "mDeb','');\n";
				$content .= comment ( "Site web du projet s'il y en a " );
				if (isset ( $proj ['website'] ) && ! empty ( $proj ['website'] ))
					$content .= "define('" . strtolower ( $proj ['name'] ) . "WebSite','" . $proj ['website'] . "');\n";
				else
					$content .= "define('" . strtolower ( $proj ['name'] ) . "WebSite','');\n";
				$content .= comment ( "Email du responsable du projet" );
				if (isset ( $proj ['managerEmail'] ) && ! empty ( $proj ['managerEmail'] ))
					$content .= "define('" . strtolower ( $proj ['name'] ) . "Manager_Email','" . $proj ['managerEmail'] . "');\n";
				else
					$content .= "define('" . strtolower ( $proj ['name'] ) . "Manager_Email','');\n";
				$content .= comment ( "l'adresse mail des admin du projet" );
				if (isset ( $proj ['adminGroupEmail'] ) && ! empty ( $proj ['adminGroupEmail'] ))
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_AdminGroup_Email','" . $proj ['adminGroupEmail'] . "');\n";
				else
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_AdminGroup_Email','');\n";
				$content .= comment ( "Datapolicy générale du projet" );
				if (isset ( $proj ['datapolicy'] ) && ! empty ( $proj ['datapolicy'] )) {
					$content .= "define('" . strtolower ( $proj ['name'] ) . "DataPolicy','" . $proj ['datapolicy'] . "');\n";
					$ldapProjects [] = $proj ['name'];
				} else
					$content .= "define('" . strtolower ( $proj ['name'] ) . "DataPolicy','');\n";
				if ($j < count ( $result_array ['mainProjects'] ['project'] ) && $compt == 0) {
					$mainprojects .= ",";
				}
				// roles
				$roles = null;
				$m = 0;
				foreach ( $proj ['roles'] ['role'] as $role ) {
					$m ++;
					$roles .= $role;
					if ($m < count ( $proj ['roles'] ['role'] )) {
						$roles .= ",";
					}
				}
				$content .= comment ( "Liste des roles pour chaque projet ('hymexCore','hymexAsso', ...)" );
				if (isset ( $roles ) && ! empty ( $roles ))
					$content .= "define('" . strtolower ( $proj ['name'] ) . "ListRoles','" . $roles . "');\n";
				else
					$content .= "define('" . strtolower ( $proj ['name'] ) . "ListRoles','');\n";
				$roles = null;
				// logos
				$c = 0;
				$logos = null;
				foreach ( $proj ['HomePageAssoLogosPath'] ['logo'] as $logo ) {
					$c ++;
					$logos .= $logo;
					if ($c < count ( $proj ['HomePageAssoLogosPath'] ['logo'] )) {
						$logos .= ",";
					}
				}
				$content .= comment ( "Les chemins des logos des partenaires dans la page d'acceuil du projets" );
				if (isset ( $logos ) && ! empty ( $logos ))
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_HomePageAssoLogosPath','" . $logos . "');\n";
				else
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_HomePageAssoLogosPath','');\n";
				$logos = null;
				// subprojects
				$k = 0;
				$subprojects = null;
				foreach ( $proj ['subprojects'] ['subproject'] as $subproj ) {
					$k ++;
					if (count ( $proj ['subprojects'] ['subproject'] ) > 1)
						$subprojects .= $subproj ['name'];
					else
						$subprojects .= $subproj;
					if ($k < count ( $proj ['subprojects'] ['subproject'] )) {
						$subprojects .= ",";
					}
				}
				$content .= comment ( "Sous projets du projet (Les projets qu'on affiche dans le menu du haut du portail)" );
				if (isset ( $subprojects ) && ! empty ( $subprojects ))
					$content .= "define('" . strtolower ( $proj ['name'] ) . "SubProjects','" . $subprojects . "');\n";
				else
					$content .= "define('" . strtolower ( $proj ['name'] ) . "SubProjects','');\n";
				if (isset ( $subprojects ) && ! empty ( $subprojects ))
					$subProjects [$proj ['name']] = explode ( ',', $subprojects );
				$subprojects = null;
				// datapolicy
				$content .= comment ( "nombre de charte à signer pour le projet" );
				if (isset ( $proj ['signDatapolicies'] ['signDatapolicy'] ) && ! empty ( $proj ['signDatapolicies'] ['signDatapolicy'] ))
					$content .= "define('" . strtolower ( $proj ['name'] ) . "NbSignDataPolicy','" . count ( $proj ['signDatapolicies'] ['signDatapolicy'] ) . "');\n";
				else
					$content .= "define('" . strtolower ( $proj ['name'] ) . "NbSignDataPolicy','');\n";
				$content .= comment ( "Chartes à signer pour le projet" );
				$i = 0;
				foreach ( $proj ['signDatapolicies'] ['signDatapolicy'] as $dp ) {
					if (isset ( $dp ) && ! empty ( $dp ))
						$content .= "define('" . strtolower ( $proj ['name'] ) . "SignDataPolicy" . $i . "','" . $dp . "');\n";
					else
						$content .= "define('" . strtolower ( $proj ['name'] ) . "SignDataPolicy" . $i . "','');\n";
					$i ++;
				}
				$content .= comment ( "Le code de la page d'acceuil : 0 si c'est une page d'acceuil normale, et de 1 à 7 pour afficher selon les critères de recherche disponibles (même ordre fichier xml)" );
				if (isset ( $proj ['homePage'] ) && ! empty ( $proj ['homePage'] ))
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_HomePage','" . $proj ['homePage'] . "');\n";
				else
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_HomePage','0');\n";
				$content .= comment ( "Menu du gauche pour le projet, la valeur des paramètres doit être true ou false" );
				// parameters
				$param = $proj ['parameters'];
				if (isset ( $param ['HasAdminCorner'] ) && ! empty ( $param ['HasAdminCorner'] ))
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_HasAdminCorner','" . $param ['HasAdminCorner'] . "');\n";
				else
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_HasAdminCorner','');\n";
				if (isset ( $param ['HasParameterSearch'] ) && ! empty ( $param ['HasParameterSearch'] ))
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_HasParameterSearch','" . $param ['HasParameterSearch'] . "');\n";
				else
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_HasParameterSearch','');\n";
				if (isset ( $param ['HasInstrumentSearch'] ) && ! empty ( $param ['HasInstrumentSearch'] ))
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_HasInstrumentSearch','" . $param ['HasInstrumentSearch'] . "');\n";
				else
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_HasInstrumentSearch','');\n";
				if (isset ( $param ['HasCountrySearch'] ) && ! empty ( $param ['HasCountrySearch'] ))
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_HasCountrySearch','" . $param ['HasCountrySearch'] . "');\n";
				else
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_HasCountrySearch','');\n";
				if (isset ( $param ['HasPlatformSearch'] ) && ! empty ( $param ['HasPlatformSearch'] ))
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_HasPlatformSearch','" . $param ['HasPlatformSearch'] . "');\n";
				else
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_HasPlatformSearch','');\n";
				if (isset ( $param ['HasProjectSearch'] ) && ! empty ( $param ['HasProjectSearch'] ))
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_HasProjectSearch','" . $param ['HasProjectSearch'] . "');\n";
				else
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_HasProjectSearch','');\n";
				if (isset ( $param ['HasEventSearch'] ) && ! empty ( $param ['HasEventSearch'] ))
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_HasEventSearch','" . $param ['HasEventSearch'] . "');\n";
				else
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_HasEventSearch','');\n";
				if (isset ( $param ['HasCampaignSearch'] ) && ! empty ( $param ['HasCampaignSearch'] ))
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_HasCampaignSearch','" . $param ['HasCampaignSearch'] . "');\n";
				else
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_HasCampaignSearch','');\n";
				if (isset ( $param ['HasModelRequest'] ) && ! empty ( $param ['HasModelRequest'] ))
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_HasModelRequest','" . $param ['HasModelRequest'] . "');\n";
				else
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_HasModelRequest','');\n";
				if (isset ( $param ['HasSatelliteRequest'] ) && ! empty ( $param ['HasSatelliteRequest'] ))
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_HasSatelliteRequest','" . $param ['HasSatelliteRequest'] . "');\n";
				else
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_HasSatelliteRequest','');\n";
				if (isset ( $param ['HasInsituRequest'] ) && ! empty ( $param ['HasInsituRequest'] ))
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_HasInsituRequest','" . $param ['HasInsituRequest'] . "');\n";
				else
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_HasInsituRequest','');\n";
				if (isset ( $param ['HasModelOutputs'] ) && ! empty ( $param ['HasModelOutputs'] ))
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_HasModelOutputs','" . $param ['HasModelOutputs'] . "');\n";
				else
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_HasModelOutputs','');\n";
				if (isset ( $param ['HasSatelliteProducts'] ) && ! empty ( $param ['HasSatelliteProducts'] ))
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_HasSatelliteProducts','" . $param ['HasSatelliteProducts'] . "');\n";
				else
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_HasSatelliteProducts','');\n";
				if (isset ( $param ['HasInsituProducts'] ) && ! empty ( $param ['HasInsituProducts'] ))
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_HasInsituProducts','" . $param ['HasInsituProducts'] . "');\n";
				else
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_HasInsituProducts','');\n";
				if (isset ( $param ['HasMultiInsituProducts'] ) && ! empty ( $param ['HasMultiInsituProducts'] ))
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_HasMultiInsituProducts','" . $param ['HasMultiInsituProducts'] . "');\n";
				else
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_HasMultiInsituProducts','');\n";
				if (isset ( $param ['HasValueAddedProducts'] ) && ! empty ( $param ['HasValueAddedProducts'] ))
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_HasValueAddedProducts','" . $param ['HasValueAddedProducts'] . "');\n";
				else
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_HasValueAddedProducts','');\n";
				$content .= comment ( "Les chemins des images des partenaires dans la page d'acceuil" );
				if (isset ( $param ['HasAssociatedUsers'] ) && ! empty ( $param ['HasAssociatedUsers'] ))
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_HasAssociatedUsers','" . $param ['HasAssociatedUsers'] . "');\n";
				else
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_HasAssociatedUsers','');\n";
				$content .= comment ( "Pour l'affichage ou pas des autres projets du portail dans le menu du haut comme pour Hymex" );
				if (isset ( $param ['DisplayOnlyProjectOnTopBar'] ) && ! empty ( $param ['DisplayOnlyProjectOnTopBar'] ))
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_DisplayOnlyProjectOnTopBar','" . $param ['DisplayOnlyProjectOnTopBar'] . "');\n";
				else
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_DisplayOnlyProjectOnTopBar','');\n";
				$content .= comment ( "Les tags à afficher" );
				if (isset ( $param ['HasBlueTag'] ) && ! empty ( $param ['HasBlueTag'] ))
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_HasBlueTag','" . $param ['HasBlueTag'] . "');\n";
				else
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_HasBlueTag','');\n";
				if (isset ( $param ['HasPurpleTag'] ) && ! empty ( $param ['HasPurpleTag'] ))
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_HasPurpleTag','" . $param ['HasPurpleTag'] . "');\n";
				else
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_HasPurpleTag','');\n";
				if (isset ( $param ['HasOrangeTag'] ) && ! empty ( $param ['HasOrangeTag'] ))
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_HasOrangeTag','" . $param ['HasOrangeTag'] . "');\n";
				else
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_HasOrangeTag','');\n";
				if (isset ( $param ['HasGreenTag'] ) && ! empty ( $param ['HasGreenTag'] ))
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_HasGreenTag','" . $param ['HasGreenTag'] . "');\n";
				else
					$content .= "define('" . strtolower ( $proj ['name'] ) . "_HasGreenTag','');\n";
			}
		}
		$compt ++;
	}
	$i = 0;
	foreach ( $result_array ['otherProjects'] ['project'] as $proj ) {
		$i ++;
		$otherprojects .= $proj ['name'];
		if ($i < count ( $result_array ['otherProjects'] ['project'] )) {
			$otherprojects .= ",";
		}
	}
	$content .= "\n";
	$content .= comment ( "Les projets principaux du portail" );
	if (isset ( $mainprojects ) && ! empty ( $mainprojects ))
		$content .= "define('MainProjects','" . $mainprojects . "');\n";
	else
		$content .= "define('MainProjects','');\n";
	$content .= "\n";
	$content .= comment ( "Les autres projets du portail" );
	if (isset ( $otherprojects ) && ! empty ( $otherprojects ))
		$content .= "define('OtherProjects','" . $otherprojects . "');\n";
	else
		$content .= "define('OtherProjects','');\n";
	$content .= "\n\n";
	$content .= comment ( "Tableau qui contient tous les projets principaux du portail" );
	$content .= '$MainProjects = explode(",", "' . $mainprojects . '");';
	$content .= "\n";
	$content .= comment ( "Tableau qui contient les autres projets" );
	$content .= '$OtherProjects = explode(",", "' . $otherprojects . '");';
	$content .= "\n\n";
	$content .= "?>";
	fwrite ( $file, $content );
	global $Projects, $Portal_name, $database, $app_path;
	$Projects = explode ( ',', $mainprojects . ',' . $otherprojects );
	$Portal_name = $result_array ['name'];
	$app_path = $result_array ['webPath'];
	$database = array (
			"0" => $result_array ['database'] ['host'],
			"1" => $result_array ['database'] ['user'],
			"2" => $result_array ['database'] ['name'] 
	);
	fclose ( $file );
}

// Portal database creation function
function duplicateDatabase($db2) {
	exec('pg_dump -h '.Duplicated_db_host.' -U '.Duplicated_db_user.' -s '.Duplicated_db_name.' > ./target/database/dumpSchemaDatabase.sql');
	exec('pg_dump -h '.Duplicated_db_host.' -U '.Duplicated_db_user.' --data-only -t gcmd_plateform_keyword -t gcmd_instrument_keyword -t gcmd_science_keyword -t dataset_type -t unit -t organism -t contact_type -t data_format -t type_journal '.Duplicated_db_name.' > ./target/database/dumpTablesDatabase.sql');
	$db2conn = pg_connect ( "host=" . Duplicated_db_host . " user=" . Duplicated_db_user );
	$result = pg_query ( $db2conn, "SELECT 1 AS result FROM pg_database WHERE datname='".strtolower($db2[2])."';" );
	$db_exists = pg_fetch_row ( $result );
	if ($db_exists [0] != 1) {
		pg_query ( $db2conn, 'CREATE DATABASE ' . strtolower($db2 [2]) . ';' );
		pg_close ( $db2conn );
		exec ( 'psql -h ' . $db2 [0] . ' -U ' . $db2 [1] . ' -d ' . strtolower($db2 [2]) . ' -f ./target/database/dumpSchemaDatabase.sql' );
		exec ( 'psql -h ' . $db2 [0] . ' -U ' . $db2 [1] . ' -f ./target/database/dumpTablesDatabase.sql ' . strtolower($db2 [2]) );
	}
}
// Sphinx database creation function
function duplicateSphinxDatabase($db) {
	exec('pg_dump -h '.Sphinx_Duplicated_db_host.' -U '.Sphinx_Duplicated_db_user.' -s '.Sphinx_Duplicated_db_name.' > ./target/database/dumpSchemaSphinxDatabase.sql');
	$dbconn = pg_connect ( "host=" . $db [0] . " user=" . $db [1] );
	$result = pg_query ( $dbconn, "SELECT 1 AS result FROM pg_database WHERE datname='".strtolower($db[2])."';" );
	$db_exists = pg_fetch_row ( $result );
	if ($db_exists [0] != 1) {
		pg_query ( $dbconn, 'CREATE DATABASE ' . strtolower($db [2]) . ';' );
		pg_close ( $dbconn );
		exec ( 'psql -h ' . $db [0] . ' -U ' . $db [1] . ' -d ' . strtolower($db [2]) . ' -f ./target/database/dumpSchemaSphinxDatabase.sql' );
	}
}

// LDAP creation functions
function generateFile($filepath, $content) {
	$file = fopen ( $filepath, "w" ) or die ( "Unable to open file!" );
	fwrite ( $file, $content );
	fclose ( $file );
}
function generateProjectsSchemas($ldapProjects) {
	global $Portal_name;
	$l = new ldapIds();
	$x = $l->getId($Portal_name);
	$x1 = 1;
	$x2 = 1;
	$content .= "dn: cn=" . strtolower ( $Portal_name ) . ",cn=schema,cn=config\n";
	$content .= "cn: " . strtolower ( $Portal_name ) . " \n";
	$content .= "objectclass: olcSchemaConfig\n";
	$content .= "olcattributetypes: ( 1.3.6.1.4.1.23115.1.1.70.2." . $x . ".1.1 NAME '" . strtolower ( $Portal_name ) . "ApplicationDate' DESC 'Date (format YYYYMMDD, only numeric chars)' EQUALITY numericStringMatch SUBSTR numericStringSubstringsMatch SYNTAX 1.3.6.1.4.1.1466.115.121.1.36{8} SINGLE-VALUE )\n";
	$content .= "olcattributetypes: ( 1.3.6.1.4.1.23115.1.1.70.2." . $x . ".1.2 NAME '" . strtolower ( $Portal_name ) . "RegistrationDate' DESC 'Date (format YYYYMMDD, only numeric chars)' EQUALITY numericStringMatch SUBSTR numericStringSubstringsMatch SYNTAX 1.3.6.1.4.1.1466.115.121.1.36{8} SINGLE-VALUE )\n";
	$content .= "olcattributetypes: ( 1.3.6.1.4.1.23115.1.1.70.2." . $x . ".1.3 NAME '" . strtolower ( $Portal_name ) . "Status' DESC 'Etat de la demande : en cours, acceptee ou rejetee' EQUALITY caseIgnoreMatch SYNTAX 1.3.6.1.4.1.1466.115.121.1.44 )\n";
	$content .= "olcattributetypes: ( 1.3.6.1.4.1.23115.1.1.70.2." . $x . ".1.4 NAME '" . strtolower ( $Portal_name ) . "Abstract' DESC 'Description du travail dans " . strtolower ( $Portal_name ) . "' EQUALITY caseIgnoreMatch  SUBSTR caseIgnoreSubstringsMatch SYNTAX 1.3.6.1.4.1.1466.115.121.1.15{1024} )\n";
	$content .= "olcobjectclasses: ( 1.3.6.1.4.1.23115.1.1.70.2." . $x . ".2.1 NAME '" . strtolower ( $Portal_name ) . "User' DESC 'Attributs specifiques aux Utilisateurs du projet " . strtolower ( $Portal_name ) . "' SUP top AUXILIARY MUST " . strtolower ( $Portal_name ) . "ApplicationDate MAY ( " . strtolower ( $Portal_name ) . "RegistrationDate $ " . strtolower ( $Portal_name ) . "Status $ " . strtolower ( $Portal_name ) . "Abstract) )\n";
	$x1 = 5;
	$x2 = 2;
	foreach ( $ldapProjects as $proj ) {
		$content .= "olcattributetypes: ( 1.3.6.1.4.1.23115.1.1.70.2." . $x . ".1.". $x1 ." NAME '" . strtolower ( $proj ) . "ApplicationDate' DESC 'Date (format YYYYMMDD, only numeric chars)' EQUALITY numericStringMatch SUBSTR numericStringSubstringsMatch SYNTAX 1.3.6.1.4.1.1466.115.121.1.36{8} SINGLE-VALUE )\n";
		$x1++;
		$content .= "olcattributetypes: ( 1.3.6.1.4.1.23115.1.1.70.2." . $x . ".1.". $x1 ." NAME '" . strtolower ( $proj ) . "RegistrationDate' DESC 'Date (format YYYYMMDD, only numeric chars)' EQUALITY numericStringMatch SUBSTR numericStringSubstringsMatch SYNTAX 1.3.6.1.4.1.1466.115.121.1.36{8} SINGLE-VALUE )\n";
		$x1++;
		$content .= "olcattributetypes: ( 1.3.6.1.4.1.23115.1.1.70.2." . $x . ".1.". $x1 ." NAME '" . strtolower ( $proj ) . "Status' DESC 'Etat de la demande : en cours, acceptee ou rejetee' EQUALITY caseIgnoreMatch SYNTAX 1.3.6.1.4.1.1466.115.121.1.44 )\n";
		$x1++;
		$content .= "olcattributetypes: ( 1.3.6.1.4.1.23115.1.1.70.2." . $x . ".1.". $x1 ." NAME '" . strtolower ( $proj ) . "Abstract' DESC 'Description du travail dans " . strtolower ( $proj ) . "' EQUALITY caseIgnoreMatch  SUBSTR caseIgnoreSubstringsMatch SYNTAX 1.3.6.1.4.1.1466.115.121.1.15{1024} )\n";
		$x1++;
		$content .= "olcattributetypes: ( 1.3.6.1.4.1.23115.1.1.70.2." . $x . ".1.". $x1 ." NAME '" . strtolower ( $proj ) . "Wg' DESC 'Work Group " . strtolower ( $proj ) . "' EQUALITY caseIgnoreMatch SYNTAX 1.3.6.1.4.1.1466.115.121.1.44 )\n";
		$x1++;
		$content .= "olcattributetypes: ( 1.3.6.1.4.1.23115.1.1.70.2." . $x . ".1.". $x1 ." NAME '" . strtolower ( $proj ) . "AssociatedProject' DESC '' EQUALITY caseIgnoreMatch SYNTAX 1.3.6.1.4.1.1466.115.121.1.15 )\n";
		$x1++;
		$content .= "olcobjectclasses: ( 1.3.6.1.4.1.23115.1.1.70.2." . $x . ".2.". $x2 ." NAME '" . strtolower ( $proj ) . "User' DESC 'Attributs specifiques aux Utilisateurs du projet " . strtolower ( $proj ) . "' SUP top AUXILIARY MUST " . strtolower ( $proj ) . "ApplicationDate MAY ( " . strtolower ( $proj ) . "RegistrationDate $ " . strtolower ( $proj ) . "Status $ " . strtolower ( $proj ) . "Abstract $ " . strtolower ( $proj ) . "Wg $ " . strtolower ( $proj ) . "AssociatedProject) )\n";
	    $x1++;
		$x2++;
	}
	generateFile ( './target/ldap/' . strtolower ( $Portal_name ) . '.schema', $content );
}
function generateDB_CONFIG() {
	$content = "set_cachesize 0 268435456 1\n";
	$content .= "set_lg_regionmax 262144\n";
	$content .= "set_lg_bsize 2097152\n";
	generateFile ( './target/ldap/DB_CONFIG', $content );
}
function generateProjectLdif() {
	global $Portal_name;
	$content .= "dn: olcDatabase=bdb,cn=config\n";
	$content .= "objectclass: olcDatabaseConfig\n";
	$content .= "objectclass: olcBdbConfig\n";
	$content .= "olcaccess: to *  by dn.base=\"cn=replication,dc=" . strtolower ( $Portal_name ) . ",dc=sedoo,dc=fr\" read   by * none break\n";
	$content .= "olcaccess: to attrs=userPassword  by self write  by set.exact=\"user/memberOf & [root]\" write  by dn.base=\"cn=wwwadm,dc=" . strtolower ( $Portal_name ) . ",dc=sedoo,dc=fr\" write  by anonymous auth  by * none\n";
	$content .= "olcaccess: to attrs=memberOf  by self read  by dn.base=\"cn=wwwadm,dc=" . strtolower ( $Portal_name ) . ",dc=sedoo,dc=fr\" write  by set.exact=\"user/memberOf & [root]\" write  by set.exact=\"user/memberOf & [admin]\" write  by * none\n";
	$content .= "olcaccess: to *  by self write  by set.exact=\"user/memberOf & [root]\" write  by dn.base=\"cn=wwwadm,dc=" . strtolower ( $Portal_name ) . ",dc=sedoo,dc=fr\" write  by users read by * search\n";
	$content .= "olcaddcontentacl: FALSE\n";
	$content .= "olcdatabase: bdb\n";
	$content .= "olcdbcachefree: 1\n";
	$content .= "olcdbcachesize: 1000\n";
	$content .= "olcdbconfig: {0}set_cachesize 0 268435456 1\n";
	$content .= "olcdbconfig: {1}\n";
	$content .= "olcdbconfig: {2}set_lg_regionmax 262144\n";
	$content .= "olcdbconfig: {3}set_lg_bsize 2097152\n";
	$content .= "olcdbdirectory: " . REP_LDAP . "\n";
	$content .= "olcdbdirtyread: FALSE\n";
	$content .= "olcdbdncachesize: 0\n";
	$content .= "olcdbidlcachesize: 0\n";
	$content .= "olcdbindex: objectClass pres,eq\n";
	$content .= "olcdbindex: cn pres,eq,sub\n";
	$content .= "olcdbindex: ou pres,eq,sub\n";
	$content .= "olcdbindex: mail pres,eq,sub\n";
	$content .= "olcdbindex: sn pres,eq,sub\n";
	$content .= "olcdbindex: memberOf pres,eq\n";
	$content .= "olcdbindex: givenName pres,eq,sub\n";
	$content .= "olcdblinearindex: FALSE\n";
	$content .= "olcdbmode: 0600\n";
	$content .= "olcdbnosync: FALSE\n";
	$content .= "olcdbsearchstack: 16\n";
	$content .= "olcdbshmkey: 0\n";
	$content .= "olclastmod: TRUE\n";
	$content .= "olcmaxderefdepth: 15\n";
	$content .= "olcmonitoring: TRUE\n";
	$content .= "olcreadonly: FALSE\n";
	$content .= "olcrootdn: cn=Manager,dc=" . strtolower ( $Portal_name ) . ",dc=sedoo,dc=fr\n";
	$content .= "olcrootpw: pro001\n";
	$content .= "olcsizelimit: unlimited\n";
	$content .= "olcsuffix: dc=" . strtolower ( $Portal_name ) . ",dc=sedoo,dc=fr\n";
	$content .= "olcsyncusesubentry: FALSE\n";
	$content .= "\n";
	generateFile ( './target/ldap/' . strtolower ( $Portal_name ) . '.ldif', $content );
}
function generateInitLdif($ldapProjects) {
	global $Portal_name;
	$content .= "dn: dc=" . strtolower ( $Portal_name ) . ",dc=sedoo,dc=fr\n";
	$content .= "objectClass: dcObject\n";
	$content .= "objectClass: project\n";
	$content .= "dc: " . strtolower ( $Portal_name ) . "\n";
	$content .= "cn: " . $Portal_name . "\n";
	$content .= "\n";
	$content .= "dn: ou=People,dc=" . strtolower ( $Portal_name ) . ",dc=sedoo,dc=fr\n";
	$content .= "objectClass: organizationalUnit\n";
	$content .= "ou: People\n";
	$content .= "\n";
	$content .= "dn: ou=Group,dc=" . strtolower ( $Portal_name ) . ",dc=sedoo,dc=fr\n";
	$content .= "objectClass: organizationalUnit\n";
	$content .= "ou: Group\n";
	$content .= "\n";
	$content .= "dn: ou=Project,dc=" . strtolower ( $Portal_name ) . ",dc=sedoo,dc=fr\n";
	$content .= "objectClass: organizationalUnit\n";
	$content .= "ou: Project\n";
	$content .= "\n";
	$content .= "dn: cn=wwwadm,dc=" . strtolower ( $Portal_name ) . ",dc=sedoo,dc=fr\n";
	$content .= "objectClass: person\n";
	$content .= "cn: wwwadm\n";
	$content .= "sn: wwwadm\n";
	$content .= "userPassword: www001\n";
	$content .= "\n";
	$content .= "dn: cn=replication,dc=" . strtolower ( $Portal_name ) . ",dc=sedoo,dc=fr\n";
	$content .= "objectClass: person\n";
	$content .= "cn: replication\n";
	$content .= "sn: replication\n";
	$content .= "userPassword: rep001\n";
	$content .= "\n";
	$content .= "dn: groupId=root,ou=Group,dc=" . strtolower ( $Portal_name ) . ",dc=sedoo,dc=fr\n";
	$content .= "objectClass: group\n";
	$content .= "groupId: root\n";
	$content .= "cn: Database Superusers\n";
	$content .= "isAdmin: TRUE\n";
	$content .= "\n";
	$content .= "dn: groupId=" . strtolower ( $Portal_name ) . ",ou=Group,dc=" . strtolower ( $Portal_name ) . ",dc=sedoo,dc=fr\n";
	$content .= "objectClass: group\n";
	$content .= "objectClass: top\n";
	$content .= "groupId: " . strtolower ( $Portal_name ) . "\n";
	$content .= "cn: " . $Portal_name . " Users\n";
	$content .= "isAdmin: FALSE\n";
	$content .= "parentProject: " . $Portal_name . "\n";
	$content .= "\n";
	$content .= "dn: groupId=" . strtolower ( $Portal_name ) . "Adm,ou=Group,dc=" . strtolower ( $Portal_name ) . ",dc=sedoo,dc=fr\n";
	$content .= "objectClass: group\n";
	$content .= "objectClass: top\n";
	$content .= "groupId: " . strtolower ( $Portal_name ) . "Adm\n";
	$content .= "cn: " . $Portal_name . " Admins\n";
	$content .= "isAdmin: TRUE\n";
	$content .= "memberUid: guillaume.brissebrat@obs-mip.fr\n";
	$content .= "parentProject: " . $Portal_name . "\n";
	$content .= "\n";
	$content .= "dn: cn=" . strtolower ( $Portal_name ) . ",ou=Project,dc=" . strtolower ( $Portal_name ) . ",dc=sedoo,dc=fr\n";
	$content .= "objectClass: project\n";
	$content .= "objectClass: top\n";
	$content .= "cn: " . $Portal_name . "\n";
	$content .= "\n";
	foreach ( $ldapProjects as $proj ) {
		$content .= "dn: groupId=" . strtolower ( $proj ) . "Adm,ou=Group,dc=" . strtolower ( $Portal_name ) . ",dc=sedoo,dc=fr\n";
		$content .= "objectClass: group\n";
		$content .= "groupId: " . strtolower ( $proj ) . "Adm\n";
		$content .= "cn: " . $proj . " Admins\n";
		$content .= "isAdmin: TRUE\n";
		$content .= "memberUid: guillaume.brissebrat@obs-mip.fr\n";
		$content .= "parentProject: " . $proj . "\n";
		$content .= "\n";
		$content .= "dn: groupId=" . strtolower ( $proj ) . "Core,ou=Group,dc=" . strtolower ( $Portal_name ) . ",dc=sedoo,dc=fr\n";
		$content .= "objectClass: group\n";
		$content .= "groupId: " . strtolower ( $proj ) . "Core\n";
		$content .= "cn: " . $proj . " Core Users\n";
		$content .= "isAdmin: FALSE\n";
		$content .= "parentProject: " . $proj . "\n";
		$content .= "\n";
		$content .= "dn: groupId=" . strtolower ( $proj ) . "Asso,ou=Group,dc=" . strtolower ( $Portal_name ) . ",dc=sedoo,dc=fr\n";
		$content .= "objectClass: group\n";
		$content .= "groupId: " . strtolower ( $proj ) . "Asso\n";
		$content .= "cn: " . $proj . " Associated Scientists\n";
		$content .= "isAdmin: FALSE\n";
		$content .= "parentProject: " . $proj . "\n";
		$content .= "\n";
		$content .= "dn: groupId=" . strtolower ( $proj ) . "Participant,ou=Group,dc=" . strtolower ( $Portal_name ) . ",dc=sedoo,dc=fr\n";
		$content .= "objectClass: group\n";
		$content .= "groupId: " . strtolower ( $proj ) . "Participant\n";
		$content .= "cn: " . $proj . " Participants (other site web users)\n";
		$content .= "isAdmin: FALSE\n";
		$content .= "parentProject: " . $proj . "\n";
		$content .= "\n";
		$content .= "dn: ou=Project,dc=" . strtolower ( $Portal_name ) . ",dc=sedoo,dc=fr\n";
		$content .= "objectClass: project\n";
		$content .= "cn: " . $proj . "\n";
		$content .= "\n";
	}
	generateFile ( './target/ldap/init.ldif', $content );
}
function generateLdapConfigFiles($ldapProjects) {
	generateDB_CONFIG ();
	generateProjectsSchemas ( $ldapProjects );
	generateProjectLdif ();
	generateInitLdif ( $ldapProjects );
	generateLdapCreationScript ();
}
function generateLdapCreationScript() {
	global $Portal_name;
	$content .= "#!/usr/bin/php \n";
	$content .= "<?php \n";
	$content .= "set_include_path ( '.:/usr/share/php' );\n";
	$content .= "exec('ldapadd -xw ldap001 -D cn=config -h localhost -f " . strtolower ( $Portal_name ) . ".schema'); \n";
	$content .= "exec('mkdir -p ".REP_LDAP."'); \n";
	$content .= "exec('chown ldap:ldap " . REP_LDAP . "'); \n";
	$content .= "exec('cp -R DB_CONFIG " . REP_LDAP . "/'); \n";
	$content .= "exec('ldapadd -xw ldap001 -D cn=config -h localhost -f " . strtolower ( $Portal_name ) . ".ldif'); \n";
	$content .= "exec('ldapadd -xw pro001 -D cn=Manager,dc=" . strtolower ( $Portal_name ) . ",dc=sedoo,dc=fr -h localhost -f init.ldif'); \n";
	$content .= "?>";
	generateFile ( './target/ldap/' . strtolower ( $Portal_name ) . 'LdapCreationScript.php', $content );
}

// PHP server configuration
function generateConfdFile($server_name, $app_path) {
	global $Portal_name;
	$content .= "<VirtualHost *:80> \n" . "\t ServerName $server_name \n" . "\t DocumentRoot $app_path \n" . "\t CustomLog    /var/log/httpd/access_log." . strtolower ( $Portal_name ) . " combined \n" . "\t ErrorLog     /var/log/httpd/error_log." . strtolower ( $Portal_name ) . " \n" . "\t <Directory $app_path> \n" . "\t\t php_value include_path \".:/usr/share/pear:/usr/share/php:/usr/local/lib/php/:$app_path/scripts:$app_path/:$app_path/template:/usr/share/php/jpgraph\" \n" . "\t </Directory> \n" . "\t <Directory $app_path/att_img> \n" . "\t\t AllowOverride All \n" . "\t </Directory> \n" . "\t ScriptAlias /extract/cgi-bin/ /www/" . strtolower ( $Portal_name ) . "-extract/cgi-bin/ \n" . "</VirtualHost> \n";
	generateFile ( './target/apache/' . strtolower ( $Portal_name ) . '.conf', $content );
}

// Extraction filter generation
function generateExtractFilter() {
	global $result_array;
	if (isset ( $result_array ['database'] ['password'] ) && ! empty ( $result_array ['database'] ['password'] ))
		$db_password = $result_array ['database'] ['password'];
	else
		$db_password = '';
	$content .= "log.level=INFO \n" . "log.appender=fileDlyAppender \n" . "\n#root_path = racine definie dans le template.xml \n" . "log.path=" . $result_array ['portalWorkPath'] . "/log \n" . "result.path=" . $result_array ['portalWorkPath'] . "/download \n" . "\n#A partir de l'élement database \n" . "db.host=" . $result_array ['database'] ['host'] . " \n" . "db.name=" . $result_array ['database'] ['name'] . " \n" . "db.username=" . $result_array ['database'] ['user'] . " \n" . "db.password=" . $db_password . " \n" . "\n#A partir de l'element ldap \n" . "ldap.host=" . $result_array ['ldap'] ['host'] . "\n" . "ldap.base=" . $result_array ['ldap'] ['base'] . " \n" . "\n#A partir du nom DNS configure dans le template \n" . "ui.dl=http://" . $result_array ['dns'] . "/extract/download.php \n" . "ui.dl.pub=http://" . $result_array ['dns'] . "/extract/downloadPub.php \n" . "\nxml.response.schema.uri=http://" . $result_array ['dns'] . "/extract/reponse \n" . "xml.response.schema.xsd=http://" . $result_array ['dns'] . "/extract/reponse.xsd \n" . "\n#bin defini dans le template.xml \n" . "java.bin=" . $javaBin['java_bin'] . " \n" . "\n#rootEmail \n" . "mail.admin=" . $result_array ['rootEmail'] . " \n" . "mail.from=" . $result_array ['rootEmail'] . " \n" . "mail.topic.prefix=[" . $result_array ['name'] . "-DATABASE] \n";
	generateFile ( "./extracteur/src/main/filters/PORTAL.properties", $content );
}
// extractor generation
function generateExtractor() {
	global $Portal_name, $result_array, $javaBin;
	exec ( "cd ./extracteur; " . $javaBin['maven_bin'] . "/mvn clean package -Dcible=PORTAL", $message );
	echo "\n";
	foreach ( $message as $m )
		echo $m . "\n";
	echo "\n";
	exec ( "cp -R ./extracteur/target/extracteur-install.zip ./target/extraction/" );
}

//backup files generation
function generateBackupFiles(){
	global $result_array,$Portal_name;
	duplicateDirectory ('./input/backup','./target/backup');
	changeWordInDirectory('./target/backup', '#MainProject', strtolower($Portal_name));
	changeWordInDirectory('./target/backup', '#PortalWorkPath', $result_array['portalWorkPath']);
	$tab_path = explode('/',$result_array['portalWorkPath']);
	if(count($tab_path) >= 3){
		for($i=0; $i<count($tab_path)-1; $i++){
			$path .= '/'.$tab_path[$i];
		}
	}
	changeWordInDirectory('./target/backup', '#PortalPath', str_replace("//", "/", $path));
}

//Errors display
function libxml_display_error($error) {
	$return = "\n";
	switch ($error->level) {
		case LIBXML_ERR_WARNING :
			$return .= "Warning $error->code: \n";
			break;
		case LIBXML_ERR_ERROR :
			$return .= "Error $error->code: \n";
			break;
		case LIBXML_ERR_FATAL :
			$return .= "Fatal Error $error->code: \n";
			break;
	}
	$return .= trim ( $error->message );
	if ($error->file) {
		$return .= " in $error->file ";
	}
	$return .= " on line $error->line \n";
	
	return $return;
}
function libxml_display_errors() {
	$errors = libxml_get_errors ();
	foreach ( $errors as $error ) {
		print libxml_display_error ( $error );
	}
	libxml_clear_errors ();
}

// Main program
$xmlFile_path = './input/projet-template.xml';
if (isset ( $argv [1] ) && ! empty ( $argv [1] ))
	$xmlFile_path = $argv [1];
	// Enable user error handling
libxml_use_internal_errors ( true );
$xml = new DOMDocument ();
$xml->load ( $xmlFile_path );
if (! $xml->schemaValidate ( './input/projet-template.xsd' )) {
	print 'DOMDocument::schemaValidate() Generated Errors!';
	libxml_display_errors ();
} else {
	$i = 0;
	$Files_list = array ();
	$Projects = array ();
	$subProjects = array ();
	$ldapProjects = array ();
	$database = array ();
	$result_array = array ();
	$Portal_name = null;
	$app_path = null;
	define ( 'path', './target/template_catalogue' );
	define ( 'template_path', '/template_catalogue' );
	// creation du répertoire target
	if (! file_exists ( "./target" )) {
		exec ( "mkdir ./target" );
	}
	if (! file_exists ( "./target/apache" )) {
		exec ( "mkdir ./target/apache" );
	}
	if (! file_exists ( "./target/extraction" )) {
		exec ( "mkdir ./target/extraction" );
	}
	if (! file_exists ( "./target/ldap" )) {
		exec ( "mkdir ./target/ldap" );
	}
	duplicateDirectory ( '.' . template_path . '/', path . '/' );
	// creation des répertoire att_img et graphs
	if (! file_exists ( path . '/att_img' )) {
		mkdir ( path . '/att_img' );
	}
	if (! file_exists ( path . '/graphs' )) {
		mkdir ( path . '/graphs' );
	}
	exec ( 'chmod -R 777 target' );
	//exec ( 'chown -R '.$apacheConf['user'].':'.$apacheConf['group'].' target' );
	exec ( "cp ./input/.htaccess " . path . "/att_img/" );
	// copie des images dans le répertoire image du projet généré
	if (! file_exists ( './input/img' )) {
		exec ( "mkdir ./input/img" );
	}
	ScanDirectory ( './input/img' );
	foreach ( $Files_list as $img ) {
		exec ( "cp $img " . path . "/img/" );
	}
	echo "\n";
	echo "Please wait...\n";
	for($i = 0; $i < 2; $i ++) {
		echo "====================================";
		sleep ( 1 );
	}
	echo "\n";
	echo "parsing xml file and generating conf.php and conf.php.template files ... 1/9 \n";
	generatePHPFile ( path . "/conf/conf.php" );
	generatePHPFile ( path . "/conf/conf.php.template", 'template' );
	echo "Config file generated successfully !!!\n";
	echo "Creating projects directories ... 2/9 \n";
	createProjectsDirectories ( $Projects );
	echo "Projects directories created successfully !!!\n";
	echo "Setting portal name in all folders ... 3/9 \n";
	changeWordInDirectory ( path, '#MainProject', $Portal_name );
	changeWordInDirectory ( path . "/build.properties", '#PORTAL_NAME', strtolower ( $Portal_name ) );
	changeWordInDirectory ( path . "/build.properties", '#PORTAL_VERSION', $result_array ['portal_version'] );
	moveDirectory ( path, './target/' . strtolower ( $Portal_name ) . '_catalogue' );
	eraseDirectory ( './target/' . strtolower ( $Portal_name ) . '_catalogue' . '/project-directory-template' );
	exec ( 'mkdir ./target/database');
	exec ( 'chmod -R 777 target' );
	//exec ( 'chown -R '.$apacheConf['user'].':'.$apacheConf['group'].' target' );
	// Database creation
	echo "Creating portal databases ... 4/9 \n";
	if (isset ( $database ) && ! empty ( $database ))
		duplicateDatabase ( $database );
	// Ldap files generation
	echo "Generating ldap config and creation files ... 5/9 \n";
	generateLdapConfigFiles ( $ldapProjects );
	echo "Generating apache configurationfile ... 6/9 \n";
	generateConfdFile ( $result_array ['dns'], $app_path );
	echo "Generating extraction filter file ... 7/9 \n";
	generateExtractFilter ();
	echo "Generating data extractor... 8/9 \n";
	generateExtractor ();
	echo "Generating backup files... 9/9 \n";
	generateBackupFiles();
	exec ( 'chmod -R 777 target/backup' );
	//exec ( 'chown -R '.$apacheConf['user'].':'.$apacheConf['group'].' target/backup' );
	echo "Done !!! \n";
	exec ( 'chmod -R 777 /export1/data_local/log' );
	exec ( 'chown -R '.$apacheConf['user'].':'.$apacheConf['group'].' /export1/data_local/log' );
}
?>
