<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US" xml:lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
<link rel="stylesheet" type="text/css" href="users_guide/user_guide.css" />
</head>
<body>
<?php 
define('ATOMIK_AUTORUN', false);
include "atomik/index.php";
include "atomik/app/config.php";
include "includes/bug_functions.php";
include "inc/Db.class.php";
include "inc/Date.class.php";
include "inc/Data.class.php";
include "inc/Review.class.php";
include "inc/User.class.php";
include "inc/Action.class.php";
include "includes/class.html2text/class.html2text.inc";
include "simplehtmldom/simple_html_dom.php";
require_once 'htmlconverter/h2d_htmlconverter.php';
require_once 'htmlconverter/styles.inc';
require_once 'word/PHPWord/PHPWord.php';
include "inc/ExportDoc.class.php";
/**
 * Export of review in openXML (word 2007) format
 *
 * Written by O.Appere
 *
 */
$today_date = date("d").' '.date("F").' '.date("Y"); 
$PHPWord = new PHPWord();
// New portrait section
$section_first_page = $PHPWord->createSection(array('orientation'=>'portrait',
				'marginLeft' => 400,
			    'marginRight' => 400,
			    'marginTop' => 100,
			    'marginBottom' => 100));
/*
 * Document style definition
 */
// Add title styles
$PHPWord->addTitleStyle(1, array('size'=>20, 'color'=>'333333', 'bold'=>true));
$PHPWord->addTitleStyle(2, array('size'=>16, 'color'=>'666666'));
$PHPWord->addFontStyle('rStyle', array('bold'=>true, 'italic'=>true, 'size'=>16));
$PHPWord->addFontStyle('fontStyle', $fontStyle);
$PHPWord->addLinkStyle('linkStyle', $linkStyle);
$PHPWord->addParagraphStyle('pStyle', array('align'=>'center', 'spaceAfter'=>2000, 'spaceBefore'=>4000));
$PHPWord->addParagraphStyle('newStyle', array('align'=>'left', 'spaceAfter'=>20, 'spaceBefore'=>40));
$PHPWord->addTableStyle('myOwnTableStyle', $styleTable, $styleFirstRow);
$PHPWord->addTableStyle('SigTableStyle', $styleSigTable);

/*
 * review
 */
if (isset($_GET['multiple_review_id'])){
       $review_id = $_GET['multiple_review_id'];
       $review = new Review_test($review_id); 
       $show_review  = $review->id;
       $show_project = $review->project_id;
       $show_lru = $review->lru_id;
      $select_checklist = "";
      $mom_id = $review->link;
      $review_type = $review->type_id;
      $id_type = $review->type_id;
      $indice_dal = "";
      $show_baseline = "";       
}
else {
      $show_review = $_POST['show_review'];
      $show_project = $_POST['show_project'];
      $show_lru = $_POST['show_lru'];
      $select_checklist = $_POST['select_checklist'];
      $mom_id = $_POST['mom_id'];
      $review_type = $_GET['id_review_type'];
      $id_type = $_POST['id_type'];
      if ($id_type =="") {
        $id_type = $_GET['id_review_type'];
      }
      $indice_dal = $_POST['dal'];
      $show_baseline = $_POST['show_baseline'];
}
//echo "TEST: ".$show_review.":".$select_checklist.":<br>";

$where="";
if ($show_review != "") {
	$where ="AND reviews.id = {$show_review}";
	//echo "test:".$show_review."<br/>";
}

if ($review_type != "") {
	$where.=" AND review_type.id = {$review_type}";
}
//$query = "SELECT reviews.date, reviews.id as id,managed_by,review_type.type as type ".
//		"FROM reviews, review_type WHERE reviews.type = review_type.id {$where} LIMIT 1";
//echo $query."<br/>";
//$result_review = do_query($query);
//$row = mysql_fetch_array($result_review);
//$review = new Review ($row);

?>
<!-- <h3> Generation of Minutes of the review </h3> -->
<?php
echo $review_description;

$firstname = $userLogFname;
$lastname = $userLogLname;
$head_office = "Quality Department";
$phone_number = "0156061104";
$fax_number = "";
$memo_subject = "QA logbook";

//echo $show_baseline."<br/>";
if ($show_baseline == NULL) {
    $show_data_baseline = "";
}
else {
    $show_data_baseline = "AND baseline_join_data.baseline_id = '{$show_baseline}' ";//AND bug_applications.id = data_id ";
}

