<?php
$data_id = (isset($_GET['id']) ? $_GET['id'] : null);
Atomik_Db::delete("bug_applications","id = ".$data_id);
Atomik::flash('Data removed.','success');
Atomik::redirect('data');
