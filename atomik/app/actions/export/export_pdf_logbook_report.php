<?php
Atomik::noRender();
Atomik::disableLayout();
Atomik::needed('Data.class');
Atomik::needed('Date.class');
Atomik::needed('Action.class');
Atomik::needed('Review.class');
Atomik::needed('Logbook.class');
Atomik::needed('Remark.class');
Atomik::needed('Baseline.class');
Atomik::needed('PeerReviewer');
Atomik::needed('User.class');
Atomik::needed('Tool.class');
require_once('../tcpdf/config/lang/fra.php');
require_once('../tcpdf/tcpdf.php');

// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {
	//Page header
	public function Header() {
			$ormargins = $this->getOriginalMargins();
			$headerfont = $this->getHeaderFont();
			$headerdata = $this->getHeaderData();
			if (($headerdata['logo']) AND ($headerdata['logo'] != K_BLANK_IMAGE)) {
				$this->Image(K_PATH_IMAGES.$headerdata['logo'], $this->GetX()+150, $this->getHeaderMargin(), $headerdata['logo_width']);
				$imgy = $this->getImageRBY();
			} else {
				$imgy = $this->GetY();
			}
			$cell_height = round(($this->getCellHeightRatio() * $headerfont[2]) / $this->getScaleFactor(), 2);
			// set starting margin for text data cell
			if ($this->getRTL()) {
				$header_x = $ormargins['right'] + ($headerdata['logo_width'] * 1.1);
			} else {
				$header_x = $ormargins['left'] + ($headerdata['logo_width'] * 1.1);
			}
			$this->SetTextColor(0, 0, 0);
			// header title
			$this->SetFont($headerfont[0], 'B', $headerfont[2] + 1);
			$this->SetX($header_x);
			$this->Cell(0, $cell_height, $headerdata['title'], 0, 1, '', 0, '', 0);
			// header string
			$this->SetFont($headerfont[0], $headerfont[1], $headerfont[2]);
			$this->SetX($header_x);
			$this->MultiCell(0, $cell_height, $headerdata['string'], 0, '', 0, 1, '', '', true, 0, false);
			// print an ending header line
			$this->SetLineStyle(array('width' => 0.85 / $this->getScaleFactor(), 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
			$this->SetY((2.835 / $this->getScaleFactor()) + max($imgy, $this->GetY()));
			if ($this->getRTL()) {
				$this->SetX($ormargins['right']);
			} else {
				$this->SetX($ormargins['left']);
			}
			$this->Cell(0, 0, '', 'T', 0, 'C');
			//Logo ECE
            $this->Image('../atomik/assets/images/small_my_ece_logo.jpeg',15,$this->getHeaderMargin(),20);
		}	
}
$fill = false;
$context_array['project_id']= isset($_GET['show_project']) ? $_GET['show_project'] : (Atomik::has('session/current_project_id')?Atomik::get('session/current_project_id'):Atomik::get('session/project_id'));
$context_array['sub_project_id'] = Atomik::has('session/sub_project_id')?Atomik::get('session/sub_project_id'):"";
$context_array['baseline_id'] = Atomik::has('session/baseline_id')?Atomik::get('session/baseline_id'):"";
$context_array['type_id'] = "";
$context_array['user_id'] = "";
$context_array['group_id'] = "";

Tool::deleteKey('session/search');
// var_dump($context_array);
// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, 
				PDF_UNIT, 
				PDF_PAGE_FORMAT, 
				true, 
				'iso-8859-1', 
				false); 

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor(User::getNameUserLogged());
$pdf->SetTitle('QA Logbook');
$pdf->SetSubject('Quality Assurance Logbook');
$pdf->SetKeywords('quality, process, assurance, logbook');

$logbook = new Logbook(&$context_array);
$today_date = date("d").' '.date("M").' '.date("Y");
$title = $logbook->board;
$filename="result/".$logbook->title."_".$today_date.".pdf";
$pdf->SetHeaderData('../../atomik/assets/images/zodiacaerospace.jpeg', PDF_HEADER_LOGO_WIDTH, $title,'QA Logbook by '.User::getNameUserLogged().'                    '.date("d").' '.date("F").' '.date("Y") );

// set default header data
// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

//set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

//set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

//set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); 

//set some language-dependent strings
$pdf->setLanguageArray($l); 

// ---------------------------------------------------------

