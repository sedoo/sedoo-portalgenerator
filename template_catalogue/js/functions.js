function popupWithContent(windowname,content){
	var popUpDiv = document.getElementById(windowname);
	popUpDiv.innerHTML = content;
}

function hideRows(rowName){
	rows = document.getElementsByName(rowName);
	for (var i=0; i < rows.length; i++)
		rows[i].style.display = "none";
	
	document.getElementsByName(rowName + '_s')[0].style.display="";
}

function displayRows(rowName){
	rows = document.getElementsByName(rowName);
	for (var i=0; i < rows.length; i++)
		rows[i].style.display = "";
	document.getElementsByName(rowName + '_s')[0].style.display="none";
}

function disableText(boxName){
	box = document.getElementsByName(boxName)[0];
	box.disabled=true;
	box.value="";
}

function blockTextBox(boxName){
	box = document.getElementsByName(boxName)[0];
	if (document.all) { 
		//IE
		box.onfocus = function() { this.blur(); };
		box.style.cssText="background-color: transparent;";
	}else{
		box.setAttribute("onfocus","blur()");
		box.setAttribute("style","background-color: transparent;");	
	}
}

function unblockTextBox(boxName){
	box = document.getElementsByName(boxName)[0];
	if (document.all) { 
		//IE
		box.onfocus = function() {};
		box.style.cssText="background-color: white;";
	}else{
		box.setAttribute("onfocus","");
		box.setAttribute("style","background-color: white;");
	}
}

function blockBoxes(boxesNames){
	for (var i=0; i < boxesNames.length; i++)
		blockTextBox(boxesNames[i]);
}

function unblockBoxes(boxesNames){
	for (var i=0; i < boxesNames.length; i++)
		unblockTextBox(boxesNames[i]);
}

function activeText(boxName){
	document.getElementsByName(boxName)[0].disabled=false;
}

function updateText(selectName,boxName){
	liste = document.getElementsByName(selectName)[0];
	if (liste. options[0].selected) {
		activeText(boxName);
	}else{
		disableText(boxName);
	}
}

function updateTextBoxes(selectName,boxesNames){
	for (i=0;i<boxesNames.length;i++){
		updateText(selectName,boxesNames[i]);
	}
}

function getXmlHttp(){
	var xmlhttp = null;
	if(window.XMLHttpRequest){
		xmlhttp = new XMLHttpRequest();
	}
	else if(window.ActiveXObject){
		try  {
			//xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
			xmlhttp = new ActiveXObject("MSXML2.XMLHTTP");

		}catch(e){
			//document.getElementById("errors").innerHTML += "[Ajax] e: " + e;
			try{
				xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
			}catch(el){
				//document.getElementById("errors").innerHTML += "[Ajax] el: " + el;
				xmlhttp = null;
			}
		}
	}
	return xmlhttp;
}

function arrayToCsv(array,separator){
	var result = "";
	
	for (var i=0; i < array.length; i++) {
		result += array[i];
		if (i != array.length-1)
			result += separator;
	}
	return result;	
}

function buildMap(keys,values){
	var map = new Array();
	
	for (var i=0; i < Math.min(keys.length,values.length); i++) {
		map[keys[i]] = values[i];
	}
	return map;
}


function resetSelect(selectName){
	document.getElementsByName(selectName)[0].selectedIndex = 0;
}

function updateSelects(selectNames,valeurs){
	for (var i=0; i < Math.min(selectNames.length,valeurs.length); i++) {
		changeSelect(selectNames[i],valeurs[i]);
	}
}

function changeSelect(selectName,valeur){
	var select = document.getElementsByName(selectName)[0];
	//document.getElementById("errors").innerHTML+= "nlle valeur-" + valeur + "<br>";
	for (var i=0; i < select.options.length; i++){
		
		//document.getElementById("errors").innerHTML+= i + "-" + select.options[i].value + "<br>";
		
		if (select.options[i].value == valeur){
			select.selectedIndex = i;
			select.setAttribute('selectedIndex',i);
			//document.getElementById("errors").innerHTML+= "setSelected " + i + "<br>";
		}
	}
			
	//document.getElementById("errors").innerHTML+= "valeur fin: " + select[select.selectedIndex].value; + "<br>";
	
	//select.onchange();
	
}

