<?php
Atomik::needed("User.class");
Atomik::needed("Date.class");
Atomik::set('tab_select','misc');

$posts = Atomik_Db::findAll('spip_articles',null,'date_modif DESC');

$html ="<div class='subnav'><ul >";
$html .="<h2>Articles</h2>";
foreach ($posts as $row):
	$html .='<li class="noarrow"><a href="'.Atomik::url('view', array('id' => $row['id_article'])).'"><span class="small-grey-font_">'.Date::convert_date($row['date_modif']).'</span><br/>'.$row['titre'].'</a></li>';
endforeach;
$html .="</ul></div>";

Atomik::set('title',"Wiki");
Atomik::set('css_title',"wiki");
Atomik::set('css_reset',"no_show");
Atomik::set('url',"add");
Atomik::set('url_add',Atomik::url('add',false));
Atomik::set('title_add',"Add a new article");
Atomik::set('css_add',"no_show_");
Atomik::set('css_page_previous','no_show');	
Atomik::set('css_page_next','no_show');	
Atomik::set('css_page',"no_show");
Atomik::set('select_menu',$html);
