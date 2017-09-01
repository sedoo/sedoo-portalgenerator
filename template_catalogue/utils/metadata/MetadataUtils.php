<?php 
require_once ("conf/conf.php");
require_once ("bd/url.php");
require_once ("bd/dataset_factory.php");
require_once ("sedoo-metadata/sedoo_metadata_all.php");

define ( 'URL_BASE', 'https://' . $_SERVER['HTTP_HOST'] . '/' );
define ( 'UUID', '74cde792-584b-11e3-%1$04d-ce3f5508acd9' );


class MetadataUtils {
		
	static function portalDatasetToSedooDataset($datsId, $programName = null, $collectionName = null) {
		if (isset ( $datsId )) {	
			$dats = dataset_factory::createDatasetById($datsId);
	
			if (isset ( $dats )) {
				$uuid = sprintf ( $dats->dats_uuid, $dats->dats_id );
	
				$builder = new sedoo_metadata_builder ( $uuid, $dats->dats_title, $dats->dats_abstract );
	
				self::addGeneralInfos ( $builder, $dats);
				self::addAerisInfos($builder, $programName, $collectionName);
				
				self::addConstraints ( $builder, $dats );
				self::addUrls ( $builder, $dats, PROGRAM_NAME . ' data portal' );
				self::addContacts ( $builder, $dats );
				self::addProjects ( $builder, $dats );
					
				self::addInstruments( $builder, $dats );
				self::addSingleInstrument ( $builder, $dats );
				self::addBoundingsFromMapFile ( $builder, $dats );
	
				self::addVariables ( $builder, $dats );
				self::addFormats ( $builder, $dats );
				self::addThumbnail ( $builder, $dats );
	
				return $builder->build ();
			}
		}
	}
	
	static function addUrls($builder, $dats, $urlDescription = null) {
		$u = new url ();
		$urls = $u->getByDataset ( $dats->dats_id );
	
		if (! isset ( $urlDescription ) || empty ( $urlDescription )) {
			$urlDescription = $dats->dats_title;
		}
	
		foreach ( $urls as $url ) {
			if ($url->url_type == 'ftp') {
				if (strpos ( $url->url, 'ftp' ) === 0) {
					$builder->downloadUrl ( $url->url, $urlDescription );
				} else {
					$builder->downloadUrl ( URL_BASE . $url->url, $urlDescription );
				}
			} else if ($url->url_type == 'http') {
				if (strpos ( $url->url, 'http' ) === 0) {
					$builder->downloadUrl ( $url->url, $urlDescription );
				} else {
					$builder->downloadUrl ( URL_BASE . $url->url."&project_name=" . PROGRAM_NAME, $urlDescription );
				}
			}
		}
		$builder->linkUrl ( URL_BASE.'?editDatsId=' . $dats->dats_id, $urlDescription );
	}
	
	static function addContacts($builder, $dats) {
		foreach ( $dats->originators as $originator ) {
			$cb = new sedoo_metadata_contact_builder ( $originator->pers_name );
			$cb->email ( $originator->pers_email_1 )->organisation ( $originator->organism->org_sname );
			if ($originator->isPI ()) {
				$cb->pi ();
			} else {
				$cb->pointOfContact ();
			}
			$builder->contact ( $cb->build () );
		}
	}
	
	static function addConstraints($builder, $dats) {
		$accessConstraints = null;
		if (isset ( $dats->data_policy ) && ! empty ( $dats->data_policy )) {
			$accessConstraints = $dats->data_policy->data_policy_name . ', ' . $dats->data_policy->data_policy_url;
			$builder->dataPolicy ( $dats->data_policy->data_policy_name, $dats->data_policy->data_policy_url );
		}
		$builder->constraints ( $dats->dats_use_constraints, $accessConstraints );
	}
	
	static function addProjects($builder, $dats) {
		foreach ( $dats->projects as $pro ) {
			$builder->project ( $pro->getFullName() );
		}
	}
	
	static function addFormats($builder, $dats) {
		if (isset ( $dats->data_formats ) && ! empty ( $dats->data_formats )) {
			foreach ( $dats->data_formats as $f ) {
				$builder->format ( $f->data_format_name );
			}
		}
	}
	static function addThumbnail($builder, $dats) {
		if (isset ( $dats->image ) && ! empty ( $dats->image )) {
			$builder->thumbnail ( URL_BASE . $dats->image );
		}
	}
	static function addGeneralInfos($builder, $dats) {
		$builder->dataset()->doi($dats->dats_doi)->sedoo ()->temporalExtent($dats->dats_date_begin, $dats->dats_date_end)
			->creationDate($dats->dats_pub_date )->purpose ( $dats->dats_purpose )->asNeededUpdate ()->localIdentifier ( '' . $dats->dats_id );
			
	}
	
