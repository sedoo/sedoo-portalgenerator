<?php

if($project_name != strtolower(MainProject)) {
	$root_admin_path = "".$project_name."/Admin-Corner";
}
else {
	$root_admin_path = "Admin-Corner";
}

?>

<h1>Admin corner</h1>
<section>
	<a href="/<?php echo "".$root_admin_path."";?>?adm&pageId">Registered Users</a>
	<a href="/<?php echo "".$root_admin_path."";?>?adm&pageId">Registration Requests</a>
	<a href="/<?php echo "".$root_admin_path."";?>?adm&pageId=3">Rejected Registration Requests</a>
	<?php
	if((isset($MainProjects) && !empty($MainProjects)) && (count($MainProjects) >=1))
		echo "<a href='/Admin-Corner?adm&pageId=16' >Registered Users in all ".MainProject." projects</a>";
	?>

	<!-- <a href="/<?php //echo "".$root_admin_path."";?>?adm&pageId=4">Participants</a> -->
</section>

<section>
	<h2>Journal</h2>
	<a href="/<?php echo "".$root_admin_path."";?>?adm&pageId=5&type=3&proj=1">Download history</a>
	<a href="/<?php echo "".$root_admin_path."";?>?adm&pageId=5&type=1&proj=1">Email notifications</a>
	<a href="/<?php echo "".$root_admin_path."";?>?adm&pageId=5&type=2&proj=1">Data updates</a>
	<a href="/<?php echo "".$root_admin_path."";?>?adm&pageId=5&add=1">Add a news (data update)</a>
	<a href="/<?php echo "".$root_admin_path."";?>?adm&pageId=5&type=5">Changes</a>
	<a href="/<?php echo "".$root_admin_path."";?>?adm&pageId=5&type=5&add=1">Add a change</a>
</section>

<section>
	<h2>URL management</h2>
	<a href="/<?php echo "".$root_admin_path."";?>?adm&pageId=6">Edit URL</a>
	<a href="/<?php echo "".$root_admin_path."";?>?adm&pageId=6&type=2">New IPSL dataset</a>
	<a href="/<?php echo "".$root_admin_path."";?>?adm&pageId=6&type=1">New Sedoo dataset</a>
	<a href="/<?php echo "".$root_admin_path."";?>?adm&pageId=6&type=4">New external dataset</a>
	<a href="/<?php echo "".$root_admin_path."";?>?adm&pageId=6&type=3">New map</a>
	<!--
	<a href="<?php //echo "".$root_admin_path."";?>?adm&pageId=8">Group management</a>	
	<a href="<?php //echo "".$root_admin_path."";?>/Admin-Corner?adm&pageId=8&create">New group</a>	-->
</section>

<section>
	<h2>Dataset overview</h2>
	<a href="/<?php echo "".$root_admin_path."";?>?adm&pageId=15">Available data</a>
	<a href="/<?php echo "".$root_admin_path."";?>?adm&pageId=13">Metadata quality</a>
	<a href="/<?php echo "".$root_admin_path."";?>?adm&pageId=11">Roles</a>
	<a href="/<?php echo "".$root_admin_path."";?>?adm&pageId=16">Archives</a>
</section>

<section>
	<h2>DOI management</h2>
	<a href="/<?php echo "".$root_admin_path."";?>?adm&pageId=14">DOI list</a>
	<a href="/<?php echo "".$root_admin_path."";?>?adm&pageId=14&newDoi">Register new DOI</a>
</section>

<section>
	<h2>Stats</h2>
	<a href="/<?php echo "".$root_admin_path."";?>?adm&pageId=7&type=0&proj=1">Data downloads</a>
	<a href="/<?php echo "".$root_admin_path."";?>?adm&pageId=7&type=1&proj=1">User registrations</a>
</section>

<section>
        <h2>Search</h2>
        <a href="/<?php echo "".$root_admin_path."";?>?adm&pageId=19">Search index</a>
</section>

<section>
	<h2>Database</h2>
	<a href="/<?php echo "".$root_admin_path."";?>?adm&pageId=9">Inserted datasets</a>
	<a href="/<?php echo "".$root_admin_path."";?>?adm&pageId=10">Params</a>
	<a href="/<?php echo "".$root_admin_path."";?>?adm&pageId=12">Requests</a>
</section>

<section>
	<h2>Contacts</h2>
	<a href="/<?php echo "".$root_admin_path."";?>?adm&pageId=17">Contact users</a>
</section>