<?php
/* include "../includes/config.php"; */
Atomik::needed("Data.class");	
Atomik::needed("Date.class");
Atomik::needed("Review.class");
Atomik::needed("Company.class");
Atomik::needed("User.class");
Atomik::needed("Tool.class");
Atomik::needed("Action.class");
Atomik::needed('Project.class');
Atomik::needed('Aircraft.class');

function get_leader($item) {
	$user = new User;
	$leader ="";
	if ($item->leader !="") {
		$user->get_user_info($item->leader);
		$leader = "<span style='color:#AAF'>";
		$leader.= "<img border='0' src='".Atomik::asset('assets/images/16x16/tux.png')."' title='".$user->name."'>";
		$leader.= "</span>  Person in charge: <a href='".Atomik::url('edit_user',array('id'=>$item->leader))."'>".$user->name."</a>";
	}
	unset($user);
	return($leader);
}
function build_menu($name,$tree) {
	$text  =  "<table style='width:100px'><thead><tr>";
	$text .=  "<td>".$name."</td>";
	$text .=  "<td><a><span class='down_arrow' onClick=\"return display_atomik_review('".$tree."',this)\"></a></td>";
	$text .=  "</tr></thead></table>";
	$text = "<a><b>".$name."</b><span class='down_arrow2' onClick=\"return display_atomik_review('".$tree."',this)\"></span></a>";
	return $text;
}
function build_description($item,$local_root_path) {
	$text = "";
	if ($item->description != ""){
		$text .= "<h3>Abstract</h3>";
		$text .=  "<p style='margin-left:10px'>".str_replace("\\n","<br/ >",$item->description)."</p>";
	}
	return $text;
}	
function build_info($item,$local_root_path) {
	$text = "";
	if ($item->description != ""){
		$text .= "<h3>Abstract</h3>";
		$text .=  "<p style='margin-left:10px'>".str_replace("\\n","<br/ >",$item->description)."</p>";
	}	
	$text .= "<h3>Info</h3>";
	$leader = get_leader(&$item);
	if ($leader != ""){
		$text .= "<p class='decal'>".$leader."</p>";
	}
	if ($item->icm != ""){
		$text .= "<p class='decal'><a href='#' OnClick='get_working_dir(\"".$local_root_path.$item->icm."\");return false'><img border='0' src='".Atomik::asset('assets/images/16x16/edit.png')."' title='Coord Memo'> Coord Memo<a/></p>";
	}	
	if ($item->pr != ""){
		$text .= "<p class='decal'><a href='#' OnClick='get_working_dir(\"".$local_root_path.$item->pr."\");return false'><img border='0' src='".Atomik::asset('assets/images/16x16/whatsnext.png')."' title='EPR'> Problem Reports<a/></p>";
	}
	if ($item->folder != ""){
		$text .= "<p class='decal'><a href='#' border='0' OnClick='get_working_dir(\"".$local_root_path.$item->folder."\");return false'>";
		$text .=  "<img border='0' src='".Atomik::asset('assets/images/16x16/folder.png')."' title='Working directory'> Working directory</a></p>";
	}
	unset($leader);
	return $text;
}
function build_doc($item,$local_root_path) {
	$text = "<h3>Document+s location</h3>";
	foreach ($item->doc as $doc) {
		$text .="<p class='decal'><a href='".$local_root_path.$doc->loc."' border='0'>";
		$text .="<img border='0' src='".Atomik::asset('assets/images/16x16/news_subscribe.png')."' title='Docs'>";
		$text .= $doc->name."</a></p>";
	}
	return $text;
}

