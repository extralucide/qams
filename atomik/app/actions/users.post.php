<?php
Atomik::needed("Tool.class.php");
$post = new Tool;
$post->setPOST();
$postArray = &$_POST;
// var_dump($_POST);
// exit();
$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;  
$limite = isset($_POST['limite']) ? (int)$_POST['limite'] : 8;
$search = isset($_POST['search']) ? $_POST['search'] : "";

if (isset($_POST['show_company'])) {
	Atomik::set('session/company_id',$_POST['show_company']);
}
if (isset($_POST['show_project'])) {
	Atomik::set('session/project_id',$_POST['show_project']);
}
if (isset($_POST['submit_user'])) {
	$edit_user_id = $_POST['edit_user_id'];
	if ($edit_user_id != "") {
		$result = User::update_user(&$postArray);
		if ($result) {
			$error = "Update successful !";
			$status = "success";
		}
		else {
			$error = "Update failed !";
			$status = "failed";
		}
	}
	else {
		$result = User::add_user(&$postArray);
		if ($result) {
			$error = "Adding user successful !";
			$status = "success";
		}
		else {
			$error = "Adding user failed !";
			$status = "failed";
		}
	}
	// var_dump($postArray);exit();
	Atomik::Flash($error,$status);
	Atomik::redirect('users?page='.$page.'&limite='.$limite.'&search='.$search);
}

if (isset($postArray['export_user_submit'])){
	/* Export user's list in SQLite 3 format */
	$list_users = Atomik_Db::findAll('bug_users');
	$sqlite_txt =  "";
	foreach($list_users as $user):
		$sqlite_txt .= "INSERT INTO `users` (`id`, `fname`, `lname`, `username`, `password`, `function`, `enterprise_id`, `telephone`, `email`, `is_admin`, `last_logged`, `dismissed`, `service_id`, `department_id`, `lotus_database`) VALUES ";
		$sqlite_txt .= "('{$user['id']}','{$user['fname']}','{$user['lname']}','{$user['username']}','{$user['password']}','{$user['function']}','{$user['enterprise_id']}','{$user['telephone']}','{$user['email']}','{$user['is_admin']}','{$user['last_logged']}','{$user['dismissed']}','{$user['service_id']}','{$user['department_id']}','{$user['lotus_database']}');"; 
		$sqlite_txt .= "\n";
	endforeach;
	$monfichier = fopen("..".DIRECTORY_SEPARATOR."result".DIRECTORY_SEPARATOR."sqlite.txt", 'w');
	fputs($monfichier, $sqlite_txt."\n");
	fclose($monfichier);
	Atomik::Flash("User list exported.","success");
}
// exit(0);
Atomik::redirect('users?page='.$page.'&limite='.$limite.'&search='.$search,false);
