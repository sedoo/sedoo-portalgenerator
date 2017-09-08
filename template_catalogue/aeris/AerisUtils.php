<?php
require_once ("utils/metadata/MetadataUtils.php");
require_once ("aeris/conf.php");

class AerisUtils {
	static function portalDatasetToAerisDataset($datsId, $programName = PROGRAM_NAME, $collectionName = DEFAULT_COLLECTION) {
		if ($programName == null){
			echo "WARN program is null<br/>";
		}
		if ($collectionName == null){
			echo "WARN collection is null<br/>";
		}
		return MetadataUtils::portalDatasetToSedooDataset ( $datsId, $programName, $collectionName );
	}
	
	/**
	 * Convertit un jeu au format Aeris.
	 *
	 * $collectionName: nom de la collection Aeris
	 * $test: true pour envoyer au catalogue aeris (false par défaut)
	 */
	static function datasetToAeris($datsId, $test = false, $collectionName = DEFAULT_COLLECTION) {
		$dataset = self::portalDatasetToAerisDataset ( $datsId, PROGRAM_NAME, $collectionName );
		if (isset ( $dataset )) {
			if ($test) {
				header ( "Content-Type: application/json" );
				sedooMetadataToAerisCollection ( $dataset );
				// sedooMetadataToJson ( $dataset );
			} else {
				sedooMetadataToAerisDepot ( $dataset );
			}
		}
	}
	/**
	 * Convertit au format Aeris tous les jeux d'un projet.
	 *
	 * $projectName: nom du projet (tel que renseigné dans la base)
	 * $test: true pour envoyer au catalogue aeris (false par défaut)
	 * $filter: true pour ne conserver que les jeux marqués atmosphère (false par défaut)
	 * $collectionName: nom de la collection Aeris ($projectName par défaut)
	 */
	static function projectToAeris($projectName, $test = false, $filter = false, $collectionName = null) {
		
		if ($collectionName == null){
			$collectionName = $projectName;
		}
		
		$proj = new project ();
		$projList = $proj->getIdByProjectName ( $projectName );
		foreach ( $projList as $p ) {
			echo "<br/><b>$p->project_id $p->project_name</b><br/>";
			$query = "SELECT DISTINCT dats_id, dats_title FROM dataset JOIN dats_proj USING (dats_id) JOIN project USING (project_id) " 
					. "LEFT JOIN dats_type USING (dats_id) LEFT JOIN url USING (dats_id) " 
					. "WHERE (dats_type_id = 4 OR dats_type_id IS NULL) AND (project_id = $p->project_id or pro_project_id = $p->project_id) " 
					. "AND (is_archived is null OR NOT is_archived) AND url is not null ORDER BY dats_title";
			
			$d = new dataset ();
			$dtsList = $d->getOnlyTitles ( $query );
			foreach ( $dtsList as $dts ) {
				echo "<br/>$dts->dats_id $dts->dats_title<br/>";
				$dataset = self::portalDatasetToAerisDataset ( $dts->dats_id, PROGRAM_NAME, $collectionName );
				if (isset ( $dataset )) {
					if ($filter && ! in_array ( sedoo_metadata_topics_iso::CLIM_METEO_ATMOS, $dataset->topicsISO )) {
						echo "NO ATMO<br/>";
					} else {
						if ($test) {
							echo "TEST OK<br/>";
						} else {
							sedooMetadataToAerisDepot ( $dataset );
						}
					}
				}
			}
		}
	}
}
?>