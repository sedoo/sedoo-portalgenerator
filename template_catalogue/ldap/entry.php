<?php

class entry{
	
	var $dn;
	
	function __construct($dn) {
		$this->dn=$dn;
	}
	
	function toString(){
		return "DN: $this->dn\n";
	}
	
}

?>