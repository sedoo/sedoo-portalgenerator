<?php
/*
 * Created on 8 juil. 2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 	require_once("bd/bdConnect.php");
 	
 	class project
 	{
 		var $project_id;
 		var $pro_project_id;
 		var $project_name;
 		var $parent_project;
 		var $enfants;
 		var $project_url;
 		
 		function new_project($tab)
 		{
 			$this->project_id = $tab[0];
 			$this->pro_project_id = $tab[1];
 			$this->project_name = $tab[2];
 			$this->project_url = $tab[3];
 			
 			if (isset($this->pro_project_id) && !empty($this->pro_project_id)){
 				$this->parent_project = $this->getById($this->pro_project_id);
 			}
 			/*$query = "select * from project where pro_project_id = ".$this->project_id." order by project_name";
 			$this->enfants = $this->getByQuery($query);*/
 		}
 		
 		function getFullName(){
 			return  (($this->parent_project)?$this->parent_project->toString().' > ':'').$this->project_name;
 		}
 		
 		function toString(){
 			$label = $this->getFullName();
 			if ($this->project_url){
 				return "<a href='$this->project_url' target='_blank' >$label</a>";
 			}else{
 				return $label;
 			}
 		}
 		
 		function getAll()
 		{
 			$query = "select * from project order by project_name";
      		return $this->getByQuery($query);
 		}
 		
 		function getSousProjets(){
 			$query = "SELECT * FROM project WHERE pro_project_id = $this->project_id ORDER BY project_name";
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
          			$liste[$i] = new project;
          			$liste[$i]->new_project($resultat[$i]);
        		}
      		}
      		return $liste;
 		}
 		
 		function getIdByProjectName($name){
 			if (!isset($name) || empty($name))
 				return new project;
 			$query = "select project_id from project where project_name = '".$name."'";
 			$bd = new bdConnect;
 			if ($resultat = $bd->get_data($query))
 			{
 				$project = new project;
 				$project->new_project($resultat[0]);
 			
 				//echo ' -> project_name: '.$project->project_name;
 			
 			}
 			return $project;
 		}
 		
 		function getById($id)
 		{
 			if (!isset($id) || empty($id))
        		return new project;

      		$query = "select * from project where project_id = ".$id;
      		
      		//echo 'query: '.$query;
      		
      		$bd = new bdConnect;
      		if ($resultat = $bd->get_data($query))
      		{
        		$project = new project;
        		$project->new_project($resultat[0]);
        		
        		//echo ' -> project_name: '.$project->project_name;
        		
      		}
      		return $project;
 		}
 		
 		function existe()
    	{
        	$query = "select * from project where " .
        			"lower(project_name) = lower('".(str_replace("'","\'",$this->project_name))."')";
        	//echo $query."<br>";
        	$bd = new bdConnect;
        	if ($resultat = $bd->get_data($query))
        	{
          		$this->new_project($resultat[0]);
          		return true;
        	}
        	return false;
    	}

    	function idExiste()
    	{
        	$query = "select * from project where project_id = ".$this->project_id;
        	//echo $query."<br>";
        	$bd = new bdConnect;
        	if ($resultat = $bd->get_data($query))
        	{
          		$this->new_project($resultat[0]);
          		return true;
        	}
        	return false;
    	}

    	function chargeForm($form,$label,$titre){
    		
    		$array_proj[0] = "--Project--";
      		$array_sous_proj[0][0] = "--Sub_project--";
      		$array_sous_proj2[0][0][0] = "--Sub_project_2--";
      		
    		$query = "select * from project where pro_project_id is null order by project_name";
    		
    		$liste_proj = $this->getByQuery($query);
    		    		    		
    		for ($i = 0; $i < count($liste_proj); $i++){
        		$j = $liste_proj[$i]->project_id;
        		$array_proj[$j] = $liste_proj[$i]->project_name;

        		$array_sous_proj[$j][0] = "--Sub_project--";
        		$array_sous_proj2[$j][0][0] = "--Sub_project_2--";
        		
          		$query2 = "select * from project where pro_project_id = ".$j." order by project_name";
          		$sous_proj = $this->getByQuery($query2);
          		for ($k = 0; $k < count($sous_proj);$k++){
          			$l = $sous_proj[$k]->project_id;
          			$array_sous_proj[$j][$l] = $sous_proj[$k]->project_name;
          			
          			$array_sous_proj2[$j][$l][0] = "--Sub_project_2--";
          			$query3 = "select * from project where pro_project_id = ".$l." order by project_name";
          			$sous_proj2 = $this->getByQuery($query3);
          			for ($m = 0; $m< count($sous_proj2);$m++){
          				$n = $sous_proj2[$m]->project_id;
          				$array_sous_proj2[$j][$l][$n] = $sous_proj2[$m]->project_name;         				 
          			}
          			
          		}
        		
        	}

        	$s = & $form->createElement('hierselect',$label,$titre);
	        $s->setOptions(array($array_proj,$array_sous_proj,$array_sous_proj2));
      		return $s;
        	
    	}
    	
    	//creer element select pour formulaire
    	function chargeFormOld($form,$label,$titre)
    	{

      		$liste = $this->getAll();
      		$array_proj[0] = "--Project--";
      		$array_sous_proj[0][0] = "--Sub_project--";
      		for ($i = 0; $i < count($liste); $i++)
        	{
          		$j = $liste[$i]->project_id;
          		$array_proj[$j] = $liste[$i]->project_name;
          		//charge les sous-projets
          		$array_sous_proj[$j][0] = "--Sub_project--";
          		$query = "select * from project where pro_project_id = ".$j." order by project_name";
          		$sous_proj = $this->getByQuery($query);
          		for ($k = 0; $k < count(sous_proj);$k++)
          		{
          			$l = $sous_proj[$k]->project_id;
          			$array_sous_proj[$j][$l] = $sous_proj[$k]->project_name;
          		}
          		//echo 'array['.$j.'] = '.$array[$j].'<br>';
        	}
      		 $s = & $form->createElement('hierselect',$label,$titre);
	        $s->setOptions(array($array_proj,$array_sous_proj));
      		return $s;
    	}
 	}
?>
