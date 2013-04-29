<?php
$tab_select = "data";
Atomik::needed("Db.class");
Atomik::needed("User.class");
Atomik::needed("Baseline.class");
Atomik::needed("Date.class");
Atomik::needed("Data.class");
Atomik::needed("Remark.class");
Atomik::needed("Project.class");
Atomik::needed("PeerReviewer.class");
//include "../display_rtf.php";
require_once "../phpuploader/include_phpuploader.php"; 
$db = new Db;
$line_counter = 0;
$today_date = date("Y-m-d");	
$upload_status = "";
$button_value="Update";	
/* Get parameters */
$data_id 		= isset($_REQUEST['id']) ? $_REQUEST['id'] : "";
$copy_data 		= isset($_GET['copy_data']) ? $_GET['copy_data'] : "";
$baseline_tag   = isset($_REQUEST['baseline_tag']) ? $_REQUEST['baseline_tag'] : "";
$multi_modify 	= isset($_REQUEST['multi_modify']) ? $_REQUEST['multi_modify'] : "";
$group 			= isset($_REQUEST['group']) ? $_REQUEST['group'] : ""; 
if (isset($_GET['tab'])){
	switch ($_GET['tab']){
		case "description":
			Atomik::set('description_highlight','active');
			break;	
		case "baseline":
			Atomik::set('baseline_highlight','active');
			break;
		case "traceability":
			Atomik::set('traceability_highlight','active');
			break;
		case "attachment":
			Atomik::set('attachment_highlight','active');
			break;
		case "impact":
			Atomik::set('impact_highlight','active');
			break;			
		case "peer_review":
			Atomik::set('peer_review_highlight','active');
			break;
		case "quality":
			Atomik::set('quality_highlight','active');
			break;			
	}
}
$context_array['project_id']= isset($_GET['show_project']) ? $_GET['show_project'] : (Atomik::has('session/current_project_id')?Atomik::get('session/current_project_id'):Atomik::get('session/project_id'));
$context_array['sub_project_id']= isset($_GET['show_lru']) ? $_GET['show_lru'] : Atomik::get('session/sub_project_id');
$context_array['type_id']= isset($_GET['show_type']) ? $_GET['show_type'] : (Atomik::has('session/type_id')?Atomik::get('session/type_id'):"");

