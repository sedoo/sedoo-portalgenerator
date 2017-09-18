<?php
require_once ("bd/project.php");
function get_filtre_projets($project_name) {
	if (isset($project_name) && !empty($project_name) && $project_name != MainProject) {
		$project = new project ();
		$pro = $project->getIdByProjectName ( $project_name );
		if(isset($pro->project_id) && !empty($pro->project_id))
			return 'select project_id from project where project_id = ' . $pro->project_id . ' or pro_project_id IN (select project_id from project where project_id = ' . $pro->project_id . ' or pro_project_id = ' . $pro->project_id . ')';
	} else {
		return 'select project_id from project';
	}
}
?>
