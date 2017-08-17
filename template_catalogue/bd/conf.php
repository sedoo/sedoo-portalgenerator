<?php
require_once ("conf/conf.php");
//Listes d'ids (séparés par des virgules) à ne pas afficher dans les listes des formulaires (fonctions getAll des objets)
define('DATA_FORMAT_EXCLUDE','8,9');
define('PERSONNE_EXCLUDE','29');
define('MANUFACTURER_EXCLUDE','57,58');
define('GCMD_PLAT_EXCLUDE_INSITU','1,8,11,12,15,16,22,19,20,24,25,26,27,28,29,30');
define('GCMD_PLAT_MODEL','11,12,19,20,24,25,26,27,28,29,30');
define('MODEL_CATEGORIES','24,25,26,27,28,29,30');
define('GCMD_PLAT_EXCLUDE_LSTPLAT','1');
//Limites d'affichage des listes à arbitrer
define('MANUFACTURER_MAX_ID','180');
?>
