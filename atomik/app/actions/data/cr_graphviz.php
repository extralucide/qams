<?php
Atomik::disableLayout();
Atomik::setView("view_diagram");
Atomik::needed("Data.class");
$data_id = (isset($_GET['id']) ? $_GET['id'] : null);
$data = new Data;
$data->get($data_id);
$type = "png";
$file = "cr_graph";
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
		A1 -> A2[label="analysed"][color="blue"];
		A2 -> A3[label="reviewed"][color="blue"];
		A3 -> A4[label="modified"][color="blue"];
		A4 -> A5[label="verified"][color="blue"];		
		type="rounded";
		label = "";
		color=grey
	}
	A5 -> A8[label="incomplete modification"][color="coral"][fontcolor="coral"];	
	A5 -> A4[label="Incomplete verification"][color="coral"][fontcolor="coral"];
	A2 -> A1[label="Incomplete analysis"][color="coral"][fontcolor="coral"];
	A2 -> A7[label="postpone"];
	A8 -> A3[label="reviewed"];
	A7 -> A3[label="reviewed"];		
	A3 -> A8[label="analysis KO"][color="coral"][fontcolor="coral"];
	A4 -> A8[label="verif KO"][color="coral"][fontcolor="coral"];
	
	Start -> A1[label="submit"][color="blue"];
	A5 -> A6[label="close"][color="blue"];
	A2 -> A9[label="reject"];	
	A7 -> A9[label="reject"];
	A5 -> A10[label="reject"];
	Start [shape=point];
	A1 [shape=box] [label="In analysis"];
	A2 [shape=diamond,style=filled,color=lightgrey][label="In review"];
	A5 [shape=diamond,style=filled,color=lightgrey][label="Fixed"];
	A7 [shape=diamond,style=filled,color=lightgrey][label="Postponed"];
	A8 [shape=diamond,style=filled,color=lightgrey][label="Complementary analysis"];	
	A3 [label="Under modification"];
	A4 [label="Under verification"];
	A6 [shape=Msquare] [label="Closed"];
	A9 [shape=Msquare] [label="Rejected"];
	A10 [shape=Msquare] [label="Cancel"];
	
	graph [
	rankdir = "TP";
	ratio = "compress";
	splines="curved";
	];
	
____TXT;
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
