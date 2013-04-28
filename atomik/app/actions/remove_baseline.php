<?php
$where = "";
$remove_baseline_id= "";
if ((isset($_REQUEST['id'])) &&($_REQUEST['id'] != "" )) {
	$remove_baseline_id = $_REQUEST['id'];
	$where = "id = ".$remove_baseline_id;
}
$location = "{$_SERVER['PHP_SELF']}?action=baseline";
$delete_result = Atomik_Db::delete ('baseline_join_data',$where);

if ($delete_result)
	Atomik::flash('Baseline link successfully removed!', 'success');
else
	Atomik::flash('Baseline link not removed!', 'failed');
Atomik::redirect('baseline');
