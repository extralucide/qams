<?php
if (isset($_POST['submit_cancel'])){
	/* Cancel  */
	Atomik::redirect('edit_data',false);
	echo "TEST";
	exit();
}
if (isset($_POST['import_prr_w_response'])){
	/* Import remarks */
	Atomik::redirect('import_prr?import=answer');
}