function resetBox(boxName){
	//document.getElementById("errors").innerHTML+= "reset : " + boxName + "<br>";
	document.getElementsByName(boxName)[0].value = "";
}

function resetBoxes(boxesNames){
	for (var i=0; i < boxesNames.length; i++)
		resetBox(boxesNames[i]);
}

function disableBox(boxName){
	document.getElementsByName(boxName)[0].disabled = true;
}
function enableBox(boxName){
	document.getElementsByName(boxName)[0].disabled = false;
}

function disableBoxes(boxesNames){
	for (var i=0; i < boxesNames.length; i++)
		disableBox(boxesNames[i]);
}

function enableBoxes(boxesNames){
	for (var i=0; i < boxesNames.length; i++)
		enableBox(boxesNames[i]);
}


function fillBox(selectName,boxName,tableName,columnName){
	fillBoxes(selectName,new Array(boxName),tableName,new Array(columnName));
}

function checkDatasetSelected(){
	var select = document.getElementsByName('dataset')[0];
	var addButton = document.getElementsByName('bouton_add')[0];
	value = select[select.selectedIndex].value;
	if (value > 0){
		addButton.disabled=false;
	}else{
		addButton.disabled=true;	
	}
}

function AfficheDataPolicy(){
	var select = document.getElementsByName('dataset')[0];
	var addButton = document.getElementsByName('bouton_add')[0];
	var policyErrors = document.getElementsByName('data_policy_errors')[0];
	var policyDisplay = document.getElementsByName('data_policy_td')[0];
	
	policyDisplay.innerHTML = "";
	policyErrors.innerHTML = "";
	
	value = select[select.selectedIndex].value;

	if (value > 0){
		addButton.disabled=false;

		var xmlhttp = getXmlHttp();
		if (xmlhttp != null){
			xmlhttp.onreadystatechange=function(){
				if (xmlhttp.readyState==4){
					if (xmlhttp.status==200){
						policy = readDataPolicy(xmlhttp.responseXML,"data_policy");
						use_constraints = readDataPolicy(xmlhttp.responseXML,"use_constraints");
						//alert("Value: " + value + ", policy: " + policy + ", use_constraints: " + use_constraints);		
						
						if (policy == null ){
							addButton.disabled=true;
							policyErrors.innerHTML += "<br/>'Data policy' is missing in the metadata!";
						}else{
							policyDisplay.innerHTML = policy;
						}
						
						if (use_constraints == null){
							addButton.disabled=true;
							policyErrors.innerHTML += "<br/>'Use constraints' is missing in the metadata!";
						}
					}else 
						document.getElementById("errors").innerHTML="[Ajax] Error code " + xmlhttp.status;

				}
			}
			xmlhttp.open("GET", "/ajaxRespPolicy.php?id="+value, true);
			xmlhttp.send("");   

		}else 
			document.getElementById("errors").innerHTML += "[Ajax] xmlHttp non initialisé";
	}else{
		addButton.disabled=true;	
	}
}



//code Arnaud

function ShowDoiXML(textarea, error, selectName, text, project_name){
	jQuery.ajax({
		url: '/scripts/showDoiXML.php?dats_id='+selectName+'&project='+project_name,
		dataType: 'JSON',
		success:function(data){
			AffichageXML(textarea, error, selectName, text, data.xml, data.xml_error, project_name, data.projects);
		},
		error: function(errorThrown){
			alert('error ' + errorThrown);
			console.log(errorThrown);
		}

	});


}

