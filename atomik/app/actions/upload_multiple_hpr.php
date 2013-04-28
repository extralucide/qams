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
<?php //include "menu_generic.php";?>
</head>
<body>
<div id="content">
<div style="height:20px;"></div>  
<div style="padding:20px;">
<h3> Requirements extraction report </h3>
<?php
//include "includes/header.php";
//include "includes/bug_list_globals.php";
include "../../includes/bug_functions.php";
include "../../includes/bug_list_globals.php";
include "../../excel/Classes/PHPExcel.php";
include "../../includes/config.php"; //These are the database and ftp settings
include "../../includes/cookie.php"; //This checks that user is logged in and gets info from cookie

function odt2text($filename) {
    return readZippedXML($filename, "content.xml");
}

function docx2text($filename) {
    return readZippedXML($filename, "word/document.xml");
}

function readZippedXML($archiveFile, $dataFile) {
    // Create new ZIP archive
    $zip = new ZipArchive;

    // Open received archive file
    if (true === $zip->open($archiveFile)) {
        // If done, search for the data file in the archive
        if (($index = $zip->locateName($dataFile)) !== false) {
            // If found, read it to the string
            $data = $zip->getFromIndex($index);
            // Close archive file
            $zip->close();
            // Load XML from a string
            // Skip errors and warnings
            $xml = DOMDocument::loadXML($data, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
            // Return data without XML formatting tags
			  $liste = $xml->getElementsByTagName('field');

			  foreach($liste as $lis)
				{
				  if ($lis->hasAttribute("name")) {
				if($lis->getAttribute("name")=="Title")
				  echo $lis->nodeValue;
				  }
				}
						return strip_tags($xml->saveXML());
					}
					$zip->close();
				}

    // In case of failure return empty string
    return "error no archive file received from ".$archiveFile;
} 
function Display_A350_SWDD_ENMU_Req ($text)
{
	//$text = "[SWRD_ENMU_001]The usage of ROM/RAM capacity should have a margin at entry into service for possible extension in size of at least 50 %.Refers to:[SES_EPDS_ENMU_1010]Status:MATURESafety:Derived:Allocation:ConstraintRationale:Npl_Refers to:[ABD0100.1.9-020-G]Additional Information: [End Requirement]";
	//$text = "[SWRD1_ENMU_xxx]Text of the Development Data, tablesRefers to:[SES_ENMU_XXXX]Status:MATURESafety:YESDerived:YESAllocation:Rationale:Additional Information:[End Requirement]AttributePossible Values";
	//$text = "(MEMORY, CPU, I/O)[SWRD_ENMU_001]The usage of ROM/RAM capacity should have a margin at entry into service for possible extension in size of at least 50 %.Refers to:[SES_EPDS_ENMU_1010]Status:MATURESafety:Derived:Allocation:ConstraintRationale:Npl_Refers to:[ABD0100.1.9-020-G]Additional Information: [End Requirement][SWRD_ENMU_002]The usage of CPU capacity shall have a margin at entry into service for possible extension in size of at least 50 %.Refers to:[SES_EPDS_ENMU_1010]Status:MATURESafety:Derived:Allocation:ConstraintRationale:Npl_Refers to:Additional Information: [End Requirement]OperationsNot applicable.Aircraft adaptation requirementsNone.ENMF CSCI Software functionsThe software embedded in the ENMU module is named “ENMF CSCI”. This CSCI carries out all of the oper";
	//preg_match_all("#(\[SWRD\_[a-z0-9._-]+\])(.+)(\[End Requirement\])#s", $text, $matches, PREG_SET_ORDER);
	preg_match_all("#\[(SWRD[-. ]?\_.{2,6}\_.{2,6})\](.+)Refers to:(.+)Status:(.+)Safety:(.*)Derived:(.*)Allocation:(.*)Rationale:(.*)Npl_Refers to:(.*)Additional Information:(.*)\[End Requirement\]#U", $text, $matches, PREG_SET_ORDER);
	/*
	 * parse also REF _Ref235431527 \r \h 0
	 */
	foreach ($matches as $val) {
		//echo "matched: " . $val[0] . "<BR>";
		echo "Id: " . $val[1] . "<BR>";
		echo "Body: " . $val[2] . "<BR>";
		echo "Refers to: " . $val[3] . "<BR>";
		echo "Status: " . $val[4] . "<BR>";
		echo "Safety: " . $val[5] . "<BR>";
		echo "Derived: " . $val[6] . "<BR>";
		echo "Allocation: " . $val[7] . "<BR>";
		echo "Rationale: " . $val[8] . "<BR>";
		echo "Npl_Refers to: " . $val[9] . "<BR>";
		echo "Additional Information: " . $val[10] . "<BR>";
	}	
	//cho $text;
}
function Display_A350_SWRD_ENMU_Req ($text)
{
	global $db_select;
	global $today_date;
	global $file_template;
error_reporting(E_ALL);
$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
PHPExcel_Settings::setCacheStorageMethod($cacheMethod);
/** PHPExcel_IOFactory */
require_once '../../excel/Classes/PHPExcel/IOFactory.php';

/** PHPExcel_IOFactory */
require_once '../../excel/Classes/PHPExcel/Worksheet/RowIterator.php';

if (!file_exists($file_template)) {
	exit("SAQ225 template is missing.\n");
}

$objPHPExcel = PHPExcel_IOFactory::load($file_template);
		
$objPHPExcel->setActiveSheetIndex(0);
$styleArray = array(
	'font' => array(
		'bold' => true,
	),
	'alignment' => array(
		'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
	),
	'borders' => array(
		'top' => array(
			'style' => PHPExcel_Style_Border::BORDER_THIN,
		),
	),
	'fill' => array(
		'type' => PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR,
		'rotation' => 90,
		'startcolor' => array(
			'argb' => 'FFA0A0A0',
		),
		'endcolor' => array(
			'argb' => 'FFFFFFFF',
		),
	),
);
$objPHPExcel->getActiveSheet()->getStyle('A27:J500')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('A27:J500')->getAlignment()->setWrapText(true);
$title = "";
$reference = "";
$username = "";
$objPHPExcel->getActiveSheet()->setCellValue('C8', $title);
$objPHPExcel->getActiveSheet()->setCellValue('C9', $reference);

/* date of reading */
$objPHPExcel->getActiveSheet()->setCellValue('D17', $today_date);
/* name of the reader */
$objPHPExcel->getActiveSheet()->setCellValue('F17', $username);

$objPHPExcel->getProperties()->setCreator($username)
							 ->setLastModifiedBy($username)
							 ->setTitle("peer".$title."review")
							 ->setSubject("Ref:".$reference)
							 ->setDescription("Peer review report for ".$title)
							 ->setKeywords("PRR openxml php")
							 ->setCategory("Peer Review Report");
	preg_replace("#REF \_Ref[0-9]{9} \\r \\h 0#"," ");
	preg_match_all("#\[(SWRD[-. ]?\_.{2,6}\_.{2,6})\](.+)".						 
	"Refers to:(.+)".
	"Status:(.+)".
	"Safety:(.*)".
	"Derived:(.*)".
	"Allocation:(.*)".
	"Rationale:(.*)".
	"Npl_Refers to:(.*)".
	"Additional Information:(.*)".
	"\[End Requirement\]".
	"#U", $text, $matches, PREG_SET_ORDER);
	/*
	 * parse also REF _Ref235431527 \r \h 0
	 */
	$row_counter = 26;
	$header_fields = array("","Id","Body", "Refers to", "Status", "Safety", "Derived", "Allocation","Rationale","Npl_Refers to","Additional Information");
	for ($index = 1; $index <= 10; $index++) {
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($index-1,$row_counter, $header_fields[$index]);
	}

	$row_counter = 27;
	foreach ($matches as $val) {

	for ($index = 1; $index <= 10; $index++) {
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($index-1,$row_counter, $val[$index]);
		if (preg_match("MATURE", $val[4])) {
		//if ($val[4] != "MATURE") {
			$objPHPExcel->getActiveSheet()->getStyle('D'.$row_counter)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
		}
	}
	$row_counter++;
}
/* count requirerments */
//$objPHPExcel->getActiveSheet()->setCellValue('B14','=NBVAL(A,A)');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$filename = "Traca_{$today_date}.xlsx";
$objWriter->save('../../result/'.$filename);
//$objWriter->save('php://output'); 

// Echo done
echo date('H:i:s') . " Done writing remarks.<br />";
echo " Peer review generated at ".date('H:i:s')." with peak memory usage: " . (memory_get_peak_usage(true) / 1024 / 1024) . " MB\r\n";
}
function Display_Legacy450_SWRD_SSPC_Req ($text)
{
	global $db_select;
	global $today_date;
	global $file_template;
	global $row_counter;
	
error_reporting(E_ALL);
$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
PHPExcel_Settings::setCacheStorageMethod($cacheMethod);
/** PHPExcel_IOFactory */
require_once '../../excel/Classes/PHPExcel/IOFactory.php';

/** PHPExcel_IOFactory */
require_once '../../excel/Classes/PHPExcel/Worksheet/RowIterator.php';
//$file_template = "SAQ225_3.xlsx";
if (!file_exists($file_template)) {
	exit("SAQ225 template is missing.\n");
}

$objPHPExcel = PHPExcel_IOFactory::load($file_template);
		
$today_date = date("d").' '.date("F").' '.date("Y");

$objPHPExcel->setActiveSheetIndex(0);
$title = "";
$reference = "";
$username = "";
$objPHPExcel->getActiveSheet()->setCellValue('C8', $title);
$objPHPExcel->getActiveSheet()->setCellValue('C9', $reference);

/* date of reading */
$objPHPExcel->getActiveSheet()->setCellValue('D17', $today_date);
/* name of the reader */
$objPHPExcel->getActiveSheet()->setCellValue('F17', $username);

$objPHPExcel->getProperties()->setCreator($username)
							 ->setLastModifiedBy($username)
							 ->setTitle("peer".$title."review")
							 ->setSubject("Ref:".$reference)
							 ->setDescription("Peer review report for ".$title)
							 ->setKeywords("PRR openxml php")
							 ->setCategory("Peer Review Report");
							 
	//preg_match_all("#\[(SWRD[-. ]?\_.{2,6}\_.{2,6})\](.+)".
	//echo $text;
	
	//$text = "following is an example:SwRD_SSPC_EXTIF_CAN_001SSCS_BOARD_MODU_CAN_002Justification: NAThe SSPC Channel Software shall communicate with CPS through a dedicated CAN.End_SwRD_ReqRequirement Terms UsedThe â€œshallâ€, â€œwillâ€";
	/* expression régulière correcte sauf traça multiple
	preg_match_all("#(SwRD\_[A-Z]{4}\_.+\_[0-9]{3}[\_MNS]?)(Derived|.{2,6}\_Missing\_Requirement[\_MNS]?|.{2,6}\_.{2,6}\_?.{2,6}?\_?.{2,6}?\_[0-9]{3})".
	"Justification:( ?NA|.+\.?)".
	"(.+)".
	"End_SwRD_Req".
	"#Um", 
	$text, $matches, PREG_SET_ORDER);	
	*/

//echo $text;
$regexp_req = "((Covered by SMP &amp; PSAC|SwRD_[A-Z]{2,4}_?[A-Z]{2,12}?_?[A-Z]{2,12}?_[0-9]{3})(?:_[MNS]|)";
$regexp_ref = "(\w{2,7}_?[A-Z]{2,12}?_?[A-Z]{2,12}?_?[A-Z]{2,12}?_[0-9]{3}|Derived|\w{2,7}_Missing_Requirement)(?:_[MNS]|))";
$regexp_rat = "Justification:( ?NA|.+\.)";
$regexp_bod = "(.+)";
$regexp_end = "End_SwRD_Req";
$regular_expression = $regexp_req.$regexp_ref;//.$regexp_rat.$regexp_bod.$regexp_end ;
$regular_expression_2 = $regexp_req.$regexp_ref.$regexp_rat.$regexp_bod.$regexp_end ;
$regular_expression_3 = "(Covered)";
$source1="Covered by SMP & PSACSSCS_BOARD_MODU_CTRL_008Justification:NAtototitiEnd_SwRD_ReqSwRD_SSPC_SAFETY_001SSCS_BOARD_SOFT_002";
$source2="SwRD_SSPC_RESOURCE_002_NDerivedJustification:NAtatatetetiEnd_SwRD_ReqSwRD_SSPC_SAFETY_001SSCS_Missing_Requirement";
$source=$source1.$source2;
/* remove REF _Ref163013971 \h */
$text2 = preg_replace("#REF \_Ref[0-9]{9} ?.?r? .h #","",$text);
$source=$text2;
//echo $source;
preg_match_all("#".$regular_expression."#U",$source, $matches_traca, PREG_SET_ORDER);
	foreach ($matches_traca as $val) {
	echo  "<BR><BR><BR>";
	//echo  "Requiremn:".$val[0]."<BR>";
	echo  "Requiremn:".$val[2]."<BR>";
	echo  "Refers to:".$val[3]."<BR>";
	}
//	$text2 = preg_replace("#REF \_Ref[0-9]{9} ?.?r? .h #","",$text);
//	preg_match_all("#(SwRD\_[A-Z]{4}\_.+\_[0-9]{3}(\<\_[MNS]\>)?)(\w{2,7}\_?.{2,30}?|Missing\_Requirement\_[0-9]{3}?)".
        /* look for NA or a sentence with dot at the end on one line otherwise justification is merge with body of the req */
	//"Justification:( ?NA|.+\.?)".
	//"(.+)".
	//"End_SwRD_Req".
	//"#U", 
	//$text2, $matches, PREG_SET_ORDER);	
	/*
	 * parse also REF _Ref235431527 \r \h 0
	 */
	$row_counter = 26;
	$header_fields = array("","Id","Body", "Refers to", "Status", "Safety", "Derived", "Allocation","Rationale","Npl_Refers to","Additional Information");
	for ($index = 1; $index <= 10; $index++) {
		//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($index,$row_counter, $header_fields[$index]);
	}

	$row_counter = 27;
	preg_match_all("#".$regular_expression_2."#U",$source, $matches, PREG_SET_ORDER);
	foreach ($matches as $val) {
		echo  "<BR><BR><BR>";
		//echo $val[0];
		/* remove SWRD tag and copy reference to ref */
		//preg_match ("#".$regexp_req."#",$val[1],$id);
		//$add_ref = preg_replace("#".$regexp_req."#","",$val[1]);
		echo  "ID:".$val[2]."<BR>";
		echo  "Refers to: ";//.$val[3]."<BR>";
		//do
		//{
			$refers = current ($matches_traca);
			echo $refers[3]."<BR>";
		//}while ($refers[2] == $val[2]);
				
		echo  "Rationale:".$val[4]."<BR>";
		echo  "Body:".$val[5]."<BR>";
		for ($index = 1; $index <= 10; $index++) {
			//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($index,$row_counter, $val[$index]);
		}
		$row_counter++;
	}
/* count requirerments */
//$objPHPExcel->getActiveSheet()->setCellValue('B14','=NBVAL(A,A)');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$filename = "Traca_{$today_date}.xlsx";
$objWriter->save('../../result/'.$filename);
//$objWriter->save('php://output'); 

// Echo done
echo date('H:i:s') . " Done writing remarks.<br />";
echo " Peer review generated at ".date('H:i:s')." with peak memory usage: " . (memory_get_peak_usage(true) / 1024 / 1024) . " MB\r\n";
}
/* 
 * B787 SwRD 
 * 
 */
function Display_B787_SWRD_ELCU_P_Req ($text)
{
	global $db_select;
	global $today_date;
	global $file_template;
	global $row_counter;
	
error_reporting(E_ALL);
$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
PHPExcel_Settings::setCacheStorageMethod($cacheMethod);
/** PHPExcel_IOFactory */
require_once '../../excel/Classes/PHPExcel/IOFactory.php';
/** PHPExcel_IOFactory */
require_once '../../excel/Classes/PHPExcel/Worksheet/RowIterator.php';

if (!file_exists($file_template)) {
	exit("SAQ225 template is missing.\n");
}

$objPHPExcel = PHPExcel_IOFactory::load($file_template);
		
$objPHPExcel->setActiveSheetIndex(0);
$styleArray = array(
	'font' => array(
		'bold' => true,
	),
	'alignment' => array(
		'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
	),
	// 'borders' => array(
		// 'top' => array(
			// 'style' => PHPExcel_Style_Border::BORDER_THIN,
		// ),
	// ),
	// 'fill' => array(
		// 'type' => PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR,
		// 'rotation' => 90,
		// 'startcolor' => array(
			// 'argb' => 'FFA0A0A0',
		// ),
		// 'endcolor' => array(
			// 'argb' => 'FFFFFFFF',
		// ),
	// ),
);
$objPHPExcel->getActiveSheet()->getStyle('A27:J500')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('A27:J500')->getAlignment()->setWrapText(true);

$title = "";
$reference = "";
$username = "";
$objPHPExcel->getActiveSheet()->setCellValue('A3', $title);
$objPHPExcel->getActiveSheet()->setCellValue('A4', $reference);

/* date of reading */
$objPHPExcel->getActiveSheet()->setCellValue('A5', $today_date);
/* name of the reader */
$objPHPExcel->getActiveSheet()->setCellValue('A6', $username);


$objPHPExcel->getProperties()->setCreator($username)
							 ->setLastModifiedBy($username)
							 ->setTitle("peer".$title."review")
							 ->setSubject("Ref:".$reference)
							 ->setDescription("Peer review report for ".$title)
							 ->setKeywords("PRR openxml php")
							 ->setCategory("Peer Review Report");
							 
$regexp_req = "((Covered by SMP &amp; PSAC|SwRD_[A-Z]{2,4}_?[A-Z]{2,12}?_?[A-Z]{2,12}?_[0-9]{3})(_[MNS]|)";
$regexp_ref = "([-/A-Za-z]{2,12}_[-/A-Za-z]{2,12}(_[-/A-Za-z]{1,12})?(_[-/A-Za-z]{2,12})?(_[0-9]{3}_[0-9]{2}|_[0-9]{3})|Derived|\w{2,7}_Missing_Requirement)(?:_[MNS]|))";
$regexp_rat = "Justification:( ?NA|.+[^0-9]\.)";
$regexp_bod = "(.*)";
$regexp_end = "End_SwRD_Req";
$regular_expression = $regexp_req.$regexp_ref;//.$regexp_rat.$regexp_bod.$regexp_end ;
$regular_expression_2 = $regexp_req.$regexp_ref.$regexp_rat.$regexp_bod.$regexp_end ;
$regular_expression_3 = "(Covered)";
$source1="Covered by SMP & PSACSSCS_BOARD_MODU_CTRL_008Justification:NAtototitiEnd_SwRD_ReqSwRD_SSPC_SAFETY_001SSCS_BOARD_SOFT_002";
$source2="SwRD_SSPC_RESOURCE_002_NDerivedJustification:NAtatatetetiEnd_SwRD_ReqSwRD_SSPC_SAFETY_001SSCS_Missing_Requirement";
$source=$source1.$source2;
/* remove REF _Ref163013971 \h */
$text2 = preg_replace("#REF \_Ref[0-9]{9} ?.?r? .h #","",$text);
$source=$text2;
//echo $source;
preg_match_all("#".$regular_expression."#U",$source, $matches_traca, PREG_SET_ORDER);


	$header_fields = array("","Id","Body", "Refers to", "Status", "Safety", "Derived", "Allocation","Rationale","Npl_Refers to","Additional Information");
	for ($index = 1; $index <= 10; $index++) {
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($index,$row_counter, $header_fields[$index]);
	}

	$row_counter++;
	preg_match_all("#".$regular_expression_2."#U",$source, $matches, PREG_SET_ORDER);
	foreach ($matches as $val) {
		//echo  "<BR><BR><BR>";
		//echo $val[0];
		/* remove SWRD tag and copy reference to ref */
		//preg_match ("#".$regexp_req."#",$val[1],$id);
		//$add_ref = preg_replace("#".$regexp_req."#","",$val[1]);
		//echo  "ID:".$val[2]."<BR>";
		//echo  "Refers to: ";//.$val[3]."<BR>";
		$refers = current ($matches_traca);
		$counter = 0;
		$refers_to = "";
		//echo "check:".$refers[2]." vs ".$val[2]."<BR>";
			
		while ($refers[2] == $val[2]){		
			//echo $refers[0]."<BR>";
			//echo $refers[2]."<BR>";
			//echo ":".$refers[0]."<BR>";	
			$refers_to = $refers_to.$refers[4]."\n";
			//echo "Refers to: ".$refers[4]."<BR>";
			$refers = next ($matches_traca);
			$counter = $counter + 1;
			if ($counter > 20)
				break;
		};
		switch ($val[3]) {
		case "_M":
			$status="Modified";	
			break;
		case "_N":
			$status="New";	
			break;
		case "_S":
			$status="Suppressed";	
			break;
		default:
			$status="NA";	
			break;			
		}
		
		//echo  "Status:".$status."<BR>";
		//$refers = prev ($matches_traca);	
		//echo  "Rationale:".$val[8]."<BR>";
		//echo  "Body:".$val[9]."<BR>";
		/* ID */
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1,$row_counter, $val[2]);
		/* Body */
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2,$row_counter, $val[9]);
		/* Refers to */
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3,$row_counter, $refers_to);
		/* Status */
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4,$row_counter, $status);
		/* Derived */
		if (preg_match("#Derived#", $refers_to)) {
			$derived="YES";
		} else {
			$derived="";
		}
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6,$row_counter, $derived);
		/* Rationale */
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8,$row_counter, $val[8]);
		for ($index = 1; $index <= 10; $index++) {
			//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($index,$row_counter, $val[$index]);
		}
		$row_counter++;
	}
