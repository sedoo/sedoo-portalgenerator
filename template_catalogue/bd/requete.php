<?php

require_once ("bd/bdConnect.php");

class requete{

	const CODE_EN_COURS = 0;
	const CODE_ECHEC = -1;
	const CODE_SUCCES = 1;
	const CODE_VIDE = 2;

	var $requeteId;

	var $dateDeb;
	var $dateActive;
	var $dateFin;

	var $nbValeurs;

	var $mail;
	var $xml;

	var $etat;
	var $killed;
/*
 requete_id          | integer                     | non NULL Par défaut, nextval('requete_requete_id_seq'::regclass)
 requete_email       | character varying(50)       | non NULL
 requete_xml         | text                        | non NULL
 requete_date_debut  | timestamp without time zone | non NULL
 requete_date_active | timestamp without time zone | non NULL
 requete_date_fin    | timestamp without time zone | 
 requete_etat        | smallint                    | non NULL
 requete_kill        | boolean                     | non NULL
 requete_nb_val   */
	function new_requete($tab){
		$this->requeteId = $tab[0];
		$this->mail = $tab[1];
		$this->xml = $tab[2];

		$this->dateDeb = new DateTime($tab[3]);
		$this->dateActive = new DateTime($tab[4]);
		if (isset($tab[5]))
			$this->dateFin = new DateTime($tab[5]);
		
		$this->etat = $tab[6];
		if (isset($tab[7]) && $tab[7] == 't')
			$this->killed = true;
		else
			$this->killed = false;
		
		$this->nbValeurs = $tab[8];
	}

	function isRunning(){
		return ($this->etat == self::CODE_EN_COURS) && !$this->killed;
	}
	
	function isFinished(){
		return ($this->etat != self::CODE_EN_COURS);
	}
	
	function getAll(){
		$query = "SELECT * FROM requete ORDER BY requete_id desc";
		return $this->getByQuery($query);
	}
		
	function getByUser($mail){
		$query = "SELECT * FROM requete WHERE requete_email = '$mail' ORDER BY requete_id desc";
		return $this->getByQuery($query);
	}
	
	function kill(){
		$query = "UPDATE requete SET requete_kill = 'true' WHERE requete_id = $this->requeteId;";
        $bd = new bdConnect;
        $bd->db_open();
       	$bd->exec($query);
       	$this->killed = true;
       	$bd->db_close();
	}
	
	function getByQuery($query){
  	      	$bd = new bdConnect;
      		$liste = array();
      		if ($resultat = $bd->get_data($query)){
        		for ($i=0; $i<count($resultat);$i++){
          			$liste[$i] = new requete;
          			$liste[$i]->new_requete($resultat[$i]);
        		}
      		}
      		return $liste;
    	}
	
}
?>