function AffichageXML(textarea, error, selectName, text, xml, xmlerror, project_name, projects){
			
	var theForm=document.forms['frmdoi'];

	textarea=document.getElementsByName(textarea);

	textarea[0].value = xml;
	
	text = document.getElementsByName(text);
	
		text[0].value = projects+'.'+selectName;
	

	textareaError=document.getElementsByName(error);
	
	if (xmlerror != ""){
		textareaError[0].value= xmlerror;
		theForm.elements['error'].style.visibility='visible';
		theForm.elements['error'].style.background='#FF7F50';
		theForm.elements['bouton_ok'].style.visibility='hidden';
	}else{
		theForm.elements['error'].style.visibility='hidden';
		theForm.elements['bouton_ok'].style.visibility='visible';
	}
	
}

function showXml(textarea, error, selectName){
	var xmlhttp = getXmlHttp();
	if (xmlhttp != null){
		var theForm=document.forms['frmdoi'];
		textarea=document.getElementsByName(textarea);
	}
}






function fillBoxes(selectName,boxesNames,tableName,columnsName){
	
	var xmlhttp = getXmlHttp();
	
	if (xmlhttp != null){
	
		var mapColumnBoxName = buildMap(columnsName,boxesNames);
				
		resetBoxes(boxesNames);
		
		liste = document.getElementsByName(selectName)[0];
		
		value = liste[liste.selectedIndex].value;
		
		xmlhttp.onreadystatechange=function(){
			if (xmlhttp.readyState==4){
				
				if (xmlhttp.status==200)
					readData(xmlhttp.responseXML, mapColumnBoxName);//box.value = xmlhttp.responseText;
				else 
					document.getElementById("errors").innerHTML="[Ajax] Error code " + xmlhttp.status;
				
			}
		}
		//xmlhttp.open("GET", "squelettes/test.php?id="+value+"&table="+tableName+"&column="+columnName, true);
		//xmlhttp.open("GET", "squelettes/testXml.php?id="+value+"&table="+tableName+"&columns="+columnName, true);
		//document.getElementById("errors").innerHTML+= "squelettes/testXml.php?id="+value+"&table="+tableName+"&columns="+arrayToCsv(columnsName, ";") + "<br>";
		xmlhttp.open("GET", "/ajaxResp.php?id="+value+"&table="+tableName+"&columns="+arrayToCsv(columnsName, ";"), true);
		xmlhttp.send("");   
		
		/*if (value != 0){
			disableBoxes(boxesNames);
		}else
			enableBoxes(boxesNames);*/
		
		//document.getElementById("errors").innerHTML+= "value-" + value + "<br>";
		
		if (value != 0){
			blockBoxes(boxesNames);
		}else
			unblockBoxes(boxesNames);

	}else 
		document.getElementById("errors").innerHTML += "[Ajax] xmlHttp non initialisé";
	
}

function updateDatasetTitle(source) {
	var champsTitre = document.getElementsByName("dats_title")[0];
	var champsSimu = document.getElementsByName(source)[0];
	
	//document.getElementById("errors").innerHTML += 'Simu: ' + champsSimu.value;
	
	if (champsTitre.value.length == 0){
		champsTitre.value = champsSimu.value;
	}
}


function readDataPolicy(xmlData,elt) {
	var nodes = xmlData.getElementsByTagName(elt);
	for (var i=0; i < nodes.length; i++) {
		if (nodes[i].firstChild != null){
			return nodes[i].firstChild.nodeValue;
		}
	}
	return null;
}

