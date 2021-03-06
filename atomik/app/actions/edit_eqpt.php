<?php
Atomik::needed('Aircraft.class');
Atomik::needed('Project.class');
Atomik::needed('User.class');
/* Form */

$context_array['company_id']= Atomik::get('session/company_id');
$context_array['aircraft_id']= Atomik::has('session/current_aircraft_id')?Atomik::get('session/current_aircraft_id'):"";
$context_array['project_id']= isset($_GET['project_id']) ? $_GET['project_id'] : (Atomik::has('session/current_project_id')?Atomik::get('session/current_project_id'):Atomik::get('session/project_id'));
							
$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;  
$limite = isset($_REQUEST['limite']) ? $_REQUEST['limite'] : 8;
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : "";
if (isset($_GET['tab'])){
	switch ($_GET['tab']){
		case "description":
			Atomik::set('description_highlight','active');
			break;	
		case "parents":
			Atomik::set('parents_highlight','active');
			break;		
	}
}

$project = new Project($context_array);
$list_aircraft = Aircraft::getAircrafts(Atomik::get('session/company_id'));
$list_projects = Project::getProject(Atomik::get('session/current_aircraft_id'),
									Atomik::get('session/company_id'));							
Atomik::set('menu',array('equipment' => 'Parent',
							'assignee' => 'Manager'));									
if ($id != "") {
   Atomik::set('title',"Update item");
   $button = "Modify";
   $submit = "modify";
   $project->getSubProject($id);
}
else {
	Atomik::set('title',"New item");
	$submit = "submit";
	$button = "Add";
}
$list_items = Project::getSelectSubProject(&$project,$project->parent);
$all_list_items = $project->getSubProjectList();
$html ='<a href="'.Atomik::url("admin",false).'" ><img src="'.Atomik::asset('assets/images/pages/sommaire.png').'" border="0" alt="Back" title="Back"><h2>Back</h2></a>';

Atomik::set('url_add',Atomik::url('add_aircraft_picture',array('id' => $id)));
Atomik::set('title_add',"Update picture");
Atomik::set('css_reset',"no_show");
Atomik::set('css_title',"hardware");
Atomik::set('css_add',"no_show");
Atomik::set('css_page',"no_show");
Atomik::set('select_menu',$html);

Atomik::disableLayout();
Atomik::noRender();
$vars = array('list_aircraft'=>$list_aircraft,
				'list_projects'=>$list_projects,
				'list_items'=>$list_items,
				'all_list_items'=>$all_list_items,
				'project'=>$project,
				'button'=>$button,
				'submit'=>$submit,				
				'page'=>$limite,
				'limite'=>$limite,
				'id'=>$id);
$view_output = Atomik::render("edit_eqpt",$vars);
$content = Atomik::renderLayout("_layout_simple",$view_output);
echo $content;
