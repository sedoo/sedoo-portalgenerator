/* 
	JavaScript Module by Ellis webmaster of toolinux.com
*/

// pour le fonctionnement autre que layer mozilla == netscape

var isNS4=(document.layers) ? 1 : 0;
var isIE4=(document.all)? 1 : 0;
var isMoz=((document.getElementById) && !(isIE4)) ? 1 :0;


function ShowNavigateur()
{
	alert(isNS4);
	alert(isIE4);
	alert(isMoz);
}

function activate_mousemove(function_mouse)
{
	if(isNS4||isMoz)
		document.captureEvents(Event.MOUSEMOVE);
	document.onmousemove = function_mouse;
}

function get_mouse(e)
{
	var mouse=new Array();
	mouse.x=0; mouse.y=0;
	if(isNS4||isMoz){
		mouse.x=e.pageX; mouse.y=e.pageY;
	}else if(isIE4){
		mouse.x=event.x+document.body.scrollLeft; mouse.y=event.y+document.body.scrollTop;
	}
	return mouse;
}

function get_layer(name)
{
	if(isNS4){
		layer=findLayer(name, document);
		//if(layer==null)
		//alert('Pb de layer');
		return layer;
	} else if (isIE4){
		
		//window.status=document.all[name];
		return document.all[name];
	} else if (isMoz) {
		element =  document.getElementById(name);
		return element;
	}
	//alert('Pb de noms');
	return null;	
}


function findLayer(name, doc) {
	var i, layer;
	//alert(doc.layers.length);
	for (i = 0; i < doc.layers.length; i++) { 
	layer = doc.layers[i];
	//alert(layer.name);
	if (layer.name == name)
		return layer;
	if (layer.document.layers.length > 0)
		if ((layer = findLayer(name, layer.document)) != null)
	        	return layer;
  	}
  return null;
}





function hide_layer(layer) 
{
	if(layer==null){
		//window.status="Pas de layer ? cacher";	
		return;
	}
	if (isNS4){
		layer.visibility = "hide";
	}
	if (isIE4||isMoz){
		layer.style.visibility = "hidden";
	}
}

function show_layer(layer) {
	if(isNS4){
		layer.visibility = "show";
	}
	if(isIE4||isMoz){
		layer.style.visibility = "visible";
	}
}

function move_layer_to(layer, x, y)
{
	//window.status=layer;
	if (isNS4)
		{	
		layer.moveTo(x, y);
		}
	if (isIE4||isMoz){
	//window.status=layer.style;
	layer.style.left = x + "px";
	layer.style.top  = y + "px";
	//alert("y = "+y+" x = "+x+"\n layer.style.position = "+layer.style.position+"\n layer.style.left = "+layer.style.left+"\n layer.style.top = "+layer.style.top);
	}
}

function setzIndex(layer, z) {
	if(isNS4)
		layer.zIndex = z;
	if(isIE4||isMoz)
		layer.style.zIndex = z;
}

function modify_content_layer(layer,content)
{
	if(isNS4){
		layer.document.write(content);
		layer.document.close();	
	} else if(isIE4||isMoz){
		layer.innerHTML=content;
	}
}

function bulle(titre, aide)
{
adroite = true;
var content;
content = "<div><h3>"+titre+"</h3><p>"+aide+"<\/p><\/div>";

l=get_layer("aide");
modify_content_layer(l,content)
show_layer(l); 
}


$(function() {
	$( "#PersonHelp" ).dialog({dialogClass: "alert"});
		
	$( "#PersonHelp" ).dialog("close");
});



function get_mouse2(e) 
{
mouse=get_mouse(e);
l=get_layer("aide");
var x = mouse.x; 
var y = mouse.y;
if(adroite)
x=x+15;
else
x=x-315; 

move_layer_to(l,x,y);

//box=document.getElementById("test");
//box.innerHTML= "("+x+","+y+") - (" +  l.style.left +","+l.style.top+") " + l.style.width;

}


function kill() 
{
l=get_layer("aide");
hide_layer(l);
}
