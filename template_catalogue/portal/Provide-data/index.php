<?php
if (!isset($_SESSION))
        session_start();
require_once ('conf/conf.php');
$project_name = explode ( '.', $_SERVER['SERVER_NAME'] )[0]; //"#MainProject";
$project_url="/#MainProject";
$titreMilieu="Provide data";
ob_start();
?>

<div class="column1-unit">
<br><br>
<div class=""><p><strong>Provide <i>in situ</i> data</strong></p>
<p><?php echo MainProject; ?> data providers are invited to upload their data files on the <?php echo MainProject; ?> ftp site: <b><?php echo Portal_FTP_Site; ?></b> . To achieve it you may use a ftp client, like filezilla or coreftp, and your portal login and password to connect. 
If not already done, you can register at the following address:
<a href="<?php echo 'https://'.$_SERVER['HTTP_HOST']; ?>/User-Account-Creation"><?php echo 'https://'.$_SERVER['HTTP_HOST']; ?>/User-Account-Creation </a> 
</p>
<p>When possible, please convert your datafiles into netCDF or CSV format for data exchange. 
<p>Once connected to the FTP, please</p>
<ul>
<li>create your own directory</li>
<li>upload your data files</li>
</ul>

<p>
Do not hesitate to provide any available documentation about the data. It will be automatically sent to every user who will download the dataset.
In particular, a readme file describing the data files content and format is welcome. 
</p>
<p>
If not already done, <b>add or update  a metadata form </b>describing the datasets you provide at the 
following address:
<a href="<?php echo 'https://'.$_SERVER['HTTP_HOST']; ?>/portal/Provide-metadata/" >
<?php echo 'https://'.$_SERVER['HTTP_HOST']; ?>portal/Provide-metadata</a>
<p>
After uploading your data, please <b>inform us</b> by email: <b><?php if (defined( 'Portal_Contact_Email' )) echo Portal_Contact_Email ;?></b>
so that we can make the data avalaible.  Don't forget to mention the following items:
<ul>
<li>The name of the FTP directory where you uploaded your data
<li>The title of the metadata form describing your dataset
<li>Which data policy (Public, <?php echo MainProject; if(constant(strtolower($project_name).'_HasAssociatedUsers') == 'true'){ echo ", ".$project_name." Core or Associated Users";} else { echo " or ".$project_name." Core Users"; }?> Access)  
and "use constraints" should be applied to your data.
Use contraints example: Permission is granted to use these data and images in research and publications when accompanied by the following statement:
"Data were obtained from the <?php echo MainProject; ?> program, sponsored by Grants <?php echo MainProject."/".$project_name;?> [ and the relevant project or Institution or Lab]."
</ul>
<br>
<p><strong>Provide model outputs or value-added datasets</strong></p>

<p>Large dataset providers are invited to contact  <b><?php if (defined( 'Portal_Contact_Email' ) ) echo Portal_Contact_Email;?></b> in order to define the best way to upload the data.</p></div>
</div>
<?php
  $milieu = ob_get_clean();
  include("template.php");
?>
