<?php
Atomik::disableLayout();
Atomik::setView("export/export_xlsx_actions_list");
include "../includes/config.php"; 
include "../includes/cookie.php";
//include "includes/bug_list_globals.php";
//include "includes/bug_functions.php";
Atomik::needed("Db.class");
Atomik::needed("User.class");
Atomik::needed("Date.class");
Atomik::needed("Data.class");
Atomik::needed("Project.class");
Atomik::needed("Tool.class");
Atomik::needed("Review.class");
Atomik::needed("Mail.class");
Atomik::needed("Logbook.class");
Atomik::needed("Remark.class");
Atomik::needed("Baseline.class");
Atomik::needed("PeerReviewer.class");

if(isset($_POST['context'])) {
	$context=unserialize(urldecode(stripslashes((stripslashes($_POST['context'])))));
	$env_context['project_id'] = isset($context['project_id']) ? $context['project_id'] : "";
	$env_context['sub_project_id'] = isset($context['sub_project_id']) ? $context['sub_project_id'] : "";
	$env_context['review_id'] = isset($context['review_id']) ? $context['review_id'] : "";  
	$env_context['data_status_id'] = isset($context['action_status_id']) ? $context['action_status_id'] : ""; 
	$env_context['user_id'] = isset($context['user_id']) ? $context['user_id'] : "";
	$env_context['criticality_id'] = isset($context['criticality_id']) ? $context['criticality_id'] : "";    
	$env_context['search']=isset($context['search']) ? $context['search'] : "";
	$env_context['order']=isset($context['order']) ? $context['order'] : "";
	$env_context['user_logged_id']=$userLogID;
}
else{
	$env_context[] = array();
}

$data = new Data(&$env_context);
/* create excel file */
$data->exportXlsx();
$excel_filename = $action->getExportFilename();

include "excel/Classes/PHPExcel.php";
error_reporting(E_ALL);
$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
PHPExcel_Settings::setCacheStorageMethod($cacheMethod);
/** PHPExcel_IOFactory */
require_once 'excel/Classes/PHPExcel/IOFactory.php';

/** PHPExcel_IOFactory */
require_once 'excel/Classes/PHPExcel/Worksheet/RowIterator.php';
include "inc/ExportXls.class.php";
$file_template = "template/data_list.xlsx";
if (!file_exists($file_template)) {
	echo "<BR><b>Warning: Assurance_Process_Dashboard template is missing.</b><BR>";
	$objPHPExcel = new PHPExcel;
}
else {
	$objPHPExcel = PHPExcel_IOFactory::load($file_template);
}
/* Create logbook */
$logbook = new Logbook();
$today_date = date("d").' '.date("M").' '.date("Y");
$filename="result/".$logbook->board."_data_list_".$today_date.".xlsx";

$objPHPExcel->setActiveSheetIndex(0);
//$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, 1,"Amount of remarks");
$header=array("Reference", "Issue", "Type","Author" ,"Description","Released","Status","Acceptance","Peer reviews");
   // "Total","Open");
for($i=0;$i<count($header);$i++) {
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, 2, $header[$i]);
}
$sql = sort_data();
//echo "test:".$sql."<br/>";
$db = new Db;
$result = $db->db_query($sql);
/* draw thick border around the actions */
$amount_data=mysql_num_rows($result) + 3;
$last_column = "I";
$objPHPExcel->getActiveSheet()->getStyle('A2:'.$last_column.strval($amount_data - 1))->applyFromArray($style_encadrement);
$row_counter = 3;
while($row = mysql_fetch_array($result)) {
	if ($row_counter % 2) {
		/* alternate white and grey line color */
		$objPHPExcel->getActiveSheet()->getStyle('A'.$row_counter.':'.$last_column.$row_counter)->applyFromArray($style_white_line);
	}
	$document = new Data($row);
    /* get data remarks statistics */
    /*    $remarks = new StatRemarks($document->id,
                                   $document->reference,
                                   $document->version,
                                   $document->type);*/
    $description = clean_text($document->description);
	$description = convert_html2txt($description);
	//echo $description;
	$acceptance = clean_text($document->acceptance);
	$acceptance = convert_html2txt($acceptance);
    $data = array ($document->reference,$document->version,$document->type,$document->author,$description,$document->date_published,$document->status,$acceptance);

    $index = 0;
	if ($document->status == "Approved") {
		$objPHPExcel->getActiveSheet()->getStyle('G'.$row_counter)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$objPHPExcel->getActiveSheet()->getStyle('G'.$row_counter)->getFill()->getStartColor()->setARGB('00FF00');
	}
    foreach ($data as $val) {
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($index++, $row_counter, $val);
    }
	$sql_query = "SELECT peer_review_location.id,name,ext,date FROM peer_review_location ".
				"LEFT OUTER JOIN bug_applications ON bug_applications.id = data_id WHERE data_id = {$document->id}";	
	$result_prr = $db->db_query($sql_query);
	$list_peer_reviews = "";
	while($peer_reviews = mysql_fetch_object($result_prr)) {
		$list_peer_reviews .= $peer_reviews->name."\n";
	}	
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($index, $row_counter, $list_peer_reviews);
	/*
    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($index++, $row_counter, $remarks->amount_remarks);
	if ($remarks->amount_remarks > 0) {
		foreach ($remarks->remark_tab as $name => $amount) {
			 $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($index++, $row_counter, $amount);
		}
	}
	*/
    $row_counter++;
    //break;
}
/* To apply an autofilter to a range of cells */
$objPHPExcel->getActiveSheet()->setAutoFilter('A2:'.$last_column.'2');
// redirect output to client browser
//header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
//header('Content-Disposition: attachment;filename="'.$filename.'"');
//header('Cache-Control: max-age=0');
/*
 *
 * Summary
 *
 *
 */
 /* get data remarks statistics for a specific baseline */
 //echo "Test:".$show_baseline."<br/>";
