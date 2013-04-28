<?php
Atomik::needed("Tool.class");
Atomik::needed("Data.class");

if (isset($_POST['show_company'])) {
    Atomik::set('session/company_id',$_POST['show_company']);
	Atomik::redirect('edit_aircraft');
}
$user_id = isset($_POST['user_id']) ? $_POST['user_id'] : ""; 
$upload_error = isset($_FILES['filename']['error']) ? $_FILES['filename']['error'] : "";
if(isset($_POST['upload_data']) AND 
   (!empty($_FILES['filename']['tmp_name'])) AND 
   (is_uploaded_file($_FILES['filename']['tmp_name'])))
{
	$maxSize=30000000;  // Only save files smaller than 30M
	$nomOrigine = $_FILES['filename']['name'];
	$elementsChemin = pathinfo($nomOrigine);
	$extensionFichier = $elementsChemin['extension'];
	$extensionsAutorisees = array("png","jpg","jpeg");
	$error = "";
	$upload_status = "";
	if (!(in_array($extensionFichier, $extensionsAutorisees))) {
		$error = "Le fichier n'a pas l'extension attendue !"; 
		$upload_status = "failed";
	} else {   
		$info = array('maxSize'=>$maxSize,
						'error'=>$error,
						'upload_status'=>$upload_status,
						'location'=>"atomik".DIRECTORY_SEPARATOR."assets".DIRECTORY_SEPARATOR."images".DIRECTORY_SEPARATOR."aircrafts".DIRECTORY_SEPARATOR);				
		/* Check if the same uploaded data is not already linked to this data */
        $filename = $user_id.".".$extensionFichier;
        Data::upload($filename,&$info);
        Atomik_Db::update('aircrafts',array('img_ext'=>$extensionFichier),array('id'=>$user_id));
        $error = "File {$filename} uploaded with success !"; 
        $upload_status = "success";
        /* Create thumbnail */
        $dest_filename =  $user_id."_tb.".$extensionFichier;
        $new_w=200;
        $new_h=140;
        $path = dirname(__FILE__).DIRECTORY_SEPARATOR.
                "..".DIRECTORY_SEPARATOR.
                "..".DIRECTORY_SEPARATOR.
                "..".DIRECTORY_SEPARATOR.$info['location'];
        Tool::createthumb($path.$filename,$path.$dest_filename,$new_w,$new_h);     
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
}  
Atomik::Flash($error,$upload_status);
Atomik::redirect('edit_aircraft');