/* Check database */
Atomik::needed("Db.class");
$db = new Db;
Tool::deleteKey('session/search');
$line_counter = 0;
$title = "";
$today_date = date('Y-m-d');
$today = date("Y-m-d");
$where = "date >= '".$today_date."'";
$actions_list = null;
$all_posts = null;
$data_list = null;
$project = new Project;
$project_found = false;
$aircraft_found = false;
if (isset($_GET['current_aircraft_id'])){
	$current_aircraft_id = $_GET['current_aircraft_id'];
	$_SESSION['current_aircraft_id'] = $current_aircraft_id;
	$current_aircraft = Atomik_Db::find("aircrafts","aircrafts.id = {$current_aircraft_id}");
	$_SESSION['current_aircraft'] = $current_aircraft['name'];
	unset($_SESSION['current_project_id']);
	$_SESSION['current_project_name'] = Aircraft::getAircraftName($current_aircraft_id);;
}
else if (isset($_GET['current_project_id'])){
	if ($_GET['current_project_id'] == "none"){
		unset($_SESSION['current_project_id']);
		unset($_SESSION['current_project_name']);
		unset($_SESSION['current_aircraft_id']);
		unset($current_aircraft_id);
	}
	else {
		$current_project_id = $_GET['current_project_id'];
		unset($current_aircraft_id);
		unset($_SESSION['current_aircraft']);
		$_SESSION['current_project_id'] = $current_project_id;
		$project->get($current_project_id);
		// $current_project = Atomik_Db::find("projects","projects.id = {$current_project_id}");
		$_SESSION['current_aircraft_id'] = $project->getAircraftId();
		// $_SESSION['current_project_name'] = $project->getAircraft();
		// $_SESSION['current_project_name'] = " ";
		$_SESSION['current_project_name'] = $project->getProjectName();
	}
}

if ((isset($_SESSION['current_project_id']))&&($_SESSION['current_project_id'] != "")){
	$previous_project_id = isset($_SESSION['previous_project_id']) ? $_SESSION['previous_project_id'] : "";
	$current_project_id = $_SESSION['current_project_id'];
	$current_project = $_SESSION['current_project_name'];
	$_SESSION['previous_project_id'] = $current_project_id;
	if ($current_project_id != $previous_project_id){
		/* Project is changed */
		$message= 'Project '.$current_project.' selected.';
		$label = 'success';
		Tool::resetSession();			
	}	
	else {
		$message= null;
	}
	/* Hot actions */
	$actions_list = Action::getHotActions("",$current_project_id);
	/* Major events and reviews */
	$all_posts = Review::getHotReviews("",$current_project_id);		 
	/* Hot peer data reviews */	  		 
	$data_list = Data::getHotPeerReview("",$current_project_id);	
}
else if (isset($_SESSION['current_aircraft_id'])){
	$current_project_id="";
	if (isset($_SESSION['current_project_id'])){
		unset($_SESSION['current_project_id']);
	}
	if (isset($_SESSION['project_id'])){
		unset($_SESSION['project_id']);
	}
	if (isset($_SESSION['previous_project_id'])){
		unset($_SESSION['previous_project_id']);
	}	
	$current_project = "None";
	$current_aircraft_id = $_SESSION['current_aircraft_id'];
	$current_aircraft = $_SESSION['current_aircraft'];
	$message= 'Aircraft '.$current_aircraft.' selected.';
	$label = 'warning';
	/* Hot actions */
	$actions_list = Action::getHotActions($current_aircraft_id);
	/* Major events and reviews */
	$all_posts = Review::getHotReviews($current_aircraft_id);	
	/* Hot peer data reviews */	  		 
	$data_list = Data::getHotPeerReview($current_aircraft_id);	
	$aircraft_found = true;
	$aircraft = new Aircraft;
	$aircraft->get($current_aircraft_id);
	$html_project = "<div style='margin-left:10px;position:relative;background-image: url(\"".$aircraft->photo_file."\");background-repeat: no-repeat;opacity:1;height:600px'>";
	$html_project.= "<div style='color:#FFF;background-color:#000;min-height:800px;width:390px;margin-left:400px;opacity:0.7;padding-top:10px'>";
	$html_project.= "<h3>Aircraft</h3>";
	$html_project.= "<p style='padding-left:20px'>".$aircraft->description."</p>";	
	$html_project.= "</div></div>";	
}
else {
	Tool::resetSession();
	$current_project_id="";
	$_SESSION['current_project_name'] = "";
	$_SESSION['previous_project_id'] ="";
	$previous_project_id ="";
	$show_project= "";
	$current_project = "None";
	$current_aircraft = "None";
	$message= 'Please select an aircraft.';
	$label = 'warning';
}
Tool::deleteKey('session/baseline_id');
Tool::deleteKey('session/search');

