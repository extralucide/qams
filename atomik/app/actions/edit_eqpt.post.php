<?php
if (isset($_POST['cancel'])) {
	Atomik::redirect('list_eqpt');
}
$acronym = $_POST['name'];
$project_id = $_POST['project_id'];
$description = $_POST['description'];
$abstract = $_POST['abstract'];
$part_number = $_POST['part_number'];
$dal = $_POST['dal'];
$scope_id = $_POST['scope_id'];
$manager_id = $_POST['show_poster'];
$parent_id = $_POST['show_lru'];
if (isset($_POST['submit'])) {
	$eqpt_id = Atomik_Db::insert('lrus',array('lru'=>$acronym,
								'project'=>$project_id,
								'description_lru'=>$description,
								'abstract'=>$abstract,
								'part_number'=>$part_number,
								'dal'=>$dal,
								'scope_id'=>$scope_id,
								'manager_id'=>$manager_id,								
								'parent_id'=>$parent_id));
	if ($eqpt_id){
		/* update parent ID in case this field is empty */
		if ($parent_id == ""){
			$result = Atomik_Db::update('lrus',array('parent_id'=>$eqpt_id),array('id'=>$eqpt_id));
		}
		Atomik::Flash("Equipment added.","success");
	}
	else{
		Atomik::Flash("Equipment adding failed.","failed");
	}
	Atomik::redirect('list_eqpt');
}
else if (isset($_POST['modify'])){
	$eqpt_id = $_POST['id'];	
	$result = Atomik_Db::update('lrus',array('lru'=>$acronym,
											'project'=>$project_id,
											'description_lru'=>$description,
											'abstract'=>$abstract,
											'part_number'=>$part_number,
											'dal'=>$dal,
											'scope_id'=>$scope_id,
											'manager_id'=>$manager_id,
											'parent_id'=>$parent_id),array('id'=>$eqpt_id));
	if ($result){
		Atomik::Flash("Equipment modified.","success");
	}
	else{
		Atomik::Flash("Equipment update failed.","failed");
	}
	Atomik::redirect('list_eqpt');	
}