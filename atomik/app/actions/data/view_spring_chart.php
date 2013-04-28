<?php
Atomik::disableLayout();
Atomik::setView("view_diagram");
Atomik::needed("Data.class");
$dir_font = "app/includes/pChart2.1.3/fonts/";
$data_id = (isset($_GET['id']) ? $_GET['id'] : null);
$data = new Data;
$data->get($data_id);
$diagram_img = "";
 /* CAT:Spring chart */ 

 /* pChart library inclusions */ 
 include("pChart2.1.3/class/pData.class.php"); 
 include("pChart2.1.3/class/pDraw.class.php"); 
 include("pChart2.1.3/class/pSpring.class.php"); 
 include("pChart2.1.3/class/pImage.class.php"); 

 /* Create the pChart object */ 
 $myPicture = new pImage(600,600); 

 /* Draw the background */ 
 $Settings = array("R"=>170, "G"=>183, "B"=>87, "Dash"=>1, "DashR"=>190, "DashG"=>203, "DashB"=>107); 
 $myPicture->drawFilledRectangle(0,0,600,600,$Settings); 

 /* Overlay with a gradient */ 
 $Settings = array("StartR"=>219, "StartG"=>231, "StartB"=>139, "EndR"=>1, "EndG"=>138, "EndB"=>68, "Alpha"=>50); 
 $myPicture->drawGradientArea(0,0,600,600,DIRECTION_VERTICAL,$Settings); 
 $myPicture->drawGradientArea(0,0,600,20,DIRECTION_VERTICAL,array("StartR"=>0,"StartG"=>0,"StartB"=>0,"EndR"=>100,"EndG"=>100,"EndB"=>100,"Alpha"=>80));

 /* Add a border to the picture */ 
 $myPicture->drawRectangle(0,0,599,599,array("R"=>0,"G"=>0,"B"=>0)); 

 /* Write the picture title */  
 $myPicture->setFontProperties(array("FontName"=>$dir_font."Silkscreen.ttf","FontSize"=>6)); 
 $myPicture->drawText(10,13,"pSpring - Draw spring charts",array("R"=>255,"G"=>255,"B"=>255)); 

 /* Set the graph area boundaries*/  
 $myPicture->setGraphArea(20,20,580,580); 

 /* Set the default font properties */  
 $myPicture->setFontProperties(array("FontName"=>$dir_font."Forgotte.ttf","FontSize"=>9,"R"=>80,"G"=>80,"B"=>80)); 

 /* Enable shadow computing */  
 $myPicture->setShadow(TRUE,array("X"=>2,"Y"=>2,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10)); 

 /* Create the pSpring object */  
 $SpringChart = new pSpring(); 

 /* Set the default parameters for newly added nodes */  
 $SpringChart->setNodeDefaults(array("Shape"=>NODE_SHAPE_SQUARE,"FreeZone"=>100,"Size"=>20)); 

 /* Create 11 random nodes */  
 /*
 for($i=0;$i<=3;$i++) 
  { 
   $Connections = ""; $RdCx = rand(0,1); 
   for($j=0;$j<=$RdCx;$j++) 
    { 
     $RandCx = rand(0,3); 
     if ( $RandCx != $j ) 
      { $Connections[] = $RandCx; } 
    } 
   $SpringChart->addNode($i,array("Name"=>"Node ".$i,"Connections"=>$Connections));
	print_r($Connections);   
  } 
  */
// $SpringChart->addNode(0,array("Name"=>"Spec 1","Connections"=>""));
// $SpringChart->addNode(1,array("Name"=>"Spec 2","Connections"=>array(1=>0)));
 $SpringChart->addNode(0,array("Name"=>"Spec 1","NodeType"=>NODE_TYPE_CENTRAL));
 $SpringChart->addNode(1,array("Name"=>"Upper Spec 2","Connections"=>"0"));
 $SpringChart->addNode(2,array("Name"=>"Upper Spec 3","Connections"=>"0"));
 $SpringChart->addNode(3,array("Name"=>"Lower Spec 4","Shape"=>NODE_SHAPE_TRIANGLE,"Connections"=>"0"));
 $SpringChart->addNode(4,array("Name"=>"Lower Spec 5","Shape"=>NODE_SHAPE_TRIANGLE,"Connections"=>"0"));
 $SpringChart->addNode(5,array("Name"=>"Lower Spec 6","Shape"=>NODE_SHAPE_TRIANGLE,"Connections"=>"0"));
  /* Define the nodes color */
 $SpringChart->setNodesColor(0,array("R"=>215,"G"=>163,"B"=>121,"BorderR"=>166,"BorderG"=>115,"BorderB"=>74));
 $SpringChart->setNodesColor(array(1,2),array("R"=>150,"G"=>215,"B"=>121,"Surrounding"=>-30));
 $SpringChart->setNodesColor(array(3,4,5),array("R"=>216,"G"=>166,"B"=>14,"Surrounding"=>-30));
 $SpringChart->setNodesColor(array(6,7,8),array("R"=>179,"G"=>121,"B"=>215,"Surrounding"=>-30));
 /* Customize some relations */
 $SpringChart->linkProperties(0,1,array("R"=>255,"G"=>0,"B"=>0,"Ticks"=>2));
 $SpringChart->linkProperties(0,2,array("R"=>255,"G"=>0,"B"=>0,"Ticks"=>2));
 /* Draw the spring chart */  
 $Result = $SpringChart->drawSpring($myPicture,array("DrawQuietZone"=>false,
													"DrawVectors"=>false,
													"Algorithm"=>ALGORITHM_WEIGHTED,
													"RingSize"=>100)); //WEIGHTED 

 /* Output the statistics */  
 // print_r($Result); 

 /* Render the picture (choose the best way) */ 
 $myPicture->autoOutput("pictures/example.spring.complex.png"); 