function readData(xmlData,map) {
	var nodes = xmlData.getElementsByTagName("column");
	
	//document.getElementById("errors").innerHTML = "";

	for (var i=0; i < nodes.length; i++) {

		//document.getElementById("errors").innerHTML+= "Column " + nodes[i].getAttribute("name")+"-"+nodes[i].firstChild.nodeValue+"<br>";
		
		if (nodes[i].firstChild != null){
			//document.getElementById("errors").innerHTML+=nodes[i].getAttribute("name")+"-"+map[nodes[i].getAttribute("name")]+"-"+nodes[i].firstChild.nodeValue+"<br>";
			box = document.getElementsByName(map[nodes[i].getAttribute("name")])[0];
			box.value = nodes[i].firstChild.nodeValue;
			//document.getElementById("errors").innerHTML+= "Update box " + map[nodes[i].getAttribute("name")] + ": " + nodes[i].firstChild.nodeValue +"<br>";	
		}
	}

	var ids = xmlData.getElementsByTagName("id");
	for (i=0; i < ids.length; i++) {
		
		//document.getElementById("errors").innerHTML+=ids[i].getAttribute("name")+"-"+map[ids[i].getAttribute("name")]+"-"+ids[i].firstChild.nodeValue+"<br>";
		box = document.getElementsByName(map[ids[i].getAttribute("name")])[0];
		
		changeSelect(map[ids[i].getAttribute("name")], ids[i].firstChild.nodeValue);
				
	}
	
	
}

function updateParam(elmt,suffix){
	fillBox(elmt,"new_variable_" + suffix,'variable','var_name');
	updateGcmd(suffix);	
}

function updateGcmd(suffix){
	var xmlhttp = getXmlHttp();
	
	if (xmlhttp != null){

		liste = document.getElementsByName("variable_" + suffix)[0];
		value=liste[liste.selectedIndex].value;
		
		xmlhttp.onreadystatechange=function(){
			if (xmlhttp.readyState==4){
				
				if (xmlhttp.status==200)
					readDataGcmd(xmlhttp.responseXML, suffix);
				else 
					document.getElementById("errors").innerHTML="[Ajax] Error code " + xmlhttp.status;
				
			}
		}
		//document.getElementById("errors").innerHTML+= "squelettes/testUpdateGcmd.php?id="+value + "<br>";
		xmlhttp.open("GET", "squelettes/testUpdateGcmd.php?id="+value, true);
		xmlhttp.send("");  
	}
}

function updateSatOld(){
	
	fillBoxes('satellite',['new_satellite'],'place',['place_name']);
	
	liste = document.getElementsByName("satellite")[0];
	value=liste[liste.selectedIndex].value;
	
	if (value <= 0){
		listeInstru = document.getElementsByName("instrument")[0];
		listeInstru.options.length = 1;
		listeInstru.options[0].value = 0;
		listeInstru.options[0].text = "";
				
	}else{
		var xmlhttp = getXmlHttp();

		if (xmlhttp != null){

			xmlhttp.onreadystatechange=function(){
				if (xmlhttp.readyState==4){

					if (xmlhttp.status==200)
						readDataInstru(xmlhttp.responseXML,"instrument");
					else 
						document.getElementById("errors").innerHTML="[Ajax] Error code " + xmlhttp.status;

				}
			}
			//document.getElementById("errors").innerHTML+= "squelettes/recupInstrus.php?satId="+value + "<br>";
			xmlhttp.open("GET", "squelettes/recupInstrus.php?satId="+value, true);
			xmlhttp.send("");  
		}
	}
}

function updateSat(index){
	
	fillBoxes('satellite_'+index,['new_satellite_'+index],'place',['place_name']);
	
	liste = document.getElementsByName("satellite_"+index)[0];
	value=liste[liste.selectedIndex].value;

//	alert("Value: " + value);
	
	if (value <= 0){
		listeInstru = document.getElementsByName("instrument_"+index)[0];
		listeInstru.options.length = 1;
		listeInstru.options[0].value = 0;
		listeInstru.options[0].text = "";
		listeInstru.options.selectedIndex = 0;
		listeInstru.onchange();		
	}else{
		var xmlhttp = getXmlHttp();

		if (xmlhttp != null){

			xmlhttp.onreadystatechange=function(){
				if (xmlhttp.readyState==4){

					if (xmlhttp.status==200)
						readDataInstru(xmlhttp.responseXML,"instrument_"+index);
					else 
						document.getElementById("errors").innerHTML="[Ajax] Error code " + xmlhttp.status;

				}
			}
			//document.getElementById("errors").innerHTML+= "squelettes/recupInstrus.php?satId="+value + "<br>";
			xmlhttp.open("GET", "/ajaxRespInstrus.php?satId="+value, true);
			xmlhttp.send("");  
		}
	}
}

