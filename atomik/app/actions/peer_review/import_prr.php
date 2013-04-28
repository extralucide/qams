<?php
Atomik::needed('Remark.class');
Atomik::needed('PeerReviewer.class'); 
Atomik::needed('Db.class');
Atomik::needed('Date.class');
Atomik::needed('Data.class');
Atomik::needed('Remark.class'); 
Atomik::needed('User.class'); 
Atomik::set('css_reset',"no_show");
Atomik::set('title',"Import Peer Review");
Atomik::set('css_title',"remark");
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
function create_response ($author_response,
                          $poster_id,
                          $application,
                          $status_id,
                          $replyValue,
						  &$db) {	
	Atomik::needed('Remark.class');
    /* yes, already input, update remark */
    /* add new status in the description */
    // $description = $description." - ".$remark_status;
	$new_status = Remark::getStatusName($status_id);
	/* Read previous status */
	$previous_status = Remark::getRemarkStatus($replyValue);
	
	// /* add new status in the description */
	// $new_status=get_name("name","bug_status","id",$status);
	$author_response = "<p> Status: {$previous_status} --> {$new_status} </p>".$author_response;
	/*
	Remark::create_response ($author_response,
							$poster_id,
							$application,
							$status_id,
							$replyValue);
	*/
	$sql_query = "INSERT INTO bug_messages (
							`description`, 
							`posted_by`, 
							`application`, 
							`status`, 
							`reply_id`)".
				" VALUES(".
							$db->quote($author_response).",". 
							$poster_id.",". 
							$application.",".
							$status_id.",".
							$replyValue.")";
	return($sql_query);						
}
if (isset($_POST['submit_cancel'])){
	/* Cancel  */
	Atomik::redirect('edit_data?tab=peer_review');
}

$data_id = isset($_GET['id']) ? $_GET['id'] : "";
$db = new Db;
$data = new Data;
$test_defect_class = new Defect_Class;
$test_stat = new Status;
// $test_poster = new Poster;
$type_remark = "";
$color = "";
$line_counter = "";
if ($data_id != "") {
	$data->get($data_id);
}	
$application = $data->id;
$author_id = $data->author_id;
$data_ref = $data->reference;
$preview = isset($_POST["preview"])?$_POST["preview"]:"";
if (isset($_POST["remark_date"])) {
  $date_dojo = $_POST["remark_date"];
  $remark_date = Date::convert_dojo_date($date_dojo);
}

/* Upload file */
//echo $preview."<BR>";
if (isset($_POST['submit_import'])){
    /* Import remarks */
    $type        = $_POST["format_remark"];
    $uploadName  = $_POST["filename"];
    $uploadSize  = $_POST["filesize"];
	$nb_remarks  = $_POST["nb_remarks"];
    $preview     = "off";   
	$sql_query = get_cmd();
	if ($sql_query != ""){
		$result = $db->pdo_query($sql_query,true);
	}
	else{
		$result = false;
	}
	// $result = A('db:'.$sql_query);
	if($result){
		Atomik::Flash($nb_remarks.' remarks have been imported successfully.','success');
	}
	else{
		Atomik::Flash('Importation of '.$nb_remarks.' failed.','failed');	
	}
	Atomik::redirect('edit_data');
    // Atomik::redirect('peer_review/import_prr?import=answer');
}
else{
    //if (isset($_POST['submit_w_response'])){
        /* Import remarks */
    //    Atomik::redirect('peer_review/import_prr?import=answer');
    //}
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
			Atomik::redirect('edit_data?tab=peer_review');
		}
		move_uploaded_file($_FILES['filename']['tmp_name'], $uploadName);   // save file.
    }
	else{
		Atomik::redirect('edit_data?tab=peer_review');	
	}
}
$header_fields = array("Ref","Peer", "Section", "Line", "Remarks","","","", "Response","","","", "Defect","Status","Justif","Id" ); 

		/* create temporary remarks table */
