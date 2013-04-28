<?php
Atomik::needed('Tool.class');
Atomik::needed('Data.class');
$fill = false;
$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;  
$limite = isset($_REQUEST['limite']) ? $_REQUEST['limite'] : 8;
$order  = isset($_GET['order']) ? $_GET['order'] : "";
$list_data_type = Data::getTypeList($order);

/*
 *
 * DO-178B part
 *
*/
/* This write guidelines about life cycle data type */	
function write_data_chapters ($type_data) {
    $get_do_chapter = "SELECT do_178b_join_data_chap.paragraph,description FROM do_178b_chapters,do_178b_join_data_chap ".
            "WHERE do_178b_join_data_chap.life_cycle_data = ".$type_data." AND do_178b_join_data_chap.paragraph = do_178b_chapters.paragraph ORDER BY do_178b_chapters.paragraph ASC";
    $result = A("db:".$get_do_chapter);
    print '<div class="my_text" style="margin-left:10px;margin-right:10px;padding-left:10px;padding-right:10px">';
   foreach ($result as $text) {
        echo "DO-178B <b>section ".$text['paragraph']."</b>: ".$text['description'];
        echo "<br/><br/>";
    }
    print "</div>";
}
/*
 *
 * DO-254 part
 *
*/
/* This write guidelines about life cycle data type */	
function write_data_do254_chapters ($type_data) {
    $get_do_chapter = "SELECT do_254_join_data_chap.paragraph,description FROM do_254_chapters,do_254_join_data_chap ".
            "WHERE do_254_join_data_chap.life_cycle_data = ".$type_data." AND do_254_join_data_chap.paragraph = do_254_chapters.paragraph ORDER BY do_254_chapters.paragraph ASC";
    $result = A("db:".$get_do_chapter);
    print '<div class="my_text" style="margin-left:10px;margin-right:10px;padding-left:10px;padding-right:10px">';
    foreach ($result as $text) {
        echo "DO-254 <b>section ".$text['paragraph']."</b>: ".$text['description'];
        echo "<br/><br/>";
    }
    print "</div>";
}
/* This write information about people function */
function write_function_chapters ($type_data) {
    $get_do_chapter = "SELECT paragraph,description FROM do_178b_chapters,do_178b_join_role_chapter ".
            "WHERE do_178b_join_role_chapter.people_function_id = ".$type_data." AND do_178b_join_role_chapter.chapter_id = do_178b_chapters.paragraph ORDER BY do_178b_chapters.paragraph ASC";
    $result = A("db:".$get_do_chapter);
    print '<div class="my_text" style="margin-left:10px;margin-right:10px;padding-left:10px;padding-right:10px">';
    foreach ($result as $text) {
        echo "DO-178B <b>section ".$text['paragraph']."</b>: ".$text['description'];
        echo "<br/><br/>";
    }
    print "</div>";
}
/*
 *
 * ABD0100 part
 *
*/
function write_data_abd0100_chapters ($type_data) {
    $get_abd_chapter = "SELECT issue,part,chapitre,sous_chapitre,requirement,subject,description FROM abd0100,abd0100_join_data ".
            "WHERE abd0100_join_data.life_cycle_data_id = ".$type_data." AND abd0100_join_data.abd0100_chapter_id = abd0100.id ORDER BY abd0100.subject ASC";
    $result = A("db:".$get_abd_chapter);
    print '<div class="my_text" style="margin-left:10px;margin-right:10px;padding-left:10px;padding-right:10px">';
    foreach ($result as $text) {
        if ($text['requirement'] != 0)
            echo "ABD0100.".$text['part'].".".$text['chapitre']."-".$text['requirement']."-".$text['issue']."<b> ".$text['subject']."</b>: ".$text['description'];
        else
            echo "ABD0100.".$text['part'].".".$text['chapitre']."  ?".$text['sous_chapitre']."<b> ".$text['subject']."</b>: ".$text['description'];
        echo "<br/><br/>";
    }
    print "</div>";
}
$html ="";
/*
$html ="<form method='post' action='".Atomik::url('data_type')."'>";
$html.='<fieldset class="medium">';
$html.="<label for='add_type_name'>Acronym:</label>";
$html.="<input type=text name='add_type_name' size='10' /><br/>";
$html.="<label for='add_type_desc'>Description:</label>";
$html.="<input type='text' name='add_type_desc' size='30' /><br/>";
$html.="<span class='art-button-wrapper'>";
$html.="<span class='l'> </span>";
$html.="<span class='r'> </span>";
$html.="<input class='art-button' type='submit' value='Add Type'/>";
$html.="</span>";
$html.='</fieldset >';
$html.="</form>";
*/
Atomik::set('title',"Documents type");
Atomik::set('css_title',"data");
Atomik::set('css_reset',"no_show");
Atomik::set('url',"data_type");
Atomik::set('url_add',Atomik::url('edit_data_type'));
Atomik::set('title_add',"Add a type");
Atomik::set('page',$page);
Atomik::set('limite',$limite);
Atomik::set('css_page_previous','no_show');	
Atomik::set('css_page_next','no_show');	
Atomik::set('css_page',"no_show");
Atomik::set('select_menu',$html);
