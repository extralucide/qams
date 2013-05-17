<?php
$system_id = isset($_GET['id']) ? $_GET['id'] : "";
$id = isset($_REQUEST['item_id']) ? $_REQUEST['item_id'] : "";
$delete_result = Atomik_Db::delete ('lrus', "id = ".$id);
if ($delete_result)
	Atomik::flash('Item successfully removed!', 'success');
else
	Atomik::flash('Item not removed!', 'failed');
Atomik::redirect('edit_project?id='.$system_id,false);
