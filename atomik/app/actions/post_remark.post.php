<?php
$postArray = &$_POST;
$type = isset($_POST['type'])?$_POST['type']:"";
$from = isset($_POST['from'])?$_POST['from']:"";
$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;  
$limite = isset($_POST['limite']) ? (int)$_POST['limite'] : 8;
$info['id']		  		= isset($_POST['id']) ?  $_POST['id'] : "";
$info['user_id']  		= isset($_POST['show_poster']) ?  $_POST['show_poster'] : "";
$info['description']    = isset($_POST['description']) ?  $_POST['description'] : "";
$info['category_id']    = isset($_POST['show_category']) ?  $_POST['show_category'] : "";
$info['date_open']     	= isset($_POST['date']) ?  $_POST['date'] : "";	
$info['description']    = isset($_POST['description']) ?  $_POST['description'] : "";
$info['poster_id']  	= isset($_POST['show_poster']) ?  $_POST['show_poster'] : "";
$info['subject']  		= isset($_POST['subject']) ?  $_POST['subject'] : "";
$info['subject']  		= stripslashes($info['subject']);
$info['category_id']  	= isset($_POST['show_category']) ?  $_POST['show_category'] : 1;
$info['criticality_id'] = isset($_POST['show_criticality']) ?  $_POST['show_criticality'] : "";	
$info['application'] 	= isset($_POST['show_application']) ?  $_POST['show_application'] : "";
$info['status_id'] 		= isset($_POST['show_status']) ?  $_POST['show_status'] : 15;
$info['date'] 			= isset($_POST['date']) ?  $_POST['date'] : "";
$info['paragraph'] 		= isset($_POST['paragraph']) ?  $_POST['paragraph'] : "";
$info['line'] 			= isset($_POST['line']) ?  $_POST['line'] : "";
$info['justification'] 	= isset($_POST['justification']) ?  $_POST['justification'] : "";
$info['action_id'] 		= isset($_POST['action_id']) ?  $_POST['action_id'] : "";	
if ($from == ""){
	$from = "inspection?show_application=".$info['application']."&show_poster=&page=".$page."&limite=".$limite."&search";
}
else if($from == "edit_data"){
	$from = "edit_data?id=".$info['application'];
}
if (isset($postArray['cancel_up']) || isset($postArray['cancel_down'])) {
	/* clean form inputs */
	foreach($postArray as $key => $value):
		//echo $key ."<br/>";
		if (Atomik::has('session/post_remark/'.$key)){
			Atomik::delete('session/post_remark/'.$key);
		}
	endforeach;
	Atomik::redirect($from);
}
if (isset($postArray['submit_remark']) || isset($postArray['submit_remark_up'])){
	/* Save form inputs */
	if (Atomik::has('session/post_remark/justification')){
		Atomik::delete('session/post_remark/justification');
	}
	if (Atomik::has('session/post_remark/description')){
		Atomik::delete('session/post_remark/description');
	}
	if (Atomik::has('session/post_remark/date')){
		Atomik::delete('session/post_remark/date');
	}	
	Atomik::add('session/post_remark',array('justification' => $postArray['justification']));
	Atomik::add('session/post_remark',array('description' => $postArray['description']));
	Atomik::add('session/post_remark',array('date' => $postArray['date']));
	/* Test form inputs */
	$rule = array(  
		'show_poster' => array('required' => true),
		'show_application' => array('required' => true),
		'description' => array('required' => true),
		'category' => array('required' => false),
		'date' => array('required' => false),
		'status' => array('required' => false),
		'paragraph' => array('required' => false),
		'line' => array('required' => false),	
		'application' => array('required' => false),
		'subject' => array('required' => false),	
		'action_id' => array('required' => false),		
	);

	if (($data = Atomik::filter($_POST, $rule)) === false) {
		Atomik::flash(A('app/filters/messages'), 'failed');
		//$var = Atomik::get('session/post_action/date_expected');
		// echo Date::convert_dojo_date($var);
		// exit(0);
		Atomik::redirect('post_remark');
	}
	$context['data_id'] = $info['application'];
	$remark = new Remark(&$context);
	$list_remarks = $remark->getRemarks();
	$amount_remarks=count($list_remarks) + 1;
	
	if($type=="new"){
		/* New remark */
		$new_remark_id = $remark->insert(&$info);		
		$remark->set($new_remark_id);
		if($new_remark_id !== false){
			Atomik::flash("Remark {$new_remark_id} has been successfully added by ".User::getNameUserLogged()." at ".date('H:i:s')." on ".date('l')." ".date('j')." ".date('M')." ".date('Y'). ". <b>".$amount_remarks."</b> remarks so far.","success");
		}
		else {
			Atomik::flash("Remark input failed.","failed");
		}
		Atomik::redirect($from);		
	}
	else {
		/* Update remark */					
		$result = $remark->update(&$info);
		if ($result) {	
			Atomik::flash("Remark {$info['id']} has been successfully updated.","success");
		}
		else {
			Atomik::flash("Remark {$info['id']} update failed.","failed");
		}
		Atomik::redirect($from);		
	}
	Atomik::redirect($from);
}
Atomik::redirect('post_remark');
