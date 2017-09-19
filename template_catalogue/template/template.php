<?php
  header("Content-type: text/html; charset=UTF8");
?>
<?php require("inc-head.html");?>
<body>

<div id="content-area" class="wrapper leftMenu">
   <nav role="leftMenu">
        <?php require("template-menu.php");?>
    </nav>


    <main role="main">       
        <section role="authUser">
            <?php include("logout.php"); ?>
        </section>
        <?php require('template/template-milieu.php');?>
    </main>                    
</div> <!-- end content-area -->
<!-- <footer>
    <?php //require("inc-footer.html");?>
</footer> -->

</body>
</html>

