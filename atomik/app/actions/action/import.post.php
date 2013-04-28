<?php
if (isset($_POST['submit_cancel'])){
	/* Cancel  */
	Atomik::redirect('post_review',false);
	echo "TEST";
	exit();
}

