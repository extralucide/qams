<?php
Atomik::needed('Company.class');
Atomik::needed('Project.class');
Atomik::needed('User.class');
Atomik::needed('Tool.class');
Atomik::set('tab_select','misc');
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;  
$limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 8;
// $filter_letter = isset($_REQUEST['show_letter'])? $_REQUEST['show_letter'] : ""; /* both POST and GET */
if (isset($_GET['show_letter'])){
	Atomik::set('session/first_letter',$_GET['show_letter']);
}

// if (!isset($_GET['page'])){	
	// Tool::deleteKey('session/company_id');
	// Tool::deleteKey('session/project_id');
	// Tool::deleteKey('session/first_letter');
// }
$context_array['company_id']= isset($_GET['show_company']) ? $_GET['show_company'] : Atomik::get('session/company_id');
$context_array['project_id']= Atomik::has('session/project_id')?Atomik::get('session/project_id'):Atomik::get('session/current_project_id');
$context_array['first_letter']= Atomik::get('session/first_letter');
$context_array['user_search'] = isset($_REQUEST['search']) ? $_REQUEST['search'] : Atomik::get('session/search');
Atomik::set("session/search",$context_array['user_search']);
// var_dump($_REQUEST['search']);
// var_dump(Atomik::get('session/search'));
$filter_letter = $context_array['first_letter'];

$show_company = "";
$show_project = "";
$show_poster = "";
$line = 0;
$header_fields = array("Id","Name", "Company",  "Function","Project","Email","Telephone","" );
// $env_context[] = array();
$context = array();
// $env_context = $context_array;
// $env_context['filter_letter']=$filter_letter;
// var_dump($context_array);
$user = new User(&$context_array);
// var_dump($context_array);
$list_users = $user->getUsersList();
$nbtotal = count($list_users);
$nbpage = Tool::compute_pages($nbtotal,&$page,&$debut,$limite);						
Atomik::set('nb_pages',$nbpage);
$user->prepare($user->get_list_poster());
$list_users_lite = $user->execute($debut,$limite);
$html = "";

/* menu company */
$html=  '<form method="POST" action="'.Atomik::url('users', false).'">';
$html.= '<fieldset class="medium">';
$html.= Company::getSelectCompany($context_array['company_id'],"active");
$html.= '</fieldset >';
$html.= '</form>';

/* menu project */
$html.=  '<form method="POST" action="'.Atomik::url('users', false).'">';
$html.= '<fieldset class="medium">';
$html.= Project::getSelectProject($context_array['project_id'],"active");
$html.= '</fieldset >';
$html.= '</form>';
if (User::getAdminUserLogged()){
	Atomik::set('css_admin',"no_show_");
}
else{
	Atomik::set('css_admin',"no_show");
}
Atomik::set('search',$context_array['user_search']);
Atomik::set('nb_entries',$nbtotal);
Atomik::set('title',"Users");
Atomik::set('css_title',"user");
Atomik::set('css_reset',"");
Atomik::set('url_reset',"user/reset_user");
Atomik::set('url','users');
Atomik::set('url_add',Atomik::url('edit_user'));
Atomik::set('title_add',"Add a user");
Atomik::set('css_add',"");
Atomik::set('select_menu',$html);
Atomik::set('css_page',"");
Atomik::set('css_page_previous','');
Atomik::set('css_page_next','');
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
Atomik::set('url_first',Atomik::url('users',array('page'=>1,'context'=>$context)));
Atomik::set('url_previous',Atomik::url('users',array('page'=>$page-1,'context'=>$context)));
Atomik::set('url_next',Atomik::url('users',array('page'=>$page+1,'context'=>$context)));
Atomik::set('url_last',Atomik::url('users',array('page'=>$nbpage,'context'=>$context)));
Atomik::set('page',$page);
Atomik::set('limite',$limite);