<?php
Atomik::disableLayout();
Atomik::needed('Db.class');
Atomik::needed('Remark.class');
Atomik::needed('Date.class');
Atomik::needed('Data.class');

$pr_id = isset($_GET['id']) ? $_GET['id'] : "";
$pr_treatment = isset($_GET['pr']) ? $_GET['pr'] :"yes";
$remove_pr = isset($_GET['remove_pr']) ? $_GET['remove_pr'] : "";
$previous_data_id = "";
// $db = new Db;
if ($remove_pr == "yes"){
	$remove_link_id= isset($_GET['remove_link_id']) ? $_GET['remove_link_id'] : "";
	$sql_query = "DELETE FROM pr_link WHERE id = ".$remove_link_id;	
	//echo $sql_query;
	$result = A('db:'.$sql_query); 
}
$pr = new Data;
$pr->get($pr_id);
/* The data is a PR or not ?*/
if ($pr_treatment == "no"){
	/* a specification for instance */
	$list_pr_sql_query = "SELECT pr_link.pr_id as data_id, pr_link.id, projects.id as project_id, data_cycle_type.name as type,bug_applications.application as reference,bug_applications.version,bug_applications.description,projects.project,lrus.lru FROM pr_link ".
						 "LEFT OUTER JOIN bug_applications ON bug_applications.id = pr_id ".
						 "LEFT OUTER JOIN projects ON bug_applications.project = projects.id ".		
						 "LEFT OUTER JOIN lrus ON bug_applications.lru = lrus.id ".	
						 "LEFT OUTER JOIN data_cycle_type ON bug_applications.type = data_cycle_type.id ".				 
						 "WHERE data_id = {$pr_id}";
	echo "<li class='warning' style='list-style-type: none'>To add a link to a PR go to PR description.</li>";				 
}
else {
	/* Thisi is a PR */
	$list_pr_sql_query = "SELECT pr_link.data_id,pr_link.id, projects.id as project_id, data_cycle_type.name as type,bug_applications.application as reference,bug_applications.version,bug_applications.description,projects.project,lrus.lru FROM pr_link ".
						 "LEFT OUTER JOIN bug_applications ON bug_applications.id = data_id ".
						 "LEFT OUTER JOIN projects ON bug_applications.project = projects.id ".		
						 "LEFT OUTER JOIN lrus ON bug_applications.lru = lrus.id ".	
						 "LEFT OUTER JOIN data_cycle_type ON bug_applications.type = data_cycle_type.id ".				 
						 "WHERE pr_id = {$pr_id}";
	$which_project = " AND bug_applications.project = {$pr->project_id}";
	$sql_query = "SELECT DISTINCT bug_applications.id,date_published,bug_applications.application as reference,version,bug_applications.description, ".
		   "data_cycle_type.name as type,data_cycle_type.description as type_description FROM bug_applications,data_cycle_type ".
		   "WHERE bug_applications.type = data_cycle_type.id {$which_project} ORDER BY type ASC, reference ASC, date_published DESC";				 
	$result = A('db:'.$sql_query);
	$list_data = $result->fetchAll(PDO::FETCH_ASSOC);
}			 
$result = A('db:'.$list_pr_sql_query);
$list_pr = $result->fetchAll(PDO::FETCH_OBJ);
/* amount of rows */
if 	($list_pr !== false){
	$nb_row_response=count($list_pr);
}
// if ($nb_row_response == 0) {
	// Atomik::noRender();
// }