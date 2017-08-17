//Simule un clic sur l'élément dont l'id est spécifié (version Firefox)
function simulateClickFF(id) {
	var evt = document.createEvent("MouseEvents");
	evt.initMouseEvent("click", true, true, window,0, 0, 0, 0, 0, false, false, false, false, 0, null);
    document.getElementById(id).dispatchEvent(evt);
}

//Simule un clic sur l'élément dont l'id est spécifié (version IE)
function simulateClickIE(id) {
	document.getElementById(id).fireEvent("onclick");
}