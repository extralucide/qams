<?php
Atomik::disableLayout();
Atomik::setView("view_diagram");
Atomik::needed("Data.class");
$data_id = (isset($_GET['id']) ? $_GET['id'] : null);
$data = new Data;
$data->get($data_id);
$type = "png";
$file = "review_graph";
if($fp = fopen($file.'.dot', "w+")) {
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

// echo $graph."<br/>";		
$txt  = <<<____TXT
	subgraph cluster_1 {
		node [style=filled];
		A1 -> A2 -> A3 -> A4 -> A5;		
		type="rounded";
		label = "";
		color=grey
	}	

	Start [shape=point];
	A1 [shape=box] [label="HPR"];
	A2 [shape=box][label="PDR"];
	A3 [shape=box][label="CDR"];
	A4 [shape=box] [label="FDR 1"];
	A5 [shape=box] [label="FDR 2"];
	
	graph [
	rankdir = "LR";
	ratio = "compress";
	splines="curved";
	];
	
____TXT;
	// echo $txt."<br/>";
	fputs($fp,$txt);	
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
