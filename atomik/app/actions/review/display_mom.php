<?php
Atomik::disableLayout();
Atomik::setView("review/display_mom");
ini_set('display_errors', 'On');
error_reporting(E_ALL);
Atomik::needed('Tool.class');
Atomik::needed('Db.class');
Atomik::needed('User.class');
Atomik::needed('Date.class');
Atomik::needed('Data.class');
Atomik::needed('Review.class');
Atomik::needed('Action.class');
/*
 * review
 */
$memo_subject 	= "";
$review = new Review;
$mail= isset($_GET['mail']) ? $_GET['mail'] : "";
if (isset($_GET['review_id'])){
	$review_id = $_GET['review_id'];
	$review->get($review_id); 
	$show_review  = $review->id;
	$show_project = $review->project_id;
	$show_lru = $review->lru_id;
	$select_checklist = "";
	$mom_id = $review->link;
	$review_type = $review->type_id;
	$id_type = $review->type_id;
	$indice_dal = "";
	$show_baseline = "";       
}
else {
	$show_review = $_POST['show_review'];
	$show_project = $_POST['show_project'];
	$show_lru = $_POST['show_lru'];
	$select_checklist = $_POST['select_checklist'];
	$mom_id = $_POST['mom_id'];
	$review_type = $_GET['id_review_type'];
	$id_type = $_POST['id_type'];
	if ($id_type =="") {
		$id_type = $_GET['id_review_type'];
	}
	$indice_dal = $_POST['dal'];
	$show_baseline = $_POST['show_baseline'];
}

$context[] = array();
$context['project_id']="";//$show_project;
$context['sub_project_id']="";//$show_lru;
$context['review_id']=$show_review;
$context['baseline_id']=$show_baseline;
$context['user_logged_id']=User::getIdUserLogged();

$user = new User;
$user->get_user_info(User::getIdUserLogged());
$head_office = $user->service;/* "Quality Department";*/
$fax_number = "";

if ($show_baseline == null) {
    $show_data_baseline = "";
}
else {
    $show_data_baseline = "AND baseline_join_data.baseline_id = '{$show_baseline}' ";//AND bug_applications.id = data_id ";
}
$objective_text = $review->objective;
$conclusion_text = $review->comment;
$reference 		= "Reference: ".$review->reference;
$title    		= Tool::clean_text($review->project." ".$review->lru." ".$review->type." Report ");
$sub_title		= $reference;
$ref 			= $review->reference;
$review_description = $review->managed_by." ".Tool::clean_text($review->type)." performed on ".$review->date;
if ($review->getSubject() != ""){
	$subject = Tool::filter($review->getSubject());
}
else{
	$subject = $review->managed_by." ".$review->type." for ".$review->project." ".$review->lru;
}
$review_context = "This meeting ".$review->type." has been conducted on the ".$review->date." and managed by ".$review->managed_by.".";

if ($mail=="") {
$header = <<<____HTML
	<div id="art-main" >
		<div id="bandeau">
			<div id="bandeau2">
			</div>
		</div>
	</div>		
	<div class="nice_square" style="background-color:#FFF;width:1100px;height:200px">
____HTML;
}
else {
	$file=dirname(__FILE__).
			DIRECTORY_SEPARATOR.
			"..".DIRECTORY_SEPARATOR.
			"..".DIRECTORY_SEPARATOR.
			"..".DIRECTORY_SEPARATOR.
			"assets".DIRECTORY_SEPARATOR."images".DIRECTORY_SEPARATOR."saq_086_header.jpg";
	// $file = "../../atomik/assets/images/saq_086_header.jpg";
	$header = '<img src="'.Tool::base64_encode_image($file).'" alt="header" width="1124" height="390" />';
}
$issue 			= "";
	$html = "<div style='margin-left:50px;width:1040px'>";
		$html .= "<div style='width:520px;height:300px;float:right'>";
		$html .= "<p><b>Date of the meeting/de la r&eacute;union: </b>".$review->date."</p>";
		$html .= "<p><b>Place/lieu:</b> Paris</p>";
		$html .= "<p><b>Ref.: </b>".$ref."</p>";
		$html .= "<p><b>Subject/Object:</b>".$subject."</p>";
		$html .= "</div>";
		
		$html .= "<div style='width:520px;height:300px;float:left'>";
		$html .= "<p><b>From/De: </b>".$user->name."</p>";
		$html .= "<p><b>Service: </b>".$head_office."</p>";
		$html .= "<p><b>Tel: </b>".$user->phone."</p>";
		$html .= "<p><b>E-mail:</b>".$user->email."</p>";
		$html .= "</div>";
	$html .= "</div>";
	$html .= '<div class="spacer" style="float:none"></div>';
