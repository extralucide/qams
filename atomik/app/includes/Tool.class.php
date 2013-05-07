<?php
class Tool{
    private $vars;
	function ob_file_callback($buffer)
	{
	  global $ob_file;
	  fwrite($ob_file,$buffer);
	} 
	function manage_log($buffer){
		$filename = dirname(__FILE__).DIRECTORY_SEPARATOR.
				"..".DIRECTORY_SEPARATOR.
				"..".DIRECTORY_SEPARATOR.					
				"..".DIRECTORY_SEPARATOR.A('db_config/log');
		$monfichier = fopen($filename, 'a');
		fputs($monfichier, $buffer."\n");
		fclose($monfichier);	
	}	
	/*
	* getStringIndex
	*/
	public static function getStringIndex($string,$content) {
		$index=0;
		foreach ($content as $sub_part) {
			if(preg_match($string,$sub_part)) {
				break;
			}
			$index++;
		} 	  
		return($index);
	}
	/*
	 * Check shall
	 */
	public static function checkValidity($key,$val,$justification){
		//var_dump($colspan);
		/* check @ */
		$color="";
		if (preg_match("/@/i",$val)){
			$color="yellow";
		}
		/* check multiple shall */
		if ($key == "body"){
			$val = preg_replace("/(shall)/i","shall",$val,-1,$count);
			if  ($count == 0){
				$color="red";
			}
			else if ($count > 1){
				if ($color != ""){
					$color="red";
				}
				else{
					$color="orange";
				}
			} 
			$val = preg_replace("/ (if|when|then|shall)[\s:,.]/i",'<span style="color:#484"> $1 </span>',$val,-1,$count);			
		}
		/* check derived requirement without rationale */
		if ($key == "upper"){
			if ((preg_match("/Derived/i",$val))&&(($justification=="")||($justification=="NA"))){
				$color="yellow";
			}
		}
		else if(($key == "derived")&&($val="YES")){
			if (($justification=="")||($justification=="NA")){
				$color="yellow";
			}
		}
		return($color);
	}
	public static function splitTag($input){
		$output = preg_replace("/(,|;)/","<br/>",$input);
		return($output);
	}	
	public static function getTag($tag,$input){
		$result = preg_match("/".$tag."(.+)/i",$input,$output);
		//var_dump($output);echo "<br/>";
		$value = isset($output[1])?$output[1]:"";
		if ($value == "")$value = "-";
		return($value);
	}	
	public static function addkey($key,$value){
		if (Atomik::has('context_array/'.$key)){
		}
		else{
			/* key does not exists yet */
			Atomik::add('context_array',array($key => $value));	
		}
	}
	public static function deleteKey($key){
		if (Atomik::has($key)) {
			Atomik::delete($key);
		}
	}
	public static function setFilter($field,$id,$first_item=false){
		if (($id == NULL) || ($id == 0)){
			$filter = "";
		}
		else {
			$filter = ($first_item ? ' WHERE' : ' AND')."  {$field} = {$id} ";
		}
		return($filter);
	}
	public static function setFilterWhere($field,$id,$first_item=true){
		return(self::setFilter($field,$id,true));
	}	
	public static function displayFirstPage($file,$ext){		
		 switch ($ext) {
			case "rtf":
				$rtf_name = '../docs/'.$file;
				$fp = fopen($rtf_name, 'r');
				$rtf_content = fread ($fp, filesize ($rtf_name));
				//echo $rtf_content;
				$r = new rtf( stripslashes( $rtf_content));
				$r->output("html");
				$r->parse();
				if( count( $r->err) == 0) {// no errors detected
					echo "No error display rtf<br/>";
					echo $r->out;
				}
				else {
					$index = 0;
					while ($index <= count( $r->err)) {
						echo $r->err[$index++]."<br/>";
					}
				}
				fclose($fp);
				$display = '';				
				break;
			case "pdf":
				ini_set('display_errors', 1);
				$pdf=realpath(dirname(__FILE__))."/../docs/".$file;
				$quality=90;
				$res='100x100';
				$exportName="pdf_export_" . time();
				$exportPath=realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."result".DIRECTORY_SEPARATOR;
				Tool::createFirstPage($file_attached_id.".jpg",$exportPath);
				$display = '<img src="../result/'.$file_attached_id.'.jpg" width="480" style="margin-top:10px;margin-left:5px;">';
				break;		
			default:
				$display = '';
				break;
		}
		return($display);
	}
	public static function convertEmfToPng($input){
		Atomik::needed('Db.class');
	    $db = new Db;		
		$output = preg_replace("/(.+)\.[e|w]mf/","$1.png",$input);
		$path = dirname(__FILE__).DIRECTORY_SEPARATOR.
						"..".DIRECTORY_SEPARATOR.
						"..".DIRECTORY_SEPARATOR.
						"..".DIRECTORY_SEPARATOR."result".DIRECTORY_SEPARATOR;
		$full_input = $path.$input;
		$full_output = $path.$output;
	    if (Db::getServerOS() == "unix") {
			$command = preg_replace(array("/{input}/","/{output}/"),
									array($full_input,$full_output),A('db_config/imagemagick/unix'));
	    }
	    else if(Db::getServerOS() == "mac"){
			$command = preg_replace(array("/{input}/","/{output}/"),
									array($full_input,$full_output),A('db_config/imagemagick/mac'));
	    }
	    else {
			$command = preg_replace(array("/{input}/","/{output}/"),
									array($full_input,$full_output),A('db_config/imagemagick/win'));
	    }	
	    // echo $command;
	    set_time_limit(900);	    
	    exec($command,$retval,$code);
	    $res = "";
	    foreach($retval as $row){
			$res .= $row."<br/>";
	    }
		/* reduce size */
        $new_w=800;
        $new_h=800;
		if (file_exists($full_output)) {
			self::createthumb($full_output,$full_output,$new_w,$new_h); 
		}
		else{
			$output = false;
		}
		// echo $res;
		// exit();
		return($output);	
	}
	public static function createFirstPage($input_pdf,$exportPath){
	    $db = new Db;
	    $quality=90;
	    $res='75x75';
	    if (Db::getOS() == "unix") {
			$command = preg_replace(array("/{exportPath}/","/{res}/","/{quality}/","/{input_pdf}/"),
									array(addslashes($exportPath),$res,$quality,addslashes($input_pdf)),A('db_config/ghostscript/unix'));
	    }
	    else if(Db::getOS() == "mac"){
			$command = preg_replace(array("/{exportPath}/","/{res}/","/{quality}/","/{input_pdf}/"),
									array(addslashes($exportPath),$res,$quality,addslashes($input_pdf)),A('db_config/ghostscript/mac'));
	    }
	    else {
			$command = preg_replace(array("/{exportPath}/","/{res}/","/{quality}/","/{input_pdf}/"),
									array(addslashes($exportPath),$res,$quality,addslashes($input_pdf)),A('db_config/ghostscript/win'));
	    }	
	    set_time_limit(900);
	    $config = Atomik::get('db_config');
		/* Line for finister.freeheberg.com */
		if($config['select'] == "finister"){
			$text = "No pdf creation possible on this server (exec command is disabled).";
			$res = "<li class='failed' style='list-style-type: none;'>".$text."</li>";
		}
		else{
		    exec($command,$retval,$code);
		    $res = "";
		    foreach($retval as $row){
				$res .= $row."<br/>";
		    }
		}
		return($res);
	}
	public static function move($source,$target){
		/*if destination file exists remove it */
		if (file_exists($target)) {
			unlink($target);
		}
		rename($source, $target);	
	}	
	public static function copy($filename,$dest){
		$db = new Db;
		if ($db->getOS() == "unix"){
			$copy_cmd="cp";
			$del_cmd="rm";
		}
		else{
			$copy_cmd="copy";
			$del_cmd="del";
		}	
		$res = "";
		if (file_exists($dest)){
			/*if destination file exists remove it */
			$delete = "{$del_cmd} {$dest}";
			$txt .= $delete."<br/>";
			exec($delete,$retval,$code);
			//echo $delete."<br/>";
			foreach($retval as $row){
				$res .= $row."<br/>";
			}			
		}	
		$copy = $copy_cmd." {$filename} {$dest}";
		exec($copy,$retval,$code);
		//echo $copy."<br/>";
	    foreach($retval as $row){
			$res .= $row."<br/>";
	    }
		return($res);
	}
	public static function zip($filename){
		$zip = new ZipArchive(); 
		if($zip->open($filename.'.zip', ZipArchive::CREATE) === true){
			$zip->addFile($filename);
			$zip->close();
			unlink($filename);
			$filename .= ".zip";
		}
		else{
			echo 'Impossible d&#039;ouvrir &quot;'.$this->backup_filename.'.zip<br/>';
		}
		return($filename);
	}
	public static function dbBackup(){
	    $db = new Db;
        $date = date("d-m-Y"); // On d?finit le variable $date (ici, son format)
		$copy = "";
		$backup_filename = "qams_db_backup_".$date.".sql";
		set_time_limit(900);
	    if (Db::getOS() == "unix") {
			$backup_filename .= ".gz";
			$command = preg_replace(array("/{db_server}/","/{db_user}/","/{db_pass}/","/{db_select}/","/{output}/"),
									array(A('db_config/server'),A('db_config/user'),A('db_config/pass'),A('db_config/select'),$backup_filename),A('db_config/mysqldump/unix'));
	    }
	    else if(Db::getOS() == "mac"){
			$backup_filename .= ".gz";
			$command = preg_replace(array("/{db_server}/","/{db_user}/","/{db_pass}/","/{db_select}/","/{output}/"),
									array(A('db_config/server'),A('db_config/user'),A('db_config/pass'),A('db_config/select'),$backup_filename),A('db_config/mysqldump/mac'));
	    }
	    else {
			$command = preg_replace(array("/{db_server}/","/{db_user}/","/{db_pass}/","/{db_select}/","/{output}/"),
									array(A('db_config/server'),A('db_config/user'),A('db_config/pass'),A('db_config/select'),$backup_filename),A('db_config/mysqldump/win'));

			
	    }	
	    $config = Atomik::get('db_config');
		/* Line for finister.freeheberg.com */
		if($config['select'] == "finister"){
			$text = "No database backup possible on this server (exec command is disabled).";
			$res = "<li class='failed' style='list-style-type: none;'>".$text."</li>";
			$backup_filename = false;
		}
		else{   
		    exec($command,$retval,$code);
		    $res = "";
		    foreach($retval as $row){
				$res .= $row."<br/>";
		    }
			$backup_filename = self::zip($backup_filename);
			self::copy($backup_filename,A('db_config/backup_dir'));
		}	
		return($backup_filename);
	}
	public static function appliBackup(){
	    $db = new Db;	
        $date = date("d-m-Y"); // On definit le variable $date (ici, son format)
        $backup_filename = "qams_appli_backup_".$date;		
		if (Db::getOS() == "unix"){
            /* Linux */
			$backup_filename .= ".gz";
			$command = preg_replace(array("/{output}/"),
									array($backup_filename),A('db_config/tar/unix'));
        }
		else if(Db::getOS() == "mac"){
			$backup_filename .= ".gz";
			$command = preg_replace(array("/{output}/"),
									array($backup_filename),A('db_config/tar/mac'));		
		}
        else {
            /* Windows */
			$backup_filename .= ".zip";
			$command = preg_replace(array("/{output}/"),
									array($backup_filename),A('db_config/tar/win'));
			/* copy to server spar-nas2 */
            // $copy = "copy ".$this->backup_filename." {$this->backup_dir}";
            // exec($copy,$retval,$code);
		}
	    $config = Atomik::get('db_config');
		/* Line for finister.freeheberg.com */
		if($config['select'] == "finister"){
			$text = "No QAMS backup possible on this server (exec command is disabled).";
			$res = "<li class='failed' style='list-style-type: none;'>".$text."</li>";
			$backup_filename = false;
		}
		else{  			 
		    exec($command,$retval,$code);
		    $res = "";
		    foreach($retval as $row){
				$res .= $row."<br/>";
		    }
		}
		return($backup_filename);		
	}
	public static function cleanDescription($input){
		$result = preg_replace(array("/<p>(.*)<\/p>/s","/(.*)<br ?\/>(.*)/s"),array("$1","$1"),$input);
		return($result);
	}
	public static function cleanFilename($name){
		$search = array ('@/@i','@ @i','@:@i','@,@i');
		$replace = array ('_','_','_','_');
		return preg_replace($search, $replace, $name);		
		// return($filename);
	}
	public static function convert_html2txt ($text,$compat="iso-8859-1") {
		require_once('class.html2text.inc'); 
		$h2t = new html2text($text);
		$plain_text = $h2t->get_text();
		// echo "<pre>$plain_text</pre>";
		$plain_text = html_entity_decode($plain_text, ENT_COMPAT, $compat);
		return ($plain_text);
	}	
	/* Ne fonctionne pas correctement */
	public static function clean_text ($text) {    
		$plain_text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
		$plain_text = strip_tags($plain_text);
		$plain_text = str_replace("&"," and ",$plain_text);
		return ($plain_text);
	}	
	public static function clean_author_response ($author_response) {
		/* convert html in xls format */
		$search = array ('@\<br\s*\/?\>@i','@<BR>@i','@\/?p>@i','@\\t@i','@&gt;@i','@&quot;@i');
		$replace = array ('','','$1','     ','>','"');
		return preg_replace($search, $replace, $author_response);	
	}
	public static function convert2ascii ($input_text) {
		/* This function convert unicode to windows-1250 format. Works better than utf8_decode */
		$string=iconv('utf-8','windows-1250',$input_text);
		if (0==0){
			/* Replace all characters with hexa code upper 0x7F */
			$search = array ('/\x95/',					/* bullets */
							'/(\x82|\x8B|\x91|\x92)/', 	/* single quotes */
							'/(\x84|\x93|\x94)/', 		/* double quotes */
							'/(\x96|\x97)/',			/* dash */
							'/(\xA7)/',					/* section */
							'/[\x80-\xFF]/');		 	/* others */
			$replace = array ('.',
							"'",
							'"',
							'-',
							'§',
							' ');
			$text = preg_replace($search, $replace, $string);
		}
		else{
			$text = $string;
		}
		return($text);
	}
	public static function remove_space($input){
		// $output = preg_replace("/(?:\s|&nbsp;)+/", "", $input, -1);
		// $output = preg_replace("/&nbsp;/","",$input);
		$output = trim(str_replace('&nbsp;','',$input));
		return ($output);
	}
	
