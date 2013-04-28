<?php
Atomik::needed("Db.class");
if (isset($_POST['create_db'])) {
	$db = new Db(false);
	$db->db_create_qams_db();
}
Atomik::redirect('home');
