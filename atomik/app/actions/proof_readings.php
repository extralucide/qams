<?php
$where = "";
$where_appli = "";
if (isset($_REQUEST['select_project'])) {
    $select_project= $_REQUEST['select_project'];
    $where = "project = ".$select_project;
    if (isset($_REQUEST['select_equipment'])) {
        $select_equipment= $_REQUEST['select_equipment'];
        //$where = "lru = ".$select_equipment;
        $where_appli = 'and project = '.$select_project.' and lru = '.$select_equipment;
    }
    else {
        $select_equipment= 0;
        $where_appli = 'and project = '.$select_project;
    }
}
else {
    $select_project= 0;
    $select_equipment= 0;
}

if (isset($_REQUEST['select_application'])) {
    $select_application= $_REQUEST['select_application'];
}
else {
    $select_application= 0;
}

if (isset($_REQUEST['posted_by'])) {
    $select_poster= $_REQUEST['posted_by'];
}
else {
    $select_poster= 0;
}

$projects = Atomik_Db::findAll('projects','','project');
$equipment = Atomik_Db::findAll('lrus',$where,'lru');
$application = A('db:select bug_applications.id as id, application, version, data_cycle_type.name from bug_applications,data_cycle_type where bug_applications.type = data_cycle_type.id '.$where_appli.'  order by application ASC');
$poster = Atomik_Db::findAll('bug_users',null,'username ASC');
$location = "{$_SERVER['PHP_SELF']}?action=proof_readings";
