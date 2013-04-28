<?php
include "../inc/Data.class.php";
include "../inc/Project.class.php";
if (isset($_POST['project'])){
	$show_project= $_POST['project'];
}
else {
  $show_project="";
}

if (isset($_POST['lru'])){
	$show_lru= $_POST['lru'];
}
else {
  $show_lru="";
}

if (isset($_POST['type'])){
	$show_type= $_POST['type'];
}
else {
  $show_type="";
}
$type = A("db:SELECT data_cycle_type.id,data_cycle_type.name,description FROM data_cycle_type LEFT OUTER JOIN group_type ON group_id = group_type.id WHERE group_type.name = 'Specification' ORDER BY `data_cycle_type`.`name` ASC ");

$projects = A("db:SELECT id,project FROM projects ORDER BY `projects`.`project` ASC");
if ($show_project != "")
  $where=" WHERE project = {$show_project} ";
else
  $where="";
$components = A("db:SELECT id,lru FROM lrus {$where} ORDER BY lru ASC");

