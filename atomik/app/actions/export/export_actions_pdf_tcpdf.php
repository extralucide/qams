<?php
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
require_once('tcpdf/config/lang/fra.php');
define ('K_TCPDF_EXTERNAL_CONFIG', true);
require_once('tcpdf/tcpdf.php');

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
$context_array['aircraft_id']= Atomik::has('session/current_aircraft_id')?Atomik::get('session/current_aircraft_id'):"";
$context_array['project_id']= isset($_GET['show_project']) ? $_GET['show_project'] : (Atomik::has('session/current_project_id')?Atomik::get('session/current_project_id'):Atomik::get('session/project_id'));
$context_array['sub_project_id'] = Atomik::has('session/sub_project_id')?Atomik::get('session/sub_project_id'):"";
$context_array['baseline_id'] = Atomik::has('session/baseline_id')?Atomik::get('session/baseline_id'):"";
$context_array['user_id'] = Atomik::has('session/user_id')?Atomik::get('session/user_id'):"";
Tool::deleteKey('session/search');
// create new PDF document
// define ('K_PATH_MAIN', $_SERVER['DOCUMENT_ROOT'].Atomik::asset('app/includes/tcpdf/'));
// DOCUMENT_ROOT fix for IIS Webserver
if ((!isset($_SERVER['DOCUMENT_ROOT'])) OR (empty($_SERVER['DOCUMENT_ROOT']))) {
	if(isset($_SERVER['SCRIPT_FILENAME'])) {
		$_SERVER['DOCUMENT_ROOT'] = str_replace( '\\', '/', substr($_SERVER['SCRIPT_FILENAME'], 0, 0-strlen($_SERVER['PHP_SELF'])));
	} elseif(isset($_SERVER['PATH_TRANSLATED'])) {
		$_SERVER['DOCUMENT_ROOT'] = str_replace( '\\', '/', substr(str_replace('\\\\', '\\', $_SERVER['PATH_TRANSLATED']), 0, 0-strlen($_SERVER['PHP_SELF'])));
	} else {
		// define here your DOCUMENT_ROOT path if the previous fails (e.g. '/var/www')
		$_SERVER['DOCUMENT_ROOT'] = '/';
	}
}

// be sure that the end slash is present
$_SERVER['DOCUMENT_ROOT'] = str_replace('//', '/', $_SERVER['DOCUMENT_ROOT'].'/');

// Automatic calculation for the following K_PATH_MAIN constant
$k_path_main = str_replace( '\\', '/', realpath(substr(dirname(__FILE__), 0, 0-strlen('config'))));
if (substr($k_path_main, -1) != '/') {
	$k_path_main .= '/';
}

/**
 * Installation path (/var/www/tcpdf/).
 * By default it is automatically calculated but you can also set it as a fixed string to improve performances.
 */
define ('K_PATH_MAIN', $k_path_main."../includes/tcpdf/");
define ('K_PATH_URL', 'http://');
define ('K_PATH_FONTS', K_PATH_MAIN.'fonts/');
define ('K_PATH_CACHE', K_PATH_MAIN.'cache/');
define ('K_PATH_URL_CACHE', K_PATH_URL.'cache/');
define ('K_PATH_IMAGES', $k_path_main."../../assets/images/");
define ('K_BLANK_IMAGE', K_PATH_IMAGES.'_blank.png');
define ('PDF_PAGE_FORMAT', 'A4');
define ('PDF_PAGE_ORIENTATION', 'P');
define ('PDF_CREATOR', 'TCPDF');
define ('PDF_AUTHOR', 'TCPDF');
define ('PDF_HEADER_TITLE', 'TCPDF Example');
define ('PDF_HEADER_STRING', "by Nicola Asuni - Tecnick.com\nwww.tcpdf.org");
define ('PDF_HEADER_LOGO', 'tcpdf_logo.jpg');
define ('PDF_HEADER_LOGO_WIDTH', 30);
define ('PDF_UNIT', 'mm');
define ('PDF_MARGIN_HEADER', 5);
define ('PDF_MARGIN_FOOTER', 10);
define ('PDF_MARGIN_TOP', 27);
define ('PDF_MARGIN_BOTTOM', 25);
define ('PDF_MARGIN_LEFT', 15);
define ('PDF_MARGIN_RIGHT', 15);
define ('PDF_FONT_NAME_MAIN', 'helvetica');
define ('PDF_FONT_SIZE_MAIN', 10);
define ('PDF_FONT_NAME_DATA', 'helvetica');
define ('PDF_FONT_SIZE_DATA', 8);
define ('PDF_FONT_MONOSPACED', 'courier');
define ('PDF_IMAGE_SCALE_RATIO', 1.25);
define('HEAD_MAGNIFICATION', 1.1);
define('K_CELL_HEIGHT_RATIO', 1.25);
define('K_TITLE_MAGNIFICATION', 1.3);
define('K_SMALL_RATIO', 2/3);
define('K_THAI_TOPCHARS', true);
define('K_TCPDF_CALLS_IN_HTML', true);
define('K_TCPDF_THROW_EXCEPTION_ERROR', false);
	
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, 
				PDF_UNIT, 
				PDF_PAGE_FORMAT, 
				true, 
				'iso-8859-1', 
				false); 

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor(User::getNameUserLogged());
$pdf->SetTitle('Actions list');
$pdf->SetSubject('Quality Assurance Actions list');
$pdf->SetKeywords('quality, process, assurance, action');

