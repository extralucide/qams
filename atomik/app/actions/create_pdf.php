<?php
Atomik::noRender();
Atomik::disableLayout();
include "../includes/config.php"; 
include "../includes/cookie.php";
include("../mail/lotus/urlfunctions.php");
$cookie = urlencode(str_replace('\"','"',$bug_cookie));
$review_id = 213;
//echo "bug_cookie=".$cookie."<br/>";
//$htmldata = getWebPage("http://localhost/qams/review/display_mom.php?review_id=".$review_id."&mail=yes","bug_cookie=".$cookie);
$review =array("date"=>"",
				"place"=>"",
				"ref"=>"",
				"subject"=>"",
				"author"=>"",
				"office"=>"",
				"phone"=>"",
				"mail"=>"");
$htmldata = <<<EOD
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=7">
<link rel="stylesheet" type="text/css" href="assets/css/main.css" />

</head>
<body>	
<div id="saq_086_header"></div>
<div class="nice_square" style="background-color:#FFF;width:1100px;height:200px">
<div class="saq_intro">
<div class="saq_intro_split">
EOD;
$htmldata .= "<p><b>Date of the meeting/de la r&eacute;union: </b>".$review['date']."</p>";
$htmldata .= "<p><b>Place/lieu:</b> ".$review['place']."</p>";
$htmldata .= "<p><b>Ref.: </b>".$review['ref']."</p>";
$htmldata .= "<p><b>Subject/Object:</b>".$review['subject']."</p>";
$htmldata .= "</div>";

$htmldata .= "<div class='saq_intro_split'>";
$htmldata .= "<p><b>From/De: </b>".$review['author']."</p>";
$htmldata .= "<p><b>Service: </b>".$review['office']."</p>";
$htmldata .= "<p><b>Tel: </b>".$review['phone']."</p>";
$htmldata .= "<p><b>E-mail:</b>".$review['mail']."</p>";	
$htmldata .= "</div>";
$htmldata .= "</div>";
$htmldata .= '<div class="spacer" style="float:none"></div>';
$htmldata .= "</div>";
$htmldata .= "</div>";
$htmldata .= "</body>";
echo $htmldata;
/*
<style type="text/css">
@import url('../atomik/assets/css/main.css');
div#saq_086_header {
	height:390px;
	background-color:#FFFFFF;
	background-image: url('../atomik/assets/images/saq_086_header.jpg');
	background-repeat: no-repeat;
}
</style>
*/
require_once("../dompdf/dompdf_config.inc.php");
$dompdf = new DOMPDF();	
$dompdf->set_paper("legal", "landscape");
/* $htmldata = "minutes.htm"; */
$dompdf->load_html($htmldata); 
$dompdf->render(); 
$pdfdata = $dompdf->output();
$monfichier = fopen("test.pdf", 'w');
fputs($monfichier, $pdfdata."\n");
fclose($monfichier);
