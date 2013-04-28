<?php
Atomik::disableLayout();
Atomik::needed('Tool.class');
Atomik::needed('Db.class');
Atomik::needed('Date.class');
Atomik::needed('Data.class');
Atomik::needed('Remark.class');
Atomik::needed('Project.class');
Atomik::needed('User.class');
Atomik::needed('Baseline.class');
Atomik::needed('PeerReviewer.class');

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;  
$limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 8;
$all = isset($_GET['all']) ? $_GET['all']: "no";
$nbtotal = isset($_GET['nb_total']) ? $_GET['nb_total'] : "";
$nbpage = Tool::compute_pages($nbtotal,&$page,&$debut,$limite);	
$group = isset($_REQUEST['group']) ? $_REQUEST['group'] : "";
if (isset($_GET['context'])){
  $context = $_GET['context'];
  $context_array=unserialize(urldecode(stripslashes((stripslashes($_GET['context'])))));	
}
if(!isset($context_array['aircraft_id']))$context_array['aircraft_id']= Atomik::has('session/current_aircraft_id')?Atomik::get('session/current_aircraft_id'):"";
if(!isset($context_array['project_id']))$context_array['project_id']= Atomik::get('session/current_project_id');
if(!isset($context_array['sub_project_id']))$context_array['sub_project_id']=Atomik::get('session/sub_project_id');
if(!isset($context_array['review_id']))$context_array['review_id']="";
if(!isset($context_array['data_status_id']))$context_array['data_status_id']=Atomik::get('session/data_status_id');
if(!isset($context_array['user_id']))$context_array['user_id']=Atomik::get('session/user_id');
if(!isset($context_array['criticality_id']))$context_array['criticality_id']="";	
if(!isset($context_array['baseline_id']))$context_array['baseline_id']="";	
if(!isset($context_array['data_id']))$context_array['data_id']="";
if(!isset($context_array['type_id']))$context_array['type_id']="";
if(!isset($context_array['group_id']))$context_array['group_id']= Atomik::get('session/highlight/group_id');
if(!isset($context_array['order']))$context_array['order']="";
/* patch */
/*
$show_project =  $context_array['project_id'];
$show_lru = $context_array['sub_project_id'];
$show_baseline = $context_array['baseline_id'];
$show_poster = $context_array['user_id'];
$show_type = $context_array['type_id'];
$show_category = "";
$show_criticality = $context_array['criticality_id'];
$search = "";
$show_status = $context_array['data_status_id'];
$status_id = $context_array['data_status_id'];
$show_id = "";
$show_application = $context_array['data_id'];
*/
$ignore_outdated = true;
$filter_param = "";
$data = new Data(&$context_array);
$project = new Project(&$context_array);
$data->prepare($all);
$list_data_lite = $data->execute($debut,$limite);
	
$line_counter = 0;
$previous_reference = "";
$up =  'date_published ASC, ';
$down =  'date_published DESC, ';
$review_up =  'date_review ASC, ';
$review_down =  'date_review DESC, ';
$id_up =  'id ASC, ';
$id_down =  'id DESC, ';
$header_fields = array("Project"=>2,"Reference"=>4, "Type"=>2,"Description"=>8,"Version"=>2, "Author"=>4 );

$all_baseline_list = $project->getBaseline();
$status_list = Data::getStatusList();
if (count($list_data_lite) == 0){
	Atomik::noRender();
	$no_data_html = "<li class='warning' style='list-style-type: none;margin-top:40px;margin-right:10px'>";
	if ($context_array['group_id'] != ""){
		$group_name = Data::getGroupName($context_array['group_id']);
		$no_data_html .= "No data found in group <b>{$group_name}</b>.";
	}else{
		$no_data_html .= "No data found";
	}
	$no_data_html .= "</li>";
	echo $no_data_html;
	echo "<li class='warning' style='list-style-type: none;margin-top:40px;margin-right:10px'>";
	echo "This area will house all your important project documents. Simply click the \"Add a document\" button below to get started.";
	echo "</li>";
}
