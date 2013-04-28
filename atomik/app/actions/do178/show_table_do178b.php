<?php
if (!(isset($_POST['search']))) {
	$search="";
    $show_search = "";
}
else {
  /* search .... */
  $search=$_POST['search'];
  $show_search = " ((paragraph LIKE '%$search%') OR (description LIKE '%$search%') OR (do_178b_section LIKE '%$search%')) ";
}
$selected = "";
$fill = 0;
$table = Atomik_Db::findAll('do_178b_chapters',$show_search,'paragraph');
/* result of the sql query to get field of the header of the table */
$sql_query = A('db:show columns from do_178b_chapters ');
/* get the array related to the sql query */
$column=$sql_query->fetchall();
$do_178b_section		= array (0 => "do_178b_section");
$paragraph		= array (0 => "paragraph");
$description	= array (0 => "description");
$column		= array (0 => $paragraph,1 => $do_178b_section, 2 => $description);
$section_list = A("db:SELECT id,section FROM do_178b_section ORDER BY paragraph_id ASC, sub_paragraph_id ASC");
/* menu section */
$html =  '<form method="POST" action="'.Atomik::url('do178/show_table_do178b', false).'">';
$html .= '<fieldset class="medium">';
$html .='<label for="show_project">Section:</label>';
$html .='<select class="combobox"';
$html .= 'onchange="this.form.submit()"';
$html .= ' name="select_section">';
$html .= '<option value=""/> --All--';
foreach($section_list as $row):
	$html .= '<option value="'.$row['id'].'"';
	if ($row['id'] == $selected){ 
		$html .= " SELECTED ";
	}
	$html .=">".$row['section'];
endforeach;
$html .='</select>';
$html .= '</fieldset >';
$html .= '</form>';
Atomik::set('select_menu',$html);
Atomik::set('title',"DO-178B Chapters");
Atomik::set('css_title',"action");
Atomik::set('css_add',"no_show");
Atomik::set('css_reset',"no_show");
Atomik::set('css_page',"no_show");