/* count requirerments */
//$objPHPExcel->getActiveSheet()->setCellValue('B14','=NBVAL(A,A)');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$filename = "Traca_{$today_date}.xlsx";
$objWriter->save('../../result/'.$filename);
//$objWriter->save('php://output'); 

// Echo done
echo " Requirements extracted ".date('H:i:s')." with peak memory usage: " . (memory_get_peak_usage(true) / 1024 / 1024) . " MB<BR>";
}
/* 
 * EC175 SSCS PLB 
 * 
 */
function Display_EC175_SSCS_PLB_Req ($text)
{
	global $db_select;
	global $today_date;
	global $file_template;
	global $row_counter;
	
error_reporting(E_ALL);
$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
PHPExcel_Settings::setCacheStorageMethod($cacheMethod);
/** PHPExcel_IOFactory */
require_once '../../excel/Classes/PHPExcel/IOFactory.php';
/** PHPExcel_IOFactory */
require_once '../../excel/Classes/PHPExcel/Worksheet/RowIterator.php';

if (!file_exists($file_template)) {
	exit("SAQ225 template is missing.\n");
}

$objPHPExcel = PHPExcel_IOFactory::load($file_template);
		
$objPHPExcel->setActiveSheetIndex(0);
$styleArray = array(
	'font' => array(
		'bold' => true,
	),
	'alignment' => array(
		'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
	),
);
$objPHPExcel->getActiveSheet()->getStyle('A27:J500')->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('A27:J500')->getAlignment()->setWrapText(true);

$title = "";
$reference = "";
$username = "";
$objPHPExcel->getActiveSheet()->setCellValue('A3', $title);
$objPHPExcel->getActiveSheet()->setCellValue('A4', $reference);

/* date of reading */
$objPHPExcel->getActiveSheet()->setCellValue('A5', $today_date);
/* name of the reader */
$objPHPExcel->getActiveSheet()->setCellValue('A6', $username);


$objPHPExcel->getProperties()->setCreator($username)
							 ->setLastModifiedBy($username)
							 ->setTitle("peer".$title."review")
							 ->setSubject("Ref:".$reference)
							 ->setDescription("Peer review report for ".$title)
							 ->setKeywords("PRR openxml php")
							 ->setCategory("Peer Review Report");
							 
//$regexp_req = "((SSCS_PLB_(.+))(SES_EMB(.+)))|((SSCS_(.+))(derived))";
$regexp_req = "(SSCS_PLB(.){8,64})(SES_EMB(.){8,64}|TBD(.){8,64})";
$regexp_ref = "";
$regexp_alloc = "(?:Attribute allocation ?: ?FPGA_FUNC)"; 
$regexp_crit = "(?:Critical function ?: ?yes)";
$regexp_bod = "(.*)";
//$regexp_end = "End_SwRD_Req";
$regular_expression = $regexp_req.$regexp_alloc.$regexp_crit;
//$regular_expression_2 = $regexp_req.$regexp_ref.$regexp_rat.$regexp_bod.$regexp_end ;
$regular_expression_3 = "(Covered)";
$source3="TUS = LoSSCS_PLB_7_GLC control 2SES_EMB_7_GLC control 2Attribute allocation: FPGA_FUNCCritical function : yes";

$source1="Covered by SMP & PSACSSCS_BOARD_MODU_CTRL_008Justification:NAtototitiEnd_SwRD_ReqSwRD_SSPC_SAFETY_001SSCS_BOARD_SOFT_002";
$source2="SwRD_SSPC_RESOURCE_002_NDerivedJustification:NAtatatetetiEnd_SwRD_ReqSwRD_SSPC_SAFETY_001SSCS_Missing_Requirement";
$source=$source3;
/* replace tabulation by spaces */
$text2 = preg_replace('/\\t/i', '    ', $text);
/* remove REF _Ref163013971 \h */
//$text2 = preg_replace("#REF \_Ref[0-9]{9} ?.?r? .h #","",$text);
$source=$text2;
//echo "<BR>".$source."<BR>".$regular_expression."<BR>";
preg_match_all("#".$regular_expression."#U",$source, $matches_traca, PREG_SET_ORDER);
echo "<BR>";
foreach ($matches_traca as $val) {
		echo "<BR>Critical req:".$val[0]."<BR>";
		echo $val[1]."<BR>";
		$row_counter++;
}
	$header_fields = array("","Id","Body", "Refers to", "Status", "Safety", "Derived", "Allocation","Rationale","Npl_Refers to","Additional Information");
	for ($index = 1; $index <= 10; $index++) {

		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($index,$row_counter, $header_fields[$index]);
	}

	

/* count requirerments */
//$objPHPExcel->getActiveSheet()->setCellValue('B14','=NBVAL(A,A)');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$filename = "Traca_{$today_date}.xlsx";
$objWriter->save('../../result/'.$filename);
//$objWriter->save('php://output'); 

// Echo done
echo "<BR>".$row_counter." requirements extracted ".date('H:i:s')." with peak memory usage: " . (memory_get_peak_usage(true) / 1024 / 1024) . " MB<BR>";
}
//$text = docx2text ("C:\\xampplite\\A350_EPDS_ENMU_SWDD_ET1816-E_Issue_1.docx");
//echo Display_Req($text);
$today_date = date("d").' '.date("F").' '.date("Y");
$file_template = "../../template/hpr_template.xlsx";
$row_counter = 7;

