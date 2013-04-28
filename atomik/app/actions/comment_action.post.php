<?php
Atomik::needed("Action.class");
Atomik::needed("User.class");
$postArray = &$_POST;
$context_array = isset($_POST['context'])? unserialize(urldecode(stripslashes($_POST['context']))):"";
$context = urlencode(serialize($context_array));
//exit(0);
$id = $postArray['id'];
$new_comment = $postArray['comment'];
$action = new Action;
$action->get($id);
if (isset($postArray['cancel'])) {
	/* clean form inputs */
	foreach($postArray as $key => $value):
		//echo $key ."<br/>";
		if (Atomik::has('session/post_action/'.$key)){
			Atomik::delete('session/post_action/'.$key);
		}
	endforeach;
	//exit(0);
	Atomik::redirect('actions',false);
}
if (isset($postArray['submit'])){
	if ($action->getStatusId() == 8){
		$new_comment .= "<br/> Status set to <b>Propose to close</b>";
		$action->comment(&$new_comment);
		/* set status to "Propose to close" */
		$action->setStatus(13);
		Atomik::flash("Action {$id} has been successfully proposed to close.","success");
	}
	else if ($action->getStatusId() == 13){
		$new_comment .= "<br/> Status set to <b>Close</b>";
		$action->comment(&$new_comment);
		/* set status to "Close" */
		$result = $action->setStatus(9);
		if ($result){
			Atomik::flash("Action {$id} has been successfully closed.","success");
		}
		else{
			Atomik::flash("Action not closed.","failed");		
		}
	}
	else{
		Atomik::flash("Action status is still ".$action->getStatusId(),"failed");	
	}
	$text = "Action {$id} commented by ".User::getNameUserLogged()." at ".date('H:i:s')." on ".date('Y/m/d');
	qams_log($text);
	Atomik::redirect('actions',false);
}
if (isset($postArray['submit_close'])){
	$new_comment .= "<br/> Status set to <b>Close</b>";
	$action->comment(&$new_comment);
	/* set status to "Close" */
	$result = $action->setStatus(9);
	if ($result){
		Atomik::flash("Action {$id} has been successfully closed.","success");
	}
	else{
		Atomik::flash("Action not closed.","failed");		
	}
	$text = "Action {$id} commented by ".User::getNameUserLogged()." at ".date('H:i:s')." on ".date('Y/m/d');
	qams_log($text);
	Atomik::redirect('actions',false);
}
if (isset($postArray['submit_comment'])){
	$action->set($id);
	$action->comment(&$new_comment);	
	Atomik::flash("Action {$id} has been successfully commented.","success");
	Atomik::flash("Action {$id} has been successfully commented.","success");
	$text = "Action {$id} commented by ".User::getNameUserLogged()." at ".date('H:i:s')." on ".date('Y/m/d');
	qams_log($text);	
	Atomik::redirect('actions',false);
}
Atomik::redirect('comment_action',false);