//echo $show_review." ".$id_type." ".$indice_dal."/".$show_data_baseline."/".$show_project." ".$show_lru."<br/>";
//$sql    = "SELECT * FROM review_type WHERE id = '{$id_type}' ORDER BY `type` ASC LIMIT 0,1";
//$result = do_query($sql);
//$sql = sort_review(" AND review_type.id = '{$id_type}' AND reviews.id = '{$show_review}'");
//$result = do_query($sql);
//echo $sql."<br>";
//$row = mysql_fetch_array($result);

if ($show_review != "") {				
	$review = new Review_test($show_review);
	$objective_text = $review->objective;
	$conclusion_text = $review->comment;
	$reference 		= "Reference: ".$review->reference;
	$logo_droite 	= '../../images/header_ece_zodiac_1124.jpg';
	$title    		= clean_text($review->project." ".$review->lru." ".$review->type." Report ");
	$sub_title		= $reference;
	$ref 			= $review->reference;
	$review_description = $review->managed_by." ".clean_text($review->type)." performed on ".$review->date;
}
$issue 			= "";

// /* fill header of the document*/
$properties = $PHPWord->getProperties();
$properties->setCreator($firstname." ".$lastname); 
$properties->setCompany('ECE Zodiacaerospace');
$properties->setTitle($title);
$properties->setDescription('Review Report'); 
$properties->setCategory('');
$properties->setLastModifiedBy($firstname." ".$lastname);
$properties->setCreated( mktime(0, 0, 0, 3, 12, 2010) );
$properties->setModified( mktime(0, 0, 0, 3, 14, 2010) );
$properties->setSubject('Review Report'); 
$properties->setKeywords('Review');
/*
 * Header first page
 */
$header_first_page = $section_first_page->createHeader();
$table = $header_first_page->addTable();
$table->addRow();
$table->addCell(3000,array('valign'=>'bottom'))->addImage('images/ece logo.jpg', array('width'=>76, 'height'=>32, 'align'=>'left'));
$table->addCell(8000,array('valign'=>'center',))->addText2($title,array('bold'=>true),array('align'=>'center'));
$table->addCell(3000)->addImage('images/small_zodiacaerospace.jpeg', array('width'=>147, 'height'=>50, 'align'=>'right'));
$table->addRow();
$table->addCell(12000)->addText('Reference: '.$ref);
$table->addCell(500)->addText('Issue: '.$issue);
$table->addCell(4500)->addText('');
/*
 * Footer first page
 */
$footer_first_page = $section_first_page->createFooter();
$table = $footer_first_page->addTable();
$table->addRow();
$table->addCell(15000)->addText("This document contains confidential information that is the property of ECE. Acceptance of this document by its addressee implies the latter's acknowledgment of the confidential nature of its contents and commitment not to use it in any way whatsoever, nor to disclose its content, communicate it to third parties or reproduce it without the prior authorization of ECE.",null,array('align'=>'justify'));
$table->addRow();
$table->addCell(15000)->addText('');
$table->addRow();
$table->addCell(15000)->addText2('ECE  129, Boulevard DAVOUT  CS 82012  75990 Paris Cedex 20  France',null,array('align'=>'center'));
$table->addRow();
$table->addCell(15000)->addText2('Tel.: +33 1 56 06 10 00       Fax: +33 1 56 06 10 10',null,array('align'=>'center'));
$table->addRow();
$table->addCell(15000)->addText2('SAQ100_H',null,array('align'=>'right'));

/*
 * First page
 */
$section_first_page->addText($title, 'rStyle', 'pStyle');
$section_first_page->addTextBreak();
/**
 *  Create the table of SIGNATURES
 */

