<?php
Atomik::disableLayout();
Atomik::needed('Remark.class');
Atomik::needed('Date.class');
Atomik::needed('Data.class');
Atomik::needed('Tool.class');
Atomik::needed("Remark.class");
Atomik::needed("PeerReviewer.class");
Atomik::needed("Baseline.class");

$line_counter = 0;
$bar_filename = '../result/remarks_bar_'.uniqid().'.png';
$pie_filename = '../result/peer_reviewers_pie._'.uniqid().'png';

if (isset($_GET['context'])){
	$context = $_GET['context'];
	$context_array=unserialize(urldecode(stripslashes((stripslashes($_GET['context'])))));	
	$baseline = new Baseline;
	$remarks = new StatRemarks(&$context_array);
	$peer_reviewers = new PeerReviewer(&$context_array);
	$peer_reviewers->get();
	$baseline->get($context_array['baseline_id']);
}
else if (isset($_GET['id'])){
	$data_id = $_GET['id'];
	$remarks = new StatRemarks;
	$remarks->setDocument($data_id);
	// $remarks->get($data_id);
	$peer_reviewers = new PeerReviewer;
	$peer_reviewers->get($data_id);
}	
// var_dump($remarks);
if ($remarks->amount_remarks > 0){
	$remarks->count_all_remarks();
	$remarks->drawBar($bar_filename);
	$peer_reviewers->drawPie($pie_filename,"Authors of remarks");
	Atomik::setView("peer_review/display_internal_peer_review");
}
else{
	echo "<li class='warning' style='list-style-type: none;margin-top:40px;margin-right:10px'>No remarks found. Please select a specific baseline or reference.</li>";
	Atomik::noRender();
}
