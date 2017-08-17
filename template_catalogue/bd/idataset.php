<?php

interface iDataset{

	//Pb avec la version de php sur sedoo
	//public function init($tab);
	
	public function insert();
	public function update();
	
	public function toString();
	public function display($project_name);
	
	public function set_requested($requested);

}

?>