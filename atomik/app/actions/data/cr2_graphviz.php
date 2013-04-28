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
	// subgraph cluster_1 {
		// node [style=filled];
		A1 -> A2[label="analysed"][color="blue"][fontcolor="blue"];
		A2 -> A3[label="reviewed"][color="blue"][fontcolor="blue"];
		A3 -> A4[label="modified"][color="blue"][fontcolor="blue"];
		A4 -> A5[label="verified"][color="blue"][fontcolor="blue"];		
		// type="rounded";
		// label = "";
		// color=grey
	// }
	A5 -> A8[label="incomplete modification"][color="coral"][fontcolor="coral"];	
	A5 -> A4[label="Incomplete verification"][color="coral"][fontcolor="coral"];
	A2 -> A1[label="Incomplete analysis"][color="coral"][fontcolor="coral"];
	A2 -> A7[label="postpone"];
	A8 -> A3[label="reviewed"][color="green"][fontcolor="green"];
	A7 -> A3[label="reviewed"][color="green"][fontcolor="green"];		
	A3 -> A8[label="analysis KO"][color="coral"][fontcolor="coral"];
	A4 -> A8[label="verif KO"][color="coral"][fontcolor="coral"];
	
	Start -> A1[label="submit"][color="blue"];
	A5 -> A6[label="close"][color="blue"][fontcolor="blue"];
	A2 -> A9[label="reject"][color="red"][fontcolor="red"];	
	A7 -> A9[label="reject"][color="red"][fontcolor="red"];
	A5 -> A10[label="reject"][color="red"][fontcolor="red"];
		ranksep=1;  size  =  "15,12";
	Start [shape=point];
	{	rank=same;
		A1 [shape=box] [label="In analysis"];
		A21 [shape=box] [label="Entered"];
	}
	{	rank=same;
		A2 [shape=diamond,style=filled,color=lightgrey][label="In review"];
		A7 [shape=diamond,style=filled,color=lightgrey][label="Postponed"];
	}
	{	rank=same;	
		A3 [label="Under modification"];
		A8 [shape=diamond,style=filled,color=lightgrey][label=<<table><tr><td>Complementary</td></tr><tr><td>analysis</td></tr></table>>];
		A9 [shape=Msquare] [label="Rejected"];
	}
	{	rank=same;	
		A4 [label="Under verification"];
	}
	A5 [shape=diamond,style=filled,color=lightgrey][label="Fixed"];
	{	rank=same;
		A6 [shape=Msquare] [label="Closed"];
		A10 [shape=Msquare] [label="Cancel"];
		A26 [shape=Msquare] [label="Closed"];
		A29 [shape=Msquare] [label="Rejected"];			
	}
	
	Start2 [shape=point];
	{	rank=same;
		A22 [shape=diamond,style=filled,color=lightgrey][label="In progress"];
		A25 [shape=diamond,style=filled,color=lightgrey][label="Workaround"];
	}
	
	A21 -> A22[label="implement"][color="blue"];
	A22 -> A26[label="verif OK"][color="blue"];	
	A22 -> A25[label="verif KO"];
	A25 -> A29[label="reject"];
	A22 -> A29[label="reject"];
	A25 -> A26[label="derogation"];
	A25 -> A22[label="implement"][color="blue"];
	Start2 -> A21[label="submit"][color="blue"];
	A5 -> A26 [constraint=false][arrowhead =empty,arrowtail =empty,style=dotted]];
	A6 -> A26 [constraint=false][arrowhead =empty,arrowtail =empty,style=dotted]];
	A1 -> A22 [constraint=false][arrowhead =empty,style=dotted]];
	A9 -> A29 [constraint=false][arrowhead =empty,style=dotted]];
	A10 -> A29 [constraint=false][arrowhead =empty,style=dotted]];
	A4 -> A25 [constraint=false][arrowhead =empty,style=dotted]];
	A21 -> Start [constraint=false][arrowhead =empty,style=dotted]];
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
