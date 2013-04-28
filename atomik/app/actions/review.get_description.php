<?php
        $description = "";
        $review_id   = Atomik::get('review_id');
        //$review      = Atomik_Db::findAll('reviews');
		$review      = Atomik_Db::findAll(array('reviews','review_type','projects','lrus'),
										  array('review_type.id = reviews.type',
										        'reviews.id = '.$review_id,
												'projects.id = reviews.project',
												'lrus.id = reviews.lru'),
												null,
												null,
										   array('projects.project','lrus.lru','reviews.managed_by','review_type.type'));
        //$review_type = Atomik_Db::findAll('review_type','','type');
        foreach ($review as $row_review):
          //if  ($row_review['id'] == $review_id) {
            $description=$row_review['project']." ".$row_review['lru']." ".$row_review['managed_by']." ".$row_review['type'];
            //foreach ($review_type as $row_review_type):
            //    if  ($row_review_type['id'] == $row_review['type'])$description.=" ".$row_review_type['type'];
            //endforeach;
          //}
        endforeach;
		Atomik::set('descr', $description);
        
