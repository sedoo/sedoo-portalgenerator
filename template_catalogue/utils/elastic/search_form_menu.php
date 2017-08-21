<?php

require_once("HTML/QuickForm.php");
require_once("utils/elastic/ElasticSearchUtils.php");


class search_form_menu extends HTML_QuickForm{

	var $keywords;
	var $projectName;
	function createForm($projectName){
		$this->projectName = $projectName;
		$this->addElement ( 'text', 'keywords_search_menu', 'Keywords: ', array (
				'size' => '15',
				'name' => 'q',
				'value' => isset ( $_GET ['q'] ) ? htmlentities ( $_GET ['q'] ) : '',
				'id' => 'suggestMenu',
				'placeholder' => 'Search by keyword'
		) );
		$this->addElement('hidden','search_project', $projectName);
		$this->addElement('image','bouton_search_menu','/scripts/images/loupe-16.gif',array('title' => 'Search'));
	}

	function displayForm(){
		$reqUri = "http://".$_SERVER['HTTP_HOST']."/Data-Search/?project_name=$this->projectName";
				
		echo "<form action='$reqUri' method='post' name='frsearchmenu' id='frsearchmenu' >";
		echo $this->getElement('search_project')->toHTML().$this->getElement('keywords_search_menu')->toHTML().'&nbsp;'.$this->getElement('bouton_search_menu')->toHTML();
		echo '</form>';
	}

	
/*	function search()	{
		if ( isset($_REQUEST['q']) ){
			$this->keywords = $_REQUEST['q'];
		}else{
			$keys = $this->exportValue('keywords_search_menu');
			$this->keywords = $keys;
		}

		if (empty($this->keywords)){
			$this->keywords = "*";
		}

		ElasticSearchUtils::lstQueryData($this->keywords, $this->projectName, array('terms' => $this->keywords, 'project' => $projectName));
	}*/

}
?>
