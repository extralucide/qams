<?php
if(isset($_REQUEST['bug_cookie'])) {
    $bug_cookie = $_REQUEST['bug_cookie'];
    /* If user is logged in, get all user information */
    if(isset($bug_cookie)) {
        $array=unserialize(stripslashes($bug_cookie));
        $Id_User = $array[6];
    }
}
$where_lru = "";
$select_equipment = "";
$select_status = "";
$select_severity = "";
$system_impact = "";
$system_pr = "";
$origin = "";

if ((isset($_REQUEST['select_project'])) && ($_REQUEST['select_project'] != "")) {
    $select_project= $_REQUEST['select_project'];
    $where_lru.= " project = ".$select_project;
}
else
    $select_project= "";

if(isset($_REQUEST['id'])) {
    $update_id=$_REQUEST['id'];
    unset ($_REQUEST['id']);
    $update="yes";
    $title ="Update Problem Report";
    $button="Modify Problem Report";
    $where="id = ".$update_id;
    //echo $where;
    $updated_pr = Atomik_Db::findAll('sprs',$where);
    //echo $updated_pr;
    if ($updated_pr) {
        //echo "on va là";
        $project_id = $updated_pr[0]['project_id'];
        $lru_id = $updated_pr[0]['lru_id'];
        if ($select_project =="") {
            $select_project = $project_id;
            $select_equipment = $lru_id;
            $where_lru.= " project = ".$select_project;
        }
        else {

        }
        $cr_id = $updated_pr[0]['cr_id'];
        $synopsis = $updated_pr[0]['synopsis'];
        $description = $updated_pr[0]["description"];
        $impact_analysis = $updated_pr[0]['impact_analysis'];
        $conclusion = $updated_pr[0]['conclusion'];
        $severity_id = $updated_pr[0]['severity_id'];
        $select_severity = $severity_id;
        $status_id = $updated_pr[0]['status_id'];
        $select_status = $status_id;
        $system_impact = $updated_pr[0]['system_impact'];
        $system_pr = $updated_pr[0]['system_pr_id'];
        $origin = $updated_pr[0]['origin'];
        if ($system_pr == 0) {$system_pr == "none";}
        //if ($origin =="") {$origin == "none";}
    }
    else {
        $project_id = 1;
        $lru_id = 1;
        $cr_id = 0;
        $synopsis = "synopsis";
        $description = "description";
        $impact_analysis = "impact_analysis";
        $conclusion = "conclusion";
        $severity_id = "severity";
        $status_id = "status";
    }

}
else {
    $project_id = 1;
    $lru_id = 1;
    $cr_id = 0;
    $synopsis = "synopsis";
    $description = "description";
    $impact_analysis = "impact_analysis";
    $conclusion = "conclusion";
    $severity_id = "severity";
    $status_id = "status";
    $title ="Problem Report";
    $button="Add Problem Report";
    $update="no";
    $update_id="0";
}
$projects = Atomik_Db::findAll('projects','','project');
$equipment = Atomik_Db::findAll('lrus',$where_lru,'lru');
$severity = Atomik_Db::findAll('bug_criticality');
$status = Atomik_Db::findAll('spr_status');
$location = "{$_SERVER['PHP_SELF']}?action=add_spr";