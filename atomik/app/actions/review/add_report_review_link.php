<?php
Atomik::needed('Db.class');
$db = new Db();
$mom_id 	= $_POST['select_mom_id'];
$review_id = $_POST['review_id'];
$sql_query = "INSERT INTO data_join_review (`data_id`, `review_id`) VALUES('{$mom_id}','{$review_id}')";
$result = $db->db_query($sql_query);
Atomik::redirect('post_review?tab=minutes');
