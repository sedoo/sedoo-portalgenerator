<?php
/*
 * Created on 8 juil. 2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
	require_once ("bd/bdConnect.php");
	require_once ("bd/country.php");
	
	class organism
	{
		var $org_id;
		var $org_sname;
		var $org_fname;
		var $org_url;
		
		function new_organism($tab)
		{
			$this->org_id = $tab[0];
			$this->org_sname = $tab[1];
			$this->org_fname = $tab[2];
			$this->org_url = $tab[3];

		}
		
		function getName(){
			$name = "";
			if ( isset($this->org_sname) && !empty($this->org_sname) ){
				$name = $this->org_sname;
			}else{
				$name = $this->org_fname;
			}
			return $name;
		}
		
		function toString(){
			$result = "Organism: ".$this->getName();
			if ( isset($this->org_sname) && !empty($this->org_sname) && isset($this->org_fname) && !empty($this->org_fname) ){
				$result .= " (".$this->org_sname.")";
			}
			if (isset($this->org_url) && !empty($this->org_url)) {
				$result .= ", url: ".$this->org_url;
			}
			return $result;
		}
		
		function getAll()
		{
			$query = "select * from organism order by org_sname";
			return $this->getByQuery($query);
		}
		
		function getByQuery($query)
		{
			$bd = new bdConnect;
      		$liste = array();
      		if ($resultat = $bd->get_data($query))
      		{
        		for ($i=0; $i<count($resultat);$i++)
        		{
          			$liste[$i] = new organism;
          			$liste[$i]->new_organism($resultat[$i]);
        		}
      		}
      		return $liste;
		}
		
		function getById($id)
		{
			if (!isset($id) || empty($id))
        		return new organism;

      		$query = "select * from organism where org_id = ".$id;
      		$bd = new bdConnect;
      		if ($resultat = $bd->get_data($query))
      		{
        		$org = new organism;
        		$org->new_organism($resultat[0]);
      		}
      		return $org;
		}
		
		function existe()
    	{
        	$query = "select * from organism where " .
        			"lower(org_sname) = lower('".str_replace("'","\'",$this->org_sname)."') and ".
        					"lower(org_fname) = lower(".str_replace("'","\'",$this->org_fname).")"; 
        	$bd = new bdConnect;
        	if ($resultat = $bd->get_data($query))
        	{
          		$this->new_organism($resultat[0]);
          		return true;
        	}
        	return false;
    	}

    	function idExiste()
    	{
        	$query = "select * from organism where org_id = ".$this->org_id;
        	$bd = new bdConnect;
        	if ($resultat = $bd->get_data($query))
        	{
          		$this->new_organism($resultat[0]);
          		return true;
        	}
        	return false;
    	}
    	
    	//creer element select pour formulaire
    	function chargeForm($form,$label,$titre,$indice)
    	{

      		$liste = $this->getAll();
      		$array[0] = null;
      		for ($i = 0; $i < count($liste); $i++)
        	{
          		$j = $liste[$i]->org_id;
          		          		 
          		if ( isset($liste[$i]->org_sname) && !empty($liste[$i]->org_sname) ){
          			$array[$j] = $liste[$i]->org_sname;
          		}else{
          			$array[$j] = $liste[$i]->org_fname;
          		}
        	}
        	
        	$boxesNames = "['org_sname_".$indice."','org_fname_".$indice."','org_url_".$indice."']";
        	$columnsNames = "['org_sname','org_fname','org_url']";
        	
        	$s = & $form->createElement('select',$label,$titre,$array,array('onchange' => "fillBoxes('".$label."',".$boxesNames.",'organism',".$columnsNames.");"));
        	
      		return $s;
    	}
    	
		function insert(& $bd)
    	{
     	 	$query_insert = "insert into organism (org_sname,org_fname";
     	 	$query_values =	"values ('".str_replace("'","\'",$this->org_sname)."','".str_replace("'","\'",$this->org_fname)."'";
     	 	
     	 	if (isset($this->org_url) && !empty($this->org_url))
     	 	{
     	 		$query_insert .= ",org_url";
     	 		$query_values .= ",'".$this->org_url."'";
     	 	}
     	 	
     	 	$query = $query_insert.") ".$query_values.")";
      		
      		$bd->exec($query);
      		
      		$this->org_id= $bd->getLastId("organism_org_id_seq");
      		
      		return $this->org_id;
    	}
    	
    	
			
	}
?>