function updateMod(){
	
	fillBoxes('model',['new_model'],'place',['place_name','gcmd_plat_id']);
	
	liste = document.getElementsByName("model")[0];
	value=liste[liste.selectedIndex].value;

	console.log("Value: " + value);
	
	if (value <= 0){
		listeInstru = document.getElementsByName("simu")[0];
		listeInstru.options.length = 1;
		listeInstru.options[0].value = 0;
		listeInstru.options[0].text = "";
		listeInstru.options.selectedIndex = 0;
                listeInstru.onchange();				
	}else{
		var xmlhttp = getXmlHttp();

		if (xmlhttp != null){

			xmlhttp.onreadystatechange=function(){
				if (xmlhttp.readyState==4){

					if (xmlhttp.status==200)
						readDataInstru(xmlhttp.responseXML,"simu");
					else 
						document.getElementById("errors").innerHTML="[Ajax] Error code " + xmlhttp.status;

				}
			}
			//document.getElementById("errors").innerHTML+= "squelettes/recupInstrus.php?satId="+value + "<br>";
			xmlhttp.open("GET", "/ajaxRespInstrus.php?satId="+value, true);
			xmlhttp.send("");  
		}
	}
}

function updateModIndex(index){
	
	if(index == '')
		index = 0;
	
	fillBoxes('model_'+index,['new_model_'+index],'place',['place_name','gcmd_plat_id']);
	
	liste = document.getElementsByName("model_"+index)[0];
	value=liste[liste.selectedIndex].value;
	//alert("Value: " + value);
	
	if (value <= 0){
		listeInstru = document.getElementsByName("simu_"+index)[0];
		listeInstru.options.length = 1;
		listeInstru.options[0].value = 0;
		listeInstru.options[0].text = "";
		listeInstru.options.selectedIndex = 0;
                listeInstru.onchange();				
	}else{
		var xmlhttp = getXmlHttp();

		if (xmlhttp != null){

			xmlhttp.onreadystatechange=function(){
				if (xmlhttp.readyState==4){

					if (xmlhttp.status==200)
						readDataInstru(xmlhttp.responseXML,"simu_"+index);
					else 
						document.getElementById("errors").innerHTML="[Ajax] Error code " + xmlhttp.status;

				}
			}
			xmlhttp.open("GET", "/ajaxRespInstrus.php?satId="+value, true);
			xmlhttp.send("");  
		}
	}
}

function updateModNew(){

	fillBoxes('model[2]',['new_model'],'place',['place_name','gcmd_plat_id']);

	listeSat = document.getElementsByName("model[2]")[0];
	value=liste[liste.selectedIndex].value;

//	alert("Value: " + value);

	if (value <= 0){
		listeInstru = document.getElementsByName("simu")[0];
		listeInstru.options.length = 1;
		listeInstru.options[0].value = 0;
		listeInstru.options[0].text = "";
		listeInstru.options.selectedIndex = 0;
		listeInstru.onchange();
	}else{
		var xmlhttp = getXmlHttp();

		if (xmlhttp != null){

			xmlhttp.onreadystatechange=function(){
				if (xmlhttp.readyState==4){

					if (xmlhttp.status==200)
						readDataInstru(xmlhttp.responseXML,"simu");
					else
						document.getElementById("errors").innerHTML="[Ajax] Error code " + xmlhttp.status;

				}
			}
			//document.getElementById("errors").innerHTML+= "squelettes/recupInstrus.php?satId="+value + "<br>";
			xmlhttp.open("GET", "/ajaxRespInstrus.php?satId="+value, true);
			xmlhttp.send("");
		}
	}
} 

