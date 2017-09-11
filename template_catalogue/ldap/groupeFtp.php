<?php

require_once ("ldap/groupe.php");

class groupeFtp extends groupe{

	var $gidNumber;
	var $memberUid;

	function __construct($dn = null,$attrs = null) {
		parent::__construct($dn,$attrs);
                if (isset($attrs)){
			if ($attrs["memberUid"]){
                        	for ($i=0; $i < $attrs["memberUid"]["count"]; $i++) {
                                	$this->memberUid[$i] = $attrs["memberUid"][$i];
                        	}
                	}
			if ($attrs["gidNumber"]){
				$this->gidNumber=$attrs["gidNumber"][0];
			}
		}
	}

}

?>
