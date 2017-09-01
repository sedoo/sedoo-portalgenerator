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
* 'logDir'	=> '/var/log/apache2',
* 'confDir'	=> '/etc/apache2/sites-enabled',
* 'service'	=> 'apache2',
*
* ----- redhat / centos default config -----
* 'user' 	=> 'apache',
* 'group' 	=> 'apache',	
* 'logDir'	=> '/var/log/httpd',
* 'confDir'	=> '/etc/httpd/conf.d',
* 'service'	=> 'httpd',
*
************/

$apacheConf = array(
	'user' 		=> 'apache',
	'group' 	=> 'apache',	
	'logDir'		=> '/var/log/apache2', // '/var/log/httpd'
	'service'		=> 'apache2', //'httpd'
	'confDir'		=> '/etc/apache2/sites-enabled'
	);


/**
* LDAP
************/

$ldapConf = array(
	'user' 		=> 'LDAP-USER',
	'group' 	=> 'LDAP-GROUP'
	);

/**
* JAVA BIN
* depend de l'OS
************/
$javaBin = array(
	'maven_bin'		=> '/usr/bin',
	'java_bin'		=> '/usr/bin' // '/usr/local/j2sdk6/bin' sur twodoo
)


?>