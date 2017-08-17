<?php
/*
 * GB, Modif 9 aout 2011 : ajout place_level à la table
 */
 	require_once("bd/bdConnect.php");
	require_once("bd/conf.php");
 	require_once("bd/gcmd_plateform_keyword.php");
 	require_once("bd/boundings.php");
 	require_once("scripts/common.php");
 	
 	class place
 	{
 		var $place_id;
 		var $pla_place_id;
 		var $bound_id;
 		var $gcmd_plat_id;
 		var $place_name;
 		var $place_elevation_min;
 		var $place_elevation_max;
 		var $parent_place;
 		var $boundings;
 		var $gcmd_plateform_keyword;
 		var $enfants;
 		
 		var $place_level;
 		
 		var $west_bounding_coord;
 		var $east_bounding_coord;
 		var $north_bounding_coord;
 		var $south_bounding_coord;
 		 		
 		var $sensor_environment;
 		
 		function new_place($tab)
 		{
 			$this->place_id = $tab[0];
 			$this->pla_place_id = $tab[1];
 			$this->bound_id = $tab[2];
 			$this->gcmd_plat_id = $tab[3];
 			$this->place_name = $tab[4];
 			$this->place_elevation_min = intAlt2double($tab[5]);
 			$this->place_elevation_max = intAlt2double($tab[6]);
 			$this->place_level = $tab[7];
 			
 			if (isset($this->pla_place_id) && !empty($this->pla_place_id))
 			{
 				$this->parent_place = $this->getById($this->pla_place_id);
 			}
 			if (isset($this->bound_id) && !empty($this->bound_id))
 			{
 				$bound = new boundings;
 				$this->boundings = $bound->getById($this->bound_id);
 				
 				$this->west_bounding_coord = & $this->boundings->west_bounding_coord;
 				$this->east_bounding_coord = & $this->boundings->east_bounding_coord;
 				$this->north_bounding_coord = & $this->boundings->north_bounding_coord;
 				$this->south_bounding_coord = & $this->boundings->south_bounding_coord;
 				
 				
 			}
 			if (isset($this->gcmd_plat_id) && !empty($this->gcmd_plat_id))
 			{
 				$gcmd = new gcmd_plateform_keyword;
 				$this->gcmd_plateform_keyword = $gcmd->getById($this->gcmd_plat_id);
 			}
 			
 			/*
 			$query = "select * from place where pla_place_id = ".$this->place_id;
 			$this->enfants = $this->getByQuery($query);*/
 		}
 		
 		function toString(){
 			$result = 'Site: '.(($this->gcmd_plateform_keyword)?$this->gcmd_plateform_keyword->gcmd_plat_name.' > ':'').$this->place_name;
 			
 			if (isset($this->boundings)){
     	 		$result .= "\nBoundings: ".$this->boundings->toString();
     	 	}
     	 	
     	 	if (isset($this->place_elevation_min) && strlen($this->place_elevation_min) > 0){
     	 		$result .= "\nAltitude min: ".$this->place_elevation_min;
     	 	}
     	 	if (isset($this->place_elevation_max) && strlen($this->place_elevation_max) > 0){
     	 		$result .= "\nAltitude max: ".$this->place_elevation_max;
     	 	}
 			return $result;
 			
 		}
 		
 		function getAll()
 		{
 			$query = "select * from place order by place_name";
      		return $this->getByQuery($query);
 		}

		function getChildrenSites($parent,$type = 0){
			$where = "where place_level is not null and pla_place_id = $parent";
			if ($type > 0)
                                $where .= " and gcmd_plat_id = $type";
			$query = "select * from place $where order by place_name";
			//echo " $query<br>";
			return $this->getByQuery($query);
		}
 		 		
 		function getByLevel($level = 1,$parent = 0,$type = 0)
 		{
 			$where = "where place_level = $level";
 			
 			if ($parent > 0)
 				$where .= " and pla_place_id = $parent";
 			if ($type > 0)
 				$where .= " and gcmd_plat_id = $type ";
 				
 			$query = "select * from place $where order by place_name";
 			
 			//echo "$query<br>";
 			
      		return $this->getByQuery($query);
 		}
 		
 		function getAllInSitu()
 		{
 			$query = "select * from place_insitu order by place_name";
      		return $this->getByQuery($query);
 		}
 		
 		function getById($id)
    	{
      		if (!isset($id) || empty($id))
        		return new place;

      		$query = "select * from place where place_id = ".$id;
      		$bd = new bdConnect;
      		if ($resultat = $bd->get_data($query))
      		{
        		$place = new place;
        		$place->new_place($resultat[0]);
      		}
      		return $place;
    	}
    	
    	function getPlaceNameById($id){
    		$query = "select place_name from place where place_id = ".$id;
    		$bd = new bdConnect;
    		$resultat = $bd->get_data($query);
    		return $resultat;
    	}
    	
    	function getByQuery($query)
    	{
    		    		
      		$bd = new bdConnect;
      		$liste = array();
      		if ($resultat = $bd->get_data($query))
      		{
        		for ($i=0; $i<count($resultat);$i++)
        		{
          			$liste[$i] = new place;
          			$liste[$i]->new_place($resultat[$i]);
        		}
      		}
      		return $liste;
    	}

    	function existeComplet(){    		
    		$where = "where lower(place_name) = lower('".(str_replace("'","\'",$this->place_name))."')";
    		
    		if (isset($this->gcmd_plat_id) && !empty($this->gcmd_plat_id)) {
    			$where .= " and gcmd_plat_id = ". $this->gcmd_plat_id;    			
    		}
    		if (isset($this->bound_id) && !empty($this->bound_id) && $this->bound_id != -1) {
    			$where .= " and bound_id = ". $this->bound_id;    			
    		}
    		if (isset($this->pla_place_id) && !empty($this->pla_place_id)) {
    			$where .= " and pla_place_id = ". $this->pla_place_id;    			
    		}
    		if (isset($this->place_elevation_min) && strlen($this->place_elevation_min) > 0){
    			$where .= " and place_elevation_min = ". doubleAlt2int($this->place_elevation_min);;    			
    		}
    		if (isset($this->place_elevation_max) && strlen($this->place_elevation_max) > 0){
    			$where .= " and place_elevation_max = ". doubleAlt2int($this->place_elevation_max);    			
    		}
    		if (isset($this->place_level) && !empty($this->place_level)) {
    			$where .= " and place_level = ". $this->place_level;    			
    		}
    		    		
    		$query = "select * from place $where";
    		
    		//echo $query.'<br>';
    		
    		$bd = new bdConnect;
        	if ($resultat = $bd->get_data($query))
        	{
          		$this->new_place($resultat[0]);
          		return true;
        	}
        	return false;
    	}
    	
    	function existe()
    	{
        	
        	$query = "select * from place where ".
        			"lower(place_name) = lower('".(str_replace("'","\'",$this->place_name))."')";
        	
        	//echo $query."<br>";
        	$bd = new bdConnect;
        	if ($resultat = $bd->get_data($query))
        	{
          		$this->new_place($resultat[0]);
          		return true;
        	}
        	return false;
    	}

    	function idExiste()
    	{
        	$query = "select * from place where place_id = ".$this->place_id;
        	//echo $query."<br>";
        	$bd = new bdConnect;
        	if ($resultat = $bd->get_data($query))
        	{
          		$this->new_place($resultat[0]);
          		return true;
        	}
        	return false;
    	}
    	
    	function insert(& $bd)
    	{
    		if (isset($this->boundings) && $this->bound_id != -1){
	    			$this->boundings->insert($bd);
	    			$this->bound_id = $this->boundings->bound_id;
	    			//echo 'bound_id:'.$this->bound_id.'<br>';
	    		}
    		
    		//if (!$this->existe())
    		if (!$this->existeComplet())
    		{	    	    			    		
	     	 	$query_insert = "insert into place (place_name";
	     	 	$query_values = "values ('".str_replace("'","\'",$this->place_name)."'";
	     	 	if (isset($this->gcmd_plateform_keyword) && $this->gcmd_plat_id > 0)
	     	 	{
	     	 		$query_insert .= ",gcmd_plat_id";
	     	 		$query_values .= ",".$this->gcmd_plat_id;
	     	 	}
	     	 	if (isset($this->bound_id) && !empty($this->bound_id) && $this->bound_id != -1)
	     	 	{
	     	 		$query_insert .= ",bound_id";
	     	 		$query_values .= ",".$this->bound_id;
	     	 	}
	     	 	if (isset($this->pla_place_id) && !empty($this->pla_place_id))
	     	 	{
	     	 		$query_insert .= ",pla_place_id";
	     	 		$query_values .= ",".$this->pla_place_id;
	     	 	}
	     	 	if (isset($this->place_elevation_min) && strlen($this->place_elevation_min) > 0)
	     	 	{
	     	 		$query_insert .= ",place_elevation_min";
	     	 		$query_values .= ",".doubleAlt2int($this->place_elevation_min);
	     	 	}
	     	 	if (isset($this->place_elevation_max) && strlen($this->place_elevation_max) > 0)
	     	 	{
	     	 		$query_insert .= ",place_elevation_max";
	     	 		$query_values .= ",".doubleAlt2int($this->place_elevation_max);
	     	 	}
	     	 	$query = $query_insert.") ".$query_values.")";
	     		
	      		$bd->exec($query);
	    		
	      		$this->place_id = $bd->getLastId("place_place_id_seq");
    		}
      		return $this->place_id;
    	}
    	
 	function insertOld()
    	{
    		    	    		
    		if ($this->bound_id != -1){
    			$this->boundings->insert();
    			$this->bound_id = $this->boundings->bound_id;
    			echo 'bound_id:'.$this->bound_id.'<br>';
    		}
    		
     	 	$query_insert = "insert into place (place_name,gcmd_plat_id";
     	 	$query_values = "values ('".str_replace("'","\'",$this->place_name)."'" .
     	 					",".$this->gcmd_plat_id;
     	 	if (isset($this->bound_id) && !empty($this->bound_id) && $this->bound_id != -1)
     	 	{
     	 		$query_insert .= ",bound_id";
     	 		$query_values .= ",".$this->bound_id;
     	 	}
     	 	if (isset($this->pla_place_id) && !empty($this->pla_place_id))
     	 	{
     	 		$query_insert .= ",pla_place_id";
     	 		$query_values .= ",".$this->pla_place_id;
     	 	}
     	 	if (isset($this->place_elevation_min) && !empty($this->place_elevation_min))
     	 	{
     	 		$query_insert .= ",place_elevation_min";
     	 		$query_values .= ",".doubleAlt2int($this->place_elevation_min);
     	 	}
     	 	if (isset($this->place_elevation_max) && !empty($this->place_elevation_max))
     	 	{
     	 		$query_insert .= ",place_elevation_max";
     	 		$query_values .= ",".doubleAlt2int($this->place_elevation_max);
     	 	}
     	 	$query = $query_insert.") ".$query_values.")";
      		$bd = new bdConnect;
      		$bd->insert($query);
      		
      		$this->place_id = $bd->getLastId("place_place_id_seq");
      		
      		return $this->place_id;
    	}

	function chargeFormModelCategs($form,$label,$titre){
		//$array_type[0] = "";
                //$array_stype[0][0] = "";

		$gcmd = new gcmd_plateform_keyword();
                $types = $gcmd->getByIds(MODEL_CATEGORIES);

		foreach ($types as $type){
			$array_type[$type->gcmd_plat_id] = $type->gcmd_plat_name;
			$liste = $this->getByLevel(1,0,$type->gcmd_plat_id);
			foreach ($liste as $item){
				$array_stype[$type->gcmd_plat_id][$item->place_id] = $item->place_name;
			}
		}
		$s = & $form->createElement('hierselect',$label,$titre,null, '');
                $s->setOptions(array($array_type,$array_stype));
                return $s;
	}    
	
    	function chargeFormSiteLevels($form,$label,$titre){
    		global $project_name;
    		$array_type[0] = "";
    		$array_lev1[0][0] = "";
        	$array_lev2[0][0][0] = "";
        	$array_lev3[0][0][0][0] = "";
        	
        	/*$array_lev1[1] = "Topic 1";
    		$array_lev1[5] = "Topic 2";
        	$array_lev2[1][0] = "Term 11";
        	$array_lev2[1][8] = "Term 12";
        	$array_lev2[5][0] = "Term 21";
        	$array_lev3[1][0][0] = "Var 111";
        	$array_lev3[1][1][0] = "Var 121";
        	$array_lev3[1][1][1] = "Var 122";
        	$array_lev3[5][0][0] = "Var 211";*/
        	
        	
        	$gcmd = new gcmd_plateform_keyword ();
		if (constant ( strtoupper ( $project_name ) . '_SITES' ) != '' && constant ( strtoupper ( $project_name ) . '_SITES' ) != null) {
			$types = $gcmd->getByIds ( constant(strtoupper ( $project_name ) . '_SITES') );
			foreach ( $types as $type ) {
				$array_type [$type->gcmd_plat_id] = $type->gcmd_plat_name;
				$liste1 = $this->getByLevel ( 1, 0, $type->gcmd_plat_id );
				foreach ( $liste1 as $site1 ) {
					$array_lev1 [$type->gcmd_plat_id] [$site1->place_id] = $site1->place_name;
					$array_lev2 [$type->gcmd_plat_id] [$site1->place_id] [0] = '';
					$liste2 = $this->getByLevel ( 2, $site1->place_id );
					foreach ( $liste2 as $site2 ) {
						$array_lev2 [$type->gcmd_plat_id] [$site1->place_id] [$site2->place_id] = $site2->place_name;
						$array_lev3 [$type->gcmd_plat_id] [$site1->place_id] [$site2->place_id] [0] = '';
						$liste3 = $this->getByLevel ( 3, $site2->place_id );
						foreach ( $liste3 as $site3 ) {
							$array_lev3 [$type->gcmd_plat_id] [$site1->place_id] [$site2->place_id] [$site3->place_id] = $site3->place_name;
						}
					}
				}
			}
			$s = & $form->createElement ( 'hierselect', $label, $titre, null, '<br>' );
			$s->setOptions ( array (
					$array_type,
					$array_lev1,
					$array_lev2,
					$array_lev3 
			) );
			return $s;
		}
    	}
    	
    	//creer element select pour formulaire
    	function chargeForm($form,$label,$titre,$indice)
    	{

      		//$liste = $this->getAll();
      		$liste = $this->getAllInSitu();
      		    		    		
      		$array[0] = "";
      		for ($i = 0; $i < count($liste); $i++)
        	{
          		$j = $liste[$i]->place_id;
          		$array[$j] = $liste[$i]->place_name;
          		//echo 'array['.$j.'] = '.$array[$j].'<br>';
        	}
        	
        	if (isset($indice)){
        		$boxesNames = "['new_place_".$indice."','place_alt_min_".$indice."','west_bound_".$indice."','east_bound_".$indice."','north_bound_".$indice."','south_bound_".$indice."','place_alt_max_".$indice."','gcmd_plat_key_".$indice."']";
        		$columnsNames = "['place_name','place_elevation_min','west_bounding_coord','east_bounding_coord','north_bounding_coord','south_bounding_coord','place_elevation_max','gcmd_plat_id']";
        	}else{
        		$boxesNames = "['new_place','place_alt_min','west_bound','east_bound','north_bound','south_bound','place_alt_max','gcmd_plat_key']";
        		$columnsNames = "['place_name','place_elevation_min','west_bounding_coord','east_bounding_coord','north_bounding_coord','south_bounding_coord','place_elevation_max','gcmd_plat_id']";
        	}
        	$s = & $form->createElement('select',$label,$titre,$array,array('onchange' => "fillBoxes('".$label."',".$boxesNames.",'place',".$columnsNames.");",'style' => 'width: 200px;'));
 
        	/*
      		$s = & $form->createElement('select',$label,$titre);
      		$s->loadArray($array);*/
      		return $s;
    	}
    	
 	function chargeFormModOld($form,$label,$titre){
    		return $this->chargeFormByType($form,$label,$titre,"Model","updateMod();");
    	}

	function chargeFormMod($form,$label,$titre,$onchange = "updateMod();"){
		$query = 'SELECT DISTINCT ON (place_name) * from place where gcmd_plat_id in ('.GCMD_PLAT_MODEL.') AND place_level IS NULL order by place_name';
                $liste = $this->getByQuery($query);
                $array[0] = "";
                for ($i = 0; $i < count($liste); $i++)
                {
                        $j = $liste[$i]->place_id;
                        $array[$j] = $liste[$i]->place_name;
                }

                $s = & $form->createElement('select',$label,$titre,$array,array('onchange' => $onchange));

                return $s;
	}
	
	function chargeFormInstruvadataset($form,$label="instru_place_",$titre="instru_place"){
		$liste = $this->getAllInSitu();
		//$array[0] = "";
		for ($i = 0; $i < count($liste); $i++)
		{
		$j = $liste[$i]->place_id;
			$array[$j] = $liste[$i]->place_name;
		}
	
		$s = & $form->createElement('select',$label,$titre,$array,array('onchange' => $onchange));
	
		return $s;
	}
	
	function chargeFormSat($form,$i,$label = 'satellite_',$titre = 'Satellite'){    		
    		return $this->chargeFormByType($form,$label.$i,$titre,'Satellites','updateSat('.$i.');');
    	}
	
    	function chargeFormSatvadataset($form,$i,$label = 'satellite_',$titre = 'Satellite'){
    		return $this->chargeFormByType($form,$label.$i,$titre,'Satellites','updateSat('.$i.');');
    	}

    	function chargeFormRegion($form,$label,$titre,$simpleVersion = false){
    		if ($simpleVersion){
    			$boxesNames = "['new_area','west_bound_0','east_bound_0','north_bound_0','south_bound_0']";
    			$columnsNames = "['place_name','west_bounding_coord','east_bounding_coord','north_bounding_coord','south_bounding_coord']";
    		}else{
    			$boxesNames = "['new_area','west_bound_0','east_bound_0','north_bound_0','south_bound_0','place_alt_min_0','place_alt_max_0']";
        		$columnsNames = "['place_name','west_bounding_coord','east_bounding_coord','north_bounding_coord','south_bounding_coord','place_elevation_min','place_elevation_max']";
    		}
    		return $this->chargeFormByType($form,$label,$titre,"Geographic Regions","fillBoxes('".$label."',".$boxesNames.",'place',".$columnsNames.");");
    	}
    	
 		function chargeFormByType($form,$label,$titre,$type,$onchange){
			$query = "select * from place where gcmd_plat_id in (select gcmd_plat_id from gcmd_plateform_keyword where gcmd_plat_name ilike '%".$type."%') AND place_level IS NULL order by place_name";
			//echo 'place.chargeFormByType: '.$query;
      		$liste = $this->getByQuery($query);
      		$array[0] = "";
      		for ($i = 0; $i < count($liste); $i++)
        	{
          		$j = $liste[$i]->place_id;
          		$array[$j] = $liste[$i]->place_name;
          		//echo 'array['.$j.'] = '.$array[$j].'<br>';
        	}
        	
        	$s = & $form->createElement('select',$label,$titre,$array,array('onchange' => $onchange, 'onclick' => $onchange));
 
      		return $s;
    	}
    	function chargeFormByTypeVadataset($form,$label,$titre,$type,$onchange){
			$query = "select * from place where gcmd_plat_id in (select gcmd_plat_id from gcmd_plateform_keyword where gcmd_plat_name ilike '%".$type."%') AND place_level IS NULL order by place_name";
			//echo 'place.chargeFormByType: '.$query;
      		$liste = $this->getByQuery($query);
      		//$array[0] = "";
      		$x=0;
      		for ($i = 0; $i < count($liste); $i++)
        	{
          		$j = $liste[$i]->place_id;
          		$array[$j] = $liste[$i]->place_name;
          		//echo 'array['.$j.'] = '.$array[$j].'<br>';
          		if(i==0) $x=$j;
        	}
        	
        	$s = & $form->createElement('select',$label,$titre,$array,array('onchange' => $onchange , 'onclick' => $onchange , 'onload' => $onchange ));
 
      		return $s;
    	}
 		
 	}
?>
