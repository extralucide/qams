<?php
Atomik::needed('User.class');
$edit_user_id = isset($_REQUEST['edit_user_id']) ? $_REQUEST['edit_user_id'] : "";
if (User::getAdminUserLogged() === false){
	//Atomik::Flash('You are not allowed to perform this operation','failed');
	Atomik::setView("user/view_user");
	Atomik::set('title',"View user");
	// Atomik::redirect('users');
}
else{
	if ($edit_user_id != "") {
	   Atomik::set('title',"Update user");
	   $button = "Modify";
	}
	else {
		Atomik::set('title',"New user");
		Atomik::set('css_modify',"no_show");
		$button = "Add";
		$admin_check = '';
		$admin_no_check = 'CHECKED';	
	}
}
Atomik::needed('Project.class');
Atomik::needed('Company.class');
if (isset($_GET['tab'])){
	switch ($_GET['tab']){
		case "description":
			Atomik::set('description_highlight','active');
			break;
		case "projects":
			Atomik::set('projects_highlight','active');
			break;			
	}
}
else {
	Atomik::set('description_highlight','active');
}
/* Form */
$read_user_only=false;
$title = "Edit User";

$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;  
$limite = isset($_REQUEST['limite']) ? $_REQUEST['limite'] : 8;


$user_to_edit = new User;
$user_to_edit->get_user_info($edit_user_id); 
/* admin right */
if (User::getAdminUserLogged()){
	Atomik::set('css_admin',"no_show_");
}
else{
	Atomik::set('css_admin',"no_show");
}
if ($user_to_edit->getAdmin()){
	$admin_check = 'CHECKED';
	$admin_no_check = '';
}
else{
	$admin_check = '';
	$admin_no_check = 'CHECKED';
}
if ($user_to_edit->getActive()){
	$active_check = 'CHECKED';
	$active_no_check = '';
}
else{
	$active_check = '';
	$active_no_check = 'CHECKED';
}
$update = "";

$companies_list = Company::getCompany();
$project_list = Project::getProject();
if ($edit_user_id == 1){
	$html = '<div style="width:450px"><a href="'.Atomik::url('user/cv',array('id'=>$user_to_edit->id)).'" style="text-decoration: none;outline-width: medium;outline-style: none;width:50%;float:left" title="Add new entry">';
	$html .= '<img src="'.Atomik::asset('assets/images/32x32/kghostview.png').'" class="systemicon" width="32" alt="Add new action" title="See CV" border="no" />See CV</a></div>';
}
else{
	$html = "";
}
$html .='<p><a href="'.Atomik::url("users",false).'" ><img src="'.Atomik::asset('assets/images/pages/sommaire.png').'" border="0" alt="Back" title="Back"><h2>Back</h2></a></p>';

Atomik::set('url_add',Atomik::url('user/add_user_picture',array('user_id' => $edit_user_id)));
Atomik::set('title_add',"Update picture");
Atomik::set('css_reset',"no_show");
Atomik::set('css_title',"user");
Atomik::set('css_add',"no_show");
Atomik::set('css_page',"no_show");
Atomik::set('select_menu',$html);
