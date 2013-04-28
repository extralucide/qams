<?php
if (isset($_POST['cancel'])) {
	Atomik::redirect('list_company');
}
if (isset($_POST['submit'])) {
	$acronym = $_POST['name'];
	$description = $_POST['description'];
	$result = Atomik_Db::insert('enterprises',array('name'=>$acronym,
													'description'=>$description));
	if ($result){
		Atomik::Flash("Company added.","success");
	}
	else{
		Atomik::Flash("Company adding failed.","failed");
	}
	Atomik::redirect('list_company');
}
else if (isset($_POST['modify'])){
	$id = $_POST['id'];
	$acronym = $_POST['name'];
	$description = $_POST['description'];		
	$result = Atomik_Db::update('enterprises',array('name'=>$acronym,
													'description'=>$description),array('id'=>$id));
	if ($result){
		Atomik::Flash("Company modified.","success");
	}
	else{
		Atomik::Flash("Company update failed.","failed");
	}
	Atomik::redirect('list_company');	
}