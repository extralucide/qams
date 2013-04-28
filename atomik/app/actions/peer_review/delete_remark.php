<?php
$remark_id = (isset($_GET['id']) ? $_GET['id'] : null);
Atomik_Db::delete("bug_messages","reply_id = ".$remark_id);
Atomik::flash('Remark '.$remark_id.' removed.','success');
Atomik::redirect('inspection');
