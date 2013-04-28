<?php
Atomik::needed('Date.class');
Atomik::needed('Data.class');
Atomik::needed('Review.class');
Atomik::needed('Company.class');

$context['company_id']= Atomik::get('session/company_id');
$context['scope_id']= Atomik::get('session/scope_id');
$input_width=60;
$fill = false;
$review_type = new Type (&$context);
if (isset($_GET['tab'])){
	switch ($_GET['tab']){
		case "type":
			Atomik::set('type_form_highlight','active');
			break;
		case "checklist":
			Atomik::set('checklist_highlight','active');
			break;		
	}
}
Atomik::set('tab_select','review');
if(isset($_GET['id']) && ($_GET['id'] != 0)) {
    $update_id=$_GET['id'];
    unset ($_REQUEST['id']);
    $update="yes";
    $title ="Edit review type";
    $button="Update";

	$review_type->get($update_id);
	$list_questions = Question::getQuestionsList($update_id);    
}
else {
    $title ="Add Review type";
    $button="Add Review type";
    $update="no";
    $update_id="0";
    $list_questions = array();
}
$sql_query = "SELECT id,scope FROM scope ORDER BY scope ASC";
$list_scope = A('db:'.$sql_query);
$html = '<div style="width:450px"><a href="'.Atomik::url('edit_checklist',array('checklist_id'=>$review_type->id)).'" target="_blank" style="text-decoration: none;outline-width: medium;outline-style: none;width:50%;float:left" title="Add new entry">';
$html .= '<img src="'.Atomik::asset('assets/images/newobject.gif').'" class="systemicon" width="32" alt="Add new action" title="Add new action" border="no" />Add new question</a></div><br/>';
//$html .='<div class="my_menu" style="width:200px"><ul><li class="export_excel"><h2><a href="#" onclick="return review_excel_get_checkbox_value();" border="0" >Export</a></h2></li></ul></div>';
$html .='<p><a href="" ><img alt="Export checklist" title="Export checklist" border="0" src="'.Atomik::asset('assets/images/32x32/Excel2007.png').'" class="no_img_button" width="32" height="32" />Export Checklist</a></p>';
$html .='<p><a href="'.Atomik::url("review_type",false).'" ><img src="'.Atomik::asset('assets/images/pages/sommaire.png').'" border="0" alt="Back" title="Back"><h2>Back</h2></a></p>';
Atomik::set('select_menu',$html);
Atomik::set('title',$title);
Atomik::set('css_title',"data");
Atomik::set('css_reset',"no_show");
Atomik::set('css_add',"no_show");
Atomik::set('url',"list_eqpt");
Atomik::set('url_add',Atomik::url('edit_eqpt'));
Atomik::set('title_add',"Add an equipement");
Atomik::set('css_page_previous','no_show');	
Atomik::set('css_page_next','no_show');	
Atomik::set('css_page',"no_show");
