<?php
Atomik::needed("Date.class");
Atomik::needed("Data.class");
Atomik::needed("Action.class");
Atomik::needed("Project.class");
Atomik::needed("Review.class");

$context_array['project_id']= isset($_GET['show_project']) ? $_GET['show_project'] : (Atomik::has('session/current_project_id')?Atomik::get('session/current_project_id'):Atomik::get('session/project_id'));
$context_array['sub_project_id'] = Atomik::has('session/sub_project_id')?Atomik::get('session/sub_project_id'):"";
$context_array['action_status_id'] = Atomik::has('session/action_status_id')?Atomik::get('session/action_status_id'):"";
$context_array['review_id'] = Atomik::has('session/review_id')?Atomik::get('session/review_id'):"";
$context_array['baseline_id'] = Atomik::has('session/baseline_id')?Atomik::get('session/baseline_id'):"";
$context_array['type_id'] = ""; // No specific type selected
$context_array['user_id'] = ""; // No specific user selected
$context_array['group_id'] = ""; // No specific group selected

$project = new Project(&$context_array);	

$all = Atomik::has('session/see_all_data')?Atomik::get('session/see_all_data'):"yes";
$data = new Data(&$context_array);
$count_data = $data->count_data($all);

$action = new Action(&$context_array);
/* count actions and display pie chart */
$actions_closed = $action->countActions("closed");
$actions_open = $action->countActions("open");
$count_actions = $action->countActions();
$actions = array('closed'=>$actions_closed,'open'=>$actions_open);
$pie_filename = "../result/actions_pie.png";
$action->new_drawPie($actions,
					$pie_filename,
					"Status of ".$count_actions." actions");
// $gdPie_img = @imagecreatefrompng($pie_filename);
// $list_actions = $action->getActions();

$review = new Review(&$context_array);
$review_list = $review->getReviewList(PDO::FETCH_OBJ);
$count_reviews = count($review_list);

$img_indicator = $review->createIndicator(&$review_list,
											$count_reviews." reviews held since selected review");

$header_fields = array("project", "LRU", "Baseline","Actions", "PDF", "XLS","DOC"); 
/* menu project */
$html=  '<form method="POST" action="'.Atomik::url('build_logbook', false).'">';
$html.= '<fieldset class="medium">';
$html.= Project::getSelectProject($context_array['project_id'],"active");
$html.= '</fieldset >';
// $html.= '<input type="hidden" name="context" value="'.$context.'">';
$html.= '</form>';

/* menu sub project */
Atomik::set('menu',array('equipment' => 'Equipment'));
$html.= '<form method="POST" action="'.Atomik::url('build_logbook', false).'">';
$html.= '<fieldset class="medium">';
$html.= Project::getSelectSubProject(&$project,$context_array['sub_project_id'],"active");
$html.= '</fieldset >';
$html.= '</form>';

/* menu review */
$html.=  '<form method="POST" action="'.Atomik::url('build_logbook', false).'">';
$html.= '<fieldset class="medium">';
$html.= "<label for='show_review'>Reviews:</label>";
$html.= "<select class='combobox' onchange='this.form.submit()' name='show_review' id='show_review'>";
$html.= "<option value='' /> --All--";
require_once("Date.class.php");
foreach($review->getAllReviewList(PDO::FETCH_OBJ) as $row):
	$html.= "<option value='".$row->id."'";
	if ($row->id == $context_array['review_id']){ 
		$html.= " SELECTED ";
	}
	$html.= ">".$row->type." (".$row->managed_by.") ".Date::convert_date($row->date);
endforeach;
$html.= "</select>";
$html.= '</fieldset >';
$html.= '</form>';

/* menu baseline */
$html.='<form method="POST" action="'.Atomik::url('build_logbook', false).'">';
$html.='<fieldset class="medium">';
$html.= Project::getSelectBaseline(&$project,$context_array['baseline_id'],"active");
$html.='</fieldset >';
$html.='</form>';

/* menu status */
$html.='<form method="POST" action="'.Atomik::url('build_logbook', false).'">';
$html.='<fieldset class="medium">';
$html.= $action->getSelectStatus($context_array['action_status_id'],"active");
$html.='</fieldset >';
// $html.='<input type="hidden" name="context" value="'.$context.'">';

$html.='</form>';
Atomik::set('select_menu',$html);
Atomik::set('title',"Generate logbook");
Atomik::set('url_reset',"data/reset_data");
Atomik::set('url',"build_logbook");
Atomik::set('css_title',"logbook");
Atomik::set('css_add',"no_show");
Atomik::set('css_page',"no_show");
Atomik::set('url_add',Atomik::url('edit_data',array('new'=>'yes')));
Atomik::set('title_add',"Add a document");
