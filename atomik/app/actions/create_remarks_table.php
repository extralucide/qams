<?php
/* Atomik::noRender(); */
Atomik::disableLayout();
/* include "app/config.php";
include "../includes/config.php";*/
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
$nbtotal = isset($_GET['nb_total']) ? $_GET['nb_total'] : "";
$nbpage = Tool::compute_pages($nbtotal,&$page,&$debut,$limite);
$context_array['aircraft_id']= isset($_GET['show_aircraft']) ? $_GET['show_aircraft'] :(Atomik::has('session/current_aircraft_id')?Atomik::get('session/current_aircraft_id'):"");
$context_array['project_id']= isset($_GET['show_project']) ? $_GET['show_project'] : Atomik::get('session/current_project_id');
$context_array['sub_project_id']= isset($_GET['show_lru']) ? $_GET['show_lru'] : Atomik::get('session/sub_project_id');
$context_array['data_id']= isset($_GET['show_application']) ? $_GET['show_application'] : Atomik::get('session/data_id');
$context_array['remark_status_id']=isset($_GET['show_status']) ? $_GET['show_status'] :(Atomik::has('session/remark_status_id')?Atomik::get('session/remark_status_id'):"");
$context_array['user_id']= isset($_GET['show_poster']) ? $_GET['show_poster'] : (Atomik::has('session/user_id')?Atomik::get('session/user_id'):"");
$context_array['category_id']=isset($_GET['show_category']) ? $_GET['show_category'] : (Atomik::has('session/category_id')?Atomik::get('session/category_id'):"");	
$context_array['baseline_id']=isset($_GET['show_baseline']) ? $_GET['show_baseline'] : (Atomik::has('session/baseline_id')?Atomik::get('session/baseline_id'):"");	
$context_array['remarks_search'] = isset($_GET['search']) ? $_GET['search'] : (Atomik::has('session/search')?Atomik::get('session/search'):"");

$ignore_outdated = true;
$filter_param = "";

$remark = new Remark(&$context_array);
$project = new Project(&$context_array);

$remark->prepare();
$list_remarks = $remark->execute($debut,$limite);

$line_counter = 0;
$header_fields = array("Id"=>2,"Date"=>2,"Poster"=>2, "Data"=>2,"Description"=>8,"Category"=>2, "Status"=>3,"Action ID"=>2 );
$where = Tool::setFilter("project",$context_array['project_id']);
$sql = "SELECT id,project,Description as description FROM actions WHERE criticality != 14 {$where}";
$result_action = A("db:".$sql);
$today_date = date("Y")."-".date("m")."-".date("d");
