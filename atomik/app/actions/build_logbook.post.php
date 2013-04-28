<?php
$postArray = &$_POST;
//var_dump($_POST);
Atomik::needed('Tool.class');
$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;  
$limite = isset($_POST['limite']) ? (int)$_POST['limite'] : 8;
$search = isset($_POST['search']) ? $_POST['search'] : "";
// if (isset($_POST['show_project'])) {
	// Atomik::add('context_array',array('project_id' => $_POST['show_project']));	
	// Atomik::set('session/project_id',$_POST['show_project']);
	// if (Atomik::has('context_array/sub_project_id')){
		// Atomik::delete('context_array/sub_project_id');
	// }
// }
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
if (isset($_POST['show_baseline'])) {
	Atomik::set('session/baseline_id',$_POST['show_baseline']);	
}
// if (isset($_POST['context'])) {
	// $context_array=unserialize(urldecode(stripslashes(($_POST['context']))));
	// foreach($context_array as $key => $value){
		// Tool::addkey($key,$value);
	// }	
// }
// $context_array = Atomik::get('context_array');
// $context = serialize($context_array);
//echo "TEST:";
//$context_array = Atomik::get('context_array');
//var_dump($context);
//exit(0);
// exit(0);
Atomik::redirect('build_logbook',false);
