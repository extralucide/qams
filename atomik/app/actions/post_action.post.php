<?php
Atomik::needed('User.class');
$postArray = &$_POST;
$context_array = isset($_POST['context'])? unserialize(urldecode(stripslashes($_POST['context']))):"";
$context = urlencode(serialize($context_array));
//exit(0);
$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;  
$limite = isset($_POST['limite']) ? (int)$_POST['limite'] : 8;
if (isset($postArray['cancel_up']) || isset($postArray['cancel_down'])) {
	/* clean form inputs */
	foreach($postArray as $key => $value):
		//echo $key ."<br/>";
		if (Atomik::has('session/post_action/'.$key)){
			Atomik::delete('session/post_action/'.$key);
		}
	endforeach;
	//exit(0);
	Atomik::redirect('actions?page='.$page.'&limite='.$limite,false);
}
if (isset($postArray['submit_action']) || isset($postArray['submit_action_up'])){
	$action = new Action;
	/* Save form inputs */
	if (Atomik::has('session/post_action/action_context')){
		Atomik::delete('session/post_action/action_context');
	}
	if (Atomik::has('session/post_action/description')){
		Atomik::delete('session/post_action/description');
	}
	if (Atomik::has('session/post_action/date_expected')){
		Atomik::delete('session/post_action/date_expected');
	}
	if (Atomik::has('session/post_action/review_id')){
		Atomik::delete('session/post_action/review_id');
	}	
	Atomik::add('session/post_action',array('action_context' => $postArray['action_context']));
	Atomik::add('session/post_action',array('description' => $postArray['description']));
	Atomik::add('session/post_action',array('date_expected' => $postArray['date_expected']));
	Atomik::add('session/post_action',array('review_id' => $postArray['review']));	
	/* Test form inputs */
	$rule = array(
		'project' => array('required' => false),
		'action_context' => array('required' => false),
		'lru' => array('required' => false),   
		'review' => array('required' => false),
		'submittername' => array('required' => false),
		'username' => array('required' => true),
		'description' => array('required' => true),
		'status' => array('required' => false),
		'criticality' => array('required' => false),
		'date_open' => array('required' => false),
		'date_expected' => array('required' => false),
	);
// var_dump($_POST);
// exit();
	if (($data = Atomik::filter($_POST, $rule)) === false) {
		Atomik::flash(A('app/filters/messages'), 'failed');
		//$var = Atomik::get('session/post_action/date_expected');
		// echo Date::convert_dojo_date($var);
		// exit(0);
		Atomik::redirect('post_action?context='.$context);
	}	
	Tool::deleteKey('session/post_action/action_context');
	Tool::deleteKey('session/post_action/description');
	Tool::deleteKey('session/post_action/date_expected');
	$info['project_id'] 		= isset($_POST['project']) ?  $_POST['project'] : "";
	$info['context']     		= isset($_POST['action_context']) ?  $_POST['action_context'] : "";
	$info['sub_project_id'] 	= isset($_POST['lru']) ?  $_POST['lru'] : "";
	$info['review_id'] 		 	= isset($_POST['review']) ?  $_POST['review'] : "";
	$info['submitter_id']  		= isset($_POST['submittername']) ?  $_POST['submittername'] : User::getIdUserLogged();
	$info['user_id']  			= isset($_POST['username']) ?  $_POST['username'] : "";
	$info['description']        = isset($_POST['description']) ?  $_POST['description'] : "";
	$info['criticality_id']     = isset($_POST['show_criticality']) ?  $_POST['show_criticality'] : "";
	$info['date_open']     		= isset($_POST['date_open']) ?  $_POST['date_open'] : "";
	$info['date_expected'] 		= isset($_POST['date_expected']) ?  $_POST['date_expected'] : "";
	$info['id'] 				= isset($_POST['update_id']) ?  $_POST['update_id'] : "";

	if($info['id'] == ""){
		/* New action */
		$new_action_id = $action->insert(&$info);	
		$action->set($new_action_id);
	}
	else {
		$info['status_id'] 		= isset($_POST['show_status']) ?  $_POST['show_status'] : "";
		$info['date_closure'] 	= isset($_POST['date_closure']) ?  $_POST['date_closure'] : "";
		/* Update action */
		$result = $action->update(&$info);
		if ($result) {
			$text = "Action ".$info['id']." updated by ".User::getNameUserLogged()." at ".date('H:i:s')." on ".date('Y/m/d');
			qams_log($text);	
			Atomik::Flash($text,'success');
		}
		else {
			Atomik::Flash('Action not taken into account.','failed');
		}		
	}
	Atomik::redirect('actions?page='.$page.'&limite='.$limite,false);
}
/* Upload document */ 
$upload_error = isset($_FILES['filename']['error']) ? $_FILES['filename']['error'] : "";
if(isset($_POST['upload_data']) AND 
   (!empty($_FILES['filename']['tmp_name'])) AND 
   (is_uploaded_file($_FILES['filename']['tmp_name']))) {
	$update_action_id = isset($_POST['id']) ?  $_POST['id'] : "";
	$attachment_id = "";
	$maxSize=30000000;  // Only save files smaller than 30M
	$nomOrigine = $_FILES['filename']['name'];
	$elementsChemin = pathinfo($nomOrigine);
	$extensionFichier = $elementsChemin['extension'];
	$extensionsAutorisees = array("pdf", "doc", "docm", "docx", "dot", "xls", "xlsx", "xlsm", "rtf", "ppt", "pptx");
	if (!(in_array($extensionFichier, $extensionsAutorisees))) {
		$error = "Le fichier n'a pas l'extension attendue !"; 
		$upload_status = "failed";
	} else {
		$upload_status = "failed";	
		/* Check if the same uploaded data is not already linked to this data */
		if ($attachment_id == "") {
			$attachment_id = Atomik_Db::insert("actions_attachment",array("data_id"=>$update_action_id,"ext"=>$extensionFichier,"real_name"=>$nomOrigine));
			if ($attachment_id) {
				$error_prr = 'New input:'.$attachment_id;
				$upload_status = "success";
			}
			else {
				//echo $sql_query."<br/>";
				$error_prr = 'DB insert error'.$update_action_id.":".$extensionFichier.":".$nomOrigine."<br/>";
			}
		}
		else {
			/* replace the already existing file */
			$result = Atomik_Db::update('actions_attachment',array('real_name'=>$nomOrigine,'ext'=>$extensionFichier),array('id'=>$attachment_id));
            if ($result) {
				$error_prr = 'Old input:'.$attachment_id;
				$upload_status = "success";
			}
			else {
				//echo $sql_query."<br/>";
				$error_prr = 'DB update error'.$update_data_id.":".$extensionFichier.":".$nomOrigine."<br/>";
			}			
		}
        $info = array('maxSize'=>$maxSize,
                        'error'=>$error_prr,
                        'upload_status'=>$upload_status,
                        'location'=>"docs".DIRECTORY_SEPARATOR."actions".DIRECTORY_SEPARATOR);               
        /* Check if the same uploaded data is not already linked to this data */
        // if (Data::Check_Link_Validity($update_data_id)) {
        $filename = $attachment_id.".".$extensionFichier;
        $uploadName = Data::upload($filename,&$info);
		Atomik::Flash($error_prr,$upload_status);
		Atomik::redirect('post_action?id='.$update_action_id,false);
	}
}
Atomik::redirect('post_action');
