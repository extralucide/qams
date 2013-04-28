<?php
        $description = "";
        $review_id   = Atomik::get('review_id');
        $review      = Atomik_Db::findAll('reviews');
        $review_type = Atomik_Db::findAll('review_type','','type');
        $today_date  = date('Y-m-d');
        foreach ($review as $row_review):
          if  ($row_review['id'] == $review_id) {
            //$description=$row_review['managed_by'];
            foreach ($review_type as $row_review_type):
                if  ($row_review_type['id'] == $row_review['type']){
                    $description = $row_review_type['type'];
                }
            endforeach;
            /* Convert date to display nicely */
            require_once("../includes/calendrier/calendrier.php");
            $cut_date = substr($row_review['date'],0,10);
            $date=PrettyDate ($cut_date);
            /* check if the review is in the past or in the future */
            if ($cut_date < $today_date)
                $description = "Baseline for the review ".$description." led by ".$row_review['managed_by']." and performed on ".$date;
            else
                $description = "Baseline for the review ".$description." led by ".$row_review['managed_by']." and planned on ".$date;
            break;
          }
        endforeach;
		Atomik::set('descr', $description);
        
