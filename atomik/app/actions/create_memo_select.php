<?php 
Atomik::needed('Data.class');
if (isset($_GET['memo_id'])){
	$memo_id = $_GET['memo_id'];
	Atomik::set('css_display','no_show');
	Atomik::set('filename',$memo_id);	
}
else{
	$context_array['project_id']= Atomik::has('session/current_project_id')?Atomik::get('session/current_project_id'):Atomik::get('session/project_id');
	$context_array['sub_project_id']= Atomik::has('session/sub_project_id')? Atomik::get('session/sub_project_id'):Atomik::get('session/sub_project_id');

	$data = new Data;
	$memo_id = $data->createMemo($context_array['project_id'],
								$context_array['sub_project_id']);
										
	$reference = $data->reference;
	Atomik::set('reference',$reference);
	Atomik::set('memo_id',$memo_id);
}

Atomik::set('css_title',"data");
Atomik::set('css_reset',"no_show");
Atomik::set('css_add',"no_show");
Atomik::set('css_page',"no_show");
