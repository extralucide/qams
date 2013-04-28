<?php
Atomik::needed("Data.class");
Atomik::needed('Tool.class');
Atomik::needed('Baseline.class');
$postArray = &$_POST;
// var_dump($_POST);
// exit();
$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;  
$limite = isset($_POST['limite']) ? (int)$_POST['limite'] : 8;
$search = isset($_POST['search']) ? $_POST['search'] : "";
$context = "";
if (isset($_POST['context'])){
	$context = $_POST['context'];
	$context_array=unserialize(urldecode(stripslashes((stripslashes($_POST['context'])))));
}
else{
	if (isset($_POST['show_project'])) {
		Atomik::set('session/project_id',$_POST['show_project']);
	}
	if (isset($_POST['show_lru'])){
		Atomik::set('session/sub_project_id',$_POST['show_lru']);
	}
	if (isset($_POST['show_status'])) {
		Atomik::set('session/remark_status_id',$_POST['show_status']);	
	}
	if (isset($_POST['show_category'])) {
		Atomik::set('session/category_id',$_POST['show_category']);	
	}
	if (isset($_POST['show_poster'])) {
		Atomik::set('session/user_id',$_POST['show_poster']);	
	}
	if (isset($_POST['show_baseline'])) {
		Atomik::set('session/baseline_id',$_POST['show_baseline']);	
	}
	if (isset($_POST['show_application'])) {
		Atomik::set('session/data_id',$_POST['show_application']);	
	}
	if (isset($_POST['search'])) {
		Atomik::set('session/search',$_POST['search']);	
	}
}
Atomik::redirect('inspection?page='.$page.'&limite='.$limite.'&search='.$search.'&context='.$context,false);
