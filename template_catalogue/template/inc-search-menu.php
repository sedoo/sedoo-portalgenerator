<?php
	require_once ('utils/elastic/search_form_menu.php');
	$form = new search_form_menu();
	$form->createForm($project_name);
			
?>
<dt style='margin-bottom: 2px;padding-left: 25px;'><?php $form->displayForm() ?></dt>
