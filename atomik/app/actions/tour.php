<?php
Atomik::needed('User.class');
//Atomik::noRender(); 
//Atomik::disableLayout();
$html="";
$html ='<div class="subnav">';
$html.='<ul class="li_below">'; 
$html.='	<li class="noarrow"><h2><a href="">Action Items</a></h2></li>';
$html.='	<li class="noarrow"><h2><a href="">Peer Review Records</a></h2></li>';
$html.='	<li class="noarrow"><h2><a href="">Phase Reviews</a></h2></li>';
$html.='</ul>';
$html.='</div>';
Atomik::set('title',"QAMS Tour");
Atomik::set('css_title',"aircraft");
Atomik::set('css_reset',"no_show");
Atomik::set('css_add',"no_show");
Atomik::set('css_page_previous','no_show');	
Atomik::set('css_page_next','no_show');	
Atomik::set('css_page',"no_show");
Atomik::set('select_menu',$html);

if (!User::isUserLogged()){
	/* Atomik::flash("You are not logged in. Limited access is granted.","warning"); */
	/* No user logged */
	Atomik::disableLayout();
	Atomik::noRender();
	$view_output = Atomik::render("tour");
	$content = Atomik::renderLayout("_layout_anonymous",$view_output);
	echo $content;
}
