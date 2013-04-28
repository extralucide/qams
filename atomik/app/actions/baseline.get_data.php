<?php
//        $description = "";
//        $data       = Atomik_Db::findAll('bug_applications',"application = 'DQ/OA/N10-6925'");
//        $today_date  = date('Y-m-d');
//        foreach ($data as $row):
//          $data_array[] = array ($row['application'],$row['version'],$row['type'],$row['description'],$row['status']);
//        endforeach;
		//Atomik::set('data', $data_array);
/*
 * Data
 */
$description = "";
$baseline_id   = Atomik::get('baseline_id');
//echo $baseline_id ;
//$table[] = array('bug_applications','baseline_join_data','projects','lrus');
$fields ="bug_applications.id as id, ".
         "projects.project as project_name, ".
         "bug_applications.application, ".
         "bug_applications.description, ".
         "bug_applications.version, ".
         "lrus.lru, ".
         "bug_applications.type, ".
         "bug_applications.status, ".
         "bug_applications.location, ".
         "bug_applications.date_published, ".
         "baseline_join_data.baseline_id as baseline ";
//$where[] = array("baseline_join_data.baseline_id = {$baseline_id}",
//                 "bug_applications.id            = data_id",
//                 "bug_applications.project       = projects.id",
//                 "bug_applications.lru           = lrus.id");

//$data = new Atomik_Db_Query();
//$data->select()->from('bug_applications');
//$data->select('bug_applications.id as id')->from('bug_applications')->from('baseline_join_data')->where('baseline_join_data.baseline_id = {$baseline_id}')->where('bug_applications.id = data_id');

//$data       = Atomik_Db::findAll('bug_applications','bug_applications.id = data_id',"","",'bug_applications.application');
/*
$data       = Atomik_Db::findAll(
        array('bug_applications','baseline_join_data','projects','lrus','data_cycle_type'),
        array("baseline_join_data.baseline_id = {$baseline_id}", 
              "bug_applications.id = data_id",
              "bug_applications.project = projects.id",
              "bug_applications.lru     = lrus.id",
              "bug_applications.type    = data_cycle_type.id"),
        "name",
        null,
        array("projects.project",
              "lrus.lru",
              "data_cycle_type.name",
              "data_cycle_type.description as type_description",
              "bug_applications.application",
              "bug_applications.description",
              "bug_applications.version",
			  "baseline_join_data.id"));
*/
//print_r($data);
$data = A("db:SELECT projects.project, lrus.lru, data_cycle_type.name, data_cycle_type.description as type_description, bug_applications.application, ".
              "bug_applications.description, ".
              "bug_applications.version, ".
			  "baseline_join_data.id ".
              "FROM bug_applications ".
              "LEFT OUTER JOIN baseline_join_data ON bug_applications.id = data_id ".
              "LEFT OUTER JOIN projects ON bug_applications.project = projects.id ".
              "LEFT OUTER JOIN lrus ON bug_applications.lru     = lrus.id ".
              "LEFT OUTER JOIN data_cycle_type ON bug_applications.type    = data_cycle_type.id ".
              " WHERE baseline_join_data.baseline_id = {$baseline_id} ORDER BY description ASC ");			  
if ($data) {
foreach ($data as $row):
    if ($row['description'] == "")
            $data_description = $row['type_description'];
        else
            $data_description = $row['description'];
    $description.= "<tr><td colspan='5'>".$row['project']." ".$row['lru']." ".$row['application']." ".$row['name']." issue ".$row['version'].": ".$data_description."</td>";
    $description.= "<td><a href='".Atomik::url('remove_baseline', array('id' => $row['id']))."' ><img style='padding-left:5px;padding-top:5px' border=0 width='20' height='20' src='assets/images/32x32/agt_action_fail.png' alt='remove link' title='remove link' /></a></td><tr>";

endforeach;
}
else{
    $description = "<tr><td colspan='6'>nothing found</td>";
}
Atomik::set('descr', $description);
        