// set font
$pdf->SetFont('dejavusans', '', 6);
// on compte le nombre d'actions total
$action = new Action(&$context_array);
$list_actions = $action->getActions();
$nb_actions=count($list_actions);
$nb_actions_closed = $action->new_count_actions("closed");
$nb_actions_open = $action->new_count_actions("open");
$pdf->AddPage();
$pdf->Bookmark('Introduction', 0, 0);
$html_text = "<h2 style='padding-left:30px'>Introduction</h2>";
$html_text .= <<<____TEXT
As a result of all the activities defined in the Process/Quality Assurance Plan will be compiled throughout the development and issued at main baselines.<br/>
The purpose of the logbook is to collect and summarize the work that has been completed.<br/>
This summary of work constitutes the evidence of Process/Quality Assurance required by guidelines ARP-4754/ED-79 or DO-178/ED-12 or DO-254/ED-80 and 
forms an input (normally by reference) to the development Accomplishment Summary.<br/>
The logbook will give a statement of compliance with respect to the activities defined in the plan during the development of a baseline.
____TEXT;
$pdf->writeHTML($html_text, true, 0, true, 0);
// set JPEG quality
$pdf->setJPEGQuality(85);
// Image example
$pdf->Image(dirname(__FILE__).DIRECTORY_SEPARATOR.
					'..'.DIRECTORY_SEPARATOR.
					'..'.DIRECTORY_SEPARATOR.
					'..'.DIRECTORY_SEPARATOR.
					'assets'.DIRECTORY_SEPARATOR.
					'images'.DIRECTORY_SEPARATOR.
					'qa_process.jpeg', 10, 60, 200, 150, '', 'http://intranet-ece.in.com/dq/processus', '', true, 150);
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
// Print a table
$documents = new Data(&$context_array);
$amount_data = $documents->count_data();
/* Documents */
$pdf->AddPage();
$pdf->Bookmark('Documents list', 0, 0);
$header=array("Reference", "Issue", "Type","Author" ,"Description","Released Date","Review Deadline","Status","Peer reviews");
$width_column=array(15,5,8,8,24,8,8,8,15);
$htmltable = "<h2 style='padding-left:30px'>Documents list</h2>";
$htmltable.= 	'<table border="1">'.
				'<thead>'.
				'<tr bgcolor="grey" color="#EEEEEE" >';
for($index=0;$index<count($header);$index++) {
	$htmltable.= '<td width="'.$width_column[$index].'%">'.$header[$index]."</td>";
}
$htmltable.= '</tr></thead>';
$counter=0;	
$htmltable .= '<tbody>';		
foreach($documents->getData(PDO::FETCH_OBJ) as $document):
	$documents->get($document->id);
	$color = ($fill)?"white":"lightgrey";	
	$htmltable.= '<tr bgcolor="'.$color.'">';
    /* get data remarks statistics */
    // $remarks = new StatRemarks($document->id,null,false);

    $list_data = array ($document->application,
						$document->version,
						$document->type,
						$document->author_fname." ".$document->author_lname,
						$document->description,
						$document->date_published,
						$document->date_review_sql);
	$index = 0;
	// $list_data = array(10,4,5,8,20,8,8);
	// print_r($width_column);
    foreach ($list_data as $val) {
       $htmltable.= '<td width="'.$width_column[$index++].'%">'.$val."</td>";
    }
	if ($documents->getDeadlineOver()){
		$htmltable.= '<td bgcolor="orange" color="green" width="'.$width_column[$index++].'%">'.$document->status.'</td>';
	}
	else{
		$htmltable.= '<td width="'.$width_column[$index++].'%">'.$document->status.'</td>';
	}
	/* external peer reviews */
	$result_prr = $documents->getExternalPeerReviewList();
	$list_peer_reviews = "";
	foreach($result_prr as $peer_reviews):
		$list_peer_reviews .= $peer_reviews->name."<br/>";
	endforeach;	
	/* internal peer reviews */
	$amount_remarks = Data::countPeerReviews($document->id);
	if($amount_remarks > 0){
		$list_peer_reviews .= "<b>".$amount_remarks."</b> remarks in DB<br/>";
	}
	if ($list_peer_reviews == "")$list_peer_reviews = "-";
	$htmltable.= '<td width="'.$width_column[$index++].'%">'.$list_peer_reviews."</td>";
	$htmltable.= '</tr>';
	$htmltable.= '<tr bgcolor="'.$color.'"><td colspan="9">'.$document->acceptance.'</td>';
    $htmltable.= '</tr>';
	$fill=!$fill;
    if 	(++$counter > 20)break;
