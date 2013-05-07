<?php
Atomik::needed('Db.class');
$db = new Db();
$user_id 	 = $_POST['users_id'];
$review_id   = $_POST['review_id'];
$copy_switch = isset($_POST['copy'])?$_POST['copy']:"";
if ($copy_switch == "on") {
	$copy = 1;
}
else {
	$copy = 0;
}
$update_review = $_POST['update_review_attendee'];
$sql_query = "INSERT INTO user_join_review (`user_id`, `review_id`,`copy`) VALUES('{$user_id}','{$review_id}','{$copy}')";
$result = $db->db_query($sql_query);
$_SESSION['add_attendee']="yes";
$_SESSION['update_review']=$update_review;
$_SESSION['attendee_highlight']="active";
if ($result !== false){
	Atomik::Flash("Attendee added.","success");	
}
else{
	Atomik::Flash("Adding attendee failed.","failed");	
}
Atomik::redirect('post_review?tab=attendee&id='.$review_id,false);