/* Last read documents*/
$last_read_data = Data::getLastRead();
$companies_list = Company::getCompany();
/* ---------------------------------------------------------------------------------------------------------------- */
/* --------------------------------------------->>> Menu vertical <<<---------------------------------------------- */
/* ---------------------------------------------------------------------------------------------------------------- */
$html ='
<div id="menu_projects">
    <div class="subnav">
		<h1>Programs</h1>';
$html.='<ul class="li_below"><li><a href="#">Aircrafts</a>';
$html.='<ul>	
			<li class="noarrow"><a href="'.Atomik::url('home',array('current_project_id' => "none")).'">All</a></li>';
			$aircrafts_list = Aircraft::getAircrafts();
			// $html.='<ul class="li_below">';
			foreach ($aircrafts_list as $aircraft):
				$html.='<li class="noarrow"><a href="'.Atomik::url('home',array('current_aircraft_id' => $aircraft['id'])).'">'.$aircraft['company']." ".$aircraft['aircraft'].'</a></li>';
			endforeach;
			$html.='</ul>';
		$html.='</ul></li>';	
		$html.='</ul>';
$html .= <<<____HTML
		<ul class="li_below">
		<li class="noarrow"><a href="
____HTML;
$html .= Atomik::url('home',array('current_project_id' => 6));
$html .= <<<____HTML
">Methodology</a></li>
		<li class="noarrow"><a href="
____HTML;
$html .= Atomik::url('home',array('current_project_id' => 16));
$html .= <<<____HTML
">Quality</a></li>
		</ul>
