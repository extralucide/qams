<?php
Atomik::needed("User.class");
$Id_User = User::getIdUserLogged();
Class Wiki{
	private $title;
	private $description;
	
	public function __construct(){
		$this->title = "";
		$this->description = "";
	}
	public function get($id){
		$post = Atomik_Db::find('spip_articles',array('id_article'=>$id));
		$this->title = $post['titre'];
		$this->description = $post['texte'];
	}
	public function getTitle(){
		return($this->title);
	}
	public function getDescription(){
		return($this->description);	
	}
}
$post = new Wiki();
if(isset($_REQUEST['id'])) {
    $update_id=$_REQUEST['id'];
    $update="yes";
    $title ="Update article";
    $button="Modify Article";
    $post->get($update_id);
    // $updated_posts = Atomik_Db::findAll('spip_articles',$where);
    // if ($updated_posts){
        // foreach ($posts as $row_posts):
        // $title = $updated_posts[0]['titre'];
        // $text_post = $updated_posts[0]['texte'];
        // endforeach;
    // }
    // else
        // $text_post='&lt;p&gt;This is some &lt;strong&gt;sample text&lt;/strong&gt;. You are using &lt;a href="http://ckeditor.com/"&gt;CKEditor&lt;/a&gt;.&lt;/p&gt;';
}
else {
    $title ="New article";
	// $text_post='&lt;p&gt;This is some &lt;strong&gt;sample text&lt;/strong&gt;. You are using &lt;a href="http://ckeditor.com/"&gt;CKEditor&lt;/a&gt;.&lt;/p&gt;';
    $button="Add Article";
    $update="no";
    $update_id="0";
}
Atomik::set('title',$title);
Atomik::set('css_title',"wiki");
Atomik::set('css_reset',"no_show");
Atomik::set('url',"add");
Atomik::set('css_add',"no_show");
Atomik::set('css_page_previous','no_show');	
Atomik::set('css_page_next','no_show');	
Atomik::set('css_page',"no_show");