function readDataInstru(xmlData, listName) {
	var nodes = xmlData.getElementsByTagName("instrument");

	listeInstru = document.getElementsByName(listName)[0];
	listeInstru.options.length = nodes.length+1;
	listeInstru.options[0].value = 0;
	listeInstru.options[0].text = "";
	
	for (var i=0; i < nodes.length; i++) {	
		if (nodes[i].firstChild != null){
			listeInstru.options[i+1].value = nodes[i].getAttribute("id");
			listeInstru.options[i+1].text = nodes[i].firstChild.nodeValue;
		}
	}

	listeInstru.options.selectedIndex = 0;
	listeInstru.onchange();
		
}


function readDataGcmd(xmlData,suffix) {
	var gcmd = xmlData.getElementsByTagName("gcmd");
			
	//document.getElementById("errors").innerHTML+="GCMD" + gcmd.length + "<br>";
	for (var i=0; i < 4; i++) {
		if (i < gcmd.length){
			//document.getElementById("errors").innerHTML+= "GCMD level " + gcmd[i].getAttribute("level")+"-"+gcmd[i].getAttribute("id")+"<br>";
			changeSelect("gcmd_science_key_" + suffix + "[" + i + "]", gcmd[i].getAttribute("id"));
		}/*else
			changeSelect("gcmd_science_key_" + suffix + "[" + i + "]", 0);*/
		//gcmd_science_key_0[i]
	}
}

function displayDatapolicy(){
    if(document.getElementById('datapolicy')== null){
    	if(document.getElementById("warning_sign") == null){
    		$('#sign_data_policy').prepend('<td id="warning_sign" style="color:red;"> Please to choose first the project you want to register in.</td>');
    	}
	}
	$('#datapolicy').dialog('open');
}

function UseDialogForm(){
	$('#datapolicy').dialog({
		// will keep it from opening until called upon
		autoOpen: false,
		modal : true,
		// just some effects for opn and close of this 'alert' dialog box
		show: 'blind',
		hide: 'explode',
		draggable: false,
		height : 700,
		title  : 'Data Policy',
		width : 600,
		close: function( event, ui ) {},
		// create and tell ok button to submit form, and cancel to close form
		buttons: {
			'OK': function(e) {
				    //$('form#frmdatapolicy').submit();
				    $.post('/scripts/frmprofile.php', $('#frmdatapolicy').serialize(), function(result){
				        console.log(result);
				    });
				    $.post('/scripts/frmprofile.php', $('#frmdatapolicy').serialize(), function(result){
				        console.log(result);
				    }); 
					$('#datapolicy').dialog('close');
					}
				 }
		});
}

function DeactivateButtonAddSource(){
	if (document.getElementsByName('source_type')[0].selectedIndex == 0) 
		document.getElementsByName('bouton_add_source')[0].disabled=true; 
	else
		document.getElementsByName('bouton_add_source')[0].disabled=false;
	}



/*
function testAjax(){
	var xmlhttp = null;
	if(window.XMLHttpRequest){
		xmlhttp = new XMLHttpRequest();
	}
	else if(window.ActiveXOject){
		try  {
			xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
		}catch(e){
			try{
				xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
			}catch(el){
				xmlhttp = null;
			}
		}
	}
	if (xmlhttp == null){
		alert("Votre navigateur ne supporte pas les objets XMLHTTPRequest\nVeuillez le mettre à jour");
	}
	
	xmlhttp.onreadystatechange=function(){
		if (xmlhttp.readyState==4){
			
			if (xmlhttp.status==200)
				document.getElementsByName('new_database')[0].value = xmlhttp.responseText;
			else 
				document.getElementById("txtHint").innerHTML="Error code " + xmlhttp.status;
			
		}
	}

	xmlhttp.open("GET", "squelettes/test.php", true);
	xmlhttp.send();    	
}
*/

