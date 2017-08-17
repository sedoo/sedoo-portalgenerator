<?php

class calendrier_cellule{

	const BLANC = "#ffffff";
	const VERT = "#32CD32";
	const ROUGE = "#ff9090";
		
	var $jour;
	var $color;

	function calendrier_cellule($jour = null,$color = self::BLANC){
		$this->color = $color;
		$this->jour = $jour;
	}

	function setAvailable(){
		$this->color = self::VERT;
	}

	function setMissing(){
                $this->color = self::ROUGE;
        }

}

?>
