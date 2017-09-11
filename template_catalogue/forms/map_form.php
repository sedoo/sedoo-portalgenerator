<?php
require_once 'login_form.php';

class map_form extends login_form{
	
	var $latMin;
	var $latMax;
	var $lonMin;
	var $lonMax;
	
	function createFormMap($defaultLatMin = MAP_DEFAULT_LAT_MIN, $defaultLatMax = MAP_DEFAULT_LAT_MAX,
		$defaultLonMin = MAP_DEFAULT_LON_MIN, $defaultLonMax = MAP_DEFAULT_LON_MAX){		
		$this->addElement('hidden','minLat', $defaultLatMin);
		$this->addElement('hidden','maxLat', $defaultLatMax);
		$this->addElement('hidden','minLon', $defaultLonMin);
		$this->addElement('hidden','maxLon', $defaultLonMax);
		$this->addElement('hidden','startMinLat', $defaultLatMin);
		$this->addElement('hidden','startMaxLat', $defaultLatMax);
		$this->addElement('hidden','startMinLon', $defaultLonMin);
		$this->addElement('hidden','startMaxLon', $defaultLonMax);
		$this->addElement('hidden','defaultMinLat', MAP_DEFAULT_LAT_MIN);
		$this->addElement('hidden','defaultMaxLat', MAP_DEFAULT_LAT_MAX);
		$this->addElement('hidden','defaultMinLon', MAP_DEFAULT_LON_MIN);
		$this->addElement('hidden','defaultMaxLon', MAP_DEFAULT_LON_MAX);
		if ($_POST['minLat']) $this->getElement('startMinLat')->setValue($_POST['minLat']);
		if ($_POST['maxLat']) $this->getElement('startMaxLat')->setValue($_POST['maxLat']);
		if ($_POST['minLon']) $this->getElement('startMinLon')->setValue($_POST['minLon']);
		if ($_POST['maxLon']) $this->getElement('startMaxLon')->setValue($_POST['maxLon']);
		$this->addElement('text','maxLatDeg','Lat: ',array('id'=>'maxLatDeg','size'=>3));
		$this->addElement('text','minLatDeg','Lat: ',array('id'=>'minLatDeg','size'=>3));
		$this->addElement('text','maxLonDeg','Lon: ',array('id'=>'maxLonDeg','size'=>3));
		$this->addElement('text','minLonDeg','Lon: ',array('id'=>'minLonDeg','size'=>3));
		$this->addElement('text','maxLatMin','',array('id'=>'maxLatMin','size'=>2));
		$this->addElement('text','minLatMin','',array('id'=>'minLatMin','size'=>2));
		$this->addElement('text','maxLonMin','',array('id'=>'maxLonMin','size'=>2));
		$this->addElement('text','minLonMin','',array('id'=>'minLonMin','size'=>2));
		$this->addElement('text','maxLatSec','',array('id'=>'maxLatSec','size'=>2));
		$this->addElement('text','minLatSec','',array('id'=>'minLatSec','size'=>2));
		$this->addElement('text','maxLonSec','',array('id'=>'maxLonSec','size'=>2));
		$this->addElement('text','minLonSec','',array('id'=>'minLonSec','size'=>2));
		$this->addElement('button','unzoom','UnZoom',array('onclick' => "unZoomPortal()"));
	}
	
	function saveFormMap(){
		$this->latMin = $this->deg2Double($this->exportValue('minLatDeg'),$this->exportValue('minLatMin'),$this->exportValue('minLatSec'));
		$this->latMax = $this->deg2Double($this->exportValue('maxLatDeg'),$this->exportValue('maxLatMin'),$this->exportValue('maxLatSec'));
		$this->lonMin = $this->deg2Double($this->exportValue('minLonDeg'),$this->exportValue('minLonMin'),$this->exportValue('minLonSec'));
		$this->lonMax = $this->deg2Double($this->exportValue('maxLonDeg'),$this->exportValue('maxLonMin'),$this->exportValue('maxLonSec'));
	}
	
	function addValidationRulesMap(){
		$this->addRule('maxLatDeg','Latitude &deg; must be numeric','numeric');
		$this->addRule('maxLatDeg','Latitude &deg; is incorrect','number_range',array(-90,90));
		$this->addRule('minLatDeg','Latitude &deg; must be numeric','numeric');
		$this->addRule('minLatDeg','Latitude &deg; is incorrect','number_range',array(-90,90));
		$this->addRule('maxLonDeg','Longitude &deg; must be numeric','numeric');
		$this->addRule('maxLonDeg','Longitude &deg; is incorrect','number_range',array(-180,180));
		$this->addRule('minLonDeg','Longitude &deg;  must be numeric','numeric');
		$this->addRule('minLonDeg','Longitude &deg; is incorrect','number_range',array(-180,180));
		$this->addRule('maxLatMin','Latitude \' must be numeric','numeric');
		$this->addRule('maxLatMin','Latitude \' is incorrect','number_range',array(0,59));
		$this->addRule('minLatMin','Latitude \' must be numeric','numeric');
		$this->addRule('minLatMin','Latitude \' is incorrect','number_range',array(0,59));
		$this->addRule('maxLonMin','Longitude \' must be numeric','numeric');
		$this->addRule('maxLonMin','Longitude \'  is incorrect','number_range',array(0,59));
		$this->addRule('minLonMin','Longitude \'  must be numeric','numeric');
		$this->addRule('minLonMin','Longitude \' is incorrect','number_range',array(0,59));
		$this->addRule('maxLatSec','Latitude " must be numeric','numeric');
		$this->addRule('maxLatSec','Latitude " is incorrect','number_range',array(0,59));
		$this->addRule('minLatSec','Latitude " must be numeric','numeric');
		$this->addRule('minLatSec','Latitude " is incorrect','number_range',array(0,59));
		$this->addRule('maxLonSec','Longitude " must be numeric','numeric');
		$this->addRule('maxLonSec','Longitude " is incorrect','number_range',array(0,59));
		$this->addRule('minLonSec','Longitude " must be numeric','numeric');
		$this->addRule('minLonSec','Longitude " is incorrect','number_range',array(0,59));
	}
	
