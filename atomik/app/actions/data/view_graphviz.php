<?php
Atomik::disableLayout();
Atomik::setView("view_diagram");
Atomik::needed("Data.class");
$data_id = (isset($_GET['id']) ? $_GET['id'] : null);
$data = new Data;
$data->get($data_id);
$type = "png";
$file = "graph";
if($fp = fopen($file.'.dot', "r+")) {
	fputs($fp,"digraph G {\n");
	fputs($fp,"\t edge [color=black, arrowsize=1, arrowType=empty];\n");
	fputs($fp,"\t node [color=lightyellow2, style=filled];\n");
	/*
	if($result = mysql_query("SELECT * FROM parainnage")) {
		while($ligne = mysql_fetch_array($result)) {
			fputs("P" . $ligne['parrain_id'] . " -> F" . $ligne['filleul_id'] . ";");
			fputs("P" . $ligne['parrain_id'] . " [label=\"" . $ligne['parrain_nom'] . "\"];");
			fputs("F" . $ligne['filleul_id'] . " [label=\"" . $ligne['filleul_nom'] . "\"];");
		}
	}*/
$counter = 1;
$item = Data::concatDocName($data->reference,$data->version,$data->type);
$item_number = $counter;
$graph = 'A'.$counter++.'  [shape=box] [label="'.$item.'"];';
// echo $graph."<br/>";
$found_upper_data = Data::Get_List_Upper_Data($data->id,&$list_upper);
if ($found_upper_data){    
	/* find upper data */
	foreach ($list_upper as $parent_data) {
		$item = Data::concatDocName($parent_data['reference'],$parent_data['version'],$parent_data['type']);
		$graph .= 'A'.$counter.'  [shape=box] [label="'.$item.'"];';
		$graph .= 'A'.$counter++.' -> A'.$item_number.';';
	}
}

$downstream_data_list = Data::Get_List_Downstream_Data($data->id);
if ($downstream_data_list !== false){
	foreach ($downstream_data_list as $id) :
		$data->get($id);
		$item = Data::concatDocName($data->reference,$data->version,$data->type);
		$graph .= 'A'.$counter.'  [shape=box] [label="'.$item.'"];';
		$graph .= 'A'.$item_number.' -> A'.$counter++.';';
	endforeach;
}
// echo $graph."<br/>";		
$txt  = <<<____TXT
	A1 -> A3;
	A2 -> A3;
	A3 -> A5;
	A3 -> A6;
	A1  [shape=box] [label="Hugo"];
	A2 [label="Boris"];
	A3 [label="Cécile"];
	A4 [label="François"];
	A5 [label="Frank"];
	A6 [label="Elise"];
____TXT;
	fputs($fp,$graph);	
	fputs($fp,"\n}");	
	fclose($fp);
	$cmd = "C:\\GraphViz\\App\\bin\\dot -T$type $file.dot -Gcharset=latin1 > $file.$type";
	// echo $cmd;
	exec($cmd,$retval,$code);
	$res = "";
	foreach($retval as $row){
		$res .= $row."<br/>";
	}
	// echo $res;		
	// exit();
	$diagram_img = "<img src='../$file.$type' >";
}
