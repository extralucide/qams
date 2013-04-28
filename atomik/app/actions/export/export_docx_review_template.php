<?php
Atomik::needed('Review.class');
Atomik::needed('Project.class');
Atomik::needed('Logbook.class');
require_once '../word/PHPWord/PHPWord.php';

$PHPWord = new PHPWord;
$file_template = dirname(__FILE__).
				DIRECTORY_SEPARATOR."..".
				DIRECTORY_SEPARATOR."..".
				DIRECTORY_SEPARATOR."..".
				DIRECTORY_SEPARATOR."assets".
				DIRECTORY_SEPARATOR."template".
				DIRECTORY_SEPARATOR."SAQ086 compte rendu reunion_with_actions_table_6.docx";
print $file_template;
$document = $PHPWord->loadTemplate($file_template);
$context_array['aircraft_id']= Atomik::has('session/current_aircraft_id')?Atomik::get('session/current_aircraft_id'):"";
$context_array['project_id']= Atomik::has('session/current_project_id')?Atomik::get('session/current_project_id'):Atomik::get('session/project_id');
$context_array['sub_project_id']= Atomik::has('session/sub_project_id')? Atomik::get('session/sub_project_id'):Atomik::get('session/sub_project_id');

$logbook = new Logbook(&$context_array);
// $project_ = new Project(&$context_array);
// $project = $project_->getProjectName();
// $equipment = $project_->getSubProjectName();

$context['id'] = isset($_GET['id']) ? $_GET['id'] : "";
$review = new Review(&$context);
 if ($context['id'] != ""){    
	$review->get($context['id']);
	$minutes_list_linked = $review->getMinutes();
	foreach ($minutes_list_linked as $row):
		// $document = new Data;
		// $document->get($row->data_id);
		$memo_reference = $row->reference;
		$memo_id = $row->data_id;
		$subject   = "";
		$meeting_attendee   = "";
		// $memo_subject 	= $logbook->board." ".$subject;		
		/*  first reference only */
		break;
	endforeach;
}
// $memo_reference     = isset($_POST['reference']) ? $_POST['reference'] : "";
// $memo_id   = isset($_POST['memo_id']) ? $_POST['memo_id'] : "";
// $subject   = isset($_POST['subject']) ? $_POST['subject'] : "";
// $meeting_attendee   = isset($_POST['attendees']) ? $_POST['attendees'] : "";
$memo_subject 	= $logbook->board.": ".$review->type;
//$filename = 'result/'.$review_description." Memo.docx";
//echo $_POST['description'];
Atomik::needed('Tool.class');
$memo_body = Tool::convert_html2txt($review->description);
$memo_location  = "Paris";
$today_date 	= date("d F Y");
$meeting_date   = date("d/m/y");
$meeting_missing = "";
$meeting_copy = "";
Atomik::needed('User.class');
$user = new User;
$user->get_user_info(User::getIdUserLogged());
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
	// echo "<pre>".$memo_body."</pre>";
if (1==1){		
	$document->setValue('Value9', $memo_reference);
	$document->setValue('Value10', $memo_location);
	$document->setValue('Value11', $meeting_date);
	//$document->setValue('Value12', $meeting_attendee);
	$document->setValue('Value13', $meeting_missing);
	$document->setValue('Value14', $meeting_copy);
	/* create attendees table */

	if($review->attendees != null) {
		$table = '<w:tbl>';
		$table .= '<w:tblPr><w:tblW w:w = "5000" w:type="pct"/></w:tblPr>';
		foreach ($review->attendees as $id => $user) {
			 $table .= '<w:tr>'; //new xml table row
			 $table .= '<w:tc><w:p><w:r><w:t>'; //start cell
			 $table .= Tool::convert_html2txt($user['company']); //cell contents
			 $table .= '</w:t></w:r></w:p></w:tc>'; //close cell					 
			 $table .= '<w:tc><w:p><w:r><w:t>'; //start cell
			 $table .= Tool::convert_html2txt($user['fname']." ".$user['lname']); //cell contents
			 $table .= '</w:t></w:r></w:p></w:tc>'; //close cell
			 $table .= '<w:tc><w:p><w:r><w:t>';
			 $user_function = Tool::clean_text($user['function']);
			 $table .= $user_function;
			 $table .= '</w:t></w:r></w:p></w:tc>';
			 $table .= '</w:tr>';	
		}
		$table .= '</w:tbl>'; //close xml table	
		$document->setValue('Value58',$table); //insert xml into template
	}
	$document->setValue('Value59',"");
}
//$document->setValue('Value8', $memo_body);
$filename = '../result/'.Tool::cleanFilename($memo_reference." ".$logbook->board.'.docx');
$document->save($filename);
$flash = '<a href="'.$filename.'" >';
$flash .= '<img alt="Download memo" title="Donload memo" border="0" src="assets/images/32x32/OfficeWord.png" class="img_button" />';
$flash .= '</a>';
Atomik::Flash("Memo ref. ".$memo_reference." created. Available here: ".$flash,"success");
// exit();
Atomik::redirect('show_reviews');