____HTML;
/*
    	$html.='<h1>Memorandum</h1>';
        $html.='<ul class="li_below">';
    	$html.='<li><a href="'.Atomik::url('create_memo').'">Create Memo</a>';
		$html.='	<ul>';
		$html.='	    <li class="noarrow"><a href="'.Atomik::url('create_memo').'">Create Memo</a></li>';
        $html.='        <li class="noarrow"><a href="'.Atomik::url('create_minutes').'">Create Minutes of the Meeting</a></li>';
        $html.='        <li class="noarrow"><a href="../create_audit_select.php">Create Audit Report</a></li>';
		$html.='		<li class="noarrow"><a href="../create_remboursement_frais.php">Remboursement frais</a></li>';
		$html.='		<li class="noarrow"><a href="../create_feuilles_heures.php">Feuilles d\'heures</a></li>';
        $html.='   </ul>';
		$html.='</li>';
		$html.='</ul>';*/
		$config = Atomik::get('db_config');
		/* Line for finister.freeheberg.com */
		if($config['select'] != "finister"){		
			$html.='<h1>Procedures</h1>';
			$html.='<ul class="li_below">';          
			$html.='<li><a href="'.Atomik::url('data', array('page' => 1,'limite' => 100,'order'=>'id','show_project'=>6,'show_lru'=>7,'show_type'=>22,'show_baseline'=>'','search'=>'NOTREGEXP^7')).'">ECE</a>';
			$html.='<ul class="li_below">';
			$html.='<li class="noarrow"><a href="'.Atomik::url('data', array('page' => 1,'limite' => 100,'order'=>'id','show_project'=>6,'show_lru'=>7,'show_type'=>22,'show_poster'=>'','search'=>'NOTREGEXP^7')).'">IG</a></li>';
			$html.='<li class="noarrow"><a href="'.Atomik::url('data', array('page' => 1,'limite' => 20,'order'=>'id','show_project'=>6,'show_lru'=>7,'show_type'=>29,'show_poster'=>'','limite'=>100)).'">SAQ</a></li>';
			$html.='<li class="noarrow"><a href="'.Atomik::url('data', array('page' => 1,'limite' => 20,'order'=>'id','show_project'=>6,'show_lru'=>7,'show_type'=>49,'show_poster'=>'','limite'=>100)).'">ZAQ</a></li>';
			$html.='</ul>';
			$html.='</li>';
			$html.='<li class="noarrow"><a href="'.Atomik::url('data', array('page' => 1,'limite' => 20,'order'=>'id','show_project'=>6,'show_lru'=>7,'show_type'=>22,'show_poster'=>'','search'=>'REGEXP^7')).'">IN-D7</a></li>';
			$html.='<li class="noarrow"><a href="'.Atomik::url('data', array('page' => 1,'limite' => 20,'order'=>'id','show_project'=>6,'show_lru'=>7,'show_type'=>51,'show_poster'=>'')).'">ABD</a>';
			$html.='<ul class="li_below"><li><a href="'.Atomik::url('show_abd0100').'">ABD0100 Requirements</a></li></ul></li>';
			$html.='</ul>';
		}
		$html.='<h1>Certification</h1>';
        $html.='<ul class="li_below">';
		$html.='<li><a href="'.Atomik::url('data', array('page' => 1,'limite' => 20,'order'=>'id','show_aircraft'=>8,'show_project'=>6,'show_baseline'=>49,'show_poster'=>'','show_lru'=>'','show_application'=>'','show_type'=>'')).'">DO</a>';
		$html.='<ul  class="li_below">';
		$html.='<li class="noarrow"><a href="'.Atomik::url('do178/do178_objectives').'">DO178B objectives</a></li>';
		$html.='<li class="noarrow"><a href="'.Atomik::url('do178/show_table_do178b').'">DO178B chapters</a></li>';
        $html.='<li class="noarrow"><a href="'.Atomik::url('show_table').'">DO254 chapters</a></li>';
		$html.='</ul>';
		$html.='</li>';
		$html.='<li class="noarrow"><a href="'.Atomik::url('data', array('page' => 1,'limite' => 20,'order'=>'id','show_project'=>6,'show_lru'=>7,'show_type'=>39,'show_application'=>'','show_poster'=>'')).'">ARP</a></li>';
		$html.='<li class="noarrow"><a href="'.Atomik::url('show_table_soi').'">SOI Checklists</a></li>';
        $html.='<li class="noarrow"><a href="'.Atomik::url('data', array('page' => 1,'limite' => 20,'order'=>'id','show_project'=>6,'show_lru'=>7,'show_type'=>32,'show_application'=>'','show_poster'=>'')).'">CAST</a>';
		$html.='<ul class="li_below">';
		$html.='<li class="noarrow"><a href="http://www.faa.gov/aircraft/air_cert/design_approvals/air_software/cast/cast_papers/">FAA website</a></li>';
		$html.='</ul>';
		$html.='</li>';
		$html.='<li><a href="'.Atomik::url('data',array('show_type'=>89)).'">EASA Certification Memoranda</a>';
		$html.='<ul class="li_below">';
		$html.='<li class="noarrow"><a href="http://easa.europa.eu/certification/certification-memoranda.php">EASA website</a></li>';
		$html.='</ul>';
		$html.='</li>';
        $html.='<li class="noarrow"><a href="'.Atomik::url('data',array('show_type'=>30)).'">CRI</a></li>';
        $html.='</ul>';
		if($config['select'] != "finister"){
			$html.='<h1>Databases</h1>';
			$html.='<ul class="li_below">';
			$html.='<li><a href="">Identification</a>';
			$html.='<ul>';
			$html.='<li class="noarrow"><a href="'.Atomik::asset('../../qualite/11%20-%20COURRIERS/COURRIERS%20DEPART/01-CHRONO-FLN.xls').'">Identification Chrono (DQ/..)</a></li>';
			$html.='<li class="noarrow"><a href="'.Atomik::asset('../../qualite/12%20-%20REFERENCEMENT/plans%20qualit%C3%A9/R%C3%A9pertoire%20des%20plans%20qualit%C3%A9.xls').'">Identification Plans Qualit&eacute; (PQ0.1.0...)</a></li>';
			$html.='<li class="noarrow"><a href="'.Atomik::asset('../../qualite/12%20-%20REFERENCEMENT/R%C3%A9f%C3%A9rences%20Logiciels%20et%20Composants%20Complexes%20Mat%C3%A9riels/Tableau%20de%20bord%20m%C3%A9moire.xls').'">Identification CSCI (A3..)</a></li>';
			$html.='<li class="noarrow"><a href="'.Atomik::asset('../../gpe/FICHIERS DISPONIBLES au GPE (Dont ET et SDT)/ET_ENREGISTREMENT DES PROPOSITIONS  en  ET.xlsx').'">GE documents (ETxxxx)</a></li>';
			$html.='</ul>';
			$html.='</li>';
			$html.='<li class="noarrow"><a href="'.Atomik::asset('http://intranet-ece.in.com/content/download/3638/12046/file/Listing_des_Projets_et_Codes_Projets_V30_30-05-2012.xlsx').'">Code projets</a></li>';
			$html.='<li><a href="'.Atomik::asset('http://intranet-ece.in.com/drh/accords_et_gestion_du_personnel/vos_demarches').'">D&eacute;marches RH</a>';
			$html.='<ul>';
			$html.='<li class="noarrow"><a href="'.Atomik::asset('https://www.zadig-hr.adp.com').'">Demandes de con&eacute;s (Zadig)</a></li>';
			$html.='<li class="noarrow"><a href="'.Atomik::asset('http://intranet-ece.in.com:8080/dq_form_data/175.doc').'">Ordre de mission</a></li>';
			$html.='<li class="noarrow"><a href="'.Atomik::asset('http://intranet-ece.in.com:8080/dq_form_data/173.xls').'">Remboursmeent de frais</a></li>';
			$html.='</ul>';
			$html.='</li>';
			$html.='<li class="noarrow"><a href="'.Atomik::asset('../../maturity/LAQ037_BLL_Draft.xls').'">Lesson Learnt</a></li>';
			$html.='</ul>';	
		}
		/*
		if ($current_project_id == ""){
			$html.='<div class="tundra" style="margin-left:5px;margin-top:10px;margin-bottom:10px;float:left">';
			$html.='<div style="margin-left:10px;" id="gallery1" dojoType="dojox.image.SlideShow" noLink="false" loop="true" autoLoad="true" autoStart="true" slideshowInterval="5"  imageWidth="200" imageHeight="240" titleTemplate="" fixedHeight="true" hasNav="false" _tempImgPath="assets/images/loading.gif">';
			$html.='</div>';
			$html.='<div jsId="imageItemStore" dojoType="dojo.data.ItemFileReadStore" url="images.json">';
			$html.='</div>';
			$html.='</div></div>';
			$html.='<div class="spacer">';
		}
		*/
		$html.= '</div>';
		$html.=	'</div>';