	public static function filter($in) {
		$search = array ('@[éèêëÊË]@i','@[àâäÂÄ]@i','@[îïÎÏ]@i','@[ûùüÛÜ]@i','@[ôöÔÖ]@i','@[ç]@i','@[§]@i','@[&]@i','@[“”]@i','@[•]@i');
		$replace = array ('e','a','i','u','o','section','section','and','"','.');
		return preg_replace($search, $replace, $in);
	}	
	public static function base64_encode_image ($imagefile,$raw=false) {
		$imgtype = array('jpg','jpeg', 'gif', 'png');
		// var_dump($imagefile);
		$filename = file_exists($imagefile) ? htmlentities($imagefile) : die('Image file name does not exist');
		$filetype = pathinfo($filename, PATHINFO_EXTENSION);
		if (in_array($filetype, $imgtype)){
			$imgbinary = fread(fopen($filename, "r"), filesize($filename));
		} else {
			die ('Invalid image type, jpg, gif, and png is only allowed');
		}
		if ($raw==true){
			return chunk_split(base64_encode($imgbinary));
		}else{
			return 'data:image/' . $filetype . ';base64,' . chunk_split(base64_encode($imgbinary));
		}

	}	
	public static function compute_pages($nbtotal,$page,$debut,$limite){
		if ($limite != 0){
			$nbpage = ceil($nbtotal / $limite);
		}
		else {
			$nbpage = 1;
		}
		if ($page > $nbpage) {
			/* on rectifie la page courante pour qu'elle 
			   ne depasse pas le nombre de pages */
			$page = $nbpage;
		}
		if ($page > 0){
			/* partir de quel enregistrement commence la selection dans notre cas si $page=1 $debut=0 / si $page=2 $debut=(2-1)*3 = 3 */
			$debut=($page-1)*$limite; // $debut   
		}
		else{
			$debut=0;
		}
		return($nbpage);
	}
    //fonctions de sauvegarde et de restauration pas session
    public function setSession($nom){
      return $_SESSION[$nom]=$this->vars;
    }
    public function getSession($nom){
      return $this->load($_SESSION[$nom]);
    }	
    /*
        Function createthumb($name,$filename,$new_w,$new_h)
        creates a resized image
        variables:
        $name       Original filename
        $filename   Filename of the resized image
        $new_w      width of resized image
        $new_h      height of resized image
    */  
    public static function createthumb($name,$filename,$new_w,$new_h)
    {
        $system=explode(".",$name);
        //var_dump($system);
        if (preg_match("/jpg|jpeg$/",$name)){
            $src_img=imagecreatefromjpeg($name);
        }
        if (preg_match("/png$/",$name)){
            $src_img=imagecreatefrompng($name);
        }
        $old_x=imageSX($src_img);
        $old_y=imageSY($src_img);
        if ($old_x > $old_y) 
        {
            $thumb_w=$new_w;
            $thumb_h=$old_y*($new_w/$old_x);
        }
        if ($old_x < $old_y) 
        {
            $thumb_w=$old_x*($new_h/$old_y);
            $thumb_h=$new_h;
        }
        if ($old_x == $old_y) 
        {
            $thumb_w=$new_w;
            $thumb_h=$new_h;
        }
        //echo $thumb_w.":".$thumb_h."<br/>";
        //exit();
        $dst_img=ImageCreateTrueColor($thumb_w,$thumb_h);
		imagesavealpha($dst_img, true);
		$trans_colour = imagecolorallocatealpha($dst_img, 0xFF, 0xFF, 0xFF, 127);
		imagefill($dst_img, 0, 0, $trans_colour);
        imagecopyresampled($dst_img,
							$src_img,
							0,0,0,0,
							$thumb_w,
							$thumb_h,
							$old_x,
							$old_y); 
        if (preg_match("/png/",$system[1])){
            imagepng($dst_img,$filename); 
        } else {
            imagejpeg($dst_img,$filename); 
        }
        imagedestroy($dst_img); 
        imagedestroy($src_img); 
    }
	// Fonction de dessin basique d'un "array" PHP sous forme de treeview
	// 10.05.2011 - gael@memepasmal.ch - http://www.memepasmal.ch/2011/05/10/php-array-treeview/
	public static function drawTree($myarray, $level = 0)
	{
		// Boucle sur chaque élément du tableau
		foreach($myarray as $key => $value)
		{
			echo '<ul>';
			// En cas d'objet on convertit en tableau
			if (is_object($value)) $value = (array)$value;
		   
			// Si l'élément est un tableau
			if (is_array($value)) {
		   
				// On l'affiche en tant que noeud
				echo '<li>[' . $key . ']';
			   
				// Puis on affiche son arborescence, à un niveau supérieur
				Tool::drawTree($value, $level + 1);
				echo '</li>';
			   
			} else {
		   
				// C'est une valeur, on l'affiche
				echo '<li>' . $key . ' = <b>' . $value . '</b></li>';
			   
			}
			echo '</ul>';
		}
	}
	public static function shrink($text){
		$text = str_replace("\r","",$text);
		$text = str_replace("\n","",$text);
		$text = str_replace("\t","",$text);
		// while(substr_count($text,"  ") != 0){
			// $text = str_replace("  "," ",$text);
		// }
		return($text);
	}
    //constructeur
    public function __construct(){
      $this->vars=array();
    }
    //fonctions basiques
    public function __get($key){
      return $this->vars[$key];
    }
    public function __set($key,$value){
      return $this->vars[$key]=$value;
    }
	public function setPOST(){
	  foreach($_POST as $key=>$value){
		if(isset($this->vars[$key])){
		  $this->vars[$key]=$value;
		}
	  }
	}	
	public static function resetSession(){
		Tool::deleteKey('session/sub_project_id');
		Tool::deleteKey('session/review_id');
		Tool::deleteKey('session/data_status_id');
		Tool::deleteKey('session/action_status_id');
		Tool::deleteKey('session/user_id');
		Tool::deleteKey('session/type_id');
		Tool::deleteKey('session/review_type_id');		
		Tool::deleteKey('session/criticality_id');
		Tool::deleteKey('session/baseline_id');
		Tool::deleteKey('session/reference');
		Tool::deleteKey('session/group_id');
		Tool::deleteKey('session/highlight/group_id');
		Tool::deleteKey('session/highlight/all');
		Tool::deleteKey('session/highlight/plan');
		Tool::deleteKey('session/highlight/cert');
		Tool::deleteKey('session/highlight/spec');
		Tool::deleteKey('session/highlight/design');
		Tool::deleteKey('session/highlight/conf');
		Tool::deleteKey('session/highlight/test');
		Tool::deleteKey('session/highlight/prod');
		Tool::deleteKey('session/highlight/note');
		Tool::deleteKey('session/highlight/review');
	}
   public static function Get_Filename ($uploaded_id,$extension,$atomik="") {
      if ($uploaded_id != "") {
          $filename = "docs/".$uploaded_id.".".$extension;
         if ($atomik == "atomik") {
            $filename = "../".$filename;
         }
      }
      else {
          $filename = "empty";
      }
      return ($filename);
   }	
   public static function Get_Mime ($link) {
     if (preg_match("#.doc(x|m)?$#", $link)) {
        $link_mime = Atomik::asset("assets/images/icon-ms-word-2003.gif"); 
     }
     else if (preg_match("#.rtf$#", $link)) {
        $link_mime = Atomik::asset("assets/images/icon-ms-word-2003.gif"); 
     }     
     else if (preg_match("#.xls[x|m]?$#", $link)) {
        $link_mime =Atomik::asset("assets/images/icon-ms-excel-2003.gif");   
     }
     else if (preg_match("#.pptx?$#", $link)) {
        $link_mime = Atomik::asset("assets/images/icon-ms-powerpoint-2003.gif"); 
     }
     else if (preg_match("#.pdf$#", $link)) {
        $link_mime = Atomik::asset("assets/images/pdficon_large.gif");  
     }  
     else {
       $link_mime = Atomik::asset("assets/images/32x32/attachment.png");    
     }
     return ($link_mime);
   }
	private  static function getWorksheetName($uploadName,$type){
		if (preg_match("/xls/i", $type))
		{
			/* Detect type of peer review */ 
			if (($type == "xlsx")||($type == "xlsm")){
				/* read worksheet names */        
				require_once("../excel_176/Classes/PHPExcel/Reader/Excel2007.php");
				$objReader = PHPExcel_IOFactory::createReader('Excel2007');
			}		
			elseif ($type == "xls"){
				/* read worksheet names */
				require_once("../excel_176/Classes/PHPExcel/Reader/Excel5.php");
				$objReader = PHPExcel_IOFactory::createReader('Excel5');		
			}
			$worksheet = $objReader->listWorksheetNames($uploadName);	
		}
		return($worksheet);
	}   
	public static function scanExcelFull($uploadName,$type,$sheet=null){
		if ((file_exists($uploadName))&&(preg_match("/xls/i", $type))){
			$worksheet_names = Tool::getWorksheetName($uploadName,$type);
			if ($sheet == null){
				$sheet = array($worksheet_names[0]);
			}
			$objWorksheet = Tool::getWorksheet($uploadName,$type,$sheet);
		}
		return($objWorksheet);
	}
	private static function getWorksheet($uploadName,$type,$sheet_to_load){
		if (preg_match("/xls/i", $type))
		{
			/* Detect type of peer review */ 
			if (($type == "xlsx")||($type == "xlsm")){
				/* read worksheet names */        
				require_once("../excel_176/Classes/PHPExcel/Reader/Excel2007.php");
				$objReader_xlsx = new PHPExcel_Reader_Excel2007();
			}
			else if ($type == "xls"){
				/* read worksheet names */
				require_once("../excel_176/Classes/PHPExcel/Reader/Excel5.php");
				$objReader_xls = new PHPExcel_Reader_Excel5();
			}
			/* No need for styling */
			$objReader_xlsx->setReadDataOnly(true);
			$objReader_xlsx->setLoadSheetsOnly( $sheet_to_load );
			$objPHPExcel = $objReader_xlsx->load($uploadName);
			// $objPHPExcel->setActiveSheetIndex($sheet_index);
			$objWorksheet = $objPHPExcel->getActiveSheet();			
		}
		return($objWorksheet);
	}	
   public static function read_openxml_header($filename,$xslt="preview-header.xslt"){
	   require_once('../word/ReadDocx/openxml/openxml.class.php');

		$document = $filename;

		// foreach ($documents as $document) {

		   //echo "<b><u>$document</u></b><br/>";
		   
		   try  {

			  $mydoc = OpenXMLDocumentFactory::openDocument($document);
			  $text = $mydoc->getHTMLHeaderPreview($xslt);
		   	  // echo  $text;
		   }
		   catch (OpenXMLFatalException $e) {
		   
			  echo $e->getMessage();
		   
		   }
		   echo '<br/><br/>';
		   
		// }
		return ($text);
   }
   public static function getMedia($filename,$id,$img){
   	   $document = $filename;
   	   $mydoc = OpenXMLDocumentFactory::openDocument($document);
   	   $image_name = $mydoc->getMedia($id,&$img);
   	   return($image_name);
   }
   public static function read_openxml($filename,$xslt="preview-word.xslt"){
	   require_once('../word/ReadDocx/openxml/openxml.class.php');

		$document = $filename;
		$text = "";
		// foreach ($documents as $document) {

		   //echo "<b><u>$document</u></b><br/>";
		   
		   try  {

			  $mydoc = OpenXMLDocumentFactory::openDocument($document);
		   /*
			  echo '<br/><i>Metadata :</i><br/><br/>';
			  echo 'Creator: ' . $mydoc->getCreator() . '<br/>';
			  echo 'Subject: ' . $mydoc->getSubject() . '<br/>';
			  echo 'Keywords: ' . $mydoc->getKeywords() . '<br/>';
			  echo 'Description: ' . $mydoc->getDescription() . '<br/>';
			  echo 'Creation Date: ' . $mydoc->getCreationDate() . '<br/>';
			  echo 'Last Modification Date: ' . $mydoc->getLastModificationDate() . '<br/>';
			  echo 'Last Writer: ' . $mydoc->getLastWriter() . '<br/>';
			  echo 'Revision: ' . $mydoc->getRevision() . '<br/>';
				 
			  echo '<br/><i>Properties of document:</i><br/><br/>';
			  
			  echo 'Generated by: ' . $mydoc->getApplication() . '<br/>';
		   */
			  $document_class = get_class($mydoc); 
			 /* 
			  if ($document_class == 'WordDocument') {
			  
		   
				 echo 'Paragraphs: ' . $mydoc->getNbOfParagraphs() . '<br />';
				 echo 'Characters: ' . $mydoc->getNbOfCharacters() . '<br />';
				 echo 'Characters (avec les espaces): ' . $mydoc->getNbOfCharactersWithSpaces() . '<br/>';
				 echo 'Pages: ' . $mydoc->getNbOfPages() . '<br/>';
				 echo 'Words: ' . $mydoc->getNbOfWords() . '<br/>';
				 
			  }
			  
			  echo '<br/><i>Preview document:</i> <br/>';
			  */
			  $text = $mydoc->getHTMLPreview($xslt);
			  //$img = "";
			  //$image = $mydoc->getMedia('rId26',$img);
			  //var_dump($image);
			  //var_dump($img);
			  //exit();
		   	  // echo  $text;
		   }
		   catch (OpenXMLFatalException $e) {
		   
			  echo $e->getMessage();
		   
		   }
		   echo '<br/><br/>';
		   
		// }
		return ($text);
   }
	public static function odt2text($filename) {
		return self::readZippedXML($filename, "content.xml");
	}
	
