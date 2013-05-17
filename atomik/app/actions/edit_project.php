<?php
Atomik::needed('Aircraft.class');
Atomik::needed('Project.class');
/* Form */
if (isset($_GET['tab'])){
	switch ($_GET['tab']){
		case "system":
			Atomik::set('system_highlight','active');
			break;	
		case "equipement":
			Atomik::set('equipement"','active');
			break;		
	}
}
$list_aircraft = Aircraft::getAircrafts(Atomik::get('session/company_id'));
$context_array['company_id']= Atomik::get('session/company_id');
$context_array['aircraft_id']= (Atomik::has('session/current_aircraft_id')?Atomik::get('session/current_aircraft_id'):"");
$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;  
$limite = isset($_REQUEST['limite']) ? $_REQUEST['limite'] : 8;
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : "";
$project = new Project(&$context_array);
$list_data = $project->getSubProjectList();
if ($id != "") {
   Atomik::set('title',"Update system");
   $button = "Modify";
   $project->get($id);
   Atomik::set('title_add',"Add an item");
   Atomik::set('url_add',Atomik::url('edit_eqpt&project_id='.$id,false));
}
else {
	Atomik::set('title',"New system");
	$button = "Add";
	Atomik::set('css_eqpt',"no_show");
	Atomik::set('css_add',"no_show");
}
$html ='<a href="'.Atomik::url("list_project",false).'" ><img src="'.Atomik::asset('assets/images/pages/sommaire.png').'" border="0" alt="Back" title="Back"><h2>Back</h2></a>';
// Atomik::set('url_add',Atomik::url('add_aircraft_picture',array('id' => $id)));

Atomik::set('css_reset',"no_show");
Atomik::set('css_title',"user");
Atomik::set('css_page',"no_show");
Atomik::set('select_menu',$html);

Atomik::disableLayout();
Atomik::noRender();
$vars = array('list_aircraft'=>$list_aircraft,
				'list_data'=>$list_data,
				'project'=>$project,
				'button'=>$button,
				'page'=>$limite,
				'limite'=>$limite,
				'id'=>$id);
$view_output = Atomik::render("edit_project",$vars);
$content = Atomik::renderLayout("_layout_simple",$view_output);
echo $content;