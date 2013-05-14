<?php
Atomik::needed('Action.class');
Atomik::needed('Project.class');
Atomik::needed('Review.class');
Atomik::needed("Date.class");
Atomik::needed("User.class");
Atomik::needed("Tool.class");

$page = isset($_REQUEST['page']) ? (int)$_REQUEST['page'] : 1;  
$limite = isset($_REQUEST['limite']) ? (int)$_REQUEST['limite'] : 8;
// var_dump($_GET);
$iterations_selected = isset($_GET['iterations']) ? (int)$_GET['iterations'] : 10; /* 10 months by default */
$granularity_selected = isset($_GET['granularity']) ? (int)$_GET['granularity'] : 2; /* Months by default */
Atomik::set('tab_select','action');
if (isset($_GET['tab'])){
	switch ($_GET['tab']){
		case "actions":
			Atomik::set('actions_highlight','active');
			break;	
		case "metrics":
			Atomik::set('metrics_highlight','active');
			break;		
	}
}
$context_array['aircraft_id']= Atomik::has('session/current_aircraft_id')?Atomik::get('session/current_aircraft_id'):"";
$context_array['project_id']= isset($_GET['show_project']) ? $_GET['show_project'] : (Atomik::has('session/current_project_id')?Atomik::get('session/current_project_id'):Atomik::get('session/project_id'));
$context_array['sub_project_id'] = Atomik::has('session/sub_project_id')?Atomik::get('session/sub_project_id'):"";
$context_array['review_id'] = Atomik::has('session/review_id')?Atomik::get('session/review_id'):"";
$context_array['action_status_id'] = Atomik::has('session/action_status_id')?Atomik::get('session/action_status_id'):"";
$context_array['user_id'] = Atomik::has('session/user_id')?Atomik::get('session/user_id'):"";
$context_array['submitter_id'] = Atomik::has('session/submitter_id')?Atomik::get('session/submitter_id'):"";
$context_array['assignee_id'] = Atomik::has('session/user_id')?Atomik::get('session/user_id'):"";
$context_array['criticality_id']=Atomik::has('session/severity_id')?Atomik::get('session/severity_id'):"";
$context_array['action_search']=isset($_GET['search']) ? $_GET['search'] :(Atomik::has('session/search')?Atomik::get('session/search'):"");

Atomik::set("session/search",$context_array['action_search']);

$review = new Review(&$context_array);
$project = new Project(&$context_array);
$id_up =  'id ASC, ';
$id_down =  'id DESC, ';
$expected_up = ' date_expected ASC, ';
$expected_down = ' date_expected DESC, ';
$open_up = ' date_open ASC, ';
$open_down = ' date_open DESC, ';
$action = new Action(&$context_array);
$status_list = Action::getStatusList();
$line_counter = 0;
$nb_actions = $action->new_count_actions();
Atomik::set('nb_entries',$nb_actions);
$nbpage = Tool::compute_pages($nb_actions,&$page,&$debut,$limite);						
Atomik::set('nb_pages',$nbpage);
$action->prepare();
$list_actions_lite = $action->execute($debut,$limite);
$dir_path_result = "../result/";
if (($nb_actions > 0)&&($context_array['action_search']=="")) {
	$pie_filename = $dir_path_result.'actions_pie_'.uniqid().'.png';
	$bar_filename = $dir_path_result.'actions_bar_'.uniqid().'.png';
	$spline_filename = $dir_path_result.'actions_spline_'.uniqid().'.png';
	Atomik::set('session/actions_graph',urlencode(serialize(array('actions_pie'=>$pie_filename,'actions_bar'=>$bar_filename,'actions_spline'=>$spline_filename))));
	/* draw pie chart */
	$actions_closed = $action->new_count_actions("closed");
	$actions_open = $action->new_count_actions("open");
	$actions['closed']=$actions_closed;
	$actions['open']=$actions_open;	
	$action->new_drawPie($actions,$pie_filename);
	
	/* draw bar chart */
	$context_array['assignee_id'] = Atomik::has('session/user_id')?Atomik::get('session/user_id'):"";
	$user = new User(&$context_array);
	$user->get_stat_actions (true); 
	if ($user->nb != 0) {	
		$action->new_drawBar(&$user,$bar_filename);
	}
	
	/* draw aera chart */
	$action->getAeraStats($spline_filename,$granularity_selected,$iterations_selected);	
	// $action->drawArea($stats,$abscissa,$spline_filename);
}
else {
	$pie_filename = "";
	$bar_filename = "";
	$spline_filename = "";
}	
/* set layout tags */
Atomik::set('search',$context_array['action_search']);
Atomik::set('url_reset',"action/reset_action");
Atomik::set('url',"action");
Atomik::set('url_add',Atomik::url('post_action'));
Atomik::set('title_add',"Add an action");
Atomik::set('page',$page);
Atomik::set('limite',$limite);
Atomik::set('title',"Actions");
Atomik::set('css_title',"action");
if ((($page >= $nbpage) && ($nbpage > 1))||(($page < $nbpage)&&($page != 1))){
	Atomik::set('css_page_previous','show');	
}
else{
	Atomik::set('css_page_previous','no_show');	
}

