<?php

$postArray = &$_POST ;

$rule = array(
        'project_id'  => array('required' => true),
        'lru_id'  => array('required' => true),
        'synopsis'  => array('required' => true), /* pour l'auteur --temporaire */
        'description' => array('required' => true),
        'impact_analysis' => array('required' => true),
        'conclusion' => array('required' => true),
        'severity_id' => array('required' => true),
        'status_id' => array('required' => true)
);

/* on nepeut pas utiliser cette fonction filter car elle supprime les balises html */
if (($data_tmp = Atomik::filter($_POST, $rule)) === false) {
    Atomik::flash(A('app/filters/messages'), 'error');
    return;
}
$update=$postArray['update'];
$update_id=$postArray['update_id'];
unset($postArray['update'],$postArray['update_id']);
if ($update == "yes") {
    foreach ( $postArray as $sForm => $value ) {
        if ( get_magic_quotes_gpc() )
            $data[$sForm] = stripslashes( $value );
        else
            $data[$sForm] = $value;
        echo "field:".$sForm."=>".$value."<br />";
    }
    
    $where = "id = ".$update_id;
    echo $where;
    $update_result = Atomik_Db::update('sprs', $data, $where);
    if ($update_result)
        Atomik::flash('SPR successfully updated!', 'success');
    else
        Atomik::flash('SPR not updated!', 'failed');
}
else {
    foreach ( $postArray as $sForm => $value ) {
        if ( get_magic_quotes_gpc() )
            $data[$sForm] = stripslashes( $value );
        else
            $data[$sForm] = $value;
    }
    $data['maj'] = date('Y-m-d h:i:s');
    $insert_result = Atomik_Db::insert('sprs', $data);
    if ($insert_result)
        Atomik::flash('SPR successfully added!', 'success');
    else
        Atomik::flash('SPR not added!', 'failed');
}
//Atomik::redirect('show_spr');
