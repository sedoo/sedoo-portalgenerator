var map;

function initializeGMap(lat,lon,zoomLvl) {
	var centre = new google.maps.LatLng(lat, lon);
	var myOptions = {
			zoom: zoomLvl,
			center: centre,
			mapTypeId: google.maps.MapTypeId.TERRAIN
	};
	map = new google.maps.Map(document.getElementById("map_canvas"),
			myOptions);
}

function changeMapCenter(lat,lon){
	map.setCenter(new google.maps.LatLng(lat,lon))
}

function addMark(title,lat,lon,info,pinColor){
	var latLon = new google.maps.LatLng(lat,lon);
	var ServerHost= document.location.origin;
	switch (pinColor) {
	case 'orange':
		img = ServerHost+"/gmap/img/point-orange.png"
			break;
	case 'vert':
	case 'green':
		img = ServerHost+"/gmap/img/point-vert.png"
			break;
	case 'bleu':
	case 'blue':
		img = ServerHost+"/gmap/img/point-bleu.png"
			break;
	default: 
		img = ServerHost"/gmap/img/point-rouge.png";
	break;
	}
	var pinImage = new google.maps.MarkerImage(img,new google.maps.Size(12, 12),new google.maps.Point(0, 0),new google.maps.Point(6, 6));  	
	var markOptions = {
			position: latLon,
			map: map,
			icon: pinImage
	};
	var marker = new google.maps.Marker(markOptions);
	var boxText = document.createElement("div");
	boxText.style.cssText = "border: 1px solid black; margin-top: 8px; background: white; padding: 5px;padding-right:20px;";
	boxText.innerHTML = info;
	var myOptions = {
			content: boxText
			,disableAutoPan: false
			,maxWidth: 0
			,pixelOffset: new google.maps.Size(-40, 0)
	,zIndex: null
	,boxStyle: { 
		background: "url('tipbox.gif') no-repeat"
		,opacity: 0.9
	}
	,closeBoxMargin: "10px 2px 2px 2px"
		,closeBoxURL: "http://www.google.com/intl/en_us/mapfiles/close.gif"
			,infoBoxClearance: new google.maps.Size(1, 1)
	,isHidden: false
	,pane: "floatPane"
		,enableEventPropagation: false
	};
	google.maps.event.addListener(marker, "click", function (e) {
		ib.open(map, this);
		var selImage = new google.maps.MarkerImage(ServerHost+"/gmap/img/point-noir.png",new google.maps.Size(12, 12),new google.maps.Point(0, 0),new google.maps.Point(6, 6));
		marker.setIcon(selImage);
	});
	var ib = new InfoBox(myOptions);
	google.maps.event.addListener(ib, "closeclick", function (e) {
		var newImage = new google.maps.MarkerImage(img,new google.maps.Size(12, 12),new google.maps.Point(0, 0),new google.maps.Point(6, 6));
		marker.setIcon(newImage);
		ib.close();
	});
}

function addZone(west,east,south,north){
	var coords = [
	              new google.maps.LatLng(south,west),
	              new google.maps.LatLng(north,west),
	              new google.maps.LatLng(north, east),
	              new google.maps.LatLng(south, east),
	              new google.maps.LatLng(south,west)
	              ];
	rectangle = new google.maps.Polygon({
		paths: coords,
		map: map,
		strokeColor: "#FF0000",
		strokeOpacity: 0.8,
		strokeWeight: 1,
		fillColor: "#FF0000",
		fillOpacity: 0.15
	});
}

function addRadarZone(lat,lon){
	cercle = new google.maps.Circle({
		map: map,
		center: new google.maps.LatLng(lat,lon),
		radius: 100000,
		strokeColor: "#FF0000",
		strokeOpacity: 0.4,
		strokeWeight: 0.5,
		fillColor: "#FF0000",
		fillOpacity: 0.25
	});

	cercle2 = new google.maps.Circle({
		map: map,
		center: new google.maps.LatLng(lat,lon),
		radius: 250000,
		strokeColor: "#FF0000",
		strokeOpacity: 0.2,
		strokeWeight: 0.3,
		fillColor: "#FF0000",
		fillOpacity: 0.1
	});
}
function hideLink(){
	var a = document.getElementById('show_map_canvas');
	a.style.visibility='hidden';
}



