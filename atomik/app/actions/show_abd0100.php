<?php
if (!(isset($_POST['search']))) {
	$search="";
    $show_search = "";
}
else {
  /* search .... */
  $search=$_POST['search'];
  $show_search = " ((subject LIKE '%$search%') OR (description LIKE '%$search%')) ";
}
$table = Atomik_Db::findAll('abd0100',$show_search,'subject');
/* result of the sql query to get field of the header of the table */
$sql_query = A('db:show columns from abd0100 ');
/* get the array related to the sql query */
$column=$sql_query->fetchall();
// $id		= array (0 => "id");
// $do_254_section		= array (0 => "do_254_section");
// $paragraph		= array (0 => "paragraph");
// $description	= array (0 => "description");
// $column		= array (0 => $id,1 => $do_254_section, 2 => $paragraph,3 => $description);
