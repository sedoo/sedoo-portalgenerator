<?php

require_once("bd/gcmd_science_keyword.php");

class metadata
 	{

 		var $paramsGCMD;

 		var $array_topic;
 		var $array_categorie;
 		var $array_variable;
 		var $array_variable2;
 		
 		/*function __construct(){
 			$this->loadGCMDScienceKeywords();
 		}*/
 		
 		 		
 		
 		function loadGCMDScienceKeywords()
    	{
    		$this->$array_topic[0] = "-- Topic --";
        	$this->$array_categorie[0][0] = "-- Term --";
        	$this->$array_variable[0][0][0] = "-- Var_level1 --";
        	$this->$array_variable2[0][0][0][0] = "-- Var_level2 --";
    		
        	$gcmd = new gcmd_science_keyword;
    		
        	$query = "select * from gcmd_science_keyword where gcmd_level = 1 order by gcmd_name";
			$liste_topic = $gcmd->getByQuery($query);
			
			for ($i = 0; $i < count($liste_topic); $i++)
	        {
	        	$j = $liste_topic[$i]->gcmd_id;
	          	$this->$array_topic[$j] = $liste_topic[$i]->gcmd_name;
	          	
	        	$query2 = "select * from gcmd_science_keyword where gcm_gcmd_id = ".$j." order by gcmd_name";
	          	$liste_categ = $gcmd->getByQuery($query2);
	          	$this->$array_categorie[$j][0] = "-- Term --";
	          	
	          	 for ($k = 0; $k < count($liste_categ); $k++)
		        {
		        			        			        	
		        	$l = $liste_categ[$k]->gcmd_id;
		           $this->$array_categorie[$j][$l] = $liste_categ[$k]->gcmd_name;
		        	
		         	$query3 = "select * from gcmd_science_keyword where gcm_gcmd_id = ".$l." order by gcmd_name";
		            $liste_param = $gcmd->getByQuery($query3);
		            $this->$array_variable[$j][$l][0] = "-- Var_level1 --";
		            for ($m = 0; $m < count($liste_param); $m++)
		            {
		            	$n = $liste_param[$m]->gcmd_id;
		                $this->$array_variable[$j][$l][$n] = $liste_param[$m]->gcmd_name;
		                
		                $this->$array_variable2[$j][$l][$n][0] = "-- Var_level2 --";
		                $query4 = "select * from gcmd_science_keyword where gcm_gcmd_id = ".$n." order by gcmd_name";
		                $param2 = $gcmd->getByQuery($query4);
		                for ($o = 0; $o < count($param2);$o++)
		                {
		                	$p = $param2[$o]->gcmd_id;
		                	$this->$array_variable2[$j][$l][$n][$p] = $param2->gcmd_name;
		                }
		            }
		           		            
		        }
	          	
	        }
			
    	}
 		
 		

 	}

?>