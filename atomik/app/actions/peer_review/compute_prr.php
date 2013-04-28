<?php
Atomik::noRender();
Atomik::disableLayout();
// Atomik::setView("peer_review/compute_prr");
Atomik::needed("Remark.class");

$prr_id = isset($_REQUEST['prr_id']) ? $_REQUEST['prr_id'] : ""; 
$prr = Atomik_Db::find('peer_review_location',array('id'=>$prr_id));   
if($prr){
	$data_id = $prr['data_id'];        
	/* Check if the same uploaded data is not already linked to this data */
	$base_path = dirname(__FILE__).DIRECTORY_SEPARATOR.
								"..".DIRECTORY_SEPARATOR.
								"..".DIRECTORY_SEPARATOR.
								"..".DIRECTORY_SEPARATOR.
								"..".DIRECTORY_SEPARATOR;
	$uploadName = $base_path."docs".DIRECTORY_SEPARATOR."peer_reviews".DIRECTORY_SEPARATOR.$prr['id'].".".$prr['ext'];
	$res = Remark::scanPeerReview($uploadName,$prr['ext']);
	if ($res['type_id'] != 0){
		$type = Atomik_Db::find('peer_review_type',array('id'=>$res['type_id']));
		unset($res['nb_known_remarks']);
		unset($res['nb_response_remarks']);
		Atomik_Db::update('peer_review_location',$res,array('id'=>$prr_id));
		$status = "success";
		$result = 'Peer review '.$prr['name'].' analysed and classified as '.$type['type'].'.';
	}
	else{
		$result = 'Failed to classifify peer review '.$prr['name'].'.';
		$status = "failed";
	}
}
else{
	$data_id = "";
	$result = 'Failed to find peer review item.';
	$status = "failed";
}
Atomik::Flash($result,$status);
echo $result."<br/>";
echo "Data id:".$data_id;
Atomik::redirect('peer_review/display_peer_review?id='.$data_id);
