<?php
Atomik::needed('User.class');
// Atomik::setView("user/add_user_project_link");
Atomik::noRender();
Atomik::disableLayout();
$user_id = isset($_POST['user_id']) ? $_POST['user_id'] : ""; 
$project_id = isset($_POST['project_id']) ? $_POST['project_id'] : ""; 
$result = Atomik_Db::insert('user_join_project',array('user_id'=>$user_id,'project_id'=>$project_id));
if ($result){
	Atomik::Flash("Project assignment added.","success");
}
else{
	Atomik::Flash("Project assignment failed.","failed");
}
Atomik::redirect('edit_user?tab=projects&edit_user_id='.$user_id);
