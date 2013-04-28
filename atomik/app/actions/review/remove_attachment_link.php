<?php
$delete_link_id = (isset($_GET['link_id']) ? $_GET['link_id'] : null);
$data_id = (isset($_GET['data_id']) ? $_GET['data_id'] : null);
Atomik_Db::delete("reviews_attachment","id = ".$delete_link_id);
Atomik::flash('Link remove.','success');
Atomik::redirect('post_review?tab=attachment&id='.$data_id);
