<?php
Atomik::disableLayout();
Atomik::needed('Remark.class');
Atomik::needed('Date.class');
Atomik::needed('Data.class');
Atomik::needed('Tool.class');
//$monUrl = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']; 
//echo $monUrl;
$list_prr = array();
$nb_prr = 0;
$data_id = isset($_REQUEST['id']) ?  $_REQUEST['id'] : "";
if ($data_id != ""){
	$sql_query = "SELECT peer_review_location.id,".
					"name,".
					"ext,".
					"date, ".
					"nb_remarks, ".
					"open_remarks, ".
					"peer_review_type.type ".
					"FROM peer_review_location ".
					"LEFT OUTER JOIN peer_review_type ON peer_review_location.type_id = peer_review_type.id ".
					"LEFT OUTER JOIN bug_applications ON bug_applications.id = data_id WHERE data_id = {$data_id}";	
	$result = A("db:".$sql_query);
	$list_prr = $result->fetchAll(PDO::FETCH_OBJ);
	$nb_prr = count($list_prr);
	$color = 0;
	$nb_remarks = 0;
	$open_remarks = 0;
	$line_counter=0;
}
if ($nb_prr > 0){
	Atomik::setView("peer_review/display_peer_review");
}

