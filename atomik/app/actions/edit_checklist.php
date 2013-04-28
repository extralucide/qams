<?php
Atomik::needed("Review.class");
$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;  
$limite = isset($_REQUEST['limite']) ? $_REQUEST['limite'] : 8;
$input_width=60;
$question = new Question;
if(isset($_GET['checklist_id']) && ($_GET['checklist_id'] != 0)) {
	Atomik::set('sub_title','New Question');
	Atomik::set('button','Add');
    $update_id=$_GET['checklist_id'];
    unset ($_GET['checklist_id']);
    $update="yes";
    $title ="Edit checklist";
    $button="Update";
	$row = Atomik_Db::find('review_type',"id = ".$update_id);
    // foreach ($review_type_array as $row):
    $review_type = $row['type']." ".$row['description'];
    // endforeach;	
	$item_no = $row['last_item'] + 1;
    //$which_checklist='WHERE review_type.id = '.$update_id;
	//$questions_query = "SELECT review_type.type as acronym ,checklist_questions.id,review_id,question,type FROM checklist_questions LEFT OUTER JOIN review_type ON review_type.id = review_id ".$which_checklist." ORDER BY review_id ASC";
    //$questions_array = A("db:".$questions_query);
	//$nb_questions = Atomik_Db::count('checklist_questions',array('review_id'=>$update_id));
	$nb_questions = "";
}
else if(isset($_GET['question_id'])) {
	Atomik::set('sub_title','Edit Question');
	Atomik::set('button','Update');
    $question->get($_GET['question_id']);
    $item_no = $question->item;
    $nb_questions = "";
    $update_id=$question->review_id;
}
else {
	Atomik::set('sub_title','New Question');
	Atomik::set('button','Add');    
    $update="no";
    $update_id="0";
    $item_no = "";
    $nb_questions = "";
}
$html = "<b>".$question->type."</b>";
Atomik::set('title',"Edit checklists");
Atomik::set('css_title',"checklist");
Atomik::set('css_reset',"no_show");
Atomik::set('url',"edit_checklist");
Atomik::set('url_add',Atomik::url('edit_checklist'));
Atomik::set('title_add',"Add a question");
Atomik::set('css_add',"no_show");
Atomik::set('page',$page);
Atomik::set('limite',$limite);
Atomik::set('css_page_previous','no_show');	
Atomik::set('css_page_next','no_show');	
Atomik::set('css_page',"no_show");
Atomik::set('select_menu',$html);