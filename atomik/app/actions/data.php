<?php
Atomik::needed("Project.class");
Atomik::needed("User.class");
Atomik::needed("Data.class");
Atomik::needed("Tool.class");
Atomik::needed("Remark.class");
Atomik::needed("PeerReviewer.class");
Atomik::needed("Baseline.class");
Atomik::set('tab_select','data');

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;  
$limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 8;

$all = Atomik::has('session/see_all_data')?Atomik::get('session/see_all_data'):"yes";

$context_array['aircraft_id']= isset($_GET['show_aircraft']) ? $_GET['show_aircraft'] :(Atomik::has('session/current_aircraft_id')?Atomik::get('session/current_aircraft_id'):"");
$context_array['project_id']= isset($_GET['show_project']) ? $_GET['show_project'] : (Atomik::has('session/current_project_id')?Atomik::get('session/current_project_id'):Atomik::get('session/project_id'));
$context_array['sub_project_id']= isset($_GET['show_lru']) ? $_GET['show_lru'] : Atomik::get('session/sub_project_id');
$context_array['type_id']= isset($_GET['show_type']) ? $_GET['show_type'] : (Atomik::has('session/type_id')?Atomik::get('session/type_id'):"");
$context_array['review_id']="";
$context_array['data_status_id']=(Atomik::has('session/data_status_id')?Atomik::get('session/data_status_id'):"");
$context_array['user_id']= isset($_GET['show_poster']) ? $_GET['show_poster'] : (Atomik::has('session/user_id')?Atomik::get('session/user_id'):"");
$context_array['criticality_id']="";	
$context_array['baseline_id']=isset($_GET['show_baseline']) ? $_GET['show_baseline'] : Atomik::get('session/baseline_id');
$context_array['reference']=Atomik::has('session/reference')?Atomik::get('session/reference'):"";
if (!isset($_GET['show_project'])){
	$context_array['group_id'] = (Atomik::has('session/highlight/group_id')?Atomik::get('session/highlight/group_id'):"");
}
else {
	$context_array['group_id'] = "";
}
$context_array['data_search'] = isset($_GET['search']) ? $_GET['search'] :(Atomik::has('session/search')?Atomik::get('session/search'):"");
$context_array['order']  = isset($_GET['order_data']) ? $_GET['order_data'] : "";

Atomik::set("session/search",$context_array['data_search']);
if (!Data::isInGroup($context_array['type_id'],$context_array['group_id'])){
	if (Atomik::has('session/highlight')) {
		Atomik::delete('session/highlight');
	}	
	Atomik::set('session/highlight/all',"active");
	$context_array['group_id']="";
}
// var_dump($context_array);
$serial_context = urlencode(serialize($context_array));
// var_dump($context_array);
$project = new Project(&$context_array);					
$data = new Data(&$context_array);
$line_counter = 0;
$nb_data = $data->count_data($all);
if ($nb_data > 1){
	Atomik::set('nb_entries',$nb_data." entries found");
}
else if ($nb_data == 1){
	Atomik::set('nb_entries'," 1 entry found");
}
else{
	Atomik::set('nb_entries'," No entry found");
}
$nbpage = Tool::compute_pages($nb_data,&$page,&$debut,$limite);						
Atomik::set('nb_pages',$nbpage);	
Atomik::set('page',$page);
Atomik::set('limite',$limite);
Atomik::set('menu',array('assignee' => 'Author',
						'equipment' => 'Equipment'));
/* menu project */
$html=  '<form method="POST" action="'.Atomik::url('data', false,true,true).'">';
$html.= '<fieldset class="medium">';
$html.= Project::getSelectProject($context_array['project_id'],"active",$context_array['aircraft_id']);
$html.= '</fieldset >';
$html.= '</form>';

/* menu sub project */
$html.= '<form method="POST" action="'.Atomik::url('data', false,true,true).'">';
$html.= '<fieldset class="medium">';
$html.= Project::getSelectSubProject(&$project,$context_array['sub_project_id'],"active");
$html.= '</fieldset >';
$html.= '</form>';