$data = new Data(&$context_array);
/* New document input */
if (isset($_GET['new'])){ 
	//var_dump($data);exit();
	$new_id = $data->add_application($data->project_id,
									 &$data->reference,
									 $data->version,
									 $data->lru_id,
									 $data->type_id,
									 $data->description,
									 $data->abstract,
									 $data->status_id,
									 $data->date_published_sql,
									 "",
									 "",
									 $data->date_review_sql,
									 $data->author_id,
									 ""); /* Previous data */										 
	if  ($new_id == "") {
		$error = "Data adding failed !";
		$status = "failed";
	}
	else {
		$error = "Data ".$data->reference." successfully added !";
		$status = "success";
	}
	Atomik::flash($error, $status);
	Atomik::redirect('edit_data?id='.$new_id.'&test=007',false);
} 
if ($data_id != ""){
   $result=$data->get($data_id);
   if ($data->status == "Approved"){
		Atomik::set('date_hidden',"no_show");
   }
   if ($result === false){
		Atomik::flash('This document does not exist in database.','failed');	
		Atomik::redirect('data',false);
   }
	$list_attached = $data->getAllAttached();
	// var_dump($list_attached);
	$project = new Project(array("project_id"=>$data->project_id,
								"sub_project_id"=>$data->lru_id));
   /* store data in last read table */
   $result = Atomik_Db::find("last_data_read","data_id = ".$data->id);
   if ($result){
   		A('db:UPDATE last_data_read SET read_date = NOW() WHERE data_id = '.$data->id);
   }
   else{
   		Atomik_Db::insert('last_data_read',array('data_id'=>$data->id,'user_id'=>User::getIdUserLogged()));
   }
   /* Keep always 20 items maximum */
   $sql_query = "DELETE FROM last_data_read WHERE id NOT IN ( SELECT id FROM ( SELECT id FROM last_data_read ORDER BY read_date DESC LIMIT 40) x )"; 
   A('db:'.$sql_query);
   /* EPR, HPR or SPR*/
   if ($data->isPr()){
	  /* PR */
	  $data_type = 'hpr';
	  $pr_treatment ="yes";
   }
   else {
	  /* data */
	  $data_type = 'data';
	  $pr_treatment ="no";
   } 
   $peer_review_requested = $data->peer_review_requested;
   /* Copy document */
	if (isset($_GET['copy_data'])){  
		$new_id = $data->add_application($data->project_id,
										 &$data->reference,
										 $data->version,
										 $data->lru_id,
										 $data->type_id,
										 $data->description,
										 $data->abstract,
										 $data->status_id,
										 $data->date_published_sql,
										 "",
										 "",
										 $data->date_review_sql,
										 $data->author_id,
										 $data->previous_data_id); /* Previous data */
		if  ($new_id == "") {
			$error = "Data copy failed !";
			$status = "failed";
		}
		else {
			$error = "Data ".$data->reference." successfully copied !";
			$status = "success";
		}
		Atomik::flash($error, $status);	
		Atomik::redirect('edit_data?id='.$new_id,false);
	}
	/* get data remarks statistics */
	$remarks = new StatRemarks;
	$remarks->get($data->id);
	// var_dump($remarks);
	/* get data peer reviewers statistics */
	$peer_reviewers = new PeerReviewer;	
	$peer_reviewers->get($data->id,false);
	// $peer_reviewers = new PeerReviewer($data->id,
										// $data->reference,
										// $data->version);
	// var_dump($peer_reviewers);
	/* graph */
	$bar_filename = '../result/remarks_bar.png';
	$pie_filename = '../result/peer_reviewers_pie.png';
	if ($remarks->amount_remarks > 0){
		$remarks->drawBar($bar_filename);
		$peer_reviewers->drawPie($pie_filename,"Authors of remarks");
	}   
}
else {
	Atomik::flash('This document does not exist in database.','failed');	
	Atomik::redirect('data',false);
}

if (isset($_GET['tab'])){
	switch ($_GET['tab']){
		case "description":
			Atomik::set('description_highlight','active');
			break;	
		case "baseline":
			Atomik::set('baseline_highlight','active');
			break;
		case "traceability":
			Atomik::set('traceability_highlight','active');
			break;
		case "attachment":
			Atomik::set('attachment_highlight','active');
			break;
		case "impact":
			Atomik::set('impact_highlight','active');
			break;			
		case "peer_review":
			Atomik::set('peer_review_highlight','active');
			break;			
	}
}
			
