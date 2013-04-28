<?php
Atomik::disableLayout();
Atomik::setView("mail/send_data");
Atomik::needed("Db.class");
Atomik::needed("User.class");
Atomik::needed("Date.class");
Atomik::needed("Data.class");
Atomik::needed("Project.class");
Atomik::needed("Tool.class");
Atomik::needed("Action.class");
Atomik::needed("Review.class");
Atomik::needed("Mail.class");
Atomik::needed("Logbook.class");
Atomik::needed("Remark.class");
Atomik::needed("Baseline.class");
Atomik::needed("PeerReviewer.class");
include("../mail/lotus/urlfunctions.php");
include("../mail/logo.php");
include "../mail/htmlMailing.php";
$base_path = dirname(__FILE__).DIRECTORY_SEPARATOR.
						"..".DIRECTORY_SEPARATOR.
						"..".DIRECTORY_SEPARATOR.
						"..".DIRECTORY_SEPARATOR.
						"..".DIRECTORY_SEPARATOR;
$data_id = isset($_GET['id']) ? $_GET['id'] : ""; 
$filename_prr = isset($_GET['filename']) ? $_GET['filename'] : "";
$draft = isset($_GET['draft']) ? $_GET['draft'] : "";
$data = new Data;
$data->get($data_id);
if ($filename_prr != ""){
	$description = "Ce rapport de revue de pair concerne le document <b>{$data->type}</b> r&eacute;f&eacute;renc&eacute; <b>{$data->reference}</b> version <b>{$data->version}</b>.";
	$remarks = new StatRemarks;
	$remarks->setDocument($data->id);
	$bar_filename = '../result/remarks_bar.png';
	if ($remarks->amount_remarks > 0){
		$remarks->count_all_remarks();
		$remarks->drawBar($bar_filename);
		$abstract = "Le rapport contient <b>{$remarks->amount_remarks}</b> remarques dont la r&eacute;partition est la suivante:<br/>";
	}
	else{
		$abstract = "Le rapport ne contient pas de remarques<br/>";
	}

	$data->setLink("result/".$filename_prr);
	$data->small_ident = $filename_prr;
	$type_document = "le rapport de revue de pair <b>".$data->small_ident."</b>";
	$to['cc'] =  $data->getAuthorEmail();
	$html_abstract = "<h3 style='font-family: \"Century Gothic\",\"Trebuchet MS\",Helvetica,Arial,Geneva,sans-serif;font-size: 1.1em;line-height: 1.1;'>Synth&egrave;se</h3>";
	$html_abstract.= $abstract;					
}
else{
	if ($data->link!="empty"){ 
		$type_document = "le document <b>".$data->small_ident."</b>";
	}
	else{
		$type_document = 'le document <b><a href="'.$data->location.'">'.$data->small_ident.'<a></b>';
	}
	$description = $data->description;
	$abstract = $data->abstract;

	$html_abstract  = "<h3 style='font-family: \"Century Gothic\",\"Trebuchet MS\",Helvetica,Arial,Geneva,sans-serif;font-size: 1.1em;line-height: 1.1;'>R&eacute;sum&eacute;</h3>";
	$html_abstract .= '<table>';
	$html_abstract .= "<tr bgcolor='#e0e0e0'>";
	$html_abstract .= "<td valign=\"top\"><img src='cid:id_data' border='0' alt ='' title='' /></td><td><td>".$data->abstract."</td>";
	$html_abstract .= '</tr></table>';
}
$env_context['user_logged_id']=User::getIdUserLogged();
$env_context['project_id'] = isset($context['project_id']) ? $context['project_id'] : "";
$env_context['sub_project_id'] = isset($context['sub_project_id']) ? $context['sub_project_id'] : "";
$mail = new Mail(&$env_context);
$to['email'] =  User::getEmailUserLogged();
$mail->setSubject("Envoi document ".$data->small_ident);
$mail->setRecipients(&$to);

