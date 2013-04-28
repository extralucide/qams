<h3> Minutes Of the Meeting generated</h3>
<?php
require_once 'word/PHPWord/PHPWord.php';
//takes in single DB letter data
function evalLetter($data,$filename){
		 $PHPWord = new PHPWord();
		 $template = $PHPWord->loadTemplate('templates/'.$data['code'].'.tpl.docx');
		 $template->setValue('ACCOUNTNUMBER',$data['accountnumber']);
		 $select = '
		 SELECT * FROM data_debts,data_clients WHERE data_debts.accountnumber="'.$accountnumber.'" AND data_debts.currentamt>"0" AND data_debts.clientname = data_clients.accountnumber
		 ';
		 $results = queryDB($select); //my queryDB function returns an object or false, depending on whether it gets a hit or not - so YMMV depending on how you return your query data
		 $table = ''; //empty table
		 if($results){ //if there was data returned from queryDB()
		 		 $table .= '<w:tbl>';
		 		 $table .= '<w:tblPr><w:tblW w:w = "5000" w:type="pct"/></w:tblPr>';
		 		 foreach($results as $debt){
		 		 		 $table .= '<w:tr>'; //new xml table row
		 		 		 $table .= '<w:tc><w:p><w:r><w:t>'; //start cell
		 		 		 $table .= $debt->clientname; //cell contents
		 		 		 $table .= '</w:t></w:r></w:p></w:tc>'; //close cell
		 		 		 $table .= '<w:tc><w:p><w:r><w:t>';
		 		 		 $table .= $debt->clientaccount;
		 		 		 $table .= '</w:t></w:r></w:p></w:tc>';
		 		 		 $table .= '<w:tc><w:p><w:r><w:t>';
		 		 		 $table .= $debt->regard;
		 		 		 $table .= '</w:t></w:r></w:p></w:tc>';		 		 
		 		 		 $table .= '<w:tc><w:p><w:r><w:t>';
		 		 		 $table .= int2cash($debt->currentamt);
		 		 		 $table .= '</w:t></w:r></w:p></w:tc>';
		 		 		 $table .= '</w:tr>';
		 		 } //done with dynamic data
		 		 $table .= '</w:tbl>'; //close xml table
		 		 $template->setValue('ACCOUNTINFO',$table); //insert xml into template
		 
		 }else{
			setAlert('error','No info for this account'); return 0;
		} //returns 0 if proc failed
		 $template->save('data/'.$filename.''); //save filled out template
		 return 1; //returns 1 if everything worked
 }

$PHPWord = new PHPWord();

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
            return strip_tags($xml->saveXML());
        }
        $zip->close();
    }

    // In case of failure return empty string
    return "error no archive file received from ".$archiveFile;
}
$document = $PHPWord->loadTemplate('template/SAQ086 compte rendu reunion_with_actions_table_5.docx');//Template.docx
$project= "";
$equipment= "";
$review="";
$review_id_type == "";
if ((isset($_POST['show_project'])) && ($_POST['show_project']!="")){
	$project		= get_name("project","projects","id",$_POST['show_project']);
}
if ((isset($_POST['show_lru'])) && ($_POST['show_lru']!="")){
	$equipment		= get_name("lru","lrus","id",$_POST['show_lru']);
}
//if (isset($_POST['reference'])){
//	$reference		= get_name("application","bug_applications","id",$_POST['reference']);
//}
// if (isset($_POST['show_review'])){
	// $review 		= get_name("review","reviews","id",$_POST['show_review']);
// }
//if (isset($_POST['id_type'])){
	//echo $_POST['id_type']."<br/>";
//	$review_id_type = get_name("type","review_type","id",$_POST['id_type']);
//}
$memo_reference = $_POST['reference'];
$memo_subject 	= convert_html2txt($_POST['subject']);
$memo_subject 	= $project[project]." ".$equipment[lru]." ".$memo_subject." ".$review_id_type['name'];
$filename = 'result/'.$memo_reference."_".$project[project]."_".$equipment[lru]."_".$review_id_type['name']." MoM.docx";
$memo_location  = "Paris";
$today_date 	= date("d").' '.date("F").' '.date("Y");
$meeting_date   = date("d").' '.date("m").' '.date("y");
$meeting_missing = "";
$meeting_copy = "";
//echo $_POST['description'];
$memo_body    	= clean_text($_POST['description']);
$meeting_attendee = $_POST['attendees'];
$user = new User;
$user->get_user_info($userLogID);
$name = $user->name;
$email = $user->email;
$phone = $user->phone;
$department = $user->service;
	
$document->setValue('Value1',$name );
$document->setValue('Value2', $department);
$document->setValue('Value3', $phone);
$document->setValue('Value4', ''); /* Fax */
$document->setValue('Value5',$email );
$document->setValue('Value6', $today_date);
$document->setValue('Value7', $memo_subject);
$document->setValue('Value8', $memo_body);
$document->setValue('Value9', $memo_reference);
$document->setValue('Value10', $memo_location);
$document->setValue('Value11', $meeting_date);
//$document->setValue('Value12', $meeting_attendee);
$document->setValue('Value13', $meeting_missing);
$document->setValue('Value14', $meeting_copy);
// 1 : on ouvre le fichier
$file_list_attendees = 'export/list_users.txt';
if (file_exists($file_list_attendees)) {
	$monfichier = fopen($file_list_attendees, 'r');
}
else {
$monfichier = false;
}
if($monfichier) {
	$table .= '<w:tbl>';
	$table .= '<w:tblPr><w:tblW w:w = "5000" w:type="pct"/></w:tblPr>';
	while (($user_id = fgets($monfichier, 4096)) !== false) {
		if ($user_id != ""){
			$user->get_user_info($user_id);
			 $table .= '<w:tr>'; //new xml table row
			 $table .= '<w:tc><w:p><w:r><w:t>'; //start cell
			 $table .= convert_html2txt($user->company_name); //cell contents
			 $table .= '</w:t></w:r></w:p></w:tc>'; //close cell					 
			 $table .= '<w:tc><w:p><w:r><w:t>'; //start cell
			 $table .= convert_html2txt($user->name); //cell contents
			 $table .= '</w:t></w:r></w:p></w:tc>'; //close cell
			 $table .= '<w:tc><w:p><w:r><w:t>';
			 $user_function = clean_text($user->user_function);
			 $table .= $user_function;
			 //echo $user_function."<br/>";
			 $table .= '</w:t></w:r></w:p></w:tc>';
			 $table .= '</w:tr>';	
		}
	}
	$table .= '</w:tbl>'; //close xml table	
	$document->setValue('Value58',$table); //insert xml into template
}
$document->setValue('Value59',"");
$memo_name = 'result/'.$memo_reference." ".$memo_subject.'.docx';
$document->save($filename);
?>
<a href="<?php echo $filename ?>" >
<img alt="Export openxml" title="Export openxml" border=0 src="images/128x128/120px-OfficeWord.png" class='img_button' />
</a>                     
