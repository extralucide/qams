<?php
Atomik::needed('Aircraft.class');
Atomik::needed('Project.class');
Atomik::needed('Company.class');
Atomik::set('tab_select','admin');
$fill = false;
$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;  
$limite = isset($_REQUEST['limite']) ? $_REQUEST['limite'] : 8;
$context_array['company_id']= Atomik::has('session/company_id')?Atomik::get('session/company_id'):"";
$context_array['aircraft_id']= Atomik::get('session/current_aircraft_id');
if ($context_array['company_id'] == ""){
	$aircraft = Aircraft::getAircraft($context_array['aircraft_id']);
	$context_array['company_id'] = $aircraft['company_id'];
}
//$list_data = Aircraft::getList($context_array['company_id']);
$list_data = Project::getProject($context_array['aircraft_id'],
                                $context_array['company_id']);
$project = new Project;
/* menu company */
$html=  '<form method="POST" action="'.Atomik::url('list_project', false).'">';
$html.= '<fieldset class="medium">';
$html.= Company::getSelectCompany($context_array['company_id'],"active");
$html.= '</fieldset >';
$html.= '</form>';

/* menu aircraft */
$html.=  '<form method="POST" action="'.Atomik::url('list_project', false).'">';
$html.= '<fieldset class="medium">';
$html.= Project::getSelectAircraft($context_array['company_id'],
                                    $context_array['aircraft_id'],
                                    "active");
$html.= '</fieldset >';
$html.= '</form>';
$html .='<a href="'.Atomik::url("admin",false).'" ><img src="'.Atomik::asset('assets/images/pages/sommaire.png').'" border="0" alt="Back" title="Back"><h2>Back</h2></a>';

Atomik::set('title',"Project");
Atomik::set('css_title',"action");
Atomik::set('css_reset',"no_show");
Atomik::set('url',"list_project");
Atomik::set('url_add',Atomik::url('edit_project'));
Atomik::set('title_add',"Add a project");
Atomik::set('page',$page);
Atomik::set('limite',$limite);
Atomik::set('css_page_previous','no_show');	
Atomik::set('css_page_next','no_show');	
Atomik::set('css_page',"no_show");
Atomik::set('select_menu',$html);
