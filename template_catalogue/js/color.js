/*
 * Fonctions utilisées pour l'affichage des boutons apply et unzoom
 */
function colorEnregistrer(o){
	if(o.selected!=true){
		o.style.backgroundColor='#F5B555';
	}
}

function unColorEnregistrer(o){
	if(o.selected!=true){
		o.style.backgroundColor='#8888bb';
	}
}