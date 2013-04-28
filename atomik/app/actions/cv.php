<?php
Atomik::needed('Date.class');
Atomik::needed('User.class');
$id = isset($_GET['id']) ? $_GET['id'] : "";
$sql_query = "SELECT enterprises.name as company, cv.qualification, cv.description,cv.keywords,cv.languages,date_start,date_end FROM cv LEFT OUTER JOIN enterprises ON enterprises.id = company_id ORDER BY date_start DESC";
$list_items = A('db:'.$sql_query);
$user = new User;
$user->get_user_info(User::getIdUserLogged()); 

$html = '<img style="padding:10px" src="'.$user->photo_file.'" alt="" width="144" height="192" >';
$html .= "<p><a href='".Atomik::url('edit_user',array('edit_user_id'=>$user->id))."'>".$user->getFullName()."</a></p>"; 
$html .= "<h3>Formation</h3>"; 
$html .= $user->getProperty(); 
$html .='<p><a href="'.Atomik::url("edit_user",array('edit_user_id'=>$id),false).'" ><img src="'.Atomik::asset('assets/images/pages/sommaire.png').'" border="0" alt="Back" title="Back"><h2>Back</h2></a></p>';
Atomik::set('select_menu',$html);
Atomik::set('title',"Curriculum Vitae");
Atomik::set('css_title',"user");
Atomik::set('css_reset',"no_show");
Atomik::set('url',"report_bugs");
Atomik::set('url_add',Atomik::url('report_bugs'));
Atomik::set('css_add',"no_show");
Atomik::set('title_add',"Add a type");
Atomik::set('css_page_previous','no_show');	
Atomik::set('css_page_next','no_show');	
Atomik::set('css_page',"no_show");