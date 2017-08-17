<?php

$id=$_GET["id"];
$table=$_GET["table"];
$columns=$_GET["columns"];

header("Content-Type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
echo "<response>";

if (isset($table) && isset($columns) && (isset($id))){

	require_once("bd/".$table.".php");
	
	if ($id > 0){
		
		$obj = new $table;
		$obj = $obj->getById($id);
						
		foreach (split(";",$columns) as $column){
			
			if (strstr($column,"_id")){
				echo "<id name=\"".$column."\">";
				echo $obj->$column;
				echo "</id>";
			}else{
				echo "<column name=\"".$column."\">";
				echo $obj->$column;
				echo "</column>";
			}
		}

	}else{
		foreach (split(";",$columns) as $column){
				
			if (strstr($column,"_id")){
				echo "<id name=\"".$column."\">";
				echo 0;
				echo "</id>";
			}
		}
	}
}
echo "</response>";
?>

