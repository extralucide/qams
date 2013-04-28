<?php
Atomik::needed('User.class');
Atomik::needed('Db.class');
$clientOS = Db::setOS();
$systemOS = Db::setServerOS();
if (!User::getAdminUserLogged()){
	Atomik::noRender();
	echo "<div style='min-height:600px;'>";
	echo "You do not have admin rights to perform DB administration.";
	echo "</div>";	
}
Atomik::set('tab_select','admin');
if ((isset($_GET['backup_result'])) && ($_GET['backup_result']=="ok")) {
	Atomik::flash('Database saved!', 'success');
	unset($_GET['backup_result']);
}
else {

}
$html="";
$html ='<div class="subnav">';
$html.='<ul class="li_below">'; 
$html.='	<li class="noarrow"><h2><a href="'.Atomik::url('list_company').'">Companies</a></h2></li>';
$html.='	<li class="noarrow"><h2><a href="'.Atomik::url('list_aircraft').'">Aircrafts</a></h2></li>';
$html.='	<li class="noarrow"><h2><a href="'.Atomik::url('list_project').'">Systems</a></h2></li>';
$html.='	<li class="noarrow"><h2><a href="'.Atomik::url('list_eqpt').'">Equipments</a></h2></li>	';
$html.='	<li class="noarrow"><h2><a href="'.Atomik::url('users').'">Users</a></h2></li>';
$html.='	<li class="noarrow"><h2><a href="'.Atomik::url('list_category').'">Categories</a></h2></li>';
$html.='	<li class="noarrow"><h2><a href="'.Atomik::url('list_severity').'">Severities</a></h2></li>';
$html.='	<li class="noarrow"><h2><a href="'.Atomik::url('status_type').'">Status</a></h2></li>';
$html.='	<li class="noarrow"><h2><a href="'.Atomik::url('data_type').'">Document Types</a></h2></li>';
$html.='	<li class="noarrow"><h2><a href="'.Atomik::url('review_type').'">Review Types</a></h2></li>';
$html.='</ul>';
$html.='</div>';
Atomik::set('select_menu',$html);
Atomik::set('title',"Administration");
Atomik::set('css_title',"db");
Atomik::set('css_reset',"no_show");
Atomik::set('css_add',"no_show");
Atomik::set('css_page',"no_show");
if (Atomik::has('session/see_all_data')){
	$all_data = Atomik::get('session/see_all_data');
	if ($all_data == "yes"){
		$all_data_yes = 'CHECKED';
		$all_data_no = '';
	}
	else{
		$all_data_yes = '';
		$all_data_no = 'CHECKED';	
	}
}
else{
	$all_data_yes = 'CHECKED';
	$all_data_no = '';
}

/* log 	*/
$log_txt = "";
$filename = dirname(__FILE__).DIRECTORY_SEPARATOR.
			"..".DIRECTORY_SEPARATOR.
			"..".DIRECTORY_SEPARATOR.				
			"..".DIRECTORY_SEPARATOR.A('db_config/log');
if (!file_exists($filename))	{
	/* create file */
	$fhandle = fopen($filename, 'w+'); 
}
else {
	$fhandle = fopen($filename, 'r'); 
}		
if($fhandle !== false){
	while (($log = fgets($fhandle, 4096)) !== false) {
		$log_txt.= $log."<br/>";
	}	
	fclose($fhandle);
}
