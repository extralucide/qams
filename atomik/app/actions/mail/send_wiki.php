<?php 
Atomik::disableLayout();
Atomik::setView("mail/send_minutes");
Atomik::needed("User.class");
Atomik::needed("Date.class");
Atomik::needed("Tool.class");
Atomik::needed("Mail.class");

$error = "";
$wiki_id = isset($_GET['id']) ? $_GET['id'] : "";
if ($wiki_id != ""){
	/* Article info */
	$row = Atomik_Db::find('spip_articles', array('id_article' => A('request/id')));
	$context[] = array('user_logged_id'=>User::getIdUserLogged());
	$mail = new Mail(&$context);
	/*
	 * Message info
	 */
$html =  <<<____CSS
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US" xml:lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
<style type="text/css">
<!--
blockquote { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 12px; font-style: normal; color: #798F9B; background-color:#BBB; border:solid 1px}
-->
</style>
</head>
<body>
____CSS;
	$path = "file://Spar-nas2/commun%20qualite/Appere/";
	$html.= '<table width="800">';
	$html.= "<tr bgcolor='#F0F0F0'>";
	$html.= "<td><img src='cid:id_training_small' border='0' alt ='' title='' /></td>";	
	$html.= "<td>Article \"<i>".$row['titre']."</i>\" published on <strong>".Date::convert_date($row['date_modif'])."</strong> by ".User::getName($row['chapo'])."</td></tr></table>";
	$html.= '<table width="800"><tr bgcolor="#DDD"><td>';
	$html.= $row['texte'];
	$html.= "</td></tr></table>";
	$html.= Mail::html_signature_link;
	$html.= "</body></html>";
	/*
	 * COM
	 */ 
	$from =  $mail->getFromWho();
	/*
	 * Recipient: mail to
	 */
	$to = ""; 
	$cc = ""; 
	/* create mail */
	$mail->setSubject($row['titre']);
	$error .= $mail->setRecipients(&$to);
	$mail->setCopy(&$cc);
	$mail->setFrom($from);
	$mail->setBody($html);
	/*
	 * Send mail
	 */
	if($mail->getAccess()){
		$mail->createHeader();
		$mail->createParent();
		$mail->writeBody();	

		$dir_img_path=dirname(__FILE__)."/jpeg/";
		$result = $mail->create_child($dir_img_path."small_zodiacaerospace.jpg","_2_032EAAC4032EA6F0003D60EAC12579E3");
		$result = $mail->create_child($dir_img_path."training_small.jpg","id_training_small");

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
		/*$error .= "<b>Subject:</b>".$mail->getSubject()."<br/>";
		$error .= "<b>From:</b>".$mail->getFromWho()."<br/>";
		$error .= "<b>To:</b>".$mail->getRecipients()."<br/>";
		$error .= "<b>Copy:</b>".$mail->getCopy()."<br/></p>";*/
		unset($mail);
	}
	else {
		$error = "Lotus databse access failed.";
		$status = "failed";
	}
}
else{
	$error = "Article not identified.";
	$status = "failed";
}