if ($multi_modify=="yes"){
	$multiple_data_id = $_GET['multiple_data_id'];
	$baseline_id = $_REQUEST['show_baseline'];
	//echo "baseline_id:".$baseline_id."<br/>";
	//echo "multiple_data_id:".$multiple_data_id."<br/>";
	$regular_expression = "(\d{1,5});";
	preg_match_all("#".$regular_expression."#",$multiple_data_id, $matches);
	$value = $matches[1];
	next($value);
	//print_r($value);
	foreach( $value as $data_id) {
		
		echo "update data with ID: ".$data_id." with baseline ID: ".$baseline_id."<br/>";
		Data::update_baseline_application ($data_id,$baseline_id);
	}
	Atomik::Redirect('edit_data');
}
$html = "";
$html .= '<div class="remark">';
// if ($data->status_id != 45) {// not approved
$html .= '<a href="'.Atomik::url('post_remark',array('data_id'=>$data->id,'from'=>'edit_data')).'" style="text-decoration: none;outline-width: medium;outline-style: none;width:50%;float:left" title="Add new entry">';
$html .= '<img src="'.Atomik::asset('assets/images/newobject.gif').'" class="systemicon" width="32" alt="Add new remark" title="Add new remark" border="no"  />Add new remark</a>';
$html .= '<a href="#" onclick="send_mail('.$data->id.')" style="text-decoration: none;outline-width: medium;outline-style: none;width:50%;float:left"><img alt="Send mail" title="Send mail" width="32" height="32" border="0" src="assets/images/32x32/mail_send.png">Send by mail</a>';
// }
$html .='</div>';
$html .= "<table cellspacing='0' class='pagetable' style='width:100%'>";
$html .= "<tbody><tr class='vert'>";
/* Previous arrow */
$html .= $data->getPrevious();
$last_issue_color = $data->getLastIssue()?"pastel_red":"grey";
$html .= '<td class="'.$last_issue_color.'" style="width:80%" >'.$data->full_ident;
if ($data->password != "") {
	$html .=  "	Pwd:".$data->password;
}
$html .=  '</td>';
/* Next arrow document.nom_formulaire.nom_bouton.click()' */
$next_arrow = $data->getNext();
if ($next_arrow === false){
	$html  .= '<td class="td_arrow"><a href_="#" onclick_="document.form_edit_data.submit_new.click()" >';
	$html  .= '<img src="assets/images/16x16/edit_add.png" width="16" height="16" border="0" title="New version" alt="New version"></a></td>';
}
else{
	$html .= $data->getNext();
}
$html .= "</tr>";
$html .= "</tbody></table>";
/* find data uploaded */ 
if ($data->link != "empty") {
	$html .= '<h3>Attachment</h3><p class="vert" style="width:100%"><a href="../'.$data->link.'"><img alt="Open document" title="Open document '.$data->real_filename.'" width="32" height="32" border="0" src="'.$data->link_mime.'" /></a>';
	$html .= '<a href="'.Atomik::url("data/remove_data_attachment",array("id"=>$data->id)).'">';
	$html .= "<img style='padding-left:5px;padding-top:5px' border='0' width='12' height='12' src='".Atomik::asset('assets/images/32x32/agt_action_fail.png')."' alt='Remove link' title='Remove link' onclick='return confirmFileAttachRemove()' /></a></p>";
}
$html .= "<img style='padding-left:5px;padding-top:5px' border='0' width='128' height='128' src='".Atomik::asset('assets/images/128x128/kword_kwd.png')."' alt='Cover' title='Cover' /><br/>";
$html .='<a href="'.Atomik::url("data",false).'" ><h2><img src="'.Atomik::asset('assets/images/pages/sommaire.png').'" border="0" alt="Back" title="Back">Back</h2></a>';
			
$previous_data_list = Data::getPreviousDataList($data->project_id,$data->type_id);
$list_type=Data::combo_box_type_query();
$priority_list = Data::getPriorityList();
// var_dump($priority_list);
/* Upper data */
$list_upper_data = Data::getUpperDataList($data->project_id,$data->group_id);	

/* List of upper data */
if ($data->id != ""){
	$found_upper_data = Data::Get_List_Upper_Data($data->id,&$list_upper);
	$downstream_data_list = Data::Get_List_Downstream_Data($data->id);
	$downstream_data = new Data;	
	/*  Display First Page (rtf or pdf) */
	$first_page_img = $data->Create_First_Page();	
}
else {
	$found_upper_data = false;
	$downstream_data_list = false;
	$first_page_img = false;
}

// echo $diagram_img;
Atomik::set('menu',array('assignee' => 'Author',
						'equipment' => 'Equipment'));
Atomik::set('select_menu',$html);
Atomik::set('title',"Edit ".$data->type." Document");
Atomik::set('css_title',"data");
Atomik::set('css_add',"no_show");
Atomik::set('url_add',"");
Atomik::set('title_add',"");
Atomik::set('url',"data");
Atomik::set('css_reset',"no_show");
Atomik::set('css_page','no_show');	
Atomik::set('css_page_previous','no_show');	
Atomik::set('css_page_next','no_show');	

if (($data->type == "CSCI")||($data->type == "HWCI")) {
	$uploader=new PhpUploader();
	$uploader->Name="myuploader";
	$uploader->MultipleFilesUpload=true;
	$uploader->InsertText="Upload multiple files";
	$uploader->MaxSizeKB=1024000;
	$uploader->AllowedFileExtensions="*.vhd,*.c,*.s";
	$uploader->SaveDirectory="savefiles";
	$uploader->FlashUploadMode="Partial";
}