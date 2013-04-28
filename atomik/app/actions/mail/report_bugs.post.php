<?php
Atomik::needed("Mail.class");
Atomik::needed("Remark.class");
include("../mail/lotus/urlfunctions.php");
include("../mail/logo.php");
include "../mail/htmlMailing.php";

if(isset($_POST['submit'])){
	/**
	 * Remark
	 */
	$remark = new Remark;
	$info['poster_id']  	= isset($_POST['reporter_id']) ?  $_POST['reporter_id'] : "";
	$info['description']    = isset($_POST['report']) ?  $_POST['report'] : "";
	$info['application']  	= 1310;		
	$new_remark_id = $remark->insert(&$info);		
	$remark->set($new_remark_id);
	// if($new_remark_id !== false){
		// Atomik::flash("Remark {$new_remark_id} has been successfully added by ".User::getNameUserLogged()." at ".date('H:i:s')." on ".date('Y/m/d'),"success");
	// }
	// else {
		// Atomik::flash("Remark input failed.","failed");
	// }
	$flash_success_message = "Comment logged into remark ID {$new_remark_id} and mail sent by ".User::getNameUserLogged()." at ".date('H:i:s')." on ".date('D j M y');
	/**
	 * Mail 
	 */
	$mail = new Mail();
	$from[] = User::getEmailUserLogged();
	$to[] = User::getAdminEmail();
	$reporter_name = User::getName($_POST['reporter_id']);
	$mail->setSubject("Rapport de bug");
	$mail->setRecipients(&$to);
	$mail->setCopy(&$from);
	/*
	 * Send mail
	 */
	$html = '<img src="cid:id_bug" border="0" alt ="" title="" />';
	$html.= "<p><font size='1' color='#BBB' face='Arial'>Rapport de bug n° {$new_remark_id} de {$reporter_name}";
	$html.= "{$_POST['report']}";
	$dir_img_path=dirname(__FILE__)."/jpeg/";
	if($mail->getAccess()){
		$mail->createHeader();
		$mail->createParent();

		$html.= $html_signature_link;	
		$mail->setBody($html);		
		/* Connected to Lotus domino database */
		/*
		 * attachment
		 */
		$mail->writeBody();
		/* Second child, zodiac logo */
		
		$child_second = $mail->create_child($dir_img_path."small_zodiacaerospace.jpg","_2_032EAAC4032EA6F0003D60EAC12579E3");
		$child_third  = $mail->create_child($dir_img_path."bug.jpeg","id_bug");
		
		// $mime = $mail->email_object ->getMIMEEntity();
		// $n = 1;
		// $header = $mime->getNthHeader("Content-Type", $n);
		// $header->setHeaderVal("multipart/related");
		$mail->send();
		$html = "";
		$html .= "Mail sent to Lotus domino server.<br/>";
		$html .= "<b>Subject:</b>".$mail->getSubject()."<br/>";
		$html .= "<b>From:</b>".$mail->getFromWho()."<br/>";
		$html .= "<b>To:</b>".$mail->getRecipients()."<br/>";

		//$session -> ConvertMime = TRUE; 
		//Release the session object
		//$session = null;
		unset($mail);
		Atomik::Flash($flash_success_message,'success');
		Atomik::redirect('home');
	}
	else{
	     //----------------------------------------------- 
	     //DECLARE LES VARIABLES 
	     //----------------------------------------------- 
	
	     $email_expediteur='finister@freeheberg.com'; 
	     $email_reply='finister@freeheberg.com';
	     $destinataire='olivier.appere@gmail.com';
	     $message_texte=''; 
	
	     $message_html=$html;
		 $html.= $html_signature_link;

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
	     $message .= $html."\n\n"; 
	
	     $message .= '--'.$frontiere."\n"; 
	
	     //----------------------------------------------- 
	     //IMAGES
	     //-----------------------------------------------
		$message .= 'Content-Location: CID:somethingatelse1'."\n";
		$message .= 'Content-Type: image/jpeg name="small_zodiacaerospace.jpg"'."\n"; 
		$message .= 'Content-ID: <_2_032EAAC4032EA6F0003D60EAC12579E3>'."\n";
		$message .= 'Content-Transfer-Encoding: base64'."\n"; 
		$message .= chunk_split(base64_encode(file_get_contents($dir_img_path."small_zodiacaerospace.jpg")))."\n";
		$message .= '--'.$frontiere."\n"; 
		$message .= 'Content-Location: CID:somethingatelse2'."\n";
		$message .= 'Content-Type: image/jpeg name="bug.jpeg"'."\n";;
		$message .= 'Content-ID: <id_bug>'."\n";;
		$message .= 'Content-Transfer-Encoding: base64'."\n";
		$message .= chunk_split(base64_encode(file_get_contents($dir_img_path."bug.jpeg")))."\n";		
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
	     	Atomik::Flash($flash_success_message,'success');
	     } 
	     else { 
	     	Atomik::Flash('No mail sent.','failed');
	     }
		Atomik::redirect('home');		
	}
}
Atomik::redirect('home');
