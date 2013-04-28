<?php

$data_id = isset($_REQUEST['data_id']) ? $_REQUEST['data_id'] : "";
$link_id = isset($_REQUEST['link_id']) ? $_REQUEST['link_id'] : "";

$delete_result = Atomik_Db::delete ('data_location',"id = ".$link_id);

if ($delete_result)
	Atomik::flash('Data link successfully removed!', 'success');
else
	Atomik::flash('Data link not removed!', 'failed');
Atomik::redirect('../edit_data?tab=attachment&id='.$data_id,false);
