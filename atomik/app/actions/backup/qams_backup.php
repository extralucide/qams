<?php
// include "../includes/config.php";
// define('ATOMIK_AUTORUN', false);
// include "../atomik/index.php";
// include "../atomik/app/config.php";
// include "../atomik/app/includes/Project.class.php";
// include "../atomik/app/includes/User.class.php";
// include "../atomik/app/includes/Mail.class.php";
// include "../atomik/app/includes/Db.class.php";
Atomik::disableLayout();
Atomik::needed('Db.class');
$db = new Db();
/* Get database */
$db->db_backup();
/* Back-up application */
$db->qams_backup();