if ((($page==1) && ($nbpage > 1))||($page < $nbpage)) {
	Atomik::set('css_page_next','show');	
}
else{
	Atomik::set('css_page_next','no_show');	
}
Atomik::set('url',Atomik::url('actions'));
Atomik::set('url_first',Atomik::url('actions',array('page'=>1)));
Atomik::set('url_previous',Atomik::url('actions',array('page'=>$page-1)));
Atomik::set('url_next',Atomik::url('actions',array('page'=>$page+1)));
Atomik::set('url_last',Atomik::url('actions',array('page'=>$nbpage)));
Atomik::set('menu',array('assignee' => 'Assignee',
						'equipment' => 'Item'));
/* menu project */
$html=  '<form method="POST" action="'.Atomik::url('actions', false).'">';
$html.= '<fieldset class="medium">';
$html.= Project::getSelectProject($context_array['project_id'],"active",$context_array['aircraft_id']);
$html.= '</fieldset >';
$html.= '</form>';

/* menu sub project */
$html.= '<form method="POST" action="'.Atomik::url('actions', false).'">';
$html.= '<fieldset class="medium">';
$html.= Project::getSelectSubProject(&$project,$context_array['sub_project_id'],"active");
$html.= '</fieldset >';
$html.= '</form>';

/* menu review */
$html.= '<form method="POST" action="'.Atomik::url('actions', false).'">';
$html.= '<fieldset class="medium">';
$html.= $action->getSelectReview(&$review,$context_array['review_id'],"active");
$html .='</fieldset >';
$html .='</form>';

/* menu status */
$html.='<form method="POST" action="'.Atomik::url('actions', false).'">';
$html.='<fieldset class="medium">';
$html.= $action->getSelectStatus($context_array['action_status_id'],"active");
$html.='</fieldset >';
$html.='</form>';

/* menu submitter */
$list_users_pdo_statement = $project->getUsers();
if ($list_users_pdo_statement != false){
	$list_users = $list_users_pdo_statement->fetchAll();
}
else{
	$list_users = array();
}
$html.='<form method="POST" action="'.Atomik::url('actions', false).'">';
$html.='<fieldset class="medium">';
$html.='<label for="submitter_id">Submittter:</label>';
$html.='<select class="combobox" name="submitter_id" onchange="submit()">';
$html.='<option value=""/> --All--';
foreach($list_users as $row):
	$html.='<option value="'.$row['id'].'"';
	if ($row['id'] == $context_array['submitter_id']){ $html.=' SELECTED ';}
	$html.='>'.$row['lname'].' '.$row['fname'];
endforeach;
$html.='</select><br />';
$html.='</fieldset >';
$html.='</form>';

/* menu assignee */
$html.='<form method="POST" action="'.Atomik::url('actions', false).'">';
$html.='<fieldset class="medium">';
$html.= User::getSelectAssignee(&$project,$context_array['user_id'],"active");
$html.='</fieldset >';
$html.='</form>';

/* menu severity */
$html.='<form method="POST" action="'.Atomik::url('actions', false).'">';
$html.='<fieldset class="medium">';
$html.= $action->getSelectSeverity($context_array['criticality_id'],"active");
$html.='</fieldset >';
$html.='</form>';
Atomik::set('select_menu',$html);
