/* ******* VARIABLES ******* */

// Detect if the browser is IE or not.
var IE = document.all?true:false

//coordonn?es de la box de d?part
var startLATmin;
var startLONmin;
var startLATmax;
var startLONmax;

//dimensions initiales de la carte
var startImgLarg; 
var startImgHaut; 

//coordonn?es de la box actuelles
var LATmin = -90;
var LONmin = -90;
var LATmax = 90;
var LONmax = 90;

//Dimensions de l'image
var imgLarg;
var imgHaut;

//d?calage pour netscape (car les border sont inclus)
var decal = 0;

//Positions de la souris
var tempX = 0;
var tempY = 0;
var startX = 0;
var startY = 0;

//les objets
var box;
var boxBorder;
var map;
var mapContainer;
var boxBack;
var lonVal;
var latVal;

//positions
var posXmap;
var posYmap;

//coordonn?es des bordures de la carte
var imgLATmin;
var imgLONmin;
var imgLATmax;
var imgLONmax;

var line1;
var line2;
var canvas;

var initOk = false;

//a true si le premier choix (zones pr?d?finies) est s?lectionn?
var choix1 = false;

/* ******* FONCTIONS D'INITIALISATION ******* */
var urlMap;

//Fonction appel�e au chargement de la page (onload du tag body)
//Initialise l'url de la cgi qui g�n�re la carte
function initUrl(url){
	urlMap = url;
}

//Fonction appel�e au chargement de la page (onload du tag body) si version=simple
function initSimple(){
	choix1=false;
	initMap();	
	activeMap();
}

//Fonction appel�e au chargement de la page (onload du tag body) si version=withAreas
function initWithAreas(){
	canvas = new jsGraphics("selDraw");
	initMap();
	activeMap();	
	//updateZones();
}

//Initialisation des variables et des actions
function initMap(){
	var form;
        if (document.forms["frmmap"] != null)
            form = document.forms["frmmap"];
        else
                form = document.forms[0];

	if (!IE) document.captureEvents(Event.MOUSEMOVE);

	document.onmousemove = mousePos;

	//les objets
	box=document.getElementById("selectionBox");
	boxBorder=document.getElementById("boxBorder");
	map=document.getElementById("map");	
	mapContainer=document.getElementById("mapContainer");
	boxBack=document.getElementById("boxBack");
	lonVal=document.getElementById("xval");
	latVal=document.getElementById("yval");
	
	//positions
	posXmap=findPosX(map);
	posYmap=findPosY(map);
	
	//petit d?calage sous netscape
  	if(!IE && boxBorder.style.borderWidth) decal = parseInt(boxBorder.style.borderWidth)*2;
	/*alert(boxBorder.style.borderWidth);
	alert(decal);*/
	
  	//utilisation de la transprence si c'est permis
	if(boxBack.style.opacity!=null){
		boxBack.style.backgroundColor='#ddddff';
	}
	if(boxBack.style.filter!=null){
		boxBack.style.backgroundColor='#ddddff';
	}
	if(boxBack.style.MozOpacity!=null){
		boxBack.style.backgroundColor='#ddddff';
	}
	
	//misc
	startImgLarg = parseInt(map.style.width); 
	startImgHaut = parseInt(map.style.height);
	
	if (isNaN(startImgLarg))
		startImgLarg = 400;
	if (isNaN(startImgHaut))
		startImgHaut = 400;
	
	imgLarg=startImgLarg;
	imgHaut=startImgHaut;
			
	//on enregistre les bordures de la nouvelle carte MODIFIE
	LATmin=parseFloat(form.startMinLat.value);
	LATmax=parseFloat(form.startMaxLat.value);
	LONmin=parseFloat(form.startMinLon.value);
	LONmax=parseFloat(form.startMaxLon.value);

}

/* ******* FONCTIONS DE GESTION DES PANNEAUX ******* */

//Carte et zoom
function activeMap(){
	document.getElementById("map").onmousedown=onDownMap;
	document.getElementById("mapContainer").style.visibility='visible';
	document.getElementById("zoom").style.visibility='visible';
	map.style.cursor="crosshair";	
	changeMap();
}

//Carte affich?e, mais pas de zoom possible
function desactiveMap(){
	document.getElementById("zoom").style.visibility='hidden';
	document.getElementById("mouseCoord").style.visibility='hidden';
	map.onmousedown="";
	document.onmousemove = "";
	map.style.cursor="default";
}


/* ******* FONCTIONS DE DESSINS DES ZONES ******* */