endforeach;
$htmltable .= '</tbody>';
$htmltable .=  '</table>';
// echo $htmltable;
$pdf->writeHTML($htmltable, true, 0, true, 0);
$html_text = <<<____TEXT
The possible status of documents are:
<ul>
<li><strong>Not published</strong>: Document does not exist, only reference.</li>
<li><strong>New</strong>: Document is published but no review has been performed yet.</li>
<li><strong>Under review</strong>: Peer document review is in progress.</li>
<li><strong>Reviewed</strong>: Peer document review is finished.</li>
<li><strong>Approved</strong>: Document is updated according to peer reviews remarks and document is signed.</li>
</ul>
Status of document which status is "<strong>Under Review</strong>" is colored in orange when review deadline is passed.
____TEXT;
$pdf->writeHTML($html_text, true, 0, true, 0);
/* Actions */
$pdf->AddPage();
$pdf->Bookmark('Actions list', 0, 0);
$htmltable = "<h2 style='padding-left:30px'>Actions list</h2>";
//Titres des colonnes
$header=array('Id','Project','Context','Eqpt','Assignee','Description','Criticality','Date open','Date expected','Status');
$w=array(5,10,15,5,8,25,6,10,10,5);

$htmltable.= 	'<table border="1">'.
				'<thead>'.
				'<tr bgcolor="grey" color="#EEEEEE" >';
for($index=0;$index<count($header);$index++) {
	$htmltable.= '<td width="'.$w[$index].'%">'.$header[$index]."</td>";
}
$htmltable.= '</tr></thead>';
$htmltable .= 	'<tbody>';
// create some HTML content
foreach($action->getActions() as $row) {
	$action->get($row['id']);
	$color = ($fill)?"white":"lightgrey";	
	$htmltable.= '<tr bgcolor="'.$color.'">';
    $data[0] = $action->id;
    $data[1] = $action->project;
    $data[2] = Tool::clean_text($action->context);
    $data[3] = $action->lru;
    $data[4] = $action->getAssignee(true);
	$description = Tool::clean_text($action->getDescription());
    if ($action->status == "Closed" ) {
		$description .= "<br/> <strong>[".$action->date_closure."] ".$action->response."</strong>";
		$status = "Closed";
	}
	else {
        $status = "Open";
	}
	$data[5] = $description;
    $data[6] = $action->getSeverity();
    $data[7] = $action->date_open;
    $data[8] = $action->date_expected;

    $index = 0;
    foreach ($data as $val) {
       $htmltable.= '<td width="'.$w[$index++].'%">'.$val."</td>";
    }
	if ($action->getDeadlineOver()){
		$htmltable.= '<td bgcolor="orange" color="green" width="'.$w[$index++].'%">'.$status."</td>";
	}
	else{
		$htmltable.= '<td width="'.$w[$index++].'%">'.$action->status."</td>";
	}	
    $fill=!$fill;
    $htmltable.= '</tr>';	
}
$htmltable .= '</tbody>';
$htmltable.= '</table>';

// output the HTML content
$pdf->writeHTML($htmltable, true, 0, true, 0);
$html_text = <<<____TEXT
Action is <strong>closed</strong> when the person responsible of the action rules on an adopted solution and when Process/Quality Assurance Manager validates this solution.<br/> In other case, the action stays <strong>opened</strong> and in progress.
____TEXT;
$pdf->writeHTML($html_text, true, 0, true, 0);
$pdf->AddPage();

if ($nb_actions > 0) {
	$cheese_actions_closed = ($nb_actions_closed * 360)/$nb_actions;
	$cheese_actions_opened = ($nb_actions_open * 360)/$nb_actions;
	$xcenter = 105;
	$ycenter = 100;
	$radius = 40;

	$htmltable = "<h3 style='padding-left:30px'>".$nb_actions_closed." actions closed from ".$nb_actions."</h3>";
	$pdf->SetFillColor(120, 120, 255);
	$pdf->PieSector($xcenter, $ycenter, $radius, 0, $cheese_actions_closed);
	$pdf->SetFillColor(120, 255, 120);
	$pdf->PieSector($xcenter, $ycenter, $radius, $cheese_actions_closed, 360);
}
else {
	$htmltable = "<h3 style='padding-left:30px'>No actions available.</h3>";
}

