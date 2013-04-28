<?php
Atomik::needed('Db.class');
Atomik::needed('Baseline.class');
$review_id = $_POST['review_id'];
$baseline_id = $_POST['show_baseline'];
Baseline::update_baseline_review ($review_id,
									$baseline_id);
Atomik::redirect('../post_review?tab=baseline&id='.$review_id,false);
