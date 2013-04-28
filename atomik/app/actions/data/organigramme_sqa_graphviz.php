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
	
$txt  = <<<____TXT
	ranksep=1;  size  =  "10,10";

	//subgraph cluster_1 {
		// node [style=filled];
	/* Links */		
		PQA -> QVP[label="Report"][color="blue"][fontcolor="blue"][constraint=false];
		PQA -> SQAP[label="Approve"][color="green"][fontcolor="green"];
		SYS -> SQA[label="System baseline"][color="coral"][fontcolor="coral"];
		SQA -> PQA[label="Noncompliance issues alert"][color="blue"][fontcolor="blue"];
		SQA -> SWD;
		SQA -> SCM[label="SQAR under configuration control"][color="coral"][fontcolor="coral"];
		SCM -> SQA[label="Baselines, CRs, Master/Archive"][color="blue"][fontcolor="blue"];	
		SQA -> SV;
		SQA -> SQAR[constraint=false][style=dotted];
		SQA -> SQAP[constraint=false][style=dotted];
		//A3 -> A8 [constraint=false][arrowhead =none,style=dotted];
		label = "Software";
		// style=rounded;
		// color=grey
	//}	
	
	
	/* Artefacts */
	
	/* Bubbles */
	{  rank  =  same;
		QVP [shape=box,label=<<TABLE BORDER="0" BGCOLOR="white"><TR><TD ROWSPAN="2"><IMG SRC="assets/images/zodiac.jpg"/></TD><TD>Quality Vice Precident</TD></TR><TR><TD>Head of Quality Department</TD></TR></TABLE>>,image="assets/images/small_zodiacaerospace.jpg"];
	}	
	{  rank  =  same;
		PQA [shape=box,label=<<TABLE BORDER="0" BGCOLOR="white"><TR><TD ROWSPAN="2"><IMG SRC="assets/images/zodiac.jpg"/></TD><TD>Program Quality Assurance</TD></TR><TR><TD>Program Quality Manager</TD></TR></TABLE>>,image="assets/images/small_zodiacaerospace.jpg"];
		SYS [shape=box,label=<<TABLE BORDER="0" BGCOLOR="white"><TR><TD ROWSPAN="2"><IMG SRC="assets/images/zodiac.jpg"/></TD><TD>System Development</TD></TR><TR><TD>System Manager</TD></TR></TABLE>>,image="assets/images/small_zodiacaerospace.jpg"];
	}
	{  rank  =  same;
		SQA [shape=box][label=<<TABLE BORDER="0" BGCOLOR="white"><TR><TD ROWSPAN="3"><IMG SRC="assets/images/zodiac.jpg"/></TD><TD>Software Quality Assurance</TD></TR><TR><TD>ECE/ZAM Software Quality Manager</TD></TR><TR><TD>Plans SQA activities</TD></TR></TABLE>>];
		SQAR [shape=circle,label="SQA Records"]
		SQAP [shape=circle,label="SQA Plan"]
	}	
	{	rank  =  same; 
		SWD [shape=box][label=<<TABLE BORDER="0" BGCOLOR="white"><TR><TD ROWSPAN="2"><IMG SRC="assets/images/zodiac.jpg"/></TD><TD>Software Development</TD></TR><TR><TD>Software Project Manager</TD></TR></TABLE>>];
		SCM [shape=box][label=<<TABLE BORDER="0" BGCOLOR="white"><TR><TD ROWSPAN="2"><IMG SRC="assets/images/zodiac.jpg"/></TD><TD>Software Configuration Management</TD></TR><TR><TD>Software Configuration Manager</TD></TR></TABLE>>];
		SV [shape=box][label=<<TABLE BORDER="0" BGCOLOR="white"><TR><TD ROWSPAN="2"><IMG SRC="assets/images/zodiac.jpg"/></TD><TD>Software Verification</TD></TR><TR><TD>ECE/ZAM Software Verifiation Engineers</TD></TR></TABLE>>];
		//A8 [shape=box][label=<<TABLE BORDER="0" BGCOLOR="white"><TR><TD ROWSPAN="2"><IMG SRC="assets/images/zodiac.jpg"/></TD><TD>Olivier Appéré</TD></TR><TR><TD>Process Assurance</TD></TR></TABLE>>];
	}
	graph [
	rankdir = "TB";
	//ratio = "compress";
	//splines="ortho";
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
