<?php
Atomik::needed('Date.class');
Atomik::needed('User.class');

/*
if (!Atomik::has('request/id')) {
	Atomik::flash('Missing article ID parameter', 'error');
	Atomik::redirect('index');
}
*/

if (Atomik::has('request/id')) {
	Atomik::setView("view");
	$row = Atomik_Db::find('spip_articles', array('id_article' => A('request/id')));
	if ($row === false){
		Atomik::flash('Article not found.','failed');
		Atomik::redirect('wiki',false);
	}
	$html ='<a href="#" onclick="send_wiki('.A('request/id') .')"><img alt="Send article" title="Send article" width="32" height="32" border="0" src="assets/images/32x32/mail_send.png"></a><br/>';
	$html .='<a href="'.Atomik::url("wiki",false).'" ><img src="'.Atomik::asset('assets/images/pages/sommaire.png').'" border="0" alt="Back" title="Back"><h2>Back</h2></a>';
	Atomik::set('title',"");
	Atomik::set('css_title',"wiki");
	Atomik::set('css_reset',"no_show");
	Atomik::set('url',"wiki");
	Atomik::set('css_add',"no_show");
	Atomik::set('css_page_previous','no_show');	
	Atomik::set('css_page_next','no_show');	
	Atomik::set('css_page',"no_show");
	Atomik::set('select_menu',$html);	
}
else{
	Atomik::redirect('home');
}
if (!User::isUserLogged()){
	/* Atomik::flash("You are not logged in. Limited access is granted.","warning"); */
	/* No user logged */
	Atomik::disableLayout();
	Atomik::noRender();
	$vars['row'] = $row;
	$view_output = Atomik::render("view",$vars);
	$content = Atomik::renderLayout("_layout_anonymous",$view_output);
	echo $content;
}