// Add table
$table = $section_first_page->addTable('SigTableStyle');
$cell_witdh=array(300,3000,1000,1000,1000);
$header = array("","Function", "Name", "Visa", "Date" );
// Add row
$table->addRow(300);
$index=0;
$shadow_cell_width = 2000;
$table->addCell($shadow_cell_width, $shadow_styleCell);
foreach ($header as $value) {
    $table->addCell($cell_witdh[$index++], $styleCell)->addText($value, $fontStyle);
}
$table->addRow(300);
$index=0;
$data = array("Writer",$board." Sw/Hw Quality Assurance","{$userLogFname} {$userLogLname}", "", "{$today_date}");
$table->addCell($shadow_cell_width, $shadow_styleCell);
foreach ($data as $value) {
    $table->addCell($cell_witdh[$index++], $styleCell)->addText($value, $fontStyle);
}
$table->addRow(300);
$index=0;
$data = array("Validation",$board." Process Assurance Nominee","", "", "");
$table->addCell($shadow_cell_width, $shadow_styleCell);
foreach ($data as $value) {
    $table->addCell($cell_witdh[$index++], $styleCell)->addText($value, $fontStyle);
}
$table->addRow(300);
$index=0;
$data = array("Approval",$board." Software Project Manager","", "", "");
$table->addCell($shadow_cell_width, $shadow_styleCell);
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
$table_second_page->addCell(8000,array('valign'=>'center'))->addText2($title,array('bold'=>true),array('align'=>'center'));
$table_second_page->addCell(3000)->addImage('images/small_zodiacaerospace.jpeg', array('width'=>147, 'height'=>50, 'align'=>'right'));
$table_second_page->addRow();
$table_second_page->addCell(12000)->addText('Reference: '.$ref);
$table_second_page->addCell(500)->addText('Issue: '.$issue);
$table_second_page->addCell(4500)->addText('');
/*
 * Footer second page
 */
$footer_second_page = $section_second_page->createFooter();
$table_second_page = $footer_second_page->addTable();
$table_second_page->addRow();
$table_second_page->addCell(15000)->addText("THIS DOCUMENT AND THE INFORMATION CONTAINED THEREIN ARE ECE'S EXCLUSIVE PROPERTY. ANY COPY AND/OR DISCLOSURE ARE SUBJECT TO ECE'S PRIOR WRITTEN CONSENT.	SAQ100_H",array('size'=>7));
$footer_second_page->addPreserveText('Page {PAGE} of {NUMPAGES}.', array('align'=>'right')); 
/*
 * Table of Content
 */
$section_second_page->addText('Table Of Content', 'rStyle', 'pStyle');
$PHPWord->addParagraphStyle('TOCStyle', array('align'=>'center', 'spaceAfter'=>100));
$section_second_page->addTOC('TOCStyle');
$section_second_page->addPageBreak();
/*
 * Life cycle data
 */
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
$table_landscape_page->addCell(8000,array('valign'=>'center'))->addText2($title,array('bold'=>true),array('align'=>'center'));
$table_landscape_page->addCell(3000)->addImage('images/small_zodiacaerospace.jpeg', array('width'=>147, 'height'=>50, 'align'=>'right'));
$table_landscape_page->addRow();
$table_landscape_page->addCell(4500)->addText('Reference: '.$ref);
$table_landscape_page->addCell(500)->addText('Issue: '.$issue);
$table_landscape_page->addCell(4500)->addText('');
/*
 * Footer landscape page
 */
$footer_landscape_page = $section_landscape->createFooter();
$table_landscape_page = $footer_landscape_page->addTable();
$table_landscape_page->addRow();
$table_landscape_page->addCell(15000)->addText("THIS DOCUMENT AND THE INFORMATION CONTAINED THEREIN ARE ECE'S EXCLUSIVE PROPERTY. ANY COPY AND/OR DISCLOSURE ARE SUBJECT TO ECE'S PRIOR WRITTEN CONSENT.	SAQ100_H",array('size'=>7));
$footer_landscape_page->addPreserveText('Page {PAGE} of {NUMPAGES}.', array('align'=>'right'));			
/*
 *
 * Objectives
 *
 */
$section_landscape->addTitle('Objectives',2);
$section_landscape->addTextBreak();
display_html($review->objective,&$section_landscape);
$section_landscape->addTextBreak();
$section_landscape->addText("This meeting ".$review->type." has been conducted on the ".$review->date." and managed by ".$review->managed_by);

/* 
 *
 * Attendees
 *
 */
