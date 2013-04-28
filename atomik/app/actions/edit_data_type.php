<?php
Atomik::needed('Data.class');
$id = isset($_GET['id']) ? $_GET['id'] : "";
$data = new Data;
$type = Data::getType($id);
Atomik::set('title_add',"Update picture");
Atomik::set('css_reset',"no_show");
Atomik::set('css_title',"data");
Atomik::set('css_add',"no_show");
Atomik::set('css_page',"no_show");
