<?php
require_once ('conf/conf.php');
define('MISSING_VALUE','NA');
//define(MAP_PATH,"");

class map_form{

	//Centre
	var $lat = 50;
	var $lon = 15;
	
	//Dimensions carte
	var $width = 640;
	var $height = 640;
	var $zoomLvl = 4;


	private function includeScripts(){
		echo '<link href="/gmap/gmap.css" rel="stylesheet" type="text/css" />';
		echo '<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=false"></script>';
		echo '<script type="text/javascript" src="/gmap/infobox.js"></script>';
		echo '<script type="text/javascript" src="/gmap/gmap.js"></script>';
	}
	
	private function openScriptElt(){
		echo '<script type="text/javascript">';
                echo 'function draw(){';
                echo 'var d = document.getElementById("map_canvas");d.style.width="'.$this->width.'px";d.style.height="'.$this->height.'px";';
                echo "initializeGMap($this->lat,$this->lon,$this->zoomLvl);";
                //echo "var a = document.getElementById('show_map_canvas');a.style.visibility='hidden';";
		//echo 'hideLink();';
	}

	private function closeScriptElt(){
		echo '}</script>';
	}

	function displayDrawLink($txt = 'Locate on a map'){
		echo '<a id="show_map_canvas" style="cursor: pointer;" onclick="draw();hideLink();">'.$txt.'<a/>';
	}

	function displayMapDiv(){
                echo '<a name="map"/><div id="map_canvas" ></div>';
        }

	function drawStationsFromUrl($url){
                if ($this->genScriptFromUrl($url)){
                        $this->displayDrawLink('Locate stations on a map');
                        $this->displayMapDiv();
                }
        }

        function genScriptFromUrl($url){
        	if ( (strpos($url,'file://localhost'.MAP_PATH.'/') === 0) ){
        		$file = str_replace('file://localhost','',$url);
        		if (file_exists($file)){
        			$this->includeScripts();
        			$this->openScriptElt();
        			echo "addMarkers();";
        			$this->closeScriptElt();
        			$this->readStations($file);
        			return true;
        		}else{
        			echo "File not found: $file";
        		}
        	}else{
        		echo "Bad url: $url.";
        	}
        	return false;
        }
            
	/**
	 * 
	 * return true en cas de succès
	 */
        function genScriptFromSite($site, $mapFileUrl = null){
        	$withBox = (isset($site->west_bounding_coord) && strlen($site->west_bounding_coord) > 0)
        				|| (isset($site->east_bounding_coord) && strlen($site->east_bounding_coord) > 0)
        				|| (isset($site->north_bounding_coord) && strlen($site->north_bounding_coord) > 0)
        				|| (isset($site->south_bounding_coord) && strlen($site->south_bounding_coord) > 0) ;

        	$withMarkers = false;
        	if ( isset($mapFileUrl) && (strpos($mapFileUrl,'file://localhost'.MAP_PATH.'/') === 0) ){
        		$file = str_replace('file://localhost','',$mapFileUrl);
        		if (file_exists($file)){
        			$this->readStations($file);
        			$withMarkers = true;
        		}else{
        			echo "File not found: $file";
        		}
        	}
        	
        	if ($withBox || $withMarkers){
        		if ($withBox){
        			$this->zoomLvl = 7;
        			$this->lon = ($site->east_bounding_coord + $site->west_bounding_coord) / 2.0;
        			$this->lat = ($site->north_bounding_coord + $site->south_bounding_coord) / 2.0;
        		}
        		$this->includeScripts();
        		$this->openScriptElt();

        		if ($withBox){
        			echo 'addZone('.$site->west_bounding_coord.', '.$site->east_bounding_coord
        					.', '.$site->south_bounding_coord.', '.$site->north_bounding_coord.');';
        		}

        		if ($withMarkers){
        			echo "addMarkers();";
        		}
        		
        		$this->closeScriptElt();
        		return true;
        	}
        	return false;
        }

	function drawSite($site){

		if ($this->genScriptFromSite($site)){
			$this->displayDrawLink('Locate site on a map');
			$this->displayMapDiv();
		}
	}


	function genScriptFromSensors($sensors){
		$ret = false;
                if (isset($sensors) && count($sensors) > 0){
                        $this->includeScripts();
                        $this->openScriptElt();
                        foreach ($sensors as $sensor){
                                $ret = $this->addInstrument($sensor,count($sensors) == 1) || $ret;
                        }
                        $this->closeScriptElt();
                }
		return $ret;
        }

