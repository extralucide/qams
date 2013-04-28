<?php
//Atomik::noRender(); 
Atomik::disableLayout();
Atomik::setView("export/export_xlsx_peer_review_report");
Atomik::needed("Remark.class");
Atomik::needed("User.class");

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : "";
$env_context['project_id']= isset($_GET['show_project']) ? $_GET['show_project'] : (Atomik::has('session/current_project_id')?Atomik::get('session/current_project_id'):Atomik::get('session/project_id'));
$env_context['sub_project_id'] = Atomik::has('session/sub_project_id')?Atomik::get('session/sub_project_id'):"";
$env_context['review_id'] = Atomik::has('session/review_id')?Atomik::get('session/review_id'):"";
$env_context['status_id'] = Atomik::has('session/remark_status_id')?Atomik::get('session/remark_status_id'):"";
$env_context['user_id'] = Atomik::has('session/user_id')?Atomik::get('session/user_id'):"";
$env_context['assignee_id'] = Atomik::has('session/user_id')?Atomik::get('session/user_id'):"";
$env_context['criticality_id']=Atomik::has('session/severity_id')?Atomik::get('session/severity_id'):"";
$env_context['search']=Atomik::has('session/search')?Atomik::get('session/search'):"";
$env_context['order']=Atomik::has('session/order')?Atomik::get('session/order'):"";
$env_context['user_logged_id']= User::getIdUserLogged();

$remark = new Remark(&$env_context);
if ($id!=""){
	$remark->setDocument($id);
}
$filename = $remark->exportInspectionXlsx();
