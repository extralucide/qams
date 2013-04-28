<?php
Atomik::needed("Db.class");
Atomik::needed("User.class");
Atomik::needed("Date.class");
Atomik::needed("Data.class");
Atomik::needed("Project.class");
Atomik::needed("Review.class");
Atomik::needed("Baseline.class");
$line_counter = 0;
if (isset($_GET['tab'])){
	switch ($_GET['tab']){
		case "description":
			Atomik::set('description_highlight','active');
			break;
		case "attendee":
			Atomik::set('attendee_highlight','active');
			break;
		case "baseline":
			Atomik::set('baseline_highlight','active');
			break;
		case "actions":
			Atomik::set('actions_highlight','active');
			break;				
		case "minutes":
			Atomik::set('minutes_highlight','active');
			break;
		case "attachment":
			Atomik::set('attachment_highlight','active');
			break;			
	}
}
Atomik::set('tab_select','review');

$context['id'] = isset($_REQUEST['id']) ? $_REQUEST['id'] : "";
$context['aircraft_id']= Atomik::has('session/current_aircraft_id')?Atomik::get('session/current_aircraft_id'):"";
$context['project_id']= isset($show_project) ? isset($show_project) : (Atomik::has('session/current_project_id')?Atomik::get('session/current_project_id'):Atomik::get('session/project_id'));
$context['sub_project_id']=isset($show_lru) ? isset($show_lru) : "";
$context['sub_project_id']=(Atomik::has('session/sub_project_id')?Atomik::get('session/sub_project_id'):"");
$context['type_id'] = (Atomik::has('session/review_type_id')?Atomik::get('session/review_type_id'):"");

$show_status = "";
$description_highlight="";
$attendee_highlight="";
$baseline_highlight="";
$minutes_highlight="";
$add_app_author="";
$param = "";	
$fill = false;

$review = new Review(&$context); 
$project = new Project(&$context);
$baseline = new Baseline;
 if ($context['id'] != ""){    
	$button = "Update"; 
	$update_review = "yes";
	$review->get($context['id']); 
	$review_id   = $review->id;
	$previous_review_id   = $review->previous_id;
	$managed_by   = $review->managed_by;
	$project->setProject($review->project_id);
	$context['project_id']= $review->project_id;
	$project->setSubProject($review->lru_id);
	$context['sub_project_id']=$review->lru_id;
	$context['type_id'] = $review->type_id;
	$current_date = $review->date_sql;
	$end_date     = $review->date_end_sql;
	$file_attached_info = new Data;
	$file_attached_info->get($review->report_link_id);
	if (($file_attached_info->link != "")&&(Data::Get_File_Ext($file_attached_info->filename)=="pdf") ){
		$first_page = $file_attached_info->Create_First_Page();	
	}
	else {
		$first_page='<iframe class="iframe" width="1160" height="800" src="'.Atomik::url('review/display_mom',array('review_id'=>$review_id)).'" ></iframe>';
	}
	$minutes_list_linked = $review->getMinutes();
	$minutes_list = $review->getAllMinutes();
	$list_data = $review->getData();
	$list_actions = $review->getActions();
	$list_baseline = $baseline->getBaselineList();
	$list_baseline_linked = $review->getBaseline(true); /* get all projects */
	$list_attached = $review->getAllAttached();
	$list_questions = Question::getQuestionsList($review->type_id);
$html = '<div class="remark" style="width:350px">';
$html .= '<a href="'.Atomik::url('post_action',array('review'=>$review->id)).'" target="_blank" style="text-decoration: none;outline-width: medium;outline-style: none;width:50%;float:left" title="Add new entry">';
$html .= '<img src="'.Atomik::asset('assets/images/newobject.gif').'" class="systemicon" width="32" alt="Add new action" title="Add new action" border="no" />Add new action</a>';
$html .='</div>';
$html .= "<table class='art-article'>";
$html .="<tbody><tr class='vert'>";
if ($review->id != "") {
	/* search previous review */
	$html .= $review->getPrevious();
	$html .= '<td colspan="5">'.$review->title.'</td>';
	$html .='<td>';
	if ($review->link != "empty") {
		$html .='<a href="'.$review->link.'">';
	}
	$html .='<img alt="Minutes attached" title="Minutes attached" width="32" height="32" border="0" src="'.$review->link_mime.'" />';
	if ($review->link != "empty") {
		$html .='</a>';
	}
	$html .='</td>';
	$html .= '<td><a href="#" onclick="send_minutes('.$review->id .')"><img alt="Send mail" title="Send mail" width="32" height="32" border="0" src="assets/images/32x32/mail_send.png"></a></td>';
	$html .= $review->getNext();
}
$html .="</tr></tbody></table>";
$html .= <<<EOF
<h3 class="edit"style="color:#000" >Import Action Items</h3>
EOF;
$html .= '<form class="post_" id="import_prr" name="import_prr" method="post" action="'.Atomik::url('action/import').'" enctype="multipart/form-data">';
$html .= <<<EOF
<fieldset>
<input type="hidden" name="MAX_FILE_SIZE" value="2097152"/>
<label for='filename'>Filename</label>
<input class="no_file" type="file" name="filename" id="filename"/><br/>
EOF;
$html .= '<input type="hidden" name="review_id" value="'.$review_id.'"/>';
$html .= <<<EOF
<span class="art-button-wrapper">
<span class="l"> </span>
<span class="r"> </span>
<input class="art-button" type="submit" name="submit" value="Import"/>
</span>
</fieldset>
</form>
EOF;
}
else {
	$review_id = "";
	$button = "New"; 
	$update_review = "no";
	$current_date = date("Y")."-".date("m")."-".date("d");
	$end_date = $current_date;
	$first_page="";
	$minutes_list = false;
	$list_data = false;
	$list_actions = false;
	$list_baseline = false;
	$list_baseline_linked = false;
	$list_attached = false;
	$list_questions = array();
	$html = "";
 }
$list_eqpt = $project->getSubProjectList();

$html .='<p><a href="'.Atomik::url("show_reviews",false).'" ><img src="'.Atomik::asset('assets/images/pages/sommaire.png').'" border="0" alt="Back" title="Back"><h2>Back</h2></a></p>';
Atomik::set('select_menu',$html);
Atomik::set('title',"Post Review");
Atomik::set('css_title',"review");
Atomik::set('css_reset',"no_show");
Atomik::set('css_add',"no_show");
Atomik::set('url',"show_review");
Atomik::set('url_add',Atomik::url('data_type'));
Atomik::set('title_add',"Add a type");
Atomik::set('css_page_previous','no_show');	
Atomik::set('css_page_next','no_show');	
Atomik::set('css_page',"no_show");
// var_dump($review);