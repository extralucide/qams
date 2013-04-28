<?php
$fontStyle = array('bold'=>true, 'align'=>'center','name'=>'Verdana','size'=>8,'bold'=>false);
$linkStyle = array('color'=>'0000FF','underline'=>PHPWord_Style_Font::UNDERLINE_SINGLE); 
// Define font style for first row
$fontStyleGreen = array('bold'=>true, 'align'=>'center', 'color'=>'green');
$fontStyleOrange = array('bold'=>true, 'align'=>'center', 'color'=>'yellow');
$fontStyleRed = array('bold'=>true, 'align'=>'center', 'color'=>'red');
$fontStyleGrey = array('bold'=>true, 'align'=>'center', 'color'=>'lightGray');
// Define the TOC font style
$TOCfontStyle = array('spaceAfter'=>60, 'size'=>12);

// Define table style arrays
$styleTable = array('borderSize'=>6, 'borderColor'=>'666666', 'cellMargin'=>40,'size'=>8,'bold'=>false);
$styleFirstRow = array('borderBottomSize'=>18, 'borderBottomColor'=>'AAAAAA', 'bgColor'=>'999999');
$styleSigTable = array('borderSize'=>6, 
						//'borderColor'=>'000000',
						'name'=>'Verdana',
						'size'=>10,
						'bold'=>false,
						'cellMarginTop'=>80,
						'cellMarginLeft'=>80,
						'cellMarginRight'=>80,
						'cellMarginBottom'=>80);

// Define cell style arrays
$shadow_styleCell = array('borderColor'=>'FFFFFF',
						  'bgColor'=>'FFFFFF',
						  'borderBottomSize'=>18,
						  'borderTopSize'=>18,
						  'borderLeftSize'=>18);
$styleCell = array('valign'=>'center');
$styleCellGrey = array('valign'=>'center','bgColor'=>'888888');
$styleCellBTLR = array('valign'=>'center', 'textDirection'=>PHPWord_Style_Cell::TEXT_DIR_BTLR);

// Define font style for first row

$fontStyleGreen = array('bold'=>true, 'align'=>'center', 'color'=>'green');
$fontStyleOrange = array('bold'=>true, 'align'=>'center', 'color'=>'yellow');
$fontStyleRed = array('bold'=>true, 'align'=>'center', 'color'=>'red');
$fontStyleGrey = array('bold'=>true, 'align'=>'center', 'color'=>'lightGray');

$img_status_style = array('width'=>32, 'height'=>32, 'align'=>'center');
// Write a function with parameter "$element"
function my_callback($element) {
		// Hide all <a> tags
			if ($element->tag=='a')
                $element->outertext = $element->innertext;
        // Hide all <u> tags
        //if ($element->tag=='u')
        //        $element->outertext = $element->innertext;
        // Hide all <strong> tags
        //if ($element->tag=='strong')
        //        $element->outertext = $element->innertext;
		if ($element->tag=='h1')
			$element->tag='p';
			//$element->outertext = "<p>".$element->outertext."</p>";		
}
function find_header($input_text) {
	if (preg_match("/(.*)<h1>(.*)<\/h1>(.*)/s", $input_text,$output)) {
		$input_text = $output[2];
		$find = true;
	}
	else {
		$find = false;
	}	
	return($find);
}
function find_underline($input_text) {
	if (preg_match("/(.*)<u>(.*)<\/u>(.*)/s", $input_text,$output)) {
		$input_text = $output[2];
		$find = true;
	}
	else {
		$find = false;
	}	
	return($find);
}
function find_bold($input_text) {
	if (preg_match("/(.*)<strong>(.*)<\/strong>(.*)/s", $input_text,$output)) {
		$input_text = $output[2];
		$find = true;
	}
	else {
		$find = false;
	}	
	return($find);
}

function display_paragraph($str) {
  global $section_landscape;
	//return;
  $html = str_get_html($str);
  // Register the callback function with it's function name
  $html->set_callback('my_callback');
  $str = $html;
  //echo $str;
  // echo $html;
  $counter_p = 1;
  $font_style_array[] = array();
  $paragraph_style[] = array();
  //$paragraph_style['spacing'] = 0;
  //$textrun = $section_landscape;//->createTextRun();
  foreach($html->find('p') as $p) {
	if ($p->innertext != ""){
  		 $pieces = explode("<br />", $p->innertext);
  		 //print_r($pieces);
  		 //$pieces = preg_split('/<br \/>/i',$p->plaintext);
  		 foreach($pieces as $sentences) {
			  //echo $sentences;
			  unset($font_style_array);
			  /* Parse header */
			  if (find_header(&$sentences)) {
				$font_style_array['size'] = 18;
			  }
			  /* Parse underline */
			  if (find_underline(&$sentences)) {
				$font_style_array['underline'] = PHPWord_Style_Font::UNDERLINE_SINGLE;
				/* Parse strong */
				if (find_bold(&$sentences,$output)) {
					$font_style_array['bold'] = true;
				}
			  }	
			  /* Parse strong */
			  else if (find_bold(&$sentences,$output)) {
					$font_style_array['bold'] = true;
			  }
			  else {			  
			    unset($font_style_array);
			  }
			  echo $sentences."<br/>";
			  /* Remplace les é en &eacute par ecemple évite de planter phpword mais ce n'ai pas une boone méthode.
			     Il faut intervenir au nbieau de la base MySQL */
			  $text = html_entity_decode($sentences, ENT_QUOTES, 'iso-8859-1');
			  $section_landscape->addText($text,$font_style_array,'newStyle');
			  //echo "te:".$text."<br/>";
			  //$section_landscape->addTextBreak();
			  
			  //$text = html_entity_decode($sentences, ENT_QUOTES, 'iso-8859-1');
			  //$section_landscape->addText($text);
	          //$section_landscape->addText(convert_html2txt($sentences));
	          //echo "T:".$sentences."<br/>";
	          //$section_landscape->addTextBreak();
  		 }
      }
    $counter_p++;
  }
}  
function display_html ($input_text,$section){
	// HTML Dom object:
	$html_dom = new simple_html_dom();
	$html_dom->load('<html><body>' . $input_text . '</body></html>');
	// Note, we needed to nest the html in a couple of dummy elements

	// Create the dom array of elements which we are going to work on:
	$html_dom_array = $html_dom->find('html',0)->children();

	// Provide some initial settings:
	$initial_state = array(
		  'current_style' => array('size' => '11'),
		  'style_sheet' => h2d_styles(), // This is the "style sheet" in styles.inc
		  'parents' => array(0 => 'body'), // Our parent is body
		  'list_depth' => 0, // This is the current depth of any current list
		  'context' => 'section', // Possible values - section, footer or header
		  'base_root' => 'http://test.local', // Required for link elements
		  'base_path' => '/', // Required for link elements
		  'pseudo_list' => TRUE, // NOTE: Word lists not yet supported (TRUE is the only option at present)
		  'pseudo_list_indicator_font_name' => 'Wingdings', // Bullet indicator font
		  'pseudo_list_indicator_font_size' => '7', // Bullet indicator size
		  'pseudo_list_indicator_character' => 'l ', // Gives a circle bullet point with wingdings
		  );    

	// Convert the HTML and put it into the PHPWord object
	h2d_insert_html($section, $html_dom_array[0]->nodes, $initial_state);
	//$section_landscape->addText($review->objectives);
}