//Simule un clic sur l'�l�ment dont l'id est sp�cifi� (version Firefox)
function simulateClickFF(id) {
	var evt = document.createEvent("MouseEvents");
	evt.initMouseEvent("click", true, true, window,0, 0, 0, 0, 0, false, false, false, false, 0, null);
    document.getElementById(id).dispatchEvent(evt);
}

//Simule un clic sur l'�l�ment dont l'id est sp�cifi� (version IE)
function simulateClickIE(id) {
	document.getElementById(id).fireEvent("onclick");
}