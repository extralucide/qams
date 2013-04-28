<?php
$postArray = &$_POST;
//var_dump($_POST);
Atomik::needed("Tool.class");

if (isset($_POST['show_project'])) {
	Atomik::set('session/project_id',$_POST['show_project']);
}
if (isset($_POST['show_lru'])){
	Atomik::set('session/sub_project_id',$_POST['show_lru']);	
}
if (isset($_POST['show_type'])) {
	Atomik::set('session/review_type_id',$_POST['show_type']);		
}
if (isset($_POST['show_baseline'])){
	Atomik::add('context_array',array('baseline_id' => $_POST['show_baseline']));		
}
if (isset($_POST['context'])) {
	$context_array=unserialize(urldecode(stripslashes(($_POST['context']))));
	foreach($context_array as $key => $value){
		Tool::addkey($key,$value);
	}	
}
$context_array = Atomik::get('context_array');
$context = serialize($context_array);
//echo "TEST:";
//$context_array = Atomik::get('context_array');
//var_dump($context);
//exit(0);
Atomik::redirect('show_reviews');
