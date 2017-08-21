<?php

require_once("conf/conf.php");

define('DOI_RESOLVER','http://dx.doi.org/');

define('DATACITE_WEB','http://data.datacite.org/');
define('DATACITE_CITATION','http://catalogue.sedoo.fr/metadata-services/datacite/citation?doi=');
define('DATACITE_BIBTEX','http://catalogue.sedoo.fr/metadata-services/datacite/citation?style=bibtex&doi=');

define("SERVICE_DOI_USER","INIST.OMP");
define("SERVICE_DOI_PASSWD","18071971");
define("SERVICE_DOI_URL","https://".SERVICE_DOI_USER.":".SERVICE_DOI_PASSWD."@mds.datacite.org/doi/");

define("SERVICE_DOI_UPDATE","https://".SERVICE_DOI_USER.":".SERVICE_DOI_PASSWD."@mds.datacite.org/doi");
define("SERVICE_DOI_UPLOAD_METADATA","https://".SERVICE_DOI_USER.":".SERVICE_DOI_PASSWD."@mds.datacite.org/metadata");

//define('SERVICE_DOI_UPDATE','https://mds.datacite.org/doi?testMode=true');
//define('SERVICE_DOI_UPLOAD_METADATA','https://mds.datacite.org/metadata?testMode=true');






?>
