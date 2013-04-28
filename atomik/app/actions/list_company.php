<?php
Atomik::needed('User.class');
Atomik::needed('Company.class');
$fill = false;
$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;  
$limite = isset($_REQUEST['limite']) ? $_REQUEST['limite'] : 8;
$context_array['company_type_id']= Atomik::get('session/company_type_id');
$list_data = Company::getCompany($context_array['company_type_id']);
/*
$html ="<form method='post' action='".Atomik::url('edit_company')."'>";
$html.='<fieldset class="medium">';
$html.="<label for='add_type_name'>Name:</label>";
$html.="<input type=text name='company_name' size='10' /><br/>";
$html.="<span class='art-button-wrapper'>";
$html.="<span class='l'> </span>";
$html.="<span class='r'> </span>";
$html.="<input class='art-button' type='submit' value='Add Company'/>";
$html.="</span>";
$html.='</fieldset >';
$html.="</form>";
*/
/* menu type company */
$html=  '<form method="POST" action="'.Atomik::url('list_company', false).'">';
$html.= '<fieldset class="medium">';
$html.= Company::getSelectCompanyType($context_array['company_type_id'],"active");
$html.= '</fieldset >';
$html.= '</form>';

Atomik::set('title',"Companies");
Atomik::set('css_title',"data");
Atomik::set('css_reset',"no_show");
Atomik::set('url',"edit_company");
Atomik::set('url_add',Atomik::url('edit_company'));
Atomik::set('title_add',"Add a company");
Atomik::set('page',$page);
Atomik::set('limite',$limite);
Atomik::set('css_page_previous','no_show');	
Atomik::set('css_page_next','no_show');	
Atomik::set('css_page',"no_show");
Atomik::set('select_menu',$html);