//Dessine les zones correspondant aux valeurs du champ hidden selZones
//(zones s?par?es par des |)
function drawZones(){
	unZoom();
	canvas.clear();
	var form;
        if (document.forms["frmmap"] != null)
            form = document.forms["frmmap"];
        else
                form = document.forms[0];
	form.minLat.value = "90";
	form.maxLat.value = "-90";
	form.minLon.value = "180";
	form.maxLon.value = "-180";
	var checked = false;
	var i=0;
	var selZonesInput=document.getElementById("zone_"+i);
	while (selZonesInput != null)
	{
		if(selZonesInput.checked){
			checked = true;
			var tab_coords=selZonesInput.value.split(";");
			for (j = 0; j < tab_coords.length; j++)
			{
				var tab=tab_coords[j].split("|");
				drawRectangle(findXPos(tab[2]),findYPos(tab[1]),findXPos(tab[3]),findYPos(tab[0]));
				if (parseFloat(form.minLat.value) > parseFloat(tab[0]))
				{
					form.minLat.value = tab[0];
					//alert("minLat = "+tab[0]);
				}
				if (parseFloat(form.maxLat.value) < parseFloat(tab[1]))
				{
					 form.maxLat.value = tab[1];
					 //alert("maxLat = "+tab[1]);
				}
				if (parseFloat(form.minLon.value) > parseFloat(tab[2]))
				{
					 form.minLon.value = tab[2];
					 //alert("minLon = "+tab[2]);
				}
				if (parseFloat(form.maxLon.value) < parseFloat(tab[3]))
				{
					 form.maxLon.value = tab[3];
					 //alert("maxLon = "+tab[3]);
				}
			}
		}
		i++;
		selZonesInput=document.getElementById("zone_"+i);
		
	}
	if (checked)
		desactiveMap();
	else
	{
		initMap();
		activeMap();
	}
}

//Dessine dans canvas un rectangle de couleur #0085c0 (bleu).
function drawRectangle(x1,x2,x3,x4){
	canvas.setStroke(2);
  	canvas.setColor("#0085c0"); 
  	canvas.drawRect(x1,x2,parseInt(x3)-parseInt(x1),parseInt(x4)-parseInt(x2)); // co-ordinates related to "canvas"
  	canvas.paint();
}

/* ******* EVENEMENTS LIES A LA SOURIS ******* */

var isDown=false;

//Lors du clic sur la carte
function onDownMap(e){
	// Set-up to use getMouseXY function onMouseMove
	document.onmousemove = getMouseXY;
	// Set-up to use getMouseXY function onMouseMove
	document.onmouseup = onUpMap;
	
	isDown=true;
	//show the box at the starting place if it is the first move
	box.style.visibility='visible';
	box.style.top=tempY+'px';
	startY=tempY;
	box.style.left=tempX+'px';
	startX=tempX;
	box.style.width=0+'px';
	box.style.height=0+'px';
}

//Lors du relachement du bouton de la souris sur la carte (et ailleurs dans la page d'ailleurs)
function onUpMap(e){
	//on change les events
	document.onmousemove = mousePos;
	document.onmouseup = "";
	
	isDown=false;
	/*alert(box.style.left+"-"+box.style.width+"-"+box.style.top+"-"+box.style.height);
	alert(box.style.left.substring(0,box.style.left.length-2)+"-"+box.style.width.substring(0,box.style.width.length-2)+"-"+box.style.top.substring(0,box.style.top.length-2)+"-"+box.style.height.substring(0,box.style.height.length-2));
  alert(parseInt(box.style.left.substring(0,box.style.left.length-2)));
  alert(parseInt(box.style.width.substring(0,box.style.width.length-2)));
  alert(decal);*/
	//enregistrement des coordonn?es de la box (en pixel)
  	LONmax=parseInt(box.style.left.substring(0,box.style.left.length-2))+parseInt(box.style.width.substring(0,box.style.width.length-2))+decal;
  	LATmin=parseInt(box.style.top.substring(0,box.style.top.length-2))+parseInt(box.style.height.substring(0,box.style.height.length-2))+decal;
  	LATmax=parseInt(box.style.top.substring(0,box.style.top.length-2));
  	LONmin=parseInt(box.style.left.substring(0,box.style.left.length-2));
  	
  	//document.getElementById("tmpMsg").innerHTML="box: "+LATmin+"-"+LATmax+"-"+LONmin+"-"+LONmax;
  	//alert("box: "+LATmin+"-"+LATmax+"-"+LONmin+"-"+LONmax);
  	if(LONmax-LONmin+LATmin-LATmax>20){
	
		//on transforme les dimension du carr? en Lat-Lon
		LATmin=findLat(LATmin);
		LATmax=findLat(LATmax);
		LONmax=findLon(LONmax);
		LONmin=findLon(LONmin);
		
		//document.getElementById("tmpMsg").innerHTML+=" => "+LATmin+"-"+LATmax;
		
		//on met ? jour l'affichage (carte...)
		box.style.visibility='hidden';
				
		changeMap();
		//simulateApply();

	}else{//on annule si la zone s?lectionn? est trop petite (un clic par exemple)
		box.style.visibility='hidden';
	}
	document.getElementById("maxLatDeg").focus();
	mousePos(e);
	
}

