<?php

$postArray = &$_POST ;
	
$rule = array(
	'title' => array('required' => true),
	'description' => array('required' => true),
	'attendee' => array('required' => true),
	'date' => array('required' => true),
    'update' => array('required' => true),
    'update_id' => array('required' => true),
);

/* on nepeut pas utiliser cette fonction filter car elle supprime les balises html */
if (($data_tmp = Atomik::filter($_POST, $rule)) === false) {
	Atomik::flash(A('app/filters/messages'), 'error');
	return;
}
$update=$postArray['update'];
$update_id=$postArray['update_id'];
unset($postArray['update'],$postArray['update_id']);
foreach ( $postArray as $sForm => $value ) {
    if ( get_magic_quotes_gpc() )
        $data[$sForm] = stripslashes( $value );
    else
        $data[$sForm] = $value;
}
/* Convert date from dojo format to sql format */
$month = strtok($data_tmp['date'],'/');
$day = strtok('/');
$year = strtok('/');

$updata['date'] = "20".$year."-".$month."-".$day;
$updata['description'] = $data['description'];
$updata['title'] = $data['title']; 
$updata['event'] = 1;
if ($update == "yes") {
    $where = "id = ".$update_id;
    $update_result = Atomik_Db::update('reviews', $updata, $where);
    if ($update_result)
        Atomik::flash('Event successfully updated!', 'success');
    else
        Atomik::flash('Event not updated!', 'failed');
}
else {
	$insert_result = Atomik_Db::insert('reviews', $updata);
	if ($insert_result)
	    Atomik::flash('Event successfully added!', 'success');
	else
	    Atomik::flash('Event not added!', 'failed');
}  
Atomik::redirect('home');
