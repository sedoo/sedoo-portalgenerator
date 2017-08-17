<?php

/*
 * Cette classe utilise la commande zip du système et pas l'extension zip de php. 
 * 
 * ZipArchive de php 5.2 ne fonctionne pas correctement dès que le nombre de fichiers à ajouter est trop important.
 * Corrigé dans php 5.3.
 */
class ZipArchiveTest {
	
	protected $_archiveFileName = null;
	
	protected $_iniDir;
	protected $_workingDir;
	
	public function setWorkingDir($dir){
		$this->_workingDir = $dir;
		chdir($dir);
	}
	

 	public function open($fileName) {
        $this->_archiveFileName = $fileName;
        $this->_iniDir = getcwd();
        return true;
    }

    public function close() {
        $this->_archiveFileName = null;
        chdir($this->_iniDir);
        return true;
    }
    
    public function addFile($filename) {
    	if (isset($this->_archiveFileName)){
    		$output = array();
    		exec('zip '.$this->_archiveFileName.' '.$filename,$output,$ret);
    		return ( $ret == 0 );
    	}
    	return false;
    }
    
    public function addDir($filename) {
    	if (isset($this->_archiveFileName)){
    		$output = array();
    		exec('zip -r '.$this->_archiveFileName.' '.$filename,$output,$ret);
    		return ( $ret == 0 );
    	}
    	return false;
    }
    
    /*
    public function addFile($filename,$localname = null) {
    	if (isset($this->_archiveFileName)){
    		
    		if (isset($localname))
    			exec('zip '.$this->_archiveFileName.' '.$localname);
    		else
    			exec('zip '.$this->_archiveFileName.' '.$filename);
			return true;	
    	}
    	return false;
    }
            */
}

?>
