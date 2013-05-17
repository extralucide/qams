<?php
Atomik::needed('Project.class');
Atomik::needed("User.class");
Atomik::setView("system/display_parents_list");
Atomik::disableLayout();
$item_id = isset($_GET['id']) ? $_GET['id'] : "";
$counter=0;
$list_data = Project::getParentsList($item_id);
if (User::getAdminUserLogged()){
	Atomik::set('css_admin',"no_show_");
}
else{
	Atomik::set('css_admin',"no_show");
}
if ($list_data == NULL){
	Atomik::noRender();
}
