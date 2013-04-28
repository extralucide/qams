<?php
$action_id = isset($_GET['id'])?$_GET['id']:"";
$delete_result = Atomik_Db::delete ('actions_attachment',"data_id = ".$action_id);

if ($delete_result)
	Atomik::flash('Data link successfully removed!', 'success');
else
	Atomik::flash('Data link not removed!', 'failed');
Atomik::redirect('post_action?id='.$action_id);
