<?php
Atomik::needed("Project.class");
Atomik::needed("Baseline.class");

$baseline_id = isset($_GET['baseline_id']) ? $_GET['baseline_id'] : "";
$context_array['project_id']= isset($_REQUEST['show_project']) ? $_REQUEST['show_project'] : Atomik::get('session/current_project_id');
$context_array['sub_project_id']= isset($_GET['show_lru']) ? $_GET['show_lru'] : Atomik::get('session/sub_project_id');
$project = new Project(&$context_array);
$baseline = new Baseline(&$context_array);
$baseline->get($baseline_id);
Atomik::set('menu',array('equipment' => 'Equipment'));

// Atomik::set('select_menu',$html);
if ($baseline_id != ""){
	Atomik::set('title',"Modify baseline");
	Atomik::set('button',"Modify");
}
else{
	Atomik::set('title',"Add baseline");
	Atomik::set('button',"New");	
}
Atomik::set('css_reset',"no_show");
Atomik::set('css_title',"data");
Atomik::set('css_add',"no_show");
Atomik::set('css_page',"no_show");
