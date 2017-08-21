<?php
//require_once("ldap/portalUser.php");
require_once("forms/download_form_ipsl.php");
require_once ("utils/elastic/ElasticSearchUtils.php");

session_start();
$form = new download_form_ipsl();

echo "<h1>$project_name Data FTP Access</h1><br/>";

if ( array_key_exists('terms',$_REQUEST) ){
	ElasticSearchUtils::addBackToSearchResultLink();
}

/************************************************/
/*Fonctions                                     */
/************************************************/
function is_empty($var, $allow_false = false, $allow_ws = false) {
    if (is_null($var) || ($allow_ws == false && trim($var) == "" && !is_bool($var)) || ($allow_false === false && is_bool($var) && $var === false) || (is_array($var) && empty($var))) {   
        return true;
    } else {
        return false;
    }
}


/************************************************/
/* Initialisation des variables par GET ou POST */
/************************************************/
//
// Lien FTP vers les donnees:
if (array_key_exists("LnkFTP",$_POST)) $LnkFTP=$_POST['LnkFTP'];
elseif (array_key_exists("LnkFTP",$_GET)) $LnkFTP=$_GET['LnkFTP'];
if(!isset($LnkFTP)|| is_empty($LnkFTP)) $LnkFTP = "ftp://ftp.climserv.ipsl.polytechnique.fr/";

if (array_key_exists("open",$_POST)) $open=true;
elseif (array_key_exists("open",$_GET)) $open=true;
else $open=false;

if ($form->isPortalUser()){

$login = $form->user->mail;
$password = $form->user->userPassword;

//
// Lien FTP avec Login et Mot de passe :
if ( $login != "") {
  $ProtPos=strpos($LnkFTP,"://");
  if ( $ProtPos === FALSE ) {
    $LnkFTPProt="ftp";
    $LnkFTPHost=substr($LnkFTP,0,strpos($LnkFTP,"/"));
    $LnkFTPDirc=substr($LnkFTP,strlen($LnkFTPHost),strlen($LnkFTP)-strlen($LnkFTPHost));
  } else {
    $LnkFTPProt=substr($LnkFTP,0,$ProtPos);
    $LnkFTPHost=substr($LnkFTP,$ProtPos+3,strpos($LnkFTP,"/",$ProtPos+3)-strlen($LnkFTPProt)-3);
    $LnkFTPDirc=substr($LnkFTP,strlen($LnkFTPProt."//:".$LnkFTPHost),strlen($LnkFTP)-strlen($LnkFTPProt."//:".$LnkFTPHost));
  }
  if ( strtolower($LnkFTPProt) == "ftp") {
    if ( strpos($password, '?') === FALSE && $password != "" ) {
      $LnkFTPFull = $LnkFTPProt."://".$login.":".$password."@".$LnkFTPHost.$LnkFTPDirc;
    } else {
      $LnkFTPFull = $LnkFTPProt."://".$login."@".$LnkFTPHost.$LnkFTPDirc;
    }
  } else {
    $LnkFTPFull = $LnkFTPProt."://".$LnkFTPHost.$LnkFTPDirc;
  }
}

// Acces FTP
/******************/

if ($open){
?>
<iframe src="<?php echo $LnkFTPFull; ?>" width="640px" height="640px" frameborder="0"></iframe>
<?php
}else{
if ( strtolower($LnkFTPProt) == "ftp" ) {
//echo $_SERVER['REQUEST_URI'].'<br>';
if (strpos($_SERVER['REQUEST_URI'],'?')){
        $reqUri = substr($_SERVER['REQUEST_URI'],0,strpos($_SERVER['REQUEST_URI'],'?'));
}else{
        $reqUri = $_SERVER['REQUEST_URI'];
}
$openUri = $reqUri."?LnkFTP=$LnkFTP&open";
?>
<p>The requested dataset can be downloaded directly either from your browser or using a FTP Client.</p>
<ul>
  <li>If you download the data from a web browser or other URL-based tool, you can find the requested data at:</li>
</ul>

<?php
/*<center><a target="_blank" href="<?php echo $LnkFTPFull; ?>"><?php echo $LnkFTPFull; ?></a></center><br/>*/
?>

<center><a href="<?php echo $openUri; ?>"><?php echo $LnkFTP; ?></a></center><br/>

<ul>
  <li>If you connect to the FTP server from the command line or a FTP client:
  <ul>
    <li>Log in with your <?php echo $project_name ?> <b>Login</b> and <b>Password</b> to:<br/><b><?php echo $LnkFTPHost; ?></b></li>
    <li>and retrieve the requested files from the following directory:<b><br/><?php echo $LnkFTPDirc; ?></b></li>
  </ul></li>
</ul>

<?php
  } else {
?>
<p>The requested data can be downloaded directly from you browser at the followin address:</p>
<center><a target="_blank" href="<?php echo $LnkFTPFull; ?>"><?php echo $LnkFTPFull; ?></a></center><br/>
<?php
  }
}

}else if(isset($form->user)){
?>
<p>The access to this dataset is restricted to the <?php echo $project_name ?> registered users.</p>
<?php
}else{
?>
	<p>The access to this dataset is restricted to the <?php echo $project_name ?> participants. 
Please sign in to access this dataset. 
If you don't have an ID yet, you can apply for an account at 
<a href='<?php echo 'http://'.$_SERVER['HTTP_HOST'].'/'.$project_name; ?>/Data-Access-Registration'>the registration page</a>.</p>
<?php
	$form->displayLGForm();
}
//print_r($loggedUser);


?>