	function displayFormMap($title = ''){
		echo $this->getElement('minLat')->toHTML();
		echo $this->getElement('maxLat')->toHTML();
		echo $this->getElement('minLon')->toHTML();
		echo $this->getElement('maxLon')->toHTML();
		echo $this->getElement('startMinLat')->toHTML();
		echo $this->getElement('startMaxLat')->toHTML();
		echo $this->getElement('startMinLon')->toHTML();
		echo $this->getElement('startMaxLon')->toHTML();
		echo $this->getElement('defaultMinLat')->toHTML();
		echo $this->getElement('defaultMaxLat')->toHTML();
		echo $this->getElement('defaultMinLon')->toHTML();
		echo $this->getElement('defaultMaxLon')->toHTML();
		echo '<div id="line1"></div><div id="line2"></div>
                <table border="0">
                </table>
                <table>
                <tr>
                        <td width=400 style="text-align: left; vertical-align: top;">
                                <div id="mapContainer">
                                <div id="mapPoints" style="position:absolute;width:400px;"></div>
                                <div id="redPoint" style="position:absolute;"></div>
                                <div id="boxTitle" class="">'.$title.'</div>
                                <div id="map" style="cursor: crosshair;width:400px;height:200px;">
                                <div id="selDraw">
                                <div id="selectionBox" style="position:relative;visibility:hidden;">
                                <div id="boxBorder" style="border-width: 2; position:relative;width:100%;height:100%; border-color: #000000; border-style: solid;">
                                <div id="boxBack" style="background-color: transparent; -moz-opacity:0.5; filter:Alpha(Opacity=50); opacity:0.50; width:100%; height:100%;">
                                </div></div></div></div></div></div>
                        </td>
                        <td valign="top">
                                <div id="mouseCoord" style="position:relative;padding:15px 0px 15px 0px;">
                                <div id="boxTitle">Mouse position</div>
                                <table border="0" width="99%">
                                <tr><td style="vertical-align:middle;font-size:12px;">Lat :<br> <a id="yval"></a><br>Lon :<br> <a id="xval"></a></td>
                                <td style="vertical-align:middle"><img src="/img/mousePos.gif"></td></tr>
                                </table>
                                </div>
                                <div style="position:relative;padding:0px 0px 15px 60px;">'.$this->getElement('unzoom')->toHTML().'</div>
                                <div id="zoom" style="position:relative;">
                                <div id="boxTitle">Zoom</div>
                                <div id="msg" class="INFO" style=""></div>
                                <table width="99%" style="font-size:10px"><tr><td align="left" style="white-space:nowrap;">
                                '.$this->getElement('maxLatDeg')->getLabel().''.$this->getElement('maxLatDeg')->toHTML().'&#176;'
								.$this->getElement('maxLatMin')->toHTML().'\''.$this->getElement('maxLatSec')->toHTML().'" <br>
                                '.$this->getElement('minLonDeg')->getLabel().' '.$this->getElement('minLonDeg')->toHTML().'&#176;'
                                .$this->getElement('minLonMin')->toHTML().'\''.$this->getElement('minLonSec')->toHTML().'"
                                <br></td></tr><tr><td border="0px"><img style="padding:0px 30px 0px 30px;" src="/img/zoomBox.gif"></td></tr><tr><td align="right" style="white-space:nowrap;">
                                '.$this->getElement('minLatDeg')->getLabel().' '.$this->getElement('minLatDeg')->toHTML().'&#176;'
								.$this->getElement('minLatMin')->toHTML().'\''.$this->getElement('minLatSec')->toHTML().'" <br>
                                '.$this->getElement('maxLonDeg')->getLabel().' '.$this->getElement('maxLonDeg')->toHTML().'&#176;'
								.$this->getElement('maxLonMin')->toHTML().'\''.$this->getElement('maxLonSec')->toHTML().'"
                                <br>
                                </td></tr>
                                </table>
                                <br>
                            </td>
                     </tr></table>';
	}
	
	function deg2Double($deg,$min,$sec){
		if($deg=="")$deg=0;
		if($min=="")$min=0;
		if($sec=="")$sec=0;
		$d=intval($deg);
		$m=intval($min);
		$s=intval($sec);
		if($d!=0){
			$sign=$d/abs($d);
		}else if($deg.length>1 && $deg.substring(0,1)=="-"){
			$sign=-1;
		}else{
			$sign=1;
		}
		return ($d+$sign*$m/60+$sign*$s/3600)*10000;
	}
	
}

?>