var compteur=0;
// Main function to retrieve mouse x-y pos.s
function getMouseXY(e) {

	//positions
	//posXmap=findPosX(map);
	//posYmap=findPosY(map);

	//position de la souris
  	if (IE) { // grab the x-y pos.s if browser is IE
    	tempX = event.clientX + document.body.scrollLeft-posXmap
    	tempY = event.clientY + document.body.scrollTop-posYmap
	} else {  // grab the x-y pos.s if browser is NS
    	tempX = e.pageX-posXmap;
    	tempY = e.pageY-posYmap;
  	}
  	
	//On ne met ? jour la position de la souris que tous les 3 coups : am?liore la fluidit?
	compteur++;
	if(compteur==3){
		mousePos(e);
		compteur=0;
	}
  
  	// catch possible negative values in NS4
  	if (tempX < 0){tempX = 0}
  	if (tempY < 0){tempY = 0}
	
  	//empeche les d?bordements
	if(tempX+decal>imgLarg) tempX=imgLarg-decal;
	if(tempY+decal>imgHaut) tempY=imgHaut-decal;	
	if(tempY<0) tempY=0;
	if(tempX<0) tempX=0;
	
	//change la taille de la box
	var width=tempX-startX;
	var height=tempY-startY;
		
	//on gere les valeurs n?gatives
	if(width<0){
		box.style.left=tempX+'px';
		box.style.width=(-1*width)+'px';
	}else{
		box.style.left=startX+'px';
		box.style.width=width+'px';
	}
	if(height<0){
		box.style.top=tempY+'px';
		box.style.height=(-1*height)+'px';
	}else{
		box.style.top=startY+'px';
		box.style.height=height+'px';
	}
	
  	return true;
}

/* ******* ACTIONS ******* */

//fonction de d?-zoomage
function unZoomNoSubmit(){
	var form;
        if (document.forms["frmmap"] != null)
            form = document.forms["frmmap"];
        else
                form = document.forms[0];
	//on enregistre les bordures de la nouvelle carte
	LATmin=parseFloat(form.startMinLat.value);
	LATmax=parseFloat(form.startMaxLat.value);
	LONmin=parseFloat(form.startMinLon.value);
	LONmax=parseFloat(form.startMaxLon.value);
	changeMap();
}

function unZoomPortal(){
	var form;
        if (document.forms["frmmap"] != null)
            form = document.forms["frmmap"];
        else
                form = document.forms[0];
	LATmin=parseFloat(form.defaultMinLat.value);
	LATmax=parseFloat(form.defaultMaxLat.value);
	LONmin=parseFloat(form.defaultMinLon.value);
	LONmax=parseFloat(form.defaultMaxLon.value);
	changeMap();
}


//Fonction qui correspond au bouton unzoom
function unZoom(){
	unZoomNoSubmit();
}

//Fonction qui correspond au bouton apply
function apply(){
	//r?up?ration des donn?es entr?es par l'utilisateur
	LATmin=dmsToDd(document.getElementById("minLatDeg").value,document.getElementById("minLatMin").value,document.getElementById("minLatSec").value);
	LATmax=dmsToDd(document.getElementById("maxLatDeg").value,document.getElementById("maxLatMin").value,document.getElementById("maxLatSec").value);
	LONmin=dmsToDd(document.getElementById("minLonDeg").value,document.getElementById("minLonMin").value,document.getElementById("minLonSec").value);
	LONmax=dmsToDd(document.getElementById("maxLonDeg").value,document.getElementById("maxLonMin").value,document.getElementById("maxLonSec").value);

	changeMap();
}

//Simule un click sur le bouton apply.
function simulateApply(){
	if (IE){
		simulateClickIE("applyButton");
	}else{
		simulateClickFF("applyButton");
	}
}


