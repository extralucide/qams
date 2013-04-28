<?php
$where = "";
$remove_question_id= "";
$review_id = "";
if ((isset($_REQUEST['id'])) &&($_REQUEST['id'] != "" )) {
	$remove_question_id = $_REQUEST['id'];
	$review_id = $_REQUEST['review_id'];
	$where = "id = ".$remove_question_id;
}
$location = "{$_SERVER['PHP_SELF']}?action=show_checklist";
$delete_result = Atomik_Db::delete ('checklist_questions',$where);

if ($delete_result)
	Atomik::flash('Question from checklist successfully removed!', 'success');
else
	Atomik::flash('Question from checklist not removed!', 'failed');
Atomik::redirect('edit_review_type?tab=checklist&id='.$review_id,false);
