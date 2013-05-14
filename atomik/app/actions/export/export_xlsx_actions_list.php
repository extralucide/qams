<?php
Atomik::disableLayout();
Atomik::setView("export/export_xlsx_actions_list");
Atomik::needed("Db.class");
Atomik::needed("User.class");
Atomik::needed("Date.class");
Atomik::needed("Data.class");
Atomik::needed("Project.class");
Atomik::needed("Tool.class");
Atomik::needed("Action.class");
Atomik::needed("Review.class");
Atomik::needed("Mail.class");
Atomik::needed("Logbook.class");
Atomik::needed("Remark.class");
Atomik::needed("Baseline.class");
Atomik::needed("PeerReviewer.class");

$env_context['aircraft_id']= isset($_GET['show_aircraft']) ? $_GET['show_aircraft'] :(Atomik::has('session/current_aircraft_id')?Atomik::get('session/current_aircraft_id'):"");
$env_context['project_id']= isset($_GET['show_project']) ? $_GET['show_project'] : (Atomik::has('session/current_project_id')?Atomik::get('session/current_project_id'):Atomik::get('session/project_id'));
$env_context['sub_project_id'] = Atomik::has('session/sub_project_id')?Atomik::get('session/sub_project_id'):"";
$env_context['review_id'] = Atomik::has('session/review_id')?Atomik::get('session/review_id'):"";
$env_context['action_status_id'] = Atomik::has('session/action_status_id')?Atomik::get('session/action_status_id'):"";
$env_context['user_id'] = Atomik::has('session/user_id')?Atomik::get('session/user_id'):"";
$env_context['assignee_id'] = Atomik::has('session/user_id')?Atomik::get('session/user_id'):"";
$env_context['criticality_id']=Atomik::has('session/severity_id')?Atomik::get('session/severity_id'):"";
$env_context['search']=Atomik::has('session/search')?Atomik::get('session/search'):"";
$env_context['order']=Atomik::has('session/order')?Atomik::get('session/order'):"";
$env_context['user_logged_id']= User::getIdUserLogged();	

$action = new Action(&$env_context);
/* create excel file */
$excel_filename = $action->exportXlsx();
