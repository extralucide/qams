<?php
//include "../inc/Db.class.php";
if(!isset($_REQUEST['id'])) {
	//return;
}
$postArray = &$_POST ;
if (!isset($postArray['cancel'])) {
	$rule = array(
			'rationale'  => array('required' => true),
			'text'  => array('required' => true),
			'status'  => array('required' => true),
			'allocation' => array('required' => true),
			'validation' => array('required' => true),
			'derived' => array('required' => true),
			'safety' => array('required' => true)
	);

	/* on nepeut pas utiliser cette fonction filter car elle supprime les balises html */
	if (($data_tmp = Atomik::filter($_POST, $rule)) === false) {
		Atomik::flash(A('app/filters/messages'), 'error');
		return;
	}
	//print_r($postArray);
	$update=$postArray['update'];
	$update_id=$postArray['update_id'];
	$table_req=$postArray['table_req'];
	$project_id=$postArray['project_id'];
	$lru_id=$postArray['lru_id'];
	$type_id=$postArray['type_id'];
	$spec_id=$postArray['spec_id'];
	Atomik::set(array(
		'project_id' => $project_id,
		'lru_id' => $lru_id
	));
	unset($postArray['update'],
		$postArray['update_id'],
		$postArray['table_req'],
		$postArray['project_id'],
		$postArray['lru_id'],
		$postArray['type_id'],
		$postArray['spec_id']);
	/* Check if tables of requirements exits */	
	$list_table_query = "SHOW TABLES  FROM olivier_appere LIKE '%".$table_req."%'";
	$db= new Db("atomik");
	$result = $db->db_query($list_table_query); 
	$nbtotal=mysql_num_rows($result);
	if ($nbtotal == 0) {
		// create req table
		$sql_table_query = "CREATE TABLE `olivier_appere`.`".$table_req."` (`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,".
			"`text` TEXT NOT NULL ,".
			"`derived` BOOL NOT NULL ,".
			"`safety` BOOL NOT NULL ,".
			"`rationale` TEXT NOT NULL ,".
			"`allocation` INT NOT NULL ,".
			"`status` INT NOT NULL ,".
			"`validation` INT NOT NULL) ENGINE = MYISAM ;";
		$table_req_create = A("db:".$sql_table_query);
	}

	if ($update == "yes") {
		foreach ( $postArray as $sForm => $value ) {
			if ( get_magic_quotes_gpc() )
				$data[$sForm] = stripslashes( $value );
			else
				$data[$sForm] = $value;
			//echo "field:".$sForm."=>".$value."<br />";
		}
		
		$where = "id = ".$update_id;
		//echo "Table Req: ".$table_req." Where: ".$where."<br>";
		//print_r($data);
		$update_result = Atomik_Db::update($table_req, $data, $where);
		//echo $update_result;
		if ($update_result)
			Atomik::flash('Requirement successfully updated!', 'success');
		else
			Atomik::flash('Requirement not updated!', 'failed');
	}
	else {
		foreach ( $postArray as $sForm => $value ) {
			if ( get_magic_quotes_gpc() )
				$data[$sForm] = stripslashes( $value );
			else
				$data[$sForm] = $value;
		}
		//$data['maj'] = date('Y-m-d h:i:s');
		//echo "TABLE:".$table_req."<br/>";
		//print_r($data);
		$insert_result = Atomik_Db::insert($table_req, $data);
		if ($insert_result)
			Atomik::flash('Requirement successfully added!', 'success');
		else
			Atomik::flash('Requirement not added!', 'failed');
	}
}	
Atomik::redirect('show_requirements');//?project_id='.$project_id.'&lru_id='.$lru_id.'&type_id='.$type_id.'&spec_id='.$spec_id);
Atomik::end();
