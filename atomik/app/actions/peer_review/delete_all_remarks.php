<?php
Atomik::needed("Data.class");
$remark_id = (isset($_GET['id']) ? $_GET['id'] : null);
$data_id = (isset($_GET['delete_app']) ? $_GET['delete_app'] : null);
$data = new Data;
$data->get($data_id);
Atomik_Db::delete("bug_messages","application = ".$data_id);
Atomik::flash('All remarks of document '.$data->reference.' removed.','success');
Atomik::redirect('data');
