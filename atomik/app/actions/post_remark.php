<?php
//Atomik::disableLayout();
Atomik::needed('Date.class');
Atomik::needed('Data.class');
Atomik::needed('Remark.class');
Atomik::needed('Action.class');
Atomik::needed('Task.class');
Atomik::needed('User.class');
Atomik::needed('Project.class');
Atomik::needed('Review.class');
Atomik::needed("Date.class");
Atomik::set('tab_select','remark');

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : "";
$from = isset($_GET['from']) ? $_GET['from'] : "";
$copy_id = isset($_REQUEST['copy_id']) ? $_REQUEST['copy_id'] : "";
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;  
$limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 8;

if(isset($_REQUEST['context'])) {
  //$env_context = $_REQUEST['context'];
  $context_array=unserialize(urldecode(stripslashes((stripslashes($_REQUEST['context'])))));
  $context_array['project_id'] = isset($context_array['project_id']) ? $context_array['project_id'] : "";
  $context_array['sub_project_id'] = isset($context_array['sub_project_id']) ? $context_array['sub_project_id'] : "";
  $context_array['remark_status_id'] = isset($context_array['remark_status_id']) ? $context_array['remark_status_id'] : ""; 
  $context_array['user_id'] = isset($context_array['user_id']) ? $context_array['user_id'] : "";
  $context_array['category_id'] = isset($context_array['category_id']) ? $context_array['category_id'] : ""; 
  $context_array['data_id']= isset($_GET['show_application']) ? $_GET['show_application'] : Atomik::get('session/data_id');  
}  
else {
	$context_array['project_id']= isset($show_project) ? isset($show_project) : Atomik::get('session/current_project_id');
	$context_array['sub_project_id']=isset($show_lru) ? isset($show_lru) : "";
	$context_array['remark_status_id']=isset($show_status) ? isset($show_status) : "";
	$context_array['user_id']=isset($show_poster) ? isset($show_poster) : "";	
	$context_array['category_id']=isset($show_criticality) ? isset($show_criticality) : "";
	$context_array['data_id']= isset($_GET['show_application']) ? $_GET['show_application'] : Atomik::get('session/data_id');  
}
$data_id = isset($_REQUEST['data_id']) ? $_REQUEST['data_id'] : $context_array['data_id'];
$env_context = urlencode(serialize($context_array)); 
$remark = new Remark(&$context_array);
$project = new Project(&$context_array);
if ($id != "") {
	Atomik::set('title',"Update remark");
	Atomik::set('type',"update");	
	Atomik::set('remark_id',$id);
	$remark->get($id);
}
else if ($copy_id != ""){
	Atomik::set('title',"Copy remark");
	Atomik::set('type',"copy");	
	Atomik::set('remark_id',$copy_id);
	$remark->get($copy_id);
	$remark->resetDate();
}
else {
	$remark->reset();
	if ($data_id != "") {
		$remark->setDocument($data_id);
	}
	Atomik::set('title',"Post remark");
	Atomik::set('type',"new");
	if(Atomik::has('session/post_remark/justification')){
		$context = Atomik::get('session/post_remark/justification');
	}
	else {
		$context = "";
	}
	if(Atomik::has('session/post_remark/description')){
		$description = Atomik::get('session/post_remark/description');		
	}
	else {
		$description = "";
	}
	$date_open = Date::getTodayDate();
	if(Atomik::has('session/post_remark/date')){
		$date_expected = Date::convert_dojo_date(Atomik::get('session/post_remark/date'));		
	}
	else {
		$date_expected = $date_open;
	}	
	$date_closure   		= "";
}
//Atomik::set('select_menu',$html);
Atomik::set('menu',array('assignee' => 'Submitter',
						 'equipment' => 'Equipment'));
Atomik::set('css_title',"inspection");
Atomik::set('css_reset',"no_show");
Atomik::set('css_add',"no_show");
Atomik::set('css_page',"no_show");