$remarks_baseline = new StatRemarks();
/* get data peer reviewers statistics for a specific baseline */
$peer_reviewers_baseline = new PeerReviewer();
$objWorksheet1 = $objPHPExcel->createSheet();
$objWorksheet1->setTitle('Summary');
$objPHPExcel->setActiveSheetIndex(1);
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
if ($peer_reviewers_baseline->index_peer_reviewer > 0){
  foreach ($peer_reviewers_baseline->peer_reviewer_tab as $name => $function) {
	//echo $name." ".$function.":".$peer_reviewers_baseline->peer_reviewer_nb_tab[$name];
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $row_counter, $name);
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, $row_counter, $function);
	$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, $row_counter, $peer_reviewers_baseline->peer_reviewer_nb_tab[$name]);
	$row_counter++;
  }
}
$objPHPExcel->getActiveSheet()->getStyle('B3:D'.strval($row_counter - 1))->applyFromArray($styleArray);
if ($row_counter < 10)
	$row_counter = 10;	
if ($remarks_baseline->amount_remarks > 0) {
	//ob_start("rappel");
	require_once "draw_bar_w_pchart.php";
	$graph_remarks = 'result/mon_graphique.png';
	draw_bar($remarks_baseline->name_serial,$remarks_baseline->nb_serial,$graph_remarks);
}
else {
	$graph_remarks = 'artichow/images/error.png';
}
$gdImage = @imagecreatefrompng($graph_remarks);

if ($peer_reviewers_baseline->nb > 0) {
	require_once "draw_pie_peer_reviewers_w_pchart.php";
	$graph_peer_reviewers = 'result/my_pie2.png';
	draw_pie_peer_reviewers($peer_reviewers_baseline->name_serial,$peer_reviewers_baseline->nb_serial,$graph_peer_reviewers);	
}
else {
	$graph_peer_reviewers = 'artichow/images/error.png';
}
$gdImage_peer_reviewers = @imagecreatefrompng($graph_peer_reviewers);
// Add a drawing to the worksheet

$objDrawing = new PHPExcel_Worksheet_MemoryDrawing();
$objDrawing->setName('Sample image');
$objDrawing->setDescription('Sample image');
$objDrawing->setImageResource($gdImage);
$objDrawing->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_JPEG);
$objDrawing->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
$objDrawing->setHeight(600);
$objDrawing->setCoordinates('B'.strval($row_counter+20));
$objDrawing->setOffsetX(10);
//$objDrawing->setRotation(25);
$objDrawing->getShadow()->setVisible(true);
$objDrawing->getShadow()->setDirection(45);
$objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
/*
 * Pie peer reviewers
 */
$objDrawing2 = new PHPExcel_Worksheet_MemoryDrawing();
$objDrawing2->setName('Sample image');
$objDrawing2->setDescription('Sample image');
$objDrawing2->setImageResource($gdImage_peer_reviewers);
$objDrawing2->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_JPEG);
$objDrawing2->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
$objDrawing2->setHeight(400);
$objDrawing2->setCoordinates('B2');
$objDrawing2->setOffsetX(300);
//$objDrawing->setRotation(25);
$objDrawing2->getShadow()->setVisible(true);
$objDrawing2->getShadow()->setDirection(45);
$objDrawing2->setWorksheet($objPHPExcel->getActiveSheet());
$objPHPExcel->setActiveSheetIndex(0);
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save($filename);

echo '<ul><li class="success">';
echo " Data list report generated at ".date('H:i:s')." with peak memory usage: " . (memory_get_peak_usage(true) / 1024 / 1024) . " MB\r\n";
echo'<a href="'.$filename.'" >';
echo '<img alt="Export openxml" title="Export openxml" border=0 src="images/256x256/Excel2007.png" class="img_button"/></a>';
echo '</li></ul>';?>
<!--
<div style="height:20px;"></div>
</div> --><!-- content -->
<?php //include "includes/footer.php"; 
?>
<script type="text/javascript">
<!-- 
window.top.window.uploadEnd("OK");

//-->
</script>
</body>
</html>
