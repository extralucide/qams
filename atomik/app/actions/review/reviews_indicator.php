<?php
Atomik::disableLayout();
Atomik::noRender();
/* pChart library inclusions */
require_once("pChart2.1.3/class/pData.class.php"); 
require_once("pChart2.1.3/class/pDraw.class.php");  
require_once("pChart2.1.3/class/pImage.class.php");
require_once("pChart2.1.3/class/pIndicator.class.php");
require_once("pChart2.1.3/class/pIndicator_special.class.php");
$dir_font = "app/includes/pChart/Fonts/";
$dir_palette = "app/includes/pChart/";	
/* Create and populate the pData object */
$MyData = new pData();
/* Create the pChart object */
$myPicture = new pImage(700,230,$MyData);
/* Enable shadow support */
$myPicture->setShadow(TRUE,array("X"=>10,"Y"=>10,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>5));
/* Draw the background */
$Settings = array("R"=>170, "G"=>183, "B"=>87, "Dash"=>1, "DashR"=>190, "DashG"=>203, "DashB"=>107);
$myPicture->drawFilledRectangle(0,0,700,230,$Settings);

/* Overlay with a gradient */
$Settings = array("StartR"=>219, "StartG"=>231, "StartB"=>139, "EndR"=>1, "EndG"=>138, "EndB"=>68, "Alpha"=>50);
$myPicture->drawGradientArea(0,0,700,230,DIRECTION_VERTICAL,$Settings);
$myPicture->drawGradientArea(0,0,700,20,DIRECTION_VERTICAL,array("StartR"=>0,"StartG"=>0,"StartB"=>0,"EndR"=>50,"EndG"=>50,"EndB"=>50,"Alpha"=>80));

/* Add a border to the picture */
$myPicture->drawRectangle(0,0,699,229,array("R"=>0,"G"=>0,"B"=>0));

/* Write the picture title */ 
$myPicture->setFontProperties(array("FontName"=>$dir_font."Silkscreen.ttf","FontSize"=>6));
$myPicture->drawText(10,13,"Reviews held",array("R"=>255,"G"=>255,"B"=>255));

/* Create the pIndicator object */ 
$Indicator = new pIndicator_special($myPicture);
$myPicture->setFontProperties(array("FontName"=>$dir_font."pf_arma_five.ttf","FontSize"=>6));
/* Define the indicator sections */
$IndicatorSections   = "";
$IndicatorSections[] = array("Date"=>"12 Jui 2010","Start"=>0,"End"=>99,"Caption"=>"Specification","R"=>69,"G"=>142,"B"=>49,"Review"=>"PDR","Actions"=>5,"Open"=>2);
$IndicatorSections[] = array("Date"=>"14 Jui 2010","Start"=>100,"End"=>199,"Caption"=>"Design","R"=>108,"G"=>157,"B"=>49,"Review"=>"CDR","Actions"=>8,"Open"=>4);
$IndicatorSections[] = array("Date"=>"16 Jui 2010","Start"=>200,"End"=>300,"Caption"=>"Test","R"=>117,"G"=>140,"B"=>49,"Review"=>"FDR","Actions"=>3,"Open"=>1);
/* Draw the 1st indicator */
$IndicatorSettings = array("Values"=>array("PDR",
										   "CDR",
										   "FDR"),
							/* "Unit"=>" actions", */
							"CaptionPosition"=>INDICATOR_CAPTION_INSIDE,
							"DrawLeftHead"=>FALSE,
							"ValueDisplay"=>INDICATOR_VALUE_BUBBLE,
							"ValueFontName"=>$dir_font."Forgotte.ttf",
							"ValueFontSize"=>15, 
							"IndicatorSections"=>$IndicatorSections,
							"SubCaptionColorFactor"=>-40);
$Indicator->draw(80,50,550,50,$IndicatorSettings);
/* Left green box */
$RectangleSettings = array("R"=>150,"G"=>200,"B"=>170,"Dash"=>TRUE,"DashR"=>170,"DashG"=>220,"DashB"=>190,"BorderR"=>255, "BorderG"=>255,"BorderB"=>255);
// $myPicture->drawFilledRectangle(20,60,400,170,$RectangleSettings);

/* Render the picture */
$bar_filename="test.png";
$myPicture->Render($bar_filename);			
$gdImage_poster = @imagecreatefrompng($bar_filename);
echo '<img src="../'.$bar_filename.'">';  
