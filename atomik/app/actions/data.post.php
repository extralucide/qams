<?php
Atomik::needed("Data.class");
Atomik::needed('Tool.class');
Atomik::needed('Baseline.class');
$postArray = &$_POST;

$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;  
$limite = isset($_POST['limite']) ? (int)$_POST['limite'] : 8;
$search = isset($_POST['search']) ? $_POST['search'] : "";
$data_type_id="";
if (isset($_POST['show_project'])) {
	Atomik::set('session/project_id',$_POST['show_project']);
}
if (isset($_POST['show_lru'])){
	Atomik::set('session/sub_project_id',$_POST['show_lru']);
}
if (isset($_POST['show_status'])) {
	Atomik::set('session/data_status_id',$_POST['show_status']);	
}
if (isset($_POST['show_poster'])) {
	Atomik::set('session/user_id',$_POST['show_poster']);	
}
if (isset($_POST['show_type'])) {
	Atomik::set('session/type_id',$_POST['show_type']);
	/* Does the data type is included in the selected group ?*/
	$data_type_id = $_POST['show_type'];
}
if (isset($_POST['show_baseline'])) {
	Atomik::set('session/baseline_id',$_POST['show_baseline']);	
}
if (isset($_POST['show_application'])) {
	Atomik::set('session/reference',$_POST['show_application']);	
}
if (isset($_POST['submit_group'])){
	$group_id = $_POST['submit_group'];
	if (Atomik::has('session/highlight')) {
		Atomik::delete('session/highlight');
	}
	Atomik::set('session/highlight/group_id',$group_id);
	switch($group_id) {
		case Plans:
			Atomik::set('session/highlight/plan',"active");			
			break;
		case Specification:
			Atomik::set('session/highlight/spec',"active");			
			break;
		case Design:
			Atomik::set('session/highlight/design',"active");			
			break;
		case Notes:
			Atomik::set('session/highlight/note',"active");	
			break;
		case Verification: 
			Atomik::set('session/highlight/test',"active");		
			break;	
		case Production:
			Atomik::set('session/highlight/prod',"active");		
			break;			
		case Certification:
			Atomik::set('session/highlight/cert',"active");				
			break;	
		case Configuration:
			Atomik::set('session/highlight/conf',"active");			
			break;			
		default:
			Atomik::set('session/highlight/all',"active");	
			Atomik::delete('session/highlight/group_id');
			break;
	}
	if (!Data::isInGroup($data_type_id,$group_id)){
		if (Atomik::has('session/type_id')) {
			Atomik::delete('session/type_id');
		}	
	}
}
if (isset($_POST['submit_baseline'])) {
	$set_baseline_id = isset($_POST['set_baseline']) ? $_POST['set_baseline'] : "";
	/* get all check buttons */
	for ($index=0;$index<count($_POST['data_check']);$index++)
	{
		$data_check_id = $_POST['data_check'][$index];	
		$result = Baseline::update_baseline_application ($data_check_id,$set_baseline_id);
		Atomik::flash($result["error"],$result["status"]);	
	}
	Atomik::redirect('data?page='.$page.'&limite='.$limite);
}
if (isset($_POST['submit_export'])) {
	$set_baseline_id = isset($_POST['set_baseline']) ? $_POST['set_baseline'] : "";
	/* get all check buttons */
	for ($index=0;$index<count($_POST['data_check']);$index++)
	{
		$data_check_id = $_POST['data_check'][$index];	
		$result = Baseline::update_baseline_application ($data_check_id,$set_baseline_id);
		Atomik::flash($result["error"],$result["status"]);	
	}
	Atomik::redirect('data?page='.$page.'&limite='.$limite);
}	
if (isset($_POST['set_status'])) {
	$set_status_id = isset($_POST['set_status']) ? $_POST['set_status'] : "";
	//echo "TEST checkbox<br/>";
	$error = "";
	$status = "success";
	for ($index=0;$index<count($_POST['data_check']);$index++)
	{
		$data_check_id = $_POST['data_check'][$index];
		
		$result = Data::updateStatus($data_check_id,$set_status_id);
		if ($result) {
			$error .= "update data with ID ".$data_check_id." with status ID ".$set_status_id."<br/>";
		}
		else {
			$error = "Status set failed !";
			$status = "failed";
			break;
		}
	}	
	Atomik::flash($error,$status);
	Atomik::redirect('data?page='.$page.'&limite='.$limite);
}
Atomik::redirect('data?page='.$page.'&limite='.$limite.'&search='.$search,false);
