function showRow(trId){
	document.getElementById("more_" + trId).style.display="";
}

function hideRow(trId){
	document.getElementById("more_" + trId).style.display="none";
}

function testShow(tdId){
	document.getElementById("more_" + tdId).style.display="";
	document.getElementById("vide_" + tdId).style.display="none";
}

function testHide(tdId){
	document.getElementById("more_" + tdId).style.display="none";
	document.getElementById("vide_" + tdId).style.display="";
}
