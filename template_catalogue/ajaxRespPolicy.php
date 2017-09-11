<?php
require_once ("bd/data_policy.php");

$id=$_GET["id"];

header("Content-Type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
echo "<response>";

if ($id){
	$dp = new data_policy;
	$dp = $dp->getByQuery("SELECT data_policy.* FROM dataset JOIN data_policy USING (data_policy_id) WHERE dats_id = $id");
	
	if (isset($dp) && !empty($dp)){
		echo "<data_policy>".$dp[0]->data_policy_name."</data_policy>";
	}
		
	$query = "SELECT dats_use_constraints FROM dataset WHERE dats_id = $id";
	$bd = new bdConnect;
	if ($resultat = $bd->get_data($query)){
		$useConstraints = $resultat[0][0];
		if ($useConstraints){
			echo "<use_constraints>$useConstraints</use_constraints>";
		}
		
	}
}		

echo "</response>";

?>