	static function addAerisInfos($builder, $programName, $collectionName) {
		$builder->program ( $programName )->collection( $collectionName );
	}
	
	static function addVariables($builder, $dats) {
		$isAtmo = false;
		$isOcean = false;
		if (isset ( $dats->dats_variables ) && ! empty ( $dats->dats_variables )) {
			foreach ( $dats->dats_variables as $v ) {
				$paramName = null;
				$paramGcmd = null;
				$paramUnit = null;
				if (isset ( $v->variable ) && ! empty ( $v->variable )) {
					if (isset ( $v->variable->gcmd ) && ! empty ( $v->variable->gcmd )) {
						$gcmd = $v->variable->gcmd->toString ();
						$paramGcmd = $v->variable->gcmd->gcmd_name;
						if (stripos ( $gcmd, 'Atmosphere' ) === 0) {
							$isAtmo = true;
						}
						if (stripos ( $gcmd, 'Ocean' ) === 0) {
							$isOcean = true;
						}
					}
					if (isset ( $v->variable->var_name ) && ! empty ( $v->variable->var_name )) {
						$paramName = $v->variable->var_name;
					}
					if (isset ( $v->unit->unit_name ) && ! empty ( $v->unit->unit_name )) {
						$paramUnit = $v->unit->unit_name;
					} else if (isset ( $v->unit->unit_code ) && ! empty ( $v->unit->unit_code )) {
						$paramUnit = $v->unit->unit_code;
					}
					$builder->parameter ( $paramName, $paramGcmd, $paramUnit );
				}
			}
		}
	
		if ($isAtmo) {
			$builder->atmosphere ();
		}
		if ($isOcean) {
			$builder->ocean ();
		}
	}
	
	static function addSingleInstrument($builder, $dats) {
		$lineage = array ();
		if (isset ( $dats->dats_sensors ) && ! empty ( $dats->dats_sensors ) && count ( $dats->dats_sensors ) == 1) {
			foreach ( $dats->dats_sensors as $s ) {
				if (isset ( $s->sensor ) && ! empty ( $s->sensor )) {
					if (isset ( $s->sensor->gcmd_instrument_keyword ) && ! empty ( $s->sensor->gcmd_instrument_keyword )) {
						$builder->gcmdInstrumentKeyword ( $s->sensor->gcmd_instrument_keyword->gcmd_sensor_name );
						$instrumentGcmd = $s->sensor->gcmd_instrument_keyword->gcmd_sensor_name;
					}
					$instrumentName = '';
					if (isset ( $s->sensor->manufacturer )) {
						$instrumentName = $s->sensor->manufacturer->manufacturer_name;
					}
					$instrumentName .= ' ' . $s->sensor->sensor_model;
					$instrumentName = trim ( $instrumentName );
					$lineage [] = $s->sensor->toString ();
	
					$platformBoundings = null;
					// boundings
					if (isset ( $s->sensor->boundings ) && ! empty ( $s->sensor->boundings )) {
						$platformBoundings = new sedoo_metadata_boundings ( $s->sensor->boundings->north_bounding_coord, $s->sensor->boundings->east_bounding_coord, $s->sensor->boundings->south_bounding_coord, $s->sensor->boundings->west_bounding_coord );
					}
					if ((isset ( $instrumentName ) && ! empty ( $instrumentName )) || isset ( $instrumentGcmd )) {
						$instrumentKeyword = new sedoo_metadata_keyword ( $instrumentName, $instrumentGcmd );
					}
					$s->sensor->get_sensor_places ();
					if (isset ( $s->sensor->sensor_places ) && ! empty ( $s->sensor->sensor_places ) && count ( $s->sensor->sensor_places ) == 1) {
						foreach ( $s->sensor->sensor_places as $sp ) {
							$sp->getPlace ();
							$platformGcmd = null;
							$platformName = null;
							if (isset ( $sp->place->gcmd_plateform_keyword ) && ! empty ( $sp->place->gcmd_plateform_keyword )) {
								$platformGcmd = $sp->place->gcmd_plateform_keyword->gcmd_plat_name;
							} else if (isset ( $sp->place->parent_place->gcmd_plateform_keyword ) && ! empty ( $sp->place->parent_place->gcmd_plateform_keyword )) {
								$platformGcmd = $sp->place->parent_place->gcmd_plateform_keyword->gcmd_plat_name;
							}
	
							if (isset ( $sp->place->place_name ) && ! empty ( $sp->place->place_name )) {
								$platformName = trim ( $sp->place->place_name );
							} else if (isset ( $sp->place->parent_place ) && ! empty ( $sp->place->parent_place )) {
								$platformName = trim ( $sp->place->parent_place->place_name );
							}
	
							if ($sp->environment) {
								$infos = array (
										'Environment' => $sp->environment
								);
							}
	
							if (! isset ( $s->sensor->boundings ) || empty ( $s->sensor->boundings )) {
								if (isset ( $sp->place->boundings ) && ! empty ( $sp->place->boundings )) {
									$platformBoundings = new sedoo_metadata_boundings ( $sp->place->boundings->north_bounding_coord, $sp->place->boundings->east_bounding_coord, $sp->place->boundings->south_bounding_coord, $sp->place->boundings->west_bounding_coord );
								}
							}
							if (isset ( $platformName ) || isset ( $platformGcmd )) {
								$builder->platform ( $platformName, $platformGcmd, $platformBoundings, array()/*array (
										$instrumentKeyword
								)*/, $infos );
							}
						}
					}
				}
			}
		}
		if ($lineage) {
			$builder->lineage ( implode ( PHP_EOL, $lineage ) );
		}
	}
	
