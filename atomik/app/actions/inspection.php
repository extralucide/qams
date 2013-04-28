<?php
Atomik::needed("Project.class");
Atomik::needed("User.class");
Atomik::needed("Date.class");
Atomik::needed("Data.class");
Atomik::needed("PeerReviewer.class");
Atomik::needed("Remark.class");
Atomik::needed("Tool.class");
Atomik::set('tab_select','remark');
$page = isset($_GET['page']) ? $_GET['page'] : 1;  
$limite = isset($_GET['limite']) ? $_GET['limite'] : 8;
if (isset($_REQUEST['context'])){
	$context = $_REQUEST['context'];
	$context_array=unserialize(urldecode(stripslashes((stripslashes($_REQUEST['context'])))));
}
else{
	$context_array['aircraft_id']= isset($_GET['show_aircraft']) ? $_GET['show_aircraft'] :(Atomik::has('session/current_aircraft_id')?Atomik::get('session/current_aircraft_id'):"");
	$context_array['project_id']= isset($_GET['show_project']) ? $_GET['show_project'] : (Atomik::has('session/current_project_id')?Atomik::get('session/current_project_id'):Atomik::get('session/project_id'));
	$context_array['sub_project_id']= isset($_GET['show_lru']) ? $_GET['show_lru'] : (Atomik::has('session/sub_project_id')?Atomik::get('session/sub_project_id'):"");
	$context_array['data_id']= isset($_GET['show_application']) ? $_GET['show_application'] : ( isset($_GET['data_id']) ? $_GET['data_id']:Atomik::get('session/data_id'));
	$context_array['remark_status_id']=isset($_GET['show_status']) ? $_GET['show_status'] :(Atomik::has('session/remark_status_id')?Atomik::get('session/remark_status_id'):"");
	$context_array['user_id']= isset($_GET['show_poster']) ? $_GET['show_poster'] : Atomik::get('session/user_id');
	$context_array['category_id']=isset($_GET['show_category']) ? $_GET['show_category'] : (Atomik::has('session/category_id')?Atomik::get('session/category_id'):"");	
	$context_array['baseline_id']=isset($_GET['show_baseline']) ? $_GET['show_baseline'] : (Atomik::has('session/baseline_id')?Atomik::get('session/baseline_id'):"");
	$context_array['remarks_search'] = isset($_GET['search']) ? $_GET['search'] : Atomik::get('session/search');	
}
if ($context_array['data_id'] != ""){
	$data = new Data;
	$data->get($context_array['data_id']);
	$context_array['project_id']= $data->project_id;
	$context_array['sub_project_id']= $data->lru_id;
}

Atomik::set("session/search",$context_array['remarks_search']);
$project = new Project(&$context_array);					
$remark = new Remark(&$context_array);
if ($context_array['data_id'] != ""){
	$remarks = new StatRemarks(&$context_array);
	$remarks->setDocument($context_array['data_id']);
	if ($remarks->amount_remarks > 0){
		/* defects bar graph */
		$bar_filename = '../result/remarks_bar.png';
		$remarks->count_all_remarks();		
		$remarks->drawBar($bar_filename);
		Atomik::set('stats_graphic_bar',$bar_filename);
		/* peers pie */
		$pie_filename = '../result/peer_reviewers_pie.png';
		$peer_reviewers = new PeerReviewer;
		$peer_reviewers->get($context_array['data_id']);
		$peer_reviewers->drawPie($pie_filename,"Authors of remarks");
		Atomik::set('stats_graphic_pie',$pie_filename);
	}
}
else{
	Atomik::set('stats_graphic_bar',Atomik::asset('assets/images/64x64/kchart.png'));
}
// var_dump($context_array);
$line_counter = 0;
$current_date = Date::getTodayDate();
$nb_remarks = $remark->count();
Atomik::set('nb_entries',$nb_remarks);
Atomik::needed("Tool.class");
$nbpage = Tool::compute_pages($nb_remarks,&$page,&$debut,$limite);						
Atomik::set('nb_pages',$nbpage);	
Atomik::set('page',$page);
Atomik::set('limite',$limite);
Atomik::set('menu',array('assignee' => 'Peer reviewer',
						'equipment' => 'Equipment'));
