<?php
$delete_link_id = (isset($_GET['link_id']) ? $_GET['link_id'] : null);
$data_id = (isset($_GET['data_id']) ? $_GET['data_id'] : null);
$result = Atomik_Db::delete("peer_review_location","id = ".$delete_link_id);
if ($result){
	Atomik::flash('Link removed.','success');
}
else{
	Atomik::flash('Link removal failed.','failed');
}
Atomik::redirect('edit_data?tab=peer_reviews&id='.$data_id,false);