$section_landscape->addPageBreak();
//$continuous_section = $PHPWord->createSection(array('orientation'=>'paysage'));
//$sectionStyle = $continuous_section->getSettings();
//$sectionStyle->setSectionType("continuous");
//$continuous_section->addText('Essai section continue', 'rStyle', 'pStyle');
$attendee_table = $section_landscape->addTable('shadow_styleCell');
$attendee_table->addRow(600);
$attendee_table->addCell(4000, $styleCell)->addText("Attendees:",$fontStyle);
$attendee_table->addCell(2000, $styleCell)->addImage("images/32x32/edit_group.png",$img_status_style);
/*
$section_landscape->addTitle("Attendees:",2);
$section_landscape->addImage("images/32x32/edit_group.png",$img_status_style);
*/
//$section_landscape->addTextBreak();
//$sql_query = "SELECT DISTINCT bug_users.id,fname,lname,username,email,telephone,last_logged,function ,enterprises.name as enterprise ".
//		"FROM bug_users LEFT OUTER JOIN enterprises ON enterprises.id = enterprise_id ".
//		"LEFT OUTER JOIN user_join_project ON bug_users.id = user_join_project.user_id ".
//		"WHERE bug_users.id = bug_users.id AND user_join_project.project_id = ".$show_project.
//    " ORDER BY `bug_users`.`lname` ASC";
    //echo $sql_query;
//$result =do_query($sql_query);
/*
 * Create user table
 */
/*
$user_table = $section_landscape->addTable('myOwnTableStyle');
$header = array("Attendees","Company","Function");
  $w=array(2000,2000,4000);
  // Add row
  $user_table->addRow(600);
  for($i=0;$i<count($header);$i++) {
  	$user_table->addCell($w[$i], $styleCell)->addText($header[$i], $fontStyle);
  }
*/  
 
if ($review->attendees != NULL) {
    foreach( $review->attendees as $id => $users ) {
		//$textrun->addLink('http://www.google.com', null, 'NLink');
         //$meeting_attendee.= $users.", ";
         //$document->setValue('Value'.$index++,$users);
		  //$user_table->addRow(600);
		  //$user_table->addCell($w[0], $styleCell)->addText($user->name, $fontStyle);
		  //echo ($users['name']);
		  $user_info = $users['name'].", ".$users['company'].", ".$users['function'];
		  //$user_info_decode = html_entity_decode($user_info, ENT_QUOTES, 'iso-8859-1');
		  //$test_decode = html_entity_decode("&eacute", ENT_QUOTES, 'iso-8859-1');
		  $user_mailto = "mailto:".$users['email'];
		  //echo $test_decode."<br/>";
		  $section_landscape->addLink($user_mailto,$user_info ,'linkStyle','newStyle');
		  //textrun->addText($users['name'].", ", 'rStyle');
		  //$section_landscape->addText($user_info, 'fontStyle');
		  /*
		  $user_table->addCell($w[0], $styleCell)->addText($users['name'], $fontStyle);
		  $user_table->addCell($w[1], $styleCell)->addText($users['company'], $fontStyle);
		  $user_table->addCell($w[2], $styleCell)->addText($users['function'], $fontStyle);	
		  */		  
    }
	
//while($row = mysql_fetch_array($result)) {
  //$user = new User($row);
  //$section_landscape->addText($user->name.", ".$user->user_function);
  //$user_table->addRow(600);
  //$user_table->addCell($w[0], $styleCell)->addText($user->name, $fontStyle);
  //$user_table->addCell($w[1], $styleCell)->addText($user->user_function, $fontStyle);
}	
//$section_landscape->addPageBreak();
//  $table_checklist = $section_landscape->addTable('myOwnTableStyle'); 
//  $data = array();
//  $header = array("Checklist items", "Compliant");
//  $w=array(4000,200);
  // Add row
//  $table_checklist->addRow(600);
//  for($i=0;$i<count($header);$i++) {
//  	$table_checklist->addCell($w[$i], $styleCell)->addText($header[$i], $fontStyle);
//  }
//while($row = mysql_fetch_object($result)) {
//  echo $index_question++.") ".new_clean_text($row->question)."<br>";
//  $table_checklist->addRow(600);
//  $table_checklist->addCell($w[0], $styleCell)->addText($row->question."\nTest Retour chariot", $fontStyle);
//  $table_checklist->addCell($w[1], $styleCell)->addText("", $fontStyle);
//}
$section_landscape->addPageBreak();
/*
 * SPR
 */
$section_landscape->addTitle('Sw Problem Report status',2);
$section_landscape->addTextBreak(); 
$section_landscape->addText("Software Problem Report extracted from IBM Change database on ".$today_date." at ".date('H:i:s') );
require_once 'spr/list_spr_change.php'; 
$spr_table = get_change_spr($review->lru_id); 
//echo $spr_table;
display_html($spr_table,&$section_landscape);
$section_landscape->addPageBreak();
/*
 * Baseline and Data table
 */
