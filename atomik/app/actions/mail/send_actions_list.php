<?php
Atomik::disableLayout();
Atomik::setView("mail/send_actions_list");
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

$env_context['aircraft_id']= Atomik::has('session/current_aircraft_id')?Atomik::get('session/current_aircraft_id'):"";
$env_context['project_id']= isset($_GET['show_project']) ? $_GET['show_project'] : (Atomik::has('session/current_project_id')?Atomik::get('session/current_project_id'):Atomik::get('session/project_id'));
$env_context['sub_project_id'] = Atomik::has('session/sub_project_id')?Atomik::get('session/sub_project_id'):"";
$env_context['review_id'] = Atomik::has('session/review_id')?Atomik::get('session/review_id'):"";
$env_context['action_status_id'] = Atomik::has('session/action_status_id')?Atomik::get('session/action_status_id'):"";
$env_context['user_id'] = Atomik::has('session/user_id')?Atomik::get('session/user_id'):"";
$env_context['assignee_id'] = Atomik::has('session/user_id')?Atomik::get('session/user_id'):"";
$env_context['criticality_id']=Atomik::has('session/severity_id')?Atomik::get('session/severity_id'):"";
$env_context['search']=Atomik::has('session/search')?Atomik::get('session/search'):"";
$env_context['order']=Atomik::has('session/order')?Atomik::get('session/order'):"";
$env_context['user_logged_id']= User::getIdUserLogged();

$actions_pie_graph = "..".DIRECTORY_SEPARATOR."result".DIRECTORY_SEPARATOR."actions_pie.png";
$actions_bar_graph = "..".DIRECTORY_SEPARATOR."result".DIRECTORY_SEPARATOR."actions_bar.png";	
/*
 * Recipient: mail to
 */

$user = new User(&$env_context);
$user->get_stat_actions (); 

/*
* Create pie raph
*/
/* count actions and display pie chart */
$action = new Action(&$env_context);
/* Get all closed actions */
$_actions['Closed'] = $action->new_count_actions("closed");
/* Get all open actions */
$_actions['Open'] = $action->new_count_actions("open");
/* Get all actions */
$nb_all_actions = $action->new_count_actions();
/* 
 * get list of person whom actions are assigned 
 */  
$db = new Db;
$list = $user->action_get_list_poster ();
    
/* amount of rows */	
$nb=count($list); 
if ($nb != 0) {
	foreach($list as $row) {
		$to[] =  $row->email;
	}
}
/* create excel file */
// var_dump($action->getReviewId());
$excel_filename = $action->exportXlsx();
// var_dump($action->getReviewId());
// $excel_filename = $action->getExportFilename();
/* create mail */
$mail = new Mail(&$env_context);
$subject = "Liste des actions ouvertes";
$mail_text = "Veuillez trouver ci-joint la liste de ".$nb_all_actions." actions qualit&eacute; (".$_actions['Open']." ouvertes, ".$_actions['Closed']." closes)";
if ($env_context['review_id'] != ""){
	$review = new Review;
	$review->get($env_context['review_id']);
	$subject .= " durant la revue ".$review->getContext();
	$mail_text .= " lev&eacute;es durant la revue ".$review->getContext();
}
$mail->setSubject(": ".$subject);
$mail->setRecipients(&$to);
if($mail->getAccess()){
	$mail->createHeader();
	$mail->createParent();
}
$html = "<div style='border:solid 1px #A9BFCB;margin:10px 10px 10px 10px;padding:5px 45px 5px 41px;background-color:#D2DEE4'>";
$html.= "<p><font size='1' color='#BBB' face='Arial'>Bonjour,<br/><br/>{$mail_text}<br/>";
$html.= "<h3 style='font-family: \"Century Gothic\",\"Trebuchet MS\",Helvetica,Arial,Geneva,sans-serif;font-size: 1.2em;line-height: 1.1;'>Synth&egrave;se</h3>";
$html.= '<table><tr>';
$html.= '<td><img src="cid:id_pie" border="0"></td>';
$html.= '</tr>';
$html.= '<tr>';
$html.= '<td><img src="cid:id_bar" border="0" width="700" height="280"></td>';	
$html.= '</tr></table>';
$html.= "<h3 style='font-family: \"Century Gothic\",\"Trebuchet MS\",Helvetica,Arial,Geneva,sans-serif;font-size: 1.2em;line-height: 1.1;'>Tableau d'Actions</h3>";
$html.= '<p style="margin-left: 80px;"><table><tr>';
$html.= '<td>Open:<img  src="cid:id_open"  width="16" height="16" border="0"></td>';
$html.= '<td>Deadline over:<img src="cid:id_deadline"  width="16" height="16" border="0"></td>';
$html.= '<td>Propose to close:<img  src="cid:id_propose"  width="16" height="16" border="0"></td>';
$html.= '<td>Close:<img src="cid:id_close"  width="16" height="16" border="0"></td></tr></table></p>';

