<?php 
Atomik::disableLayout();
Atomik::setView("mail/send_minutes");
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

$error = "";
$review_id = isset($_GET['review_id']) ? $_GET['review_id'] : "";
if ($review_id != ""){
	/* Review info */
	$review = new Review();
	$review->get($review_id);
	$context[] = array();
	$context['project_id']=$review->project_id;
	$context['sub_project_id']=$review->lru_id;
	$context['status_id']=$review->status_id;
	$context['criticality_id']="";
	$context['assignee_id']="";
	$context['review_id']=$review->id;
	$context['baseline_id']="";
	$context['search']="";
	$context['order']="";
	$context['user_logged_id']=User::getIdUserLogged(); 

	$file=dirname(__FILE__).
			DIRECTORY_SEPARATOR.
			"..".DIRECTORY_SEPARATOR.
			"..".DIRECTORY_SEPARATOR.
			"..".DIRECTORY_SEPARATOR.
			"assets".DIRECTORY_SEPARATOR."images".DIRECTORY_SEPARATOR."saq_086_header.jpg";	
	$message_header = '<img src="'.Tool::base64_encode_image($file).'" alt="header" width="1124" height="390" />';
	$cookie = urlencode(str_replace('\"','"',$bug_cookie));
	$url = "http://localhost/qams/atomik/review/display_mom?review_id={$review_id}&mail=yes";
	$data = getWebPage($url,"bug_cookie=".$cookie);
	$list_minutes = $review->getMinutes();
	
	if ($list_minutes !==  false){
		foreach ($list_minutes as $row):
			$minutes_file = Tool::cleanFilename($row->reference).".htm";
			$filename = "../result/".$minutes_file;
			$windows_filename = "..".DIRECTORY_SEPARATOR."result".DIRECTORY_SEPARATOR.$minutes_file;
			break;
		endforeach;
	}
	else{
		$minutes_file = "minutes.htm";
		$filename = "../result/minutes.htm";
		$windows_filename = "..".DIRECTORY_SEPARATOR."result".DIRECTORY_SEPARATOR."minutes.htm";
	}
	$fp = fopen($windows_filename, 'w+');
	fwrite($fp, $data);
	fclose($fp);
	$backup_dir = Atomik::get('db_config/backup_dir');
	
	$copy = "copy {$windows_filename} {$backup_dir}";
    exec($copy,$retval,$code);

	$mail = new Mail(&$context);
	$context['project_id']="";
	$context['sub_project_id']="";
	$action = new Action(&$context);
	/*
	 * Message info
	 */
	if ($review->getSubject() != ""){
		$subject = $review->getSubject();
	}
	else{
		$subject = $review->managed_by." ".Tool::clean_text($review->type);
	}
	$path = "file://Spar-nas2/commun%20qualite/Appere/";
	$html = '<img src="cid:id_ece_header" border="0" alt ="" title="" />';
	$html.= "<div style='border:solid 1px #A9BFCB;margin:10px 10px 10px 10px;padding:5px 45px 5px 41px;background-color:#D2DEE4'>";
	$html.= "<p><font size='1' color='#BBB' face='Arial'>Bonjour,<br/><br/>Veuillez trouver ci-joint le compte rendu qualit&eacute; (r&eacute;f&eacute;rence:<b>";
	$html.= "<a href='".$path.$minutes_file."'>";
	$html.= "<font size='1' color='#808080' face='Arial'>".$review->reference."</font></a></b>) de la revue ";
	$html.= "<font size='2' color='#808080' face='Arial'>".$review->managed_by." ".Tool::clean_text($review->type)."</font>";
	$html.= "<br/>qui s'est d&eacute;roul&eacute;e &agrave; la date du ".$review->small_date."</font></p>";
	$html.= "</div>";
	$html.= "<h3 style='font-family: \"Century Gothic\",\"Trebuchet MS\",Helvetica,Arial,Geneva,sans-serif;font-size: 1.2em;line-height: 1.1;'>Sujet</h3>";
	$html.= "<p><font size='1' color='#BBB' face='Arial'>".$subject."</font></p>";
	$html.= "<h3 style='font-family: \"Century Gothic\",\"Trebuchet MS\",Helvetica,Arial,Geneva,sans-serif;font-size: 1.2em;line-height: 1.1;'>Objectif</h3>";
	$html.= "<div style='border:solid 1px #A9BFCB;margin:10px 10px 10px 10px;padding:5px 45px 5px 41px;background-color:#D2DEE4'>";
	if ($review->objective != "") {
		$html.= "<p><font size='1' color='#BBB' face='Arial'>".$review->objective."</font></p>";
	}
	else {
		$html.= "<p>Attention, il manque l'objectif de cette r&eacute;union<br/><font size='1' color='#BBB' face='Arial'></font></p>";
	}
	$html.= "</div>";
	$html.= "<h3 style='font-family: \"Century Gothic\",\"Trebuchet MS\",Helvetica,Arial,Geneva,sans-serif;font-size: 1.2em;line-height: 1.1;'>Actions</h3>";
	$html.= "<div style='border:solid 1px #A9BFCB;margin:10px 10px 10px 10px;padding:5px 45px 5px 41px;background-color:#D2DEE4'>";
	$html.= "<p><b>Les actions qui en r&eacute;sultent:</b></p>";
	$action->setReview($review->id);
	$html.= $action->buildActionTable();	
	$html.= "</div>";
	$html.= "<div style='border:solid 1px #A9BFCB;margin:10px 10px 10px 10px;padding:5px 45px 5px 41px;background-color:#D2DEE4'>";
	$html.= "<p><b>Les actions de la pr&eacute;c&eacute;dente revue:</b></p>";
	if ($review->previous_id != 0){
		$review->get($review->previous_id);
		$html .= "<p>Previous meeting was ".$review->managed_by." ".Tool::clean_text($review->type)." performed on ".$review->date."</p>";
		$review->get($review_id);		
		$action->setReview($review->previous_id);
		$html .= $action->buildActionTable();
	}
	else {
		$html .= "No previous meeting.";
	}
	$html.= "</div>";
	$html.= '<p style="margin-left: 80px;"><table><tr>';
	$html.= '<td>Open:<img  src="cid:id_open"  width="16" height="16" border="0"></td>';
	$html.= '<td>Deadline over:<img src="cid:id_deadline"  width="16" height="16" border="0"></td>';
	$html.= '<td>Propose to close:<img  src="cid:id_propose"  width="16" height="16" border="0"></td>';
	$html.= '<td>Close:<img src="cid:id_close"  width="16" height="16" border="0"></td></tr></table></p>';		
	$html.= $html_signature_link;	
	/*
	 *
	 * COM
	 *
	 */ 
	$from =  $mail->getFromWho();
	/*
	 * Recipient: mail to
	 */
	//$to = ""; 
	//$cc = ""; 
	if ($review->attendees != null){
		foreach( $review->attendees as $id => $users ) {
			$to[$id] =  $users['email'];
		}
	}
	else {
		$error .= "<p>Warning, no attendees at the meeting !</p>";
		$to['email'] =  $mail->getFromWho();
	}
	if ($review->person_copy != null){
		foreach( $review->person_copy as $id => $users ) {
			$cc[$id] =  $users['email']; 
		}
	}	
	/* create mail */
	$mail->setSubject($review->project." ".$review->lru." Compte de rendu 'Assurance Processus' de la revue ".Tool::clean_text($review->type)." du ".$review->small_date);
	$error .= $mail->setRecipients(&$to);
	$mail->setCopy(&$cc);
	$mail->setFrom($from);
	$mail->setBody($html);

	/*
	 *
	 * Send mail
	 *
	 */
	if($mail->getAccess()){
		$mail->createHeader();
		$mail->createParent();
		$mail->writeBody();	

		/* attachment */
		 if (User::getIdUserLogged() == 1) {/* Only available for Olivier Appéré so far */
			$mail->attach("test","C:/xampplite/htdocs/qams/mail/".$filename);
		}

		$dir_img_path=dirname(__FILE__)."/jpeg/";
		$child_second = $mail->create_child($dir_img_path."small_zodiacaerospace.jpg","_2_032EAAC4032EA6F0003D60EAC12579E3");
		$child_third  = $mail->create_child($dir_img_path."run.jpg","id_open");
		$child_fourth = $mail->create_child($dir_img_path."agt_update_critical.jpg","id_deadline");
		$child_fifth  = $mail->create_child($dir_img_path."agt_runit.jpg","id_propose");
		$child_sixth  = $mail->create_child($dir_img_path."agt_action_success.jpg","id_close");
		$child_seven  = $mail->create_child($dir_img_path."minutes_header.jpg","id_ece_header");

		//$email_object ->ReplaceItemValue("BlindCopyTo",$ccc);

		/* save document in lotus */
		/* 	Syntax

		flag = doc->Save( force, createResponse [, markRead ] )

		Parameters

		force

		  Boolean. If True, the document is saved even if someone else edits 
		  and saves the document while the script is running. 
		  The last version of the document that was saved wins; the earlier version is discarded.

		  If False, and someone else edits the document while the script is running, 
		  the createResponse argument determines what happens.

		createResponse

		  Boolean. If True, the current document becomes a response to the original document 
		  (this is what the replicator does when there's a replication conflict). 
		  If False, the save is canceled. 
		  If the force parameter is True, the createResponse parameter has no effect.

		markRead

		  Boolean. If True, the document is marked as read. 
		  If False (default), the document is not marked as read.

		Return value

		* True indicates that the document was successfully saved
		* False indicates that the document was not saved 
		
		*/
		// $mime = $email_object ->getMIMEEntity();
		// $n = 1;
		// $header = $mime->getNthHeader("Content-Type", $n);
		// $header->setHeaderVal("multipart/related");
		$draft="yes";
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
		if($result){
			$error .= "<p>Mail sent to Lotus domino server into Draft folder<br/>";
			$status = "success";		
		}
		else {
			$error .= "<p>Mail sending to Lotus domino server into Draft folder failed !<br/>";

			$status = "failed";
		}
		$error .= "<b>Subject:</b>".$mail->getSubject()."<br/>";
		$error .= "<b>From:</b>".$mail->getFromWho()."<br/>";
		$error .= "<b>To:</b>".$mail->getRecipients()."<br/>";
		$error .= "<b>Copy:</b>".$mail->getCopy()."<br/></p>";
		unset($mail);
	}
	else {
		$error = "Lotus databse access failed.";
		$status = "failed";
	}
}
else{
	$error = "Review not identified.";
	$status = "failed";
}
