<?php
$delete_link_id = (isset($_GET['delete_link_id']) ? $_GET['delete_link_id'] : null);
$table_upper_id = (isset($_GET['table_upper_id']) ? $_GET['table_upper_id'] : null);
Atomik_Db::delete("table_upper_data_{$table_upper_id}","id = ".$delete_link_id);
//$sql_query = "DELETE FROM table_upper_data_{$table_upper_id} WHERE id = ".$delete_link_id;	
//$result = $db->db_query($sql_query); 
Atomik::flash('Link remove.','success');
Atomik::redirect('edit_data?id='.$table_upper_id);
