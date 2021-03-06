<?php
//============================================================+
// File name   : example_006.php
// Begin       : 2008-03-04
// Last Update : 2009-09-30
// 
// Description : Example 006 for TCPDF class
//               WriteHTML and RTL support
// 
// Author: Nicola Asuni
// 
// (c) Copyright:
//               Nicola Asuni
//               Tecnick.com s.r.l.
//               Via Della Pace, 11
//               09044 Quartucciu (CA)
//               ITALY
//               www.tecnick.com
//               info@tecnick.com
//============================================================+

/**
 * Creates an example PDF TEST document using TCPDF
 * @package com.tecnick.tcpdf
 * @abstract TCPDF - Example: WriteHTML and RTL support
 * @author Nicola Asuni
 * @copyright 2004-2009 Nicola Asuni - Tecnick.com S.r.l (www.tecnick.com) Via Della Pace, 11 - 09044 - Quartucciu (CA) - ITALY - www.tecnick.com - info@tecnick.com
 * @link http://tcpdf.org
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 * @since 2008-03-04
 */


Atomik::noRender();
Atomik::disableLayout();
Atomik::needed('Data.class');
Atomik::needed('Date.class');
Atomik::needed('Action.class');
Atomik::needed('Logbook.class');
Atomik::needed('Remark.class');
Atomik::needed('Baseline.class');
Atomik::needed('PeerReviewer');
Atomik::needed('User.class');
Atomik::needed('Tool.class');
require_once('../tcpdf/config/lang/fre.php');
require_once('../tcpdf/tcpdf.php');

// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {
	//Page header
	public function Header() {
			$ormargins = $this->getOriginalMargins();
			$headerfont = $this->getHeaderFont();
			$headerdata = $this->getHeaderData();
			if (($headerdata['logo']) AND ($headerdata['logo'] != K_BLANK_IMAGE)) {
				$this->Image(K_PATH_IMAGES.$headerdata['logo'], $this->GetX()+156, $this->getHeaderMargin(), $headerdata['logo_width']);
				$imgy = $this->getImageRBY();
			} else {
				$imgy = $this->GetY();
			}
			$cell_height = round(($this->getCellHeightRatio() * $headerfont[2]) / $this->getScaleFactor(), 2);
			// set starting margin for text data cell
			if ($this->getRTL()) {
				$header_x = $ormargins['right'] + ($headerdata['logo_width'] * 1.1) - 10;
			} else {
				$header_x = $ormargins['left'] + ($headerdata['logo_width'] * 1.1) - 10;
			}
			$this->SetTextColor(0, 0, 0);
			// header title
			$this->SetFont($headerfont[0], 'B', $headerfont[2] );
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
$context_array['project_id']= isset($_GET['show_project']) ? $_GET['show_project'] : (Atomik::has('session/current_project_id')?Atomik::get('session/current_project_id'):Atomik::get('session/project_id'));
$context_array['sub_project_id'] = Atomik::has('session/sub_project_id')?Atomik::get('session/sub_project_id'):"";
$context_array['baseline_id'] = Atomik::has('session/baseline_id')?Atomik::get('session/baseline_id'):"";
$context_array['assignee_id'] = Atomik::has('session/user_id')?Atomik::get('session/user_id'):"";
$context_array['data_id'] = isset($_GET['show_application']) ? $_GET['show_application'] : (Atomik::has('session/data_id')?Atomik::get('session/data_id'):"");

Tool::deleteKey('session/search');
// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'iso-8859-1', false); 

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Olivier Appéré');
$pdf->SetTitle('Actions list');
$pdf->SetSubject('TCPDF Tutorial');
$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

// on teste si cela concerne un document en particulier

if ($context_array['data_id'] != "") {
	// 
	Atomik::needed('Data.class');
	$document = new Data;
	$document->get($context_array['data_id']);
	$pdf->SetHeaderData('../../atomik/assets/images/zodiacaerospace.jpeg', PDF_HEADER_LOGO_WIDTH, $document->reference.' i'.$document->version.' '.$document->project.' '.$document->lru.' '.$document->type.' Peer Review Remarks','by Olivier Appéré                    '.date("d").' '.date("F").' '.date("Y") );

}	  
else {
	$pdf->SetHeaderData('../../atomik/assets/images/zodiacaerospace.jpeg', PDF_HEADER_LOGO_WIDTH,'All Remarks list','by Olivier Appéré                    '.date("d").' '.date("F").' '.date("Y")); 
}
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

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
// Print a table

// add a page
$pdf->AddPage('L', 'A4');
//$pdf->AddPage();
//Titres des colonnes
$header=array('Ref','Inspector name','Paragraph','Line','Remarks','Author response','Defect class','Remarks status','Justification');
$w=array(3,8,10,4,25,20,14,8,8);
//$htmltable = '<table border="1" cellspacing="2" cellpadding="2"><tr>';
$htmltable = '<table><tr bgcolor="grey" color="#EEEEEE" >';
for($i=0;$i<count($header);$i++) {
        $htmltable.= "<td width=".$w[$i]."%>$header[$i]</td>";
}
$htmltable.= '</tr>';
// create some HTML content
// on balaye les lignes par groupe
$counter = 1;
$remark = new Remark(&$context_array);
//$project = new Project(&$context_array);

$list_remarks = $remark->getRemarks();
Atomik::needed('Date.class');
$fill = false;
foreach($list_remarks as $row) {
    $application = $row['application'];
	$data_issue  = $row['version'];
	/* Convert date to display nicely */
	$date_open = Date::convert_date($row['date']);
	if ($fill) {
		$htmltable.= '<tr bgcolor="#CCC">';
	}
	else {
		$htmltable.= '<tr bgcolor="#EEE">';
	}
	$text = "[#".$row['id']."#] ";
	$plain_text     = html_entity_decode ($row['remark'],ENT_QUOTES,"UTF-8");
	if (!($context_array['data_id'] == "")) {
		$text.= "<strong>[".$date_open."]</strong> ".$plain_text;
	}
	else {
		$text.= "<strong>[".$date_open."]</strong> ".$application." ".$row['type']." ".$data_issue." ".$row['remark'];
	}
    $plain_text     =$text;
	$htmltable.= "<td width=".$w[0]."%>R".$counter++."</td>";
	$htmltable.= "<td width=".$w[1]."%>".$row['fname']." ".$row['lname']."</td>";
	$htmltable.= "<td width=".$w[2]."%>".$row['paragraph']."</td>";
	$htmltable.= "<td width=".$w[3]."%>".$row['line']."</td>";
	$htmltable.= "<td width=".$w[4]."%>".$plain_text."</td>";
	/* build author response */
	$sql_author_response = "SELECT * FROM bug_messages,bug_users WHERE bug_users.id = bug_messages.posted_by AND reply_id != bug_messages.id AND reply_id = ".$row['id']." ORDER BY reply_id ASC, bug_messages.id ASC";
	$result_response = A('db:'.$sql_author_response);
	/* erase author response buffer */
	$author_response="";
	foreach($result_response as $row_response) {
	    $date_response   = Date::convert_date_conviviale ($row_response['date']);
		$author_response = $author_response."<strong>[".$date_response."] ".$row_response['fname']." ".$row_response['lname']."</strong> ".$row_response['description']."<br/>";
	}
	$plain_author_response     = html_entity_decode ($author_response,ENT_QUOTES,"UTF-8");
	$htmltable.= "<td width=".$w[5]."%>".$plain_author_response."</td>";
	$htmltable.= "<td width=".$w[6]."%>".$row['category']."</td>";
	$htmltable.= "<td width=".$w[7]."%>".$row['status']."</td>";
	$htmltable.= "<td width=".$w[8]."%>".$row['justification']."</td>";
    $fill=!$fill;
    $htmltable = $htmltable.'</tr>';	
}
$htmltable = $htmltable.'</table>';

// output the HTML content
$pdf->writeHTML($htmltable, true, 0, true, 0);

// reset pointer to the last page
$pdf->lastPage();


// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output('remarks_list.pdf', 'I');

//============================================================+
// END OF FILE                                                 
//============================================================+