if ($current_project_id != ""){
	$project = new Project;
	$project->get($current_project_id);
	$aircraft = new Aircraft;
	$aircraft->get($project->aircraft_id);
	$html_project = "<div style='margin-left:10px;position:relative;background-image: url(\"".$aircraft->photo_file."\");background-repeat: no-repeat;opacity:1;height:600px'>";
	$html_project.= "<div style='color:#FFF;background-color:#000;min-height:800px;width:390px;margin-left:400px;opacity:0.7;padding-top:10px'>";
	$html_project.= "<h3>Aircraft</h3>";
	$html_project.=  "<p style='padding-left:20px'>".$aircraft->description."</p>";
	$html_project.= "<h3>Project</h3>";
	$html_project.=  "<p style='padding-left:20px'>".$project->description."</p>";
	$html_project.= "<h3>Workspace</h3>";
	$html_project.=  "<p style='padding-left:20px'><a href='' onclick=\"window.top.window.ouvrir('../../".$project->getWorkspace()."')\">Link to project workspace</a></p>";		
	//$html_project.= build_description(&$aircraft,&$root_path);
	$html_project.= "</div></div>";		
	$aircraft_found = true;	
}
/*
$xml = simplexml_load_file('projects.xml');
foreach ($xml->customers->customer as $customer):
	foreach ($customer->aircraft as $aircraft):
		if (isset($current_aircraft_id) && ($aircraft->id == $current_aircraft_id)){
			$html_project.= "<div style='margin-left:10px;position:relative;background-image: url(\"".$aircraft->image."\");background-repeat: no-repeat;opacity:1;height:600px'>";
			$html_project.= "<div style='color:#FFF;background-color:#000;min-height:800px;width:390px;margin-left:400px;opacity:0.7;padding-top:10px'>";	
			$html_project.= build_description(&$aircraft,&$root_path);
			$html_project.= "</div></div>";		
			$aircraft_found = true;
			break 2;
		}
		foreach ($aircraft->system as $system) {
			if (isset($current_project_id) && ($system->id == $current_project_id)){
				$html_project = "<ul>";
				$html_project.= '<h1>'.$customer->name.' '.$aircraft->name.' '.$system->name.'</h1>';
				$html_project.= "<div style='position:relative;background-image: url(\"".$aircraft->image."\");background-repeat: no-repeat;opacity:1;height:600px'>";
				$html_project.= "<div style='color:#FFF;background-color:#000;min-height:800px;width:600px;margin-left:10px;opacity:0.7;padding-top:10px;overflow:scroll'>";
				$html_project.= build_description(&$aircraft,&$root_path);
				//$html_project.= "<h3>Abstract</h3>";
				//$html_project.= "<p style='margin-left:10px'>".str_replace("\\n","<br/ >",$system->description)."</p>";
				$html_project.= "<h3>Product Breakdown Structure</h3>";
				$html_project.= "<div class='subnav_'>";
				$html_project.= "<ul class='li_below_'><li style='padding-top:10px;margin-left:30px'>".$system->name."</a>";
				$html_project.= build_info(&$system,&$root_path);
				$html_project.= "<ul>";
				$subsystem_id = 0;
				foreach ($system->subsystem as $subsystem) {
					$subsystem_id++;
					$html_project.= "<li>";
					$html_project.= build_menu($subsystem->name,'tree_subsystem_'.$subsystem_id);
					$html_project.= "<ul class='menu' id='tree_subsystem_".$subsystem_id."'>";
					$html_project.= build_info(&$subsystem,&$root_path);
					// doc
					$html_project.= build_doc(&$subsystem,&$root_path);
					//$html_project.= "</ul>";
					//$html_project.= "<ul>";
					$equipment_id = 0;
					foreach ($subsystem->equipment as $equipment) {
						$equipment_id++;
						$html_project.= "<li class='decal'>";
						$html_project.= build_menu($equipment->name,'tree_equipment_'.$equipment_id);
						$html_project.= "<ul class='menu' id='tree_equipment_".$equipment_id."'>";
						$html_project.= build_info(&$equipment,&$root_path);
						$html_project.= build_doc(&$equipment,&$root_path);
						$board_id = 0;
						foreach ($equipment->board as $board) {  
							$board_id++;
							$html_project.= "<li class='decal'>";
							$html_project.= build_menu($board->name,'tree_board_'.$equipment_id.'_'.$board_id);
							$html_project.= "<ul class='menu' id='tree_board_".$equipment_id.'_'.$board_id."'>";	
							$html_project.= build_info(&$board,&$root_path);
							$html_project.= build_doc(&$board,&$root_path);							
							foreach ($board->hardware as $hardware) {
								// there is a name (i.e. there is an FPGA ?)
								if ($hardware->name != "") {
									$html_project.= "<li  class='decal'>".$hardware->name." PLD</li>";
									$html_project.= build_info(&$hardware,&$root_path);
									$html_project.= build_doc(&$hardware,&$root_path);
								}
							}
							foreach ($board->software as $software) {
								// there is a name (i.e. there is a software ?)
								if ($software->name != "") {
									$html_project.= "<li  class='decal'>".$software->name." SW</li>";
									$html_project.= build_info(&$software,&$root_path);
									$html_project.= build_doc(&$software,&$root_path);
								}
							}
							$html_project.= "</ul>";
							$html_project.= "</li>";
						}
						$html_project.= "</ul>";
						$html_project.= "</li>";
					}
					$html_project.= "</ul>";
					$html_project.= "</li>";
				}
				$html_project.= "</ul>";
				$html_project.= "</li></ul>";
				$html_project.= "</div>";
				$html_project.= "<h3>Working directory</h3>";
				$html_project.= "<p style='margin-left:10px'>";
				$html_project.= '<a href="#" OnClick="get_working_dir(\''.$root_path.$system->folder.'\');return false">Working directory</a></p>';
				$html_project.= "</div>";
				$html_project.= "</div>";
				$html_project.= "</ul>";
				$project_found = true;
				break 3;
			} 
		}
	endforeach;
endforeach;
*/
Atomik::set("css_search","no_show");
Atomik::set('title',"Dashboard");
Atomik::set('css_title',"logbook");
Atomik::set('css_reset',"no_show");
Atomik::set('css_add',"no_show");
Atomik::set('select_menu',$html);
Atomik::set('css_page',"no_show");
Atomik::set('css_page_previous','no_show');
Atomik::set('css_page_next','no_show');
header("Cache-Control: no-cache");