$section_landscape->addTitle('Baseline and documents',2);
$section_landscape->addTextBreak();
$data = array();
$header = array("Reference", "Issue", "Type","Component","Author" ,"Description","Released","Status" );
$w=array(2000,100,200,500,500,7000,2000,200);
// Add table
$table = $section_landscape->addTable('myOwnTableStyle');
// Add row
$table->addRow(600);
for($i=0;$i<count($header);$i++) {
	$table->addCell($w[$i], $styleCell)->addText($header[$i], $fontStyle);
}
//$show_data_type = " AND status != '40' "; /* do not display obsolete data */
$order = "'name'";


/* Baseline */
if ($review->id != "") {
	$sql_query = "SELECT baseline_id as id FROM `baseline_join_review` WHERE `review_id` = ".$review->id." LIMIT 0 , 1";
	$result = do_query ($sql_query);
	//echo "Baseline: ".$sql_query."<br>";
	$row = mysql_fetch_object($result);
	$nbtotal=mysql_num_rows($result);
}
else {
	$nbtotal=0;
}
if ($nbtotal != 0){ 
	$export_review = true;
	$sql_list_data = sort_data($review->project,$review->lru,$row->id);

	//$sql_list_data = sort_data();
	//echo "SQALDATA:".$sql_list_data."<br/>";
	$result = do_query ($sql_list_data);
	while($row = mysql_fetch_array($result)) {
		$document = new Data($row);
		$data = array ($document->reference,
						$document->version,
						$document->type,
						$document->lru,
						utf8_decode($document->author),
						clean_text(utf8_decode($document->description)),
						$document->date_published,
						$document->status);
		$table->addRow(600);

		for($i=0;$i<count($data)-1;$i++) {
			$table->addCell($w[$i], $styleCell)->addText($data[$i], $fontStyle);
		}
		$table->addCell($w[$i], $styleCell)->addImage($document->img_status,$img_status_style);
	} //ends while loop
	$section_landscape->addPageBreak();
	//echo date('H:i:s') . " Done writing life cycle data.<br />";
}
else {
    $table->addRow(600);
	for($i=0;$i<count($header);$i++) {
		$table->addCell($w[$i], $styleCell)->addText("---", $fontStyle);
	}
}
/*
 *
 * Checklist
 *
 */

$section_landscape->addPageBreak();
$section_landscape->addTitle('Checklist',2);
$section_landscape->addTextBreak();
//echo $review->type."<br/>";
// switch ($review->type) {
	// case "System Specification Review":
		// $select_checklist = 1;
		// break;
	// case "Change Control Board":
		// $select_checklist = 2;
		// break;		
	// default:
		// $select_checklist = "";
		// break;
// }
if ($review->id != "") {
  $select_checklist_query = "WHERE review_id = {$review->type_id}";

	$sql = "SELECT question FROM checklist_questions LEFT OUTER JOIN reviews ON checklist_questions.review_id = reviews.id  {$select_checklist_query}";
	$result = do_query($sql);
	//echo $sql."<br>";
	$index_question = 1;
	/*
	 * Create checklist table
	 */
	  $table_checklist = $section_landscape->addTable('myOwnTableStyle');
	  $data = array();
	  $header = array("Checklist items", "Compliant");
	  $w=array(8000,200);
	  // Add row
	  $table_checklist->addRow(600);
	  for($i=0;$i<count($header);$i++) {
		$table_checklist->addCell($w[$i], $styleCell)->addText($header[$i], $fontStyle);
	  }
	 
	while($row = mysql_fetch_object($result)) {
	  //echo $index_question++.") ".new_clean_text($row->question)."<br>";
	  $table_checklist->addRow(600);
	  $table_checklist->addCell($w[0], $styleCell)->addText(clean_text($row->question)."", $fontStyle);
	  $table_checklist->addCell($w[1], $styleCell)->addText("", $fontStyle);
	}
}
else {
  $select_checklist_query = "";
}