//affiche la carte en fonction des LATmin etc...
function changeMap(){
	var form;
        if (document.forms["frmmap"] != null)
            form = document.forms["frmmap"];
        else
                form = document.forms[0];

	document.getElementById("msg").innerHTML="";

	if( (LONmin == LONmax && LATmin != LATmax) || (LONmin != LONmax && LATmin == LATmax) || LONmin>LONmax || LATmin>LATmax || isNaN(parseInt(LONmax)) || isNaN(parseInt(LONmin)) || isNaN(parseInt(LATmax)) || isNaN(parseInt(LATmin))){
		alert("Error! A zoom entry you made is not correct.");
	}else{
		//changement de la carte
		document.getElementById("mapPoints").innerHTML="";
		
		if ((LATmax - LATmin) < (LONmax - LONmin)){
			imgLarg = startImgLarg;
			imgHaut = imgLarg * (LATmax - LATmin)/(LONmax - LONmin);
			document.getElementById("mapContainer").width = imgLarg+'px';
			document.getElementById("map").style.height = imgHaut+'px';
			document.getElementById("map").style.width = imgLarg+'px';
		}
		else{
			imgHaut = startImgHaut;
			imgLarg = imgHaut * (LONmax - LONmin)/(LATmax - LATmin);
			document.getElementById("mapContainer").width = imgLarg+'px';
			document.getElementById("map").style.height = imgHaut+'px';
			document.getElementById("map").style.width = imgLarg+'px';
		}
		//map.style.backgroundImage='url(<%=Props.getMapProperties().getProperty("generatorURL")%>?latMax='+LATmax+'&latMin='+LATmin+'&lonMax='+LONmax+'&lonMin='+LONmin+'&Width='+imgLarg+'&Height='+imgHaut+')';
		
		//document.getElementById("tmpMsg").innerHTML+="; lat:"+LATmin+"-"+LATmax;
		
		var urlGenMap = urlMap+'?latMax='+LATmax+'&latMin='+LATmin+'&lonMax='+LONmax+'&lonMin='+LONmin+'&Width='+imgLarg+'&Height='+imgHaut;
		map.style.backgroundImage='url('+urlGenMap+')';
		
		//on enregistre les bordures de la nouvelle carte
		imgLATmin=LATmin;
		imgLATmax=LATmax;
		imgLONmin=LONmin;
		imgLONmax=LONmax;

		if (LONmin == LONmax && LATmin == LATmax) {
			document.getElementById("msg").innerHTML="Cannot render the selected area (might be due to its size)";
		}

		//enregistrment des valeur dans la formbean MODIFIE
		form.minLat.value=imgLATmin;
		form.maxLat.value=imgLATmax;
		form.minLon.value=imgLONmin;
		form.maxLon.value=imgLONmax;
		
		//mise ? jour de l'affichage de la box
		var maxLatDMS=ddToDms(imgLATmax);
		if(maxLatDMS[0]==0){
			document.getElementById("maxLatDeg").value=maxLatDMS[3]+maxLatDMS[0];
		}else{
			document.getElementById("maxLatDeg").value=maxLatDMS[0];
		}
		document.getElementById("maxLatMin").value=maxLatDMS[1];
		document.getElementById("maxLatSec").value=maxLatDMS[2];
		
		var minLatDMS=ddToDms(imgLATmin);
		if(minLatDMS[0]==0){
			document.getElementById("minLatDeg").value=minLatDMS[3]+minLatDMS[0];
		}else{
			document.getElementById("minLatDeg").value=minLatDMS[0];
		}
		document.getElementById("minLatMin").value=minLatDMS[1];
		document.getElementById("minLatSec").value=minLatDMS[2];
		
		var maxLonDMS=ddToDms(imgLONmax);
		if(maxLonDMS[0]==0){
			document.getElementById("maxLonDeg").value=maxLonDMS[3]+maxLonDMS[0];
		}else{
			document.getElementById("maxLonDeg").value=maxLonDMS[0];
		}
		document.getElementById("maxLonMin").value=maxLonDMS[1];
		document.getElementById("maxLonSec").value=maxLonDMS[2];
		
		var minLonDMS=ddToDms(imgLONmin);
		if(minLonDMS[0]==0){
			document.getElementById("minLonDeg").value=minLonDMS[3]+minLonDMS[0];
		}else{
			document.getElementById("minLonDeg").value=minLonDMS[0];
		}
		document.getElementById("minLonMin").value=minLonDMS[1];
		document.getElementById("minLonSec").value=minLonDMS[2];
	
		//if (document.getElementsByName("mapForm:choix")[0].checked){
		if (choix1){
			drawZones();
		}
							
	}
}

/* ******* POSITION DE LA SOURIS ******* */

