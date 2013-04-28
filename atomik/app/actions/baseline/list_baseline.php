<?php
Atomik::disableLayout();
Atomik::setView("baseline/list_baseline");
Atomik::needed("Data.class");
Atomik::needed("Project.class");

if (isset($_GET['id']) ? $data_id = $_GET['id'] : $data_id = "");
if (isset($_GET['project_id']) ? $project_id = $_GET['project_id'] : $project_id = "");
$line_counter=0;
$data = new Data;
$data->get($data_id);
$baseline_list = $data->getBaseline();
$project = new Project(array('project_id'=>$data->project_id,
							'sub_project_id'=>$data->lru_id));
$baseline_choice = $project->getBaseline();
