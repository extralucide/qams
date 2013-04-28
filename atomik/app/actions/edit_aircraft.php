<?php
Atomik::needed('Aircraft.class');
Atomik::needed('Company.class');

$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;  
$limite = isset($_REQUEST['limite']) ? $_REQUEST['limite'] : 8;
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : "";
$company_id = Atomik::get('session/company_id');
$aircraft = new Aircraft();
if ($id != "") {
   Atomik::set('title',"Update aircraft");
   $button = "Modify";
   $aircraft->get($id);
}
else {
	Atomik::set('title',"New aircraft");
	$button = "Add";
}
$html="";
Atomik::set('url_add',Atomik::url('add_aircraft_picture',array('id' => $id)));
Atomik::set('title_add',"Update picture");
Atomik::set('css_reset',"no_show");
Atomik::set('css_title',"user");
Atomik::set('css_add',"no_show");
Atomik::set('css_page',"no_show");
Atomik::set('select_menu',$html);
