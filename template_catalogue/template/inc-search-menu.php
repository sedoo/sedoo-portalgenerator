<?php
	require_once('forms/search_form_menu.php');
	$form = new search_form_menu();
	$form->createForm();
			
?>
<dt style='margin-bottom: 2px;padding-left: 25px;'><?php $form->displayForm() ?></dt>