$pdf->writeHTML($htmltable, true, 0, true, 0);
/* Reviews */
$reviews = new Review(&$context_array);
$review_list = $reviews->getReviewList(PDO::FETCH_OBJ);
$amount_reviews=count($review_list);
$pdf->AddPage();
$pdf->Bookmark('Reviews list', 0, 0);
$htmltable = "<h2 style='padding-left:30px'>Reviews list</h2>";
$pdf->writeHTML($htmltable, true, 0, true, 0);
//Titres des colonnes
$header=array("Led by","Type", "Date", "Objective","Conclusion", "Status","Minutes");
$w=array(8,5,8,30,30,8,8);

$htmltable = 	'<table border="1">'.
				'<thead>'.
				'<tr bgcolor="grey" color="#EEEEEE" >';
for($index=0;$index<count($header);$index++) {
	$htmltable.= '<td width="'.$w[$index].'%">'.$header[$index]."</td>";
}
$htmltable.= '</tr></thead>';
// create some HTML content
$htmltable .= 	'<tbody>';
foreach($review_list as $review):
	$color = ($fill)?"white":"lightgrey";	
	$htmltable.= '<tr bgcolor="'.$color.'">';
	$reviews->get($review->id);/*
	$list_review = array ($review->managed_by,
							$review->type,
							$review->date,
							Tool::filter($reviews->getObjective(true)),
							$reviews->getConclusion(true),
							$review->status_name,
							$review->reference);*/
	$index = 0;
	$objective = Tool::clean_text($reviews->getObjective(true));
	$list_review = array ($review->managed_by,$review->type,$review->date,$objective,$reviews->getConclusion(true),"$review->status_name",$review->reference);
	foreach ($list_review as $val) {
		$htmltable.= '<td width="'.$w[$index++].'%">'.$val."</td>";
	}
    $fill=!$fill;
    $htmltable.= '</tr>';	
endforeach;
$htmltable .= '</tbody>';
$htmltable .= '</table>';
// echo $htmltable;
$pdf->writeHTML($htmltable, true, 0, true, 0);
$html_text = <<<____TEXT
As far as the Process/Quality Assurance point of view is concerned, the objective of the reviews is to
establish the compliance of the development with the project plans (after the Plan Review).<br/> 
Baseline Reviews are performed during the development. The set of reviews will depend on the DAL, the System complexity and Enhancement.<br/>
The set of reviews are defined in the Development Plan (not in Process/Quality Assurance Plan).<br/> 
The Plan Review is performed early in the development, before entering any development phase, in order to demonstrate that the set of plans are fit for purpose and 
compliant with guidelines (ARP-4754/DO-178/DO-254).</br>
The Process/Quality Assurance Manager will chair the Plan Review. For other reviews, the P/QAM will assure that the review process is properly followed and 
ensure that evidence of adherence to the plans is available.<br/>
The milestone review should focus on project management items; technical aspects are covered during validation activities. 
Nevertheless, for planning constraints or when no validation activity has been planned for the development lifecycle phase, technical aspects (focused on work product) may be assessed during a milestone review.
____TEXT;
$pdf->writeHTML($html_text, true, 0, true, 0);
$pdf->AddPage();
$pdf->Bookmark('Accomplishment Summary', 0, 0);
$html_text = "<h2 style='padding-left:30px'>Accomplishment Summary</h2>";
$pdf->Bookmark('Compliance statement', 1, 0);
$html_text .= "<h3 style='padding-left:30px'>Compliance statement</h3>";
$html_text .= <<<____TEXT
This section includes a statement of compliance with ARP-4754/DO-178/DO-254 and a summary of the methods used to demonstrate compliance with criteria specified in the plans.<br/>
This section also addresses additional rulings and deviations from the plans, procedures, and ARP-4754/DO-178/DO-254. 
____TEXT;
$pdf->writeHTML($html_text, true, 0, true, 0);
// reset pointer to the last page
$pdf->lastPage();
// ---------------------------------------------------------
// add table of content at page 1
// add a new page for TOC
$pdf->AddPage();
// write the TOC title
$pdf->SetFont('times', 'B', 16);
$pdf->MultiCell(0, 0, 'Table Of Content', 0, 'C', 0, 1, '', '', true, 0);
$pdf->Ln();

$pdf->SetFont('dejavusans', '', 12);
$pdf->addTOC(1, 'courier', '.', 'Index');
//Close and output PDF document
$pdf->Output($logbook->title.'_'.$today_date.'.pdf', 'I');
