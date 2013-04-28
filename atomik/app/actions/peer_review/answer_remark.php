<?php
// Atomik::needed("Db.class");
Atomik::needed("Date.class");
Atomik::needed("Data.class");
Atomik::needed("User.class");
Atomik::needed("Remark.class");

$id = isset($_POST['id']) ? $_POST['id'] : "";
$status_id = isset($_POST['reply_status_id']) ? $_POST['reply_status_id'] : "";
$poster_id = isset($_POST['show_poster']) ? $_POST['show_poster'] : "";
$description = isset($_POST['description']) ? $_POST['description'] : "";

$new_status = Remark::getStatusName($status_id);
/* Read previous status */
$previous_status = Remark::getRemarkStatus($id);

// /* add new status in the description */
// $new_status=get_name("name","bug_status","id",$status);
$description = $description."<p> Status: {$previous_status} --> {$new_status} </p>";
// $date_sql = Date::getTodayDate();
/* create answer */
$new_reply_id = Remark::create_response($description,
										$poster_id,
									  0,
									  $status_id,
									  $id);
// $db = new Db;
// $new_reply_id = $db->db_insert('bug_messages',array('description'=>$description,
								  // 'posted_by'=>$poster_id,
								  // 'status'=>$status_id,
								  // 'date'=>$date_sql,
								  // 'reply_id'=>$id));
/* update remark status */
$result = Remark::setStatus($id,$status_id);
if ($result) {
	ob_start("manage_log");
	$text = "Response for remark {$id} added by ".User::getNameUserLogged()." at ".date('H:i:s')." on ".date('Y/m/d');
	echo $text;
	ob_end_clean();
	Atomik::flash("A reply has been successfully added to remark {$id} by ".User::getNameUserLogged()." at ".date('H:i:s')." on ".date('Y/m/d'),"success");
}
else {
	Atomik::flash("Remark {$id} reply failed.","failed");
}
Atomik::redirect('inspection');
