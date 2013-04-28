<?php
Atomik::needed("Db.class");
Atomik::needed("Data.class");
$add_app_name             	= isset($_POST['add_app_name']) ? $_POST['add_app_name'] : "";
$add_app_version          	= isset($_POST['add_app_version']) ? $_POST['add_app_version'] : "";
$add_app_equipment        	= isset($_POST['show_lru']) ? $_POST['show_lru'] : "";
$add_app_project          	= isset($_POST['show_project']) ? $_POST['show_project'] : "";
$add_app_type             	= isset($_POST['show_type']) ? $_POST['show_type'] : "";
$previous_data_id        	= isset($_POST['previous_data_id']) ? $_POST['previous_data_id'] : "";
$add_app_status           	= isset($_POST['show_status']) ? $_POST['show_status'] : "";
$priority_id           		= isset($_POST['priority_id']) ? $_POST['priority_id'] : "";
$add_app_description      	= isset($_POST['add_app_description']) ? $_POST['add_app_description'] : "";
$add_app_abstract      		= isset($_POST['add_app_abstract']) ? $_POST['add_app_abstract'] : "";
$add_app_baseline         	= isset($_POST['add_app_baseline']) ? $_POST['add_app_baseline'] : "";
$add_app_location         	= isset($_POST['add_app_location']) ? $_POST['add_app_location'] : "";
$add_app_peer_review      	= isset($_POST['peer_review_location']) ? $_POST['peer_review_location'] : "";
$add_app_review_deadline  	= isset($_POST['peer_review_deadline']) ? $_POST['peer_review_deadline'] : "";
$add_app_date             	= isset($_POST['add_app_date']) ? $_POST['add_app_date'] : "";
$add_app_author           	= isset($_POST['show_poster']) ? $_POST['show_poster'] : "";
$update_data_id 			= isset($_POST['id']) ? $_POST['id'] : ""; 
$keywords          			= isset($_POST['keywords']) ? $_POST['keywords'] : "";
$db = new Db;
$data = new Data;
if (isset($_POST['last_issue'])||isset($_POST['last_issue'])){
   $data->get($update_data_id);
   /* update table of last data */
   $found_data = Atomik_Db::find('data_last',array('reference'=>$data->reference));
   if ($found_data){
		$result = Atomik_Db::update('data_last',array('data_id'=>$update_data_id),array('reference'=>$data->reference));
   }
   else{
		/* first version */
		$result = Atomik_Db::insert('data_last',array('data_id'=>$update_data_id,'reference'=>$data->reference));
   }
	if  ($result === false) {
		$error = "Failed to grant this document version to official issue.";
		$status = "failed";
	}
	else {
		$error = "This document is now the offcial issue displayed.";
		$status = "success";
	}
	Atomik::flash($error, $status);   
	Atomik::redirect('edit_data?id='.$update_data_id);		
}
if (isset($_POST['cancel_up'])||isset($_POST['cancel_down'])){
	Atomik::redirect('data');		
}
if (isset($_POST['add_baseline_link'])) {
	/* Insert link for PR */
	$baseline_id = isset($_POST['baseline_id']) ? $_POST['baseline_id'] : "";
	$data_id = isset($_POST['data_id']) ? $_POST['data_id'] : "";
	$description = isset($_POST['description']) ? $_POST['description'] : "";
	$res = Atomik_Db::insert('baseline_join_data',array('data_id'=>$data_id,'baseline_id'=>$baseline_id));
	$baseline = Atomik_Db::find('baselines',array('id'=>$baseline_id));

	$description = $baseline['description'];
	if($res) {
		$error = "Link with baseline {$description} successfully added !";
		$status = "success";
	}
	else {
		$error = "Link with baseline {$description} adding failed !";
		$status = "failed";
	}	
	Atomik::Flash($error,$status);
	Atomik::redirect('edit_data?id='.$data_id.'&tab=baseline',false);	
}
if (isset($_POST['submit_new'])) {
   $new_previous_id = $update_data_id;
	$new_id = $data->add_application($add_app_project,
									 &$add_app_name,
									 Data::getNextVersion($add_app_version),
									 $add_app_equipment,
									 $add_app_type,
									 stripslashes($add_app_description),
									 stripslashes($add_app_abstract),
									 $add_app_status,
									 "",
									 $add_app_location,
									 $add_app_peer_review,
									 "",
									 $add_app_author,
									 $new_previous_id,
									 $keywords,
									 $priority_id); /* Previous data */
		
	if  ($new_id == "") {
		$error = "Data adding failed !";
		$status = "failed";
	}
	else {
		$error = "Data ".$add_app_name." successfully added !";
		$status = "success";
	}
	Atomik::flash($error, $status);
	Atomik::redirect('edit_data?id='.$new_id.'&test=007',false);
}
if ((isset($_POST['submit']))||(isset($_POST['change_project']))){
	// echo "PROJECT: ".$add_app_project;
   $res = $data->update_application($add_app_project,
								 $add_app_name,
								 $add_app_version,
								 $add_app_equipment,
								 $add_app_type,
								 stripslashes($add_app_description),
								 stripslashes($add_app_abstract),
								 $add_app_status,
								 $add_app_baseline,
								 $add_app_date,
								 $add_app_location,
								 $add_app_peer_review,
								 $add_app_review_deadline,
								 $add_app_author,
								 $update_data_id,
								 $previous_data_id,
								 $keywords,
								 $priority_id);
	if ($res) {
		$error = "Update successful !";
		$status = "success";
	}
	else {
		$error = "Update failed !";
		$status = "failed";
	}
	Atomik::flash($error, $status);
	Atomik::redirect('edit_data?tab=description&id='.$update_data_id,false);	
}   
if (isset($_POST['add_pr_link'])) {
	/* Insert link for PR */
	$data_impacted_id = isset($_POST['data_impacted_id']) ? $_POST['data_impacted_id'] : "";
	$sql_query = "INSERT INTO pr_link (`data_id`, `pr_id`) VALUES('$data_impacted_id','$update_data_id')";
	$res = A('db:'.$sql_query);
	if($res) {
		$error = "Link successfully added !";
		$status = "success";
	}
	else {
		$error = "Link adding failed !";
		$status = "failed";
	}
    Atomik::Flash($error,$status);
	Atomik::redirect('edit_data');	
}

