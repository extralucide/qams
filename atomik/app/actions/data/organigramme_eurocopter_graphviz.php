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
	fputs($fp,"\t edge [color=black, arrowsize=1, arrowType=empty,splines=ortho];\n");
	fputs($fp,"\t node [color=grey];\n");
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
	ranksep=1;  size  =  "12,12";
	// subgraph cluster_1 {
		// node [style=filled];
		A1 -> A2;
		A2 -> A3;		
		A3 -> A8 [constraint=false][arrowhead =none,style=dotted]];
		// label = "ECE";
		// style=rounded;
		// color=grey
	// }	
	// subgraph cluster_2 {
		// node [style=filled];
		A3 -> A4;
		A4 -> A5;
		A4 -> A7;
		A4 -> A9 [constraint=false][arrowhead =none,style=dotted]];		
		// label = "See4Sys";
		// style=rounded;
		// color=grey;
	// }
	
	{  rank  =  same;
		A1 [shape=box,label=<<TABLE BORDER="0" BGCOLOR="white"><TR><TD ROWSPAN="2"><IMG SRC="assets/images/zodiac.jpg"/></TD><TD>Mezut Ozdemir</TD></TR><TR><TD>Système Coeur</TD></TR></TABLE>>,image="assets/images/small_zodiacaerospace.jpg"];
	}
	{  rank  =  same;
		A2 [shape=box][label=<<TABLE BORDER="0" BGCOLOR="white"><TR><TD ROWSPAN="2"><IMG SRC="assets/images/zodiac.jpg"/></TD><TD>Edouard Gausach</TD></TR><TR><TD>Protection Logic Board</TD></TR></TABLE>>];
	}	
	{	rank  =  same; 
		A3 [shape=box][label=<<TABLE BORDER="0" BGCOLOR="white"><TR><TD ROWSPAN="2"><IMG SRC="assets/images/zodiac.jpg"/></TD><TD>Philippe Sajous</TD></TR><TR><TD>FPGA FUNC/BITE</TD></TR></TABLE>>];
		A8 [shape=box][label=<<TABLE BORDER="0" BGCOLOR="white"><TR><TD ROWSPAN="2"><IMG SRC="assets/images/zodiac.jpg"/></TD><TD>Olivier Appéré</TD></TR><TR><TD>Process Assurance</TD></TR></TABLE>>];
	}
	{	rank  =  same; 
		A4 [shape=box][label=<<TABLE BORDER="0" BGCOLOR="white"><TR><TD ROWSPAN="2"><IMG SRC="assets/images/see4sys_lite_site.png"/></TD><TD>Guillaume Zin</TD></TR><TR><TD>Coordination SEE4SYS (chef de projet)</TD></TR></TABLE>>];
		A9 [shape=box][label=<<TABLE BORDER="0" BGCOLOR="white"><TR><TD ROWSPAN="2"><IMG SRC="assets/images/see4sys_lite_site.png"/></TD><TD>Alain Du Colombier</TD></TR><TR><TD>Process Assurance</TD></TR></TABLE>>];
	}
	{	rank  =  same; 
		A5 [shape=box][label=<<TABLE BORDER="0" BGCOLOR="white"><TR><TD ROWSPAN="2"><IMG SRC="assets/images/see4sys_lite_site.png"/></TD><TD>Guillaume Zin</TD><TD>Thomas Carré</TD></TR><TR><TD COLSPAN="2">Design (BITE) Vérification (FUNC)</TD></TR></TABLE>>];
		A7 [shape=box][label=<<TABLE BORDER="0" BGCOLOR="white"><TR><TD ROWSPAN="2"><IMG SRC="assets/images/see4sys_lite_site.png"/></TD><TD>Clément Cabail</TD></TR><TR><TD>Design (FUNC) / Vérification (BITE)</TD></TR></TABLE>>];
	}	
	graph [
	rankdir = "TB";
	ratio = "compress";
	splines="ortho";
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
