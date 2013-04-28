<?php
$user_id = isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : "";
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : "";

$delete_result = Atomik_Db::delete ('user_join_project', "id = ".$id);
if ($delete_result)
	Atomik::flash('Project link successfully removed!', 'success');
else
	Atomik::flash('Project link not removed!', 'failed');
Atomik::redirect('../edit_user?edit_user_id='.$user_id,false);
