<?php 
    require_once ("/sites/kernel/#MainProject/conf.php");

    define('XML_TEMPLATE','https://'.$_SERVER['HTTP_HOST'].'/extract/requete.xml');
    define('XML_DEFAULT_FORMAT','ames');
    define('XML_DEFAULT_FORMAT_VERSION','2160');
    define('XML_DEFAULT_COMPRESSION','zip');
    define('XML_DEFAULT_FLAG',0);
    define('XML_DEFAULT_DELTA',0);

    define('XML_FICHIERS_TEMPLATE','https://'.$_SERVER['HTTP_HOST'].'/extract/requete_files.xml');
?>