if ($review->scope == "sw") {
  //$convert_html2doc->html2text ($row['description']);
  //$section_landscape->addText($convert_html2doc->get_text());
  $section_landscape->addPageBreak();
  $section_landscape->addTitle('DO-178B DAL '.$dal.' objectives',2);
  switch ($dal) {
         case "A":
           $indice_dal='dal_a';
         break;
         case "B":
           $indice_dal='dal_b';
         break;
         case "C":
           $indice_dal='dal_c';
         break;
         case "D":
           $indice_dal='dal_d';
         break;
         default:
           $indice_dal='dal_a';
         }
  $query = "SELECT * FROM do_178b_join_review,do_178b_tables ".
		"WHERE review={$review->type_id} AND ({$indice_dal} = 'm' OR {$indice_dal} = 'i') AND".
		" do_178b_join_review.objective_id = do_178b_tables.id ORDER BY table_a,objective ASC";
  //echo $query."<br/>";
  $result_obj = do_query($query);
  $table = $section_landscape->addTable('myOwnTableStyle');
  $data = array();
  $header = array("Table", "Objective", "Description" ,"Compliant");
  $w=array(200,200,4000);
  
  // Add row
  $table->addRow(600);
  for($i=0;$i<count($header);$i++) {
  	$table->addCell($w[$i], $styleCell)->addText($header[$i], $fontStyle);
  }
  while($row_obj = mysql_fetch_array($result_obj)) {
        $table_a="A-".$row_obj['table_a'];
  	  $objective=$row_obj['objective'];
  	  $description=$row_obj['description'];
  	  //echo "do:".$row_obj['description']."<br/>";
  	      $data = array ($table_a,
      				$objective,
      				$description);
  	$table->addRow(600);
  
  		$table->addCell($w[0], $styleCell)->addText($table_a, $fontStyle);
  		$table->addCell($w[1], $styleCell)->addText($objective, $fontStyle);
  		$table->addCell($w[2], $styleCell)->addText($description, $fontStyle);
  		$table->addCell($w[3], $styleCell)->addText("", $fontStyle);
  
  	  //echo "<tr><td>A-".$table_a.".".$objective."</td><td>".$description."</td><td></td></tr>";
  }
  $section_landscape->addPageBreak();
}
/*
 * Display Minutes of the meeting.
 */
$section_landscape->addPageBreak();
$section_landscape->addTitle('Minutes',2); 
$section_landscape->addTextBreak();
//$str = $review->description;
//display_paragraph($review->description);
display_html($review->description,&$section_landscape);
?> </div><?php
$show_proj = "";
$show_equip = "";
/*
 *
 * Previous Actions list
 *
 */
$section_landscape->addTitle('Previous Actions list',2);
$section_landscape->addTextBreak(2);