	public static function docx2text($filename) {
	    return self::readZippedXML($filename, "word/document.xml");
	}
	
	public static function readZippedXML($archiveFile, $dataFile) {
	    // Create new ZIP archive
	    $zip = new ZipArchive;
	
	    // Open received archive file
	    if (true === $zip->open($archiveFile)) {
	        // If done, search for the data file in the archive
	        if (($index = $zip->locateName($dataFile)) !== false) {
	            // If found, read it to the string
	            $data = $zip->getFromIndex($index);			
				// var_dump($data);
				// exit();
	            // Close archive file
	            $zip->close();
	            // Load XML from a string
	            // Skip errors and warnings
				
				// $xml = new SimpleXMLElement($data);
				// var_dump($xml);
				// echo $movies->movie[0]->plot;
				// $data_stripped = str_replace("w:","",$data);
				
				$xml = new DOMDocument();
	            $xml->loadXML($data, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
	            // Return data without XML formatting tags
				// $liste = $xml->getElementsByTagName('*');
				// $count=0;
				// $x = $xml->documentElement;
				// foreach ($x->childNodes AS $item)
				  // {
				  // print $item->nodeName . " = " . $item->nodeValue . "<br />";
				  // }
				// foreach($xm as $lis):
					// if ($lis->hasAttribute("name")) {
						// if($lis->getAttribute("name")=="Title"){
							// echo $lis->nodeValue;
						// }
					// }
					// $count++;
					// echo $count.": ".$lis->nodeValue, PHP_EOL;
					// var_dump($lis);
				// endforeach;
				// echo $xml->getName() . "<br />";
				// var_dump($xml->children());
				// foreach($xml->children() as $child)
				  // {
				  // echo $child->getName() . ": " . $child . "<br />";
				  // }
				$text = $xml->saveXML();
				// echo "Analyse terminé.<br/>";
				// echo $text;
				// exit();
				return strip_tags($text);
			}
			$zip->close();
		}
	    // In case of failure return empty string
	    return "error no archive file received from ".$archiveFile;
	}  
}