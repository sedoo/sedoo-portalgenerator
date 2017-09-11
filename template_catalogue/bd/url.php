<?php
/*
 * Created on 8 juil. 2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 	require_once ("bd/bdConnect.php");
 	require_once ("bd/dataset.php");
	
 	class url{
 		
 		var $dats_id;
 		var $url_id;
 		var $url;
 		var $url_type;
 		 		
 		function new_url($tab)
 		{
 			$this->url_id = $tab[0];
 			$this->url = $tab[1];
 			$this->dats_id = $tab[2];
 			$this->url_type = $tab[3];

			if ( strpos($this->url,'Data-Download') === 0 )
				$this->url = '/'.$this->url;

			if (substr($this->url, -1) == '/') {
				$this->url = substr($this->url, 0, strlen($this->url)-1);
			}

 		}
 		
 		function getAll()
 		{
 			$query = "select * from url order by dats_id";
      		return $this->getByQuery($query);
 		}

		function getById($urlId){
                        $query = "SELECT * FROM url WHERE url_id = $urlId";
                	$liste = $this->getByQuery($query);
			if (isset($liste) && count($liste) == 1){
				return $liste[0];
			}else{
				return null;
			}
                }

		function getByDataset($datsId){
                        $query = "SELECT * FROM url WHERE dats_id = $datsId";
                return $this->getByQuery($query);
                }
 		
 		function getLocalFileByDataset($datsId){
 			$query = "select * from url where dats_id = $datsId and url_type = 'local file'"; 			
      		return $this->getByQuery($query);
 		}

		function getMapFileByDataset($datsId){
			$query = "select * from url where dats_id = $datsId and url_type = 'map'";
			return $this->getByQuery($query);
		}
 		
 		function getHttpByDataset($datsId){
 			$query = "select * from url where dats_id = $datsId and url_type = 'http'"; 			
      		return $this->getByQuery($query);
 		}
   

		static function addUrl(&$bd,$url,$datsId,$type){
			$queryExists = "select * from url where url_type = '$type' and dats_id = $datsId";
			if ($resultat = $bd->get_data2($queryExists)){
				echo 'URL déjà présente<br>';
						return false;
					}else{
				$query = "insert into url values (default,'$url',$datsId,'$type')";
						echo $query.'<br>';
				$bd->exec($query);
				return true;
			}
		}

		static function updateUrl(&$bd,$url_id,$url){
			$query = "update url set url = '$url' where url_id = $url_id;";
			echo $query.'<br>';
			$bd->exec($query);
			return true;
		}

		static function deleteUrl(&$bd,$url_id){
			$query = "delete from url where url_id = $url_id;";
			echo $query.'<br>';
			$bd->exec($query);
			return true;
        }

		static function deleteUrls(&$bd,$dats_id){
			$query = "delete from url where dats_id = $dats_id;";
			echo $query.'<br>';
			$bd->exec($query);
			return true;
        }
 
		function getFtpByDataset($datsId){
 			$query = "select * from url where dats_id = $datsId and url_type = 'ftp'"; 			
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
          			$liste[$i] = new url;
          			$liste[$i]->new_url($resultat[$i]);
        		}
      		}
      		return $liste;
    	}
 	}
?>
