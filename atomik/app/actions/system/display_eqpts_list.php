<?php
Atomik::needed('Project.class');
Atomik::setView("system/display_eqpts_list");
Atomik::disableLayout();

$project_id = isset($_GET['id']) ? $_GET['id'] : "";
$context_array['project_id']= $project_id;
$counter=0;
$project = new Project(&$context_array);
$list_data = $project->getSubProjectList();
Atomik::set('css_admin',"no_show");