$html .= "</div>";
if ((isset($_GET['review_id'])) && 
	($_GET['review_id'] != "")){
	$html .= '<div style="width:1040px;">';
	$html .= "<h1>Context</h1>";
	$html .= "<blockquote>";
	$html .=  "<p>".$review_context."</p></blockquote>";
	$html .=  "</div>";
	/*
	 * Attendees
	 */
	$html .=  "<h1>Attendees</h1>";
	if ($review->attendees != null) {
$html .= <<<____HTML
		<div style="width:1040px;">
		<div style="width:600px;padding-left:10px">
		<p><table class="art-article pagetable" >
		<thead>
		<tr class='vert'>
		<th>Name</th><th>Company</th><th>Function</th>
		</tr>
		</thead>
		<tbody>
____HTML;
		  $fill = false;
		  foreach( $review->attendees as $id => $users ):
					$color = ($fill)?'rouge':'vert';
$html .= <<<____HTML
					 <tr class="{$color}">
					 <td><a href="mailto:{$users['email']}">{$users['fname']} {$users['lname']}</a></td><td>{$users['company']}</td><td>{$users['function']}</td></tr>
____HTML;
					$fill = !$fill;
		  endforeach;
$html .= <<<____HTML
		</tbody>
		</table></p><br/><br/>
		</div>
		<div style="width:440px;float:left">
		</div>
		<div class="spacer" style="float:none"></div>
		</div>
____HTML;
	}
	if ($review->person_copy != null) {
	$html .=  "<h1>Copy</h1>";	
$html .= <<<____HTML
		<div style="width:1040px;">
		<div style="width:600px;padding-left:10px">
		<p><table class="art-article pagetable" >
		<thead>
		<tr class='vert'>
		<th>Name</th><th>Company</th><th>Function</th>
		</tr>
		</thead>
		<tbody>
____HTML;
		  $fill = false;
		  foreach( $review->person_copy as $id => $users ):
					$color = ($fill)?'rouge':'vert';
$html .= <<<____HTML
					 <tr class="{$color}">
					 <td><a href="mailto:{$users['email']}">{$users['fname']} {$users['lname']}</a></td><td>{$users['company']}</td><td>{$users['function']}</td></tr>
____HTML;
					$fill = !$fill;
		  endforeach;
$html .= <<<____HTML
		</tbody>
		</table></p><br/><br/>
		</div>
		<div style="width:440px;float:left">
		</div>
		<div class="spacer" style="float:none"></div>
		</div>
____HTML;
	}	
	/*
	 * Objective
	 */
	$html .= '<div style="width:1040px;">';
	$html .= "<h1 style='page-break-before:always;'>Objectives</h1><br/>";
	$html .= "<blockquote><p>".$review->objective."</p></blockquote>";

	$project= "";
	$equipment= "";
	$review_id_type = "";

   $review_id = $_GET['review_id'];
   $review->get($review_id);   
   $review_id    = $review->id;
   $managed_by   = $review->managed_by;
   $show_status  = $review->status;
   $current_date = $review->date_sql;

	$reference		= " "; // TBD

	$today_date 	= date("d").' '.date("F").' '.date("Y");
	$meeting_date   =  Date::convert_date_conviviale ($review->date_sql);
	$meeting_date_small   =  Date::convert_date_small ($review->date_sql);
	$memo_subject 	= $review->project." ".$review->lru." ".$memo_subject." ".$review->type." ".$meeting_date;
	$memo_location  = "Paris";
	/*
	 * SPR status
	 */
	/*Display SPR table for sw review only */ 
	if (($review->type == "SRR")||
		($review->type == "SDR")||
		($review->type == "TRR")||
		($review->type == "FQR")||
		($review->type == "FCA/PCA")	
		) {
		$html .="<h2 style='page-break-before:always;'>Sw Problem Report status</h2>";
		$html .= "Software Problem Report extracted from IBM Change database on ".$today_date." at ".date('H:i:s');
		require_once '../spr/list_spr_change.php'; 
		$spr_table = get_change_spr($review->lru_id);
		$html .= "<div style='width:1000px'>";
		$html .=  $spr_table;
		$html .= "</div>";
	}
	$meeting_missing = "";
	$meeting_copy = "";
	$line_counter=0;
	$html .= "<br/><br/>";
	$html .= "<h1 style='page-break-before:always;'>Inputs</h1>";
	$html .= "<blockquote>";
	$html .= "<p>";
	$list_data = $review->getData();
	$list_baseline_linked = $review->getBaseline();
	if ($list_data !== false){
		$html .= "<table class='art-article pagetable'>";
		$html .= "<thead>";	
		foreach ($list_baseline_linked as $row):
		  /* Display baseline */
		  $html .= "<tr><td colspan='8'>".$row->baseline_name."</td></tr>";
		endforeach; 
		// }
		$html .= "</thead>";
		$html .= "<tbody>";
		foreach ($list_data as $baseline_row):
			$description = $baseline_row['application']." ".$baseline_row['lru']." ".$baseline_row['name'];
			if($baseline_row['version'] != ""){
				$description .= " issue ".$baseline_row['version'];
			}
			$data_link = Tool::Get_Filename($baseline_row['link_id'],$baseline_row['link_extension']);
			$color= ($line_counter++ % 2 == 0) ? "rouge" : "vert";
			$html .= "<tr class='".$color."' ><td>[Ref ".$line_counter."]</td><td colspan='3'>".$description;
			$html .= "</td><td colspan='5'>".Tool::clean_text($baseline_row['description'])."</td></tr>";
	   endforeach;
		$html .= "</tbody>";
		$html .= "</table>";
	}
	$html .= "</p>";
	$html .= "</blockquote>";
	$html .= "<h1 style='page-break-before:always;'>Attached documents</h1>";
	$html .= "<blockquote>";
	$html .= "<p>";
	$list_data = $review->getAllAttached();
	if ($list_data !== false){
		$html .= "<table class='art-article pagetable'>";
		$html .= "<thead>";	
		$html .= "<tr><td>Reference</td></tr>";
		$html .= "</thead>";
		$html .= "<tbody>";
		foreach ($list_data as $row):
			$description = $row['real_name'];
			$color= ($line_counter++ % 2 == 0) ? "rouge" : "vert";
			$html .= "<tr class='".$color."'><td>".$description."</td>";
	   	endforeach;
		$html .= "</tbody>";
		$html .= "</table>";
	}
	$html .= "</p>";
	$html .= "</blockquote>";
/* Checklist */
	$list_questions = Question::getQuestionsList($review->type_id);
	$html .= "<h1 style='page-break-before:always;'>Checklist</h1>";
	$html .= "<blockquote>";
	$html .= "<p>";

		$html .= "<table class='art-article pagetable'>";
		$html .= "<thead>";	
		$html .= "<tr><th colspan='2'>Id</th><th colspan='11'>Questions</th></tr>";
		$html .= "</thead>";
		$html .= "<tbody>";
		foreach ($list_questions as $row_table):
			$color= ($line_counter++ % 2 == 0) ? "rouge" : "vert";
			$html .= "<tr class='".$color."'>";
			$html .= '<td colspan="2">'.$row_table['acronym'].'_'.sprintf("%1$03d",$row_table['item_order']).'</td>';
            $html .= '<td colspan="10">'.$row_table['question'].'</td><td></td>';
			$html .= "</tr>";
	   	endforeach;
		$html .= "</tbody>";
		$html .= "</table>";
	$html .= "</p>";
	$html .= "</blockquote>";	
/* Description */	
	$html .= "<h1 style='page-break-before:always;'>Description</h1>";
	$html .= "<blockquote>";
	$html .= "<p>";
	$html .= $review->description;
	$html .= "</p>";
	$html .= "</blockquote>";
	$html .= "</div>";
	$action = new Action(&$context);

	$file = "../atomik/assets/images/32x32/run.png";
	$icon_open_src = Tool::base64_encode_image($file);
	$file = "../atomik/assets/images/32x32/agt_update_critical.png";
	$icon_deadline_src = Tool::base64_encode_image($file);	
	$file = "../atomik/assets/images/32x32/agt_runit.png";
	$icon_proposed_close_src = Tool::base64_encode_image($file);
	$file = "../atomik/assets/images/32x32/agt_action_success.png";
	$icon_close_src = Tool::base64_encode_image($file);	
	$img_status = array("deadline" => $icon_deadline_src,
						"close" => $icon_close_src,
						"open" => $icon_open_src,
						"propose" => $icon_proposed_close_src);				
	$action->setStatusLogo($img_status);
/* Previous Actions list */
	$html .= "<h1 style='page-break-before:always;'>Actions list from previous meeting</h1>";
	$html .= "<div style='margin-left:10px;width:1024px'>"; 
	if ($review->previous_id != 0){
		$review->get($review->previous_id);
		$html .= "<p>Previous meeting was ".$review->managed_by." ".Tool::clean_text($review->type)." performed on ".$review->date."</p>";
		$review->get($review_id);
		$action->setReview($review->previous_id);
		$html .= $action->buildActionTable();
	}
	else {
		$html .= "No previous meeting.";
	}
	$html .= "</div>";
	/*
	 *
	 * Current Actions list
	 *
	 */
	$html .= "<h1 tyle='page-break-before:always;'>Actions list</h1>";
	$html .= "<div style='margin-left:10px;width:1024px'>";
	if ($review->id != "") {
		$action->setReview($review->id);
		$html .= $action->buildActionTable();
	}
	else {
	  $show_rev ="";
	}
	$html . "</div>";
/* Conclusion */
	$html .= "<div style='margin-left:10px;width:1024px'>"; 
	$html .= "<h1 tyle='page-break-before:always;'>Conclusion</h1>";
	/*echo '<div id="zodiac_openspace" style="height:800px">';*/
	$html .= "<blockquote><p>".$review->comment."</p></blockquote>";
	$html .= "</div>";

	/* Baseline */
	$list_baseline = $review->getBaseline();
	if ($show_baseline != 0) {
		$sql_list_data = sort_data($review->project,$review->lru,$show_baseline);
		$result = do_query ($sql_list_data);
		$html . $sql_list_data."<br/>";
		while($row = mysql_fetch_array($result)) {
			$baseline_data = new Data($row);
			$document_name = $baseline_data->reference." ".
			  $baseline_data->version." ".
			  $baseline_data->type." ".
			  //$baseline_data->author." ".
			  $baseline_data->description;
			  //$baseline_data->date_published." ".
			  //$baseline_data->status;
			 //echo "Value:".$index." ".$document_name."<br/>";
			$html . $document_name."<br/>";
			
		}
	}
}
else {
	$error = "No review selected !.";
	$status = "failed";
	$html . '<ul><li class="'.$status.'">'.$error.'</li></ul>';
}	
if ($mail=="") {
$footer = <<<____HTML
	<div id="piedpage" style="width:1124px"><span style="display: block; text-align: center; padding: 20px 0pt 0pt; color: rgb(170, 170, 170);"><small>Copyright &copy; 2012 All Rights Reserved</small></span></div>
	</div>
____HTML;
}
else {
	$file=dirname(__FILE__).
			DIRECTORY_SEPARATOR.
			"..".DIRECTORY_SEPARATOR.
			"..".DIRECTORY_SEPARATOR.
			"..".DIRECTORY_SEPARATOR.
			"assets".DIRECTORY_SEPARATOR."images".DIRECTORY_SEPARATOR."mastering_the_elements_1124.jpg";
	$footer = '<img src="'.Tool::base64_encode_image($file).'" alt="header" width="1124" height="80" />';	
}

