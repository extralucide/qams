<?php
/*
$name = isset($_POST['name']) ? $_POST['name'] : "";
$description = isset($_POST['description']) ? $_POST['description'] : ""; 

$result = Atomik_Db::insert('enterprises',array('name'=>$name,'description'=>$description));
if ($result){
	Atomik::Flash("Company added successfully.","success");
}
else{
	Atomik::Flash("Company adding failed.","failed");
}
*/
if (isset($_POST['cancel'])) {
	Atomik::redirect('list_company');
}

if (isset($_POST['submit'])) {
	$id = $_POST['id'];
	$acronym = $_POST['name'];
	$description = $_POST['description'];
	$type_id = $_POST['company_type_id'];
	if ($id == ""){
		$result = Atomik_Db::insert('enterprises',array('name'=>$acronym,
														'type_id'=>$type_id,
														'description'=>$description));
		if ($result){
			Atomik::Flash("Company added.","success");
		}
		else{
			Atomik::Flash("Company adding failed.","failed");
		}
	}
	else{	
		$result = Atomik_Db::update('enterprises',array('name'=>$acronym,
														'type_id'=>$type_id,
														'description'=>$description),array('id'=>$id));
		if ($result){
			Atomik::Flash("Company modified.","success");
		}
		else{
			Atomik::Flash("Company update failed.","failed");
		}
		
	}
	Atomik::redirect('list_company');	
}
if (isset($_POST['company_type_id'])) {
    Atomik::set('session/company_type_id',$_POST['company_type_id']);
    
}
Atomik::redirect('list_company');