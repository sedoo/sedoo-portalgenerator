<?php
if (isset($_REQUEST['project_name']) && !empty($_REQUEST['project_name'])){
	$project_name = $_REQUEST['project_name'];
}
// require("inc-menu_haut.html");
?>
<nav id="site-navigation" class="main-navigation" role="navigation" aria-label="Menu principal / Main menu">
	<ul id="primary-menu" class="menu nav-menu" >
		<?php
			if(constant(strtolower($project_name).'_DisplayOnlyProjectOnTopBar') === 'true'){
				echo "<li>
						<a href='/".$project_name."/'>".$project_name."</a>
					 </li>";
				if(isset($MainProjects) && !empty($MainProjects)){
					if(isset($MainProjects[0]) && !empty($MainProjects[0])) {
		            	echo "<li class=\"menu-item-has-children\">
			         		<a href='/'>Other ".MainProject." databases</a>
					 		<ul class=\"sub-menu\">";
						foreach($MainProjects as $proj){
							if(isset($proj) && !empty($proj)){
								if($proj != $project_name)
									echo "<li><a href='/".$proj."/'>".$proj."</a></li>";
							}
						}
			    	echo "</ul>";
			    	}
			    	echo "</li>";
				}
			}else{			
				
				if(isset($OtherProjects) && !empty($OtherProjects)){
				echo "<li><a href='/'>".MainProject." database</a></li>";
					foreach($MainProjects as $proj){
						if(isset($proj) && !empty($proj)){
							echo "<li class=\"menu-item-has-children\"><a href='/".$proj."/'>".$proj."</a>";
							if(constant(strtolower($proj).'SubProjects') != ''){
								echo '<ul class=\"sub-menu\">';
								$subProj= explode(",", constant(strtolower($proj).'SubProjects'));
								foreach($subProj as $subpro){
									if(isset($subpro) && !empty($subpro)){
										$SP = str_replace(' ','-',$subpro);
										echo "<li><a href='/".$proj."/".$SP."/'>".$subpro."</a></li>";
									}
								}
								echo '</ul></li>';
							}
						}
					}
				}
				if(isset($OtherProjects) && !empty($OtherProjects)){
					if(isset($OtherProjects[0]) && !empty($OtherProjects[0]))
						echo "<li class=\"menu-item-has-children\"><a href='#'>More projects</a>
									<ul class=\"sub-menu\">";
					foreach($OtherProjects as $Otherproj){
						if(isset($Otherproj) && !empty($Otherproj)){
							echo "<li><a href='/".$Otherproj."/'>".$Otherproj."</a></li>";
						}
					}
					echo '</ul></li>';
				}
			}
		?>
	</ul>
</nav>

