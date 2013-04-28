<?php
Atomik::disableLayout();
Atomik::noRender();
Atomik::needed("Baseline.class");
Atomik::needed("Data.class");

$baseline_id = isset($_GET['baseline_id']) ? $_GET['baseline_id'] : ""; 
$baseline= new Baseline;
$baseline->get($baseline_id);
$baseline_dir = $baseline->getBaseline();
$backup_filename = $baseline_dir."_".date('d-m-Y').".zip";
/* create directory */
$baseline->createExportDir();
/* Copy external peer reviews and Create internal peer review. */
$list_all_prr=$baseline->exportPeerReview();
/* Copy documents and create the zip file. */
$res_export_data = $baseline->exportData($backup_filename,$baseline_id);

$context_array['baseline_id'] = $baseline_id;
$context_array['project_id'] = $baseline->project_id;
$data = new Data(&$context_array);
/* create excel file */
if ($baseline_dir != ""){
	$baseline_dir_web=$baseline_dir."/";
}
else {
	$baseline_dir_web=$baseline_dir;
}
/* Create the document synthesis list */
$data->exportXlsx($baseline_dir_web,&$list_all_prr);
if ($baseline_dir != "")$baseline_dir.=DIRECTORY_SEPARATOR;
$excel_filename = $baseline_dir.$data->getExportFilename();
$res = Data::copyExcel($excel_filename);
// echo $res;
$res = $res_export_data.'<br/><b>Zip file is available here:</b> <a href="../result/'.$backup_filename.'"><img src="assets/images/32x32/filesave.png" border="0"></a>';
$res = '<li class="success" style="list-style-type: none">'.$res.'</div>';
echo $res;
