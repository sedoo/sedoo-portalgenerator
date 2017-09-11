<?php
require_once ("forms/login_form.php");
require_once ("bd/url.php");
require_once ("bd/journal.php");
require_once ("bd/dats_role.php");
require_once ("bd/dataset.php");
require_once ("mail.php");
require_once ("bd/mails_new.php");
require_once ("utils/ZipArchiveTest.php");
require_once ("/sites/kernel/#MainProject/conf.php");
require_once 'extract/requeteFilesXml.php';
require_once 'extract/sortieCGI.php';

class download_form extends login_form {
	var $filesList;
	var $path;
	var $jeu;
	var $pathJeu;
	var $logFile;
	var $selection;
	var $mailNotif;
	var $projectName;
	var $dataPath;
	var $queryString;
	var $jeuRoles;
	var $isPublic = false;
	function createForm($projectName, $queryString = '') {
		$this->projectName = $projectName;
		$this->queryString = $queryString;
		if (isset ( $_SESSION ['loggedUser'] )) {
			$this->user = unserialize ( $_SESSION ['loggedUser'] );
			$this->dataPath = DATA_PATH;
			// Users "projet" pour la saisie des metadata
			if (get_class ( $this->user ) == 'user') {
				$this->user = null;
			}
		}
		if (! $this->isLogged ()) {
			$jeuId = $_REQUEST ['datsId'];
			if (! isset ( $jeuId ) || empty ( $jeuId )) {
			} else {
				$this->jeuRoles = $this->getJeuRoles ( $jeuId );
			}
			if (isset ( $this->jeuRoles ) && in_array ( PUBLIC_DATA_ROLE, $this->jeuRoles )) {
				$this->isPublic = true;
				$this->createLoginForm ( 'Mail', $this->isPublic );
			} else {
				$this->createLoginForm ( 'Mail' );
			}
		}
	}
	function saveForm() {
		echo 'Selected:';
		for($i = 0; $i < count ( $this->filesList ); $i ++) {
			if ($this->getElement ( 'file_' . $i )->getChecked ()) {
				echo '<br>- ' . $this->filesList [$i];
			}
		}
	}
	function getJeuUrl($jeuId) {
		$url = new url ();
		$liste = $url->getLocalFileByDataset ( $jeuId );
		if (isset ( $liste ) && ! empty ( $liste )) {
			return $liste [0]->url;
		}
		return '';
	}
	
