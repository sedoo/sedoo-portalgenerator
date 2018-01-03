<?php
include ('config.php');
include ("ldapIds.php");
class PortalGenerator {
	var $templateDir = './template_catalogue';
	var $extractDir = './extracteur';
	var $inputDir = './input';

	var $targetDir = './target';
	var $phpTargetDir; 			// ./target/catalogue
	var $apacheTargetDir;		// ./target/apache
	var $databaseTargetDir;		// ./target/database
	var $ldapTargetDir;			// ./target/ldap
	var $ftpTargetDir;			// ./target/ftp
	var $extractTargetDir;		// ./target/extraction
	var $backupTargetDir;		// ./target/backup

	// chemin vers le modèle XML
	var $xmlSchema = './input/projet-template.xsd'; 
	// tableau associatif donnant accès aux valeur du fichier XML : $this->xmlContent['balise']
	var $xmlContent;								
	var $projects = array (); 		// plus utilisé
	var $subProjects = array (); 	// plus utilisé
	var $ldapProjects = array (); 	
	var $portalName;
	var $options = array (); 	// tableau d'options passées en argument à l'exécution du portalGenerator
	var $conf = array (); 		// tableau de confs définies dans config.php
	
	var $rootPath; 		// /sites
	var $dataPath; 		// /sites/data
	var $depotPath; 	// /sites/depot
	var $backupPath; 	// /sites/backup
	var $workPath; 		// /sites/work
	var $dlPath; 		// /sites/work/dl
	var $logPath; 		// /sites/work/log
	var $attFilesPath;	// /sites/work/attached
	var $mapsPath;		// /sites/work/maps
	var $webPath;		// /sites/catalogue
	
	function __construct($conf, $options = array()) {
		$this->options = $options;
		$this->conf = $conf;
	}
	function clean() {
		echo "Clean...\n";
		DirUtils::rmDirectory ( $this->targetDir );
	}
	
	private function copyTemplateCatalogue() {
		echo "Copying catalogue...\n";
		$this->phpTargetDir = "$this->targetDir/" . 'catalogue';
		
		DirUtils::copyDirectory ( $this->templateDir, $this->phpTargetDir );
		// creation des répertoire att_img et graphs
		if (! file_exists ( $this->phpTargetDir . '/att_img' )) {
			mkdir ( $this->phpTargetDir . '/att_img' );
			exec ( 'chmod -R 777 ' . $this->phpTargetDir . '/att_img' );
		}
		if (! file_exists ( $this->phpTargetDir . '/graphs' )) {
			mkdir ( $this->phpTargetDir . '/graphs' );
			exec ( 'chmod -R 777 ' . $this->phpTargetDir . '/graphs' );
		}
	}
	/*
	private function createProjectsDirectories() {
		echo "Creating projects directories...\n";
		if (isset ( $this->projects ) && ! empty ( $this->projects )) {
			foreach ( $this->projects as $project ) {
				if (isset ( $project ) && ! empty ( $project )) {
					if (file_exists ( $this->phpTargetDir . '/' . $project )) {
						DirUtils::removeDirectory ( $this->phpTargetDir . '/' . $project );
					}
					DirUtils::copyDirectory ( $this->phpTargetDir . '/project-directory-template', $this->phpTargetDir . '/' . $project );
					DirUtils::changeWordInDirectory ( $this->phpTargetDir . '/' . $project, '#project', $project );
					if (isset ( $this->subProjects [$project] ) && ! empty ( $this->subProjects [$project] )) {
						foreach ( $this->subProjects [$project] as $subProj ) {
							$SP = str_replace ( ' ', '-', $subProj );
							DirUtils::copyDirectory ( $this->phpTargetDir . '/project-directory-template/subproject-directory-template', $this->phpTargetDir . '/' . $project . '/' . $SP );
							DirUtils::changeWordInDirectory ( $this->phpTargetDir . '/' . $project . '/' . $SP, '#subproject', $subProj );
							DirUtils::changeWordInDirectory ( $this->phpTargetDir . '/' . $project . '/' . $SP, '#project', $project );
						}
					}
				}
			}
		}
	}*/
	function make($xmlFilePath) {
		$this->clean ();
		if (XmlUtils::validateXml ( $xmlFilePath, $this->xmlSchema )) {
						
			echo "Parsing xml file...\n";
			$xml = simplexml_load_file ( $xmlFilePath );
			$json_string = json_encode ( $xml );
			$this->xmlContent = json_decode ( $json_string, TRUE );
			$this->portalName = $this->xmlContent ['name'];
			$this->rootPath = $this->xmlContent ['path'];
			$this->workPath = $this->rootPath . '/work';
			$this->logPath = $this->workPath . '/log';
			$this->dlPath = $this->workPath . '/dl';
			$this->mapsPath = $this->workPath . '/maps';
			$this->attFilesPath = $this->workPath . '/attached';
			$this->dataPath = $this->rootPath . '/data';
			$this->depotPath = $this->rootPath . '/depot';
			$this->backupPath = $this->rootPath . '/backup';
			$this->webPath = $this->rootPath . '/catalogue';
			
			if (! file_exists ( $this->targetDir )) {
				exec ( "mkdir $this->targetDir" );
			}
			
			// PHP
			if (in_array ( '--skip-php', $this->options ) === false) {
				$this->copyTemplateCatalogue ();
				$this->makeConfPhp ();
				// $this->createProjectsDirectories ();
				echo "Setting portal name in all folders...\n";
				DirUtils::changeWordInDirectory ( $this->phpTargetDir, '#MainProject', $this->portalName );
				echo "Renaming folder...\n";
				// DirUtils::rmDirectory ( $this->phpTargetDir . '/project-directory-template' );
			} else {
				echo "Skip php\n";
			}
			
			if (in_array ( '--skip-ldap', $this->options ) === false) {
				$this->ldapTargetDir = "$this->targetDir/ldap";
				if (! file_exists ( $this->ldapTargetDir )) {
					exec ( "mkdir $this->ldapTargetDir" );
				}
				// $this->readLdapProjects();
				$this->makeLdap ();
			} else {
				echo "Skip LDAP\n";
			}
			
			if (in_array ( '--skip-apache', $this->options ) === false) {
				$this->apacheTargetDir = "$this->targetDir/apache";
				if (! file_exists ( $this->apacheTargetDir )) {
					exec ( "mkdir $this->apacheTargetDir" );
				}
				$this->makeApache ();
			} else {
				echo "Skip Apache\n";
			}

			if (in_array ( '--skip-database', $this->options ) === false) {
				$this->databaseTargetDir = "$this->targetDir/database";
				if (! file_exists ( $this->databaseTargetDir )) {
					exec ( "mkdir $this->databaseTargetDir" );
				}
				$this->makeDatabase ();
			} else {
				echo "Skip Database\n";
			}
			
			if (in_array ( '--skip-extract', $this->options ) === false) {
				$this->extractTargetDir = "$this->targetDir/extraction";
				if (! file_exists ( $this->extractTargetDir )) {
					exec ( "mkdir $this->extractTargetDir" );
				}
				$this->makeExtract ();
			} else {
				echo "Skip extractor\n";
			}
			
			if (in_array ( '--skip-backup', $this->options ) === false) {
				$this->backupTargetDir = "$this->targetDir/backup";
				if (! file_exists ( $this->backupTargetDir )) {
					exec ( "mkdir $this->backupTargetDir" );
				}
				$this->makeBackup ();
			} else {
				echo "Skip Backup\n";
			}
			
			if (in_array ( '--skip-ftp', $this->options ) === false) {
				$this->ftpTargetDir = "$this->targetDir/ftp";
				if (! file_exists ( $this->ftpTargetDir )) {
					exec ( "mkdir $this->ftpTargetDir" );
				}
				$this->makeFtp ();
			}else {
				echo "Skip FTP\n";
			}
			
			$this->makeInstallScript();
		}
	}
	
	function makeFtp(){
		
	}
	
	function makeInstallScript(){
		$content = "#! /bin/sh \n\n";
	
		$content .= "set -x\n\n";
		
		$content .= "mkdir -p $this->dlPath\n";
		$content .= "chmod -R 777 $this->dlPath\n";
		$content .= "mkdir -p $this->logPath\n";
		$content .= "chmod -R 777 $this->logPath\n";
		$content .= "mkdir -p $this->mapsPath\n";
		$content .= "mkdir -p $this->dataPath\n";
		$content .= "mkdir -p $this->attFilesPath\n";
		$content .= "chmod -R 777 $this->attFilesPath\n";
		$content .= "mkdir -p $this->backupPath\n\n";
	
		if (in_array ( '--skip-extract', $this->options ) === false) {
			$content .= "unzip -d $this->rootPath extraction/extracteur-install.zip\n";
		}
						
		if (in_array ( '--skip-database', $this->options ) === false) {
			$content .= "cd database\n";
			$content .= "./createDb.sh\n";
			$content .= "cd ..\n\n";
		}
						
		if (in_array ( '--skip-backup', $this->options ) === false) {
			
		}
				
		if (in_array ( '--skip-php', $this->options ) === false) {
			$content .= "mkdir -p $this->rootPath" . "/projects/" . $this->xmlContent['name'] . "/conf \n"; //modif
			$content .= "mv catalogue/conf/conf.php " . "$this->rootPath" . "/projects/" . $this->xmlContent['name'] . "/conf \n"; //modif
			$content .= "mv " . "catalogue $this->rootPath \n"; //modif
		}
	
		if (in_array ( '--skip-apache', $this->options ) === false) {
			$content .= "mv apache/" . strtolower ( $this->portalName ). ".conf " . $this->conf['apache']['confDir'] . "\n";
			$content .= "service " . $this->conf['apache']['service'] . " reload\n\n";
		}
		
		$content .= "chown -R " . $this->conf['apache']['user'] . '.' . $this->conf['apache']['group'] . " $this->rootPath\n\n";
		
		if (in_array ( '--skip-ldap', $this->options ) === false) {
			$content .= "cd ldap\n";
			$content .= 'php ' . strtolower ( $this->portalName ) . 'LdapCreationScript.php' ."\n";
			$content .= "cd ..\n\n";
		}
		
		if (in_array ( '--skip-ftp', $this->options ) === false) {
			$content .= "mkdir -p $this->depotPath\n";
		}
		
		$this->generateFile ( $this->targetDir . '/install.sh', $content);
		exec ("chmod a+x $this->targetDir/install.sh");
	}
	
