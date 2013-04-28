/* A350 SWDD ENMU */
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
/* A350 SWRD ENMU */
function Display_A350_SWRD_ENMU_Req ($text)
{
	global $db_select;
	global $today_date;
	global $file_template;
	
	error_reporting(E_ALL);
	$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
	PHPExcel_Settings::setCacheStorageMethod($cacheMethod);
	/** PHPExcel_IOFactory */
	//require_once '../../excel/Classes/PHPExcel/IOFactory.php';

	/** PHPExcel_IOFactory */
	//require_once '../../excel/Classes/PHPExcel/Worksheet/RowIterator.php';

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
/* Legacy450 SWRD SSPC */
function Display_Legacy450_SWRD_SSPC_Req ($text){
	global $db_select;
	global $today_date;
	global $file_template;
	global $row_counter;
	
	error_reporting(E_ALL);
	$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
	PHPExcel_Settings::setCacheStorageMethod($cacheMethod);
	/** PHPExcel_IOFactory */
	//require_once '../../excel/Classes/PHPExcel/IOFactory.php';

	/** PHPExcel_IOFactory */
	//require_once '../../excel/Classes/PHPExcel/Worksheet/RowIterator.php';
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
/* B787 SwRD ELCU_P */
function Display_B787_SWRD_ELCU_P_Req ($text){
	global $db_select;
	global $today_date;
	global $file_template;
	global $row_counter;
	
	error_reporting(E_ALL);
	$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
	PHPExcel_Settings::setCacheStorageMethod($cacheMethod);
	/** PHPExcel_IOFactory */
	//require_once '../../excel/Classes/PHPExcel/IOFactory.php';
	/** PHPExcel_IOFactory */
	//require_once '../../excel/Classes/PHPExcel/Worksheet/RowIterator.php';

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
/* EC175 SSCS PLB */
function Display_EC175_SSCS_PLB_Req ($text)
{
	// global $db_select;
	$today_date="";
	// global $file_template;
	$row_counter=0;
	
	$file_template = dirname(__FILE__).
							DIRECTORY_SEPARATOR."..".
							DIRECTORY_SEPARATOR."..".
							DIRECTORY_SEPARATOR."atomik".
							DIRECTORY_SEPARATOR."assets".
							DIRECTORY_SEPARATOR."template".
							DIRECTORY_SEPARATOR."SAQ225_2.xlsx";
							
	error_reporting(E_ALL);
	$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
	PHPExcel_Settings::setCacheStorageMethod($cacheMethod);
	/** PHPExcel_IOFactory */
	//require_once '../../excel/Classes/PHPExcel/IOFactory.php';
	/** PHPExcel_IOFactory */
	//require_once '../../excel/Classes/PHPExcel/Worksheet/RowIterator.php';

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