$sql_query = <<<____SQL
			CREATE TEMPORARY TABLE IF NOT EXISTS bug_messages (
			  `category` int(11) NOT NULL DEFAULT '0',
			  `criticality` int(11) NOT NULL DEFAULT '0',
			  `application` int(11) NOT NULL DEFAULT '0',
			  `subject` text NOT NULL,
			  `description` longtext NOT NULL,
			  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			  `posted_by` text NOT NULL,
			  `status` int(11) NOT NULL,
			  `paragraph` text NOT NULL,
			  `line` text NOT NULL,
			  `justification` text NOT NULL,
			  `id` int(11) NOT NULL,
			  `reply_id` int(11) NOT NULL DEFAULT '0',
			  `action_id` int(11) NOT NULL,
			  KEY `id` (`id`),
			  FULLTEXT KEY `description` (`description`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1;
____SQL;
$sql_query = <<<____SQL
			CREATE TEMPORARY TABLE bug_messages SELECT * FROM bug_messages WHERE application = {$data_id}
____SQL;
$statement = $db->pdo_query($sql_query,true);
$statement = $db->pdo_query("SELECT * FROM bug_messages WHERE application = ".$data_id,true);
// foreach ($statement as $row){
	// echo $row['id']."<br/>";
// };
// exit();	

//echo "type:".$type."<br/>";
switch ($type) {
 case "csv":
 default:
 $fp = fopen($uploadName,  "r");
 $separateur=";";                 // sigle de s�parateur
 $source = fread($fp, $uploadSize);
 $pattern = '/([^\\r])(\\n)/i';
 $replacement = '${1}<br/>';
 $source=preg_replace($pattern, $replacement, $source);
 //$source=str_replace ("\n" , "  ",$source);
 //$source=str_replace ("\r" , "  ",$source);
 //echo $source."<BR>";
 /*
* ECE Peer Review Register format
*/
 $prr_format = $_POST['type_remark'];
 switch ($prr_format) {
	case 1: // ECE
		echo "CSV<br/>";
		echo "ECE Peer Review Register format<br/>";
		$regular_expression = "(R[0-9]{1,3};[^;].+\r\n|R[0-9]{1,3};[^;](.+\n)+?.+\r\n)";
		preg_match_all("#".$regular_expression."#",$source, $matches, PREG_SET_ORDER);
		$counter = 0;
		?>
		<table class="art-article" style="width:1124px">
		<thead>
		<tr>
		<? $header_fields = array("Ref","Inspector", "Paragraph", "Line", "Remarks","","", "Response","","", "Defect class","Status","Justification","QAMS id" );    
		foreach( $header_fields as $value ) {?>
			<th ><?= $value?></th>
		<? } ?>			
		</tr>
		</thead>	
		<tbody>	
		<?			
		foreach ($matches as $val) {
			$counter++;
			/* Suppression des caract�res antipathiques */
                $ligne = new_clean_text($val[0]);
			//echo $ligne."<br/>";
			list($ref_id,$reader,$paragraph,$line,$description,$author_response,$defect_class,$remark_status,$justification,$qams_id) = explode($separateur,$ligne);  // Champs s�par�s par ;			
			/*
			 * Find proof reader's name
			 */
                $customer = array(1); // ECE
                $poster = new Poster;
                $poster->find_poster ($reader,$customer);
                $poster_id=$poster->id;
                $reader=$poster->name;
			/* This gets the defect class id from class type */
                $defect_class = new Defect_Class;
                $defect_class->get_defect_class ($current_cell);
                $category_id = $defect_class->id;
                $defect_class = $defect_class->name;

			/* This gets the status id from status type */
                $test_stat = new Status;
                $test_stat->get_status ($current_cell);
                $status_id = $test_stat->id;
                $remark_status = $test_stat->name;
			
			//$description = clean_text($description);
			$abstract_remark=substr($description,0,16)."...";

			//echo $ref_id.":".$reader.":".$paragraph.":".$line.":".$description.":".$author_response.":".$defect_class.":".$status_id.":".$justification."<BR>";
			if ($preview != "on") {
				/* find if remark already input in db */
				if (Remark::find_remark($qams_id,$ref_id)) {
					//echo "remark".$ref_id."find in db <BR>";
					/* */
					/* input response */
					Remark::create_response	($author_response,
											 $author_id,
											 $application,
											 $status_id,
											 $qams_id);	
					/* update status of remark */
					Remark::update_remark_status($status_id,$qams_id);
				}
				else {
					/* no, input remark for the firt time*/
					$updateReply = create_remark($description,
												$poster_id,
												$abstract_remark,
												$category_id,
												$application,
												$status_id,
												$remark_date,
												$paragraph,
												$line,
												$justification,
												$action_id="");
					/* input response */
					Remark::create_response	($author_response,
											$author_id,
											$application,
											$status_id,
											$updateReply);					
				}
			}
			else {
				if (Remark::find_remark($qams_id,$ref_id)){
					$qams_id_found = $qams_id;
					//print "success to find remark {$ref_id}, update remark response, status and justfication<BR>";
				}
				else{
					$qams_id_found = "unknown remark";
					//print "fail to find remark<BR>";
				}
			}
			?>
			<tr>
			<td><?= $ref_id ?></td>
			<td><?= $reader ?></td>
			<td><?= $paragraph ?></td>
			<td><?= $line ?></td>
			<td><?= $description ?></td>
			<td><?= $author_response ?></td>
			<td><?= $defect_class ?></td>
			<td><?= $remark_status ?></td>
			<td><?= $justification ?></td>
			<td><?= $qams_id_found ?></td>
			</tr>
			<?php
		}?>	
		</tbody>
		</table>	
		<?php break;
	case 2: //Eurocopter
		echo "Eurocopter Observation Record format<br/>";
		$regular_expression = "([0-9]{2}\/[0-9]{2}\/[0-9]{4};[^;].+\r\n|[0-9]{2}\/[0-9]{2}\/[0-9]{4};[^;](.+\n)+?.+\r\n)";
		preg_match_all("#".$regular_expression."#",$source, $matches, PREG_SET_ORDER);
		$counter = 0;
		?>
		<table class="art-article" style="width:1124px">
		<thead>
		<tr>
		<?php $header_fields = array("Date of comment","Reviewer name", "Comment ref", "Comment category", 
		"Page/Chapter/Paragraph", "ECG comment", "Supplier Answer","Consecutive action","Comment status","QAMS id" );    
		foreach( $header_fields as $value ) {?>
			<th ><?php echo $value?></th>
		<?php } ?>			
		</tr>
		</thead>	
		<tbody>	
		<?php
		foreach ($matches as $val) {
			$counter++;
			$ligne = $val[0];
			$ligne = clean_line ($ligne);
			list($date_of_comment,$reader,$comment_ref,$comment_category,$paragraph,$description,$author_response,$consecutive_action,$remark_status,$qams_id) = explode($separateur,$ligne);  // Champs s�par�s par ;
			/*
			 * Find proof reader's name
			 */
			$customer = array(6,7);
			//$reader = find_poster ($reader,$customer);
			$test_poster->find_poster ($reader,$customer);
			$poster_id = $test_poster->id;
			$reader = $test_poster->name;
			
			/*
			 * Get criticality
			 */
			$test_crit = new Criticality;
			$test_crit->get_eurocopter_criticality ($comment_category);
			$crit_id = $test_crit->id;
			$comment_category = $test_crit->name;
			/*
			 * Get status
			 */
			$test_stat = new Status;
			$test_stat->get_eurocopter_status ($remark_status);
			$status_id = $test_stat->id;
			$remark_status = $test_stat->name;
			$date_of_comment = convert_date($date_of_comment);
			$description = clean_text($description);
			$abstract_remark=substr($description,0,16)."...";				
			if ($preview != "on") {
				if (Remark::find_remark($qams_id,$ref_id)) {
					//echo "remark".$ref_id."find in db <BR>";
					/* */
					/* input response */
					Remark::create_response	($author_response,
											$author_id,
											$application,
											$status_id,
											$qams_id);	
					/* update status of remark */
					Remark::update_remark_status($status_id,$qams_id);
				}
				else {
					/* no, input remark for the firt time*/
					//echo "no, input remark ".$ref_id." for the firt time <BR>";
					$query = "INSERT INTO `bug_messages` (`date`, `description`, `posted_by`, `subject`, `category`, `criticality`, `application`, `status`, `paragraph`,`line`,`reply_id`,`action_id`)".
							" VALUES('$date_of_comment',
									'$description', 
									'$poster_id', 
									'$abstract_remark', 
									65, 
									'$crit_id', 
									'$application', 
									'$status_id', 
									'$paragraph',
									'$line', 
									0,
									'')";
					$result_response = do_query($query);
					$updateReply = mysql_insert_id();
					/* update reply_id with id */
					$query = "UPDATE `bug_messages` SET `reply_id`='$updateReply' WHERE `id` = '$updateReply' AND `reply_id` = '0' LIMIT 1";
					//echo $query."<BR>";
					$result_response = do_query($query);
					/* input response */
					$response = Remark::create_response	($author_response,$author_id,$application,$status_id,$updateReply);	
					//if ($response)	
						//echo "response input db <BR>";					
				}	
			}
			else {
				if (find_remark($qams_id,$comment_ref)){
					//print "success to find remark {$comment_ref}, update remark response, status and justfication<BR>";
				}
				else{
					//print "fail to find remark<BR>";
				}
			}
			?>
			<tr>
			<td><?= $date_of_comment ?></td>
			<td><?= $reader ?></td>
			<td><?= $comment_ref ?></td>
			<td><?= $comment_category ?></td>
			<td><?= $paragraph ?></td>
			<td><?= $description ?></td>
			<td><?= $author_response ?></td>
			<td><?= $consecutive_action ?></td>
			<td><?= $remark_status ?></td>
			<td><?= $qams_id_found ?></td>
			</tr>
			<?php
		}
	}
	break;
	case "xls":
	case "xlsx":
		/* Read only Register sheet */
		$objWorksheet = Remark::scanPeerReviewFull($uploadName,
												  $type);
		$highestRow = $objWorksheet->getHighestRow(); // e.g. 10
		$highestColumn = 'I';//$objWorksheet->getHighestColumn(); // e.g 'F'
		$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn); // e.g. 5
		$nb_remarks=0;
		$find_remark_begin = false;
		$nb_known_remarks=0;
		$nb_new_remarks=0;
		$nb_response_remarks=0;
		$nb_unknown_poster=0;
		$res = Remark::read_ece_prr(&$objWorksheet,
									&$list_remarks,
									$data_id); 						
		$nb_remarks=$res['nb_remarks'];
        $nb_open_remarks = $res['open_remarks'];
		$nb_known_remarks = $res['nb_known_remarks'];
		$nb_new_remarks = $nb_remarks - $nb_known_remarks;
		$nb_response_remarks = $res['nb_response_remarks'];
	
		/* Copy all remarks for this document */
		// var_dump($statement);
		// exit();
			$max_id = Remark::getLastId();
			if (isset($_POST["remark_date"])) {
			  $date_dojo = $_POST["remark_date"];
			  Atomik::needed('Tool.class');
			  $remark_date = Date::convert_dojo_date($date_dojo);
			}
			$import_response = isset($_GET['import_response'])?$_GET['import_response']:"no";
			store_cmd("",true);
			if ($list_remarks != null){
				// $counter=0;
				foreach ($list_remarks as $row):
					// echo ($counter++)."<br/>";
                   // if ($preview != "on") {
				    $author_response = Tool::clean_text($row['author_response']);
					$description = $db->quote($row['description']);
					$justification = $db->quote($row['justification']);
                        if (isset($row['exists'])){
                            // echo "Found.<br/>";
                            $index = $row['qams_id'];
                            /* update status of remark */
                            if ((isset($_POST['remark_body']))&&($_POST['remark_body']=="locked")){
                           	    $sql_query = "UPDATE `bug_messages` SET ".
	                                                "`status`		='".$row['status_id']."' ,".
	                                                "`justification`	=".$justification.
	                                                " WHERE `id` 	='".$row['qams_id']."' LIMIT 1"; 	
                            }
                            else {
	                            $sql_query = "UPDATE `bug_messages` SET `description`=".$description." , ".
	                                                "`posted_by`	='".$row['poster_id']."' , 
	                                                `subject`		='' ,
	                                                `category`		='".$row['category_id']."' , 
	                                                `criticality`	='1' , ".
	                                                "`application`	='".$data->id."' ,
	                                                `status`		='".$row['status_id']."' ,
	                                                `date`			='$remark_date' ,
	                                                `paragraph`		='".$row['paragraph']."' ,
	                                                `line`			='".$row['line']."',
	                                                `justification`	=".$justification.
	                                                " WHERE `id` 	='".$row['qams_id']."' LIMIT 1";
                            }                    
                            //echo $sql_query."<br/>";	                   
                            $statement = $db->pdo_query($sql_query,true);
                            store_cmd($sql_query);

                            if ($row['author_response'] != ""){
                                /* input response */
                                // echo $row['author_response']."<br/>";
                                $sql_query = create_response  ($author_response,
															  $data->author_id,
															  $data->id,
															  $row['status_id'],
															  $index,
															  $db);
								store_cmd($sql_query);
                                // $nb_response_remarks++;
                            }   
                        }
                        else{
                            $index = ++$max_id;
                            /* insert */
                            // echo "Insert remarks";
                            $sql_query = "INSERT INTO bug_messages (
											`id`,
											`description`, 
											`posted_by`, 
											`subject`,
											`category`, 
											`criticality`, 
											`application`, 
											`status`, 
											`date`, 
											`paragraph`,
											`line`,
											`reply_id`,
											`justification`)".
										" VALUES('".$index."',
												".$description.", 
												'".$row['poster_id']."', 
												'', 
												'".$row['category_id']."', 
												1, 
												'".$data->id."', 
												'".$row['status_id']."',
												'$remark_date',
												'".$row['paragraph']."',
												'".$row['line']."',
												'$index',
												".$justification.")";  
                            // echo $sql_query."<br/>";
                            store_cmd($sql_query);
                            $statement = $db->exec($sql_query,true);

                            if ($row['author_response'] != ""){
                                /* input response */
                                // echo $row['author_response']."<br/>";
                                $sql_query = create_response  ($author_response,
															  $data->author_id,
															  $data->id,
															  $row['status_id'],
															  $index,
															  $db);
								store_cmd($sql_query);				  
                                // $nb_response_remarks++;
                            }       
                        //}
					}
				endforeach;
			}
		break;	
}
$date_sql = Date::convert_dojo_date($date_dojo);
$date_display = Date::convert_date($date_sql);
$html="";
if(isset($_GET['import'])){
	if ($nb_known_remarks == 0) {
		$html = "No updated remark.<br/>";
	} 
	else { 
		$html = "<b>".$nb_known_remarks."</b> already existing remarks have been updated.<br/>";
	}
	if ($nb_new_remarks == 0) {
		$html .= "No new remark.<br/>";
	}
	else { 
		$html .=  $nb_new_remarks."</b> remarks are new.<br/>";
	} 
	if ($nb_response_remarks == 0) {
		$html .= "No new reponses to remarks.<br/>";
	} 
	else { 
		$html .= "<b>".$nb_response_remarks."</b> response have been inserted into database.<br/>";
	}
}
else{
	if ($nb_known_remarks == 0) {
		$html = "No updated remark.<br/>";
	} 
	else { 
		$html = "<b>".$nb_known_remarks."</b> already existing remarks have been found.<br/>";
	}
	if ($nb_new_remarks == 0) {
		$html .= "No new remark.<br/>";
	}
	else { 
		$html .=  "<b>".$nb_new_remarks."</b> remarks are new.<br/>";
	} 
	if ($nb_response_remarks == 0) {
		$html .= "No new reponses to remarks found.<br/>";
	} 
	else { 
		$html .= "<b>".$nb_response_remarks."</b> new response have been found.<br/>";
	}
	if ($nb_open_remarks == 0) {
		$html .= "All remarks are closed.<br/>";
	} 
	else { 
		$html .= "<b>".$nb_open_remarks."</b> remarks are open.<br/>";
	}	
}
/* Display bar graph */
// $statement = $db->pdo_query("SELECT * FROM bug_messages WHERE application = ".$data->id,true);
// foreach ($statement as $row){
	// echo $row['id']."<br/>";
