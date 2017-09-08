<?php

	require_once("scripts/logger.php");
	require_once("conf.php");

/*
 * Created on 8 juil. 2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
	class bdConnect
	{
    	var $hote = DB_HOST;
    	var $db_name = DB_NAME;
    	var $db_user = DB_USER;
    	var $db_password = DB_PASS;
    	var $conn;

    	function db_open()
    	{
     		if ( !$this->conn = pg_pconnect("host=".$this->hote." user=".$this->db_user." dbname=".$this->db_name . " password=".$this->db_password )){
        		echo "Cannot connect to database.\n";
        		exit;
      		}
      		
	    }

    	function db_close()
    	{
      		if (! @pg_close($this->conn))
      		{
        		echo "Erreur de fermeture de la base !!!";
      		}
    	}


    	function get_data2($requete) // ?
    	{
      		if ($res = pg_query($this->conn,$requete))
      		{
        		for ($i=0;$i<pg_num_rows($res);$i++)
        		{
          			$tab_res[$i] = pg_fetch_row($res);
        		}
      		}
      		if (!isset($tab_res) || empty($tab_res))
        		return null;
      		return $tab_res;
    	}
    	
     	function get_data($requete)
    	{
      		$this->db_open();
      		if ($res = pg_query($this->conn,$requete))
      		//if ($res = $this->exec($requete))
      		{
        		for ($i=0;$i<pg_num_rows($res);$i++)
        		{
          			$tab_res[$i] = pg_fetch_row($res);
		        }
      		}
      		$this->db_close();
      		if (!isset($tab_res) || empty($tab_res))
        		return null;
      		return $tab_res;
    	}

    	function getLastIdOld($sequence){    	
    		$this->db_open();
    		//$query = "SELECT currval('".$sequence."')";
    		$query = "SELECT last_value from ".$sequence;
    		//echo "getLastId: ".$query."<br>";
    		
    		$res = pg_query($this->conn,$query) or die('Erreur SQL !'.$query.''.pg_last_error());
    		$id = pg_fetch_array($res);
    		$this->db_close();
    		return $id[0];

    	}
		function getLastId($sequence){
			$query = "SELECT last_value from ".$sequence;
			$res = $this->exec($query);
    		$id = pg_fetch_array($res);
    		return $id[0];
    	}
    	
    	function insertOld($requete)
    	{
      		$this->db_open();
      		$res = pg_query($this->conn,$requete) or die('Erreur SQL !'.$requete.''.pg_last_error());
      		//$id = pg_insert_id();
      		//$id = pg_last_oid($res);
      		//$id = pg_fetch_array($res,null,PGSQL_NUM);
      		$this->db_close();
      		//return $id[0];
    	}
		function updateOld($requete)
    	{
      		$this->db_open();
      		pg_query($this->conn,$requete) or die('Erreur SQL !'.$requete.''.pg_last_error());
      		$this->db_close();
    	}
    	
    	
    	function insert($requete)
    	{
    		$this->exec($requete);
    	}
		function update($requete)
    	{
      		return $this->exec($requete);
    	}
    	

    	function exec($requete){
    		if (!isset($this->conn) || empty($this->conn))
    			throw new Exception('ERREUR: Connection à la base non ouverte');
    			
    		//echo "[SQL] ".$requete."<br>";
    		log_debug('SQL - '.$requete);
    		
    		$res = pg_query($this->conn,$requete);
    		if (!$res)
	 			throw new Exception('Erreur SQL !'.$requete.'-'.pg_last_error($this->conn));
	 			
	 		return $res;
    	}
    	
    	function beginTransaction(){    		
    		return $this->exec("BEGIN");
    	}
    	
    	function commitTransaction(){
			return $this->exec("COMMIT");
    	}
    	function rollbackTransaction(){
			return $this->exec("ROLLBACK");
    	}
    	
    	
    	
	}
?>