	function makeBackup(){
		echo "Generating backup script...\n";
		exec("cp $this->inputDir/backup/backup.sh $this->backupTargetDir");
		DirUtils::changeWordInDirectory($this->backupTargetDir, '#MainProject', strtolower($this->portalName));
		DirUtils::changeWordInDirectory($this->backupTargetDir, '#PortalPath', $this->rootPath);
	}
	
	function makeDatabase(){
		echo "Generating database creation script...\n";
		exec("cp $this->inputDir/database/*.sql $this->databaseTargetDir");
		
		DirUtils::changeWordInDirectory($this->databaseTargetDir, '#MainProject', strtolower($this->portalName));
		DirUtils::changeWordInDirectory($this->databaseTargetDir, '#ProjectName', $this->portalName);
		DirUtils::changeWordInDirectory($this->databaseTargetDir, '#ProjectUrl', $this->xmlContent ['website']);
		
		$content = "#! /bin/sh \n\n";
		$content .= 'export PGHOST=' . $this->xmlContent ['database'] ['host'] . "\n";
		$content .= 'export PGDATABASE=' . $this->xmlContent ['database'] ['name'] . "\n";
		$content .= 'export PGUSER=' . $this->xmlContent ['database'] ['user'] . "\n";
		$content .= 'export PGPASSWORD=' . $this->xmlContent ['database'] ['password'] . "\n";
		
		$content .= "\n\nif psql -lqt | cut -d \| -f 1 | grep -qw " . $this->xmlContent ['database'] ['name'] . "; then\n";
		$content .= "# $? is 0\n";
		$content .= 'echo "Database already exists\n"' . "\n";
		$content .= "else\n";
		$content .= "#$? is 1\n";
		$content .= "createdb " . $this->xmlContent ['database'] ['name'] . "\n";
		$content .= "psql -f portalSchema.sql\n";
		$content .= "psql -f initMetadataTables.sql\n";
		$content .= "psql -f initProjects.sql\n";
		$content .= "psql -f initRoles.sql\n";
		$content .= "fi\n";
				
		$this->generateFile ( $this->databaseTargetDir . '/createDb.sh', $content);
		exec ("chmod a+x $this->databaseTargetDir/createDb.sh");
	}

