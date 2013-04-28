<?php
Atomik::needed("Project.class");
Atomik::needed("Data.class");
Atomik::needed("Tool.class");

$context_array['project_id']= isset($_GET['show_project']) ? $_GET['show_project'] : (Atomik::has('session/current_project_id')?Atomik::get('session/current_project_id'):Atomik::get('session/project_id'));
// $context_array['project_id']= Atomik::has('session/current_project_id') ? Atomik::get('session/current_project_id') : Atomik::get('session/project_id');
$context_array['sub_project_id']= isset($_GET['show_lru']) ? $_GET['show_lru'] : Atomik::get('session/sub_project_id');
$context_array['type_id']= isset($_GET['show_type']) ? $_GET['show_type'] : Atomik::get('session/type_id');
$project = new Project(&$context_array);					
$data = new Data(&$context_array);
/* menu project */
$html=  '<form method="POST" action="'.Atomik::url('export/read_docx', false).'">';
$html.= '<fieldset class="medium">';
$html.= Project::getSelectProject($context_array['project_id'],"active");
$html.= '</fieldset >';
$html.= '</form>';

/* menu sub project */
Atomik::set('menu',array('equipment' => 'Equipment'));
$html.= '<form method="POST" action="'.Atomik::url('export/read_docx', false).'">';
$html.= '<fieldset class="medium">';
$html.= Project::getSelectSubProject(&$project,$context_array['sub_project_id'],"active");
$html.= '</fieldset >';
$html.= '</form>';
// var_dump($context_array);
/* menu type */
$html.= '<form method="POST" action="'.Atomik::url('export/read_docx', false).'">';
$html.='<fieldset class="medium">';
$html.= $data->getSelectTypeGroup($context_array['type_id'],"active");
$html.='</fieldset >';
$html.='</form>';

Atomik::set('select_menu',$html);
Atomik::set('css_reset',"no_show");
Atomik::set('title','Validation Matrix');
Atomik::set('css_title',"action");
Atomik::set('css_add',"no_show");
Atomik::set('css_page',"no_show");