<?php
Atomik::disableLayout();
Atomik::noRender();
Atomik::needed('Db.class');
//include "inc/Db.class.php";
$db = new Db();
$data_id 	= $_POST['id'];
$upper_data_id 	= $_POST['select_upper_data_id'];

/* check if the table upper data already exists */
$list_table_upper_data_query = "SHOW TABLES  FROM {$db->db_select} LIKE 'table_upper_data_".$data_id."'";
$result = A("db:".$list_table_upper_data_query); 
$nbtotal=count($result->fetchAll());
if ($nbtotal == 0){ 
	$sql_query = "CREATE TABLE `{$db->db_select}`.`table_upper_data_".$data_id."` (`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,".
			"`upper_data_id` INT NOT NULL) ENGINE = MYISAM ;";        
	$result = A("db:".$sql_query);    
} 
//$sql_query = "INSERT INTO `table_upper_data_".$data_id."` (`upper_data_id`) VALUES('{$upper_data_id}')";
$link_id = Atomik_Db::insert('table_upper_data_'.$data_id,array('upper_data_id'=>$upper_data_id));
Atomik::Flash('Upper data link '.$link_id.'added.','success');
Atomik::redirect("edit_data?tab=traceability");
