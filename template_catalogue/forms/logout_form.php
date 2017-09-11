<?php
require_once ("HTML/QuickForm.php");
/*
 * Created on 24 sept. 2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

class logout_form extends HTML_QuickForm
{
	function createForm()
	{
		$this->addElement('submit','logout','logout');
	}

	function displayForm($user,$project){
		global $project_name;
		echo '<form method="post" action="'.$_SERVER['REQUEST_URI'].'" >';
		if (get_class ( $user ) == 'portalUser') {
			echo "<a href='/Your-Account'>$user->cn</a>";
			if ($project_name != MainProject) {
				if ($user->isAdmin () && constant(strtolower($project_name).'_HasAdminCorner') == 'true')
					echo "&nbsp;<a href= '/".$project_name."/Admin-Corner' style='color:green;'>(Admin)</a>";
			} else {
				if ($user->isAdmin ())
					echo "&nbsp;<a href= '/Admin-Corner?pageId=1' style='color:green;'>(Admin)</a>";
			} 
		}else if (get_class($user) == 'guestuser'){
			echo "$user->mail";
		}else {
			echo $user->cn;
		}
		echo '&nbsp;'.$this->getElement('logout')->toHTML();
		echo '</form>';
		
	}
}

?>
