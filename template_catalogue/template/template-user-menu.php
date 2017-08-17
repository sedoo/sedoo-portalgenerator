<h1>Your Account</h1>

<section>
	<a href="/Your-Account?p&pageId=1">Profile</a>
	<a href="/Your-Account?p&pageId=2">Change Password</a>
	<a href="/Your-Account?p&pageId=5&type=1">Notifications</a>
</section>

<section>
	<h2>Data access registration</h2>
	
	<a href="/Your-Account?p&pageId=11"><?php echo MainProject; ?> database 
		<?php 
			$user = unserialize($_SESSION['loggedUser']); 
			if (((isset($user->attrs[strtolower(MainProject).'Status']) && (!empty($user->attrs[strtolower(MainProject).'Status'])) && ($user->attrs[strtolower(MainProject).'Status'][0] == 'registered'))) || in_array(strtolower(MainProject),$user->attrs['memberOf'])) 
				echo "<span style='color:green;'> (registered)</span>";
			else if ($user->attrs[strtolower(MainProject).'Status'][0] == 'pending')
				echo "<span style='color:orange; '> (pending)</span>";
			else if ($user->attrs[strtolower(MainProject).'Status'][0] == 'rejected')
				echo "<span style='color:red; '> (rejected)</span>";
		?>
	</a>
	
	<?php 
	$user = unserialize($_SESSION['loggedUser']);
	reset($MainProjects);
	while($project = current($MainProjects)){
		if(constant(strtolower($project).'DataPolicy') != ''){
        	echo "<a href='/Your-Account?p&pageId=".(key($MainProjects)+15)."'>$project database";
			if ($user->attrs[strtolower($project).'Status'][0] == 'registered') 
				echo "<span style='color:green;'> (registered)</span>";
			else if ($user->attrs[strtolower($project).'Status'][0] == 'pending')
				echo "<span style='color:orange; '> (pending)</span>";
			else if ($user->attrs[strtolower($project).'Status'][0] == 'rejected')
				echo "<span style='color:red; '> (rejected)</span>";
			echo "</a>";
		}
    	next($MainProjects);
	}
	reset($MainProjects);
	reset($OtherProjects);
	while($project = current($OtherProjects)){
		if(constant(strtolower($project).'DataPolicy') != ''){
        	echo "<a href='/Your-Account?p&pageId=".(key($OtherProjects)+count($MainProjects)+15)."'>$project database";
			if ($user->attrs[strtolower($project).'Status'][0] == 'registered') 
				echo "<span style='color:green;'> (registered)</span>";
			else if ($user->attrs[strtolower($project).'Status'][0] == 'pending')
				echo "<span style='color:orange; '> (pending)</span>";
			else if ($user->attrs[strtolower($project).'Status'][0] == 'rejected')
				echo "<span style='color:red; '> (rejected)</span>";
			echo "</a>";
		}
    	next($OtherProjects);
	}
	reset($OtherProjects);
	?>
</section>

<section>
	<h2>Download history</h2>
	<a href="/Your-Account?p&pageId=5&type=3">Original files</a>
	<a href="/Your-Account?p&pageId=7">Homogenized datasets</a>
</section>

<section>
	<h2>PI corner</h2>
	<a href="/Your-Account?p&pageId=10">My datasets</a>
	<a href="/Your-Account?p&pageId=4">Duplicate dataset</a>
</section>

