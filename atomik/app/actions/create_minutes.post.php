<?php
require_once '../word/PHPWord/PHPWord.php';
$PHPWord = new PHPWord;
$file_template = dirname(__FILE__).
				DIRECTORY_SEPARATOR."..".
				DIRECTORY_SEPARATOR."..".
				DIRECTORY_SEPARATOR."assets".
				DIRECTORY_SEPARATOR."template".
				DIRECTORY_SEPARATOR."SAQ086 compte rendu reunion_with_actions_table_5.docx.docx";
$document = $PHPWord->loadTemplate($file_template);
$context_array['project_id']= Atomik::has('session/current_project_id')?Atomik::get('session/current_project_id'):Atomik::get('session/project_id');
$context_array['sub_project_id']= Atomik::has('session/sub_project_id')? Atomik::get('session/sub_project_id'):Atomik::get('session/sub_project_id');
Atomik::needed('Project.class');
$project_ = new Project(&$context_array);
$project = $project_->getProjectName();
$equipment = $project_->getSubProjectName();

$memo_reference     = isset($_POST['reference']) ? $_POST['reference'] : "";
$memo_id   = isset($_POST['memo_id']) ? $_POST['memo_id'] : "";
$subject   = isset($_POST['subject']) ? $_POST['subject'] : "";
$meeting_attendee   = isset($_POST['attendees']) ? $_POST['attendees'] : "";
$memo_subject 	= $project." ".$equipment." ".$subject;
//$filename = 'result/'.$review_description." Memo.docx";
//echo $_POST['description'];
Atomik::needed('Tool.class');
$memo_body = Tool::clean_text($_POST['description']);
$memo_location  = "Paris";
$today_date 	= date("d").' '.date("F").' '.date("Y");
$meeting_date   = date("d").' '.date("m").' '.date("y");
$meeting_missing = "";
$meeting_copy = "";
Atomik::needed('User.class');
$user = new User;
$user->get_user_info(User::getIdUserLogged());
$name = $user->name;
$email = $user->email;
$phone = $user->phone;
$department = $user->service;
//$name = $userLogFname." ".$userLogLname;
//$email = $userLogFname.".".$userLogLname."@zodiacaerospace.com";
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
//$document->setValue('Value8', $memo_body);
$filename = '../result/'.$memo_reference;
if ($project != ""){
	$filename .= "_".$project;
}
if ($equipment != ""){
	$filename .= "_".$equipment;
}
$filename .= '.docx';
$document->save($filename);
$flash = '<a href="'.$filename.'" >';
$flash .= '<img alt="Download memo" title="Donload memo" border="0" src="assets/images/128x128/120px-OfficeWord.png" class="img_button" />';
$flash .= '</a>';
Atomik::Flash("Memo ref. ".$memo_reference." created. Available here: ".$flash,"success");
Atomik::redirect('home',false);
