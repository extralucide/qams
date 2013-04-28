<?php
Atomik::needed("Tool.class");
Atomik::needed("Project.class");
Atomik::needed("Baseline.class");

$description ="";
$header_fields = array("Id"=>1,"Project"=>4,"Description"=>6, "Date"=>4);
$fill = 0;
$page = isset($_GET['page']) ? $_GET['page'] : 1;  
$limite = isset($_GET['limite']) ? $_GET['limite'] : 8;
if (isset($_GET['reset'])){	
	Tool::deleteKey('session/sub_project_id');
	Tool::deleteKey('session/review_id');
	Tool::deleteKey('session/baseline_id');			
}
// echo "TEST".Atomik::get('session/user_id');
$context_array['project_id']= isset($_POST['show_project']) ? $_POST['show_project'] : Atomik::get('session/current_project_id');
$context_array['sub_project_id']= isset($_POST['show_lru']) ? $_POST['show_lru'] : Atomik::get('session/sub_project_id');
$context_array['review_id']="";	
$context_array['baseline_id']=isset($_POST['show_baseline']) ? $_POST['show_baseline'] : Atomik::get('session/baseline_id');	

$project = new Project(&$context_array);
$baseline = new Baseline(&$context_array);
$list_baseline = $baseline->getBaselineList();

// echo $table_query;			   
// $table = A('db:'.$table_query);
/* menu project */
$html=  '<form method="POST" action="'.Atomik::url('baseline', false).'">';
$html.= '<fieldset class="medium">';
$html.= Project::getSelectProject($context_array['project_id'],"active");
$html.= '<input type="hidden" name="show_lru" value="'.$context_array['sub_project_id'].'"/>';
$html.= '</fieldset >';
$html.= '</form>';
Atomik::set('menu',array('assignee' => 'Author',
						'equipment' => 'Equipment'));
/* menu sub project */
$html.= '<form method="POST" action="'.Atomik::url('baseline', false).'">';
$html.= '<fieldset class="medium">';
$html.= Project::getSelectSubProject(&$project,$context_array['sub_project_id'],"active");
$html.= '<input type="hidden" name="show_project" value="'.$context_array['project_id'].'"/>';
$html.= '</fieldset >';
$html.= '</form>';
Atomik::set('select_menu',$html);
Atomik::set('title',"Baseline");
Atomik::set('css_title',"data");
Atomik::set('url',"baseline");
Atomik::set('css_add',"no_show_");
Atomik::set('title_add',"Add new baseline");
Atomik::set('url_add',Atomik::url('add_baseline',false));