	/*
	 * Remplace les retours à la ligne par la balise br, sauf si le texte commence par une balise.
	 */
	private function format_readme($string) {
		$string = trim ( $string );
		if (strpos ( $string, '<' ) === 0) {
			return $string;
		} else {
			return nl2br ( $string );
		}
	}
	private function searchReadme($path) {
		$filename = $path . '/' . README_FILE;
		if (is_file ( $filename )) {
			$readme = file_get_contents ( $filename );
			if ($readme) {
				$readme = $this->format_readme ( $readme );
				return $readme;
			}
		} else if ($path == $this->pathJeu) {
			return null;
		} else {
			return $this->searchReadme ( dirname ( $path ) );
		}
	}
	function getReadme() {
		$readme = $this->searchReadme ( $this->path );
		
		if ($readme) {
			return $readme;
		} else {
			// Readme global
			$filename = $this->pathJeu . '/' . DOC_DIR . '/' . README_FILE;
			if (is_file ( $filename )) {
				$readme = file_get_contents ( $filename );
				if ($readme) {
					$readme = $this->format_readme ( $readme );
					return $readme;
				}
			}
			return null;
		}
	}
	function getTitle() {
		$href = '/Data-Search/?datsId='.$this->jeu->dats_id;
		if (isset($this->queryString) && !empty($this->queryString)){
			$href .= "&$this->queryString";
		}
		if (isset($this->projectName) && !empty($this->projectName)){
			$href .= "&project_name=$this->projectName";
		}
		return "<a href='$href'>".$this->getJeuNom().'</a>';
	}
	function getJeuNom() {
		return $this->jeu->dats_title;
	}
	function initJeu($jeuId) {
		$dts = new dataset ();
		$this->jeu = $dts->getById ( $jeuId );
	}
	function getJeuRoles($jeuId) {
		$dr = new dats_role ();
		$liste = $dr->getByDataset ( $jeuId );
		$ret = array ();
		if (isset ( $liste ) && ! empty ( $liste )) {
			foreach ( $liste as $role ) {
				$ret [] = $role->role->role_name;
			}
		}
		return $ret;
	}
	function initForm() {
		$jeuId = $_REQUEST ['datsId'];
		if (! isset ( $jeuId ) || empty ( $jeuId )) {
			echo "<font size=\"3\" color='red'><b>No dataset specified.</b></font><br>";
			return false;
		}
		$this->initJeu ( $jeuId );
		$jeuRoles = $this->getJeuRoles ( $jeuId );
		if (isset ( $jeuRoles ) && ! empty ( $jeuRoles ) && (in_array ( PUBLIC_DATA_ROLE, $jeuRoles ) || $this->user->isMemberOf ( $jeuRoles ) || $this->isAdmin ( $this->projectName ))) {
			$jeuUrl = $this->getJeuUrl ( $jeuId );
			if ((strpos ( $jeuUrl, 'file://localhost' . $this->dataPath . '/' ) === 0)) {
				$this->pathJeu = str_replace ( 'file://localhost' . $this->dataPath . '/', '', $jeuUrl );
			} else {
				echo "<font size=\"3\" color='red'><b>Data not found.</b></font><br>";
				return false;
			}
			if (isset ( $this->pathJeu ) && ! empty ( $this->pathJeu )) {
				$this->pathJeu = $this->dataPath . '/' . $this->pathJeu;
				// Répertoire à afficher
				$this->path = $this->pathJeu;
				$path = $_REQUEST ['path'];
				if (isset ( $path ) && ! empty ( $path )) {
					$this->path .= '/' . $path;
				}
				if (is_dir ( $this->path )) {
					// Liste des fichiers conservée dans la session
					$var = 'filesList_' . $this->getJeuNom () . '_' . $path;
					if (isset ( $_SESSION [$var] ) && ! empty ( $_SESSION [$var] )) {
						$this->filesList = unserialize ( $_SESSION [$var] );
					} else {
						$dir = opendir ( $this->path );
						$i = 0;
						while ( false !== ($file = readdir ( $dir )) ) {
							if (! in_array ( $file, array (
									".",
									"..",
									DOC_DIR,
									README_FILE 
							) )) {
								$this->filesList [$i] = $file;
								$i ++;
							}
						}
						closedir ( $dir );
						sort ( $this->filesList );
						$_SESSION [$var] = serialize ( $this->filesList );
					}
					// Création des éléments du formulaire
					$this->filterSelection ();
					$this->addElement ( 'submit', 'bouton_down', 'Submit', array (
							'onclick' => 'document.body.style.cursor=\'wait\';popup(\'popUpDiv\');' 
					) );
					$this->addElement ( 'submit', 'bouton_reset', 'Remove All' );
					$this->addElement ( 'submit', 'bouton_add', 'Add to selection' );
					$this->addElement ( 'submit', 'bouton_addAll', 'Select All' );
					$this->addElement ( 'submit', 'bouton_selectAll', 'Select All' );
					$this->addElement ( 'submit', 'bouton_unselectAll', 'Unselect All' );
					$this->addElement ( 'submit', 'bouton_downCurrent', 'Download All' );
					$this->addElement ( 'checkbox', 'email_notif', 'I want to be informed by email when this dataset is updated.', null, array (
							'onchange' => 'this.form.email_notif_hidden.value=' . (($this->mailNotif) ? 0 : 1) . ';submit();' 
					) );
					$this->getElement ( 'email_notif' )->setValue ( $this->mailNotif );
					$this->addElement ( 'hidden', 'email_notif_hidden' );
					$this->getElement ( 'email_notif_hidden' )->setValue ( $this->mailNotif );
					return true;
				} else {
					echo "<font size=\"3\" color='red'><b>Unable to find data corresponding to this dataset.</b></font><br>";
				}
			} else {
				echo "<font size=\"3\" color='red'><b>No directory specified</b></font><br>";
			}
		} else {
			echo "<font size=\"3\" color='red'><b>You cannot access this dataset.</b></font><br>";
		}
		return false;
	}
	function getRelativepath($path, $root) {
		return substr ( str_replace ( $root, "", $path ), 1 );
	}
	function getFileSize($path) {
		$size = filesize ( $path );
		$units = array (
				' B',
				' KB',
				' MB',
				' GB',
				' TB' 
		);
		for($i = 0; $size >= 1024 && $i < 4; $i ++)
			$size /= 1024;
		return round ( $size, 2 ) . $units [$i];
	}
	function displayForm($archive = null) {
		echo '<div id="blanket" style="display:none;">';
		echo '</div><div id="popUpDiv" style="display:none;text-align:center;">';
		echo "<p><p><p><p><font size=\"3\" color='orange'><b>We are processing your request. Please Wait...</b></font></div>";
		echo '<div id="popUpFilePreviewDiv" style="display:none;text-align:left;word-break:break-all;overflow:scroll;">';
		echo '</div>';
		if ($archive) {
			echo '<br><font size=\"3\" color="green"><b>Request successfull.&nbsp;Click <a href="/download.php?file=' . $archive . '">here</a>&nbsp;to download.</b></font><br><br>';
		}
		echo '<form action="' . $reqUri . '" method="post" name="frmdl" id="frmdl" >';
		echo '<div style="float: left;position: relative;max-width:380px;">';
		$this->displayDirectory ();
		echo '</div><div style="position: relative;float:right;max-width:260px;">';
		$this->displaySelection ( $archive );
		echo '</div>';
		echo '</form>';
	}
	
