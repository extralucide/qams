<?php
include "../includes/config.php";
include "../inc/Data.class.php";
include "../inc/Project.class.php";
include "../inc/Db.class.php";

$db=new Db("atomik");
echo Atomik::get('project_id'); 
echo Atomik::get('lru_id'); 
if (isset($_REQUEST['project_id'])){
	$show_project= $_REQUEST['project_id'];
}
else {
  $show_project="";
}
//echo "TEST".$_REQUEST['lru_id'];
if (isset($_REQUEST['lru_id'])){
	$show_lru= $_REQUEST['lru_id'];
}
else {
  $show_lru="";
}

if (isset($_REQUEST['type_id'])){
	$show_type= $_REQUEST['type_id'];
}
else {
  $show_type="";
}
if ((isset($_REQUEST['spec_id'])) && ($_REQUEST['spec_id'] != "")){
	$spec_id= $_REQUEST['spec_id'];
	$table_req = "table_req_".$spec_id;
	$table_traca_req = "table_traca_req_".$spec_id;
	$data_selected = A("db:SELECT bug_applications.id,bug_applications.application as reference,version,bug_applications.description,data_cycle_type.name as type,data_cycle_type.description as type_description FROM bug_applications,data_cycle_type LEFT OUTER JOIN group_type ON group_id = group_type.id WHERE group_type.name = 'Specification' AND bug_applications.type = data_cycle_type.id AND bug_applications.id = {$spec_id} ORDER BY type ASC");

}
else {
  	$spec_id= "";
	$table_req = "table_req";
	$table_traca_req = "table_traca_req";
}
$type = A("db:SELECT data_cycle_type.id,data_cycle_type.name,description FROM data_cycle_type LEFT OUTER JOIN group_type ON group_id = group_type.id WHERE group_type.name = 'Specification' ORDER BY `data_cycle_type`.`name` ASC ");

$projects = A("db:SELECT id,project FROM projects ORDER BY `projects`.`project` ASC");
if ($show_project != "") {
  $where=" WHERE project = {$show_project} ";
  $which_data_project=" AND project = {$show_project} ";
}
else {
  $where="";
  $which_data_project="";
}
if ($show_type != "") {
  $which_type=" AND type = {$show_type} ";
}
else {
  $which_type="";
}
  
$components = A("db:SELECT id,lru FROM lrus {$where} ORDER BY lru ASC");
$data = A("db:SELECT bug_applications.id,".
			"bug_applications.application as reference,".
			"version,".
			"bug_applications.description,".
			"data_cycle_type.name as type,".
			"data_cycle_type.description as type_description ". 
			"FROM bug_applications,data_cycle_type ".
			"LEFT OUTER JOIN group_type ON group_id = group_type.id ".
			"WHERE group_type.name = 'Specification' ".
			"AND bug_applications.type = data_cycle_type.id {$which_data_project} {$which_type} ORDER BY type ASC");
/*
 * List of tables
 */
$list_table = A("db:SHOW TABLES FROM {$db_select} LIKE '%table_req%'");
foreach ($list_table as $table) {
  //echo $table[0];
  if ($table_req == $table[0]) {
  	//echo $table[0];
    //echo "<br>find<br>";
    $test_data_spec = "SELECT {$table_req}.id,text,req_derived.attribute as derived,req_safety.attribute as safety,".
    "req_allocation.attribute as allocation,rationale,req_status.attribute as status, req_validation.attribute as validation FROM {$table_req} ".
    "LEFT OUTER JOIN req_derived ON {$table_req}.derived = req_derived.id ".
    "LEFT OUTER JOIN req_safety ON {$table_req}.safety = req_safety.id ".
    "LEFT OUTER JOIN req_allocation ON {$table_req}.allocation = req_allocation.id ".
    "LEFT OUTER JOIN req_status ON {$table_req}.status = req_status.id ".
    "LEFT OUTER JOIN req_validation ON {$table_req}.validation = req_validation.id ";
    $data_spec = A("db:".$test_data_spec);
    break;
  }
  else { 
    $data_spec = "";
  }
}  
/*
 * List of traceability tables 
 */ 
//$list_table_traca = A("db:SHOW TABLES FROM olivier_appere LIKE '%table_traca_req%'");
//foreach ($list_table_traca as $table_traca) {
  //echo $table[0];
  //if ($table_traca_req == $table_traca[0]) {
  	//echo $table[0];
    //echo "<br>find<br>";
    //$test_data_traca_spec = "SELECT req_id,upper_table_id,upper_req_id FROM {$table_traca_req}";
    //echo $test_data_traca_spec;
    //$data_traca_spec = A("db:".$test_data_traca_spec);
      //$traca_upper_array=array();
      //foreach($data_traca_spec as $row_traca) {
      //	$id = 
      //  $traca_upper_array[$key] =  $value ;
     // }
    //break;
  //}
  //else { 
  //  $data_traca_spec = "";
 // }
//} 
$header_fields = array("Id", "Text","Upper Reqs","Derived","Safety","Rationale","Allocation","Status","Validation","");
