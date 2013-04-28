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
$table = Atomik_Db::findAll('soi_questions',$show_search,'question');
/* result of the sql query to get field of the header of the table */
$sql_query = A('db:show columns from soi_questions ');
/* get the array related to the sql query */
$column=$sql_query->fetchall();
$header_fields = array("SOI#", "section","Question");
//$id		= array (0 => "id");
$soi		= array (0 => "soi_id");
$paragraph		= array (0 => "item_id");
$question	= array (0 => "question");
$column		= array (0 => $soi,1 => $paragraph, 2 => $question);
