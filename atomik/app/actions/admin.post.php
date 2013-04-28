<?php
Atomik::needed("Db.class");

$postArray = &$_POST;
if (isset($_POST['submit'])){
	 $is_all_data_seen 	= isset($_POST['all_data_seen'])?$_POST['all_data_seen']:"";
	if	($is_all_data_seen){
		Atomik::set('session/see_all_data',"yes");
	}
	else{
		Atomik::set('session/see_all_data',"no");
	}
	Atomik::Flash('Config changed.','success');
	Atomik::redirect("admin");	
}
if (isset($_POST['submit_reset'])){
	$filename = dirname(__FILE__).DIRECTORY_SEPARATOR.
			"..".DIRECTORY_SEPARATOR.
			"..".DIRECTORY_SEPARATOR.		
			"..".DIRECTORY_SEPARATOR.				
			"..".DIRECTORY_SEPARATOR.A('db_config/log');
	$fhandle = fopen($filename, 'w+'); 
	fclose($fhandle);
	Atomik::Flash('Log reset.','success');
	Atomik::redirect("admin");
}
//print_r($postArray);	
$rule = array(
	'filename' => array('required' => true)
);

/* on nepeut pas utiliser cette fonction filter car elle supprime les balises html */
//if (($data_tmp = Atomik::filter($_POST, $rule)) === false) {
//	Atomik::flash(A('app/filters/messages'), 'error');
//	return;
//}
if((!empty($_FILES['filename']['tmp_name'])) AND 
   (is_uploaded_file($_FILES['filename']['tmp_name']))) {
	$maxSize=30000000;                            // Only save files smaller than 30M
	$uploadSize = $_FILES['filename']['size'];  // The size of our uploaded file
	$uploadType = $_FILES['filename']['type'];  // The type of the file.
	$long_filename = isset($_FILES['filename']['name']) ? $_FILES['filename']['name'] : "";
	if ($long_filename != "") {
		$filename = basename($long_filename);
		if (preg_match("#\.sql$#",$filename)) {
			$uploadName = getcwd().DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."result".DIRECTORY_SEPARATOR."uploadedFile.sql"; // Never trust the upload, make your own name
		}
		else if (preg_match("#\.gz$#",$filename)) {
			$uploadName = getcwd().DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."result".DIRECTORY_SEPARATOR."uploadedFile.sql.gz"; // Never trust the upload, make your own name
		}
		else if (preg_match("#\.zip$#",$filename)) {
			$uploadName = getcwd().DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."result".DIRECTORY_SEPARATOR."uploadedFile.sql.zip"; // Never trust the upload, make your own name
		}		
		else {
			$uploadName = "";
			Atomik::flash('Format not supported!', 'failed');
		}
		if ($uploadSize<$maxSize) {              // Make sure the file size isn't too big.
		   move_uploaded_file($_FILES['filename']['tmp_name'], $uploadName);   // save file.
		   $db = new Db("atomik");
		   $db->db_update($uploadName);
		   //echo "Database updated<br>";
		   Atomik::flash('Database updated!', 'success');
		}
		else {
			Atomik::flash('Database not updated!', 'failed');
		}
	} 
	else {
		Atomik::flash('No database selected!', 'failed');
	}
}
else if ($_FILES['filename']['error']) {   
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
	Atomik::flash($error, 'failed');		  
}  
//Atomik::redirect('admin');
