<?php
  header("Content-type: text/html; charset=UTF8");
?>
<?php require("inc-head.html");?>
<body>

<!-- <header id="masterhead" class="site-header" role="banner">
<?php //require("inc-header.html");?>


<div class="wrapper">
<?php 
// Main navigation > Menu top
//require("template-haut.php");
?> 
</div>
</header>

<div id="breadcrumbs">
    <div class="wrapper">
    <h1><?php //echo $project_name;?> database</h1>
    
    </div>
</div> -->



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
<!-- <footer>
    <?php //require("inc-footer.html");?>
</footer> -->

</body>
</html>

