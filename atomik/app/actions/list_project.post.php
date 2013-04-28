<?php
$postArray = &$_POST;
if (isset($_POST['show_company'])) {
    Atomik::set('session/company_id',$_POST['show_company']);
}
if (isset($_POST['show_aircraft'])) {
    Atomik::set('session/aircraft_id',$_POST['show_aircraft']);
}
if (isset($_POST['show_project'])) {
    Atomik::set('session/project_id',$_POST['show_project']);
}
if (isset($_POST['cancel'])) {
    Atomik::redirect('list_project',false);
}
if (isset($_POST['submit_project'])) {
    $data['id'] = $_POST['id'];
    $data['aircraft_id'] = $_POST['aircraft_id'];
    $data['project'] = $_POST['name'];
    $data['description'] = $_POST['description'];
	$data['folder'] = $_POST['folder'];
	$data['workspace'] = $_POST['workspace'];
    if ($data['id'] !=""){
        /* update */
        $where = array('id'=>$data['id']);
        $result = Atomik_Db::update('projects',
									array('project'=> $data['project'],
											'description'=> $data['description'],
											'aircraft_id'=>$data['aircraft_id'],
											'workspace'=>$data['workspace'],
											'folder'=>$data['folder']),
											$where);
        if($result){
            Atomik::Flash("Project updated successfully.","success");
        }
        else{
            Atomik::Flash("Project update failed.","failed");
        }
    }
    else{
        /* insert */
        $result = Atomik_Db::insert('projects', 
									array('project'=> $data['project'],
											'description'=> $data['description'],
											'aircraft_id'=>$data['aircraft_id'],
											'workspace'=>$data['workspace'],
											'folder'=>$data['folder']));
        if($result){
            Atomik::Flash("New project added successfully.","success");
        }
        else{
            Atomik::Flash("New project adding failed.","failed");
        }
    }
}
Atomik::redirect('list_project');