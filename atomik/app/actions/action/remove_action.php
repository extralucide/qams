<?php
$action_id = (isset($_GET['id']) ? $_GET['id'] : null);
Atomik_Db::delete("actions","id = ".$action_id);
Atomik::flash('Action '.$action_id.' removed.','success');
Atomik::redirect('actions');