require_once "../../phpfileuploader/phpuploader/include_phpuploader.php";
   
//Gets the GUID of the file based on uploader name   
//$fileguid=@$_POST["myuploader"];
$guidarray=explode("/",$_POST["myuploader"]);     
//get the uploaded file based on GUID   
$uploader=new PhpUploader();
$count=0;
   	  /*
   	   * Generation excel
   	   */
  		error_reporting(E_ALL);
		$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
		PHPExcel_Settings::setCacheStorageMethod($cacheMethod);
		/** PHPExcel_IOFactory */
		require_once '../../excel/Classes/PHPExcel/IOFactory.php';
		
		/** PHPExcel_IOFactory */
		require_once '../../excel/Classes/PHPExcel/Worksheet/RowIterator.php';
		
		if (!file_exists($file_template)) {
			exit("HPR template is missing.\n");
		}
		
		$objPHPExcel = PHPExcel_IOFactory::load($file_template);
				
		$objPHPExcel->setActiveSheetIndex(0);
		$styleArray = array(
			'font' => array(
				'bold' => true,
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP,
			),
			'borders' => array(
				'top' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
				),
			),
			'fill' => array(
				'type' => PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR,
				'rotation' => 90,
				'startcolor' => array(
					'argb' => 'FFA0A0A0',
				),
				'endcolor' => array(
					'argb' => 'FFFFFFFF',
				),
			),
		);
		$title = "";
		$reference ="";
		$username = "";
		$objPHPExcel->getActiveSheet()->setCellValue('C8', $title);
		$objPHPExcel->getActiveSheet()->setCellValue('C9', $reference);
		
		/* date of reading */
		$objPHPExcel->getActiveSheet()->setCellValue('D17', $today_date);
		/* name of the reader */
		$objPHPExcel->getActiveSheet()->setCellValue('F17', $username);
		
		$objPHPExcel->getProperties()->setCreator($username)
									 ->setLastModifiedBy($username)
									 ->setTitle("HPR")
									 ->setSubject("Ref:".$reference)
									 ->setDescription("HPR Synthesis")
									 ->setKeywords("HPR")
									 ->setCategory("HPR tracking");

		$row_counter = 26;
		$index = 0;
		$header_fields = array("Project","Ref","Issue", "Type", "Severity", "Status","Synopsis" ,"Author","Department","Date raised","HW Part Number","Checksum","Following","Description","Linked HPR","Analysis","Implementation","Date Visa","Date Closed","Assign","EPR Follow-up","Amendment");
		foreach ($header_fields as $field_name){
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($index++,$row_counter, $field_name);
		}
	
		$row_counter = 27;
		function Get_String_Index($string,$content) {
		  $index=0;
		  foreach ($content as $sub_part) {
				if(preg_match($string,$sub_part)) {
					break;
				}
				$index++;
		  } 	  
		return($index);
	  }
