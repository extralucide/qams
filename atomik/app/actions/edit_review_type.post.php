<?php
$postArray = &$_POST ;
if (array_key_exists('submit_cancel', $postArray)) {
	Atomik::redirect('review_type',false);
}

$rule = array(
    'type' => array('required' => false),
    'description' => array('required' => false),
    'scope_id' => array('required' => false),   
    'objectives' => array('required' => false),
    'inputs' => array('required' => false),
    'activities' => array('required' => false),
    'outputs' => array('required' => false),
    'schedule' => array('required' => false),
    'company_id' => array('required' => false),
);

/* on ne peut pas tout le temps utiliser cette fonction filter car elle supprime les balises html */
if (($data = Atomik::filter($_POST, $rule)) === false) {
    Atomik::flash(A('app/filters/messages'), 'error');
    return;
}

$update_id=$postArray['id_review_type'];
unset($postArray['id_review_type']);
if ($update_id != 0) {
    foreach ( $postArray as $sForm => $value ) {
        if ( get_magic_quotes_gpc() )
            $data[$sForm] = stripslashes( $value );
        else
            $data[$sForm] = $value;
    }
    $result = Atomik_Db::update('review_type', $data, "id = ".$update_id);
    if ($result)
        Atomik::flash('Review type successfully updated.', 'success');
    else
        Atomik::flash('Review type update failed.', 'failed');
}
else {
    $update_id = Atomik_Db::insert('review_type', $data);
    if ($update_id)
        Atomik::flash('Review type successfully added.', 'success');
    else
        Atomik::flash('Review type not added.', 'failed');
}
Atomik::redirect('edit_review_type?id='.$update_id);	
