<?
Atomik::disableLayout();
Atomik::needed("Remark.class");
Atomik::needed("User.class");
$error="";
$remark_id = isset($_GET['remark_id'])?$_GET['remark_id']:"";
$status_id = isset($_GET['status_id'])?$_GET['status_id']:"";
$current_status_id = isset($_GET['current_status_id'])?$_GET['current_status_id']:"";
if(isset($_GET['current_status_id'])){
	/* Control the transition criteria */
	$transitions_array = Remark::getTransitions($current_status_id);
	$transition_authorized = false;
	foreach($transitions_array as $transition):
		if ($transition == $status_id){
			$transition_authorized = true;
			break;
		}
	endforeach;
	if ($transition_authorized){
		
		/* Update status of the remarks without comment */
		$new_status = Remark::getStatusName($status_id);
		/* Read previous status */
		$previous_status = Remark::getRemarkStatus($remark_id);
		/* Read id of document */
		$document_id = Remark::getRemarkDocument($remark_id);
	    /* add new status in the description */
		$author_response = "<p> Status: {$previous_status} --> {$new_status} </p>";		
		$result = Remark::create_response($author_response,
											User::getIdUserLogged(),
											$document_id,
											$status_id,
											$remark_id);
		$result = Remark::setStatus($remark_id,$status_id);	
		$error="<li class='success' style='list-style-type: none;margin-top:350px;margin-right:0px;min-height:20px'>Status successfully set to {$new_status}.</li>";		
	}
	else{
		$error="<li class='failed' style='list-style-type: none;margin-top:350px;margin-right:0px;min-height:20px'>Transition not allowed.</li>";
	}
}
// $db = new Db();   
// $status_query = "SELECT * FROM bug_status WHERE `type` LIKE 'peer review' ORDER BY `name` ASC";
//echo $sql;
$status_list = Remark::getStatusList();
Atomik::needed("Remark.class");
$remark = new Remark;
$remark->get($remark_id);
// $data->get($data_id);
$diagram_img = $remark->createDiagram();
