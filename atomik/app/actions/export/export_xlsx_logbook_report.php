<?php	
Atomik::needed ("Db.class");
Atomik::needed ("User.class");
Atomik::needed ("Data.class");
Atomik::needed ("Date.class");
Atomik::needed ("Action.class");
Atomik::needed ("Remark.class");
Atomik::needed ("Logbook.class");
Atomik::needed ("Review.class");
Atomik::needed ("Baseline.class");
Atomik::needed ("PeerReviewer.class");
Atomik::needed ("Tool.class");
require_once "../excel/Classes/PHPExcel.php";
require_once '../excel/Classes/PHPExcel/IOFactory.php';
require_once '../excel/Classes/PHPExcel/Worksheet/RowIterator.php';
Atomik::needed ("ExportXls.class");
include("app/includes/ExportXls.class.php");

$path_result="../../result/";
error_reporting(E_ALL);
$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
PHPExcel_Settings::setCacheStorageMethod($cacheMethod);

$file_template = "assets/template/logbook_template.xlsx";
if (!file_exists($file_template)) {
	echo "<BR><b>Warning: Assurance_Process_Dashboard template is missing.</b><BR>";
	$objPHPExcel = new PHPExcel;
}
else {
	$objPHPExcel = PHPExcel_IOFactory::load($file_template);
}
function display_grey_header($phpexel_obj,$row){
  $phpexel_obj->getActiveSheet()->getStyle($row)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_BLACK);
  $phpexel_obj->getActiveSheet()->getStyle($row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
  $phpexel_obj->getActiveSheet()->getStyle($row)->getFill()->getStartColor()->setARGB('BBBBBB');
}
$context_array['project_id']= isset($_GET['show_project']) ? $_GET['show_project'] : (Atomik::has('session/current_project_id')?Atomik::get('session/current_project_id'):Atomik::get('session/project_id'));
$context_array['sub_project_id'] = Atomik::has('session/sub_project_id')?Atomik::get('session/sub_project_id'):"";
$context_array['baseline_id'] = Atomik::has('session/baseline_id')?Atomik::get('session/baseline_id'):"";
$context_array['assignee_id'] = Atomik::has('session/user_id')?Atomik::get('session/user_id'):"";
Tool::deleteKey('session/search');
/* Create logbook */
$logbook = new Logbook(&$context_array);
$today_date = date("d").' '.date("M").' '.date("Y");
$excel_filename = $logbook->filename;
$filename="../result/".$excel_filename;
/*
 *  Intro
 */   
$today_date = date("d").' '.date("M").' '.date("Y");
$objPHPExcel->setActiveSheetIndex(0);
$objPHPExcel->getActiveSheet()->setCellValue('A1', $logbook->title);
$objPHPExcel->getActiveSheet()->setCellValue('A4', $logbook->title);
$objPHPExcel->getActiveSheet()->setCellValue('A2', "Reference: ".$logbook->ref);
$objPHPExcel->getActiveSheet()->setCellValue('C2', "Issue: ".$logbook->issue);
$objPHPExcel->getActiveSheet()->setCellValue('C12', $logbook->author);
$objPHPExcel->getActiveSheet()->setCellValue('E12', $today_date);
/*
 *  Data list
 */ 
$objPHPExcel->setActiveSheetIndex(1);
$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 1,"Documents Lists");
$objPHPExcel->getActiveSheet()->mergeCells('A1:J1');
$header=array("Reference", "Issue", "Type","Author" ,"Description","Released Date","Review Deadline","Status","QA Acceptance","Peer reviews");
for($i=0;$i<count($header);$i++) {
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, 2, $header[$i]);
}
display_grey_header(&$objPHPExcel,'A2:J2');
/* Do not display out-dated or not published document */
$show_stat = "AND status != '40' AND status != '41' ";
$data = new Data(&$context_array);
$all = Atomik::has('session/see_all_data')?Atomik::get('session/see_all_data'):"yes";
$amount_data = $data->count_data();

