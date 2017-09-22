<?php
header ( "Content-type: text/html; charset=UTF8" );
?>
<?php require("inc-head-js.html");?>
<body onLoad="initUrl('http://amma.sedoo.fr/worldMap/carte.cgi');initSimple();">

<div id="content-area" class="wrapper leftMenu">
   <nav role="leftMenu">
		<?php require('template/template-menu.php'); ?>
	</nav>

    <main role="main">
    	<section role="authUser">
            <?php include("logout.php"); ?>
        </section>
		<?php require('template/template-milieu.php'); ?>

	</main>                    
</div> <!-- end content-area -->

</body>
</html>

