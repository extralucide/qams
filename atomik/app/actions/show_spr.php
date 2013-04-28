<?php
$where = "";
$where_lru = "";
if ((isset($_REQUEST['select_project'])) && ($_REQUEST['select_project'] != "")) {
	$select_project= $_REQUEST['select_project'];
	$where.= " and project_id = ".$select_project;
    $where_lru.= " project = ".$select_project;
}
else
	$select_project= "";
	
if ((isset($_REQUEST['select_equipment'])) && ($_REQUEST['select_equipment'] != "")) {
	$select_equipment= $_REQUEST['select_equipment'];
    $where.= " and lru_id = ".$select_equipment;
    }
else
	$select_equipment= "";
if ((isset($_REQUEST['select_severity'])) && ($_REQUEST['select_severity'] != "")) {
	$select_severity = $_REQUEST['select_severity'];
    $where.= " and severity_id = ".$select_severity;
    }
else
	$select_severity= "";
if ((isset($_REQUEST['select_status'])) && ($_REQUEST['select_status'] != "")) {
	$select_status = $_REQUEST['select_status'];
    $where.= " and status_id = ".$select_status;
    }
else
	$select_status= "";
//echo $where;
//$sprs = Atomik_Db::findAll('sprs',$where);
$sprs = A('db:select bug_criticality.name as severity, '.
          ' sprs.id, '.
		  ' eprs.epr_id, '.
          ' spr_status.status, '.
          ' projects.project, '.
          ' lrus.lru, '.
          ' sprs.cr_id, '.
          ' sprs.synopsis, '.
          ' sprs.description, '.
          ' sprs.impact_analysis, '.
          ' sprs.severity_id, '.
          ' sprs.status_id '.
          ' from projects, lrus, bug_criticality, spr_status, epr_join_spr left outer join sprs on  '.
		  ' epr_join_spr.spr_id = sprs.id left outer join eprs on eprs.id = epr_join_spr.epr_id '.
		  ' where sprs.project_id = projects.id and '.
          ' sprs.lru_id = lrus.id and '.
          ' sprs.severity_id = bug_criticality.level and '.
          ' sprs.status_id = spr_status.id '.$where.
          ' order by project_id asc, lru_id asc, cr_id asc');
/* result of the sql query to get field of the header of the table */
$sql_query = A('db:show columns from sprs ');
/* get the array related to the sql query */
$column=$sql_query->fetchall();

$projects = Atomik_Db::findAll('projects','','project');
//$where = "project = ".$select_project;
//echo $where_lru;
$equipment = Atomik_Db::findAll('lrus',$where_lru,'lru');
$severity = Atomik_Db::findAll('bug_criticality');
$status = Atomik_Db::findAll('spr_status');
$location = "{$_SERVER['PHP_SELF']}?action=show_spr";
$header_fields = array("Project", "LRU","System PR","Software PR","Synopsis","description", "Impact analysis","Severity","Status","");