$row_counter = 3;
$db= new Db;
/* draw thick border around the actions */
$last_column = "J";
$objPHPExcel->getActiveSheet()->getStyle('A2:'.$last_column.strval($amount_data + 2))->applyFromArray($style_encadrement);
foreach($data->getData(PDO::FETCH_OBJ) as $document):
	if ($row_counter % 2) {
		/* alternate white and grey line color */
		$objPHPExcel->getActiveSheet()->getStyle('A'.$row_counter.':'.$last_column.$row_counter)->applyFromArray($style_white_line);
	}
    /* get data remarks statistics */
    $remarks = new StatRemarks($document->id,null,false);
    $description = Tool::convert_html2txt($document->description);
	
	switch ($document->status) {
		case "Under review":
  			$objPHPExcel->getActiveSheet()->getStyle('H'.$row_counter)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_YELLOW);
  			$objPHPExcel->getActiveSheet()->getStyle('H'.$row_counter)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
  			$objPHPExcel->getActiveSheet()->getStyle('H'.$row_counter)->getFill()->getStartColor()->setARGB('FF0000');
			$date_review = Date::convert_date_conviviale ($document->date_review_sql);
			break;
		case "Approved":
			$objPHPExcel->getActiveSheet()->getStyle('H'.$row_counter)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$objPHPExcel->getActiveSheet()->getStyle('H'.$row_counter)->getFill()->getStartColor()->setARGB('00FF00');
			$date_review = "";
			break;
		case "Reviewed":
		    $objPHPExcel->getActiveSheet()->getStyle('H'.$row_counter)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_YELLOW);
  			$objPHPExcel->getActiveSheet()->getStyle('H'.$row_counter)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
  			$objPHPExcel->getActiveSheet()->getStyle('H'.$row_counter)->getFill()->getStartColor()->setARGB('0000FF');
			$date_review = Date::convert_date_conviviale ($document->date_review_sql);
			break;
		default:
			$date_review = Date::convert_date_conviviale ($document->date_review_sql);		
			break;
    }
	$acceptance = Tool::convert_html2txt($document->acceptance);
    $list_data = array ($document->application,
						$document->version,
						$document->type,
						$document->author_fname." ".$document->author_lname,
						$description,
						$document->date_published,
						$date_review,
						$document->status,
						$acceptance);

    $index = 0;
    foreach ($list_data as $val) {
       $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($index++, $row_counter, $val);
    }
	$data->get($document->id);
	$result_prr = $data->getExternalPeerReviewList();
	$list_peer_reviews = "";
	foreach($result_prr as $peer_reviews):
		$list_peer_reviews .= $peer_reviews->name."\n";
	endforeach;	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($index, $row_counter, $list_peer_reviews);
    $row_counter++;
    //break;
endforeach;
unset($data);
/* draw thick border around the remarks */
$objPHPExcel->getActiveSheet()->getStyle('A2:J'.strval($row_counter - 1))->applyFromArray($style_test);
/* To apply an autofilter to a range of cells */
$objPHPExcel->getActiveSheet()->setAutoFilter('A2:J2');
/*
 *  Actions list
 */   
