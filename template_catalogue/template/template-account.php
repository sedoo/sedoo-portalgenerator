<?php
  header("Content-type: text/html; charset=UTF8");
?>
<?php require("inc-head.html");?>
<body>

<div id="content-area" class="wrapper leftMenu">
   <nav role="leftMenu">
	<?php require("template-user-menu.php");?>

	</nav>

    <main role="main">
		<section role="authUser">
	   		<?php include("logout.php"); ?>
	   	</section>
		<?php echo "<h1>".$titreMilieu."</h1>";?>
        <?php echo $milieu;?>
    </main>                    
</div> <!-- end content-area -->

</body>
</html>