	/*
	 * Raccourcit le nom d'un fichier pour l'affichage.
	 */
	private function getShortFilename($filename, $maxSize = 30) {
		if (strlen ( $filename ) <= $maxSize) {
			return $filename;
		} else {
			$l = ($maxSize - 3) / 2;
			return substr ( $filename, 0, $l ) . '...' . substr ( $filename, - $l );
		}
	}
	private function getFileTitle($filename, $maxSize = 30) {
		if (strlen ( $filename ) <= $maxSize)
			return '';
		else
			return $filename;
	}
function displayDirectory() {
		$reqUri = $this->getReqUri ();
		$reqUriNoPath = $this->getReqUri ( false );
		echo '<table style="table-layout:auto;" ><tr>';
		$parent = $this->getRelativepath ( dirname ( $this->path ), $this->pathJeu );
		
		$onclick = 'onclick="document.forms[\'frmdl\'].submit();"';
		
		if ($this->path != $this->pathJeu) {
			echo '<th colspan = "4" ><a href="' . $reqUriNoPath . '&path=' . $parent . '&project_name=' . $this->projectName . '"><img src="/img/folder_up_petit.png" style="border:0px;" /></a>&nbsp;&nbsp;<b>Current Directory:&nbsp;' . $this->getRelativepath ( $this->path, $this->pathJeu ) . '</b></th></tr>';
		} else {
			echo '<th colspan = "4" ><b>Current Directory:&nbsp;/</b></th></tr>';
		}
		echo '<tr><th colspan = "4" align="center" >';
		echo $this->getElement ( 'bouton_addAll' )->toHTML ();
		echo '</th></tr>';
		for($i = 0; $i < count ( $this->filesList ); $i ++) {
			$isSelected = $this->isAlreadyInSelection ( $this->path . '/' . $this->filesList [$i] );
			echo '<tr>';
			if (is_dir ( $this->path . '/' . $this->filesList [$i] )) {
				echo '<td colspan="3" style="white-space : nowrap;"><a name="f' . $i . '"/>';
				if (! $isSelected) {
					echo '<a href="' . $reqUriNoPath . '&path=' . $this->getRelativepath ( $this->path . '/' . $this->filesList [$i], $this->pathJeu ) . '&project_name=' . $this->projectName . '">';
				}
				echo '<img src="/img/folder.png" style="border:0px;" />&nbsp;' . $this->filesList [$i];
				if (! $isSelected) {
					echo '</a>';
				}
				echo '</td>';
			} else {
				echo '<td colspan="2" style="white-space : nowrap;"><a title="' . $this->getFileTitle ( $this->filesList [$i], 35 ) . '" name="f' . $i . '"/><img src="/img/text.png" style="border:0px;" />&nbsp;' . $this->getShortFilename ( $this->filesList [$i], 30 ) . '</td>' . '<td>' . $this->getFileSize ( $this->path . '/' . $this->filesList [$i] ) . '</td>';
			}
			echo '<td align="center">';
			if ($isSelected) {
				echo '<img src="/img/ajouter-gris.png" style="border:0px;" title="Already selected" />';
			} else {
				
				if (is_file ( $this->path . '/' . $this->filesList [$i] )) {
					$finfo = new finfo ( FILEINFO_MIME_TYPE ); // Retourne le type mime
					$mime_type = $finfo->file ( $this->path . '/' . $this->filesList [$i] );
					if ($mime_type == 'text/plain') {
						$apercu = '';
						$f = fopen ( $this->path . '/' . $this->filesList [$i], 'r' );
						for($l = 1; $l <= 10; $l ++) {
							$ligne = fgets ( $f );
							if ($ligne) {
								$ligne = utf8_encode ( str_replace ( array (
										"\"",
										"'" 
								), "", $ligne ) );
								$apercu .= str_replace ( array (
										"\r\n",
										"\r",
										"\n" 
								), "<br />", $ligne );
							} else {
								break;
							}
						}
						fclose ( $f );
						$apercu .= '[...]';
						echo "<img id ='preview' onclick=\"popupWithContent('popUpFilePreviewDiv','$apercu')\" src='/img/oeil-icone-16.png' style='border:0px;' title='Preview' />&nbsp;&nbsp;";
					} else {
						if (pathinfo ( $this->path . '/' . $this->filesList [$i], PATHINFO_EXTENSION ) == 'nc') {
							unset ( $header );
							exec ( 'ncdump -h ' . $this->path . '/' . $this->filesList [$i], $header, $retour );
							if ($retour == 0) {
								$header = str_replace ( array (
										"\"",
										"\\\"" 
								), "", $header );
								$apercu = implode ( '<br />', $header );
								$apercu = utf8_encode ( $apercu );
								echo "<img id ='preview' onclick=\"popupWithContent('popUpFilePreviewDiv','$apercu')\" src='/img/oeil-icone-16.png' style='border:0px;' title='Preview' />&nbsp;&nbsp;";
							}
						}
					}
				}
				
				$this->addElement ( 'submit', 'bouton_add_' . $i, '', array (
						'style' => "border:none; color:#fff; background: transparent url('/img/ajouter.png') no-repeat top left; width:16px;height:16px;",
						'title' => 'Add to basket' 
				) );
				echo $this->getElement ( 'bouton_add_' . $i )->toHTML ();
			}
			echo '</td></tr>';
		}
		echo "</table>";
		echo " <script> $( '#popUpFilePreviewDiv' ).dialog({
	      autoOpen: false,
		  modal: true,
		  draggable: false,
		  resizable: false,
	      show: {
	        effect: 'blind',
	        duration: 1000
	      },
	      hide: {
	        effect: 'explode',
	        duration: 1000
	      },
		  width: 600,
		  height: 400 
	    });
		
	    $( 'img[id=preview]' ).click(function() {
	      $( '#popUpFilePreviewDiv' ).dialog( 'open' );
	    }); </script>";
	}
	function getReqUri($withPath = true) {
		return parse_url ( $_SERVER ['REQUEST_URI'], PHP_URL_PATH ) . '?datsId=' . $_GET ['datsId'] . "&$this->queryString" . (($withPath) ? '&path=' . $_GET ['path'] : '');
	}
	private function displaySelectedFiles() {
		foreach ( array_keys ( $this->selection ) as $i ) {
			$this->addElement ( 'submit', 'bouton_rem_' . $i, '', array (
					'style' => "border:none; color:#fff; background: transparent url('/img/supprimer.png') no-repeat top left; width:16px;height:16px;",
					'title' => 'Remove from basket' 
			) );
			
			$relPath = $this->getRelativepath ( $this->selection [$i], $this->pathJeu );
			echo '<tr><td colspan="2" title="' . $this->getFileTitle ( $relPath, 25 ) . '" >';
			if (is_dir ( $this->selection [$i] )) {
				echo '<img src="/img/folder.png" style="border:0px;" />&nbsp;';
			} else {
				echo '<img src="/img/text.png" style="border:0px;" />&nbsp;';
			}
			
			if (empty ( $relPath )) {
				echo '/';
			} else {
				echo $this->getShortFilename ( $relPath, 25 );
			}
			echo '</td><td>';
			echo $this->getElement ( 'bouton_rem_' . $i )->toHTML ();
			echo '</td></tr>';
		}
	}
	function displaySelection($archive = null) {
		$reqUri = $this->getReqUri ();
		echo $this->getElement ( 'email_notif_hidden' )->toHTML ();
		echo '<table style="table-layout:auto;" >';
		echo '<tr><th colspan="3"><b>Data Basket</b></th></tr>';
		if (! $archive) {
			if (count ( $this->selection ) == 0) {
				echo '<tr><th colspan="3" ></th></tr>';
				echo '<tr><td colspan="3">Selection is empty</td></tr>';
			} else {
				echo '<tr><th colspan="3" align="center" >';
				echo $this->getElement ( 'bouton_reset' )->toHTML ();
				echo '&nbsp;&nbsp;' . $this->getElement ( 'bouton_down' )->toHTML ();
				echo '</th></tr>';
				$this->displaySelectedFiles ();
				echo '<tr><th colspan="3" style="font-weight:normal;" >';
				echo $this->getElement ( 'email_notif' )->toHTML () . '&nbsp;' . $this->getElement ( 'email_notif' )->getLabel ();
				echo '</th></tr>';
			}
		} else {
			echo '<tr><td colspan="3"><a href="/download.php?file=' . $archive . '">Download</a></td></tr>';
		}
		echo "</table>";
	}
	function createArchive($log = false, $includeDoc = false) {
		$archiveName = uniqid ();
		$archiveFile = DATA_PATH_DL . '/' . $archiveName . '.zip';
		$archive = new ZipArchiveTest ();
		$ret = $archive->open ( $archiveFile );
		$archive->setWorkingDir ( $this->pathJeu );
		foreach ( array_keys ( $this->selection ) as $i ) {
			$this->addToArchiveTmp ( $archive, $this->selection [$i] );
			if ($log)
				$this->addToLog ( $this->selection [$i] );
		}
		journal::addDownloadEntry ( $this->user->mail, $this->jeu->dats_id, $this->selection, $this->mailNotif );
		if ($includeDoc && is_dir ( $this->pathJeu . '/' . DOC_DIR )) {
			$this->addToArchiveTmp ( $archive, $this->pathJeu . '/' . DOC_DIR );
		}
		$archive->close ();
		return $archiveName;
	}
	var $cpt = 0;
	function addToArchiveTmp($archive, $file) {
		if (is_dir ( $file )) {
			$ret = $archive->addDir ( $this->getRelativepath ( $file, $this->pathJeu ) );
		} else {
			$ret = $archive->addFile ( $this->getRelativepath ( $file, $this->pathJeu ) );
		}
		$this->cpt ++;
	}
	function downloadCurrentDir() {
		$this->selectAll ();
		return $this->download ();
	}
	function removeItemFromSelection($i) {
		unset ( $this->selection [$i] );
		sort ( $this->selection );
	}
	function addFileToSelection($file) {
		$this->selection [] = $file;
		$this->filterSelection ();
	}
	function addItemToSelection($i) {
		$this->addFileToSelection ( $this->path . '/' . $this->filesList [$i] );
	}
	function addToSelection() {
		for($i = 0; $i < count ( $this->filesList ); $i ++) {
			if ($this->getElement ( 'file_' . $i )->getChecked ()) {
				$this->addItemToSelection ( $i );
			}
		}
	}
	function addAllToSelection() {
		$this->addFileToSelection ( $this->path );
	}
	function clearSelection() {
		$this->selection = array ();
	}
	
	/**
	 * Détermine si file est dans la sous-arborescence de dir
	 *
	 * @param
	 *        	$file
	 * @param
	 *        	$dir
	 * @return boolean
	 */
	function isChild($file, $dir) {
		return (strpos ( $file, $dir ) === 0);
	}
	function isAlreadyInSelection($file) {
		foreach ( $this->selection as $selectedFile ) {
			if ($this->isChild ( $file, $selectedFile )) {
				return true;
			}
		}
		return false;
	}
	function filterSelection() {
		$this->selection = array_unique ( $this->selection );
		sort ( $this->selection );
		$previousDir = null;
		foreach ( array_keys ( $this->selection ) as $i ) {
			if (! $this->isChild ( $this->selection [$i], $this->pathJeu )) {
				unset ( $this->selection [$i] ); 
			} else if (isset ( $previousDir ) && $this->isChild ( $this->selection [$i], $previousDir )) {
				unset ( $this->selection [$i] ); 
			} else if (is_dir ( $this->selection [$i] )) {
				$previousDir = $this->selection [$i];
			}
		}
	}
	function downloadCGI() {
		$requete = new requeteFilesXml ( $this->user, $this->projectName, $this->jeu, $this->pathJeu );
		foreach ( array_keys ( $this->selection ) as $i ) {
			$requete->addFile ( $this->selection [$i] );
		}
		
		if ($this->mailNotif){
			journal::addAboEntry ( $this->user->mail,  $this->jeu->dats_id );
		}
		
		if (send_to_cgi_fichiers ( $requete->toXml (), $retour )) {
			$elts = explode ( ':', $retour, 2 );
			if ($elts [0] == '00') {
				$msg = "<font size=\"3\" color='green'>Request successfully sent. The result will be send to you by email.</font>";
			} else if ($elts [0] == '01') {
				$archiveName = null;
				$downloadUrl = trim ( $elts [1] );
				header ( "Location: $downloadUrl" );
				$msg = "<font size=\"3\" color='green'>Request successfully sent. Click <a href='$downloadUrl'>here</a> if you are no automatically redirected to the download page.</font>";
			} else {
				$msg = '<font size="3" color="red">Your Request was not processed due to technical reasons. Please contact the database administrator (' . ROOT_EMAIL . ').</font>';
			}
		} else {
			$msg = '<font size="3" color="red">Your Request was not processed due to technical reasons. Please contact the database administrator (' . ROOT_EMAIL . ').</font>';
		}
		$this->clearSelection ();
		return $msg;
	}
	function download() {
		$this->openLogFile ();
		$archiveName = $this->createArchive ( true, true );
		$this->closeLogFile ();
		mails::sendMailUser ( $this->user, $this->jeu, $this->projectName );
		$this->clearSelection ();
		return $archiveName;
	}
	/* LOG (Quel utilisateur a téléchargé quel fichier) */
	function openLogFile() {
		$this->logFile = fopen ( LOG_DL, 'a' );
	}
	function closeLogFile() {
		return fclose ( $this->logFile );
	}
	function addToLog($file) {
		$date = new DateTime ();
		$ligne = $date->format ( 'Y-m-d' ) . ';' . $this->user->mail . ';' . $this->getJeuNom () . ';' . $file;
		fwrite ( $this->logFile, $ligne );
		fwrite ( $this->logFile, PHP_EOL );
		// TODO enregistrer dans la base (table journal)
	}
}

?>
