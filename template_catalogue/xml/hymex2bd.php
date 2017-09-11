<?php
/*
 * Created on 7 oct. 2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
	set_include_path('.:/usr/share/pear:/usr/share/php:/home/mastrori/workspace/mistrals_catalogue/');
	require_once ("bd/dataset.php");
	require_once ("bd/place_var.php");

   function makeDatsTitle($id,$name,$sitename,$trademark,$model)
   {
   		return $name[0].','.$sitename[0];
   }
   
   function makeDatsOriginators($contacts)
   {
   		$originators = array();
   		$i=0;
   		foreach($contacts as $contact)
   		{
   			$originators[$i] = new personne;
   			$originators[$i]->pers_name = (string)$contact->ffname;
   			$originators[$i]->pers_email_1 = (string)$contact->email;
   			printf("contact %s, %s\n",$originators[$i]->pers_name,$originators[$i]->pers_email_1);
   			$i++;
   		}
   		return $originators;
   }
   
   function makeDatsSensor($name,$trademark,$model,$tresolution,$fixed,
   							$gresolution,$mapping,$mapping_manufacturer,$url_manufacturer)
   {
   		$dats_sensors = array();
   		$dats_sensors[0] = new dats_sensor;
   		$dats_sensors[0]->sensor = new sensor;
   		$dats_sensors[0]->sensor->sensor_model = $model[0];
   		$dats_sensors[0]->sensor_resol_temp = $tresolution[0];

   		printf("sensor_manufacturer : %s",$trademark[0]);
   		if (isset($tademark[0]) && !empty($trademark[0]) && isset($mapping_manufacturer["$trademark[0]"]))
   		{
   			printf("sensor_manufacturer : %s",$trademark[0]);
   			$manufacturer = new manufacturer;
   			$dats_sensors[0]->sensor->manufacturer->manufacturer_name = $manufacturer->getByName($mapping_manufacturer["trademark[0]"]);
   			if ($dats_sensors[0]->sensor->manufacturer != null)
   				$dats_sensors[0]->sensor->manufacturer_id = $dats_sensors[0]->sensor->manufacturer->manufacturer_id;
   			else
   			{
   				$dats_sensors[0]->sensor->manufacturer = new manufacturer;
   				$dats_sensors[0]->sensor->manufacturer->manufacturer_name = $mapping_manufacturer["trademark[0]"];
   				$dats_sensors[0]->sensor->manufacturer->manufacturer_url = $url_manufacturer["trademark[0]"];	
   			}
   		}
   		else
   		{
   			$dats_sensors[0]->sensor->manufacturer = new manufacturer;
   			$dats_sensors[0]->sensor->manufacturer->manufacturer_name = $trademark[0];
   		}
   		if (isset($name[0]) && !empty($name[0]) && isset($mapping["$name[0]"]))
   		{
   			$sensor_keyword = new gcmd_instrument_keyword;
   			$dats_sensors[0]->sensor->gcmd_instrument_keyword = $sensor_keyword->getByName($mapping["$name[0]"]);
   			if ($dats_sensors[0]->sensor->gcmd_instrument_keyword != null)
   				$dats_sensors[0]->sensor->gcmd_sensor_id = $dats_sensors[0]->sensor->gcmd_instrument_keyword->gcmd_sensor_id;
   			else{
   			}
   		}
   		else{ 
   		}
   		if (isset($fixed) && !empty($fixed))
   		{
   			$dats_sensors[0]->sensor->boundings = new boundings;
   			$dats_sensors[0]->sensor->boundings->west_bounding_coord = (double) $fixed[0]->lon;
   			$dats_sensors[0]->sensor->boundings->east_bounding_coord = (double) $fixed[0]->lon;
   			$dats_sensors[0]->sensor->boundings->north_bounding_coord = (double) $fixed[0]->lat;
   			$dats_sensors[0]->sensor->boundings->south_bounding_coord = (double) $fixed[0]->lat;
   			//hauteur au sol : dans les fiches, on a altitude -> pour le dataset.
   		}
   		$dats_sensors[0]->sensor_vert_resolution = (string)$gresolution[0]->vertical;
   		$dats_sensors[0]->sensor_lat_resolution = (string)$gresolution[0]->horizontal;
   		$dats_sensors[0]->sensor_lon_resolution = (string)$gresolution[0]->horizontal;
   		return $dats_sensors;		
   }
   
   function makeDatsFixedBoundings($fixed)
   {
   		if (isset($fixed) && !empty($fixed))
   		{
   			$bound = new boundings;
   			$bound->west_bounding_coord = (double) $fixed[0]->lon;
   			$bound->east_bounding_coord = (double) $fixed[0]->lon;
   			$bound->north_bounding_coord = (double) $fixed[0]->lat;
   			$bound->south_bounding_coord = (double) $fixed[0]->lat;
   			return $bound;
   		}
   		return null;
   }
   
   function makeDatsPlace($sitename,$plateform)
   {
   		$gcmd_plat_mapping = array("In-situ land-based platforms " => "Ground stations",
								"In-situ land-based platforms - Ground Stations" => "Ground stations",
    							"In-situ land-based platforms - Wind Profiler" => "Ground stations",
    							"In-situ land-based platforms - Ground Radars" => "Ground stations",
    							"In-situ land-based platforms - Ground-based observations" => "Ground stations",
    							"In-situ land-based platforms - Weather stations" => "Ground stations",
    							"in-situ based: lightning detection" => "Ground stations",
    							"Ground based" => "Ground stations",
    							"In-situ ocean-based platforms - BUOYS" => "Ocean plateforms",
    							"In-situ ocean-based platforms - Ships" => "Ships",
    							"Balloons - Radiosonds" => "Balloons",
    							"Balloons - Dropwindsondes" => "Balloons",
    							"Hpiconet network constituted of 21 stations" => "Ground stations",
    							"GPS" => "Ground stations",
    							"OHMCV" => "Ground stations",
    							"remote sensing platform" => "Ground stations",
    							"remote sensing platforms" => "Ground stations",
    							"Office Nationale de la Météorologie, Algéria" => "Ground stations");
   		$sites = array();
   		$sites[0] = new place;
   		$sites[0]->place_name = $sitename[0];
   		
   		if ($plateform[0] && $gcmd_plat_mapping["$plateform[0]"] != null)
   		{
   			$plat_keyword = new gcmd_plateform_keyword;
   			$sites[0]->gcmd_plateform_keyword = $plat_keyword->getByName($gcmd_plat_mapping["$plateform[0]"]);
   			$sites[0]->gcmd_plat_id = $sites[0]->gcmd_plateform_keyword->gcmd_plat_id;
   		}
   		return $sites;
   }
   
   function makeDatsDate($date_element)
   {
   		if (isset($date_element) && !empty($date_element))
   		{
   			$date = ((string)$date_element[0]->year).'-'.((string)$date_element[0]->month).'-'.((string)$date_element[0]->day);
   			if (substr($date,-2) != '--')
   				return $date;
   		}
   		return null;
   }
   
   function getGcmdId($topic,$categ,$param)
   {
   		$query = "select * from gcmd_science_keyword where lower(gcmd_name) = '".strtolower($param)."' and " .
   				"gcm_gcmd_id in (select distinct gcmd_id from gcmd_science_keyword where lower(gcmd_name) = '".strtolower($categ)."' and " .
   				"gcm_gcmd_id in (select distinct gcmd_id from gcmd_science_keyword where lower(gcmd_name) = '".strtolower($topic)."'))";
   		$gcmd_key = new gcmd_science_keyword;
   		$gcmd_keys = $gcmd_key->getByQuery($query);
   		if (isset($gcmd_keys[0]->gcmd_id))
   			return $gcmd_keys[0]->gcmd_id;
   		else 
   		{
   			$query2 = "select * from gcmd_science_keyword where lower(gcmd_name) = '".strtolower($categ)."' and " .
   				"gcm_gcmd_id in (select distinct gcmd_id from gcmd_science_keyword where lower(gcmd_name) = '".strtolower($topic)."')";
   			$gcmd_key = new gcmd_science_keyword;
   			$gcmd_keys = $gcmd_key->getByQuery($query2);
   			if (isset($gcmd_keys[0]->gcmd_id))
   				return $gcmd_keys[0]->gcmd_id;
   		}
   }
   
   function makeDatsVars($params,$derived)
   {
   		$dats_vars = array();
   		$i = 0;
   		foreach ($params as $param)
   		{
   			$dats_vars[$i] = new dats_var;
   			$dats_vars[$i]->variable = new variable;
   			if (!isset($param->unit) || empty($param->unit))
   			{
   				if (strpos((string)$param->variable,"-") > 0)
   					list($varname,$unitcode) = split('-',(string)$param->variable);
   				else
   				{
   					$varname = (string)$param->variable;
   					$unitcode = null;
   				}
   			}
   			else
   			{
   				$varname = (string)$param->variable;
   				$unitcode = (string)$param->unit;
   			}
   			$dats_vars[$i]->variable->var_name = $varname;
   			$dats_vars[$i]->unit = new unit;
   			if (isset($unitcode))
   				$dats_vars[$i]->unit = $dats_vars[$i]->unit->getByCode($unitcode);
   			if (!isset($dats_vars[$i]->unit))
   			{
   				$dats_vars[$i]->unit = new unit;
   				$dats_vars[$i]->unit->unit_id = 0;
   				$dats_vars[$i]->unit->unit_code = (string)$param->unit;
   			}
			$dats_vars[$i]->unit_id = $dats_vars[$i]->unit->unit_id;
   			$dats_vars[$i]->flag_param_calcule = $derived;
   			$dats_vars[$i]->variable->gcmd_id = getGcmdId((string)$param->topic,(string)$param->category,(string)$param->variable);
   			$i++;
   		}
   		return $dats_vars;
   }
   
   function makeDatsBoundings($point,$rectangle,$disc,$line)
   {
   		$bound = new boundings;
   		if (isset($point) && !empty($point))
   		{
   			$bound->west_bounding_coord = (double)$point[0]->lon;
   			$bound->east_bounding_coord = (double)$point[0]->lon;
   			$bound->north_bounding_coord = (double)$point[0]->lat;
   			$bound->south_bounding_coord = (double)$point[0]->lat;
   		}
   		else if (isset($rectangle) && !empty($rectangle))
   		{
   			$bound->west_bounding_coord = min((double)$rectangle[0]->lon1,(double)$rectangle[0]->lon2,(double)$rectangle[0]->lon3,(double)$rectangle[0]->lon4);
   			$bound->east_bounding_coord = max((double)$rectangle[0]->lon1,(double)$rectangle[0]->lon2,(double)$rectangle[0]->lon3,(double)$rectangle[0]->lon4);
   			$bound->north_bounding_coord = max((double)$rectangle[0]->lat1,(double)$rectangle[0]->lat2,(double)$rectangle[0]->lat3,(double)$rectangle[0]->lat4);
   			$bound->south_bounding_coord = min((double)$rectangle[0]->lat1,(double)$rectangle[0]->lat2,(double)$rectangle[0]->lat3,(double)$rectangle[0]->lat4);	
   		}
   		else if (isset($disc) && !empty($disc))
   		{
   			$bound->west_bounding_coord = (double)$disc[0]->lon;
   			$bound->east_bounding_coord = (double)$disc[0]->lon;
   			$bound->north_bounding_coord = (double)$disc[0]->lat;
   			$bound->south_bounding_coord = (double)$disc[0]->lat;
   		}
   		else if (isset($line) && !empty($line))
   		{
   			$bound->west_bounding_coord = min((double)$rectangle[0]->lon1,(double)$rectangle[0]->lon2);
   			$bound->east_bounding_coord = max((double)$rectangle[0]->lon1,(double)$rectangle[0]->lon2);
   			$bound->north_bounding_coord = max((double)$rectangle[0]->lat1,(double)$rectangle[0]->lat2);
   			$bound->south_bounding_coord = min((double)$rectangle[0]->lat1,(double)$rectangle[0]->lat2);
   		}
   		return $bound;
   }
   
   function makeDatsFormats($params,$derivedparams)
   {
   		$dats_formats = array();
   		$i = 0;
   		foreach($params as $param)
   		{
   			$dats_formats[$i] = new data_format;
   			$dats_formats[$i] = $dats_formats[$i]->getByName($param->format);
   			if (!isset($dats_formats[$i]))
   			{
   				$dats_formats[$i] = new data_format;
   				$dats_formats[$i]->data_format_id = 0;
   				$dats_formats[$i]->data_format_name = $param->format;
   			}
   			$i++;
   		}
   		foreach($derivedparams as $param)
   		{
   			$dats_formats[$i] = new data_format;
   			$dats_formats[$i] = $dats_formats[$i]->getByName($param->format);
   			if (!isset($dats_formats[$i]))
   			{
   				$dats_formats[$i] = new data_format;
   				$dats_formats[$i]->data_format_id = 0;
   				$dats_formats[$i]->data_format_name = $param->format;
   			}
   			$i++;
   		}
   		return $dats_formats;
   }
   
   function makeSensorVars($dataset,$precision,$derivedprecision)
   {
   		foreach ($dataset->dats_sensors as $dats_sensor)
   		{
   			$dats_sensor->sensor->sensor_vars = array();
   			$i = 0;
   			foreach($dataset->dats_variables as $dats_var)
   			{
   				$dats_sensor->sensor->sensor_vars[$i] = new sensor_var;
   				$dats_sensor->sensor->sensor_vars[$i]->variable = $dats_var->variable;
   				if ($dats_var->flag_param_calcule == 1)
   					$dats_sensor->sensor->sensor_vars[$i]->sensor_precision = $derivedprecision[0];
   				else
   					$dats_sensor->sensor->sensor_vars[$i]->sensor_precision = $precision[0];
   				if (!isset($dats_sensor->sensor->sensor_vars[$i]->sensor_precision) || 
   					empty($dats_sensor->sensor->sensor_vars[$i]->sensor_precision))
   					$dats_sensor->sensor->sensor_vars[$i]->sensor_precision = ' ';
   				$i++;
   			}
   		}
   }
   
   function makePlaceVars($dataset)
   {
   		foreach ($dataset->sites as $site)
   		{
   			$site->place_vars = array();
   			$i = 0;
   			foreach ($dataset->dats_variables as $dats_var)
   			{
   				$site->place_vars[$i] = new place_var;
   				$site->place_vars[$i]->variable = $dats_var->variable;
   				$i++;
   			}
   		}
   }
   
   function makeSensorPlace($dataset)
   {
   		foreach ($dataset->dats_sensors as $dats_sensor)
   		{
   			$dats_sensor->sensor->sensor_places = array();
   			$i = 0;
   			foreach($dataset->sites as $site)
   			{
   				$dats_sensor->sensor->sensor_places[$i] = new sensor_place;
   				$dats_sensor->sensor->sensor_places[$i]->place = $site;
   				$i++;
   			}
   		}
   }
   
   //mapping sensor_name -> gcmd
   $file_sensor = "liste_sensor.csv";
   $lines = file($file_sensor);
   $mapping_sensor_gcmd = array();
   foreach ($lines as $line)
   {
   		$tab = split(";",$line);
   		$mapping_sensor_gcmd["$tab[0]"] = $tab[1];
   }
   // mapping manufacturer
   $file_manufacturer = "liste_manufacturer.csv";
   $lines_manu = file($file_manufacturer);
   $mapping_manufacturer = array();
   $url_manufacturer = array();
   foreach($lines_manu as $line_manu)
   {
   		$tab = split(";",$line_manu);
   		$mapping_manufacturer["$tab[0]"] = $tab[1];
   		$url_manufacturer["tab[0]"] = $tab[2];
   }

   // Ouvre le dossier et liste tous les fichiers
  $dir = "/home/mastrori/Projets/MISTRALS/catalogue/HYMEX/fiches.utf";
  $count= 0;
  if (is_dir($dir))
  {
    if ($dh = opendir($dir))
    {
      while (($file = readdir($dh)) !== false)
      {
      	
        if( substr($file, 0, 6) == "fiche_")
        {
          if($fiche= simplexml_load_file($dir."/".$file, null,LIBXML_DTDVALID))
          {
            $count ++;
            
			$dataset = new dataset;
			$description = $fiche->xpath('description');
			$dataset->dats_title = makeDatsTitle($fiche->xpath('id'),$fiche->xpath('description/name'),
									$fiche->xpath('description/sitename'),
									$fiche->xpath('description/trademark'),
									$fiche->xpath('description/model')); 
			$dataset->projects = array();
			$dataset->projects[0] = new project;
			$dataset->projects[0]->project_id = 10; //hymex
			
			printf("***********************************************\n");
			printf("reading metadata : ".$dataset->dats_title."\n");
			
			$dataset->originators = makeDatsOriginators($fiche->xpath('description/contact'));
			$dataset->dats_version = (string)$description[0]->version;
			$dataset->dats_access_constraints = (string)$description[0]->availability;
			$dataset->dats_use_constraints = (string)$description[0]->policyspec;
			$date_creation = (string)$description[0]->datecreation;
			$dataset->dats_pub_date = substr($date_creation,0,4).'-'.substr($date_creation,4,2).'-'.substr($date_creation,-2);
			printf("datecreation : ".$dataset->dats_pub_date."\n");
			$dataset->dats_sensors = makeDatsSensor($fiche->xpath('description/name'),
										$fiche->xpath('description/trademark'),
										$fiche->xpath('description/model'),
										$fiche->xpath('temporalcoverage/tresolution'),
										$fiche->xpath('description/fixedmobile/fixed'),
										$fiche->xpath('geographicalcoverage/gresolution'),
										$mapping_sensor_gcmd,$mapping_sensor_gcmd,$url_manufacturer);
			
			$dataset->sites = makeDatsPlace($fiche->xpath('description/sitename'),$fiche->xpath('description/platform'));
			$dataset->dats_date_begin = makeDatsDate($fiche->xpath('temporalcoverage/since'));
			$dataset->dats_date_end = makeDatsDate($fiche->xpath('temporalcoverage/until'));
			$dataset->dats_variables = makeDatsVars($fiche->xpath('parameters/parameter'),0);
			$dataset->dats_variables = array_merge($dataset->dats_variables,makeDatsVars($fiche->xpath('derivedparameters/parameter'),1));
			$altitudes = $fiche->xpath('geographicalcoverage/altitudes');
			$dataset->dats_elevation_min = (double)$altitudes[0]->min;
			$dataset->dats_elevation_max = (double)$altitudes[0]->max;
			$dataset->boundings = makeDatsBoundings($fiche->xpath('geographicalcoverage/geographicalform/point'),
													$fiche->xpath('geographicalcoverage/geographicalform/rectangular'),
													$fiche->xpath('geographicalcoverage/geographicalform/disc'),
													$fiche->xpath('geographicalcoverage/geographicalform/line'));
			if (!isset($dataset->boundings) || empty($dataset->boundings))
				$dataset->boundings = makeDatsFixedBoundings($fiche->xpath('description/fixedmobile/fixed'));
			$tempcov = $fiche->xpath('temporalcoverage');
			$dataset->dats_abstract = (string)$tempcov[0]->mode;
			$dataset->data_formats = makeDatsFormats($fiche->xpath('parameters'),$fiche->xpath('derivedparameters'));
			makeSensorVars($dataset,$fiche->xpath('parameters/precision'),$fiche->xpath('derivedparameters/precision'));
			makePlaceVars($dataset);
			makeSensorPlace($dataset);
			$dataset->insert();
          }
          else echo $file." Mal Formed\n";
        }
      }
      closedir($dh);
    }
  }
 
?>
