<?php
/**
*  CONFIGS SPECIFIQUES A L'ENVIRONNEMENT SERVEUR
*
*/

/**
* PHP INCLUDE
************/
$localInstallPath = "/path/to/portalgenerator/folder";

set_include_path ( '.:/usr/local/pear/share/pear:/usr/share/php:'.$localInstallPath.':'.$localInstallPath.'/target:'.$localInstallPath.'/target/template_catalogue:'.$localInstallPath.'/target/template_catalogue/xml' );

/************************
* APACHE CONFIG
* ----- dedian default config -----
* 'user' 	=> 'www-data',
* 'group' 	=> 'www-data',	
* 'path'	=> '/etc/apache2/',
*
* ----- redhat / centos default config -----
* 'user' 	=> 'apache',
* 'group' 	=> 'apache',	
* 'path'	=> '/etc/httpd',
*
************/

$apacheConf = array(
	'user' 		=> 'apache',
	'group' 	=> 'apache',	
	'path'		=> '/etc/httpd',
	);

/**
* DATABASE
************/

$databaseConf = array(
	'host' 			=> 'DB-HOST', 
	'db_name'		=> 'DB-NAME',
	'db_user' 		=> 'DB-USER', 
	'db_password'	=> 'DB-PASSWD',

);

/**
* LDAP
************/

$ldapConf = array(
	'user' 		=> 'LDAP-USER',
	'group' 	=> 'LDAP-GROUP',
	);

/**
* JAVA BIN
* depend de l'OS
************/
$javaBin = array(
	'maven_bin'		=> '/usr/bin',
	'java_bin'		=> '/usr/local/j2sdk/bin',
)


?>