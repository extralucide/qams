<?php
require_once 'word/PHPWord/PHPWord.php';

$PHPWord = new PHPWord;
$file_template = dirname(__FILE__).
				DIRECTORY_SEPARATOR."..".
				DIRECTORY_SEPARATOR."..".
				DIRECTORY_SEPARATOR."assets".
				DIRECTORY_SEPARATOR."template".
				DIRECTORY_SEPARATOR."SAQ204 memo.docx";			
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
$memo_subject 	= $project." ".$equipment." ".$subject;
//$filename = 'result/'.$review_description." Memo.docx";
//echo $_POST['description'];
Atomik::needed('Tool.class');
$memo_body = Tool::clean_text($_POST['description']);
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
$document->setValue('Value4', '');
$document->setValue('Value5',$email );
$document->setValue('Value6', date("d").' '.date("F").' '.date("Y"));
$document->setValue('Value7', $memo_subject);
$document->setValue('Value8', $memo_body);
//$document->setValue('Value8', $memo_body);
$filename = '../result/'.$memo_reference."_".$project."_".$equipment.'.docx';
$document->save($filename);
Atomik::Flash("Memo ref. ".$memo_reference." created.","success");
Atomik::redirect('create_memo_select?memo_id='.$memo_id,false);
