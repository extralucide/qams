<?php
Atomik::needed('Action.class');
Atomik::needed('Tool.class'); 
Atomik::needed('Db.class');
Atomik::needed('Data.class');
Atomik::needed('Review.class');

Atomik::set('css_reset',"no_show");
Atomik::set('title',"Import Action Items");
Atomik::set('css_title',"action");
Atomik::set('css_add',"no_show");
Atomik::set('css_page',"no_show");

function store_cmd($buffer,$reset=false){
    $filename = dirname(__FILE__).DIRECTORY_SEPARATOR.
            "..".DIRECTORY_SEPARATOR.
            "..".DIRECTORY_SEPARATOR.   
            "..".DIRECTORY_SEPARATOR.               
            "..".DIRECTORY_SEPARATOR."result".DIRECTORY_SEPARATOR.A('db_config/import_prr');
	if($reset){		
		$monfichier = fopen($filename, 'w');
    }
	else{
		$monfichier = fopen($filename, 'a');
		fputs($monfichier, $buffer.";"."\n");
	}
    fclose($monfichier);    
}

function get_cmd(){
    $filename = dirname(__FILE__).DIRECTORY_SEPARATOR.
            "..".DIRECTORY_SEPARATOR.
            "..".DIRECTORY_SEPARATOR.   
            "..".DIRECTORY_SEPARATOR.               
            "..".DIRECTORY_SEPARATOR."result".DIRECTORY_SEPARATOR.A('db_config/import_prr');
	// $fhandle = fopen($filename, 'r'); 
	$sql_query = file_get_contents($filename);
	return($sql_query);
}

function setTable(&$table,$id,$max,$value){
	for ($index=0;$index < $max;$index++){
		$table[$index][$id]=$value;
	}
}

function test_alter(&$item1, $key, $prefix)
{
	global $header_fields;
	// static $header_fields = array("Id"=>2,"Action"=>10, "From"=>4, "Who"=>4, "When"=>3,"Closure"=>3,"Comment"=>3,"Status"=>3);
	// var_dump($header_fields);
	list($key2, $val2) = each(&$header_fields);
	// echo $val2."<br/>";
	$value = $val2;
    $item1 = '<td colspan="'.$value.'">'.$item1.'</td>';
}
$header_fields = array("ID"=>2,"Action"=>10, "From"=>4, "Who"=>4, "When"=>3,"Closure"=>3,"Comment"=>3,"Status"=>3);
if (isset($_POST['submit_cancel'])){
	/* Cancel  */
	Atomik::redirect('post_review?tab=minutes');
}

$review_id = isset($_POST['review_id']) ? $_POST['review_id'] : "";

$db = new Db;
$color = "";
$line_counter = "";
/* Upload file */
if (isset($_POST['submit_import'])){
    /* Import items */
    $uploadName  = $_POST["filename"];
    $uploadSize  = $_POST["filesize"];
	$nb_items  = $_POST["nb_items"];  
	$sql_query = get_cmd();
	if ($sql_query != ""){
		$result = $db->pdo_query($sql_query,true);
	}
	else{
		$result = false;
	}
	if($result){
		Atomik::Flash($nb_items.' action items have been imported successfully.','success');
	}
	else{
		Atomik::Flash('Importation of '.$nb_items.' failed.','failed');	
	}
	Atomik::redirect('post_review?tab=minutes');
}
else{
	if (isset($_FILES['filename'])){
		$maxSize=30000000;                            // Only save files smaller than 30M
		$uploadSize = $_FILES['filename']['size'];  // The size of our uploaded file
		$uploadType = $_FILES['filename']['type'];  // The type of the file.
		if ($uploadType == "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet") {
			/* type excel 2007 */
			$type = "xlsx";
		}
		else if ($uploadType == "application/vnd.ms-excel") {
		/* type excel */
			$type = "xls";
		}
		else {	
			preg_match("#\/(\w+)$#", $uploadType,$excel_type);
			//echo $excel_type[1]."<br/>";
			$type = isset($excel_type[1])?$excel_type[1]:"";
		}
		//echo "Type: ".$uploadType."<br/>";
		$uploadName = getcwd().DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."result".DIRECTORY_SEPARATOR."uploadedFile.".$type; // Never trust the upload, make your own name
		$filename = basename( $_FILES['filename']['name']);
		if ($uploadSize >= $maxSize) {              // Make sure the file size isn't too big.
			Atomik::Flash("File not imported.  It's too big!  Max filesize is $maxSize","failed");
			Atomik::redirect('post_review?tab=minutes');
		}
		move_uploaded_file($_FILES['filename']['tmp_name'], $uploadName);   // save file.
    }
	else{
		Atomik::Flash("No file found.","failed");
		Atomik::redirect('post_review?tab=minutes');	
	}
}