// };
$remarks = new StatRemarks($data->id,&$db);
$peer_reviewers = new PeerReviewer();
$peer_reviewers->get($data->id);
// $remarks->set();
/* graph */
if ($remarks->amount_remarks > 0){
	$bar_filename = '../result/remarks_bar.png';
	$pie_filename = '../result/peer_reviewers_pie.png';
	$remarks->drawBar($bar_filename);
	$peer_reviewers->drawPie($pie_filename,"Authors of remarks");
	$bar_filename = '../'.$bar_filename;
	$pie_filename = '../'.$pie_filename;	
}
$left = '<form id="import_prr" name="import_prr" method="POST" action="'.Atomik::url('peer_review/import_prr').'" enctype="multipart/form-data">';  
$left .= '<input type="hidden" name="type_remark" value="'.$type_remark.'"/>';
$left .= '<input type="hidden" name="format_remark" value="'.$type.'"/>';
$left .= '<input type="hidden" name="filename" value="'.$uploadName.'"/>';
$left .= '<input type="hidden" name="filesize" value="'.$uploadSize.'"/>';
$left .= '<input type="hidden" name="data" value="'.$application.'"/>';
$left .= '<input type="hidden" name="remark_date" value="'.$date_dojo.'"/>';
$left .= '<input type="hidden" name="nb_remarks" value="'.$nb_remarks.'"/>';
$left .= '<span class="art-button-wrapper">';
$left .= '<span class="l"> </span>';
$left .= '<span class="r"> </span>';
$left .= '<input class="art-button" name="submit_import" type="submit" value="Import" ></span>';
// $left .= '<span class="art-button-wrapper">';
// $left .= '<span class="l"> </span>';
// $left .= '<span class="r"> </span>';
// $left .= '<input class="art-button" name="submit_w_response" type="submit" value="Import with respons"></span>';
$left .= '<span class="art-button-wrapper">';
$left .= '<span class="l"> </span>';
$left .= '<span class="r"> </span>';
$left .= '<input class="art-button" name="submit_cancel" type="submit" value="Cancel"></span>';
$left .= '</form>';
Atomik::set('select_menu',$left);
