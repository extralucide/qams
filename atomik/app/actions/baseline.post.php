<?php
$postArray = &$_POST;

if (!isset($_POST['submit_baseline'])){
	Atomik::redirect('baseline?show_project='.$_POST['show_project'].'&show_lru='.$_POST['show_lru']);
}	
$rule = array(
	'project' => array('required' => false),
	'show_lru' => array('required' => false),
    'description' => array('default' => true),
);

/* on ne peut pas utiliser cette fonction filter car elle supprime les balises html */
if (($data = Atomik::filter($_POST, $rule)) === false) {
	Atomik::flash(A('app/filters/messages'), 'failed');
	return;
}

foreach ( $postArray as $sForm => $value )
{
	if ( get_magic_quotes_gpc() )
		$data[$sForm] = stripslashes( $value );
	else
		$data[$sForm] = $value;
    //echo "field:".$sForm."=>".$value."<br />";
}
$row['description'] 		= $data['description'];
$data_project['project_id'] = $data['project'];
$data_project['lru_id'] 	= $data['show_lru'];
if ($postArray['baseline_id']!=""){
	$result=Atomik_Db::update('baselines', $row,array('id'=>$postArray['baseline_id']));	
	$result = Atomik_Db::update('baseline_join_project', $data_project,array('baseline_id'=>$postArray['baseline_id']));
	if ($result)
		Atomik::flash('Baseline successfully modified.', 'success');
	else
		Atomik::flash('Baseline modification failed.', 'failed');	
}
else{
	$insert_baseline_id = Atomik_Db::insert('baselines', $row);
	if ($insert_baseline_id) {
		Atomik::flash('Baseline successfully added!', 'success');
		$data_project['baseline_id'] = $insert_baseline_id;
		$insert_result = Atomik_Db::insert('baseline_join_project', $data_project);
		if ($insert_result)
			Atomik::flash('Baseline to project link successfully added!', 'success');
		else
			Atomik::flash('Baseline to project link not added!', 'failed');
	}
	else
		Atomik::flash('Baseline not added!', 'failed');
}	
Atomik::redirect('baseline');