$objPHPExcel->setActiveSheetIndex(2);
$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);
$header=array('Id','Project','Context','Equipment','Assignee','Description','Criticality','Status','Date open','Date expected','Date closed');
for($i=0;$i<count($header);$i++) {
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, 2, $header[$i]);
}
$action = new Action(&$context_array);
$list_actions = $action->getActions();
$amount_actions=count($list_actions);
$row_counter = 3;
/* draw thick border around the actions */
$objPHPExcel->getActiveSheet()->getStyle('A2:K'.strval($amount_actions + 2))->applyFromArray($style_encadrement);
foreach($list_actions as $row) {
	if ($row_counter % 2) {
		/* alternate white and grey line color */
		$objPHPExcel->getActiveSheet()->getStyle('A'.$row_counter.':K'.$row_counter)->applyFromArray($style_white_line);
	}
	$action->get($row['id']);
    $data[0] = $action->id;
    $data[1] = $action->project;
    $data[2] = Tool::convert_html2txt($action->context);
	$data[2] = Tool::filter($data[2]);
    $data[3] = $action->lru;
    $data[4] = $action->getAssignee();
    if ($action->status == "Closed" ) {
  		$data[5] = Tool::convert_html2txt($action->getDescription())."\n[".$action->date_closure."] ".Tool::convert_html2txt($action->response);
  		$data[7] = "Closed";
  	}
  	else {
  		$data[5] = Tool::convert_html2txt($action->getDescription());
		$data[7] = "Open";
	}
    $data[6] = $action->getSeverity();
    $data[8] = $action->date_open;
    $data[9] = $action->date_expected;
    $data[10] = $action->date_closure;

    $index = 0;
    foreach ($data as $val) {
	   $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($index++, $row_counter, $val);
    }
    if ($action->deadline_over) {
			$objPHPExcel->getActiveSheet()->getStyle('H'.$row_counter)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_YELLOW);
			$objPHPExcel->getActiveSheet()->getStyle('H'.$row_counter)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$objPHPExcel->getActiveSheet()->getStyle('H'.$row_counter)->getFill()->getStartColor()->setARGB('FFFF0000');
    }
	switch ($action->status) {
        case "Closed":
			$objPHPExcel->getActiveSheet()->getStyle('H'.$row_counter)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$objPHPExcel->getActiveSheet()->getStyle('H'.$row_counter)->getFill()->getStartColor()->setARGB('00FF00');
    		//$objPHPExcel->getActiveSheet()->getStyle('F'.$row_counter)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_GREEN);
            break;	
        default:
        	break;
    }	
    $row_counter++;	
}
/* draw thick border around the remarks */
$objPHPExcel->getActiveSheet()->getStyle('A2:K'.strval($row_counter - 1))->applyFromArray($style_test);
/* To apply an autofilter to a range of cells */
$objPHPExcel->getActiveSheet()->setAutoFilter('A2:K2');

$objPHPExcel->getActiveSheet()->getTabColor()->setRGB('EEEEEE');

/*
 * Reviews lists
 */   
$objPHPExcel->setActiveSheetIndex(3);
$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);
$header=array("Led by","Type", "Date", "Objective","Conclusion", "Status","Minutes");
for($i=0;$i<count($header);$i++) {
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, 2, $header[$i]);
}

$review = new Review(&$context_array);
$review_list = $review->getReviewList(PDO::FETCH_OBJ);
$amount_reviews=count($review_list);
$objPHPExcel->getActiveSheet()->getStyle('A2:G'.strval($amount_reviews + 2))->applyFromArray($style_encadrement);
$row_counter = 3;
$data = array();
$count=0;
foreach($review_list as $review) {
	if ($row_counter % 2) {
		/* alternate white and grey line color */
		$objPHPExcel->getActiveSheet()->getStyle('A'.$row_counter.':G'.$row_counter)->applyFromArray($style_white_line);
	}
	// $date = Date::convert_date_conviviale($review->date);
	// $date = $review->date;
	$objective = Tool::convert_html2txt($review->objectives);
    $comment = Tool::convert_html2txt($review->comment);
	$longueur = strlen($comment);
	if ($longueur < 3) {
		$comment = Tool::convert_html2txt($review->description." ".$review->objectives);
	}
	$data = array ($review->managed_by,
					$review->type,
					$review->date,
					$objective,
					$comment,
					$review->status_name,
					$review->reference);
	$index = 0;
	foreach ($data as $val) {
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($index++, $row_counter, $val);
	}
	switch ($review->status_name) {
        case "Accepted":
			$objPHPExcel->getActiveSheet()->getStyle('F'.$row_counter)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$objPHPExcel->getActiveSheet()->getStyle('F'.$row_counter)->getFill()->getStartColor()->setARGB('00FF00');
    		//$objPHPExcel->getActiveSheet()->getStyle('F'.$row_counter)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_GREEN);
            break;
        case "Partially Accepted":
				$objPHPExcel->getActiveSheet()->getStyle('F'.$row_counter)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_DARKBLUE);
				$objPHPExcel->getActiveSheet()->getStyle('F'.$row_counter)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
				$objPHPExcel->getActiveSheet()->getStyle('F'.$row_counter)->getFill()->getStartColor()->setARGB('FFA500');
            break;
        case "Not Accepted":
				$objPHPExcel->getActiveSheet()->getStyle('F'.$row_counter)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_YELLOW);
				$objPHPExcel->getActiveSheet()->getStyle('F'.$row_counter)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
				$objPHPExcel->getActiveSheet()->getStyle('F'.$row_counter)->getFill()->getStartColor()->setARGB('FF0000');
            break;
        default:
        	break;

    }
	if ($review->extension != "") {
		$extension = $review->extension;
	}
	else{
		$extension = "pdf";	
	}
	$objPHPExcel->getActiveSheet()->getCell('G'.$row_counter)->getHyperlink()->setUrl(rawurlencode($logbook->folder).$review->reference.".".$extension);
	// $objPHPExcel->getActiveSheet()->getCell('G'.$row_counter)->getHyperlink()->setUrl($review->reference.".".$extension);
	$row_counter++;
}
// var_dump($data);
/* draw thick border around the remarks */
/* $objPHPExcel->getActiveSheet()->getStyle('A2:G'.strval($row_counter - 1))->applyFromArray($style_test); */
/* To apply an autofilter to a range of cells */
$objPHPExcel->getActiveSheet()->setAutoFilter('A2:G2');
/*
 *
 * Summary
 *
 *
 */