/* create temporary remarks table */
$sql_query = <<<____SQL
			CREATE TEMPORARY TABLE IF NOT EXISTS `actions` (
			  `id` smallint(6) NOT NULL AUTO_INCREMENT,
			  `project` int(11) NOT NULL,
			  `context` text,
			  `review` int(11) NOT NULL,
			  `lru` int(11) DEFAULT NULL,
			  `posted_by` int(11) DEFAULT NULL,
			  `assignee` int(11) NOT NULL,
			  `Description` longtext,
			  `criticality` int(11) DEFAULT NULL,
			  `status` int(11) DEFAULT NULL,
			  `date_open` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			  `date_expected` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
			  `date_closure` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
			  `comment` text NOT NULL,
			  `duration` int(11) NOT NULL,
			  KEY `id` (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=latin1;			
____SQL;
$sql_query = <<<____SQL
			CREATE TEMPORARY TABLE actions SELECT * FROM actions
____SQL;

$statement = $db->pdo_query($sql_query,true);
$sql_query = "SELECT * FROM actions WHERE review = ".$review_id;
$statement = $db->pdo_query($sql_query,true);

/* Read only Register sheet */
$objWorksheet = Tool::scanExcelFull($uploadName,
									$type);
$highestRow = $objWorksheet->getHighestRow(); // e.g. 10
$highestColumn = 'I';//$objWorksheet->getHighestColumn(); // e.g 'F'
$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn); // e.g. 5
$list_items = array();
$res = Action::readItemsSheet(&$objWorksheet,
							&$list_items);
$list_keys_reference = array('ID'=>"",'Date'=>"",'Action'=>"",'Who'=>"",'Closure'=>"",'Status'=>"",'Comment'=>"");
while (list($key, $val) = each($list_items)) {
    $list_keys[$key] = "";
}
$nb_header_found = array_diff_key($list_keys, $list_keys_reference);
while (list($key, $val) = each($nb_header_found)) {
    echo "Header's field &quot;".$key."&quot; discarded.<br/>";
}
/* Date of the opening action item. */
$review = new Review;
$review->get($review_id);
$date_open = $review->getDateSQL();

/* Action items ID */
$index=0;
if (array_key_exists('ID',$list_keys)){
	foreach ($list_items['ID'] as $text):
		$id[] = $text;
		$html_table[$index++][0]=$text;
	endforeach;
}
else{
	$id = array_fill(0,$res['nb_items'],"");
	$html_table[][0]=array_fill(0,$res['nb_items'],"");
}
$index=0;
/* Action items description */
if (array_key_exists('Action',$list_keys)){
	foreach ($list_items['Action'] as $text):
		$description[] = mysql_real_escape_string($text); /* shoulde used PDO binding instead */
		$html_table[$index++][1]=$text;
	endforeach;
}
else{
	$description = array_fill(0,$res['nb_items'],"");
	setTable(&$html_table,1,$res['nb_items'],"");
}
/* From */
setTable(&$html_table,2,$res['nb_items'],User::getNameUserLogged());

$index=0;
/* Action items closure comments */
if (array_key_exists('Comment',$list_keys)){
	foreach ($list_items['Comment'] as $text):
		$comment[] = $text;
		$html_table[$index++][6]=$text;		
	endforeach;
}
else{
	$comment = array_fill(0,$res['nb_items'],"");
	setTable(&$html_table,6,$res['nb_items'],"");
}

/* Assignees*/
$index=0;
if (array_key_exists('Who',$list_keys)){
	/* Find assignees ID in MySQL database*/
	Atomik::needed("User.class");
	$poster = new User;
	$customer = array(1);
	foreach ($list_items['Who'] as $name):
		$poster->find_poster ($name,
								$customer);
		$who[] = $poster->id;
		$html_table[$index++][3]=$poster->name;	
	endforeach;
}
else{
	$who = array_fill(0,$res['nb_items'],"");
	setTable(&$html_table,3,$res['nb_items'],"");
}

/* Due date */
$index=0;
if (array_key_exists('Date',$list_keys)){
	foreach ($list_items['Date'] as $date):
		$date_php = PHPExcel_Shared_Date::ExcelToPHP($date);
		$date_sql = date('Y-m-d',$date_php);
		$date_expected[] = $date_sql;
		$html_table[$index++][4]=Date::convert_date($date_sql);	
	endforeach;
}
else{
	$date_expected = array_fill(0,$res['nb_items'],"");
	setTable(&$html_table,4,$res['nb_items'],"");
}

/* Date closure */
$index=0;
if (array_key_exists('Closure',$list_keys)){
	foreach ($list_items['Closure'] as $date):
		$date_php = PHPExcel_Shared_Date::ExcelToPHP($date);
		$date_sql = date('Y-m-d',$date_php);	
		$date_closure[] = $date_sql;
		$html_table[$index++][5]=Date::convert_date($date_sql);	
	endforeach;
}
else{
	$date_closure = array_fill(0,$res['nb_items'],"");
	setTable(&$html_table,5,$res['nb_items'],"");
}

/* Action items status */
$index=0;
if (array_key_exists('Status',$list_keys)){
	/* Find status ID in MySQL database*/
	foreach ($list_items['Status'] as $text):
		$status_id = Action::findStatusId($text);
		if ($status_id != 0){
			$html_table[$index++][7]=Action::getStatusName($status_id);
			$status[] = $status_id;
		}
		else{
			$html_table[$index++][7]='<span class="orange">'.$text.'</span>';
			$status[] = 8; /* Open by default */			
		}
	endforeach;
}
else{
	$comment = array_fill(0,$res['nb_items'],"");
	setTable(&$html_table,7,$res['nb_items'],"");
}
for ($index=0;$index < $res['nb_items'];$index++){
	ksort($html_table[$index]);
}
// var_dump($html_table);
store_cmd("",true);
if ($list_items != null){
	for($index=0;$index < $res['nb_items'];$index++){
		// $index = ++$max_id;
		/* insert */
		/* Cannot use PDO here because we store MySQL command */
		// echo "Insert remarks";
		// var_dump($value);
		/* Patch: Date expected is equal to closure date */
		$date_expected = $date_closure[$index];
		$sql_query = "INSERT INTO `actions` (`review`,`project`,`lru`,`posted_by`, `assignee`, `Description`, `criticality`,`status`, `date_open`, `date_expected`, `date_closure`, `comment`) ".
					"VALUES ('".$review_id."','".$review->project_id."','".$review->lru_id."','".$who[$index]."','".User::getIdUserLogged()."', '".$description[$index]."','10','".$status[$index]."','".$date_open."','".$date_expected."','".$date_closure[$index]."','".$comment[$index]."')";
		store_cmd($sql_query);
		$statement = $db->exec($sql_query,true);     
	}
}	

$html = '<form id="import" name="import" method="POST" action="'.Atomik::url('action/import').'" enctype="multipart/form-data">';  
$html .= '<input type="hidden" name="filename" value="'.$uploadName.'"/>';
$html .= '<input type="hidden" name="filesize" value="'.$uploadSize.'"/>';
$html .= '<input type="hidden" name="nb_items" value="'.$res['nb_items'].'"/>';
$html .= '<span class="art-button-wrapper">';
$html .= '<span class="l"> </span>';
$html .= '<span class="r"> </span>';
$html .= '<input class="art-button" name="submit_import" type="submit" value="Import" ></span>';
$html .= '<span class="art-button-wrapper">';
$html .= '<span class="l"> </span>';
$html .= '<span class="r"> </span>';
$html .= '<input class="art-button" name="submit_cancel" type="submit" value="Cancel"></span>';
$html .= '</form>';
$html.='<table cellspacing="0" class="classic"><thead><tr><th colspan="12">Fields required <span class="small_font">(No matter the columns order)</span></th></tr>';
$html.='<tr><th colspan="4">Field</th><th colspan="8">Explanation</th></tr>';
$html.='</thead>';
$data = array("ID"=>"To detect each action in Excel sheet","Action"=>"Description of the action", "From"=>"Submitter of the action item", "Who"=>"Assignee who perform the action", "Date"=>"Due date","Closure"=>"Closure date","Comment"=>"Closure comment","Status"=>"<ul><li>Open</li><li>Propose to close</li><li>Closed</li><li>Cancelled</li></ul>");
foreach ($data as $field => $explain):
		$row_color = ($line_counter++ % 2 == 0) ? "rouge" : "vert"; 				
		$html.='<tr class="'.$row_color.'">';
		$html.='<td colspan="4"><span class="small_font">'.$field.'</span></td>';
		$html.='<td colspan="8"><span class="small_font">'.$explain.'</span></td>';
		$html.='</tr>';
endforeach;
$html.='</table>';
Atomik::set('select_menu',$html);