foreach($guidarray as $fileguid)
{	 
	 $mvcfile=$uploader->GetUploadedFile($fileguid);   
	 if($mvcfile)   
	 {   
		$extensionFichier = pathinfo($mvcfile->FileName, PATHINFO_EXTENSION);
		$nameFichier = pathinfo($mvcfile->FileName, PATHINFO_FILENAME);
		//Moves the uploaded file to a new location.   
		/* type word 2007 */
		$mvcfile->MoveTo("../../tmp");
		$uploadName = "../../tmp/".$mvcfile->FileName;
		$text = docx2text ($uploadName);

	//echo "old method:".$text."<br/>";
	//echo Display_A350_SWRD_ENMU_Req($text);
	//echo Display_Legacy450_SWRD_SSPC_Req($text);
	//echo Display_B787_SWRD_ELCU_P_Req($text);
	//echo Display_EC175_SSCS_PLB_Req($text);

require_once('openxml.class.php');

   echo "<b><u>$uploadName</u></b><br/>";
   
   try  {

      $mydoc = OpenXMLDocumentFactory::openDocument($uploadName);
   
      echo '<br/><i>Metadonnées :</i><br/><br/>';
      echo 'Créateur: ' . $mydoc->getCreator() . '<br/>';
      echo 'Sujet: ' . $mydoc->getSubject() . '<br/>';
      echo 'Mots-clés: ' . $mydoc->getKeywords() . '<br/>';
      echo 'Description: ' . $mydoc->getDescription() . '<br/>';
      echo 'Date de création : ' . $mydoc->getCreationDate() . '</br>';
      echo 'Date de dernière modification : ' . $mydoc->getLastModificationDate() . '<br/>';
      echo 'Modifié en dernier par: ' . $mydoc->getLastWriter() . '<br/>';
      echo 'Révision: ' . $mydoc->getRevision() . '<br/>';
         
      echo '<br/><i>Propriétés du document:</i><br/><br/>';
      
      echo 'Généré par: ' . $mydoc->getApplication() . '<br/>';
   
      $document_class = get_class($mydoc); 
      
      if ($document_class == 'WordDocument') {
      
   
         echo 'Nombre de paragraphes: ' . $mydoc->getNbOfParagraphs() . '<br />';
         echo 'Nombre de caractères: ' . $mydoc->getNbOfCharacters() . '<br />';
         echo 'Nombre de caractères (avec les espaces): ' . $mydoc->getNbOfCharactersWithSpaces() . '<br/>';
         echo 'Nombre de pages: ' . $mydoc->getNbOfPages() . '<br/>';
         echo 'Nombre de mots: ' . $mydoc->getNbOfWords() . '<br/>';
         
      }
      
      echo '<br/><i>Aperçu du document:</i> <br/>';    
      /*
       * Display HPR Header
       */
      $hpr_header  =  $mydoc->getHTMLHeaderPreview();  
      //echo $hpr_header."<br/>";
      //echo "<br/><br/>";
      preg_match("/PROJECT: (.+)[H|E]PR/Us",$hpr_header,$array_project);  	  
      preg_match("/Reference: (.+)Issue/Us",$hpr_header,$array_reference); 
      preg_match("/Issue:(.+)Page/Us",$hpr_header,$array_issue); 
	  //print_r($array_issue);
      preg_match_all("#(.*);#U",$hpr_header,$header_content);  
      //echo $header_content[0][1]."<br/><br/>";
      //echo $header_content[0][2]."<br/>";
      //echo $header_content[0][3]."<br/>";
      $project = str_replace(";","",$array_project[1]);
      $reference = str_replace(";","",$array_reference[1]);
      $issue = str_replace(";","",$array_issue[1]);
      echo "Project= ".$project."<br/>";
      echo "Ref    = ".$reference."<br/>";
      echo "Issue  = ".$issue."<br/>";
      //preg_match("",$hpr_content,$hpr_content); 
      /*
       * Display HPR Content
       */ 
      $hpr_content = $mydoc->getHTMLPreview(); 
      //echo $hpr_content."<br/>";
      /*
       * Checkbox
       */ 
      preg_match_all("#Checkbox:(.*)FORMCHECKBOX#U",$hpr_content,$checkbox_status_rought); 
      //print_r($checkbox_status_rought)."<br/>";   
      for ($index = 0; $index <= 12; $index++) {
      	if (($checkbox_status_rought[1][$index] == "1: ") || ($checkbox_status_rought[1][$index] == "0:: ")){
      		$checkbox_status[$index] = true;
      	}
      	else {
      		$checkbox_status[$index] = false;
      	}
      	
      }
      echo "Defect= ".$checkbox_status[0]."<br/>";
      echo "Blocking= ".$checkbox_status[1]."<br/>";
      echo "Raised= ".$checkbox_status[2]."<br/>";
      echo "In progress= ".$checkbox_status[3]."<br/>";
      echo "Fixed= ".$checkbox_status[4]."<br/>";
      echo "Closed= ".$checkbox_status[5]."<br/>";
      echo "Major= ".$checkbox_status[6]."<br/>";
      echo "Postponed= ".$checkbox_status[7]."<br/>";
      echo "Workaround= ".$checkbox_status[8]."<br/>";
      echo "Change= ".$checkbox_status[9]."<br/>";
      echo "Mnor= ".$checkbox_status[10]."<br/>";
      echo "Rejected= ".$checkbox_status[11]."<br/>";  
      echo "Enhancement= ".$checkbox_status[12]."<br/>";    
      /*
       * Technical Fact Description
       */    
      //preg_match_all("#Node 005A2BBD00285EC5005A2BBD;(.*)#isu",$hpr_content,$content);  
      $full_content = explode("Node 005A2BBD00285EC5005A2BBD;",$hpr_content);
      //print_r($hpr_content);
      foreach ($full_content as $sub_content) {

      	/* Replace NODE by carriage return */
      	$content_inter = preg_replace("/(Node \w{1,32};)(.*);/","$2\n",$sub_content);
      	/* Remove FORMTEXT */
      	$content[] = preg_replace("/(FORMTEXT)/s","",$content_inter);
      }
      //echo "Test Content<br/><br/>";
      print_r($content);
      echo "<br/><br/>";
      /*
       * Miscelleanous info
       */   
      preg_match_all("#(.*);#U",$hpr_content,$body_content); 
	  //print_r($body_content[1]);
	  $synopsis = preg_replace("/(FORMTEXT)/s","",$body_content[1][3]);
      echo "Synopsis= ".$synopsis."<br/>";
      $date = preg_replace("/(Date:)/","",$body_content[1][123]);
	  $date = preg_replace("/(FORMTEXT)/s","",$date);
      echo "Date Raised    = ".$date."<br/>";
	  $date_closed = preg_replace("/(Date:)/","",$body_content[1][127]);
	  $date_closed = preg_replace("/(FORMTEXT)/s","",$date_closed);
      echo "Date Closed    = ".$date_closed."<br/>";
	  /*
	   * Date visa
	   */
	  $index=Get_String_Index("/(Author Visa)/Us",&$content);
	  preg_match("/Date:(.+)HW Part Number/Us",$content[$index],$array_date_visa);  
	  $date_visa = str_replace(";","",$array_date_visa[1]);
      echo "Date Visa    = ".$date_visa."<br/>";
	  $author = preg_replace("/(FORMTEXT)/s","",$body_content[1][133]);
      echo "Author  = ".$author."<br/>";
	  $department = preg_replace("/(FORMTEXT)/s","",$body_content[1][137]);
      echo "Department  = ".$department."<br/>";
	   /*
   	   * Search for Assignment
   	   */
	  $index=Get_String_Index("/(Assignment:)/",&$content);
	  $tab1 = explode("EPR Follow-up", $content[$index]);
	  preg_match("#Assignment:(.+)#s",$tab1[0],$array_assignment); 
	  $assignment = str_replace(";","",$array_assignment[1]);
	  $assignment = str_replace("REF PRName","",$assignment);
	  $assignment = str_replace("MERGEFORMAT","",$assignment);
	  $assignment = str_replace("\h","",$assignment);
	  $assignment = str_replace("\*","",$assignment);
	  $assignment = str_replace("\n","",$assignment);
	  $assignment = str_replace("\r","",$assignment);
      echo "Assignment = ".$assignment."<br/>";
	   /*
   	   * Search for EPR Follow-up
   	   */
	  $index=Get_String_Index("/(Follow-up)/",&$content);
	  $tab2 = explode("Evolution", $content[$index]);
	  if ($tab2 == "") {
		$string = $content[$index];
	  }
	  else {
		$string = $tab2[0];
	  }
	  preg_match("#Follow-up:(.+)#s",$string,$array_follow_up); 
	  $follow_up = str_replace(";","",$array_follow_up[1]);
	  $follow_up = str_replace("\n","",$follow_up);
	  $follow_up = str_replace("\r","",$follow_up);
      echo "Follow-up = ".$follow_up."<br/>";	  
  	  /*
   	   * Search for HW Part Number
   	   */
	  $index=Get_String_Index("/(HW Part Number \(or Document\):)/",&$content);
	  $tab3 = explode("Amendment", $content[$index]);
	  preg_match("#HW Part Number \(or Document\):(.+)#s",$tab3[0],$array_hw_part_number); 
	  $hw_part_number = str_replace(";","",$array_hw_part_number[1]);
      //$hw_part_number = preg_replace("/(HW Part Number \(or Document\):)/","",$array_hw_part_number);
	  //$hw_part_number = $body_content[1][149];
      echo "HW Part Number (or Document) = ".$hw_part_number."<br/>";
	  /*
	   * Amendment
	   */	
	  $index=Get_String_Index("/(Amendment \(or Issue\))/Us",&$content);
	  $tab4 = explode("Checksum", $content[$index]);
	  //echo"TERST:";print_r($tab4 );
	  preg_match("#Amendment \(or Issue\):(.+)#s",$tab4[0],$array_amendment);  
	  $amendment = str_replace(";","",$array_amendment[1]);
      echo "Amendment (or Issue) = ".$amendment."<br/>";
	  /*
	   * Checksum
	   */
	  $index=Get_String_Index("/(Checksum:)/Us",&$content);
	  $tab5 = explode("Signature", $content[$index]);
	  preg_match("#Checksum:(.+)#s",$tab5[0],$array_checksum);  
	  $checksum = str_replace(";","",$array_checksum[1]);
	  $checksum = str_replace("FORMTEXT","",$checksum);
   	  echo "Checksum = ".$checksum."<br/>";
   	  /*
   	   * Search for following
   	   */  
	  $index=Get_String_Index("/(Following:)/Us",&$content);  
	  $tab6 = explode("Technical Fact Description", $content[$index]);
   	  preg_match("#Following:(.+)#s",$tab6[0],$array_following);
	  $following = str_replace(";","",$array_following[1]);
	  $following = str_replace("\n","",$following);
	  $following = str_replace("\r","",$following);	  
   	  echo "Following = ".$following."<br/>";
   	  /*
   	   * Search for tech_description
   	   */ 
	  $index=Get_String_Index("/(Technical Fact Description:)/Us",&$content);  	 
	  $tab7 = explode("Linked", $content[$index]);
   	  preg_match("#Technical Fact Description:(.+)()*#s",$tab7[0],$array_tech_description);
	  $tech_description = str_replace(";","",$array_tech_description[1]);
	  $tech_description   = substr($tech_description,0,100)." etc ...";
   	  echo "Technical Fact Description = ".$tech_description."<br/>";
   	  /*
   	   * Search for linked_hpr
   	   */
	  $index=Get_String_Index("/(Linked)/Us",&$content);
	  $tab8 = explode("Analysis", $content[$index]);
	  preg_match("/Linked(.+)/Us",$tab8[0],$array_linked_hpr);
	  $linked_hpr = str_replace(";","",$array_linked_hpr[1]);
	  $linked_hpr = str_replace("FORMTEXT","",$linked_hpr);	  
	  $linked_hpr = str_replace(";","",$linked_hpr);  
   	  echo "Linked HPR = ".$linked_hpr."<br/>";
      /*
   	   * Search for expected_modif
   	   */
 	  $index=Get_String_Index("/(Analysis \/ Expected Modification:)/Us",&$content);
	  $tab8 = explode("Evolution \/ Implemented Modification", $content[$index]);
   	  preg_match("#Analysis \/ Expected Modification:(.+)*#s",$tab8[0],$array_expected_modif);
		//preg_match("/Analysis \/ Expected Modification:(.+)/",$content[$index],$array_expected_modif);
	  $expected_modif = str_replace(";","",$array_expected_modif[1]);
	  $expected_modif   = substr($expected_modif,0,100)." etc ...";
   	  echo "Analysis \ Expected Modification = ".$expected_modif."<br/>";
      /*
   	   * Search for implementation
   	   */	 
	  $index=Get_String_Index("/(Evolution \/ Implemented Modification:)/Us",&$content); 	  
   	  $implementation = preg_replace("/(Evolution \/ Implemented Modification:)/","",$content[$index]);
   	  /* Remove Date */
   	  $implementation = preg_replace("/(Date:.DD MM YY)/","",$implementation);
	  $implementation = str_replace(";","",$implementation);
	  $implementation   = substr($implementation,0,100)." etc ...";
   	  echo "Evolution / Implemented Modification = ".$implementation."<br/>";
   	  
		$index = 0;
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($index++,$row_counter, $project);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($index++,$row_counter, $reference);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($index++,$row_counter, $issue);
		if ($checkbox_status[0]) {
			$type = "Defect";
		}
		else if ($checkbox_status[9]) {
			$type = "Change";
		}	
		else {
			$type = "";
		}			
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($index++,$row_counter, $type);
		if ($checkbox_status[1]) {
			$severity = "Blocking";
		}
		else if ($checkbox_status[6]) {
			$severity = "Major";
		}
		else if ($checkbox_status[10]) {
			$severity = "Minor";
		}
		else if ($checkbox_status[12]) {
			$severity = "Enhancement";
		}		
		else {
			$severity = "";
		}					
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($index++,$row_counter, $severity);
		$counter_check = 0;
		$status = "No status";
		if ($checkbox_status[2]) {
			$status = "Raised";
			$counter_check++;
		}
		if ($checkbox_status[3]) {
			$status = "In progress";
			$counter_check++;
		}
		if ($checkbox_status[4]) {
			$status = "Fixed";
			$counter_check++;
			$objPHPExcel->getActiveSheet()->getStyle('F'.$row_counter)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$objPHPExcel->getActiveSheet()->getStyle('F'.$row_counter)->getFill()->getStartColor()->setARGB('0000FF');
		}
		if ($checkbox_status[5]) {
			$status = "Closed";
			$counter_check++;
			$objPHPExcel->getActiveSheet()->getStyle('F'.$row_counter)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$objPHPExcel->getActiveSheet()->getStyle('F'.$row_counter)->getFill()->getStartColor()->setARGB('00FF00');
		}		
		if ($checkbox_status[7]) {
			$status = "Postponed";
			$counter_check++;
		}
		if ($checkbox_status[8]) {
			$status = "Workaround";
			$counter_check++;
		}	
		/*
		 * Test several checkboxes selected
		 */
		if ($counter_check > 1) {
			$objPHPExcel->getActiveSheet()->getStyle('F'.$row_counter)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
		}	
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($index++,$row_counter, $status);		
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($index++,$row_counter, $synopsis);	
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($index++,$row_counter, $author);	
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($index++,$row_counter, $department);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($index++,$row_counter, $date);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($index++,$row_counter, $hw_part_number);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($index++,$row_counter, $checksum);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($index++,$row_counter, $following);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($index++,$row_counter, $tech_description);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($index++,$row_counter, $linked_hpr);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($index++,$row_counter, $expected_modif);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($index++,$row_counter, $implementation);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($index++,$row_counter, $date_visa);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($index++,$row_counter, $date_closed);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($index++,$row_counter, $assignment);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($index++,$row_counter, $follow_up);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($index++,$row_counter, $amendment);
		$row_counter++;
		//Deletes the file.   
		//$mvcfile->Delete(); 
	   
   }
   catch (OpenXMLFatalException $e) {
   
      echo $e->getMessage();
   
   }
	unset($content);
	unset($hpr_content);
	unset($content);
	//unlink($uploadName);
	 }
}
	$objPHPExcel->getActiveSheet()->getStyle('A27:V'.$row_counter)->applyFromArray($styleArray);
	$objPHPExcel->getActiveSheet()->getStyle('A27:V'.$row_counter)->getAlignment()->setWrapText(true);
  	/* To apply an autofilter to a range of cells */
 	$objPHPExcel->getActiveSheet()->setAutoFilter('A26:V26');
	/* count requirerments */
	//$objPHPExcel->getActiveSheet()->setCellValue('B14','=NBVAL(A,A)');
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$filename = "HPR_{$today_date}.xlsx";
	$objWriter->save('../../result/'.$filename);
	//$objWriter->save('php://output'); 
	
	// Echo done
	echo date('H:i:s') . " Done writing HPR.<br />";
	echo " Document generated at ".date('H:i:s')." with peak memory usage: " . (memory_get_peak_usage(true) / 1024 / 1024) . " MB\r\n"; 
   echo '<br/><br/>';
   
//} else {
//   echo "File not saved.  It's too big!  Max filesize is $maxSize";
//}


?>
<a href="<?php echo '../../result/HPR_'.$today_date.'.xlsx' ?>" >
<img alt="Export openxml" title="Export openxml" border=0 src="../../images/excel_sheet.png" class='img_button'
   onmouseover="this.style.margin='0px';this.style.width='64px';this.style.height='64px';"
   onmouseout="this.style.margin='8px';this.style.width='48px';this.style.height='48px';" />
</a>                     

<div style="height:20px;"></div>
</div> <!-- content -->
<?php include "../../includes/footer.php"; ?>
</body>
</html>