$objWorksheet1 = $objPHPExcel->createSheet();
$objWorksheet1->setTitle('Summary');
$objPHPExcel->setActiveSheetIndex(4);
$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 1, "Remarks baseline status");
$objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setName('Candara');
$objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setSize(20);
$objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);
//$objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);
$objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_BLUE);
//$objPHPExcel->getActiveSheet()->getStyle('A1:A1')->applyFromArray($style_title);
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 3, "Name");
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 3, "Function");
$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, 3, "Amount");
$row_counter = 4;
$objPHPExcel->getActiveSheet()->getStyle('A4:D'.strval($row_counter - 1))->applyFromArray($style_array);

$gdImage_error = 'assets/images/error.png';
$amount_remarks = 0;
$objDrawing = new PHPExcel_Worksheet_MemoryDrawing();
if (($context_array['baseline_id'] != "") && ($context_array['baseline_id'] != 0)) {
	/* get data remarks statistics for a specific baseline */
	$remarks_baseline = new StatRemarks();
	/* get data peer reviewers statistics for a specific baseline */
	$peer_reviewers_baseline = new PeerReviewer();
	if ($peer_reviewers_baseline->index_peer_reviewer > 0){
	  foreach ($peer_reviewers_baseline->peer_reviewer_tab as $name => $function) {
		//echo $name." ".$function.":".$peer_reviewers_baseline->peer_reviewer_nb_tab[$name];
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $row_counter, $name);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $row_counter, $function);
		$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $row_counter, $peer_reviewers_baseline->peer_reviewer_nb_tab[$name]);
		$row_counter++;
	  }
	}
	$peer_reviews_bar_filename = '../result/remarks_bar.png';
	$peer_reviews_pie_filename = '../result/peer_reviewers_pie.png';
	if ($remarks_baseline->amount_remarks > 0){
		$remarks_baseline->drawBar($peer_reviews_bar_filename);
		$peer_reviewers_baseline->drawPie($peer_reviews_pie_filename,"Authors of remarks");
		$gdImage_peer_reviewers = @imagecreatefrompng($peer_reviews_pie_filename);
		$gdImage = @imagecreatefrompng($peer_reviews_bar_filename);
		// Add a drawing to the worksheet
		// 
		$objDrawing->setName('Sample image');
		$objDrawing->setDescription('Sample image');
		$objDrawing->setImageResource($gdImage);
		$objDrawing->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_JPEG);
		$objDrawing->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
		$objDrawing->setHeight(600);
		$objDrawing->setCoordinates('B'.strval($row_counter+20));
		$objDrawing->setOffsetX(10);
		$objDrawing->setRotation(0);
		$objDrawing->getShadow()->setVisible(true);
		$objDrawing->getShadow()->setDirection(45);
		$objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
		// unset($objDrawing);
		/*
		 * Pie peer reviewers
		 */
		// $objDrawing2 = new PHPExcel_Worksheet_MemoryDrawing();
		$objDrawing->setName('Sample image');
		$objDrawing->setDescription('Sample image');
		$objDrawing->setImageResource($gdImage_peer_reviewers);
		$objDrawing->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_JPEG);
		$objDrawing->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
		$objDrawing->setHeight(400);
		$objDrawing->setCoordinates('B2');
		$objDrawing->setOffsetX(300);
		$objDrawing->setRotation(0);
		$objDrawing->getShadow()->setVisible(true);
		$objDrawing->getShadow()->setDirection(45);
		$objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
		// unset($objDrawing2);		
	}
	$amount_remarks = $remarks_baseline->amount_remarks;
}
if ($row_counter < 10)
	$row_counter = 10;	