$html = '';
$html.= "<p><font size='1' color='#BBB' face='Arial'>Bonjour,<br/><br/>Veuillez trouver ci-joint {$type_document}<br/><br/>";
$html.= "<h3 style='font-family: \"Century Gothic\",\"Trebuchet MS\",Helvetica,Arial,Geneva,sans-serif;font-size: 1.1em;line-height: 1.1;'>Sujet</h3>";
$html.= '<table>';
$html.= "<tr bgcolor='#f0f0f0'>";
$html.= "<td><img src='cid:id_intro' border='0' alt ='' title='' /></td><td>".$description."</td></tr></table>";
$html.= $html_abstract;
if ($filename_prr != ""){
	$html.= '<img src="cid:id_stats" border="0" alt ="" title="" />';
}
$html.= $html_signature_link;	
$mail->setBody($html);
/*
 *
 * Send mail
 *
 */
if ($mail->getAccess()){
	$mail->createHeader();
	$mail->createParent();
	$mail->writeBody();
	$dir_img_zodiac_path=dirname(__FILE__).
						DIRECTORY_SEPARATOR.
						"..".DIRECTORY_SEPARATOR.
						"..".DIRECTORY_SEPARATOR.
						"..".DIRECTORY_SEPARATOR.
						"assets".DIRECTORY_SEPARATOR."images".DIRECTORY_SEPARATOR;
	$result = $mail->create_child($dir_img_zodiac_path.DIRECTORY_SEPARATOR."32x32".DIRECTORY_SEPARATOR."kghostview.png","id_data");
	$result = $mail->create_child($dir_img_zodiac_path.DIRECTORY_SEPARATOR."32x32".DIRECTORY_SEPARATOR."kate.png","id_intro");										
	if ($filename_prr != ""){
		$dir_stats = $base_path."result".DIRECTORY_SEPARATOR."remarks_bar.png";		
		$result = $mail->create_child($dir_stats,"id_stats");
	}								
	$result = $mail->create_child($dir_img_zodiac_path."small_zodiacaerospace.jpg","_2_032EAAC4032EA6F0003D60EAC12579E3");
										
	if ($data->link!="empty"){
		$dest = $data->smart_filename;
		if (file_exists($base_path.$data->link)){
			if ($filename_prr != ""){
				$location = $base_path."result".DIRECTORY_SEPARATOR.$filename_prr;
			}
			else {			
				copy($base_path.$data->link, $base_path."result".DIRECTORY_SEPARATOR.$data->smart_filename);
				$location = $base_path."result".DIRECTORY_SEPARATOR.$data->smart_filename;	
			}
			$mail->attach($data->link,$location);							
			if ($mail->getArchive()){
				$result = $mail->save();
			}
			else{
				if ($draft==""){
					$result = $mail->send();
				}
				else{
					$result = $mail->save();
				}
			}
			$result = true;
		}
		else {
			$result = false;
		}
	}
	else {											
		$result = $mail->save();
	}
	if($result){
		$error  = "Data {$data->small_ident} sent to Lotus Domino server !<br/>";
		$error .= "<b>From:</b>".$mail->getFromWho()."<br/>";
		$error .= "<b>To:</b>".$mail->getRecipients()."<br/>";	
		$status = "success";
	}
	else {
		$error = "Data {$data->small_ident} not sent to Lotus Domino server !";
		$status = "failed";
	}
	unset($mail);
}
else {
	$sujet = $mail->getSubject();
	$noMIME = "Si tu lis ça, c'est que tu agent de mail est trop-vieux ;)";
	echo $html;
	htmlMailing($mail->getRecipients(),
				$mail->getSubject(),
				$mail->getBody(),
				$noMIME,
				$from_email);	
	$error = "Data {$data->small_ident} not sent to Lotus Domino server !";
	$status = "failed";
}
/*
 *
 * Read mail
 *
 */
 
//Get view handle using previously received database handle.
//Note that the reserved character in the view name must be \-escaped 
/* $view = $db->getView( "(\$Inbox)" ); */

//Get first document in view using previously received view handle
/* $doc = $view->getFirstDocument(); */

//Loop until all documents in view are processed
/* while (is_object($doc)) {
	//Get handle to a field called "Subject"
	$field=$doc->GetFirstItem("Subject"); 

	//Get text value of the field
	$fieldvalue=$field->text;

	//Show the value of the field
	print "Subject: " . $fieldvalue . "<br/>";

	//Get next document in the view
	$doc = $view->getNextDocument($doc);
}
*/