/* menu project */
$html=  '<form method="POST" action="'.Atomik::url('inspection').'">';
$html.= '<fieldset class="medium">';
$html.= Project::getSelectProject($context_array['project_id'],"active");
$html.= '</fieldset >';
$html.= '</form>';

/* menu sub project */
$html.= '<form method="POST" action="'.Atomik::url('inspection').'">';
$html.= '<fieldset class="medium">';
$html.= Project::getSelectSubProject(&$project,$context_array['sub_project_id'],"active");
$html.= '</fieldset >';
$html.= '</form>';

/* menu category */
$html.= '<form method="POST" action="'.Atomik::url('inspection').'">';
$html.='<fieldset class="medium">';
$html.= $remark->getSelectCategory($context_array['category_id'],"active");
$html.='</fieldset >';
$html.='</form>';

/* menu status */
$html.= '<form method="POST" action="'.Atomik::url('inspection').'">';
$html.='<fieldset class="medium">';
$html.= $remark->getSelectStatus($context_array['remark_status_id'],"active");
$html.='</fieldset >';
$html.='</form>';

/* menu data */
$html.= '<form method="POST" action="'.Atomik::url('inspection').'">';
$html.='<fieldset class="medium">';
$html.= Data::getSelectData($context_array['project_id'],
							$context_array['sub_project_id'],
							$context_array['data_id'],"active");
$html.='</fieldset >';
$html.='</form>';

/* menu user */
$html.= '<form method="POST" action="'.Atomik::url('inspection').'">';
$html.='<fieldset class="medium">';
$html.= User::getSelectAssignee(&$project,$context_array['user_id'],"active");
$html.='</fieldset >';
$html.='</form>';

/* menu baseline */
$html.='<form method="POST" action="'.Atomik::url('inspection', false).'">';
$html.='<fieldset class="medium">';
$html.= Project::getSelectBaseline(&$project,$context_array['baseline_id'],"active");
$html.='</fieldset >';
$html.='</form>';

Atomik::set('search',$context_array['remarks_search']);
Atomik::set('select_menu',$html);
Atomik::set('title',"Inspection");
Atomik::set('css_title',"inspection");
Atomik::set('css_add',"no_show_");
Atomik::set('css_page',"no_show_");
Atomik::set('url',"inspection");
Atomik::set('url_reset',"peer_review/reset_peer_review");
Atomik::set('url_add',Atomik::url('post_remark?show_application='.$context_array['data_id']."&page=".$page."&limite=".$limite,false));
Atomik::set('title_add',"Add a remark");
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
$serial_context = urlencode(serialize($context_array));
Atomik::set('context',$serial_context);
Atomik::set('url_first',Atomik::url('inspection',array('page'=>1,
														'show_poster'=>$context_array['user_id'],
														'show_application'=>$context_array['data_id'])));
Atomik::set('url_previous',Atomik::url('inspection',array('page'=>$page-1,
															'show_poster'=>$context_array['user_id'],
															'show_application'=>$context_array['data_id'])));
Atomik::set('url_next',Atomik::url('inspection',array('page'=>$page+1,
														'show_poster'=>$context_array['user_id'],
														'show_application'=>$context_array['data_id'])));
Atomik::set('url_last',Atomik::url('inspection',array('page'=>$nbpage,
														'show_poster'=>$context_array['user_id'],
														'show_application'=>$context_array['data_id'])));
/* TODO: serialize the parameters */
$params = array('page'=>$page,
				'limite'=>$limite,
				'nb_total'=>$nb_remarks,
				'show_aircraft'=>$context_array['aircraft_id'],				
				'show_project'=>$context_array['project_id'],
				'show_lru'=>$context_array['sub_project_id'],
				'show_application'=>$context_array['data_id'],
				'show_poster'=>$context_array['user_id'],
				'show_status'=>$context_array['remark_status_id']);
$url = "create_remarks_table";//.http_build_query($params);
$table_data = Atomik::url($url,$params);