if (isset($_POST['submit_remove_baseline'])) {
	$link_id = isset($_POST['link_id']) ? $_POST['link_id'] : "";
	$data_id = isset($_POST['data_id']) ? $_POST['data_id'] : "";
	$description = isset($_POST['description']) ? $_POST['description'] : "";
	$res = Baseline::delete_baseline_link($link_id);
	if  ($res) {
		$error = "Link to baseline <b>{$description}</b> removed !";
		$status = "success";
	}
	else {
		$error = "Link to baseline <b>{$description}</b> removal failed !";
		$status = "failed";
	}
	Atomik::Flash($error,$status);
	Atomik::redirect('edit_data?id='.$data_id.'&tab=baseline');		
}
if (isset($_POST['submit_prr_location'])){
	$peer_review_highlight = isset($_POST['peer_review_highlight']) ? $_POST['peer_review_highlight'] : ""; 
	Get_Data::update_prr_location($add_app_peer_review,
									$update_data_id);
	Atomik::redirect('edit_data');									 
}
if (isset($_POST['submit_acceptance'])){
	$data_acceptance = isset($_POST['data_acceptance']) ? $_POST['data_acceptance'] : ""; 
	$res = Data::update_acceptance($update_data_id,$data_acceptance);
	if  ($res) {
		$error = "Acceptance update succeeded !";
		$status = "success";
	}
	else {
		$error = "Acceptance update failed !";
		$status = "failed";
	}
	Atomik::Flash($error,$status);
	Atomik::redirect('edit_data?id='.$update_data_id.'&tab=quality',false);	
}
else if (isset($_GET['remove_prr'])) {
	$remove_prr_id		   = isset($_GET['remove_prr_id']) ? $_GET['remove_prr_id'] : "";
	$peer_review_highlight = isset($_GET['peer_review_highlight']) ? $_GET['peer_review_highlight'] : ""; 
	mysql_query("DELETE FROM peer_review_location WHERE `peer_review_location`.`id` = '$remove_prr_id' LIMIT 1");
}
if (isset($_POST['upload_peer_review'])) {
	$upload_peer_review = $_POST['upload_peer_review'];
	$peer_review_highlight = isset($_POST['peer_review_highlight']) ? $_POST['peer_review_highlight'] : ""; 
}
else {
	$upload_peer_review = "empty";
} 
$error = "";
/* Upload document */ 
$upload_error = isset($_FILES['filename']['error']) ? $_FILES['filename']['error'] : "";
if(isset($_POST['upload_data']) AND 
   (!empty($_FILES['filename']['tmp_name'])) AND 
   (is_uploaded_file($_FILES['filename']['tmp_name'])))
{
	$maxSize=30000000;  // Only save files smaller than 30M
	$nomOrigine = $_FILES['filename']['name'];
	$elementsChemin = pathinfo($nomOrigine);
	$extensionFichier = $elementsChemin['extension'];
	$extensionsAutorisees = array("pdf","PDF",
									"doc","DOC", 
									"docm","DOCM", 
									"docx","DOCX", 
									"dot", "DOT",
									"xls", "XLS",
									"xlsx","XSLX", 
									"xlsm", 
									"rtf", 
									"ppt", 
									"pptx");
	if (!(in_array($extensionFichier, $extensionsAutorisees))) {
		$error = "Le fichier n'a pas l'extension attendue !"; 
		$upload_status = "failed";
	} else {   
		$info = array('maxSize'=>$maxSize,
						'error'=>$error,
						'upload_status'=>$upload_status,
						'location'=>"docs".DIRECTORY_SEPARATOR);				
		/* Check if the same uploaded data is not already linked to this data */
		if (Data::Check_Link_Validity($update_data_id,$extensionFichier)) {
			$server_filename = Atomik_Db::insert("data_location",array("data_id"=>$update_data_id,
																		"name"=>$extensionFichier,
																		"real_name"=>$nomOrigine));
			$update_ref = isset($_POST['update_ref'])?$_POST['update_ref']:"";
			if (($add_app_name == "")||($update_ref=="locked")){
				/* update reference with filename */
				preg_match("/(\w+)\.(\w+)/Ui",$nomOrigine,$name_wo_ext);
				if (isset($name_wo_ext[1])){
					$reference = $name_wo_ext[1];
				}
				else {
					$reference = $nomOrigine;
				}
				$result = Atomik_Db::update("bug_applications",array("application"=>$nomOrigine),
																array("id"=>$update_data_id));
			}			
			$filename = $server_filename.".".$extensionFichier;
			Data::upload($filename,&$info);
			$error = "File {$nomOrigine} uploaded with success !"; 
			$upload_status = "success";
		}
		else {
			/* replace the already existing file */
			$server_filename = Data::Get_Attachment_Id($update_data_id);
			$filename = $server_filename.".".$extensionFichier;
			$result = Atomik_Db::update("data_location",array("name"=>$extensionFichier,
																"real_name"=>$nomOrigine),
														array("id"=>$server_filename));
			if ($result){
				Data::upload($filename,&$info);
				$error = "File {$nomOrigine} replaced with success !"; 
				$upload_status = "success";
			}
			else {
				$error = "File {$nomOrigine} uploading replacement failed !"; 
				$upload_status = "failed";
			}
		}
		Atomik::Flash($error,$upload_status);
		$data->get($update_data_id);
		$first_page_img = $data->Create_First_Page(true);
		Atomik::redirect('edit_data?id='.$update_data_id.'&tab=attachment',false);
	}
}
else if ($upload_error!="") {  
	$upload_status = "failed";	 
	switch ($_FILES['filename']['error']){   
		   case 1: // UPLOAD_ERR_INI_SIZE   
			   $error = "Le fichier d&eacute;passe la limite autoris&eacute;e par le serveur (fichier php.ini) !";   
			   break;   
		   case 2: // UPLOAD_ERR_FORM_SIZE   
			   $error = "Le fichier d&eacute;passe la limite autoris&eacute;e dans le formulaire HTML !";   
			   break;   
		   case 3: // UPLOAD_ERR_PARTIAL   
			   $error = "L'envoi du fichier a &eacute;t&eacute; interrompu pendant le transfert !";   
			   break;   
		   case 4: // UPLOAD_ERR_NO_FILE   
			   $error = "Le fichier que vous avez envoy&eacute; a une taille nulle !";   
			   break;   
	}  
	Atomik::Flash($error,$upload_status);
	Atomik::redirect('edit_data&tab=attachment');	  
}  
Atomik::redirect('edit_data');
