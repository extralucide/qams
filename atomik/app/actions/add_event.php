<?php
if(isset($_REQUEST['bug_cookie'])) {
    $bug_cookie = $_REQUEST['bug_cookie'];
    /* If user is logged in, get all user information */
    if(isset($bug_cookie)) {
        $array=unserialize(stripslashes($bug_cookie));
        $Id_User = $array[6];
    }
}
if(isset($_REQUEST['id'])) {
    $update_id=$_REQUEST['id'];
    unset ($_REQUEST['id']);
    $update="yes";
    $titre ="Update event";
    $button="Modify Event";
    $where='id = '.$update_id;
    $updated_posts = Atomik_Db::findAll('reviews',$where);
    if ($updated_posts){
        //foreach ($posts as $row_posts):
        $title = $updated_posts[0]['title'];
        $text_post = $updated_posts[0]['description'];
        $date_event = substr($updated_posts[0]['date'],0,10);
        //endforeach;
    }
    else
        $text_post='&lt;p&gt;This is some &lt;strong&gt;sample text&lt;/strong&gt;. You are using &lt;a href="http://ckeditor.com/"&gt;CKEditor&lt;/a&gt;.&lt;/p&gt;';
        $title ="";
}
else {
    $titre ="New event";
    $title ="";
    $date_event = "";
	  $text_post='&lt;p&gt;This is some &lt;strong&gt;sample text&lt;/strong&gt;. You are using &lt;a href="http://ckeditor.com/"&gt;CKEditor&lt;/a&gt;.&lt;/p&gt;';
    $button="Add Event";
    $update="no";
    $update_id="0";
}
