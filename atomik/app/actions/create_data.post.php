<?php
$postArray = &$_POST ;

$rule = array(
        'project'  => array('required' => true),
        'lru'  => array('required' => true),
        'type'  => array('required' => true)
);

/* on nepeut pas utiliser cette fonction filter car elle supprime les balises html */
if (($data = Atomik::filter($_POST, $rule)) === false) {
    Atomik::flash(A('app/filters/messages'), 'error');
    return;
}
/* 
 * Insert data in req_data table 
 */
$data['date_published'] = date('Y-m-d h:i:s'); 
$new_data_id = Atomik_Db::insert('bug_applications', $data);
if ($new_data_id) {
    
    /* 
     * Create table for requirements 
     */
    /* get id of the data to concatenate with table name */
    $sql_query = "CREATE TABLE `olivier_appere`.`table_req_".$new_data_id."` (
                      `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                      `text` TEXT NOT NULL ,
                      `derived` BOOL NOT NULL ,
                      `safety` BOOL NOT NULL ,
                      `rationale` TEXT NOT NULL ,
                      `allocation` INT NOT NULL ,
                      `status` INT NOT NULL ,
                      `validation` INT NOT NULL ) ENGINE = MYISAM ;";
    Atomik_Db::exec($sql_query);
    $sql_table_traca_query = "CREATE TABLE `olivier_appere`.`table_traca_req_".$data_id."` (`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,".
    "`req_id` INT NOT NULL ,".
    "`upper_table_id` INT NOT NULL ,".
    "`upper_req_id` INT NOT NULL) ENGINE = MYISAM ;";
    Atomik_Db::exec($sql_table_traca_query);
    Atomik::flash('data table successfully created!', 'success');                      
}
else {
    Atomik::flash('data table not created!', 'failed');
}    
Atomik::redirect('mngt_data');
Atomik::end();
