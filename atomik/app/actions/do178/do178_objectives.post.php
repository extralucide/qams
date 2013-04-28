<?php
if (isset($_POST['dal'])){
	$dal = $_POST['dal'];
	Atomik::redirect('do178_objectives?dal='.$dal,false);
}
