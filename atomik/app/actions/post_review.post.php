<?php
Atomik::needed('Review.class');
$postArray = &$_POST;

if (isset($postArray['cancel_up']) || isset($postArray['cancel_down'])) {
	// $context_array = isset($_POST['context'])? unserialize(urldecode(stripslashes($_POST['context']))):"";
	// $context = urlencode(serialize($context_array));
	//exit(0);
	Atomik::redirect('show_reviews');
}

$info['id'] = isset($_POST['id']) ? $_POST['id'] : "";
$info['aircraft']= Atomik::has('session/current_aircraft_id')?Atomik::get('session/current_aircraft_id'):"";
$info['project'] = isset($_POST['show_project']) ? $_POST['show_project'] : "";
$info['equipment'] = isset($_POST['show_lru']) ? $_POST['show_lru'] : "";	
$info['type'] = isset($_POST['show_type']) ? $_POST['show_type'] : "";	
$info['status'] = isset($_POST['show_status']) ? $_POST['show_status'] : 44; /* 44 is TBD */
$info['description'] = isset($_POST['add_review_description']) ? $_POST['add_review_description'] : "";	
$info['comment'] = isset($_POST['add_review_comment']) ? $_POST['add_review_comment'] : "";	
$info['date'] = isset($_POST['add_review_date']) ? $_POST['add_review_date'] : "";	
$info['date_end'] = isset($_POST['add_review_date_end']) ? $_POST['add_review_date_end'] : "";	
$info['managed_by'] = isset($_POST['add_review_managed_by']) ? $_POST['add_review_managed_by'] : "";	
$info['objective'] = isset($_POST['objective']) ? $_POST['objective'] : "";	
$info['previous_id'] = isset($_POST['previous_review_id']) ? $_POST['previous_review_id'] : "";

$review = new Review;
$status = "warning"; 
if (isset($_POST['submit'])) {
	if (($_POST['update_review']=="no")){
		/* New review */
		$info['id'] = $review->create(&$info);
		$summary = "New review created.";
		$status = 'success';
		if ($info['id']){
			$summary = "New review created.";
			$status = 'success';
		}
		else{
			$summary = "Review creation failed.";
			$status = 'failed';
		}
	}
	else {
		/* Update review */
		$result = $review->update(&$info);
		if ($result){
			$summary = "Review updated.";
			$status = 'success';
		}
		else{
			$summary = "Review not updated.";
			$status = 'failed';
		}
	}
	Atomik::Flash($summary,$status);	
	Atomik::redirect('post_review?id='.$info['id']);
}
// else{
	// Atomik::redirect('post_review?show_project='.$info['project']);
// }
/* Upload document */ 
$upload_error = isset($_FILES['filename']['error']) ? $_FILES['filename']['error'] : "";
if(isset($_POST['upload_data']) AND 
   (!empty($_FILES['filename']['tmp_name'])) AND 
   (is_uploaded_file($_FILES['filename']['tmp_name']))) {
	$review_id = isset($_POST['id']) ?  $_POST['id'] : "";
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
			$attachment_id = Atomik_Db::insert("reviews_attachment",array("data_id"=>$review_id,"ext"=>$extensionFichier,"real_name"=>$nomOrigine));
			if ($attachment_id) {
				$error_prr = "New document {$nomOrigine} attached.";
				$upload_status = "success";
			}
			else {
				//echo $sql_query."<br/>";
				$error_prr = 'DB insert error'.$review_id.":".$extensionFichier.":".$nomOrigine."<br/>";
			}
		}
		else {
			/* replace the already existing file */
			$result = Atomik_Db::update('reviews_attachment',array('real_name'=>$nomOrigine,'ext'=>$extensionFichier),array('id'=>$attachment_id));
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
                        'location'=>"docs".DIRECTORY_SEPARATOR."reviews".DIRECTORY_SEPARATOR);               
        /* Check if the same uploaded data is not already linked to this data */
        // if (Data::Check_Link_Validity($update_data_id)) {
        $filename = $attachment_id.".".$extensionFichier;
        $uploadName = Data::upload($filename,&$info);
		Atomik::Flash($error_prr,$upload_status);
		Atomik::redirect('post_review?tab=attachment&id='.$review_id,false);
	}
}
$summary = "Nothing.";
$status = 'failed';
Atomik::Flash($summary,$status);
Atomik::redirect('post_review?id='.$info['id']);
