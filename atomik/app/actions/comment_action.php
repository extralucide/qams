<?php
Atomik::needed("Action.class");
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : "";

if(isset($_REQUEST['context'])) {
  $context_array=unserialize(urldecode(stripslashes((stripslashes($_REQUEST['context'])))));
  $context_array['project_id'] = isset($context_array['project_id']) ? $context_array['project_id'] : "";
  $context_array['sub_project_id'] = isset($context_array['sub_project_id']) ? $context_array['sub_project_id'] : "";
  $context_array['review_id'] = isset($context_array['review_id']) ? $context_array['review_id'] : "";  
  $context_array['action_status_id'] = isset($context_array['action_status_id']) ? $context_array['action_status_id'] : ""; 
  $context_array['user_id'] = isset($context_array['user_id']) ? $context_array['user_id'] : "";
  $context_array['criticality_id'] = isset($context_array['criticality_id']) ? $context_array['criticality_id'] : "";    
}  
else {
	$context_array['project_id']= isset($show_project) ? isset($show_project) : Atomik::get('session/current_project_id');
	$context_array['sub_project_id']=isset($show_lru) ? isset($show_lru) : "";
	$context_array['review_id']=isset($show_review) ? isset($show_review) : "";
	$context_array['action_status_id']=isset($show_status) ? isset($show_status) : "";
	$context_array['user_id']=isset($show_poster) ? isset($show_poster) : "";	
	$context_array['criticality_id']=isset($show_criticality) ? isset($show_criticality) : "";	
}
$env_context = urlencode(serialize($context_array)); 
$action = new Action(&$context_array);
$action->get($id);
if ($action->getStatusId() == 9){
	$new_comment = "<p>This action is closed, it is not possible to add a comment</p>";
}
else{
	$new_comment = "";
}
$reply_action_text = $action->getComment();
$button = "Promote";
Atomik::set('css_reset',"no_show");
Atomik::set('title',"Comment action");
Atomik::set('css_title',"action");
Atomik::set('css_add',"no_show");
Atomik::set('css_page',"no_show");
Atomik::set('button',$button);
