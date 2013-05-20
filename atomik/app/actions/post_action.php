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
Atomik::set('tab_select','action');
Atomik::set('action_highlight','active');
/* Form */
Atomik::set('menu',array('project'=>'Project',
							'action_context' => 'Context',
							'lru' => 'Equipment',   
							'review' => 'Review',
							'status' => 'Status',
							'submitter' => 'Submitter',
							'username' => 'Assignee',
							'description' => 'Action',
							'criticality' => 'Severity',
							'date_open' => 'Date opening',
							'date_expected' => 'Due closure date'));
$context_array['aircraft_id']= Atomik::has('session/current_aircraft_id')?Atomik::get('session/current_aircraft_id'):"";
$context_array['project_id']= isset($_GET['show_project']) ? $_GET['show_project'] : (Atomik::has('session/current_project_id')?Atomik::get('session/current_project_id'):Atomik::get('session/project_id'));
$context_array['sub_project_id']=isset($_GET['show_lru']) ? $_GET['show_lru'] : (Atomik::has('session/sub_project_id')?Atomik::get('session/sub_project_id'):"");
$context_array['review_id']=isset($_GET['review']) ? $_GET['review'] : "";
$context_array['action_status_id']=isset($_GET['show_status']) ? $_GET['show_status'] : "";
$context_array['user_id']=isset($_GET['show_poster']) ? $_GET['show_poster'] : (Atomik::has('session/user_id')?Atomik::get('session/user_id'):"");
$context_array['criticality_id']= isset($_GET['show_criticality']) ? $_GET['show_criticality'] : "";							
$id = isset($_GET['id']) ? $_GET['id'] : "";
$copy_id = isset($_REQUEST['copy_id']) ? $_REQUEST['copy_id'] : "";
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;  
$limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 8;

$update = "";
$update_id = "";
$msg = "";
$html="";
$remark_id = "";

if($copy_id != ""){
	$title = "Copy action {$copy_id}";
	// $id = $copy_id;
	$copy = "yes";
	$action = new Action(&$context_array);
	$action->get($copy_id);
	$project_selected 		= $action->getProjectId();
	$sub_project_selected	= $action->getSubProjectId();
	$context				= stripslashes($action->getContext());
	$description    		= $action->getDescription();
	$date_open      		= $action->date_open_dojo;
	$date_expected  		= $action->date_expected_dojo;
	$date_closure 			= $action->date_closure;
	$context_array['project_id']= $action->getProjectId();
	$context_array['review_id']= $action->getReviewId();
	$project = new Project(&$context_array);
	$review = new Review(&$context_array);
	$review_list = $review->getReviewList();	
}
else{
	if ($id != "") {
	   $title = "Update action {$id}";
	   $update = "update";
	   $update_id = $id;
	   $copy = "no";
	   // $action = new Action(&$context_array);
	   // $action->get($id);
	   // $context_array['project_id']= $action->getProjectId();
	}
	else {
		$title = "Post an action";
		$copy = "no";
	}
	$project = new Project(&$context_array);
	$review = new Review(&$context_array);
	$context_array['sub_project_id']="";
	$review_list = $review->getReviewList();
	if ($id != 0) {
		$default_context = "";
		$default_description = "";
		$action = new Action(&$context_array);
		$action->get($id);
		$project_selected 		= $action->getProjectId();
		$sub_project_selected	= $action->getSubProjectId();
		$context				= stripslashes($action->getContext());
		$description    		= $action->getDescription();
		$date_open      		= $action->date_open_dojo;
		$date_expected  		= $action->date_expected_dojo;
		$date_closure 			= $action->date_closure;
		/* Is there a file attached ? */
		if ($action->attachment_name != "empty") {
			$html .= '<h3>Attachment</h3><p class="vert" style="width:100%"><a href="../'.$action->link.'"><img alt="Open document" title="Open document '.$action->attachment_name.'" width="32" height="32" border="0" src="'.$action->link_mime.'" /></a>';
			$html .= '<a href="'.Atomik::url("action/remove_action_attachment",array("id"=>$action->id)).'" ></a>'.$action->attachment_name;
			$html .= "<img style='padding-left:5px;padding-top:5px' border='0' width='12' height='12' src='".Atomik::asset('assets/images/32x32/agt_action_fail.png')."' alt='Remove link' title='Remove link' onclick='return confirmFileAttachRemove()' /></p>";
		}
	}
	else {
		$default_context = "";
		$default_description = "";
		if ($remark_id != "") {
			$current_remark = new Get_Remark($remark_id);
			$item = $current_remark->application;
			$version = $current_remark->version;
			$default_context = "Remark id ".$remark_id." of the peer review of data {$item} issue {$version}";
			$sql_query = "SELECT description,justification FROM bug_messages WHERE id = ".$remark_id;
			$result = A('db:'.$sql_query);
			if ($result !== false){
				$row = $result->fetch(PDO::FETCH_OBJ);		
				$default_description = "<h3>Problem Description:</h3>".$row->description."<h3>Response:</h3>".$row->justification."<h3>Action:</h3>";
			}
			else{
				$default_description = "";
			}
		}
		$row=array("context"=>"$default_context",
					"Description"=>"$default_description",
					"criticality"=>"1");
		if ($context_array['review_id'] != ""){
			/* get sub project linked to this review */
			$review->get($context_array['review_id']);
			$context_array['project_id'] = $review->project_id;
			$context_array['sub_project_id'] = $review->lru_id;
			$project_selected 		= $review->project_id;
			$sub_project_selected 	= $review->lru_id;
		}
		else{
			$project_selected 		= $context_array['project_id'];
			$sub_project_selected 	= $context_array['sub_project_id'];
		}
		$action = new Action(&$context_array);
		if(Atomik::has('session/post_action/action_context')){
			$context = Atomik::get('session/post_action/action_context');
		}
		else {
			$context = "";
		}
		if(Atomik::has('session/post_action/description')){
			$description = Atomik::get('session/post_action/description');		
		}
		else {
			$description = "";
		}
		$date_open = Date::getTodayDate();
		if(Atomik::has('session/post_action/date_expected')){
			$date_expected = Date::convert_dojo_date(Atomik::get('session/post_action/date_expected'));	
		}
		else {
			$date_expected = $date_open;
		}	
		$date_closure   		= "";
	}
}
$submitter_selected		= $action->getSubmitterId();
$user_selected			= $action->getAssigneeId();
$review_selected 		= $action->getReviewId();
$status_selected   		= $action->getStatusId();
$severity_selected		= $action->getSeverityId();
$list_users_pdo_statement = $project->getUsers();
if ($list_users_pdo_statement != false){
	$list_users = $list_users_pdo_statement->fetchAll();
	if (User::getCompanyUserLogged() != "ECE"){
		foreach($list_users as $key => &$user):
			$user['fname'] = str_rot13($user['fname']);
			$user['lname'] = str_rot13($user['lname']);
		endforeach;
	}	
}
else{
	$list_users = array();
}

Atomik::set('select_menu',$html);
Atomik::set('css_reset',"no_show");
Atomik::set('title',$title);
Atomik::set('css_title',"action");
Atomik::set('css_add',"no_show");
Atomik::set('css_page',"no_show");
