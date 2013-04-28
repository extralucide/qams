<?php

if (!Atomik::has('request/id')) {
	Atomik::flash('Missing id parameter', 'error');
	Atomik::redirect('index');
}
$today_date = date('Y-m-d');
$events = Atomik_Db::find('reviews', array('id' => A('request/id')));
$users = Atomik_Db::findAll('bug_users','','lname');
$poster="";
