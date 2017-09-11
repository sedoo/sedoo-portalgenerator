<?php

require_once ('extract/extract_download_form.php');
require_once ('editDataset.php');

$resultId = $_REQUEST['resultId'];

$form = new extract_download_form;
$form->createForm($resultId,$project_name);

$datsId = $_REQUEST['datsId'];
if (isset($datsId) && !empty($datsId)){
	echo "<h1>Dataset Edition</h1>";
	if (isset($resultId) && !empty($resultId)){
		echo "<br/><a style='font-size:110%;font-weight:bold;' href='/extract/downloadPub.php?project_name=$project_name&resultId=$resultId'>&lt;&lt;&nbsp;Back to download page</a><br/>";
	}
	editDataset($datsId,$project_name);
}else{
	if (isset($resultId) && !empty($resultId)){
        	if ($form->isLogged() && $form->reponse->isPublic()){
			$form->display();
		}else{
			if ( $form->reponse->isPublic() ){
				$form->displayPublicLogin('Public data access');
			}else{
				echo "<font size='3' color='red'>Not a public dataset. Click <a href='/extract/download.php?project_name=$project_name&resultId=$resultId'>here</a> to access this dataset.</font><br>";
				header("Location: /extract/download.php?project_name=$project_name&resultId=$resultId");
			}
		}
	}else{
		echo '<font size="3" color="red">No result to load.</font><br>';
	}
}

?>
