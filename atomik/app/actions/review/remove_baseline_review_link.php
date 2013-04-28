<?php
Atomik::needed('Baseline.class');
$link_id = $_GET['link_id'];
$review_id = $_GET['review_id'];
Baseline::delete_baseline_review_link ($link_id);
Atomik::redirect('post_review?tab=baseline&id='.$review_id );