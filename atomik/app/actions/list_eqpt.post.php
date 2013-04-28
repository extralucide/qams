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
    Atomik::redirect('list_eqpt',false);
}
if (isset($_POST['submit_project'])) {
    //$id = $_POST['id'];
    //$name = $_POST['name'];
    //$description = $_POST['description'];
    //foreach ( $postArray as $sForm => $value ) {
    //    if ( get_magic_quotes_gpc() )
    //        $data[$sForm] = stripslashes( $value );
    //    else$_POST['submit_aircraft']
    //        $data[$sForm] = $value;
    //}
    //var_dump($data);
    $data['id'] = $_POST['id'];
    $data['aircraft_id'] = $_POST['aircraft_id'];
    $data['project'] = $_POST['name'];
    $data['description'] = $_POST['description'];
    if ($data['id'] !=""){
        /* update */
        $where = array('id'=>$data['id']);
        //var_dump($data);
        //var_dump($where);
        //exit();
        $result = Atomik_Db::update('projects', array('project'=> $data['project'],'description'=> $data['description'],'aircraft_id'=>$data['aircraft_id']), $where);
        if($result){
            Atomik::Flash("Project updated successfully.","success");
        }
        else{
            Atomik::Flash("Project update failed.","failed");
        }
    }
    else{
        /* insert */
        $result = Atomik_Db::insert('projects', array('project'=> $data['project'],'description'=> $data['description'],'aircraft_id'=>$data['aircraft_id']));
        if($result){
            Atomik::Flash("New project added successfully.","success");
        }
        else{
            Atomik::Flash("New project adding failed.","failed");
        }
    }
}
Atomik::redirect('list_eqpt');