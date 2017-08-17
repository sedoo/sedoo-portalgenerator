<?php


require_once("forms/download_form.php");
//require_once("forms/download_form_event.php");

ini_set("max_execution_time", "0");
session_start();

//print_r($_POST);

$type = $_REQUEST['type'];
$project_name = $_REQUEST['project_name'];
$search = $_REQUEST['search'];
//if (isset($type) && $type == 'event'){
//	$form = new download_form_event();
//}else{
	$form = new download_form();
//}
$form->createForm($project_name,$search);

if (isset($_POST['bouton_public'])){
if ($form->validate()){
	$form->loginPublic();
}
$form->saveErrors();

}


//if (isset($form->user) || (isset($jeuRoles) && in_array(PUBLIC_DATA_ROLE,$jeuRoles))){
if (isset($form->user)){		
	if (isset($_SESSION['selection'])){
		//echo 'selection trouvé dans la session<br>';
		//echo $_SESSION['selection'].'<br>';
		$form->selection = unserialize($_SESSION['selection']);
	}else{
		$form->selection = array();
	}
	if (isset($_SESSION['mailNotif'])){
	//	echo 'mailNotif trouvé dans la session<br>';
          //      echo $_SESSION['mailNotif'].'<br>';
		$form->mailNotif = unserialize($_SESSION['mailNotif']);
	}

	if (count($_POST) > 0) $form->mailNotif = $_POST['email_notif_hidden'];
	
	if ($form->initForm())
        {
	  echo '<div id="aide"></div>';		
// jlb add (link to metadata on title)
          if( isset($_REQUEST['jeu']) )
	    echo '<h1><a href='."/$project_name/?editDatsId=".
                    $_REQUEST['jeu'].">".$form->getTitle().'</a></h1><br/>';
          else
	    echo '<h1>'.$form->getTitle().'</h1><br/>';
		
		echo '<br/><p>'.$form->getReadme().'</p>';

		if ($search){
			echo "<br /><a href='/$project_name/Search-result' style='font-size:110%;font-weight:bold;'>&lt;&lt;&nbsp;Back to search result</a><br /><br />";
		}
		
		if (isset($_POST['bouton_down'])){
			//$archive = $form->download();
			$msg = $form->downloadCGI();
			
			echo $msg;

		}else{
			$archive = null;
//			print_r($_POST);
			foreach(array_keys($_POST) as $key){
                        	if ( strpos($key,'bouton_rem_') === 0){
					$mod = substr($key,11);		
					$form->removeItemFromSelection($mod);
				}else if ( strpos($key,'bouton_add_') === 0){
					$mod = substr($key,11);
					$form->addItemToSelection($mod);
				}
			}

			//$mod = $_REQUEST['mod'];
			if (isset($_POST['bouton_reset'])){
				$form->clearSelection();
			//}else if (isset($_POST['bouton_rem_'.$mod])){
				//echo "remove $mod<br>";
			//	$form->removeItemFromSelection($mod);
			//}else if (isset($_POST['bouton_add_'.$mod])){
				//echo "add $mod<br>";
			//	$form->addItemToSelection($mod);
			}else if (isset($_POST['bouton_addAll'])){
				$form->addAllToSelection();
			}
			$form->displayForm($archive);
		}
		
//		$form->displayForm($archive);
		
		$_SESSION['selection'] = serialize($form->selection);
		$_SESSION['mailNotif'] = serialize($form->mailNotif);
	}else{

	}
/*}else if (isset($form->jeuRoles) && in_array(PUBLIC_DATA_ROLE,$form->jeuRoles)){
	$form->displayPublicLogin('Public data access');
*/
}else $form->displayLGForm("",true);


?>
