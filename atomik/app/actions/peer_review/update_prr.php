<?php
Atomik::disableLayout();
Atomik::setView("peer_review/update_prr");
Atomik::needed("Db.class");
Atomik::needed("Data.class");
Atomik::needed("Remark.class");

$db = new Db;
$update_data_id = isset($_POST['id']) ? $_POST['id'] : ""; 
if (isset($_POST['upload_peer_review'])) {
	$upload_peer_review = $_POST['upload_peer_review'];
	$update_prr_id = isset($_POST['update_prr_id']) ? $_POST['update_prr_id'] : "";
	$peer_review_highlight = isset($_POST['peer_review_highlight']) ? $_POST['peer_review_highlight'] : ""; 
}
else {
	$upload_peer_review = "empty";
} 
$result_upload = "NOK";	
$upload_status = "failed";
if(($upload_peer_review == "yes") AND 
   (!empty($_FILES['filename']['tmp_name'])) AND 
   (is_uploaded_file($_FILES['filename']['tmp_name'])))
{
	$maxSize=30000000;  // Only save files smaller than 30M
	$nomOrigine = $_FILES['filename']['name'];
	$elementsChemin = pathinfo($nomOrigine);
	$extensionFichier = $elementsChemin['extension'];
	$extensionsAutorisees = array("pdf", "doc", "docm", "docx", "dot", "xls","xlsm","xlsx", "rtf", "ppt", "pptx");
	if (!(in_array($extensionFichier, $extensionsAutorisees))) {
		$error = "Le fichier n'a pas l'extension attendue !"; 
		$upload_status = "failed";
	} else {   
		/* Check if the same uploaded data is not already linked to this data */
		if ($update_prr_id == "") {
			$prr_id = Atomik_Db::insert("peer_review_location",array("data_id"=>$update_data_id,"ext"=>$extensionFichier,"name"=>$nomOrigine));
			if ($prr_id) {
				$error_prr = 'New input:'.$prr_id;
				$result_upload = "OK";	
			}
			else {
				//echo $sql_query."<br/>";
				$error_prr = 'DB insert error'.$update_data_id.":".$extensionFichier.":".$nomOrigine."<br/>";
			}
		}
		else {
			/* replace the already existing file */
			$prr_id = $update_prr_id;
			$result = Atomik_Db::update('peer_review_location',array('name'=>$nomOrigine,'ext'=>$extensionFichier),array('id'=>$update_prr_id));
			if ($result) {
				$error_prr = 'Old input:'.$prr_id;
				$result_upload = "OK";
			}
			else {
				echo $sql_query."<br/>";
				$error_prr = 'DB update error'.$update_data_id.":".$extensionFichier.":".$nomOrigine."<br/>";
			}			
		}
        $info = array('maxSize'=>$maxSize,
                        'error'=>$error_prr,
                        'upload_status'=>$upload_status,
                        'location'=>"docs".DIRECTORY_SEPARATOR."peer_reviews".DIRECTORY_SEPARATOR);               
        /* Check if the same uploaded data is not already linked to this data */
        // if (Data::Check_Link_Validity($update_data_id)) {
        $filename = $prr_id.".".$extensionFichier;
        $uploadName = Data::upload($filename,&$info);	
		// $prr_link = "docs/peer_reviews/".$prr_id.".".$extensionFichier;
		// $prr_link_mime = Data::Get_Mime($prr_link); 
		// $type =  $row->ext;
		// $prr_type = "unknown";
		// $uploadName = getcwd()."/../".$prr_link;
		$res = Remark::scanPeerReview($uploadName,$extensionFichier);
		Atomik_Db::update('peer_review_location',$res,array('id'=>$prr_id));
		// $res['nb_remarks']=0; 
		// $res['open_remarks']=0;
		// $res['type']=0;		
	}
}
else if ($_FILES['filename']['error']) {  
		  $upload_status = "failed";	 
          switch ($_FILES['filename_prr']['error']){   
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
}
