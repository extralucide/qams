<?php
$postArray = &$_POST ;
if(isset($_POST['submit_cancel'])){
	Atomik::redirect('wiki');	
}
$rule = array(
        'titre'  => array('required' => true),
        'texte'  => array('required' => true),
        'chapo'  => array('required' => true), /* pour l'auteur --temporaire */
        'update' => array('required' => true),
        'update_id' => array('required' => true),
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
    }
    $data['date_modif'] = date('Y-m-d h:i:s');	
    $update_result = Atomik_Db::update('spip_articles', $data,array('id_article'=>$update_id));
    if ($update_result)
        Atomik::flash('Post successfully updated!', 'success');
    else
        Atomik::flash('Post not updated!', 'failed');
}
else {
    foreach ( $postArray as $sForm => $value ) {
        if ( get_magic_quotes_gpc() )
            $data[$sForm] = stripslashes( $value );
        else
            $data[$sForm] = $value;
    }
    $data['date_redac'] = date('Y-m-d h:i:s');
    $data['date_modif'] = date('Y-m-d h:i:s');
    $insert_result = Atomik_Db::insert('spip_articles', $data);
    if ($insert_result)
        Atomik::flash('Post successfully added!', 'success');
    else
        Atomik::flash('Post not added!', 'failed');
}
Atomik::redirect('wiki');