if ($review->previous_id != 0){
  $show_rev ="AND review = ".$review->previous_id." ";
	$sql = sort_actions();
	//echo "SQL_ACTION:".$sql."<br/>";
	$result = do_query($sql);
	// Add table
	$table = $section_landscape->addTable('myOwnTableStyle');
	
	// Add row
	$table->addRow(300);
	if (($show_project != NULL) && ($show_lru != NULL)) {
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
	$action_counter = 1;
	while($row = mysql_fetch_array($result)) {
		$table->addRow(900);
		$action = new Action($row);
	    $attendee = $action->attendee;
	    $status =  $action->status;
		$date_open= $action->date_open;
	    $date_expected=$action->date_expected;
	    $date_closure=$action->date_closure;
	       
		if ($fill) {
			$styleCell = array('valign'=>'center','bgColor'=>'BBBBBB');
		}
		else {
			$styleCell = array('valign'=>'center');
		}
		$table->addCell($w[$i++], $styleCell)->addText($action_counter, $fontStyle);
		$context = clean_text($action->context); //html_entity_decode ($row['context'],ENT_QUOTES,"UTF-8");
		if (($show_project != NULL) && ($show_lru != NULL)) {
			$table->addCell($w[$i++], $styleCell)->addText($context, $fontStyle);
			$table->addCell($w[$i++], $styleCell)->addText($attendee, $fontStyle);	
		}
		else {
			$table->addCell($w[$i++], $styleCell)->addText($row['project'], $fontStyle);
			$table->addCell($w[$i++], $styleCell)->addText($row['lru'], $fontStyle);
			$table->addCell($w[$i++], $styleCell)->addText($context, $fontStyle);
			$table->addCell($w[$i++], $styleCell)->addText($attendee, $fontStyle);
		}
		$h2t = new html2text($action->description);
		$html = str_get_html($action->description);
		//$plain_action_description = $h2t->get_text(); 
		//$plain_action_description = html_entity_decode($plain_action_description, ENT_COMPAT, 'iso-8859-1');
		$plain_action_description = "";
		$cell_descr = $table->addCell($w[$i], $styleCell);
		$cell_descr->addText("[#".$action->id."#]", $fontStyle);
		$cell_descr->addTextBreak(0);
		foreach($html->find('p') as $p) {
			if ($p->innertext != ""){
				$plain_action_description = html_entity_decode($p->innertext, ENT_QUOTES, 'iso-8859-1');
				$cell_descr->addText($plain_action_description, $fontStyle);
				$cell_descr->addTextBreak(0);
			}
		}
		$listStyle = array('listType' => PHPWord_Style_ListItem::TYPE_NUMBER);
		foreach($html->find('ul') as $ul)
		{
			   foreach($ul->find('li') as $li)
			   {
					if ($li->innertext != ""){
						$text = html_entity_decode($li->innertext, ENT_QUOTES, 'iso-8859-1');
						/* addListItem ne fonctionne dans la version 0.6.2 */
						//$cell_descr->addListItem($text, 0,null,$listStyle);
						$cell_descr->addText(" - ".$text, $fontStyle);
						//$cell_descr->addTextBreak(0);
						//$plain_action_description .= $li->innertext."\n";
					}
			   }
		}
		$cell_descr->addTextBreak(1);
		$i++;
		//$plain_action_description = html_entity_decode($plain_action_description, ENT_QUOTES, 'iso-8859-1');
	    //$description = "[#".$action->id."#]".$plain_action_description; //html_entity_decode ($row['Description'],ENT_QUOTES,"UTF-8");
		
		if ($status == "Closed" ) {
			$cell_descr->addText(" [".$action->date_closure."] ".$row['comment'], $fontStyle);
			//$description = $description." [".$action->date_closure."] ".$row['comment'];
			$img_status = 'images/32x32/agt_action_success.png';
		}
		else
		{
			if ($action->deadline_over)
				$img_status = 'images/32x32/agt_update_critical.png';
			else	
				$img_status = 'images/32x32/run.png';
		}
	
		/* */
		$data = array ($row['criticality'],$status,$date_open,$date_expected);
		for($i=0;$i<count($data)-3;$i++) {
			$table->addCell($w[$i], $styleCell)->addText($data[$i], $fontStyle);
		}
		$table->addCell($w[$i++], $styleCell)->addImage($img_status,$img_status_style);
		$table->addCell($w[$i], $styleCell)->addText($data[$i++], $fontStyle);
		$table->addCell($w[$i], $styleCell)->addText($data[$i], $fontStyle);
	    $fill=!$fill;
	    $action_counter++;
	}
	$section_landscape->addPageBreak();
}
/*
 *
 * Current Actions list
 *
 */
$section_landscape->addTitle('Current Actions list',2);
$section_landscape->addTextBreak(2);
if ($review->id != "") {
  $show_rev ="AND review = ".$review->id." ";
}
else {
  $show_rev ="";
}
//echo $show_rev."<br>";
$sql = sort_actions();
//echo "SQL_ACTION:".$sql."<br/>";
$result = do_query($sql);
// Add table
$table = $section_landscape->addTable('myOwnTableStyle');

// Add row
$table->addRow(300);
if (($show_project != NULL) && ($show_lru != NULL)) {
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
$action_counter = 1;
while($row = mysql_fetch_array($result)) {
	$table->addRow(900);
	$action = new Action($row);
    $attendee = $action->attendee;
    $status =  $action->status;
	$date_open= $action->date_open;
    $date_expected=$action->date_expected;
    $date_closure=$action->date_closure;
       
	if ($fill) {
		$styleCell = array('valign'=>'center','bgColor'=>'BBBBBB');
	}
	else {
		$styleCell = array('valign'=>'center');
	}
	$h2t = new html2text($action->description);
	//$plain_action_description = $action->description;//$h2t->get_text(); 
	$html = str_get_html($action->description);
	// echo $html;
	$counter_p = 1;
	$plain_action_description = "";
	foreach($html->find('p') as $p) {
		if ($p->innertext != ""){
			$plain_action_description .= $p->innertext."\n";
		}
	}
	$plain_action_description = html_entity_decode($plain_action_description, ENT_QUOTES, 'iso-8859-1');
    $description = "[#".$action->id."#]".$plain_action_description; //html_entity_decode ($row['Description'],ENT_QUOTES,"UTF-8");
	$context     = clean_text($action->context); //html_entity_decode ($row['context'],ENT_QUOTES,"UTF-8");
	if ($status == "Closed" ) {
		$description = $description." [".$action->date_closure."] ".$row['comment'];
		$img_status = 'images/32x32/agt_action_success.png';
	}
	else
	{
		if ($action->deadline_over)
			$img_status = 'images/32x32/agt_update_critical.png';
		else	
			$img_status = 'images/32x32/run.png';
	}
	if (($show_project != NULL) && ($show_lru != NULL)) {
		$data = array ($action_counter,$context,$attendee,$description,$row['criticality'],$status,$date_open,$date_expected);
	}
	else {
		$data = array ($action_counter,$row['project'],$row['lru'],$context,$attendee,$description,$row['criticality'],$status,$date_open,$date_expected);
	}	
	/* */
	for($i=0;$i<count($data)-3;$i++) {
		$table->addCell($w[$i], $styleCell)->addText($data[$i], $fontStyle);
	}
	$table->addCell($w[$i++], $styleCell)->addImage($img_status,$img_status_style);
	$table->addCell($w[$i], $styleCell)->addText($data[$i++], $fontStyle);
	$table->addCell($w[$i], $styleCell)->addText($data[$i], $fontStyle);
    $fill=!$fill;
    $action_counter++;
}
if ($action_counter == 1) {
	$table->addRow(600);
	for($i=0;$i<count($header);$i++) {
		$table->addCell($w[$i], $styleCell)->addText("---", $fontStyle);
	}
}	
/*
 * Conclusion 
 */
$section_landscape->addPageBreak();
$section_landscape->addTitle('Conclusion',2);
display_html($review->comment,&$section_landscape);
//display_paragraph($review->comment);
//$section_landscape->addText($conclusion_text);
$section_landscape->addPageBreak();
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
$table_third_page->addCell(8000,array('valign'=>'center'))->addText2($title,array('bold'=>true),array('align'=>'center'));
$table_third_page->addCell(3000)->addImage('images/small_zodiacaerospace.jpeg', array('width'=>147, 'height'=>50, 'align'=>'right'));
$table_third_page->addRow();
$table_third_page->addCell(4500)->addText('Reference: '.$ref);
$table_third_page->addCell(500)->addText('Issue: '.$issue);
$table_third_page->addCell(4500)->addText('');
/*
 * Footer third page
 */
$footer_third_page = $section_legend->createFooter();
$table_third_page = $footer_third_page->addTable();
$table_third_page->addRow();
$table_third_page->addCell(15000)->addText("THIS DOCUMENT AND THE INFORMATION CONTAINED THEREIN ARE ECE'S EXCLUSIVE PROPERTY. ANY COPY AND/OR DISCLOSURE ARE SUBJECT TO ECE'S PRIOR WRITTEN CONSENT.	SAQ100_H",array('size'=>7));
$footer_third_page->addPreserveText('Page {PAGE} of {NUMPAGES}.', array('align'=>'right'));
$section_legend->addTitle('Terminology',2);
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
			  "Document reviewed" => "images/64x64/All software is current.png",
			  "Document under review" => "images/64x64/lists.png",
			  "Document not reviewed" => "images/64x64/kghostview.png",
			  "Document not published" => "images/64x64/kedit.png",
			  "Action closed" => "images/32x32/agt_action_success.png",
			  "Action opened" => "images/32x32/run.png",
			  "Action deadline over" => "images/32x32/agt_update_critical.png");
foreach ($data as $icon_signification => $img_status) {
	$table->addRow(300);
	$table->addCell($cell_witdh[0], $styleCell)->addImage($img_status,$img_status_style);
	$table->addCell($cell_witdh[1], $styleCell)->addText($icon_signification, $fontStyle);
}
//echo "<br/>".date('H:i:s') . " Done writing actions.<br />";

// Save File
$objWriter = PHPWord_IOFactory::createWriter($PHPWord, 'Word2007');
$filename=str_replace (" " , "_",$title);
$filename=str_replace ("/" , "_",$filename);
$filename="MoM";
$logbook_location = 'result/'.$filename.'_'.$today_date.'.docx';
$objWriter->save($logbook_location);?>
<div class="user_guide_center">
<div class="user_guide_content_small">	
Report generated at <?php date('H:i:s') ?><br/>
Peak memory usage: <?php echo (memory_get_peak_usage(true) / 1024 / 1024) ?> MB<BR>
</div>
<div class="user_guide_sidebar_small">
<a href="<?php echo $logbook_location ?>" >
<img alt="Export openxml" title="Export openxml" border=0 src="images/128x128/120px-OfficeWord.png" class='img_button' />
</a>
</div>
<div class="spacer"></div>
</div>	
</body>
</html>	