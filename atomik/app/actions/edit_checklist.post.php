<?php
$postArray = &$_POST;
if ((array_key_exists('submit_cancel_1', $postArray)) || (array_key_exists('submit_cancel_2', $postArray))) {
	Atomik::redirect('show_checklists?checklist_id='.$update_id);
}
/*
$rule = array(
    'type' => array('required' => false),
    'description' => array('required' => false),
    'scope' => array('required' => false),   
    'objectives' => array('required' => false),
    'inputs' => array('required' => false),
    'activities' => array('required' => false),
    'outputs' => array('required' => false),
    'schedule' => array('required' => false),
    'company_id' => array('required' => false),
);
*/
/* on ne peut pas tout le temps utiliser cette fonction filter car elle supprime les balises html */
// if (($data = Atomik::filter($_POST, $rule)) === false) {
    // Atomik::flash(A('app/filters/messages'), 'error');
    // return;
// }

// $update_id=$postArray['id_review_type'];
// unset($postArray['id_review_type']);
if (array_key_exists('add_submit', $postArray)){
	foreach ( $postArray as $sForm => $value ) {
		//if ($sForm == 'new_question'){
		//	$sForm = 'question';
		//}
		if ( get_magic_quotes_gpc() )
			// $data['question'] = stripslashes( $postArray['new_question'] );
		// else
			// $data['question'] = $postArray['new_question'];
			// $data['review_id'] = ;
			$data[$sForm] = stripslashes( $value );
        else
            $data[$sForm] = $value;
	}
	if($postArray['question_id']==""){
		$insert_result = Atomik_Db::insert('checklist_questions', array('item_order'=>$data['item_order'],
																		'tag'=>$data['tag'],
																		'question'=>$data['question'],
																		'review_id'=>$data['review_id']));
		if ($insert_result){
			$row = Atomik_Db::update('review_type',array('last_item'=>$data['item_order']),array('id'=>$update_id));
			Atomik::flash('Checklist questions successfully added.', 'success');
		}
		else{
			Atomik::flash('Checklist questions adding failed.', 'failed');
		}	
	}
	else{
		$result = Atomik_Db::update('checklist_questions',array('item_order'=>$data['item_order'],
																'tag'=>$data['tag'],
																'question'=>$data['question']),array('id'=>$postArray['question_id']));
		if ($result){
			Atomik::flash('Checklist questions successfully updated.', 'success');
		}
		else{
			Atomik::flash('Checklist questions update failed.', 'failed');
		}		
	}	
}
else {
    foreach ( $postArray as $sForm => $value ) {
		/* Get question ID */
		preg_match("#question_(\d{1,5})#",$sForm, $matches);
		//echo "TEST:".$sForm."<br/>";
		//print_r($matches);
		$question_id = $matches[1];
		//$data['id'] = $question_id;
		/* Get question content */
        if ( get_magic_quotes_gpc() )
            $data['question'] = stripslashes( $value );
        else
            $data['question'] = $value;
        //echo "field:".$data['id']."=>".$data['question']."<br />";
		$update_result = Atomik_Db::update('checklist_questions', $data, "id = ".$question_id);
		if (!$update_result)break;
    }
	if ($update_result){
		Atomik::flash('Checklist questions successfully updated!', 'success');
	}
	else
		Atomik::flash('Checklist questions updated!', 'failed');	
}	
    /*
	$where = "id = ".$update_id;
    $update_result = Atomik_Db::update('checklist_questions', $data, $where);
    if ($update_result)
        Atomik::flash('checklist questions successfully updated!', 'success');
    else
        Atomik::flash('checklist questions updated!', 'failed');
	*/
// }
// else {
    //print_r($data);
    // $insert_result = Atomik_Db::insert('checklist_questions', $data);
    // if ($insert_result)
        // Atomik::flash('checklist questions successfully added!', 'success');
    // else
        // Atomik::flash('checklist questions not added!', 'failed');
//}
Atomik::redirect('edit_review_type?tab=checklist&id='.$update_id,false);