//Affiche la position de la souris ? l'?cran
function mousePos(e){
	//positions
	posXmap=findPosX(map);
	posYmap=findPosY(map);
				
  	if (IE) { // grab the x-y pos.s if browser is IE
    	tempX = event.clientX + document.body.scrollLeft-posXmap
    	tempY = event.clientY + document.body.scrollTop-posYmap
	} else {  // grab the x-y pos.s if browser is NS
    	tempX = e.pageX-posXmap;
    	tempY = e.pageY-posYmap;
  	}
  
  	//On gere les bords
  	if(tempX<0) tempX=0;
  	if(tempX>imgLarg) tempX=imgLarg;
  	if(tempY<0) tempY=0;
  	if(tempY>imgHaut) tempY=imgHaut;
  	
	//transformations des Lat en Deg Min Sec
	var xval=findLon(tempX);
	var yval=findLat(tempY);
	if(!isDown && (xval>=imgLONmax || xval<=imgLONmin || yval>=imgLATmax || yval<=imgLATmin)){
		document.getElementById("mouseCoord").style.visibility="hidden";
		document.getElementById("line1").style.visibility='hidden';
		return;
	}else{
		document.getElementById("mouseCoord").style.visibility="visible";
		document.getElementById("line1").style.visibility="visible";
	}

	var degx=parseInt(xval);
	var degy=parseInt(yval);
	var minx=parseInt((xval-degx)*60);
	var miny=parseInt((yval-degy)*60);
	var secx=parseInt((xval-degx-minx/60)*3600);
	var secy=parseInt((yval-degy-miny/60)*3600);
	var xSign="";
	var ySign="";
	if(xval<0)xSign="-";
	if(yval<0)ySign="-";
	
	//affichage des r?sultats
  	lonVal.innerHTML = xSign+Math.abs(degx)+"&deg;"+Math.abs(minx)+"\'"+Math.abs(secx)+"\"";
  	latVal.innerHTML = ySign+Math.abs(degy)+"&deg;"+Math.abs(miny)+"\'"+Math.abs(secy)+"\"";

}

/* ******* CONVERSIONS DE COORDONNEES ******* */

//Deg-Min-Sec to dec
function dmsToDd(d,m,s){
	if(d=="")d=0;
	if(m=="")m=0;
	if(s=="")s=0;
	var deg=parseInt(d);
	var min=parseInt(m);
	var sec=parseInt(s);
	var sign;
	if(deg!=0){
		sign=deg/Math.abs(deg);
	}else if(d.length>1 && d.substring(0,1)=="-"){
		sign=-1;
	}else{
		sign=1;
	}
	return deg+sign*min/60+sign*sec/3600;
}

//converti les latdd ou londd en DMS
function ddToDms(dd){
	var deg=parseInt(dd);
	var min=parseInt((dd-deg)*60);
	var sec=parseInt((dd-deg-min/60)*3600);
	var sign="";
	if(dd<0)sign="-";
	return [deg,Math.abs(min),Math.abs(sec),sign];
}

//converti de pixel ? Lat
function findLat(lat){
	return ((imgHaut-lat)/imgHaut*(imgLATmax-imgLATmin)+imgLATmin);
}

//converti de Lat ? pixel
function findYPos(lat){
	//return 500-(lat-imgLATmin)/(imgLATmax-imgLATmin)*imgHaut;
	return imgHaut*(imgLATmax-lat)/(imgLATmax-imgLATmin);
}

//converti de pixel ? Lon
function findLon(lon){
	return lon/imgLarg*(imgLONmax-imgLONmin)+imgLONmin;
}

//converti de Lon ? Pixel
function findXPos(lon){
	//return (lon-imgLONmin)/(imgLONmax-imgLONmin)*imgLarg;
	return imgLarg*(lon-imgLONmin)/(imgLONmax-imgLONmin);
}

/* ******* POSITIONS D'OBJETS ******* */

//trouve la position absolue d'un objet en X
function findPosX(obj){
	if(obj!=null){
	var curleft = 0;
	if (obj.offsetParent){
		while (obj.offsetParent){
			curleft += obj.offsetLeft
			obj = obj.offsetParent;
		}
	}
	else if (obj.x){
		curleft += obj.x;
	}
	return curleft;
	}
	return 0;
}

//trouve la position absolue d'un objet en Y
function findPosY(obj){
	if(obj!=null){
		var curtop = 0;
		if (obj.offsetParent){
			while (obj.offsetParent){
				curtop += obj.offsetTop
				obj = obj.offsetParent;
			}
		}
		else if (obj.y){
			curtop += obj.y;
		}
		return curtop;
	}
	return 0;
}