/*
 *
 * Actions status
 *
 */

$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $row_counter+60, "Status of ".$amount_actions." actions");
$objPHPExcel->getActiveSheet()->getStyle('B'.strval($row_counter+60))->getFont()->setName('Candara');
$objPHPExcel->getActiveSheet()->getStyle('B'.strval($row_counter+60))->getFont()->setSize(20);
$objPHPExcel->getActiveSheet()->getStyle('B'.strval($row_counter+60))->getFont()->setBold(true);
//$objPHPExcel->getActiveSheet()->getStyle('B'.strval($row_counter+36))->getFont()->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);
$objPHPExcel->getActiveSheet()->getStyle('B'.strval($row_counter+60))->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_BLUE);
$actions_pie_filename = '../atomik/assets/images/error.png';
$actions_bar_filename = '../atomik/assets/images/error.png';
if (($amount_actions) > 0) {
	$actions_pie_filename = '../result/actions_pie.png';
	$actions_bar_filename = '../result/actions_bar.png';
	$actions_closed = $action->new_count_actions("closed");
	$actions_open = $action->new_count_actions("open");
	$actions['closed']=$actions_closed;
	$actions['open']=$actions_open;	
	$action->drawPie($actions,$actions_pie_filename);
	$gdPie = @imagecreatefrompng($actions_pie_filename);
	// $objDrawing = new PHPExcel_Worksheet_MemoryDrawing();
	$objDrawing->setName('Actions status');
	$objDrawing->setDescription('Actions status');
	$objDrawing->setImageResource($gdPie);
	$objDrawing->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_JPEG);
	$objDrawing->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
	$objDrawing->setHeight(400);
	$objDrawing->setCoordinates('B'.strval($row_counter+62));
	$objDrawing->setOffsetX(20);
	$objDrawing->setRotation(0);
	$objDrawing->getShadow()->setVisible(true);
	$objDrawing->getShadow()->setDirection(45);
  	/*
  	 * Bar
  	 */  	
	$user = new User(&$context_array);
	$user->get_stat_actions (); 
  	// $user->get_stat_actions ($logbook->project,$logbook->equipment); 
	if ($user->nb != 0) {
		/* Bar */  	 
		$action->drawBar(&$user,$actions_bar_filename);
		$gdImage_poster = @imagecreatefrompng($actions_bar_filename);
		// $objDrawing2 = new PHPExcel_Worksheet_MemoryDrawing();
		$objDrawing->setName('Sample image');
		$objDrawing->setDescription('Sample image');
		$objDrawing->setImageResource($gdImage_poster);
		$objDrawing->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_JPEG);
		$objDrawing->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
		$objDrawing->setHeight(500);
		$objDrawing->setCoordinates('B'.strval($row_counter+92));
		$objDrawing->setOffsetX(400);
		$objDrawing->setRotation(0);
		$objDrawing->getShadow()->setVisible(true);
		$objDrawing->getShadow()->setDirection(45);
	}
	else{
		echo "no users";
	}	
	$objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
}

// Add some data
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

$objWriter->save($filename);
$result = Data::copyExcel($excel_filename);
echo $result."<br/>";
$html ='<a href="'.Atomik::url("build_logbook",false).'" ><img src="'.Atomik::asset('assets/images/pages/sommaire.png').'" border="0" alt="Back" title="Back"><h2>Back</h2></a>';
Atomik::set('select_menu',$html);
Atomik::set('css_reset',"no_show");
Atomik::set('css_add',"no_show");
Atomik::set('css_page',"no_show");
