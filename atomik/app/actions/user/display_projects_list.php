<?php
Atomik::needed('User.class');
Atomik::setView("user/display_projects_list");
Atomik::disableLayout();

$user_id = isset($_GET['id']) ? $_GET['id'] : ""; 
$counter=0;
$user_to_edit = new User;
$user_to_edit->get_user_info($user_id); 
if (User::getAdminUserLogged()){
	Atomik::set('css_admin',"no_show_");
}
else{
	Atomik::set('css_admin',"no_show");
}