	function genScriptFromSensor($sensor){
		return $this->genScriptFromSensors(array($sensor));
	}
/*
	function drawInstruments($sensors){
		if ($this->genScriptFromSensors($sensors)){
			$this->displayDrawLink('Locate instrument'.((count($sensors) > 1)?'s':'').' on a map');
			$this->displayMapDiv();
		}
	}
*/
	private function addInstrument($sensor, $unique = false){
		if (isset($sensor->boundings->north_bounding_coord) && isset($sensor->boundings->west_bounding_coord)){
			$lat = $sensor->boundings->north_bounding_coord;
                        $lon = $sensor->boundings->west_bounding_coord;
			
			if ( isset($sensor->gcmd_instrument_keyword->gcmd_sensor_name) ){
                                $nom = $sensor->gcmd_instrument_keyword->gcmd_sensor_name;
                        }else{
                                $nom = null;
                        }
                  /*      $info = '"<table><tr><td><b>Instrument type</b></td><td>'.$sensor->gcmd_instrument_keyword->gcmd_sensor_name.'</td></tr>'
				//.'<tr><td><b>Id</b></td><td>'.$sensor->sensor_id.'</td></tr>'
				.'<tr><td><b>Latitude:</b></td><td>'.round($lat,3).'</td></tr>'
                                .'<tr><td><b>Longitude:</b></td><td>'.round($lon,3).'</td></tr>'
				.'<tr><td><b>Manufacturer</b></td><td>'.$sensor->manufacturer->manufacturer_name.'</td></tr>'
				.'<tr><td><b>Model</b></td><td>'.$sensor->sensor_model.'</td></tr><table>"';
		*/
			 $info = '"<div id=\"map_window_info\">'
                                .'<b>Latitude:</b> '.round($lat,3)
                                .'<br/><b>Longitude:</b> '.round($lon,3)
//                                .(($alt == MISSING_VALUE)?'':'<br/><b>Altitude (m):</b> '.round($alt,3))
				.'<br/><br/><b>Instrument type:</b> '.$sensor->gcmd_instrument_keyword->gcmd_sensor_name
				.'<br/><b>Manufacturer:</b> '.$sensor->manufacturer->manufacturer_name
                                .'<br/><b>Model:</b> '.$sensor->sensor_model
                                .'</div>"';
	
			//$info = 'null';
                        $color = 'red';
			
			if ( isset($nom) && substr($nom,0,5) == 'RADAR'){
                                echo "addRadarZone($lat,$lon);";
                        }
                        echo "addMark(\"$nom\",$lat,$lon,$info,\"$color\");";

			if ($unique){
				echo "changeMapCenter($lat,$lon);";
				echo "map.setZoom(7);";
			}
			return true;
		}
		return false;
	}
/*
	function drawInstrument($sensor){
		$this->drawInstruments(array($sensor));
	}
*/	
	/**
	 * Gnère la fonction addMarkers() à partir d'une liste de stations.
	 */	
	private function readStations($file){	 	
	        $lignes = file($file);
        	$cpt = 0;

        echo '<script type="text/javascript">';
        echo 'function addMarkers(){';        
        foreach ($lignes as $ligne){
		if (substr($ligne,0,1) == '#') {
       			$infos = explode(' ',trim($ligne));
       			if (count($infos) >= 3){
       				switch ($infos[1]){
       					case 'Zoom':
      						echo "map.setZoom($infos[2]);";
       						break;
       					case 'Center':
       						if (count($infos) == 4){
       							echo "map.setCenter(new google.maps.LatLng($infos[2],$infos[3]));";
       						}
       						break;	
        				}	
        			}
       		}else{
	                $cpt++;
               		$infos = explode(';',trim($ligne));
	                $nom = $infos[0].' - '.$infos[1];
               		$lat = $infos[2];
	                $lon = $infos[3];
			$alt = $infos[4];
                
	                $info = '"<div id=\"map_window_info\">'
				//'"<div>'
				.'<b>'.$infos[1].'</b>'
				.'<br/><br/><b>Station id:</b> '.$infos[0]
	                        .'<br/><b>Latitude:</b> '.round($lat,3)
               		        .'<br/><b>Longitude:</b> '.round($lon,3)
	                        .(($alt == MISSING_VALUE)?'':'<br/><b>Altitude (m):</b> '.round($alt,3))
               		        .'</div>"';

	                $color = 'red';
               		echo "addMark(\"$nom\",$lat,$lon,$info,\"$color\");";
	                //if ($cpt > 50) break;
		}
        }
        
        echo '}</script>';
	 }
	
}

?>
