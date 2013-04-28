<?php
$where = "";
$remove_id= "";
if ((isset($_REQUEST['id'])) &&($_REQUEST['id'] != "" )) {
	$remove_id = $_REQUEST['id'];
	$review = A("db:SELECT user_id,review_id FROM user_join_review WHERE id = {$remove_id}")->fetch();
	// var_dump($review);
	$delete_result = Atomik_Db::delete ('user_join_review',array('id'=>$remove_id));
	Atomik::needed('User.class');
	$user = new User;
	$user->get_user_info($review['user_id']);
	// exit();
}
if ($delete_result)
	Atomik::flash('User '.$user->name.' successfully removed from meeting.', 'success');
else
	Atomik::flash('User '.$user->name.' not removed.', 'failed');
Atomik::redirect("post_review?id=".$review['review_id']."&tab=attendee",false);
