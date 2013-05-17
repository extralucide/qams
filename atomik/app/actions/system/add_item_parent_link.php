<?php
Atomik::needed('User.class');
// Atomik::setView("user/add_user_project_link");
Atomik::noRender();
Atomik::disableLayout();
$item_id = isset($_POST['item_id']) ? $_POST['item_id'] : ""; 
$parent_id = isset($_POST['parent_id']) ? $_POST['parent_id'] : ""; 
$result = Atomik_Db::insert('lru_join_project',array('item_id'=>$item_id,'parent_id'=>$parent_id));
if ($result){
	Atomik::Flash("Parent link added.","success");
}
else{
	Atomik::Flash("Parent link adding failed.","failed");
}
Atomik::redirect('edit_eqpt?tab=parents&id='.$item_id);