$logbook = new Logbook(&$context_array);
$today_date = date("d M Y");
$title = $logbook->board;
$filename="result/".$logbook->board."_action_list_".$today_date.".pdf";
$pdf->SetHeaderData('../../assets/images/zodiacaerospace.jpeg', PDF_HEADER_LOGO_WIDTH, $title,'Actions list by '.User::getNameUserLogged().'                    '.date("d F Y") );

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

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
// Print a table

// add a page
$pdf->AddPage();
//Titres des colonnes
$header=array('Id','Project','Context','Assignee','Description','Status','Date open','Date expected');
$w=array(5,10,16,8,30,6,8,8);
$font_size = 6;
$htmltable = <<<EOF
<style>
	h1 {
		color: navy;
		font-family: times;
		font-size: 24pt;
		text-decoration: underline;
	}
	p.first {
		color: #003300;
		font-family: helvetica;
		font-size: 12pt;
	}
	p.first span {
		color: #006600;
		font-style: italic;
	}
	p#second {
		color: rgb(00,63,127);
		font-family: times;
		font-size: 12pt;
		text-align: justify;
	}
	p#second > span {
		background-color: #FFFFAA;
	}
	table.first {
		color: #003300;
		font-family: helvetica;
		font-size: 8pt;
		border-left: 3px solid red;
		border-right: 3px solid #FF00FF;
		border-top: 3px solid green;
		border-bottom: 3px solid blue;
		background-color: #ccffcc;
	}

	td.second {
		border: 2px dashed green;
	}
	div.test {
		color: #CC0000;
		background-color: #FFFF66;
		font-family: helvetica;
		font-size: 10pt;
		border-style: solid solid solid solid;
		border-width: 2px 2px 2px 2px;
		border-color: green #FF00FF blue red;
		text-align: center;
	}
	td.deadline {
		background-color: "orange";
		color="green";
	}	
</style>
EOF;
$htmltable.= 	'<table>'.
				'<thead>'.
				'<tr bgcolor="darkgrey" >';
for($i=0;$i<count($header);$i++) {
        $htmltable.= '<td width="'.$w[$i].'%"><font size="'.$font_size.'" color="#FFFFFF" face="Arial">'.$header[$i].'</font></td>';        
		// $htmltable.= '<td><font size="'.$font_size.'" color="#FFFFFF" face="Arial">'.$header[$i].'</font></td>';
}
$htmltable.= '</tr></thead>';
// create some HTML content
$fill = false;
$action->setOrder(" actions.status ASC, date_expected ASC, ");
foreach($action->getActions() as $row) {
	$action->get($row['id']);
	if ($fill) {
		$htmltable.= '<tr bgcolor="#FFFFFF">';	
	}
	else {
		$htmltable.= '<tr bgcolor="#EEEEEE">';	
	}
	$description = stripslashes(Tool::clean_text($action->getDescription()));
    if ($action->status == "Closed" ) {
		$description .= "<br/> <strong>[".$action->date_closure."] ".$action->response."</strong>";
		$status = "Closed";
	}
	else {
		if ($action->getDeadlineOver()){
			$status = '<span color="orange">Open</span>';
		}
		else{
			$status = "Open";
		}
	}	
	$data= array();
	$context = Tool::clean_text($action->context);
	// echo $action->context;
	array_push($data, $action->id, $action->project." ".$action->lru,$action->context,$action->getAssignee(true),$description,$status,$action->date_open,$action->date_expected);
    $index = 0;
    foreach ($data as $val) {
       // $htmltable.= '<td width="'.$w[$index++].'%" ><font size="'.$font_size.'" color="#808080" face="Arial">'.$val.'</font></td>'; 
	   $htmltable.= '<td width="'.$w[$index++].'%">'.$val.'</td>';
    }
    $fill=!$fill;
    $htmltable.= '</tr>';	
}
$htmltable.= '</table>';
// echo $htmltable;
// output the HTML content
$pdf->writeHTML($htmltable, true, false, true, false, '');
// $pdf->writeHTML($htmltable, true, 0, true, 0);
$html_text = <<<____TEXT
Action is <strong>closed</strong> when the person responsible of the action rules on an adopted solution and when Process/Quality Assurance Manager validates this solution.<br/> In other case, the action stays <strong>opened</strong> and in progress.
____TEXT;
$pdf->writeHTML($html_text, true, 0, true, 0);
// add a page
$pdf->AddPage();
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
if ($nb_actions > 0) {
	$htmltable = "<h1 style='padding-left:30px'>".$nb_actions_closed." actions closed from ".$nb_actions."</h1>";
	$pdf->writeHTML($htmltable, true, 0, true, 0);
	if (Atomik::has('session/actions_graph')){
		$graphs_encoded = Atomik::get('session/actions_graph');
		$graphs_file_list=unserialize(urldecode(stripslashes(stripslashes($graphs_encoded))));
	
	$pdf->Image($graphs_file_list['actions_pie'], 15, 50, 0, 0, 'PNG');
	$pdf->Image($graphs_file_list['actions_bar'], 15, 120, 180, 90, 'PNG');
	// add a page
	$pdf->AddPage();
	$pdf->Image($graphs_file_list['actions_spline'], 15, 50, 180, 90, 'PNG');
	}
}
else {
	$htmltable = "<h1 style='padding-left:30px'>No actions available.</h1>";
	$pdf->writeHTML($htmltable, true, 0, true, 0);
}
// reset pointer to the last page
$pdf->lastPage();

// ---------------------------------------------------------
//Close and output PDF document
$pdf->Output($title.'_Action_List_'.$today_date.'.pdf', 'I');