if(!$mail->getAccess()){
	$action->setStatusLogo();
}
$html.= $action->buildActionTable();	
$html.= $html_signature_link;	
$mail->setBody($html);
/*
 *
 * Send mail
 *
 */
if($mail->getAccess()){
	/* Connected to Lotus domino database */
	/*
	 * attachment
	 */
	if ($excel_filename!=""){ 
		$mail->attach($excel_filename);
	}
	$mail->writeBody();
	/* Second child, zodiac logo */
	$dir_img_path=dirname(__FILE__)."/jpeg/";
	$child_second = $mail->create_child($dir_img_path."small_zodiacaerospace.jpg","_2_032EAAC4032EA6F0003D60EAC12579E3");
	$child_third  = $mail->create_child($dir_img_path."run.jpg","id_open");
	$child_fourth = $mail->create_child($dir_img_path."agt_update_critical.jpg","id_deadline");
	$child_fifth  = $mail->create_child($dir_img_path."agt_runit.jpg","id_propose");
	$child_sixth  = $mail->create_child($dir_img_path."agt_action_success.jpg","id_close");
	//$child_seven  = create_child(dirname(__FILE__)."/images/mastering_the_elements.jpg","id_ece_header");
	$child_img_pie  = $mail->create_child($actions_pie_graph,"id_pie");
	$child_img_bar  = $mail->create_child($actions_bar_graph,"id_bar");
	
	// $mime = $mail->email_object ->getMIMEEntity();
	// $n = 1;
	// $header = $mime->getNthHeader("Content-Type", $n);
	// $header->setHeaderVal("multipart/related");
	$mail->save();
	$html = "";
	// if($result){
		$html .= "Mail sent to Lotus domino server into Draft folder<br/>";
		$html .= "<b>Subject:</b>".$mail->getSubject()."<br/>";
		$html .= "<b>From:</b>".$mail->getFromWho()."<br/>";
		$html .= "<b>To:</b>".$mail->getRecipients()."<br/>";
	// }
	// else {
		// $html .= "Mail sending failed !<br/>";
		// $html .= "To:".$mail->getRecipients()."<br/>";
	// }
	//$session -> ConvertMime = TRUE; 
	//Release the session object
	//$session = null;
	unset($mail);
}
else {
	$from="finister@freeheberg.com";
	$sujet = $mail->getSubject();
	 $frontiere = '-----=' . md5(uniqid(mt_rand())); 

	 //----------------------------------------------- 
	 //HEADERS DU MAIL 
	 //----------------------------------------------- 
	 $mail->setRecipients(array("olivier.appere@gmail.com"));
	 
	 
	     //----------------------------------------------- 
	     //DECLARE LES VARIABLES 
	     //----------------------------------------------- 
	
	     $email_expediteur='finister@freeheberg.com'; 
	     $email_reply='finister@freeheberg.com';
	     $destinataire='olivier.appere@gmail.com';
	     $message_texte=''; 
	
	     //----------------------------------------------- 
	     //GENERE LA FRONTIERE DU MAIL ENTRE TEXTE ET HTML 
	     //----------------------------------------------- 
	
	     $frontiere = '-----=' . md5(uniqid(mt_rand())); 
	
	     //----------------------------------------------- 
	     //HEADERS DU MAIL 
	     //----------------------------------------------- 
	
	     $headers = 'From: "Olivier Appéré" <'.$email_expediteur.'>'."\n"; 
	     $headers .= 'Return-Path: <'.$email_reply.'>'."\n"; 
	     $headers .= 'MIME-Version: 1.0'."\n"; 
	     $headers .= 'Content-Type: multipart/mixed; boundary="'.$frontiere.'"'; 
	
	     //----------------------------------------------- 
	     //MESSAGE TEXTE 
	     //----------------------------------------------- 
	     $message = 'This is a multi-part message in MIME format.'."\n\n"; 
	
	     $message .= '--'.$frontiere."\n"; 
	     $message .= 'Content-Type: text/plain; charset="iso-8859-1"'."\n"; 
	     $message .= 'Content-Transfer-Encoding: 8bit'."\n\n"; 
	     $message .= $message_texte."\n\n"; 
	
	     //----------------------------------------------- 
	     //MESSAGE HTML 
	     //----------------------------------------------- 
	     $message .= '--'.$frontiere."\n"; 
	
	     $message .= 'Content-Type: text/html; charset="iso-8859-1"'."\n"; 
	     $message .= 'Content-Transfer-Encoding: 8bit'."\n\n"; 
	     $message .= $mail->getBody()."\n\n"; 
	
	     $message .= '--'.$frontiere."\n"; 
	
	     //----------------------------------------------- 
	     //IMAGES
	     //-----------------------------------------------
		 $dir_img_path=dirname(__FILE__)."/jpeg/";
		$message .= 'Content-Location: CID:somethingatelse1'."\n";
		$message .= 'Content-Type: image/jpeg name="small_zodiacaerospace.jpg"'."\n"; 
		$message .= 'Content-ID: <_2_032EAAC4032EA6F0003D60EAC12579E3>'."\n";
		$message .= 'Content-Transfer-Encoding: base64'."\n"; 
		$message .= chunk_split(base64_encode(file_get_contents($dir_img_path."small_zodiacaerospace.jpg")))."\n";
		
		$message .= '--'.$frontiere."\n"; 
		$message .= 'Content-Location: CID:somethingatelse2'."\n";
		$message .= 'Content-Type: image/jpeg name="pie.jpeg"'."\n";;
		$message .= 'Content-ID: <id_pie>'."\n";;
		$message .= 'Content-Transfer-Encoding: base64'."\n";
		$message .= chunk_split(base64_encode(file_get_contents($actions_pie_graph)))."\n";

		$message .= '--'.$frontiere."\n"; 
		$message .= 'Content-Location: CID:somethingatelse3'."\n";
		$message .= 'Content-Type: image/jpeg name="bar.jpeg"'."\n";;
		$message .= 'Content-ID: <id_bar>'."\n";;
		$message .= 'Content-Transfer-Encoding: base64'."\n";
		$message .= chunk_split(base64_encode(file_get_contents($actions_bar_graph)))."\n";	
	     //----------------------------------------------- 
	     //PIECE JOINTE 
	     //----------------------------------------------- 
		 /*
	     $message .= 'Content-Type: image/jpeg; name='.$dir_img_path.'"small_zodiacaerospace.jpg"'."\n"; 
	     $message .= 'Content-Transfer-Encoding: base64'."\n"; 
	     $message .= 'Content-Disposition:attachement; filename='.$dir_img_path.'"small_zodiacaerospace.jpg"'."\n\n"; 
	
	     $message .= chunk_split(base64_encode(file_get_contents($dir_img_path."small_zodiacaerospace.jpg")))."\n"; 
		*/
	     if(mail($destinataire,$mail->getSubject(),$message,$headers)) { 
	     	Atomik::Flash('Mail sent.','success');
	     } 
	     else { 
	     	Atomik::Flash('No mail sent.','failed');
	     }
/*		 
	 $headers = 'From: "Olivier Appéré" <'.$from.'>'."\n"; 
	 $headers .= 'Return-Path: <'.$from.'>'."\n"; 	
	htmlMailing($mail->getRecipients(),
				$mail->getSubject(),
				$mail->getBody(),
				"",
				$headers);
*/				
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
