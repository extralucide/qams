<?php
$postArray = &$_POST;

if (isset($_POST['cancel'])) {
    Atomik::redirect('list_aircraft',false);
}
if (isset($_POST['submit_aircraft'])) {
    $data['id'] = $_POST['id'];
    $data['name'] = $_POST['name'];
    $data['description'] = $_POST['description'];
	$data['company_id'] = $_POST['show_company'];
    if ($data['id'] !=""){
        /* update */
        $result = Atomik_Db::update('aircrafts', 
									array('name'=> $data['name'],
										  'description'=> $data['description'],
										  'company_id'=> $data['company_id']),
									array('id'=>$data['id']));
        if($result){
            Atomik::Flash("Aircraft updated successfully.","success");
        }
        else{
            Atomik::Flash("Aircraft update failed.","failed");
        }
    }
    else{
        /* insert */
        $result = Atomik_Db::insert('aircrafts', array('name'=> $data['name'],
														'description'=> $data['description'],
														'company_id'=> $data['company_id']));
        if($result){
            Atomik::Flash("New aircraft added successfully.","success");
        }
        else{
            Atomik::Flash("New aircraft adding failed.","failed");
        }
    }
    Atomik::redirect('list_aircraft',false);
}
if (isset($_POST['show_company'])) {
    Atomik::set('session/company_id',$_POST['show_company']);
}
Atomik::redirect('list_aircraft',false);
