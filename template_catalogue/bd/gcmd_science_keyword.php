<?php
/*
 * Created on 8 juil. 2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 	require_once("bd/bdConnect.php");
 	require_once("bd/metadata.php");
 	
 	class gcmd_science_keyword
 	{
 		var $gcmd_id;
 		var $gcm_gcmd_id;
 		var $gcmd_name;
 		var $gcmd_level;
 		var $thesaurus_id;
 		var $uid;
 		
 		var $gcmd_parent;
 		var $enfants;
 		 		
 		function new_gcmd_science_keyword($tab)
 		{
 			$this->gcmd_id = $tab[0];
 			$this->gcm_gcmd_id = $tab[1];
 			$this->gcmd_name = $tab[2];
 			$this->gcmd_level = $tab[3];
 			$this->thesaurus_id = $tab[4];
 			$this->uid = $tab[5];
 			
 			if (isset($this->gcm_gcmd_id) && !empty($this->gcm_gcmd_id)){
 				$this->gcmd_parent = $this->getById($this->gcm_gcmd_id);
 			}
 		}
 		
 		function toString(){
			if (isset($this->gcmd_parent)  && !empty($this->gcmd_parent)){
				return $this->gcmd_parent->toString().' > '.$this->gcmd_name;
			}else
			return $this->gcmd_name;
		}
 		
 		function getAll()
 		{
 			$query = "select * from gcmd_science_keyword order by gcmd_name";
      		return $this->getByQuery($query);
 		}
 	
		function getChildren($recursive = false){
 			$liste = array();
 			$this->readChildren($liste, $recursive);
 			return $liste;
 		}
 		
 		
 		private function readChildren(&$liste,$recursive = false){
 			$query = "SELECT * FROM gcmd_science_keyword WHERE gcm_gcmd_id = $this->gcmd_id ORDER BY gcmd_name";
 			$tmp = $this->getByQuery($query);
 			if ($recursive && isset($tmp)){
 				foreach($tmp as $child){
 					$liste[] = $child;
 					$child->readChildren($liste,$recursive);
 				}
 			}
 		}
	
 		function getByQuery($query)
 		{
 			$bd = new bdConnect;
      		$liste = array();
      		if ($resultat = $bd->get_data($query))
      		{
        		for ($i=0; $i<count($resultat);$i++)
        		{
          			$liste[$i] = new gcmd_science_keyword;
          			$liste[$i]->new_gcmd_science_keyword($resultat[$i]);
        		}
      		}
      		return $liste;
 		}
 		
 		function getById($id)
 		{
 			if (!isset($id) || empty($id))
        		return new project;

      		$query = "select * from gcmd_science_keyword where gcmd_id = ".$id;
      		$bd = new bdConnect;
      		if ($resultat = $bd->get_data($query))
      		{
        		$gcmd = new gcmd_science_keyword;
        		$gcmd->new_gcmd_science_keyword($resultat[0]);
      		}
      		return $gcmd;
 		}
 		
 		
 		function chargeFormTest($form,$label,$titre)
    	{
    		$array_topic[0] = "-- Topic --";
    		$array_topic[1] = "Topic 1";
    		$array_topic[5] = "Topic 2";
        	$array_categorie[0][0] = "-- Term --";
        	$array_categorie[1][0] = "Term 11";
        	$array_categorie[1][8] = "Term 12";
        	$array_categorie[5][0] = "Term 21";
        	$array_variable[0][0][0] = "-- Var_level1 --";
        	$array_variable[1][0][0] = "Var 111";
        	$array_variable[1][1][0] = "Var 121";
        	$array_variable[1][1][1] = "Var 122";
        	$array_variable[5][0][0] = "Var 211";
        	$array_variable2[0][0][0][0] = "-- Var_level2 --";
        	$array_variable2[1][1][1][0] = "Var 1221";
        	
    		$s = & $form->createElement('hierselect',$label,$titre);
	        $s->setOptions(array($array_topic,$array_categorie,$array_variable,$array_variable2));
	        return $s;
    	}

		/*
		 *TODO Charger en mémoire la liste au lieu d'utiliser la base à chaque fois 
		 * 
		 */
    	function chargeForm($form,$label,$titre)
    	{
    		$array_topic[0] = "-- Topic --";
        	$array_categorie[0][0] = "-- Term --";
        	$array_variable[0][0][0] = "-- Var_level1 --";
        	$array_variable2[0][0][0][0] = "-- Var_level2 --";
    		
        	$query = "select * from gcmd_science_keyword where gcmd_level = 1 order by gcmd_name";
			$liste_topic = $this->getByQuery($query);
			
			for ($i = 0; $i < count($liste_topic); $i++){
	        	$j = $liste_topic[$i]->gcmd_id;
	          	$array_topic[$j] = $liste_topic[$i]->gcmd_name;
	          	
	        	$query2 = "select * from gcmd_science_keyword where gcm_gcmd_id = ".$j." order by gcmd_name";
	          	$liste_categ = $this->getByQuery($query2);
	          	$array_categorie[$j][0] = "-- Term --";
	          	
	          	 for ($k = 0; $k < count($liste_categ); $k++)
		        {
		        			        			        	
		        	$l = $liste_categ[$k]->gcmd_id;
		            $array_categorie[$j][$l] = $liste_categ[$k]->gcmd_name;
		        	
		         	$query3 = "select * from gcmd_science_keyword where gcm_gcmd_id = ".$l." order by gcmd_name";
		            $liste_param = $this->getByQuery($query3);
		            $array_variable[$j][$l][0] = "-- Var_level1 --";
		            for ($m = 0; $m < count($liste_param); $m++)
		            {
		            	$n = $liste_param[$m]->gcmd_id;
		                $array_variable[$j][$l][$n] = $liste_param[$m]->gcmd_name;
		                
		                $query4 = "select * from gcmd_science_keyword where gcm_gcmd_id = ".$n." order by gcmd_name";
		                $liste_param2 = $this->getByQuery($query4);
		                if (count($liste_param2) > 0)
		                	$array_variable2[$j][$l][$n][0] = "-- Var_level2 --";
		                for ($o = 0; $o < count($liste_param2);$o++)
		                {
		                	$p = $liste_param2[$o]->gcmd_id;
		                	$array_variable2[$j][$l][$n][$p] = $liste_param2[$o]->gcmd_name;
		                }
		            }
		            
		            
		        }
	          	
	        }
			        	
    		
    		$s = & $form->createElement('hierselect',$label,$titre);
	        $s->setOptions(array($array_topic,$array_categorie,$array_variable,$array_variable2));
	        return $s;
    	}
 	}
?>
