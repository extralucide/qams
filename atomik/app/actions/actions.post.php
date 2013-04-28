<?php
$postArray = &$_POST;
// var_dump($_POST);
// exit();
Atomik::needed('Tool.class');
$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;  
$limite = isset($_POST['limite']) ? (int)$_POST['limite'] : 8;
$search = isset($_POST['search']) ? $_POST['search'] : "";
$iterations = isset($_POST['iterations']) ? (int)$_POST['iterations'] : 10;
$granularity = isset($_POST['granularity']) ? (int)$_POST['granularity'] : 1;
if (isset($_POST['iterations'])){
	Atomik::redirect('actions?page='.$page.'&limite='.$limite.'&search='.$search.'&iterations='.$iterations.'&granularity='.$granularity.'&tab=metrics',false);
}
if (isset($_POST['granularity'])){
	Atomik::redirect('actions?page='.$page.'&limite='.$limite.'&search='.$search.'&iterations='.$iterations.'&granularity='.$granularity.'&tab=metrics',false);
}
if (isset($_POST['show_project'])) {
	Atomik::set('session/project_id',$_POST['show_project']);
}
if (isset($_POST['show_lru'])){	
	Atomik::set('session/sub_project_id',$_POST['show_lru']);		
}
if (isset($_POST['show_review'])) {
	Atomik::set('session/review_id',$_POST['show_review']);	
}	
if (isset($_POST['show_status'])) {
	Atomik::set('session/action_status_id',$_POST['show_status']);		
}
if (isset($_POST['show_poster'])) {
	Atomik::set('session/user_id',$_POST['show_poster']);		
}
if (isset($_POST['show_criticality'])) {
	Atomik::set('session/severity_id',$_POST['show_criticality']);		
}
if (isset($_POST['set_status'])) {
	$set_status_id = isset($_POST['set_status']) ? $_POST['set_status'] : "";
	//echo "TEST checkbox<br/>";
	$error = "";
	$status = "success";
	$action = new Action;
	$nb_items= count($_POST['data_check']);
	for ($index=0;$index < $nb_items;$index++)
	{
		$data_check_id = $_POST['data_check'][$index];
		$action->get($data_check_id);
		$result = $action->setStatus($set_status_id);
		if ($result) {
			$error .= "Set action ID <b>".$data_check_id."</b> with status <b>".Action::getStatusName($set_status_id)."</b><br/>";
		}
		else {
			$error = "Status setting failed !";
			$status = "failed";
			break;
		}
	}
	Atomik::flash($error,$status);
	Atomik::redirect('actions?page='.$page.'&limite='.$limite);
}
Atomik::redirect('actions?page='.$page.'&limite='.$limite.'&search='.$search,false);
