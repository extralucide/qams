<?php
$item_id = isset($_GET['item_id']) ? $_GET['item_id'] : "";
$id = isset($_GET['link_id']) ? $_GET['link_id'] : "";
if ($id != ""){
	$delete_result = Atomik_Db::delete ('lru_join_project', "id = ".$id);
	if ($delete_result)
		Atomik::flash('Parent link successfully removed.', 'success');
	else
		Atomik::flash('Parent link not removed;', 'failed');	
	}
else {
	Atomik::flash('Parent link not removed because this is the primary link;', 'failed');
}	
Atomik::redirect('edit_eqpt?tab=parents&id='.$item_id,false);
