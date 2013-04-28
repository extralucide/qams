<?
include "../includes/bug_functions.php";
include "../includes/config.php";
include "../inc/Date.class.php";
include "../inc/Data.class.php";
include "../inc/Db.class.php";
if(isset($_REQUEST['bug_cookie'])) {
    $bug_cookie = $_REQUEST['bug_cookie'];
    /* If user is logged in, get all user information */
    if(isset($bug_cookie)) {
        $array=unserialize(stripslashes($bug_cookie));
        $Id_User = $array[6];
    }
}
$update="";
$update_id="";
if(isset($_REQUEST['spec_id'])) { 
    $update_id=$_REQUEST['spec_id'];
    unset ($_REQUEST['spec_id']);
	$table_req=$_REQUEST['table_req'];
    unset ($_REQUEST['table_req']);
    $update="yes";
    $title ="Update Requirement";
    $button="Modify Requirement";
    $where= $table_req.".id = ".$update_id;
    //echo "Table:".$table_id." Req_id:".$where."<br>";
    //$updated_req = Atomik_Db::findAll($table_req,$where);
	$regexp_result = preg_match("([0-9]{1,3})",$table_req,$table_req_id);
	//print_r($table_req_id);
	//echo "Table:".$table_req_id[0]."<br>";
	$spec_id = $table_req_id[0];
	$sql_query = "SELECT * FROM ".$table_req.
				 " LEFT OUTER JOIN bug_applications ON bug_applications.id = ".$spec_id." WHERE ".$where;
	//echo $sql_query;			 
	$updated_req = A("db:".$sql_query);
    //echo "TEST:".$sql_query."<br>";
    if ($updated_req) {
		$req_id = "";
		$project_id = "";
		$lru_id = ""; 
		$type_id = "";                
		$description = "requirement internal error";
		$rationale = "";
		$derived_id = 1;
		$safety_id = 2;
		$status_id = 2;
		$allocation_id = 2;
		$validation_id = 2;
		foreach ($updated_req as $row) {
			//print_r($row);
			//echo "TEST: Project ".$row['project_id']."<br>";
			$project_id = $row['project'];
			$lru_id = $row['lru'];
			$req_id = $update_id;
			$type_id = $row['type'];
			$description = $row["text"];
			$rationale = $row['rationale'];
			$derived_id = $row['derived'];
			$safety_id = $row['safety'];
			$status_id = $row['status'];
			$allocation_id = $row['allocation'];
			$validation_id = $row['validation'];
		}
	}
    else {
		$req_id = "";
		$project_id = "";
		$lru_id = ""; 
		$type_id = "";                
		$description = "requirement internal error";
		$rationale = "";
		$derived_id = 1;
		$safety_id = 2;
		$status_id = 2;
		$allocation_id = 2;
		$validation_id = 2;
    }

}
else {
	/* new requirement */
	$update_id = "";
	$table_req=$_REQUEST['table_req'];
    unset ($_REQUEST['table_req']);
	$title ="New Requirement";
    $button="Add Requirement";
    if (isset($_REQUEST['show_project'])) {
		$project_id = $_REQUEST['show_project'];
    }
    else {
		$project_id = "";
	}
	if (isset($_REQUEST['show_lru'])) {
		$lru_id = $_REQUEST['show_lru'];
    }
    else {
		$lru_id = "";
	}	
	if (isset($_REQUEST['type_id'])) {		
		$type_id = $_REQUEST['type_id'];
	}
    else {
		$type_id = "";
	}	
	if (isset($_REQUEST['spec_id'])) {
		$spec_id = $_REQUEST['spec_id'];
	}
	else {
		$spec_id = "";
	}
	$spec_data = new Get_Data($spec_id,"atomik");
	//echo "TEST:".$project_id." ".$lru_id." ".$spec_id." ".$data->full_ident;
	$title.= '<br/><span style="font-size:small">'.$spec_data->full_ident.'</span>';
	$req_id = "";
	$description = "";
	$rationale = "";
	$derived_id = 2;
	$safety_id = 2;
	$status_id = 2;
	$allocation_id = 4;
	$validation_id = 2;
}
//$list_table = A("db:SHOW TABLES FROM olivier_appere LIKE '%table_req%'");
//echo "SHOW TABLES FROM olivier_appere LIKE '%table_req%' LEFT OUTER JOIN table_upper_data_{$update_id} ON table_upper_data_{$update_id}.upper_data_id = ";
$list_table_upper_data_query = "SHOW TABLES  FROM {$db_select} LIKE '%table_upper_data_".$spec_id."%'";
$db= new Db("atomik");
$result = $db->db_query($list_table_upper_data_query); 
$nbtotal=mysql_num_rows($result);
if ($nbtotal == 0) {
	// create upper table
	$sql_query = "CREATE TABLE `{$db_select}`.`table_upper_data_".$spec_id."` (`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,".
				 "`upper_data_id` INT NOT NULL) ENGINE = MYISAM ;";   
	$data_traca_spec = A("db:".$sql_query);
}
if ($spec_id != ""){ 
	$list_table = A("db:SELECT upper_data_id FROM table_upper_data_{$spec_id}");
	$index_table=0;
	$test_data_spec="";
	foreach ($list_table as $table) {
		//echo "index".$index_table."<br/>";
		$all_table_req_id = $table['upper_data_id'];
		$all_table_req = "table_req_".$all_table_req_id;
		if ($index_table > 0) {
			$test_data_spec.=" UNION ";
		}
		//$all_table_req = Atomik::escape($table[upper_data_id]);
		//echo "TEST:".$all_table_req."<br/>";
		//$regexp_result = preg_match("([0-9]{1,3})",$all_table_req,$all_table_req_id);
		//print_r($table_req_id);
		//$all_spec_id = $all_table_req_id[0];
		$test_data_spec.= "SELECT {$all_table_req}.id as req_id,bug_applications.application as reference, bug_applications.version, data_cycle_type.name as type FROM {$all_table_req} ".
										"LEFT OUTER JOIN bug_applications ON bug_applications.id = {$all_table_req_id} ".
										"LEFT OUTER JOIN data_cycle_type ON data_cycle_type.id = bug_applications.type ";

		$index_table++;
		//break;  
	}  
	$all_req = A("db:".$test_data_spec);	
} 
else {
	echo "Warning, missing data id.";
	$all_req = "";
}
//echo $test_data_spec."<br/>";
$derived_combobox = Atomik_Db::findAll('req_derived');
$safety_combobox = Atomik_Db::findAll('req_safety');
$allocation_combobox = Atomik_Db::findAll('req_allocation');
$status_combobox = Atomik_Db::findAll('req_status');
$validation_combobox = Atomik_Db::findAll('req_validation');	
//Atomik::end();
