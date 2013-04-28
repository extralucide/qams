<?php
Atomik::disableLayout();
Atomik::setView("view_diagram");
Atomik::needed("Data.class");
$data_id = (isset($_GET['id']) ? $_GET['id'] : null);
$data = new Data;
$data->get($data_id);
$type = "png";
$file = "excr_graph";
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
		A1 -> A2[label="implement"][color="blue"];
		A2 -> A6[label="verif OK"][color="blue"];		
		type="rounded";
		label = "";
		color=grey
	}	
	A2 -> A5[label="verif KO"];
	A5 -> A9[label="reject"];
	A2 -> A9[label="reject"];
	A5 -> A6[label="derogation"];
	Start -> A1[label="submit"][color="blue"];

	Start [shape=point];
	A1 [shape=box] [label="Entered"];
	A2 [shape=diamond,style=filled,color=lightgrey][label="In progress"];
	A5 [shape=diamond,style=filled,color=lightgrey][label="Workaround"];
	A6 [shape=Msquare] [label="Closed"];
	A9 [shape=Msquare] [label="Rejected"];
	
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