$html.= '<form method="POST" action="'.Atomik::url('data', false,true,true).'" onchange="submit()" >';
$html.='<fieldset class="medium">';
$html.='<label for="show_application">Reference:</label>';
$html.='<select class="combobox"';
$html.= ' name="show_application">';
$html.='<option value=""/> --All--';
foreach(Data::getReferenceList($context_array['project_id'],$context_array['sub_project_id']) as $row):
	$html .= '<option value="'.$row['reference'].'"';
	if (($row['reference'] == $context_array['reference'])&&($context_array['reference']!="")){ 
		$html .= " SELECTED ";
	}
	$html .=">".$row['reference'].": ".$row['type'].": ".$row['description'];
endforeach;
$html .='</select>';
$html.='</fieldset >';
$html.='</form>';

/* menu type */
$html.= '<form method="POST" action="'.Atomik::url('data', false,true,true).'">';
$html.='<fieldset class="medium">';
$html.= $data->getSelectTypeGroup($context_array['type_id'],"active",$context_array['group_id']);
$html.='</fieldset >';
$html.='</form>';

/* menu status */
$html.= '<form method="POST" action="'.Atomik::url('data', false,true,true).'">';
$html.='<fieldset class="medium">';
$html.= $data->getSelectStatus($context_array['data_status_id'],"active");
$html.='</fieldset >';
$html.='</form>';

/* menu author */
$html.='<form method="POST" action="'.Atomik::url('data', false,true,true).'">';
$html.='<fieldset class="medium">';
$html.= User::getSelectAssignee(&$project,$context_array['user_id'],"active");
$html.='</fieldset >';
$html.='</form>';

/* menu baseline */
$html.='<form method="POST" action="'.Atomik::url('data', false,true,true).'">';
$html.='<fieldset class="medium">';
$html.= Project::getSelectBaseline(&$project,$context_array['baseline_id'],"active");
$html.='</fieldset >';
$html.='</form>';
$last_read_data = Data::getLastRead();
$html.='<table cellspacing="0" class="classic"><thead><tr><th colspan="12"><span class="small_font">Last accessed documents</span></th></tr>';
//$html.='<tr><th>Id</th><th>Type</th><th colspan="3">Reference</th><th colspan="3">Description</th></tr>';
$html.='</thead>';
foreach ($last_read_data as $last_docs):
		$row_color = ($line_counter++ % 2 == 0) ? "rouge" : "vert"; 
		$reference = ($last_docs['version'] != "")?$last_docs['reference']." issue ".$last_docs['version']:$last_docs['reference'];
		$description = Tool::cleanDescription($last_docs['description']);
		$description = ($description != "")?$description:$last_docs['type_description'];				
		$html.='<tr class="'.$row_color.'">';
		$html.='<td colspan="2"><span class="small_font"><a href="'.Atomik::url('edit_data',array('id'=>$last_docs['id'])).'">'.$last_docs['id'].'</a></span></td>';
		$html.='<td colspan="2"><span class="small_font">'.$last_docs['type'].'</span></td>';
		$html.='<td colspan="8"><span class="small_font">'.$reference.'</span></td>';
		// $html.='<td colspan="3"><p><span class="small_font">'.$description.'</span></p></td>';
		$html.='</tr>';
		if($line_counter>3)break;
	endforeach;
$html.='</table>';			

Atomik::set('search',$context_array['data_search']);
Atomik::set('select_menu',$html);
Atomik::set('title',"Documents");
Atomik::set('url_reset',"data/reset_data");
Atomik::set('url',"data");
Atomik::set('css_title',"data");
Atomik::set('css_add',"no_show_");
Atomik::set('css_page',"no_show_");
Atomik::set('url_add',Atomik::url('edit_data',array('new'=>'yes')));
Atomik::set('title_add',"Add a document");
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
Atomik::set('url_first',Atomik::url('data',array('page'=>1,'context'=>"")));
Atomik::set('url_previous',Atomik::url('data',array('page'=>$page-1,'context'=>"")));
Atomik::set('url_next',Atomik::url('data',array('page'=>$page+1,'context'=>"")));
Atomik::set('url_last',Atomik::url('data',array('page'=>$nbpage,'context'=>"")));

$params = array('page'=>$page,
				'limite'=>$limite,
				'nb_total'=>$nb_data,
				'context'=>$serial_context,
				'all'=>$all);
$table_data = Atomik::url("create_data_table",$params);
