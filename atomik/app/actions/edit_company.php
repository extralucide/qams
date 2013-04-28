<?php
Atomik::needed('Company.class');
Atomik::needed('User.class');
/* Form */
$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;  
$limite = isset($_REQUEST['limite']) ? $_REQUEST['limite'] : 8;
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : "";
$company_id = Atomik::get('session/company_id');
$company = new Company();
if ($id != "") {
   Atomik::set('title',"Update company");
   $button = "Modify";
   $company->get($id);
}
else {
	Atomik::set('title',"New company");
	$button = "Add";
}
/* menu company */
//$html=  '<form method="POST" action="'.Atomik::url('edit_company', false).'">';
//$html.= '<fieldset class="medium">';
//$html.= User::getSelectCompany($company_id,"active");
//$html.= '</fieldset >';
//$html.= '</form>';

Atomik::set('url_add',Atomik::url('add_company_picture',array('id' => $id)));
Atomik::set('title_add',"Update picture");
Atomik::set('css_reset',"no_show");
Atomik::set('css_title',"user");
Atomik::set('css_add',"no_show");
Atomik::set('css_page',"no_show");
//Atomik::set('select_menu',$html);
