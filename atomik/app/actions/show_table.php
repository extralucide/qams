<?php
if (!(isset($_POST['search']))) {
	$search="";
    $show_search = "";
}
else {
  /* search .... */
  $search=$_POST['search'];
  $show_search = " ((paragraph LIKE '%$search%') OR (description LIKE '%$search%')) ";
}
$table = Atomik_Db::findAll('do_254_chapters',$show_search,'paragraph');
/* result of the sql query to get field of the header of the table */
$sql_query = A('db:show columns from do_254_chapters ');
/* get the array related to the sql query */
//$column=$sql_query->fetchall();
$id		= array (0 => "id");
$do_254_section		= array (0 => "do_254_section");
$paragraph		= array (0 => "paragraph");
$description	= array (0 => "description");
$column		= array (0 => $paragraph,1 => $do_254_section, 2 => $description);
