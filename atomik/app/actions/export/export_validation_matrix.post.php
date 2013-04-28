<?php
$postArray = &$_POST;
// var_dump($_POST);
// var_dump($_FILES);
if (isset($postArray['project_id'])) {
	Atomik::set('session/project_id',$postArray['project_id']);
}
if (isset($postArray['sub_project_id'])){
	Atomik::set('session/sub_project_id',$postArray['sub_project_id']);
}
if (isset($postArray['type_id'])) {
	Atomik::set('session/type_id',$postArray['type_id']);	
}
exit();
//Atomik::redirect('export_validation_matrix',false);
