<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US"
	xml:lang="en">
<head>
<!--
    Designed by CompanyName
    Base template (without user's data) checked by http://validator.w3.org : "This page is valid XHTML 1.0 Transitional"
    -->
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
<!--  <link rel="stylesheet" href="style.css" type="text/css" media="screen" /> -->
<link rel="stylesheet" href="style_new.css" type="text/css" media="screen" />
<link rel="stylesheet" href="form.css" type="text/css" media="screen" />
<link rel="stylesheet" type="text/css" href="atomik/assets/css/main.css" />
<link href="boites/css/customMsgBox.css" rel="stylesheet" media="all" type="text/css" />
<script type="text/javascript" src="boites/js/prototype/prototype.js"></script>
<script type="text/javascript" src="boites/js/scriptaculous/scriptaculous.js"></script>
<script type="text/javascript" src="boites/js/customMsgBox.js"></script>
<!--[if IE 6]><link rel="stylesheet" href="style.ie6.css" type="text/css" media="screen" /><![endif]-->
<!--[if IE 7]><link rel="stylesheet" href="style.ie7.css" type="text/css" media="screen" /><![endif]-->
<script type="text/javascript" src="script.js"></script>
<script type="text/javascript" src="includes/JSfunctions.js"></script>
<?php include "menu_generic.php";
	  include "inc/Data.class.php";
	  include "inc/Action.class.php";
	  include "inc/Remark.class.php";
	  include "inc/Review.class.php";
	  include "inc/Baseline.class.php";
	  include "inc/PeerReviewer.class.php";
	  include "inc/Logbook.class.php";
?>
</head>
<body>
<div id="content">
<div style="height:20px;"></div>  
<div style="padding:20px;">
<h3> Logbook generation report </h3>
<?php
/**
 * Export of QA logbook in openXML (word 2007) format
 *
 * Written by O.Appere
 *
 */
require_once 'word/PHPWord/PHPWord.php';
$firstname = $userLogFname;
$lastname = $userLogLname;
$head_office = "Quality Department";
$phone_number = "0156061104";
$fax_number = "";
$memo_subject = "QA logbook";

$today_date = date("d").' '.date("F").' '.date("Y");

$PHPWord = new PHPWord();
/*
 * Document style definition
 */
// Add title styles
$PHPWord->addTitleStyle(1, array('size'=>20, 'color'=>'333333', 'bold'=>true));
$PHPWord->addTitleStyle(2, array('size'=>16, 'color'=>'666666'));
$PHPWord->addFontStyle('rStyle', array('bold'=>true, 'italic'=>true, 'size'=>16));
$PHPWord->addParagraphStyle('pStyle', array('align'=>'center', 'spaceAfter'=>4000, 'spaceBefore'=>4000));
// Define the TOC font style
$TOCfontStyle = array('spaceAfter'=>60, 'size'=>12);

// Define table style arrays
$styleTable = array('borderSize'=>6, 'borderColor'=>'666666', 'cellMargin'=>40,'size'=>8,'bold'=>false);
$styleFirstRow = array('borderBottomSize'=>18, 'borderBottomColor'=>'AAAAAA', 'bgColor'=>'999999');
$styleSigTable = array('borderSize'=>6, 
						'borderColor'=>'000000',
						'name'=>'Verdana',
						'size'=>10,
						'bold'=>false,
						'cellMarginTop'=>80,
						'cellMarginLeft'=>80,
						'cellMarginRight'=>80,
						'cellMarginBottom'=>80);

// Define cell style arrays
$styleCell = array('valign'=>'center');
$styleCellGrey = array('valign'=>'center','bgColor'=>'888888');
$styleCellBTLR = array('valign'=>'center', 'textDirection'=>PHPWord_Style_Cell::TEXT_DIR_BTLR);

// Define font style for first row
$fontStyle = array('bold'=>true, 'align'=>'center','name'=>'Verdana','size'=>8,'bold'=>false);
$fontStyleGreen = array('bold'=>true, 'align'=>'center', 'color'=>'green');
$fontStyleOrange = array('bold'=>true, 'align'=>'center', 'color'=>'yellow');
$fontStyleRed = array('bold'=>true, 'align'=>'center', 'color'=>'red');
$fontStyleGrey = array('bold'=>true, 'align'=>'center', 'color'=>'lightGray');
// Add table style
$PHPWord->addTableStyle('myOwnTableStyle', $styleTable, $styleFirstRow);
$PHPWord->addTableStyle('SigTableStyle', $styleSigTable);

$img_status_style = array('width'=>32, 'height'=>32, 'align'=>'center');

// New portrait section
$section_first_page = $PHPWord->createSection(array('orientation'=>'portrait',
				'marginLeft' => 400,
			    'marginRight' => 400,
			    'marginTop' => 100,
			    'marginBottom' => 100));
/* Create logbook */
$logbook = new Logbook();
// /* fill header of the document*/
$properties = $PHPWord->getProperties();
$properties->setCreator($logbook->author); 
$properties->setCompany('ECE Zodiacaerospace');
$properties->setTitle($logbook->title);
$properties->setDescription('QA logbook'); 
$properties->setCategory('');
$properties->setLastModifiedBy($logbook->author);
$properties->setCreated( mktime(0, 0, 0, 3, 12, 2010) );
$properties->setModified( mktime(0, 0, 0, 3, 14, 2010) );
$properties->setSubject('QA logbook'); 
$properties->setKeywords('QA');
/*
 * Header first page
 */
$header_first_page = $section_first_page->createHeader();
$table = $header_first_page->addTable();
$table->addRow();
$table->addCell(3000,array('valign'=>'bottom'))->addImage('images/ece logo.jpg', array('width'=>76, 'height'=>32, 'align'=>'left'));
$table->addCell(8000,array('valign'=>'center',))->addText2($logbook->title,array('bold'=>true),array('align'=>'center'));
$table->addCell(3000)->addImage('images/small_zodiacaerospace.jpeg', array('width'=>147, 'height'=>50, 'align'=>'right'));
$table->addRow();
$table->addCell(4500)->addText('Reference: '.$logbook->ref);
$table->addCell(500)->addText('Issue: '.$logbook->issue);
$table->addCell(4500)->addText('');
/*
 * Footer first page
 */
$footer_first_page = $section_first_page->createFooter();
$table = $footer_first_page->addTable();
$table->addRow();
$table->addCell(15000)->addText('This document contains confidential information that is the property of ECE. Acceptance of this document by its addressee implies the latter’s acknowledgment of the confidential nature of its contents and commitment not to use it in any way whatsoever, nor to disclose its content, communicate it to third parties or reproduce it without the prior authorization of ECE.',null,array('align'=>'justify'));
$table->addRow();
$table->addCell(15000)->addText('');
$table->addRow();
$table->addCell(15000)->addText2('ECE – 129, Boulevard DAVOUT – CS 82012 – 75990 Paris Cedex 20 – France',null,array('align'=>'center'));
$table->addRow();
$table->addCell(15000)->addText2('Tel.: +33 1 56 06 10 00       Fax: +33 1 56 06 10 10',null,array('align'=>'center'));
$table->addRow();
$table->addCell(15000)->addText2('SAQ100_H',null,array('align'=>'right'));

/*
 * First page
 */
$section_first_page->addText($logbook->title, 'rStyle', 'pStyle');
$section_first_page->addTextBreak();
/**
 *  Create the table of SIGNATURES
 */
// $sectionStyle = array('orientation' => 'portrait',
			    // 'marginLeft' => 3000,
			    // 'marginRight' => 3000,
			    // 'marginTop' => 900,
			    // 'marginBottom' => 900);
// $section_table_sig = $PHPWord->createSection(sectionStyle);

// Add table
$table = $section_first_page->addTable('SigTableStyle');
$cell_witdh=array(300,3000,1000,1000,1000);
$header = array("","Function", "Name", "Visa", "Date" );
// Add row
$table->addRow(300);
$index=0;
foreach ($header as $value) {
    $table->addCell($cell_witdh[$index++], $styleCell)->addText($value, $fontStyle);
}
$table->addRow(300);
$index=0;
$data = array("Writer",$board." Sw/Hw Quality Assurance","{$logbook->author}", "", "{$today_date}");
foreach ($data as $value) {
    $table->addCell($cell_witdh[$index++], $styleCell)->addText($value, $fontStyle);
}
$table->addRow(300);
$index=0;
$data = array("Validation",$board." Process Assurance Nominee","", "", "");
foreach ($data as $value) {
    $table->addCell($cell_witdh[$index++], $styleCell)->addText($value, $fontStyle);
}
$table->addRow(300);
$index=0;
$data = array("Approval",$board." Software Project Manager","", "", "");
foreach ($data as $value) {
    $table->addCell($cell_witdh[$index++], $styleCell)->addText($value, $fontStyle);
}
$data = array("Approval","","", "", "");

//$section_first_page->addPageBreak();
$section_second_page = $PHPWord->createSection(array('orientation'=>'portrait',
				'marginLeft' => 400,
			    'marginRight' => 400,
			    'marginTop' => 100,
			    'marginBottom' => 100));

/*
 * Header second page
 */
$header_second_page = $section_second_page->createHeader();
$table_second_page = $header_second_page->addTable();
$table_second_page->addRow();
$table_second_page->addCell(3000,array('valign'=>'bottom'))->addImage('images/ece logo.jpg', array('width'=>76, 'height'=>32, 'align'=>'left'));
$table_second_page->addCell(8000,array('valign'=>'center'))->addText2($logbook->title,array('bold'=>true),array('align'=>'center'));
$table_second_page->addCell(3000)->addImage('images/small_zodiacaerospace.jpeg', array('width'=>147, 'height'=>50, 'align'=>'right'));
$table_second_page->addRow();
$table_second_page->addCell(4500)->addText('Reference: '.$logbook->ref);
$table_second_page->addCell(500)->addText('Issue: '.$logbook->issue);
$table_second_page->addCell(4500)->addText('');
/*
 * Footer second page
 */
$footer_second_page = $section_second_page->createFooter();
$table_second_page = $footer_second_page->addTable();
$table_second_page->addRow();
$table_second_page->addCell(15000)->addText('THIS DOCUMENT AND THE INFORMATION CONTAINED THEREIN ARE ECE’S EXCLUSIVE PROPERTY. ANY COPY AND/OR DISCLOSURE ARE SUBJECT TO ECE’S PRIOR WRITTEN CONSENT.	SAQ100_H',array('size'=>8));
$footer_second_page->addPreserveText('Page {PAGE} of {NUMPAGES}.', array('align'=>'right'));

/*
 * Table of Content
 */
$section_second_page->addText('Table Of Content', 'rStyle', 'pStyle');
$PHPWord->addParagraphStyle('TOCStyle', array('align'=>'center', 'spaceAfter'=>100));
$section_second_page->addTOC('TOCStyle');
$section_second_page->addPageBreak();
/*
 * Legend
 */
$section_legend = $PHPWord->createSection(array('orientation'=>'portrait',
				'marginLeft' => 400,
			    'marginRight' => 400,
			    'marginTop' => 100,
			    'marginBottom' => 100));
/*
 * Header third page
 */
$header_third_page = $section_legend->createHeader();
$table_third_page = $header_third_page->addTable();
$table_third_page->addRow();
$table_third_page->addCell(3000,array('valign'=>'bottom'))->addImage('images/ece logo.jpg', array('width'=>76, 'height'=>32, 'align'=>'left'));
$table_third_page->addCell(8000,array('valign'=>'center'))->addText2($logbook->title,array('bold'=>true),array('align'=>'center'));
$table_third_page->addCell(3000)->addImage('images/small_zodiacaerospace.jpeg', array('width'=>147, 'height'=>50, 'align'=>'right'));
$table_third_page->addRow();
$table_third_page->addCell(4500)->addText('Reference: '.$logbook->ref);
$table_third_page->addCell(500)->addText('Issue: '.$logbook->issue);
$table_third_page->addCell(4500)->addText('');
/*
 * Footer third page
 */
$footer_third_page = $section_legend->createFooter();
$table_third_page = $footer_third_page->addTable();
$table_third_page->addRow();
$table_third_page->addCell(15000)->addText('THIS DOCUMENT AND THE INFORMATION CONTAINED THEREIN ARE ECE’S EXCLUSIVE PROPERTY. ANY COPY AND/OR DISCLOSURE ARE SUBJECT TO ECE’S PRIOR WRITTEN CONSENT.	SAQ100_H',array('size'=>8));
$footer_third_page->addPreserveText('Page {PAGE} of {NUMPAGES}.', array('align'=>'right'));

$section_legend->addTitle('Terminology',1);
$section_legend->addTextBreak();
// Add table
$table = $section_legend->addTable('SigTableStyle');
$cell_witdh=array(300,3000);
$header = array("Icon","Signification");
// Add row
$table->addRow(300);
$index=0;
foreach ($header as $value) {
    $table->addCell($cell_witdh[$index++], $styleCell)->addText($value, $fontStyle);
}
$data = array("Document signed" => "images/64x64/homme-cravate.png",
			  "Document validated" => "images/64x64/All software is current.png",
			  "Document validation pending" => "images/64x64/lists.png",
			  "Document not validated" => "images/64x64/kghostview.png",
			  "Document not published" => "images/64x64/kedit.png",
			  "Action closed" => "images/32x32/agt_action_success.png",
			  "Action opened" => "images/32x32/agt_update_critical.png");
foreach ($data as $icon_signification => $img_status) {
	$table->addRow(300);
	$table->addCell($cell_witdh[0], $styleCell)->addImage($img_status,$img_status_style);
	$table->addCell($cell_witdh[1], $styleCell)->addText($icon_signification, $fontStyle);
}

/*******************
 *
 *
 * Life cycle data
 *
 *
 *******************/
$section_landscape = $PHPWord->createSection(array('orientation'=>'landscape',
				'marginLeft' => 400,
			    'marginRight' => 400,
			    'marginTop' => 100,
			    'marginBottom' => 100));
/*
 * Header landscape page
 */
$header_landscape_page = $section_landscape->createHeader();
$table_landscape_page = $header_landscape_page->addTable();
$table_landscape_page->addRow();
$table_landscape_page->addCell(3000,array('valign'=>'bottom'))->addImage('images/ece logo.jpg', array('width'=>76, 'height'=>32, 'align'=>'left'));
$table_landscape_page->addCell(8000,array('valign'=>'center'))->addText2($logbook->title,array('bold'=>true),array('align'=>'center'));
$table_landscape_page->addCell(3000)->addImage('images/small_zodiacaerospace.jpeg', array('width'=>147, 'height'=>50, 'align'=>'right'));
$table_landscape_page->addRow();
$table_landscape_page->addCell(4500)->addText('Reference: '.$logbook->ref);
$table_landscape_page->addCell(500)->addText('Issue: '.$logbook->issue);
$table_landscape_page->addCell(4500)->addText('');
/*
 * Footer landscape page
 */
$footer_landscape_page = $section_landscape->createFooter();
$table_landscape_page = $footer_landscape_page->addTable();
$table_landscape_page->addRow();
$table_landscape_page->addCell(15000)->addText('THIS DOCUMENT AND THE INFORMATION CONTAINED THEREIN ARE ECE’S EXCLUSIVE PROPERTY. ANY COPY AND/OR DISCLOSURE ARE SUBJECT TO ECE’S PRIOR WRITTEN CONSENT.	SAQ100_H',array('size'=>8));
$footer_landscape_page->addPreserveText('Page {PAGE} of {NUMPAGES}.', array('align'=>'right'));			
$section_landscape->addTitle('Life cycle data',1);
$section_landscape->addTextBreak();

/*
 * Data table
 */
$data = array();
$header = array("Reference", "Issue", "Type","Author" ,"Description","Released","Status" );
$w=array(2000,100,200,500,7500,2000,200);
// Add table
$table = $section_landscape->addTable('myOwnTableStyle');
// Add row
$table->addRow(600);
for($i=0;$i<count($header);$i++) {
	$table->addCell($w[$i], $styleCell)->addText($header[$i], $fontStyle);
}
$show_data_type = " AND status != '40' "; /* do not display obsolete data */
$order = "'name'";
if ($show_baseline != 0) {
	$show_proj_save = $show_proj;
	$show_equip_save = $show_equip;
	$show_proj = "";
	$show_equip = "";
	//echo "baseline: ".$show_baseline;
	$sql_list_data = sort_data();
	$show_proj = $show_proj_save;
	$show_equip = $show_equip_save;
}
else {
	$show_data_baseline = "";
	$sql_list_data = sort_data();
}
//echo $sql_list_data;
$result = do_query ($sql_list_data);
while($row = mysql_fetch_array($result)) {
	$document = new Data($row);
	/* correction accentuation */
    $description = utf8_decode($document->description);
    $data = array ($document->reference,$document->version,$document->type,$document->author,$description,$document->date_published,$document->status);
	$table->addRow(600);

	for($i=0;$i<count($data)-1;$i++) {
		$table->addCell($w[$i], $styleCell)->addText($data[$i], $fontStyle);
	}
	$table->addCell($w[$i], $styleCell)->addImage($document->img_status,$img_status_style);
} //ends while loop
$section_landscape->addPageBreak();
echo date('H:i:s') . " Done writing life cycle data.<br />";

/*******************
 *
 *
 * Actions list
 *
 *
 *******************/
$section_landscape->addTitle('Actions list',1);
$section_landscape->addTextBreak(2);

$sql = sort_actions();
$result = do_query($sql);
// Add table
$table = $section_landscape->addTable('myOwnTableStyle');

// Add row
$table->addRow(300);
if (($logbook->project != NULL) && ($logbook->show_lru != NULL)) {
	$header=array('Id','Context','Assignee','Description','Criticality','Status','Date open','Date expected');
	$w=array(100,2000,2000,6000,200,200,1000,1000);
}
else {
	$header=array('Id','Project','Equipment','Context','Assignee','Description','Criticality','Status','Date open','Date expected');
	$w=array(200,2000,2000,2000,2000,2000,2000,2000,2000,2000);
}

for($i=0;$i<count($header);$i++) {
	$table->addCell($w[$i], $styleCell)->addText($header[$i], $fontStyle);
}
while($row = mysql_fetch_array($result)) {
	$table->addRow(900);
	$action = new Action($row);
      
	if ($fill) {
		$styleCell = array('valign'=>'center','bgColor'=>'BBBBBB');
	}
	else {
		$styleCell = array('valign'=>'center');
	}
    $description = clean_text($action->description); //html_entity_decode ($row['Description'],ENT_QUOTES,"UTF-8");
	$context     = clean_text($action->context); //html_entity_decode ($row['context'],ENT_QUOTES,"UTF-8");
	if ($action->status == "Closed" ) {
		$description = $description." [".$action->date_closure."] ".$action->response;
		$img_status = 'images/32x32/agt_action_success.png';
	}
	else
	{
		if ($action->deadline_over)
			$img_status = 'images/32x32/agt_update_critical.png';
		else	
			$img_status = 'images/32x32/run.png';
	}
	if (($logbook->show_project != NULL) && ($logbook->show_lru != NULL)) {
		$data = array ($action->id,$context,$action->attendee,$description,$action->crit,$action->status,$action->date_open,$action->date_expected);
	}
	else {
		$data = array ($action->id,$action->project,$action->lru,$context,$action->attendee,$description,$action->crit,$action->status,$action->date_open,$action->date_expected);
	}	
	/* */
	for($i=0;$i<count($data)-3;$i++) {
		$table->addCell($w[$i], $styleCell)->addText($data[$i], $fontStyle);
	}
	$table->addCell($w[$i++], $styleCell)->addImage($img_status,$img_status_style);
	$table->addCell($w[$i], $styleCell)->addText($data[$i++], $fontStyle);
	$table->addCell($w[$i], $styleCell)->addText($data[$i], $fontStyle);
    $fill=!$fill;
}
$section_landscape->addPageBreak();
echo date('H:i:s') . " Done writing actions.<br />";

/*******************
 *
 *
 * Review list
 *
 *
 *******************/
 // New portrait section
$section_portrait = $PHPWord->createSection(array('orientation'=>'portrait',
				'marginLeft' => 400,
			    'marginRight' => 400,
			    'marginTop' => 100,
			    'marginBottom' => 100));
/*
 * Header landscape page
 */
$header_portrait_page = $section_portrait->createHeader();
$table_portrait_page = $header_portrait_page->addTable();
$table_portrait_page->addRow();
$table_portrait_page->addCell(3000,array('valign'=>'bottom'))->addImage('images/ece logo.jpg', array('width'=>76, 'height'=>32, 'align'=>'left'));
$table_portrait_page->addCell(8000,array('valign'=>'center'))->addText2($logbook->title,array('bold'=>true),array('align'=>'center'));
$table_portrait_page->addCell(3000)->addImage('images/small_zodiacaerospace.jpeg', array('width'=>147, 'height'=>50, 'align'=>'right'));
$table_portrait_page->addRow();
$table_portrait_page->addCell(4500)->addText('Reference: '.$logbook->ref);
$table_portrait_page->addCell(500)->addText('Issue: '.$logbook->issue);
$table_portrait_page->addCell(4500)->addText('');
/*
 * Footer portrait page
 */
$footer_portrait_page = $section_portrait->createFooter();
$table_portrait_page = $footer_portrait_page->addTable();
$table_portrait_page->addRow();
$table_portrait_page->addCell(15000)->addText('THIS DOCUMENT AND THE INFORMATION CONTAINED THEREIN ARE ECE’S EXCLUSIVE PROPERTY. ANY COPY AND/OR DISCLOSURE ARE SUBJECT TO ECE’S PRIOR WRITTEN CONSENT.	SAQ100_H',array('size'=>8));
$footer_portrait_page->addPreserveText('Page {PAGE} of {NUMPAGES}.', array('align'=>'right'));			
$section_portrait->addTitle('Reviews list',1);
$section_portrait->addTextBreak(2);

if ($logbook->equipment == 0) {
	$filter_lru = "";
}
else
	$filter_lru = " AND lru = {$logbook->equipment}";
$query = "SELECT reviews.comment, bug_status.name ,date, managed_by, review_type.description, objectives, review_type.type ".
         "FROM review_type, reviews LEFT OUTER JOIN bug_status ON reviews.status = bug_status.id WHERE ".
         "project = {$logbook->project} ".
         "{$filter_lru} AND review_type.id = reviews.type ORDER BY date DESC";
//echo $query;
$result = do_query ($query);
// Add table
$table = $section_portrait->addTable('myOwnTableStyle');
$table->addRow(300);
$header=array("Led by","Type", "Date", "Comment", "Status");
$cell_witdh=array(300,300,2000,6000,300);
for($i=0;$i<count($header);$i++) {
	$table->addCell($cell_witdh[$i], $styleCell)->addText($header[$i], $fontStyle);
}
$data = array();
$count=0;
while($row = mysql_fetch_array($result)) {
	$table->addRow(600);
	if ($fill) {
		$styleCell = array('valign'=>'center','bgColor'=>'BBBBBB');
	}
	else {
		$styleCell = array('valign'=>'center');
	}
	$review = new Review($row);
    $where = " id = {$review->status}";
	$status_data = $review->status;
	$date_class = new Date();
	$date = $date_class->convert_date_conviviale($review->date);
   
	$descr = $review->conclusion;
    $comment = strip_tags(html_entity_decode ($descr,ENT_QUOTES,"UTF-8"));
	$longueur = strlen($comment);
	if ($longueur < 3) {
		$comment = $review->description." ".$review->objectives;
	}	
	//echo ":".$comment."\n";
	// else if ($longueur > 600) {
		// $cut_comment = substr($comment,0,600)." etc .... (see MoM for more)";
	// }	
	// else 
	{
		$cut_comment = $comment;
	}
    $data = array ($review->managed_by,$review->type,$date,$cut_comment,$status_data);
	for($i=0;$i<count($data)-1;$i++) {
		$table->addCell($cell_witdh[$i], $styleCell)->addText($data[$i], $fontStyle);
	}
	switch ($status_data) {
        case "GREEN":
            //$status_data = '<span bgcolor="lime" >'.$status_data."</span>";
			$table->addCell($w[$i], $styleCell)->addText($data[$i], $fontStyleGreen);
            break;
        case "AMBER":
            //$status_data = '<span bgcolor="orange" >'.$status_data."</span>";
			$table->addCell($w[$i], $styleCell)->addText($data[$i], $fontStyleOrange);
            break;
        case "RED":
            //$status_data = '<span bgcolor="red" >'.$status_data."</span>";
			$table->addCell($w[$i], $styleCell)->addText($data[$i], $fontStyleRed);
            break;
        default:
            //$status_data = '<span bgcolor="grey" >'.$status_data."</span>";
			$table->addCell($w[$i], $styleCell)->addText($data[$i], $fontStyleGrey);
    }
	$count++;
	$fill=!$fill;
	echo $review->description.":".$status_data."<br/>";
}
echo date('H:i:s') . " Done writing reviews.<br />";
// Save File
$objWriter = PHPWord_IOFactory::createWriter($PHPWord, 'Word2007');
$filename=str_replace (" " , "_",$title);
$filename=str_replace ("/" , "_",$filename);
$date = date("d").'_'.date("F").'_'.date("Y");
$logbook_location = 'result/'.$filename.'_'.$date.'.docx';
$objWriter->save($logbook_location);
echo " Logbook generated at ".date('H:i:s')." with peak memory usage: " . (memory_get_peak_usage(true) / 1024 / 1024) . " MB<BR>";
?>
<a href="<?php echo $logbook_location ?>" >
<img alt="Export openxml" title="Export openxml" border=0 src="images/128x128/120px-OfficeWord.png" class='img_button'
   style="margin='0px';width='48px';height='48px';" />
</a>                     

<div style="height:20px;"></div>
</div> <!-- content -->
<?php include "includes/footer.php"; ?>
</body>
</html>
