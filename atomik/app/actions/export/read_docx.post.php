<?php
$postArray = &$_POST;
if (isset($postArray['show_project'])) {
	Atomik::set('session/project_id',$postArray['show_project']);
}
if (isset($postArray['show_lru'])){
	Atomik::set('session/sub_project_id',$postArray['show_lru']);
}
if (isset($postArray['show_type'])) {
	Atomik::set('session/type_id',$postArray['show_type']);	
}
Atomik::redirect('read_docx',false);
