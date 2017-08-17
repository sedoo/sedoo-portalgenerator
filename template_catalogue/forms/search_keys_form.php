<?php
/*
 * Created on 27 janv. 2011
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
require_once("HTML/QuickForm.php");
require_once("HTML/QuickForm/radio.php");
require_once("common.php");
require_once("validation.php");

class search_keys_form extends HTML_QuickForm{
	
	var $keywords;
	var $and_or;	
	
	var $filter_data;
	var $filter_data_db;
	
	function createForm()
	{
		$this->addElement('text','keywords','Keywords: ',array('size'=>'50'));
		$and_or[] =& HTML_QuickForm::createElement('radio',null,null,'All of the above keywords (AND)','and');
		$and_or[] =& HTML_QuickForm::createElement('radio',null,null,'Any of the above keywords (OR)','or');
                $this->addGroup($and_or,'and_or','Search with: ','&nbsp;&nbsp;&nbsp;');
		$defaultValues['and_or']=  'or';
		$this->setDefaults($defaultValues);
		
		$this->createFormFilterData();
		$this->createFormFilterDataDb();
		
		$this->addElement('submit','bouton_search','search');
	}
	
	function createFormFilterData(){
		$options[] = & HTML_QuickForm::createElement('radio',null,null,'&nbsp;yes',1);
		$options[] = & HTML_QuickForm::createElement('radio',null,null,'&nbsp;no',0);
		$this->addGroup($options,'filter_data','Show only datasets with available data ?','&nbsp;&nbsp;');
		$defaultValues['filter_data'] = 0;
		$this->setDefaults($defaultValues);
	}
	
	function createFormFilterDataDb(){
		$options[] = & HTML_QuickForm::createElement('radio',null,null,'&nbsp;yes',1);
		$options[] = & HTML_QuickForm::createElement('radio',null,null,'&nbsp;no',0);
		$this->addGroup($options,'filter_data_db','Show only datasets with homogenized data ?','&nbsp;&nbsp;');
		$defaultValues['filter_data_db'] = 0;
		$this->setDefaults($defaultValues);
	}
	
	function displayForm()
	{
		$reqUri = $_SERVER['REQUEST_URI'];
		
		echo '<form action="'.$reqUri.'" method="post" name="frsearch" id="frsearch" enctype="multipart/form-data">';

		echo '<table>';
		echo '<tr><th></th><th></th>	<th></th></tr>'	;
		echo '<tr><td>'.$this->getElement('keywords')->getLabel().'</td><td colspan="2">'.$this->getElement('keywords')->toHTML().'</td></tr>';
		echo '<tr><td>'.$this->getElement('and_or')->getLabel().'</td><td colspan="2">'.$this->getElement('and_or')->toHTML().'</td></tr>';
		
		echo '<tr><td colspan="2" style="white-space:nowrap;">'.$this->getElement('filter_data')->getLabel().'</td><td>'.$this->getElement('filter_data')->toHTML().'</td></tr>';
		echo '<tr><td colspan="2" style="white-space:nowrap;">'.$this->getElement('filter_data_db')->getLabel().'</td><td>'.$this->getElement('filter_data_db')->toHTML().'</td></tr>';
				
		echo '<tr><th colspan="3" align="center">'.$this->getElement('bouton_search')->toHTML().'</th></tr></table>';
		echo '</form>';
	}
	
	function saveForm()	{
		$keys = $this->exportValue('keywords');
		$this->keywords = split(' ',$keys);
		$this->and_or = $this->exportValue('and_or');
		
		$this->filter_data = $this->exportValue('filter_data');
		$this->filter_data_db = $this->exportValue('filter_data_db');
	}
	
}
?>
