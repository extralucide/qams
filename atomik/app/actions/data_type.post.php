<?php
$postArray = &$_POST;
if (isset($_POST['cancel'])){
	Atomik::redirect('data_type');		
}
if (isset($_POST['submit_update'])){
	$id = isset($_POST['id']) ? (int)$_POST['id'] : ""; 
	$rule = array(
		'name' => array('required' => true),
		'description' => array('required' => true),
		'comment' => array('required' => false),   
		'group_id' => array('required' => false),
	);
	if (($data = Atomik::filter($postArray, $rule)) === false) {
		Atomik::flash(A('app/filters/messages'), 'failed');
		Atomik::redirect('data_type');
	}
    foreach ( $postArray as $sForm => $value ) {
        if ( get_magic_quotes_gpc() )
            $data[$sForm] = stripslashes( $value );
        else
            $data[$sForm] = $value;
    }
	unset($data['id']);
	unset($data['submit_update']);
	if ($id != ""){
		$result = Atomik_Db::update('data_cycle_type', $data, "id = {$id}");
		if ($result){
			Atomik::flash('Type successfully updated!', 'success');
		}
		else{
			Atomik::flash('Type update failed!', 'failed');
		}
	}
	else{
		$result = Atomik_Db::insert('data_cycle_type',$data);
		if ($result){
			Atomik::Flash("Type added successfully.","success");
		}
		else{
			Atomik::Flash("Adding type failed.","failed");
		}	
	}
}
else{
	$name = isset($_POST['add_type_name']) ? $_POST['add_type_name'] : "";
	$description = isset($_POST['add_type_desc']) ? $_POST['add_type_desc'] : ""; 
	$result = Atomik_Db::insert('data_cycle_type',array('name'=>$name,
														'description'=>$description));
	if ($result){
		Atomik::Flash("Type added successfully.","success");
	}
	else{
		Atomik::Flash("Adding type failed.","failed");
	}
}
Atomik::redirect('data_type',false);