	function makeConfPhp() {
		echo "Generating conf.php file...\n";
		$filePath = $this->phpTargetDir . "/conf/conf.php";
		$file = fopen ( $filePath, "w" ) or die ( "Unable to open conf.php file!" );
		
		$content = "<?php \n\n";
		
		// Portal informations
		$content .= $this->comment ( "Variables du portail " . $this->xmlContent ['name'] );
		$content .= $this->comment ( "Nom du Portail ou projet principal" );
		if (isset ( $this->xmlContent ['name'] ) && ! empty ( $this->xmlContent ['name'] ))
			$content .= "define('MainProject','" . $this->xmlContent ['name'] . "');\n";
		else
			$content .= "define('MainProject','');\n";
		$content .= $this->comment ( "site web du portail" );
		if (isset ( $this->xmlContent ['website'] ) && ! empty ( $this->xmlContent ['website'] ))
			$content .= "define('PORTAL_WebSite','" . $this->xmlContent ['website'] . "');\n";
		else
			$content .= "define('PORTAL_WebSite','');\n";
		$content .= $this->comment ( "Le compte google analytic du portail" );
		if (isset ( $this->xmlContent ['googleAnalyticAccount'] ) && ! empty ( $this->xmlContent ['googleAnalyticAccount'] ))
			$content .= "define('PortalGoogleAnalytic','" . $this->xmlContent ['googleAnalyticAccount'] . "');\n";
		else
			$content .= "define('PortalGoogleAnalytic','');\n";
		$content .= $this->comment ( "Répertoire pour les dépots ftp" );
		
		$content .= "define('PORTAL_DEPOT','" . $this->depotPath . "');\n";
		
		$content .= $this->comment ( "Favicon du portail" );
		if (isset ( $this->xmlContent ['faviconPath'] ) && ! empty ( $this->xmlContent ['faviconPath'] ))
			$content .= "define('FavIcon','" . $this->xmlContent ['faviconPath'] . "');\n";
		else
			$content .= "define('FavIcon','');\n";
		$content .= $this->comment ( "Hauteur de la bannière" );
		if (isset ( $this->xmlContent ['bannerHeight'] ) && ! empty ( $this->xmlContent ['bannerHeight'] ))
			$content .= "define('PORTAL_BannerHeight','" . $this->xmlContent ['bannerHeight'] . "');\n";
		else
			$content .= "define('PORTAL_BannerHeight','');\n";
		$content .= $this->comment ( "Affichage ou pas du nom du portail" );
		if (isset ( $this->xmlContent ['displayBannerTitle'] ) && ! empty ( $this->xmlContent ['displayBannerTitle'] ))
			$content .= "define('PORTAL_DisplayBannerTitle','" . $this->xmlContent ['displayBannerTitle'] . "');\n";
		else
			$content .= "define('PORTAL_DisplayBannerTitle','');\n";
		$content .= $this->comment ( "Affichage ou pas du logo du portail dans la bannière" );
		if (isset ( $this->xmlContent ['displayLogoOnBanner'] ) && ! empty ( $this->xmlContent ['displayLogoOnBanner'] ))
			$content .= "define('PORTAL_DisplayLogoOnBanner','" . $this->xmlContent ['displayLogoOnBanner'] . "');\n";
		else
			$content .= "define('PORTAL_DisplayLogoOnBanner','');\n";
		$content .= $this->comment ( "Logo du portail" );
		if (isset ( $this->xmlContent ['logoPath'] ) && ! empty ( $this->xmlContent ['logoPath'] ))
			$content .= "define('PORTAL_LogoPath','" . $this->xmlContent ['logoPath'] . "');\n";
		else
			$content .= "define('PORTAL_LogoPath','');\n";
		$content .= $this->comment ( "La barre du haut du portail" );
		if (isset ( $this->xmlContent ['topbarPath'] ) && ! empty ( $this->xmlContent ['topbarPath'] ))
			$content .= "define('PORTAL_TopBarPath','" . $this->xmlContent ['topbarPath'] . "');\n";
		else
			$content .= "define('PORTAL_TopBarPath','');\n";
		$content .= $this->comment ( "Les paramètres du pied du portail" );
		if (isset ( $this->xmlContent ['footerTextPeriod'] ) && ! empty ( $this->xmlContent ['footerTextPeriod'] ))
			$content .= "define('PORTAL_FooterTextPeriod','" . $this->xmlContent ['footerTextPeriod'] . "');\n";
		else
			$content .= "define('PORTAL_FooterTextPeriod','');\n";
		if (isset ( $this->xmlContent ['footerTextDeveloper'] ) && ! empty ( $this->xmlContent ['footerTextDeveloper'] ))
			$content .= "define('PORTAL_FooterTextDeveloper','" . $this->xmlContent ['footerTextDeveloper'] . "');\n";
		else
			$content .= "define('PORTAL_FooterTextDeveloper','');\n";
		if (isset ( $this->xmlContent ['footerTextDeveloperWebsite'] ) && ! empty ( $this->xmlContent ['footerTextDeveloperWebsite'] ))
			$content .= "define('PORTAL_FooterTextDeveloperWebsite','" . $this->xmlContent ['footerTextDeveloperWebsite'] . "');\n";
		else
			$content .= "define('PORTAL_FooterTextDeveloperWebsite','');\n";
		$content .= "define('ATT_IMG_URL_PATH','/att_img/');\n";
		$content .= $this->comment ( "Répertoire où est placée la doc associée aux données d'un jeu." );
		$content .= $this->comment ( "Il est ajouté au résultat de toutes les requetes concernant le jeu." );
		$content .= "define('DOC_DIR','0_Documentation');\n";
		$content .= "define('README_FILE','README');\n";
		
		$content .= $this->comment ( "Répertoire des données" );
		$content .= "define('DATA_PATH', '" . $this->dataPath . "' );\n";
				
		$content .= "define('portalWorkPath','" . $this->workPath . "');\n";
		$content .= $this->comment ( "Répertoire où sont placés les fichiers à télécharger" );
		$content .= "define('DATA_PATH_DL','" . $this->dlPath . "');\n";
		$content .= $this->comment ( "Fichier log téléchargement" );
		$content .= "define('LOG_DL','" . $this->logPath . "/dl.log');\n";
		$content .= $this->comment ( "Fichier de log utilisé par logger.php (log des requetes sql)" );
		$content .= "define('LOG_FILE','" . $this->logPath . "/catalogue.log');\n";
		$content .= $this->comment ( "Répertoire où sont les fichiers permettant de générer les cartes (liste des points à afficher)" );
		$content .= "define('MAP_PATH','" . $this->mapsPath . "');\n";
		$content .= $this->comment ( "Répertoires des images et fichiers attachés" );
		$content .= "define('ATT_FILES_PATH','" . $this->attFilesPath . "');\n";
		
		date_default_timezone_set('UTC');
		$content .= "define('STATS_DEFAULT_MIN_YEAR', " . date ('Y') . ");\n";
				
		$content .= $this->comment ( "répertoire du site web" );
		$content .= "define('WEB_PATH','" . $this->webPath . "');\n";
		
		$content .= $this->comment ( "//téléchargements des jeux insérés" );
		
		if (isset ( $this->xmlContent ['dns'] ) && ! empty ( $this->xmlContent ['dns'] )) {
			$content .= "define('EXTRACT_CGI', '/extract/cgi-bin/extract.cgi');\n";
			$content .= "define('EXTRACT_CGI_FICHIERS', '/extract/cgi-bin/extractFiles.cgi');\n";
		} else {
			$content .= "define('EXTRACT_CGI','');\n";
			$content .= "define('EXTRACT_CGI_FICHIERS','');\n";
		}

		$content .= "define('EXTRACT_RESULT_PATH','" . $this->dlPath . "');\n";

		if (isset ( $this->xmlContent ['extractInformPi'] ) && ! empty ( $this->xmlContent ['extractInformPi'] ))
			$content .= "define('EXTRACT_INFORM_PI','" . $this->xmlContent ['extractInformPi'] . "');\n";
		else
			$content .= "define('EXTRACT_INFORM_PI','');\n";
		
		$content .= $this->comment ( "Role public pour les données" );
		if (isset ( $this->xmlContent ['publicDataRole'] ) && ! empty ( $this->xmlContent ['publicDataRole'] ))
			$content .= "define('PUBLIC_DATA_ROLE','" . $this->xmlContent ['publicDataRole'] . "');\n";
		else
			$content .= "define('PUBLIC_DATA_ROLE','');\n";
		$content .= $this->comment ( "Répertoire de l'outil permettant la conversion html/pdf" );
		
		if (isset ( $this->xmlContent ['wkhtmlBinPath'] ) && ! empty ( $this->xmlContent ['wkhtmlBinPath'] ))
			$content .= "define('WKHTML_BIN_PATH','" . $this->xmlContent ['wkhtmlBinPath'] . "');\n";
		else
			$content .= "define('WKHTML_BIN_PATH','');\n";
		
		$content .= $this->comment ( "Prefixe pour les tests : 10.5072/" );
		
		if (isset ( $this->xmlContent ['doiPrefix'] ) && ! empty ( $this->xmlContent ['doiPrefix'] ))
			$content .= "define('DOI_PREFIX','" . $this->xmlContent ['doiPrefix'] . "');\n";
		else
			$content .= "define('DOI_PREFIX','');\n";
		$content .= $this->comment ( "Si le portail en mode test" );
		
		if (isset ( $this->xmlContent ['testMode'] ) && ! empty ( $this->xmlContent ['testMode'] ))
			$content .= "define('TEST_MODE','" . $this->xmlContent ['testMode'] . "');\n";
		else
			$content .= "define('TEST_MODE','');\n";
		
		$content .= $this->comment ( "Site FTP du portail (ex: ftp://sedoo.fr)" );
		if (isset ( $this->xmlContent ['ftp'] ) && ! empty ( $this->xmlContent ['ftp'] ))
			$content .= "define('Portal_FTP_Site','" . $this->xmlContent ['ftp'] . "');\n";
		else
			$content .= "define('Portal_FTP_Site','');\n";

		$content .= $this->comment ( "L'adresse mail du groupe admin (ex mistralsAdmins@sedoo.fr)" );
		if (isset ( $this->xmlContent ['rootEmail'] ) && ! empty ( $this->xmlContent ['rootEmail'] ))
			$content .= "define('ROOT_EMAIL','" . $this->xmlContent ['rootEmail'] . "');\n";
		else
			$content .= "define('ROOT_EMAIL','');\n";
		$content .= $this->comment ( "Email de contact du portail" );
		if (isset ( $this->xmlContent ['contactEmail'] ) && ! empty ( $this->xmlContent ['contactEmail'] ))
			$content .= "define('Portal_Contact_Email','" . $this->xmlContent ['contactEmail'] . "');\n";
		else
			$content .= "define('Portal_Contact_Email','');\n";	
		$content .= $this->comment ( "Datapolicy du portail" );
		if (isset ( $this->xmlContent ['datapolicy'] ) && ! empty ( $this->xmlContent ['datapolicy'] ))
			$content .= "define('Portal_DataPolicy','" . $this->xmlContent ['datapolicy'] . "');\n";
		else
			$content .= "define('Portal_DataPolicy','');\n";
		$content .= $this->comment ( "Paramètres pour la connexion avec la BDD" );
		
		// Roles
		if (isset( $this->xmlContent ['roles']) ) {
			$roles = null;
			$m = 0;
			foreach ( $this->xmlContent ['roles'] ['role'] as $role ) {
				$m ++;
				$roles .= $role;
				if ($m < count ( $this->xmlContent ['roles'] ['role'] )) {
					$roles .= ",";
				}
			}
			$content .= $this->comment ( "Liste des roles pour le portail" );
			if (isset ( $roles ) && ! empty ( $roles ))
				$content .= "define('" . strtolower ( $this->xmlContent ['name'] ) . "ListRoles','" . $roles . "');\n";
			else
				$content .= "define('" . strtolower ( $this->xmlContent ['name'] ) . "ListRoles','');\n";
		}
		
		$content .= $this->comment ( "Année et mois du début du portail" );
		if (isset ( $this->xmlContent ['yearStart'] ) && ! empty ( $this->xmlContent ['yearStart'] ))
			$content .= "define('" . strtolower ( $this->xmlContent ['name'] ) . "yDeb','" . $this->xmlContent ['yearStart'] . "');\n";
		else
			$content .= "define('" . strtolower ( $this->xmlContent ['name'] ) . "yDeb','');\n";
					
			// database
		
		if (isset ( $this->xmlContent ['database'] ['host'] ) && ! empty ( $this->xmlContent ['database'] ['host'] ))
			$content .= "define('DB_HOST','" . $this->xmlContent ['database'] ['host'] . "');\n";
		else
			$content .= "define('DB_HOST','');\n";
		if (isset ( $this->xmlContent ['database'] ['user'] ) && ! empty ( $this->xmlContent ['database'] ['user'] ))
			$content .= "define('DB_USER','" . $this->xmlContent ['database'] ['user'] . "');\n";
		else
			$content .= "define('DB_USER','');\n";
		if (isset ( $this->xmlContent ['database'] ['name'] ) && ! empty ( $this->xmlContent ['database'] ['name'] ))
			$content .= "define('DB_NAME','" . $this->xmlContent ['database'] ['name'] . "');\n";
		else
			$content .= "define('DB_NAME','');\n";
		
		if (isset ( $this->xmlContent ['database'] ['password'] ) && ! empty ( $this->xmlContent ['database'] ['password'] ))
			$content .= "define('DB_PASS','" . $this->xmlContent ['database'] ['password'] . "');\n";
		else
			$content .= "define('DB_PASS','');\n";
		
		$content .= $this->comment ( "//Paramètres pour la connexion avec LDAP" );
		// ldap
		if (isset ( $this->xmlContent ['ldap'] ['host'] ) && ! empty ( $this->xmlContent ['ldap'] ['host'] ))
			$content .= "define('LDAP_HOST','" . $this->xmlContent ['ldap'] ['host'] . "');\n";
		else
			$content .= "define('LDAP_HOST','');\n";
		if (isset ( $this->xmlContent ['ldap'] ['port'] ) && ! empty ( $this->xmlContent ['ldap'] ['port'] ))
			$content .= "define('LDAP_PORT','" . $this->xmlContent ['ldap'] ['port'] . "');\n";
		else
			$content .= "define('LDAP_PORT','');\n";
		if (isset ( $this->xmlContent ['ldap'] ['base'] ) && ! empty ( $this->xmlContent ['ldap'] ['base'] ))
			$content .= "define('LDAP_BASE','" . $this->xmlContent ['ldap'] ['base'] . "');\n";
		else
			$content .= "define('LDAP_BASE','');\n";
		if (isset ( $this->xmlContent ['ldap'] ['dn'] ) && ! empty ( $this->xmlContent ['ldap'] ['dn'] ))
			$content .= "define('LDAP_DN','" . $this->xmlContent ['ldap'] ['dn'] . "');\n";
		else
			$content .= "define('LDAP_DN','');\n";
		if (isset ( $this->xmlContent ['ldap'] ['password'] ) && ! empty ( $this->xmlContent ['ldap'] ['password'] ))
			$content .= "define('LDAP_PASSWD','" . $this->xmlContent ['ldap'] ['password'] . "');\n";
		else
			$content .= "define('LDAP_PASSWD','');\n";
			
			// elastic
		if (isset ( $this->xmlContent ['elastic'] ['host'] ) && ! empty ( $this->xmlContent ['elastic'] ['host'] ))
			$content .= "define('ELASTIC_HOST','" . $this->xmlContent ['elastic'] ['host'] . "');\n";
		else
			$content .= "define('ELASTIC_HOST','');\n";
		if (isset ( $this->xmlContent ['elastic'] ['index'] ) && ! empty ( $this->xmlContent ['elastic'] ['index'] ))
			$content .= "define('ELASTIC_INDEX','" . $this->xmlContent ['elastic'] ['index'] . "');\n";
		else
			$content .= "define('ELASTIC_INDEX','');\n";
		
		$content .= $this->comment ( "Nombre de chartes à signer" );
		// datapolicy
		if (isset ( $this->xmlContent ['signDatapolicies'] ['signDatapolicy'] ) && ! empty ( $this->xmlContent ['signDatapolicies'] ['signDatapolicy'] ))
			$content .= "define('PortalNbSignDataPolicy','" . count ( $this->xmlContent ['signDatapolicies'] ['signDatapolicy'] ) . "');\n";
		else
			$content .= "define('PortalNbSignDataPolicy','');\n";
		$i = 0;
		$content .= $this->comment ( "Chartes de la datapolicy à signer" );
		foreach ( $this->xmlContent ['signDatapolicies'] ['signDatapolicy'] as $dp ) {
			if (isset ( $dp ) && ! empty ( $dp ))
				$content .= "define('PortalSignDataPolicy" . $i . "','" . $dp . "');\n";
			else
				$content .= "define('PortalSignDataPolicy" . $i . "','');\n";
			$i ++;
		}
		$content .= $this->comment ( "Le code de la page d'acceuil : 0 si c'est une page d'acceuil normale, et de 1 à 7 pour afficher selon les critères de recherche disponibles (même ordre fichier xml)" );
		if (isset ( $this->xmlContent ['homePage'] ) && ! empty ( $this->xmlContent ['homePage'] ))
			$content .= "define('" . strtolower ( $this->xmlContent ['name'] ) . "_HomePage','" . $this->xmlContent ['homePage'] . "');\n";
		else
			$content .= "define('" . strtolower ( $this->xmlContent ['name'] ) . "_HomePage','0');\n";
		
		/**
		 * **************************************************************************************************************************
		 * MENU DE GAUCHE
		 */
		
		$content .= $this->comment ( "Menu du gauche pour le projet, la valeur des paramètres doit être true ou false" );
		// parameters
		$param = $this->xmlContent ['parameters'];
		if (isset ( $param ['HasRequestData'] ) && ! empty ( $param ['HasRequestData'] ))
			$content .= "define('" . strtolower ( $this->xmlContent ['name'] ) . "_HasRequestData','" . $param ['HasRequestData'] . "');\n";
		else
			$content .= "define('" . strtolower ( $this->xmlContent ['name'] ) . "_HasRequestData','');\n";
		if (isset ( $param ['HasProvideData'] ) && ! empty ( $param ['HasProvideData'] ))
			$content .= "define('" . strtolower ( $this->xmlContent ['name'] ) . "_HasProvideData','" . $param ['HasProvideData'] . "');\n";
		else
			$content .= "define('" . strtolower ( $this->xmlContent ['name'] ) . "_HasProvideData','');\n";
		if (isset ( $param ['HasAdminCorner'] ) && ! empty ( $param ['HasAdminCorner'] ))
			$content .= "define('" . strtolower ( $this->xmlContent ['name'] ) . "_HasAdminCorner','" . $param ['HasAdminCorner'] . "');\n";
		else
			$content .= "define('" . strtolower ( $this->xmlContent ['name'] ) . "_HasAdminCorner','');\n";
		if (isset ( $param ['HasParameterSearch'] ) && ! empty ( $param ['HasParameterSearch'] ))
			$content .= "define('" . strtolower ( $this->xmlContent ['name'] ) . "_HasParameterSearch','" . $param ['HasParameterSearch'] . "');\n";
		else
			$content .= "define('" . strtolower ( $this->xmlContent ['name'] ) . "_HasParameterSearch','');\n";
		if (isset ( $param ['HasInstrumentSearch'] ) && ! empty ( $param ['HasInstrumentSearch'] ))
			$content .= "define('" . strtolower ( $this->xmlContent ['name'] ) . "_HasInstrumentSearch','" . $param ['HasInstrumentSearch'] . "');\n";
		else
			$content .= "define('" . strtolower ( $this->xmlContent ['name'] ) . "_HasInstrumentSearch','');\n";
		if (isset ( $param ['HasCountrySearch'] ) && ! empty ( $param ['HasCountrySearch'] ))
			$content .= "define('" . strtolower ( $this->xmlContent ['name'] ) . "_HasCountrySearch','" . $param ['HasCountrySearch'] . "');\n";
		else
			$content .= "define('" . strtolower ( $this->xmlContent ['name'] ) . "_HasCountrySearch','');\n";
		if (isset ( $param ['HasPlatformSearch'] ) && ! empty ( $param ['HasPlatformSearch'] ))
			$content .= "define('" . strtolower ( $this->xmlContent ['name'] ) . "_HasPlatformSearch','" . $param ['HasPlatformSearch'] . "');\n";
		else
			$content .= "define('" . strtolower ( $this->xmlContent ['name'] ) . "_HasPlatformSearch','');\n";
		if (isset ( $param ['HasProjectSearch'] ) && ! empty ( $param ['HasProjectSearch'] ))
			$content .= "define('" . strtolower ( $this->xmlContent ['name'] ) . "_HasProjectSearch','" . $param ['HasProjectSearch'] . "');\n";
		else
			$content .= "define('" . strtolower ( $this->xmlContent ['name'] ) . "_HasProjectSearch','');\n";
		if (isset ( $param ['HasEventSearch'] ) && ! empty ( $param ['HasEventSearch'] ))
			$content .= "define('" . strtolower ( $this->xmlContent ['name'] ) . "_HasEventSearch','" . $param ['HasEventSearch'] . "');\n";
		else
			$content .= "define('" . strtolower ( $this->xmlContent ['name'] ) . "_HasEventSearch','');\n";
		if (isset ( $param ['HasCampaignSearch'] ) && ! empty ( $param ['HasCampaignSearch'] ))
			$content .= "define('" . strtolower ( $this->xmlContent ['name'] ) . "_HasCampaignSearch','" . $param ['HasCampaignSearch'] . "');\n";
		else
			$content .= "define('" . strtolower ( $this->xmlContent ['name'] ) . "_HasCampaignSearch','');\n";
		if (isset ( $param ['HasModelRequest'] ) && ! empty ( $param ['HasModelRequest'] ))
			$content .= "define('" . strtolower ( $this->xmlContent ['name'] ) . "_HasModelRequest','" . $param ['HasModelRequest'] . "');\n";
		else
			$content .= "define('" . strtolower ( $this->xmlContent ['name'] ) . "_HasModelRequest','');\n";
		if (isset ( $param ['HasSatelliteRequest'] ) && ! empty ( $param ['HasSatelliteRequest'] ))
			$content .= "define('" . strtolower ( $this->xmlContent ['name'] ) . "_HasSatelliteRequest','" . $param ['HasSatelliteRequest'] . "');\n";
		else
			$content .= "define('" . strtolower ( $this->xmlContent ['name'] ) . "_HasSatelliteRequest','');\n";
		if (isset ( $param ['HasInsituRequest'] ) && ! empty ( $param ['HasInsituRequest'] ))
			$content .= "define('" . strtolower ( $this->xmlContent ['name'] ) . "_HasInsituRequest','" . $param ['HasInsituRequest'] . "');\n";
		else
			$content .= "define('" . strtolower ( $this->xmlContent ['name'] ) . "_HasInsituRequest','');\n";
		if (isset ( $param ['HasModelOutputs'] ) && ! empty ( $param ['HasModelOutputs'] ))
			$content .= "define('" . strtolower ( $this->xmlContent ['name'] ) . "_HasModelOutputs','" . $param ['HasModelOutputs'] . "');\n";
		else
			$content .= "define('" . strtolower ( $this->xmlContent ['name'] ) . "_HasModelOutputs','');\n";
		if (isset ( $param ['HasSatelliteProducts'] ) && ! empty ( $param ['HasSatelliteProducts'] ))
			$content .= "define('" . strtolower ( $this->xmlContent ['name'] ) . "_HasSatelliteProducts','" . $param ['HasSatelliteProducts'] . "');\n";
		else
			$content .= "define('" . strtolower ( $this->xmlContent ['name'] ) . "_HasSatelliteProducts','');\n";
		if (isset ( $param ['HasInsituProducts'] ) && ! empty ( $param ['HasInsituProducts'] ))
			$content .= "define('" . strtolower ( $this->xmlContent ['name'] ) . "_HasInsituProducts','" . $param ['HasInsituProducts'] . "');\n";
		else
			$content .= "define('" . strtolower ( $this->xmlContent ['name'] ) . "_HasInsituProducts','');\n";
		if (isset ( $param ['HasMultiInsituProducts'] ) && ! empty ( $param ['HasMultiInsituProducts'] ))
			$content .= "define('" . strtolower ( $this->xmlContent ['name'] ) . "_HasMultiInsituProducts','" . $param ['HasMultiInsituProducts'] . "');\n";
		else
			$content .= "define('" . strtolower ( $this->xmlContent ['name'] ) . "_HasMultiInsituProducts','');\n";
		if (isset ( $param ['HasValueAddedProducts'] ) && ! empty ( $param ['HasValueAddedProducts'] ))
			$content .= "define('" . strtolower ( $this->xmlContent ['name'] ) . "_HasValueAddedProducts','" . $param ['HasValueAddedProducts'] . "');\n";
		else
			$content .= "define('" . strtolower ( $this->xmlContent ['name'] ) . "_HasValueAddedProducts','');\n";
		$content .= $this->comment ( "Les chemins des images des partenaires dans la page d'acceuil" );
		if (isset ( $param ['HasAssociatedUsers'] ) && ! empty ( $param ['HasAssociatedUsers'] ))
			$content .= "define('" . strtolower ( $this->xmlContent ['name'] ) . "_HasAssociatedUsers','" . $param ['HasAssociatedUsers'] . "');\n";
		else
			$content .= "define('" . strtolower ( $this->xmlContent ['name'] ) . "_HasAssociatedUsers','');\n";
		$content .= $this->comment ( "Pour l'affichage ou pas des autres projets du portail dans le menu du haut comme pour Hymex" );
		if (isset ( $param ['DisplayOnlyProjectOnTopBar'] ) && ! empty ( $param ['DisplayOnlyProjectOnTopBar'] ))
			$content .= "define('" . strtolower ( $this->xmlContent ['name'] ) . "_DisplayOnlyProjectOnTopBar','" . $param ['DisplayOnlyProjectOnTopBar'] . "');\n";
		else
			$content .= "define('" . strtolower ( $this->xmlContent ['name'] ) . "_DisplayOnlyProjectOnTopBar','');\n";
		$content .= $this->comment ( "Les tags à afficher" );
		if (isset ( $param ['HasBlueTag'] ) && ! empty ( $param ['HasBlueTag'] ))
			$content .= "define('" . strtolower ( $this->xmlContent ['name'] ) . "_HasBlueTag','" . $param ['HasBlueTag'] . "');\n";
		else
			$content .= "define('" . strtolower ( $this->xmlContent ['name'] ) . "_HasBlueTag','');\n";
		if (isset ( $param ['HasPurpleTag'] ) && ! empty ( $param ['HasPurpleTag'] ))
			$content .= "define('" . strtolower ( $this->xmlContent ['name'] ) . "_HasPurpleTag','" . $param ['HasPurpleTag'] . "');\n";
		else
			$content .= "define('" . strtolower ( $this->xmlContent ['name'] ) . "_HasPurpleTag','');\n";
		if (isset ( $param ['HasOrangeTag'] ) && ! empty ( $param ['HasOrangeTag'] ))
			$content .= "define('" . strtolower ( $this->xmlContent ['name'] ) . "_HasOrangeTag','" . $param ['HasOrangeTag'] . "');\n";
		else
			$content .= "define('" . strtolower ( $this->xmlContent ['name'] ) . "_HasOrangeTag','');\n";
		if (isset ( $param ['HasGreenTag'] ) && ! empty ( $param ['HasGreenTag'] ))
			$content .= "define('" . strtolower ( $this->xmlContent ['name'] ) . "_HasGreenTag','" . $param ['HasGreenTag'] . "');\n";
		else
			$content .= "define('" . strtolower ( $this->xmlContent ['name'] ) . "_HasGreenTag','');\n";
		
		// if (! isset ( $this->xmlContent ["mainProjects"] ['project'] [0] ) && empty ( $this->xmlContent ["mainProjects"] ['project'] [0] )) {
		// 	$tab_mainProjects = $this->xmlContent ["mainProjects"] ['project'];
		// 	unset ( $this->xmlContent ["mainProjects"] ['project'] );
		// 	$this->xmlContent ["mainProjects"] ['project'] [0] = $tab_mainProjects;
		// }
		// if (! isset ( $this->xmlContent ["otherProjects"] ['project'] [0] ) && empty ( $this->xmlContent ["otherProjects"] ['project'] [0] )) {
		// 	$tab_otherProjects = $this->xmlContent ["otherProjects"] ['project'];
		// 	unset ( $this->xmlContent ["otherProjects"] ['project'] );
		// 	$this->xmlContent ["otherProjects"] ['project'] [0] = $tab_otherProjects;
		// }
		
		$content .= $this->comment ( "Paramètre de la carte dans la recherche avancée" );
		if (isset ( $this->xmlContent ['map'] ['MAP_DEFAULT_LAT_MIN'] ) && ! empty ( $this->xmlContent ['map'] ['MAP_DEFAULT_LAT_MIN'] ))
			$content .= "define('MAP_DEFAULT_LAT_MIN'," . $this->xmlContent ['map'] ['MAP_DEFAULT_LAT_MIN'] . ");\n";
		else
			$content .= "define('MAP_DEFAULT_LAT_MIN','');\n";
		if (isset ( $this->xmlContent ['map'] ['MAP_DEFAULT_LAT_MAX'] ) && ! empty ( $this->xmlContent ['map'] ['MAP_DEFAULT_LAT_MAX'] ))
			$content .= "define('MAP_DEFAULT_LAT_MAX'," . $this->xmlContent ['map'] ['MAP_DEFAULT_LAT_MAX'] . ");\n";
		else
			$content .= "define('MAP_DEFAULT_LAT_MAX','');\n";
		if (isset ( $this->xmlContent ['map'] ['MAP_DEFAULT_LON_MIN'] ) && ! empty ( $this->xmlContent ['map'] ['MAP_DEFAULT_LON_MIN'] ))
			$content .= "define('MAP_DEFAULT_LON_MIN'," . $this->xmlContent ['map'] ['MAP_DEFAULT_LON_MIN'] . ");\n";
		else
			$content .= "define('MAP_DEFAULT_LON_MIN','');\n";
		if (isset ( $this->xmlContent ['map'] ['MAP_DEFAULT_LON_MAX'] ) && ! empty ( $this->xmlContent ['map'] ['MAP_DEFAULT_LON_MAX'] ))
			$content .= "define('MAP_DEFAULT_LON_MAX'," . $this->xmlContent ['map'] ['MAP_DEFAULT_LON_MAX'] . ");\n";
		else
			$content .= "define('MAP_DEFAULT_LON_MAX','');\n";
			
		// Projects informations
		if (isset($this->xmlContent["mainProjects"]["project"]) || isset($this->xmlContent["otherProjects"]["project"])) {
			$j = 0;
			$compt = 0;
			$mainprojects = null;
			foreach ( array (
					$this->xmlContent ["mainProjects"],
					$this->xmlContent ["otherProjects"] 
			) as $Projects ) {
				foreach ( $Projects ['project'] as $proj ) {
					if (isset ( $proj ) && ! empty ( $proj )) {
						$j ++;
						if ($compt == 0)
							$mainprojects .= $proj ['name'];
						$content .= "\n";
						$content .= $this->comment ( "Paramètres de " . $proj ['name'] );
						$content .= $this->comment ( "Le compte google analytic de " . $proj ['name'] );
						if (isset ( $proj ['googleAnalyticAccount'] ) && ! empty ( $proj ['googleAnalyticAccount'] ))
							$content .= "define('" . strtolower ( $proj ['name'] ) . "GoogleAnalytic','" . $proj ['googleAnalyticAccount'] . "');\n";
						else
							$content .= "define('" . strtolower ( $proj ['name'] ) . "GoogleAnalytic','');\n";
						$content .= $this->comment ( "Les différents ID des sites que le projet peut avoir" );
						if (isset ( $proj ['sites'] ) && ! empty ( $proj ['sites'] ))
							$content .= "define('" . strtoupper ( $proj ['name'] ) . "_SITES','" . $proj ['sites'] . "');\n";
						else
							$content .= "define('" . strtoupper ( $proj ['name'] ) . "_SITES','');\n";
						$content .= $this->comment ( "Répertoires pour les dépots ftp" );
						if (isset ( $proj ['depot'] ) && ! empty ( $proj ['depot'] ))
							$content .= "define('" . strtoupper ( $proj ['name'] ) . "_DEPOT','" . $proj ['depot'] . "');\n";
						else
							$content .= "define('" . strtoupper ( $proj ['name'] ) . "_DEPOT','');\n";
						$content .= $this->comment ( "Hauteur de la bannière" );
						if (isset ( $proj ['bannerHeight'] ) && ! empty ( $proj ['bannerHeight'] ))
							$content .= "define('" . strtolower ( $proj ['name'] ) . "_BannerHeight','" . $proj ['bannerHeight'] . "');\n";
						else
							$content .= "define('" . strtolower ( $proj ['name'] ) . "_BannerHeight','');\n";
						$content .= $this->comment ( "Affichage ou pas du nom du projet dans la bannière" );
						if (isset ( $proj ['displayBannerTitle'] ) && ! empty ( $proj ['displayBannerTitle'] ))
							$content .= "define('" . strtolower ( $proj ['name'] ) . "_DisplayBannerTitle','" . $proj ['displayBannerTitle'] . "');\n";
						else
							$content .= "define('" . strtolower ( $proj ['name'] ) . "_DisplayBannerTitle','');\n";
						$content .= $this->comment ( "Affichage ou pas du logo du projet dans la bannière" );
						if (isset ( $proj ['displayLogoOnBanner'] ) && ! empty ( $proj ['displayLogoOnBanner'] ))
							$content .= "define('" . strtolower ( $proj ['name'] ) . "_DisplayLogoOnBanner','" . $proj ['displayLogoOnBanner'] . "');\n";
						else
							$content .= "define('" . strtolower ( $proj ['name'] ) . "_DisplayLogoOnBanner','');\n";
						$content .= $this->comment ( "Le chemin du logo du projet" );
						if (isset ( $proj ['logoPath'] ) && ! empty ( $proj ['logoPath'] ))
							$content .= "define('" . strtolower ( $proj ['name'] ) . "_LogoPath','" . $proj ['logoPath'] . "');\n";
						else
							$content .= "define('" . strtolower ( $proj ['name'] ) . "_LogoPath','');\n";
						$content .= $this->comment ( "La barre du haut pour le projet" );
						if (isset ( $proj ['topbarPath'] ) && ! empty ( $proj ['topbarPath'] ))
							$content .= "define('" . strtolower ( $proj ['name'] ) . "_TopBarPath','" . $proj ['topbarPath'] . "');\n";
						else
							$content .= "define('" . strtolower ( $proj ['name'] ) . "_TopBarPath','');\n";
						$content .= $this->comment ( "Année et mois du début du projet" );
						if (isset ( $proj ['yearStart'] ) && ! empty ( $proj ['yearStart'] ))
							$content .= "define('" . strtolower ( $proj ['name'] ) . "yDeb','" . $proj ['yearStart'] . "');\n";
						else
							$content .= "define('" . strtolower ( $proj ['name'] ) . "yDeb','');\n";
						
						$content .= $this->comment ( "Site web du projet s'il y en a " );
						if (isset ( $proj ['website'] ) && ! empty ( $proj ['website'] ))
							$content .= "define('" . strtolower ( $proj ['name'] ) . "WebSite','" . $proj ['website'] . "');\n";
						else
							$content .= "define('" . strtolower ( $proj ['name'] ) . "WebSite','');\n";

						$content .= $this->comment ( "Email de contact du responsable du projet" );
						if (isset ( $proj ['contactEmail'] ) && ! empty ( $proj ['contactEmail'] ))
							$content .= "define('" . strtolower ( $proj ['name'] ) . "Contact_Email','" . $proj ['contactEmail'] . "');\n";
						else
							$content .= "define('" . strtolower ( $proj ['name'] ) . "Contact_Email','');\n";
						
						$content .= $this->comment ( "Datapolicy générale du projet" );
						if (isset ( $proj ['datapolicy'] ) && ! empty ( $proj ['datapolicy'] )) {
							$content .= "define('" . strtolower ( $proj ['name'] ) . "DataPolicy','" . $proj ['datapolicy'] . "');\n";
						} else
							$content .= "define('" . strtolower ( $proj ['name'] ) . "DataPolicy','');\n";
						if ($j < count ( $this->xmlContent ['mainProjects'] ['project'] ) && $compt == 0) {
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
						$content .= $this->comment ( "Liste des roles pour chaque projet ('hymexCore','hymexAsso', ...)" );
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
						$content .= $this->comment ( "Les chemins des logos des partenaires dans la page d'accueil du projet" );
						if (isset ( $logos ) && ! empty ( $logos ))
							$content .= "define('" . strtolower ( $proj ['name'] ) . "_HomePageAssoLogosPath','" . $logos . "');\n";
						else
							$content .= "define('" . strtolower ( $proj ['name'] ) . "_HomePageAssoLogosPath','');\n";
						$logos = null;

						/*
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
						$content .= $this->comment ( "Sous projets du projet (Les projets qu'on affiche dans le menu du haut du portail)" );
						if (isset ( $subprojects ) && ! empty ( $subprojects ))
							$content .= "define('" . strtolower ( $proj ['name'] ) . "SubProjects','" . $subprojects . "');\n";
						else
							$content .= "define('" . strtolower ( $proj ['name'] ) . "SubProjects','');\n";
						if (isset ( $subprojects ) && ! empty ( $subprojects ))
							$this->subProjects [$proj ['name']] = explode ( ',', $subprojects );
						$subprojects = null;
						*/
						// datapolicy
						$content .= $this->comment ( "nombre de charte à signer pour le projet" );
						if (isset ( $proj ['signDatapolicies'] ['signDatapolicy'] ) && ! empty ( $proj ['signDatapolicies'] ['signDatapolicy'] ))
							$content .= "define('" . strtolower ( $proj ['name'] ) . "NbSignDataPolicy','" . count ( $proj ['signDatapolicies'] ['signDatapolicy'] ) . "');\n";
						else
							$content .= "define('" . strtolower ( $proj ['name'] ) . "NbSignDataPolicy','');\n";
						$content .= $this->comment ( "Chartes à signer pour le projet" );
						$i = 0;
						foreach ( $proj ['signDatapolicies'] ['signDatapolicy'] as $dp ) {
							if (isset ( $dp ) && ! empty ( $dp ))
								$content .= "define('" . strtolower ( $proj ['name'] ) . "SignDataPolicy" . $i . "','" . $dp . "');\n";
							else
								$content .= "define('" . strtolower ( $proj ['name'] ) . "SignDataPolicy" . $i . "','');\n";
							$i ++;
						}
						$content .= $this->comment ( "Le code de la page d'acceuil : 0 si c'est une page d'acceuil normale, et de 1 à 7 pour afficher selon les critères de recherche disponibles (même ordre fichier xml)" );
						if (isset ( $proj ['homePage'] ) && ! empty ( $proj ['homePage'] ))
							$content .= "define('" . strtolower ( $proj ['name'] ) . "_HomePage','" . $proj ['homePage'] . "');\n";
						else
							$content .= "define('" . strtolower ( $proj ['name'] ) . "_HomePage','0');\n";
						$content .= $this->comment ( "Menu du gauche pour le projet, la valeur des paramètres doit être true ou false" );
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
						$content .= $this->comment ( "Les chemins des images des partenaires dans la page d'acceuil" );
						if (isset ( $param ['HasAssociatedUsers'] ) && ! empty ( $param ['HasAssociatedUsers'] ))
							$content .= "define('" . strtolower ( $proj ['name'] ) . "_HasAssociatedUsers','" . $param ['HasAssociatedUsers'] . "');\n";
						else
							$content .= "define('" . strtolower ( $proj ['name'] ) . "_HasAssociatedUsers','');\n";
						$content .= $this->comment ( "Pour l'affichage ou pas des autres projets du portail dans le menu du haut comme pour Hymex" );
						if (isset ( $param ['DisplayOnlyProjectOnTopBar'] ) && ! empty ( $param ['DisplayOnlyProjectOnTopBar'] ))
							$content .= "define('" . strtolower ( $proj ['name'] ) . "_DisplayOnlyProjectOnTopBar','" . $param ['DisplayOnlyProjectOnTopBar'] . "');\n";
						else
							$content .= "define('" . strtolower ( $proj ['name'] ) . "_DisplayOnlyProjectOnTopBar','');\n";
						$content .= $this->comment ( "Les tags à afficher" );
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
		}
		/*
		$i = 0;
		foreach ( $this->xmlContent ['otherProjects'] ['project'] as $proj ) {
			$i ++;
			$otherprojects .= $proj ['name'];
			if ($i < count ( $this->xmlContent ['otherProjects'] ['project'] )) {
				$otherprojects .= ",";
			}
		}
		$content .= "\n";
		$content .= $this->comment ( "Les projets principaux du portail" );
		if (isset ( $mainprojects ) && ! empty ( $mainprojects ))
			$content .= "define('MainProjects','" . $mainprojects . "');\n";
		else
			$content .= "define('MainProjects','');\n";
		$content .= "\n";
		$content .= $this->comment ( "Les autres projets du portail" );
		if (isset ( $otherprojects ) && ! empty ( $otherprojects ))
			$content .= "define('OtherProjects','" . $otherprojects . "');\n";
		else
			$content .= "define('OtherProjects','');\n";
		$content .= "\n\n";
		$content .= $this->comment ( "Tableau qui contient tous les projets principaux du portail" );
		$content .= '$MainProjects = explode(",", "' . $mainprojects . '");';
		$content .= "\n";
		$content .= $this->comment ( "Tableau qui contient les autres projets" );
		$content .= '$OtherProjects = explode(",", "' . $otherprojects . '");';
		*/
		$content .= "\n\n";
		$content .= "?>";
		fwrite ( $file, $content );
		fclose ( $file );
		
		// $this->projects = explode ( ',', $mainprojects . ',' . $otherprojects );
	}

	/*
	private function readLdapProjects(){
		$this->ldapProjects = array();
		foreach ( array (
				$this->xmlContent ["mainProjects"],
				$this->xmlContent ["otherProjects"]
		) as $Projects ) {
			foreach ( $Projects ['project'] as $proj ) {
				if (isset ( $proj ) && ! empty ( $proj )) {
					if (isset ( $proj ['datapolicy'] ) && ! empty ( $proj ['datapolicy'] )) {
						$this->ldapProjects [] = $proj ['name'];
					}
				}
			}
		}
	}
	*/
	
	function makeApache() {
		echo "Generating apache configurationfile ...\n";
		$serverName = $this->xmlContent ['dns'];
		$documentRoot = $this->webPath;

		$apacheLogDir = $this->conf['apache']['logDir'];
		
		$content = "<VirtualHost *:443> \n";
		$content .= "\t ServerName $serverName\n"
				. "\t ServerAlias " . strtolower ( $this->portalName ) ."\n"
				. "\t DocumentRoot $documentRoot\n" 
				. "\t CustomLog   $apacheLogDir/access_log." . strtolower ( $this->portalName ) . " combined \n" 
				. "\t ErrorLog    $apacheLogDir/error_log." . strtolower ( $this->portalName ) . " \n";
		
		$content .= "\t SSLEngine on\n"
        		. "\t SSLCertificateFile " . $this->xmlContent ['SSLCertificateFile'] . "\n"
        		. "\t SSLCertificateKeyFile " . $this->xmlContent ['SSLCertificateKeyFile'] . "\n";
				
		$content .=	"\t <Directory $documentRoot> \n" . "\t\t Require all granted\n" . "\t\t php_value include_path \".:". $this->rootPath . "/projects/" . $this->portalName . ":/usr/share/pear:/usr/share/php:/usr/local/lib/php/:$documentRoot/scripts:$documentRoot/:$documentRoot/template:/usr/share/php/jpgraph\" \n" . "\t </Directory> \n"; 
		$content .= "\t <Directory $documentRoot/att_img> \n" . "\t\t AllowOverride All \n" . "\t </Directory> \n";
		
		$content .="\t ScriptAlias /extract/cgi-bin/ $this->rootPath/extract/cgi-bin/ \n";
		
		$content .= "\t <Directory $this->rootPath/extract/cgi-bin >\n"
            . "\t\t AllowOverride None\n"
            . "\t\t Options +ExecCGI -MultiViews +SymLinksIfOwnerMatch\n"
            . "\t\t Require all granted\n"
        	. "\t </Directory>\n";
		
		$content .= "</VirtualHost> \n";
		$content .= "<VirtualHost *:80> \n";
		$content .= "\t ServerName $serverName\n"
				. "\t ServerAlias " . strtolower ( $this->portalName ) ."\n"
				. "\t DocumentRoot $documentRoot\n"
				. "\t Redirect permanent / https://$serverName/\n";
		$content .= "</VirtualHost> \n";
		$this->generateFile ( $this->apacheTargetDir . '/' . strtolower ( $this->portalName ) . '.conf', $content );
	}
	
	function makeLdap() {
		echo "Generating Ldap DB_Config...\n";
		$repLdap = $this->rootPath . "/ldap";
		
		$content = "set_cachesize 0 268435456 1\n";
		$content .= "set_lg_regionmax 262144\n";
		$content .= "set_lg_bsize 2097152\n";
		$this->generateFile ( $this->ldapTargetDir . '/DB_CONFIG', $content );
		
		echo "Generating Ldap Schema...\n";
		$cn = strtolower ( $this->portalName );
		$l = new ldapIds ();
		$x = $l->getId ( $this->portalName );
		$x1 = 1;
		$x2 = 1;
		$content = "dn: cn=$cn,cn=schema,cn=config\n";
		$content .= "cn: $cn\n";
		$content .= "objectclass: olcSchemaConfig\n";
		$content .= "olcattributetypes: ( 1.3.6.1.4.1.23115.1.1.70.2." . $x . ".1.1 NAME '" . $cn . "ApplicationDate' DESC 'Date (format YYYYMMDD, only numeric chars)' EQUALITY numericStringMatch SUBSTR numericStringSubstringsMatch SYNTAX 1.3.6.1.4.1.1466.115.121.1.36{8} SINGLE-VALUE )\n";
		$content .= "olcattributetypes: ( 1.3.6.1.4.1.23115.1.1.70.2." . $x . ".1.2 NAME '" . $cn . "RegistrationDate' DESC 'Date (format YYYYMMDD, only numeric chars)' EQUALITY numericStringMatch SUBSTR numericStringSubstringsMatch SYNTAX 1.3.6.1.4.1.1466.115.121.1.36{8} SINGLE-VALUE )\n";
		$content .= "olcattributetypes: ( 1.3.6.1.4.1.23115.1.1.70.2." . $x . ".1.3 NAME '" . $cn . "Status' DESC 'Etat de la demande : en cours, acceptee ou rejetee' EQUALITY caseIgnoreMatch SYNTAX 1.3.6.1.4.1.1466.115.121.1.44 )\n";
		$content .= "olcattributetypes: ( 1.3.6.1.4.1.23115.1.1.70.2." . $x . ".1.4 NAME '" . $cn . "Abstract' DESC 'Description du travail dans $cn' EQUALITY caseIgnoreMatch  SUBSTR caseIgnoreSubstringsMatch SYNTAX 1.3.6.1.4.1.1466.115.121.1.15{1024} )\n";
		$content .= "olcobjectclasses: ( 1.3.6.1.4.1.23115.1.1.70.2." . $x . ".2.1 NAME '" . $cn . "User' DESC 'Attributs specifiques aux Utilisateurs du projet $cn' SUP top AUXILIARY MUST $cn" . "ApplicationDate MAY ( " . $cn . "RegistrationDate $ " . $cn . "Status $ " . $cn . "Abstract) )\n";
		$x1 = 5;
		$x2 = 2;
		foreach ( $this->ldapProjects as $proj ) {
			$content .= "olcattributetypes: ( 1.3.6.1.4.1.23115.1.1.70.2." . $x . ".1." . $x1 . " NAME '" . strtolower ( $proj ) . "ApplicationDate' DESC 'Date (format YYYYMMDD, only numeric chars)' EQUALITY numericStringMatch SUBSTR numericStringSubstringsMatch SYNTAX 1.3.6.1.4.1.1466.115.121.1.36{8} SINGLE-VALUE )\n";
			$x1 ++;
			$content .= "olcattributetypes: ( 1.3.6.1.4.1.23115.1.1.70.2." . $x . ".1." . $x1 . " NAME '" . strtolower ( $proj ) . "RegistrationDate' DESC 'Date (format YYYYMMDD, only numeric chars)' EQUALITY numericStringMatch SUBSTR numericStringSubstringsMatch SYNTAX 1.3.6.1.4.1.1466.115.121.1.36{8} SINGLE-VALUE )\n";
			$x1 ++;
			$content .= "olcattributetypes: ( 1.3.6.1.4.1.23115.1.1.70.2." . $x . ".1." . $x1 . " NAME '" . strtolower ( $proj ) . "Status' DESC 'Etat de la demande : en cours, acceptee ou rejetee' EQUALITY caseIgnoreMatch SYNTAX 1.3.6.1.4.1.1466.115.121.1.44 )\n";
			$x1 ++;
			$content .= "olcattributetypes: ( 1.3.6.1.4.1.23115.1.1.70.2." . $x . ".1." . $x1 . " NAME '" . strtolower ( $proj ) . "Abstract' DESC 'Description du travail dans " . strtolower ( $proj ) . "' EQUALITY caseIgnoreMatch  SUBSTR caseIgnoreSubstringsMatch SYNTAX 1.3.6.1.4.1.1466.115.121.1.15{1024} )\n";
			$x1 ++;
			$content .= "olcattributetypes: ( 1.3.6.1.4.1.23115.1.1.70.2." . $x . ".1." . $x1 . " NAME '" . strtolower ( $proj ) . "Wg' DESC 'Work Group " . strtolower ( $proj ) . "' EQUALITY caseIgnoreMatch SYNTAX 1.3.6.1.4.1.1466.115.121.1.44 )\n";
			$x1 ++;
			$content .= "olcattributetypes: ( 1.3.6.1.4.1.23115.1.1.70.2." . $x . ".1." . $x1 . " NAME '" . strtolower ( $proj ) . "AssociatedProject' DESC '' EQUALITY caseIgnoreMatch SYNTAX 1.3.6.1.4.1.1466.115.121.1.15 )\n";
			$x1 ++;
			$content .= "olcobjectclasses: ( 1.3.6.1.4.1.23115.1.1.70.2." . $x . ".2." . $x2 . " NAME '" . strtolower ( $proj ) . "User' DESC 'Attributs specifiques aux Utilisateurs du projet " . strtolower ( $proj ) . "' SUP top AUXILIARY MUST " . strtolower ( $proj ) . "ApplicationDate MAY ( " . strtolower ( $proj ) . "RegistrationDate $ " . strtolower ( $proj ) . "Status $ " . strtolower ( $proj ) . "Abstract $ " . strtolower ( $proj ) . "Wg $ " . strtolower ( $proj ) . "AssociatedProject) )\n";
			$x1 ++;
			$x2 ++;
		}
		$this->generateFile ( $this->ldapTargetDir . "/$cn.schema", $content );
		
		echo "Generating Ldap database conf...\n";
		$content = "dn: olcDatabase=bdb,cn=config\n";
		$content .= "objectclass: olcDatabaseConfig\n";
		$content .= "objectclass: olcBdbConfig\n";
		$content .= "olcaccess: to *  by dn.base=\"cn=replication,dc=" . strtolower ( $this->portalName ) . ",dc=sedoo,dc=fr\" read   by * none break\n";
		$content .= "olcaccess: to attrs=userPassword  by self write  by set.exact=\"user/memberOf & [root]\" write  by dn.base=\"cn=wwwadm,dc=" . strtolower ( $this->portalName ) . ",dc=sedoo,dc=fr\" write  by anonymous auth  by * none\n";
		$content .= "olcaccess: to attrs=memberOf  by self read  by dn.base=\"cn=wwwadm,dc=" . strtolower ( $this->portalName ) . ",dc=sedoo,dc=fr\" write  by set.exact=\"user/memberOf & [root]\" write  by set.exact=\"user/memberOf & [admin]\" write  by * none\n";
		$content .= "olcaccess: to *  by self write  by set.exact=\"user/memberOf & [root]\" write  by dn.base=\"cn=wwwadm,dc=" . strtolower ( $this->portalName ) . ",dc=sedoo,dc=fr\" write  by users read by * search\n";
		$content .= "olcaddcontentacl: FALSE\n";
		$content .= "olcdatabase: bdb\n";
		$content .= "olcdbcachefree: 1\n";
		$content .= "olcdbcachesize: 1000\n";
		$content .= "olcdbconfig: {0}set_cachesize 0 268435456 1\n";
		$content .= "olcdbconfig: {1}\n";
		$content .= "olcdbconfig: {2}set_lg_regionmax 262144\n";
		$content .= "olcdbconfig: {3}set_lg_bsize 2097152\n";
		$content .= "olcdbdirectory: $repLdap\n";
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
		$content .= "olcrootdn: cn=Manager,dc=" . strtolower ( $this->portalName ) . ",dc=sedoo,dc=fr\n";
		$content .= "olcrootpw: pro001\n";
		$content .= "olcsizelimit: unlimited\n";
		$content .= "olcsuffix: dc=" . strtolower ( $this->portalName ) . ",dc=sedoo,dc=fr\n";
		$content .= "olcsyncusesubentry: FALSE\n";
		$content .= "\n";
		$this->generateFile ( $this->ldapTargetDir . '/' . strtolower ( $this->portalName ) . '.ldif', $content );

		$this->generateInitLdif ();
		
		echo "Generating Ldap creation script...\n";
		$content = "<?php \n";
		$content .= "set_include_path ( '.:/usr/share/php' );\n";
		$content .= "exec('ldapadd -xw ldap001 -D cn=config -h localhost -f " . strtolower ( $this->portalName ) . ".schema'); \n";
		$content .= "exec('mkdir -p $repLdap'); \n";
		$content .= "exec('chown " . $this->conf['ldap']['user'] . ':' . $this->conf['ldap']['group'] ." $repLdap'); \n";
		$content .= "exec('cp -R DB_CONFIG $repLdap/'); \n";
		$content .= "exec('ldapadd -xw ldap001 -D cn=config -h localhost -f " . strtolower ( $this->portalName ) . ".ldif'); \n";
		$content .= "exec('ldapadd -xw pro001 -D cn=Manager,dc=" . strtolower ( $this->portalName ) . ",dc=sedoo,dc=fr -h localhost -f init.ldif'); \n";
		$content .= "?>";
		$this->generateFile ( $this->ldapTargetDir . '/' . strtolower ( $this->portalName ) . 'LdapCreationScript.php', $content );
	}
	
	private function generateInitLdif() {
		echo "Generating Ldap initial content...\n";
		$content = "dn: dc=" . strtolower ( $this->portalName ) . ",dc=sedoo,dc=fr\n";
		$content .= "objectClass: dcObject\n";
		$content .= "objectClass: project\n";
		$content .= "dc: " . strtolower ( $this->portalName ) . "\n";
		$content .= "cn: " . $this->portalName . "\n";
		$content .= "\n";
		$content .= "dn: ou=People,dc=" . strtolower ( $this->portalName ) . ",dc=sedoo,dc=fr\n";
		$content .= "objectClass: organizationalUnit\n";
		$content .= "ou: People\n";
		$content .= "\n";
		$content .= "dn: ou=Group,dc=" . strtolower ( $this->portalName ) . ",dc=sedoo,dc=fr\n";
		$content .= "objectClass: organizationalUnit\n";
		$content .= "ou: Group\n";
		$content .= "\n";
		$content .= "dn: ou=Project,dc=" . strtolower ( $this->portalName ) . ",dc=sedoo,dc=fr\n";
		$content .= "objectClass: organizationalUnit\n";
		$content .= "ou: Project\n";
		$content .= "\n";
		$content .= "dn: cn=wwwadm,dc=" . strtolower ( $this->portalName ) . ",dc=sedoo,dc=fr\n";
		$content .= "objectClass: person\n";
		$content .= "cn: wwwadm\n";
		$content .= "sn: wwwadm\n";
		$content .= "userPassword: www001\n";
		$content .= "\n";
		$content .= "dn: cn=replication,dc=" . strtolower ( $this->portalName ) . ",dc=sedoo,dc=fr\n";
		$content .= "objectClass: person\n";
		$content .= "cn: replication\n";
		$content .= "sn: replication\n";
		$content .= "userPassword: rep001\n";
		$content .= "\n";
		$content .= "dn: groupId=root,ou=Group,dc=" . strtolower ( $this->portalName ) . ",dc=sedoo,dc=fr\n";
		$content .= "objectClass: group\n";
		$content .= "groupId: root\n";
		$content .= "cn: Database Superusers\n";
		$content .= "isAdmin: TRUE\n";
		$content .= "\n";
		$content .= "dn: groupId=" . strtolower ( $this->portalName ) . ",ou=Group,dc=" . strtolower ( $this->portalName ) . ",dc=sedoo,dc=fr\n";
		$content .= "objectClass: group\n";
		$content .= "objectClass: top\n";
		$content .= "groupId: " . strtolower ( $this->portalName ) . "\n";
		$content .= "cn: " . $this->portalName . " Users\n";
		$content .= "isAdmin: FALSE\n";
		$content .= "parentProject: " . $this->portalName . "\n";
		$content .= "\n";
		$content .= "dn: groupId=" . strtolower ( $this->portalName ) . "Adm,ou=Group,dc=" . strtolower ( $this->portalName ) . ",dc=sedoo,dc=fr\n";
		$content .= "objectClass: group\n";
		$content .= "objectClass: top\n";
		$content .= "groupId: " . strtolower ( $this->portalName ) . "Adm\n";
		$content .= "cn: " . $this->portalName . " Admins\n";
		$content .= "isAdmin: TRUE\n";
		$content .= "parentProject: " . $this->portalName . "\n";
		$content .= "\n";
		$content .= "dn: cn=" . strtolower ( $this->portalName ) . ",ou=Project,dc=" . strtolower ( $this->portalName ) . ",dc=sedoo,dc=fr\n";
		$content .= "objectClass: project\n";
		$content .= "objectClass: top\n";
		$content .= "cn: " . $this->portalName . "\n";
		$content .= "\n";
		foreach ( $this->ldapProjects as $proj ) {
			$content .= "dn: groupId=" . strtolower ( $proj ) . "Adm,ou=Group,dc=" . strtolower ( $this->portalName ) . ",dc=sedoo,dc=fr\n";
			$content .= "objectClass: group\n";
			$content .= "groupId: " . strtolower ( $proj ) . "Adm\n";
			$content .= "cn: " . $proj . " Admins\n";
			$content .= "isAdmin: TRUE\n";
			$content .= "memberUid: guillaume.brissebrat@obs-mip.fr\n";
			$content .= "parentProject: " . $proj . "\n";
			$content .= "\n";
			$content .= "dn: groupId=" . strtolower ( $proj ) . "Core,ou=Group,dc=" . strtolower ( $this->portalName ) . ",dc=sedoo,dc=fr\n";
			$content .= "objectClass: group\n";
			$content .= "groupId: " . strtolower ( $proj ) . "Core\n";
			$content .= "cn: " . $proj . " Core Users\n";
			$content .= "isAdmin: FALSE\n";
			$content .= "parentProject: " . $proj . "\n";
			$content .= "\n";
			$content .= "dn: groupId=" . strtolower ( $proj ) . "Asso,ou=Group,dc=" . strtolower ( $this->portalName ) . ",dc=sedoo,dc=fr\n";
			$content .= "objectClass: group\n";
			$content .= "groupId: " . strtolower ( $proj ) . "Asso\n";
			$content .= "cn: " . $proj . " Associated Scientists\n";
			$content .= "isAdmin: FALSE\n";
			$content .= "parentProject: " . $proj . "\n";
			$content .= "\n";
			$content .= "dn: groupId=" . strtolower ( $proj ) . "Participant,ou=Group,dc=" . strtolower ( $this->portalName ) . ",dc=sedoo,dc=fr\n";
			$content .= "objectClass: group\n";
			$content .= "groupId: " . strtolower ( $proj ) . "Participant\n";
			$content .= "cn: " . $proj . " Participants (other site web users)\n";
			$content .= "isAdmin: FALSE\n";
			$content .= "parentProject: " . $proj . "\n";
			$content .= "\n";
			$content .= "dn: ou=Project,dc=" . strtolower ( $this->portalName ) . ",dc=sedoo,dc=fr\n";
			$content .= "objectClass: project\n";
			$content .= "cn: " . $proj . "\n";
			$content .= "\n";
		}
		$this->generateFile ( $this->ldapTargetDir . '/' . strtolower ( $this->portalName ) . '-init.ldif', $content );
	}
	
	function makeExtract() {
		// Extraction filter generation
		echo "Generating extraction filter file...\n";
		if (isset ( $this->xmlContent ['database'] ['password'] ) && ! empty ( $this->xmlContent ['database'] ['password'] ))
			$db_password = $this->xmlContent ['database'] ['password'];
		else
			$db_password = '';
		$content = "log.level=INFO\n" . "log.appender=fileyAppender\n" . "\n#root_path = racine definie dans le template.xml\n" . "log.path=" . $this->logPath ."\n" . "result.path=" . $this->dlPath . "\n" . "\n#A partir de l'élement database \n" . "db.host=" . $this->xmlContent ['database'] ['host'] . "\n" . "db.name=" . $this->xmlContent ['database'] ['name'] . "\n" . "db.username=" . $this->xmlContent ['database'] ['user'] . "\n" . "db.password=" . $db_password . "\n" . "\n#A partir de l'element ldap \n" . "ldap.host=" . $this->xmlContent ['ldap'] ['host'] . "\n" . "ldap.base=" . $this->xmlContent ['ldap'] ['base'] . "\n" . "\n#A partir du nom DNS configure dans le template \n" . "ui.dl=https://" . $this->xmlContent ['dns'] . "/extract/download.php\n" . "ui.dl.pub=https://" . $this->xmlContent ['dns'] . "/extract/downloadPub.php\n" . "\nxml.response.schema.uri=http://" . $this->xmlContent ['dns'] . "/extract/reponse\n" . "xml.response.schema.xsd=http://" . $this->xmlContent ['dns'] . "/extract/reponse.xsd\n" . "\n#bin defini dans le template.xml \n" . "java.bin=" . $this->conf ['java_bin'] . "\n" . "\n#rootEmail \n" . "mail.admin=" . $this->xmlContent ['rootEmail'] . "\n" . "mail.from=" . $this->xmlContent ['rootEmail'] . "\n" . "mail.topic.prefix=[" . $this->xmlContent ['name'] . "-DATABASE] \n";
		
		$this->generateFile ( $this->extractDir . '/src/main/filters/PORTAL.properties', $content );
		
		echo "Generating data extractor...\n";
		exec ( "cd ./extracteur; " . $this->conf ['maven_bin'] . "/mvn clean package -Dcible=PORTAL", $message );
		echo "\n";
		foreach ( $message as $m )
			echo $m . "\n";
		echo "\n";
		exec ( "cp -R $this->extractDir/target/extracteur-install.zip $this->extractTargetDir" );
	}
	
	private function comment($com) {
		return "//" . $com . "\n";
	}
	private function generateFile($filepath, $content) {
		$file = fopen ( $filepath, "w" ) or die ( "Unable to open file " . $filepath );
		fwrite ( $file, $content );
		fclose ( $file );
	}
	
	
	
}
class XmlUtils {
	static function validateXml($xmlFilePath, $xmlSchema) {
		echo "Validating xml file $xmlFilePath...\n";
		libxml_use_internal_errors ( true );
		$xml = new DOMDocument ();
		$xml->load ( $xmlFilePath );
		if (! $xml->schemaValidate ( $xmlSchema )) {
			echo 'DOMDocument::schemaValidate() Generated Errors!';
			self::libxml_display_errors ();
			return false;
		} else {
			return true;
		}
	}
	// Errors display
	static function libxml_display_error($error) {
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
	static function libxml_display_errors() {
		$errors = libxml_get_errors ();
		foreach ( $errors as $error ) {
			echo self::libxml_display_error ( $error );
		}
		libxml_clear_errors ();
	}
}

class DirUtils {
	static function copyDirectory($Directory, $DestDirectory) {
		exec ( "cp -R $Directory $DestDirectory" );
	}
	static function rmDirectory($dir) {
		exec ( "rm -rf $dir" );
	}
	function moveDirectory($Directory, $DestDirectory) {
		exec ( "mv $Directory $DestDirectory" );
	}
	static function ScanDirectory($Directory) {
		global $i, $Files_list;
		$MyDirectory = opendir ( $Directory ) or die ( 'Erreur' );
		
		while ( $Entry = @readdir ( $MyDirectory ) ) {
			if (is_dir ( $Directory . '/' . $Entry ) && $Entry != '.' && $Entry != '..' && $Entry != null) {
				self::ScanDirectory ( $Directory . '/' . $Entry );
			} else if (! is_dir ( $Directory . '/' . $Entry ) && $Entry != '.' && $Entry != '..' && $Entry != null) {
				$Files_list [$i] = $Directory . '/' . $Entry;
				$i ++;
			}
		}
		closedir ( $MyDirectory );
	}
	static function changeWordInDirectory($Directory, $wordToModify, $wordToReplace) {
		global $i, $Files_list;
		if (is_dir ( $Directory )) {
			self::ScanDirectory ( $Directory );
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
}

if ( count( $argv ) < 2){
	echo "usage php portalGeneratorV2.php input/projet.xml [options]\n";
	echo "options : --skip-ldap --skip-backup --skip-ftp --skip-extract --skip-database --skip-php --skip-apache\n";
}else{
	$inputFile = $argv[1];
	$options = array_slice ( $argv, 2 );

	$conf = array ();
	$conf = array_merge ( $conf, $javaBin );
	$conf['apache'] = $apacheConf;
	$conf['ldap'] = $ldapConf;

	$generator = new PortalGenerator ( $conf, $options );

	$generator->make ( $inputFile );
}

?>