	static function addInstruments($builder, $dats) {
		if (get_class ( $dats ) == 'multi_instru_dataset') {
			$site = $dats->sites [0];
			$platformGcmd = null;
			$platformName = null;
			$instrumentKeywords = array ();
			$lineage = array ();
			if (isset ( $site->gcmd_plateform_keyword ) && ! empty ( $site->gcmd_plateform_keyword )) {
				$platformGcmd = $site->gcmd_plateform_keyword->gcmd_plat_name;
			}
			
			if (isset ( $site->place_name ) && ! empty ( $site->place_name )) {
				$platformName = trim ( $site->place_name );
			}
			
			for($i = 0; $i < count ( $dats->dats_sensors ); $i ++) {
				$instrumentGcmd = null;
				$instrumentName = '';
				if (isset ( $dats->dats_sensors [$i]->sensor->manufacturer )) {
					$instrumentName = $dats->dats_sensors [$i]->sensor->manufacturer->manufacturer_name;
				}
				$instrumentName .= ' ' . $dats->dats_sensors [$i]->sensor->sensor_model;
				$instrumentName = trim ( $instrumentName );
				
				if (isset ( $dats->dats_sensors [$i]->sensor->gcmd_instrument_keyword )) {
					$instrumentGcmd = $dats->dats_sensors [$i]->sensor->gcmd_instrument_keyword->gcmd_sensor_name;
				}
				if ((isset ( $instrumentName ) && ! empty ( $instrumentName )) || isset ( $instrumentGcmd )) {
					// $instrumentKeywords[] = new sedoo_metadata_keyword ( $instrumentName, $instrumentGcmd );
				}
				
				$lineage [] = $dats->dats_sensors [$i]->sensor->toString ();
			}
			
			if ($lineage) {
				$builder->lineage ( implode ( PHP_EOL, $lineage ) );
			}
			
			$platformBoundings = null;
			// boundings
			if ((isset ( $site->west_bounding_coord ) && strlen ( $site->west_bounding_coord )) || (isset ( $site->east_bounding_coord ) && strlen ( $site->east_bounding_coord )) || (isset ( $site->north_bounding_coord ) && strlen ( $site->north_bounding_coord )) || (isset ( $site->south_bounding_coord ) && strlen ( $site->south_bounding_coord ))) {
				$platformBoundings = new sedoo_metadata_boundings ( $site->north_bounding_coord, $site->east_bounding_coord, $site->south_bounding_coord, $site->west_bounding_coord );
			}
			
			if (isset ( $platformName ) || isset ( $platformGcmd )) {
				$builder->platform ( $platformName, $platformGcmd, $platformBoundings, $instrumentKeywords );
			}
		}
	
	}
	
	static function addBoundingsFromMapFile($builder, $dats) {
		// lire fichier map s'il existe
		$url = new url ();
		$map = $url->getMapFileByDataset ( $dats->dats_id );
		if (isset ( $map ) && ! empty ( $map )) {
			$mapUrl = $map [0]->url;
			$file = str_replace ( 'file://localhost', '', $mapUrl );
			if (file_exists ( $file )) {
				$lignes = file ( $file );
				$cpt = 0;
				foreach ( $lignes as $ligne ) {
					if (substr ( $ligne, 0, 1 ) == '#') {
						continue;
					} else {
						$cpt ++;
						$infos = explode ( ';', trim ( $ligne ) );

						$lat = $infos [2];
						$lon = $infos [3];
						$alt = $infos [4];
	
						$platformBoundings = new sedoo_metadata_boundings ( $lat, $lon, $lat, $lon );
						$builder->platform($infos[1], 'In Situ Land-based Platforms', $platformBoundings);
					}
				}
			}
		}
	}
	
}


?>