<?php
Atomik::needed('Company.class');
Atomik::needed('Aircraft.class');
Atomik::needed('User.class');
Atomik::needed('Tool.class');
Atomik::set('tab_select','admin');
$fill = false;
$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;  
$limite = isset($_REQUEST['limite']) ? $_REQUEST['limite'] : 8;
$context_array['company_id']= Atomik::get('session/company_id');
$list_data = Aircraft::getList($context_array['company_id']);

/* menu company */
$html=  '<form method="POST" action="'.Atomik::url('list_aircraft', false).'">';
$html.= '<fieldset class="medium">';
$html.= Company::getSelectCompany($context_array['company_id'],"active","show_company",1);
$html.= '</fieldset >';
$html.= '</form>';

Atomik::set('title',"Aircraft");
Atomik::set('css_title',"aircraft");
Atomik::set('css_reset',"no_show");
Atomik::set('url',"list_aircraft");
Atomik::set('url_add',Atomik::url('edit_aircraft'));
Atomik::set('title_add',"Add an aircraft");
Atomik::set('page',$page);
Atomik::set('limite',$limite);
Atomik::set('css_page_previous','no_show');	
Atomik::set('css_page_next','no_show');	
Atomik::set('css_page',"no_show");
Atomik::set('select_menu',$html);
