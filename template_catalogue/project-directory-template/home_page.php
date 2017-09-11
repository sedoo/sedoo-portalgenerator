<?php
$titreMilieu = '<table><tr>
					<td width=400 style="background-color:white">#project database
					<td style="background-color:white">';
if (PORTAL_LogoPath != '') {
	$titreMilieu .= "<a href='" . PORTAL_WebSite . "' target='_blank'><img src='http://" . $_SERVER ['HTTP_HOST'] . "/" . PORTAL_LogoPath . "'  height=60/></a>&nbsp;";
}
if (constant ( strtolower ( $project_name ) . '_LogoPath' ) != '') {
	$titreMilieu .= "<a href='" . constant ( strtolower ( $project_name ) . 'WebSite' ) . "' target='_blank'><img src='http://" . $_SERVER ['HTTP_HOST'] . "/" . constant ( strtolower ( $project_name ) . '_LogoPath' ) . "'  height=60/></a>&nbsp;";
}
$titreMilieu .= '<tr></table>';
?>
<div class="column1-unit">
	<div class="">
		<h2>Welcome to The #project Database</h2>
		<p>
			<br>
		</p>
		<p>Put some text here</p>
		<center>
			<h2>
				<script>mail2("databasecontact","#project",1,"","Contact us <img src='../img/mail.jpg'/>")</script>
			</h2>
		</center>
		<p>
			<br>
			<br>
<?php
if (constant ( strtolower ( $project_name ) . '_HomePageAssoLogosPath' ) != '') {
	$Images = explode ( ",", constant ( strtolower ( $project_name ) . '_HomePageAssoLogosPath' ) );
	foreach ( $Images as $img ) {
		echo "<img src='http://" . $_SERVER ['HTTP_HOST'] . "/" . $img . "'  height='60'/>&nbsp;&nbsp";
	}
	echo '<br>';
}
?>	
	</div>
</div>
