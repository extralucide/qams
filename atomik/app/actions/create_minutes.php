<?php 
Atomik::needed('Data.class');
Atomik::needed('Review.class');
Atomik::needed('Project.class');
if (isset($_REQUEST['memo_id'])){
	$memo_id = $_REQUEST['memo_id'];
	Atomik::set('css_display','no_show');
	Atomik::set('filename',$memo_id);	
}
else{
	$context_array['project_id']= Atomik::has('session/current_project_id')?Atomik::get('session/current_project_id'):Atomik::get('session/project_id');
	$context_array['sub_project_id']= Atomik::has('session/sub_project_id')? Atomik::get('session/sub_project_id'):Atomik::get('session/sub_project_id');
	$context_array['type_id']="";
	$data = new Data;
	$memo_id = $data->createMemo($context_array['project_id'],
								$context_array['sub_project_id']);
										
	$reference = $data->reference;
	Atomik::set('reference',$reference);
	Atomik::set('memo_id',$memo_id);
}
$review = new Review(&$context_array);
$project = new Project;
$html = '<form method="POST" action="'.Atomik::url('create_minutes', false).'">';
$html.= '<fieldset class="medium">';
$html.= "<label for='review_type'>Review type</label>";
$html.= "<select class='combobox' onchange_='this.form.submit()' name='show_type' id='show_type'>";
$html.= "<option value='' /> --All--";
foreach($review->getAllReviewType() as $row):
	$html.= "<option value='".$row['id']."'";
	if ($row['id'] == $context_array['type_id']){ 
		$html.= " SELECTED ";
	}
	$html.= ">".$row['type']." ".$row['name']." ".$row['description'];
endforeach;
$html.= "</select>";	  
$html .='</fieldset >';
// $html .='<input type="hidden" name="context" value="'.$context.'">';
$html .='</form>';
Atomik::set('select_menu',$html);
Atomik::set('css_title',"data");
Atomik::set('css_reset',"no_show");
Atomik::set('css_add',"no_show");
Atomik::set('css_page',"no_show");
