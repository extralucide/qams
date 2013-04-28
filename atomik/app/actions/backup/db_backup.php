<?php
Atomik::disableLayout();
Atomik::needed('Db.class');
$db = new Db();
$db->db_backup();
