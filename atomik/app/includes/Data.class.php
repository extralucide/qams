<?php
/**
 * Quality Assurance Management System
 * Copyright (c) 2009-2013 Olivier Appere
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package     Data.class
 * @author      Olivier Appere
 * @copyright   2009-2013 (c) Olivier Appere
 * @license     http://www.opensource.org/licenses/mit-license.php
 * @link        
 */
/**
 * Handle data
 *
 * @package Data
 */
define('Specification', 1);
define('Design', 8);
define('Plans', 2);
define('Interfaces', 3);	
define('Certification', 5);
define('Safety', 6);	
define('Production', 7);	
define('Verification', 4);
define('Configuration', 9);
define('Notes', 10);
define('Methodology', 11);

function no_magic_quotes($query) {
        $data = explode("\\\\",$query);
        $cleaned = implode("/",$data);
        return $cleaned;
	} 
class Data {
	public $id;
	public $previous_data_id;
	public $group_id;
	public $project;
	public $lru;
	public $project_id;
	public $lru_id;
	public $reference;
	public $type;
	public $type_id;
	public $type_description;
	public $description;
	public $abstract;
	public $status;
	public $status_id;
	public $priority_id;
	public $version;
	public $author;
	public $author_lite;	
	public $author_id;
	public $email;
	public $date_published;
	public $date_review;
	public $location;
	public $link;
	public $link_mime;
	public $real_filename;
	public $img_status;
	public $deadline_over;
	public $acceptance;
	private $db;
	private $type_pr;
	public $which_group;
	public $which_date;  
	public $which_id;
	public $which_reference;           
	public $which_status;	   
	public $which_project;
	public $which_assignee;
	public $which_sub_project;
	public $which_review;
	public $which_type;
	public $which_baseline;
	public $search_query;
	public $order;
	public $smart_filename;
   private $test;
   public $filename;
   private $today_date;
   
   public static function update_acceptance ($data_id,$acceptance) {
	 $result = Atomik_Db::update('bug_applications',
								 array('acceptance'=>$acceptance),
								 array('id'=>$data_id));
	 return($result);
   
	}  
   public static function Get_File_Ext ($link) {
	 $extension="";
     $res = preg_match("#^\w+\.(\w+)$#", $link,$ext); 
	 //echo "TST:".$link." vers ".$ext[1];
	 if (array_key_exists(1,$ext)){
		$extension = $ext[1];
	 }
     return ($extension);
   } 
   public static function Get_File_Id ($link) {
	 $id="";
     $res = preg_match("#^(\w+)\.\w+$#", $link,$ext); 
	 //echo "TST:".$link." vers ".$ext[1];
	 if (array_key_exists(1,$ext)){
		$id = $ext[1];
	 }	 
     return ($id);
   }    
   public function Create_First_Page ($force=false) {
		$ext = Data::Get_File_Ext($this->filename);
		$id = Data::Get_File_Id($this->filename);	
	 switch ($ext) {
		case "pdf":
		case "PDF":		
			ini_set('display_errors', 1);
			$input_pdf=realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR.
								"..".DIRECTORY_SEPARATOR.
								"..".DIRECTORY_SEPARATOR.
								"..".DIRECTORY_SEPARATOR.
								"docs".DIRECTORY_SEPARATOR.$this->filename;	
			$first_page_file = "../result/".$id.".jpg";
			$exportPath=realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR.
								"..".DIRECTORY_SEPARATOR.
								"..".DIRECTORY_SEPARATOR.
								"..".DIRECTORY_SEPARATOR."result".DIRECTORY_SEPARATOR.$id.".jpg";		
			if((!file_exists($first_page_file))||($force===true)){
               if(file_exists($input_pdf)){
               	    $result = Tool::createFirstPage($input_pdf,$exportPath);
                    $img = '<img src="'.$first_page_file.'" width="480" style="margin-top:10px;margin-left:5px;">';
                    echo '<script type="text/javascript">
                    document.getElementById("messages").innerHTML = "<li class=\"success\" style=\"list-style-type: none;\">First page created.</li>";
                    </script>';
                }
                else{
                    echo '<script type="text/javascript">
                    document.getElementById("messages").innerHTML = "<li class=\"failed\" style=\"list-style-type: none;\">First page not created. Document is missing on the server.</li>";
                    </script>';
                    $img = '<img src="'.Atomik::asset('assets/images/zodiac_ppt_first_page.jpg').'" width="480" style="margin-top:10px;margin-left:5px;">';
                }
			}
            else{	
                $img = '<img src="'.$first_page_file.'" width="480" style="margin-top:10px;margin-left:5px">';
            }
			break;		
		default:
			$img=false;
			break;
	}
	return($img);
   }
	private function display_downstream_data($data_id,$fhandle){

		// global $fhandle;
		$downstream_data = new Data;
		$downstream_data_list = Data::Get_List_Downstream_Data($data_id);
		if ($downstream_data_list !== false){
			foreach ($downstream_data_list as $id) :
				$downstream_data->get($id);
				fputs($fhandle,'<node name="'.$downstream_data->type." ".$downstream_data->lru.'" id="'.$downstream_data->id.'" connectionname="" connectioncolor="#526e88" namecolor="#f" bgcolor="#d9e3ed" bgcolor2="#f" namebgcolor="#d9e3ed" namebgcolor2="#526e88" bordercolor="#526e88">');
				fputs($fhandle,$downstream_data->reference.' issue '.$downstream_data->version);
				fputs($fhandle,'</node>');
			endforeach;
		}	
	}
	private function echo_map(&$node, $selected) {
	
		// global $data_id;
		$output = "";
		$x = $node['x'];
		$y = $node['y'];
		$output .= "<a href=\"?name={$node['name']}&id={$this->id}&neighboor_id={$node['id']}\" onclick=\"window.top.window.ouvrir('".Atomik::url('edit_data',array('id'=>$node['id']))."','_blank')\">";
		$output .= "<div style=\"position:absolute;left:{$x};top:{$y};width:{$node['w']};height:{$node['h']};" . ($selected == $node['id'] ? "background-color:red;filter:alpha(opacity=40);opacity:0.4;" : "") . "\">&nbsp;</div></a>\n";
		for ($i = 0; $i < count($node['childs']); $i++) {
			$output .= $this->echo_map($node['childs'][$i], $selected);
		}
		$output .= "<a href='".Atomik::url('edit_data',array('id'=>$node['id']))."'>open</a>";
		return($output);
	}   
   public static function concatDocName($reference,
									  $version,
									  $type=""){
   		$name = $reference;
		if ($type != ""){
			$name .= ' '.$type;
		}		
		if ($version != ""){
			$name .= ' issue '.$version;
		}
		return($name);
   }
   public function createDiagram(){
		require_once 'diagram/class.diagram.php';
		require_once 'diagram/class.diagram-ext.php';

		$output = "";
		$diagram_filename = 'diagram_'.uniqid();
		$diagram_file = dirname(__FILE__).DIRECTORY_SEPARATOR.
					"..".DIRECTORY_SEPARATOR.
					"..".DIRECTORY_SEPARATOR.
					"..".DIRECTORY_SEPARATOR.
					'result'.DIRECTORY_SEPARATOR.$diagram_filename.'.xml';
		$fhandle = fopen($diagram_file,'w');
		fputs($fhandle,'<?xml version="1.0" encoding="UTF-8"?>');
		fputs($fhandle,'<diagram bgcolor="#f" bgcolor2="#d9e3ed">');

		$found_upper_data = Data::Get_List_Upper_Data($this->id,&$list_upper);
		if ($found_upper_data){    
			/* find upper data */
			foreach ($list_upper as $parent_data) {
				/* Parent document */
				fputs($fhandle,'<node name="'.$parent_data['type']." ".$parent_data['lru'].'" id="'.$parent_data['upper_data_id'].'" namecolor="#f" bgcolor="#d9e3ed" bgcolor2="#f" namebgcolor="#d9e3ed" namebgcolor2="#526e88" bordercolor="#526e88">');
				fputs($fhandle,Data::concatDocName($parent_data['reference'],$parent_data['version']));
				break;
			}  
		}
		/* current document */
		$current_doc_header = $this->type." ".$this->lru;
		fputs($fhandle,'<node name="'.$current_doc_header.'" id="'.$this->id.'" connectionname="" connectioncolor="#526e88" namecolor="#f" bgcolor="#d9e3ed" bgcolor2="#f" namebgcolor="#d9e3ed" namebgcolor2="#526e88" bordercolor="#526e88">');
		fputs($fhandle,Data::concatDocName($this->reference,$this->version));
		/* 
		 * find downstream documents 
		 */
		$downstream_data_list = Data::Get_List_Downstream_Data($this->id);
		if ($downstream_data_list !== false){
			foreach ($downstream_data_list as $id) :
				$this->get($id);
				fputs($fhandle,'<node name="'.$this->type." ".$this->lru.'" id="'.$this->id.'" connectionname="" connectioncolor="#526e88" namecolor="#f" bgcolor="#d9e3ed" bgcolor2="#f" namebgcolor="#d9e3ed" namebgcolor2="#526e88" bordercolor="#526e88">');
				fputs($fhandle,Data::concatDocName($this->reference,$this->version));
				$this->display_downstream_data($this->id,&$fhandle);
				fputs($fhandle,'</node>');
			endforeach;
		}

		fputs($fhandle,'</node>');
		if ($found_upper_data){  
		  fputs($fhandle,'</node>');
		}
		/* */    
		fputs($fhandle,'</diagram>');
		fclose($fhandle);
		$diagram = new DiagramExtended($diagram_file);
		$diagram_display = new Diagram(realpath($diagram_file));
		$diagram_png="../result/".$diagram_filename.'.png';
		$diagram_display->Draw($diagram_png);
	
		$output = '<img src="'.$diagram_png.'" border="0" style="position:absolute;left:0;top:0;" />';

		$selected = (isset($_GET['id']) ? $_GET['id'] : $id);
		$diagram_node_position = $diagram->getNodePositions();
		$output .= $this->echo_map($diagram_node_position, $selected); 
		return ($output);
   }
    public function Get_Path($link) {
     //$res = preg_match("#^(\w+)\/\w+$#", $link,$ext); 
	 $info = new SplFileInfo($link);
	  $res = $info->getPath();
	 //echo "TST:".$link." vers ".$ext[1];
     return ($res);
   }     

   public static function Get_Attachment_Id($data_id) {
   		$db = new Db;
		$query = "SELECT id FROM `data_location` WHERE `data_id` = '$data_id' ";
		$row = $db->db_query($query)->fetch(PDO::FETCH_OBJ);
		$id = $row->id;
		return($id);
	}   
   public static function Get_PRR_Attachment_Id($data_id) {
		$db = new Db;
		$query = "SELECT id FROM `peer_review_location` WHERE `data_id` = '$data_id' ";
		$row = $db->db_query($query)->fetch(PDO::FETCH_OBJ); 
		$id = $row->id;
		return($id);
	} 	
   public static function Check_Link_Validity ($data_id,$ext) {
		$db = new Db;
		$query = "SELECT * FROM  `data_location` WHERE `data_id` = '$data_id' AND `name` = '$ext'";
		$nb_links_tab = $db->db_query($query)->fetchAll();
		$nb_links=count($nb_links_tab);
		if ($nb_links == 0) {
			$valid_link=true;
		}
		else {
			$valid_link=false;
		}
		return ($valid_link);
	  } 
   public static function Check_PRR_Link_Validity ($data_id) {
		$db = new Db;
		$query = "SELECT * FROM  `peer_review_location` WHERE `data_id` = '$data_id' ";
		$nb_links_tab = $db->db_query($query)->fetchAll();
		$nb_links=count($nb_links_tab);
		if ($nb_links == 0) {
			$valid_link=true;
		}
		else {
			$valid_link=false;
		}
		return ($valid_link);
	  } 	  
	public function compute_deadline () {
	    /* remove time */
		//$cut_text = substr($this->date_expected,0,10);
		$cut_text = $this->date_review;
		/* convert from string to time format */
		$deadline_convert = strtotime($cut_text);
		$today = date("Y").date("m").date("d");
		$Jour = date("d", $deadline_convert);
		$Mois = date("m", $deadline_convert);
		$Annee = date("Y", $deadline_convert);
		$deadline = $Annee.$Mois.$Jour;
		/* a t'on depasse la deadline ?*/
		if (($today>$deadline) && ($this->status == "Under review"))  {
		    $this->deadline_over = true;
		}
		else{
		    $this->deadline_over = false;
		}
	}
	public function getDeadlineOver(){
		$this->compute_deadline();
		return($this->deadline_over);
	}	
	public static function Get_List_Downstream_Data ($update_data_id){
		$db = new Db;
		$downstream_data = false;
		$list_table_query = "SHOW TABLES FROM ".$db->db_select." LIKE '%table_upper_data%'";
		$list_table = $db->db_query($list_table_query);
		// $list_table = $result->fetchAll(PDO::FETCH_ASSOC);
		foreach ($list_table as $table) {	
			$sql_query = "SELECT * FROM ".$table[0]." WHERE upper_data_id = ".$update_data_id;
			$result = $db->db_query($sql_query);
			$list = $result->fetchAll(PDO::FETCH_ASSOC);
			if (count($list) != 0){ 
				preg_match("#table_upper_data_([0-9]{0,4})#",$table[0],$table_downstream_data);
				$downstream_data_id = $table_downstream_data[1];
				$downstream_data[] = $downstream_data_id;
			}
		}	
		return($downstream_data);
	}
	public static function Get_List_Upper_Data ($update_data_id,$list){
		$db = new Db;			
		/* Look if table exist */
		$list_table_upper_data_query = "SHOW TABLES FROM ".$db->db_select." LIKE 'table_upper_data_".$update_data_id."'";
		$result = $db->db_query($list_table_upper_data_query); 
		$nb_tab = $result->fetchAll(PDO::FETCH_ASSOC);
		// var_dump($list_table_upper_data_query);
		$nbtotal=count($nb_tab);
		if ($nbtotal != 0){    
			/* table exists */
			/* find upper data */
			 $upper_data_query = "SELECT table_upper_data_{$update_data_id}.id as link_id,".
								"upper_data_id,".
								"lrus.lru, ".
								"bug_applications.id,".
								"bug_applications.application as reference,".
								"bug_applications.description,".
								"bug_applications.version,".
								"data_cycle_type.name as type ".
								"FROM table_upper_data_{$update_data_id} ".
								"LEFT OUTER JOIN bug_applications ON bug_applications.id = table_upper_data_{$update_data_id}.upper_data_id ".
								"LEFT OUTER JOIN data_cycle_type ON data_cycle_type.id = bug_applications.type ".
								"LEFT OUTER JOIN lrus ON lrus.id = bug_applications.lru ";										
			$list = $db->db_query($upper_data_query); 
			// $list = $result->fetchAll(PDO::FETCH_ASSOC);
		}   
		else {
			$list = false;
		}
		return ($nbtotal != 0);
	}
   public function getExternalPeerReviewList(){
		$sql_query = "SELECT peer_review_location.id,".
							"name,".
							"ext,".
							"date ".
							"FROM peer_review_location ".
							"LEFT OUTER JOIN bug_applications ON bug_applications.id = data_id WHERE data_id = {$this->id}";	
		$result = $this->db->db_query($sql_query);
		$list_prr = $result->fetchAll(PDO::FETCH_OBJ);
		return($list_prr);
	}
	public static function countPeerReviews($data_id){
		Atomik::needed('Remark.class');
		$remarks = new StatRemarks;
		$remarks->setDocument($data_id);
		return($remarks->amount_remarks);
	}
	public function setLink($link){
		$this->link = $link;
	}	
	public function getAuthorEmail(){
		Atomik::needed('User.class');
		$user = new User;
		return($user->getEmail($this->author_id));
	}
    /**
     * Checks if a version of a document is a draft like 1D1.
     * 
     * @param string $version
     * @return bool
     */	
	private static function isDraft(&$version){
		$result = preg_match("#^([0-9]+)D([0-9]+)$#",$version,$draft_match_numero);
		if ($result){
			$inc_draft = isset($draft_match_numero[2]) ? $draft_match_numero[2] + 1 : "";
			$version = isset($draft_match_numero[1]) ? $draft_match_numero[1]."D".$inc_draft : "";
		}
		return($result);
	}
    /**
     * Compùute next version of a document.
     * 
     * @param string $current_version
     * @return string
     */		
	public static function getNextVersion($current_version){
		if(Data::isDraft($current_version)){
			/* version is like 1D1 */
			$new_version = $current_version;
		}
		else if(preg_match("#^[0-9]+$#",$current_version)){
			/* version is a number ? */
			/* get next number */
			$new_version = $current_version + 1;
			$new_version .= "D1";
		}
		else if(preg_match("#^[A-Za-z]+$#",$current_version)){
			/* version is a letter ? */
			/* get next letter */
			$new_version = chr(ord($current_version) + 1);
		}
		else if(preg_match("#^([0-9]+).([0-9]+)$#",$current_version,$output)){
			/* version is a float ? */
			/* get next number */
			$minor = $output[2] + 1;
			$new_version = $output[1].".".$minor;
		}		
		else{
			$new_version = "X";
		}
		return($new_version);
	}
	public function getOrder(){
		return($this->order);
	}
	public function getAllAttached(){
		$which_data = Tool::setFilterWhere("data_id",$this->id);
		$sql_query = "SELECT data_location.id,data_id,name as ext,real_name,application as reference,version FROM data_location LEFT OUTER JOIN bug_applications ON bug_applications.id = data_id {$which_data} ";
		$list = A('db:'.$sql_query);//->fetch(PDO::FETCH_ASSOC);
		return($list);
	}	
	public function __construct ($context=null) {
		if ($context!=null){
			$this->aircraft_id = isset($context['aircraft_id'])? $context['aircraft_id'] : Atomik::get('session/current_aircraft_id');
			$this->project_id = isset($context['project_id'])? $context['project_id'] : Atomik::get('session/current_project_id');
			$this->lru_id = isset($context['sub_project_id'])? $context['sub_project_id'] : (Atomik::has('session/sub_project_id')?Atomik::get('session/sub_project_id'):"");
			$this->status_id= isset($context['data_status_id'])? $context['data_status_id'] : "";	
			$this->type_id= isset($context['type_id'])? $context['type_id'] : (Atomik::has('session/type_id')?Atomik::get('session/type_id'):"");			
			$this->assignee_id= isset($context['user_id'])? $context['user_id'] : "";	
			$this->baseline_id= isset($context['baseline_id'])? $context['baseline_id'] : "";	
			$this->group_id= isset($context['group_id'])? $context['group_id'] : "";			
			$this->search= isset($context['data_search'])? $context['data_search'] : "";
			$this->order= isset($context['order'])? $context['order'] : "";
			$this->reference = isset($context['reference'])? $context['reference'] : "";
			Atomik::needed("Tool.class");
			$this->which_group 			= Tool::setFilter("data_cycle_type.group_id",$this->group_id);
			$this->which_aircraft 		= Tool::setFilter("projects.aircraft_id",$this->aircraft_id);
			$this->which_project 		= Tool::setFilter("bug_applications.project",$this->project_id);
			$this->which_sub_project 	= Tool::setFilter("bug_applications.lru",$this->lru_id);
			$this->which_type 			= Tool::setFilter("bug_applications.type",$this->type_id);
			$this->which_status 	   	= Tool::setFilter("bug_applications.status",$this->status_id);
			$this->which_assignee 		= Tool::setFilter("author_id",$this->assignee_id);
			$this->which_baseline 		= Tool::setFilter("baseline_join_data.baseline_id",$this->baseline_id);
			if ($this->reference != ""){
				$this->which_reference 		= " AND (bug_applications.application LIKE '%$this->reference%')";
			}
			else{
				$this->which_reference 		= "";
			}
			if ($this->search != ""){
				/* test if the search is regular expression */
				if(preg_match("#^REGEXP(.+)#",$this->search,$regexp)) {
					$this->search_query = " AND ((bug_applications.description REGEXP '{$regexp[1]}') OR (application REGEXP '{$regexp[1]}')) ";
				}
				else if (preg_match("#^NOTREGEXP(.+)#",$this->search,$regexp)) {
					$this->search_query = " AND ((bug_applications.description NOT REGEXP '{$regexp[1]}') AND (application NOT REGEXP '{$regexp[1]}')) ";
				}
				else {
					/* plain expression */
					/* check description, reference, keywords and id field */
					$this->search_query = " AND ((bug_applications.description LIKE '%$this->search%') OR ".
												"(application LIKE '%$this->search%') OR ".
												"(keywords LIKE '%$this->search%') OR ".
												"(bug_applications.id LIKE '%$this->search%')) ";
				}
			}
			else {
				$this->search_query="";
			}
		}
		else{
			$this->aircraft_id = "";
			$this->project_id = "";
			$this->lru_id = "";
			$this->status_id= "";	
			$this->type_id= "";			
			$this->assignee_id= "";	
			$this->baseline_id= "";	
			$this->group_id= "";			
			$this->search= "";
			$this->order= "";
			$this->reference = "";		
		}
	   $this->today_date = date("d").' '.date("M").' '.date("Y");	
	   Atomik::needed('Db.class');
	   $this->db = new Db;	
		$this->id=1237;
		$this->project="";  
		$this->lru="";	 
		$this->full_ident="";		
		$this->previous_data_id="";
		$today_date = date("Y")."/".date("m")."/".date("d");
		$this->date_published="undefined";
		$this->date_published_sql = $today_date;
		$this->date_review="undefined";
		$this->date_review_sql = $today_date;
		$this->peer_review_requested=true;
		$this->baseline="";		
		$this->version="";
		$this->type="";
		$this->password="";		
		$this->description = "";
		$this->abstract = "";	
		Atomik::needed("User.class");
		$this->author = User::getNameUserLogged();
		$this->author_lite = "";
		$this->author_id = User::getIdUserLogged();
		$this->email = "";
		$this->location ="";
		$this->peer_review = "";
		$this->filename = "1237.pdf";
		$this->smart_filename = "";
		$this->extension = "pdf";
		$this->link = "";
		$this->link_mime = "";	  
		$this->acceptance = "";
		$this->group_id  = "";
		$this->type_pr = false;			
   }
   	public function prepare($all=""){
		$sql_query = $this->sort_data($all)."  LIMIT :debut,:nombre";  //id ASC id ASC
		unset($this->prepare);
		$this->prepare = $this->db->db_prepare($sql_query);	
	}
	public function execute($start,$how_many){
		$this->prepare->bindParam(':debut', $start, PDO::PARAM_INT);
		$this->prepare->bindParam(':nombre', $how_many, PDO::PARAM_INT);
		$this->prepare->execute(); //array($start,$how_many)
		$list = $this->prepare->fetchAll(PDO::FETCH_ASSOC);
		return($list);
	}
	public function getData($type=PDO::FETCH_ASSOC){
		$sql_query = $this->sort_data();
		// echo $sql_query;
		$result = $this->db->db_query($sql_query);
		$list_data = $result->fetchAll($type);
		return($list_data);
	}
	public function createMinutes($project,
									$equipment){
		Atomik::needed('User.class');
		$new_data_id = $this->add_application($project,
											 "", /* no reference */
											 1, /* version */
											 $equipment,
											 28, /* Minutes Of the Meeting type */
											 "", /* No description */
											 "", /* No abstract */
											 11, /* Status new */
											 "", /* No baseline */
											 "",
											 "", /* No location */
											 "", /* No peer review */
											 "", /* No review deadline */
											 User::getIdUserLogged(),/* Author of the MoM is the user logged */
											 "");
		return($new_data_id);
	}
	public function createMemo($project,
								$equipment){
		Atomik::needed('User.class');
		$new_data_id = $this->add_application($project,
											 "", /* no reference */
											 1, /* version */
											 $equipment,
											 56, /* Memo type */
											 "", /* No description */
											 "", /* No abstract */
											 11, /* Status new */
											 "", /* No baseline */
											 "",
											 "", /* No location */
											 "", /* No peer review */
											 "", /* No review deadline */
											 User::getIdUserLogged(),/* Author of the MoM is the user logged */
											 "");
		return($new_data_id);
	}
   private function createReference($data_id,$type=""){
   	   /* create reference */
		$name_type = "";
		if ($type != "") {
			$result = Atomik_Db::find("data_cycle_type","id = {$type}",null,0,"data_cycle_type.name");
			$name_type = $result['name'];
		}
		$user = new User;
		$user->get_user_info(User::getIdUserLogged());
		if ($name_type != "") {
			$name = "DQ-{$user->acronym}-{$name_type}-".sprintf("%1$06d",$data_id)."-".date("y");
		}
		else {
			$name = "DQ-{$user->acronym}-".sprintf("%1$06d",$data_id)."-".date("y");
		}
		return($name);
   }	
   /* This function update an application for users to select from */
   public function update_application($project,
										$name, 
										$version, 
										$equipment,
										$type,
										$description,
										$abstract,
										$status,
										$baseline,
										$date_dojo,
										$location,
										$peer_review,
										$date_review,
										$author,
										$update_id,
										$previous_data_id,
										$keywords="",
										$priority_id=16
										) {
	   if ($location == ""){
			$location="undefined";
	   }
	   if ($description == ""){
			$description = $this->type;
	   }
	   $date = Date::new_convert_dojo_date ($date_dojo);
	   $date_review = Date::new_convert_dojo_date ($date_review);
	   $result = $this->db->update("bug_applications",
									array("project"=>$project,
										  "lru"=>$equipment,
										  "previous_data_id"=>$previous_data_id,
										  "application"=>$name,
										  "version"=>$version,
										  "type"=>$type,
										  "description"=>$description,
										  "abstract"=>$abstract,
										  "status"=>$status,
										  "date_published"=>$date ,
										  "location"=>$location,
										  "author_id"=>$author,
										  "peer_review"=>$peer_review,
										  "date_review"=>$date_review,
										  "keywords"=>$keywords,
										  "priority_id"=>$priority_id
										  ),array('id' => $update_id));
	   return($result);
   }
   public function add_application($project,
									$name, 
									$version, 
									$equipment,
									$type,
									$description,
									$abstract,
									$status,
									$date_dojo,
									$location,
									$peer_review,
									$date_review,
									$author_id,
									$previous_data_id,
									$keywords="",
									$priority_id=16) {

      /* status = not validated, New (11) by default */
	  $status = 11;
      if ($location == "") {
               $location="undefined";
	  }
      if ($date_dojo == "")    {
         // today date
         $date  = date("Y")."/".date("m")."/".date("d");
      }
      else {
         $date = Date::new_convert_dojo_date ($date_dojo);
      }
      if ($date_review == "")    {
         // today date
         $date_review  = date("Y")."/".date("m")."/".date("d");
      }
      else {
         $date_review = Date::new_convert_dojo_date ($date_review);
      }   
      /* Add data */
	$project = ($project != NULL)?$project:"";	  
	$equipment = ($equipment != NULL)?$equipment:"";
	$values =	array("project"=>$project,
					  "lru"=>$equipment,
					  "previous_data_id"=>$previous_data_id,
					  "application"=>$name,
					  "version"=>$version,
					  "type"=>$type,
					  "description"=>$description,
					  "abstract"=>$abstract,
					  "status"=>$status,
					  "date_published"=>$date ,
					  "location"=>$location,
					  "author_id"=>$author_id,
					  "peer_review"=>$peer_review,
					  "date_review"=>$date_review,
					  "keywords"=>$keywords,
					  "priority_id"=>$priority_id
					  );
	  $new_data_id = $this->db->db_insert("bug_applications",$values);
	  // var_dump($values);
	   // echo "ADD_DATA";exit();
	   /* update table of last data */
	   if (Atomik_Db::find('data_last',array('reference'=>$name))){
			Atomik_Db::update('data_last',array('data_id'=>$new_data_id),array('reference'=>$name));
	   }
	   else{
			/* first version */
			Atomik_Db::insert('data_last',array('data_id'=>$new_data_id,'reference'=>$name));
	   }
	    if ($new_data_id) {
			ob_start("manage_log");
			$text = "New data {$new_data_id} added by ".User::getNameUserLogged()." at ".date('H:i:s')." on ".date('Y/m/d');
			echo $text;
			ob_end_clean();
		   /* create reference */
		   if ($name == "") {	  
				$name = Data::createReference($new_data_id,$type);
				Data::update_reference($new_data_id,$name);
				$this->get($new_data_id);				
		   }
	   }
		return ($new_data_id);
   }   
    private static function	update_reference ($data_id,
												$reference) {
		 $result = Atomik_Db::update('bug_applications',array('application'=>$reference),array('id'=>$data_id));
		 return($result);
	} 
    public static function	updateStatus ($data_id,
										  $status_id) {
		 $result = Atomik_Db::update('bug_applications',array('status'=>$status_id),array('id'=>$data_id));
		 return($result);
	} 	 
   public function getValidReference(){
   		$issue = "";
		if ($this->version != ""){
			$issue = "_issue_".$this->version;
		}
		$eqpt = "";
		if ($this->lru != ""){
			$eqpt = $this->lru."_";
		}		
		$smart_filename = $this->project."_".$eqpt.$this->reference."_".$this->type.$issue.".".$this->extension;
		$smart_filename = str_replace("/","_",$smart_filename);
		$smart_filename = str_replace(" ","_",$smart_filename);
		return($smart_filename);
   }
   public function getBaseline(){
   		$sql_query = "SELECT baselines.description,".
					" baselines.id,".
					" baseline_join_data.id as link_data_id,".
					" projects.project,".
					" projects.id as project_id,".
					" lrus.lru, ".
					" lrus.id as lru_id ".
					" FROM baselines ".
					" LEFT OUTER JOIN baseline_join_data ON baseline_join_data.baseline_id = baselines.id".
					" LEFT OUTER JOIN baseline_join_project ON baseline_join_project.baseline_id = baselines.id".
					" LEFT OUTER JOIN projects ON baseline_join_project.project_id = projects.id".
					" LEFT OUTER JOIN lrus ON baseline_join_project.lru_id = lrus.id".
					" WHERE data_id = {$this->id} ORDER BY date DESC";
		$result = $this->db->db_query($sql_query);
		$list = $result->fetchAll();
		return($list);
   }
   public static function getHotPeerReview ($aircraft_id="",$project_id=""){
		$where_project = Tool::setFilter("projects.id",$project_id);
		$where_aircraft = Tool::setFilter("aircrafts.id",$aircraft_id); 
		 $sql = "SELECT bug_applications.id as id, ".
					"projects.project, ".
					"lrus.lru, ".
					"bug_applications.application as reference, ".
					"bug_applications.author_id, ".
					"bug_applications.date_review as date_review_sql, ".
					"bug_applications.description, ".
					"bug_applications.abstract, ".
					"bug_applications.version, ".
					"bug_applications.project as project_id, ".
					"bug_applications.lru as lru_id, ".
					"bug_applications.status as status_id, ".
					"bug_applications.date_published, ".
					"bug_applications.date_review, ".
					"data_cycle_type.name as type, ".
					"data_cycle_type.description as type_description, ".
					"bug_users.id as author_id, ".
					"bug_users.fname as author_fname, ".
					"bug_users.lname as author_lname, ".
					"bug_users.email as author_email, ".
					"bug_status.name as status ".
					"FROM bug_applications ".
					"LEFT OUTER JOIN lrus ON lrus.id = bug_applications.lru ".
					"LEFT OUTER JOIN data_location ON data_location.data_id = bug_applications.id ".
					"LEFT OUTER JOIN bug_users ON bug_applications.author_id = bug_users.id ".
					"LEFT OUTER JOIN projects ON projects.id = bug_applications.project ".
					"LEFT OUTER JOIN aircrafts ON aircrafts.id = projects.aircraft_id ".
					"LEFT OUTER JOIN data_cycle_type ON data_cycle_type.id = bug_applications.type ".
					"LEFT OUTER JOIN bug_status ON bug_status.id = bug_applications.status ".
					"WHERE ((bug_status.id = 10) OR (bug_status.id = 11) OR (bug_status.id = 12))  AND data_cycle_type.review = '1' AND date_review NOT LIKE '0000-00-00'".
					$where_project.
					$where_aircraft.
				 " GROUP BY bug_applications.id ORDER BY priority_id DESC, date_review ASC, date_published DESC,version ASC";	 
		 $data_list = A("db:".$sql);   
		 return($data_list);
   }
   public static function getLastRead ($user_id=""){
		$filter = Tool::setFilter("last_data_read.user_id",$user_id); 
		 $sql = "SELECT bug_applications.id as id, ".
					"projects.project, ".
					"lrus.lru, ".
					"bug_applications.application as reference, ".
					"bug_applications.author_id, ".
					"bug_applications.date_review as date_review_sql, ".
					"bug_applications.description, ".
					"bug_applications.abstract, ".
					"bug_applications.version, ".
					"bug_applications.project as project_id, ".
					"bug_applications.lru as lru_id, ".
					"bug_applications.status as status_id, ".
					"bug_applications.date_published, ".
					"bug_applications.date_review, ".
					"data_cycle_type.name as type, ".
					"data_cycle_type.description as type_description, ".
					"bug_users.id as author_id, ".
					"bug_users.fname as author_fname, ".
					"bug_users.lname as author_lname, ".
					"bug_users.email as author_email, ".
					"bug_status.name as status ".
					"FROM bug_applications ".
					"LEFT OUTER JOIN lrus ON lrus.id = bug_applications.lru ".
					"LEFT OUTER JOIN data_location ON data_location.data_id = bug_applications.id ".
					"LEFT OUTER JOIN bug_users ON bug_applications.author_id = bug_users.id ".
					"LEFT OUTER JOIN projects ON projects.id = bug_applications.project ".
					"LEFT OUTER JOIN data_cycle_type ON data_cycle_type.id = bug_applications.type ".
					"LEFT OUTER JOIN bug_status ON bug_status.id = bug_applications.status ".
					"RIGHT OUTER JOIN last_data_read ON last_data_read.data_id = bug_applications.id ".
					"WHERE TO_DAYS(NOW()) - TO_DAYS(read_date) <= 30 {$filter}".
				 " GROUP BY bug_applications.id ORDER BY read_date DESC LIMIT 0,10";	 
		 $data_list = A("db:".$sql);   
		 return($data_list);
   }   
   public function get ($id="") {
	  $result = false;
      if ($id != "") {
		$sql_query = "SELECT bug_applications.id as id, ".
							"bug_applications.previous_data_id, ".
							"bug_applications.application, ".
							"bug_applications.author_id, ".
							"bug_applications.date_review as date_review_sql, ".
							"bug_applications.description, ".
							"bug_applications.abstract, ".
							"bug_applications.version, ".
							"bug_applications.project as project_id, ".
							"bug_applications.lru as lru_id, ".
							"bug_applications.status as status_id, ".
							"bug_applications.priority_id, ".
							"bug_applications.password, ".
							"bug_applications.location, ".
							"bug_applications.peer_review, ".
							"bug_applications.date_published, ".
							"bug_applications.date_review, ".
							"bug_applications.acceptance, ".
							"bug_applications.keywords, ".
							"projects.project, ".
							"lrus.lru, ".
							"bug_status.name as status, ".								
							"data_cycle_type.name as type, ".
							"data_cycle_type.id as type_id, ".
							"data_cycle_type.description as type_description , ".
							"data_cycle_type.review, ".
							"data_cycle_type.group_id, ".
							"baseline_join_data.baseline_id as baseline, ".
							"bug_users.id as author_id, ".
							"bug_users.fname as author_fname, ".
							"bug_users.lname as author_lname, ".
							"bug_users.email as author_email, ".
							"data_location.id as uploaded_id, ".
							"data_location.name as extension, ".
							"data_location.real_name ".							
							"FROM bug_applications ".
							"LEFT OUTER JOIN lrus ON lrus.id = bug_applications.lru ".
							"LEFT OUTER JOIN data_location ON data_location.data_id = bug_applications.id AND data_location.name LIKE 'pdf'".
							"LEFT OUTER JOIN baseline_join_data ON bug_applications.id = baseline_join_data.data_id ".
							"LEFT OUTER JOIN bug_users ON bug_applications.author_id = bug_users.id ".
							"LEFT OUTER JOIN projects ON projects.id = bug_applications.project ".
							"LEFT OUTER JOIN data_cycle_type ON data_cycle_type.id = bug_applications.type ".
							"LEFT OUTER JOIN bug_status ON bug_status.id = bug_applications.status ".
							"WHERE bug_applications.id = {$id} ";
				
		  // $result= $this->db->db_query($sql_query);
		  $row = A('db:'.$sql_query)->fetch(PDO::FETCH_ASSOC);
		  if ($row != false){
			  $result = true;
			  $this->id=$id;
			  $this->peer_review_requested=$row['review'];
			  $this->previous_data_id=$row['previous_data_id'];
			  $this->baseline=$row['baseline'];
			  $this->project=$row['project'];
			  $this->project_id=$row['project_id'];	  
			  $this->lru=$row['lru'];
			  $this->lru_id=$row['lru_id'];	  
			  $this->reference=$row['application'];
			  $this->version=$row['version'];
			  $this->type=$row['type'];
			  $this->type_id=$row['type_id'];
			  $this->password=$row['password'];
			  Atomik::needed("Tool.class");
			  $description = Tool::cleanDescription($row['description']);
			  if ($description != ""){
					$this->description = $row['description'];
				}
				else{
					$this->description = $row['type_description'];
				}
			  $this->abstract = $row['abstract'];
			  if ($this->version != "") {
				$this->full_ident = $this->project." ".$this->lru."<br/>".$this->reference." ".$this->type." issue ".$this->version." ".$this->description;      
				$this->small_ident = $this->project." ".$this->lru." ".$this->reference." ".$this->type." issue ".$this->version;
				$this->email_subject = $this->project." ".$this->lru." ".$this->reference." ".$this->type." issue ".$this->version;
			  }
			  else {
				$this->full_ident = $this->project." ".$this->lru."<br/>".$this->reference." ".$this->type." ".$this->description;   
				$this->small_ident = $this->project." ".$this->lru." ".$this->reference." ".$this->type;
				$this->email_subject = $this->project." ".$this->lru." ".$this->reference." ".$this->type;		
			  }
			  $this->status=$row['status'];
			  $this->status_id=$row['status_id'];
			  $this->priority_id=$row['priority_id'];
			  $this->author = $row['author_fname']." ".$row['author_lname'];
			  $this->author_lite = User::getLiteName($row['author_fname'],$row['author_lname']);
			  $this->author_id=($row['author_id']!=0)?$row['author_id']:"";
			  $this->email = $row['author_email']."?subject=".$this->email_subject."&body=Peer Review";
			  $today_date = date("Y")."-".date("m")."-".date("d");
			  Atomik::needed("Date.class");
			  if ($row['date_published'] != "0000-00-00") {
					$this->date_published_sql = $row['date_published'];
					/* Convert date to display nicely */
					$this->date_published=Date::convert_date_conviviale ($this->date_published_sql);
			  }
			  else {
					$this->date_published="undefined";
					$this->date_published_sql = $today_date;
			  }
			  if ($row['date_review_sql'] != "0000-00-00") {
					$this->date_review_sql = $row['date_review_sql'];
					/* Convert date to display nicely */
					$this->date_review=Date::convert_date_conviviale ($this->date_review_sql);
			  }
			  else {
					$this->date_review="undefined";
					$this->date_review_sql = $today_date;
			  }       
			  $this->location = $row['location'];
			  $this->keywords = $row['keywords'];
			  if ($this->location == "") {
					$this->location = "undefined";
			  }
			  $this->peer_review = $row['peer_review'];
			  if ($this->peer_review == "") {
					$this->peer_review = "undefined";
			  }
			  Atomik::needed('Tool.class');
			  /* is there a PDF available ? */
			  if ($row['uploaded_id'] != ""){
					$this->filename = $row['uploaded_id'].".".$row['extension'];
					$this->extension = $row['extension'];
					$this->link = Tool::Get_Filename($row['uploaded_id'],$row['extension']);
			  }
			  else{
					$sql = "SELECT id as uploaded_id,name as extension FROM data_location WHERE data_location.data_id = {$this->id} LIMIT 0,1";
					$attach_file = A("db:".$sql)->fetch();
			  		$this->filename = $attach_file['uploaded_id'].".".$attach_file['extension'];
					$this->extension = $attach_file['extension'];
					$this->link = Tool::Get_Filename($attach_file['uploaded_id'],$attach_file['extension']);	
			  }
			  $this->link_mime = Tool::Get_Mime($this->link);
			  $this->smart_filename =  $this->getValidReference();
			  $this->acceptance = $row['acceptance'];
			  $this->group_id  = $row['group_id'];
			  $this->real_filename  = $row['real_name'];
			  if (($this->type == "EPR") || 
					($this->type == "DCR") ||
					($this->type == "HPR") || 
					($this->type == "SPR") || 					
					($this->type == "JCR")){
				  /* PR */
				  $this->type_pr = true;
			   }
			   else {
				  /* data */
				  $this->type_pr = false;
			   }
			  
		}
		else{
			$result = false;
		}
	}
	else{
		$result = false;
	}
	return($result);
   }	
   public function isPr(){
   		return($this->type_pr);
   }
   public function getLastIssue(){
		$found_data = Atomik_Db::find('data_last',array('data_id'=>$this->id));
		return($found_data);
   }
   public static function getPriorityList(){
		$sql_query = "SELECT level,name FROM bug_criticality WHERE type LIKE 'data' ";
		$list = A("db:".$sql_query);
		return($list);		
   }
	public static function getPreviousDataList($project_id,$type_id){
		$which_project = Tool::setFilter("bug_applications.project",$project_id);
		$which_type = Tool::setFilter("data_cycle_type.id",$type_id,true );
		$sql_query = "SELECT DISTINCT (bug_applications.id),".
										"date_published,".
										"bug_applications.application as reference,".
										"version,".
										"bug_applications.description, ".
										"data_cycle_type.name as type,".
										"data_cycle_type.description as type_description ".
										"FROM bug_applications ".
										"LEFT OUTER JOIN data_cycle_type ON bug_applications.type = data_cycle_type.id ".
										$which_type.
										$which_project.
										" ORDER BY reference DESC, version DESC, date_published DESC";
					
		$list = A("db:".$sql_query);
		return($list);
	}
	public static function getUpperDataList($project_id,$group_id){
		$which_project = Tool::setFilter("bug_applications.project",$project_id);
		switch ($group_id){
			case 8:
				/* for design document enable specification and interface documents and PR */
				$which_group = " AND (group_type.id = 8 OR group_type.id = 1 OR group_type.id = 3 OR group_type.id = 9) ";
				break;
			case 1:
			case 3:
				/* for specifications enable PR and interfaces too */
				$which_group = " AND (group_type.id = 1  OR group_type.id = 3 OR group_type.id = 9) ";	
				break;
			default:
				$which_group = ($group_id != "") ? " AND group_type.id = {$group_id} " : "";
				break;
		}
		$sql_query = "SELECT DISTINCT(bug_applications.id),".
									"bug_applications.application as reference,".
									"bug_applications.version,".
									"bug_applications.description,".
									"data_cycle_type.description as type_description, ".
									"data_cycle_type.name as type, ".
									"lrus.lru ".
									 "FROM bug_applications ".
									 "LEFT OUTER JOIN lrus ON lrus.id = bug_applications.lru ".	
									 "LEFT OUTER JOIN data_cycle_type ON data_cycle_type.id = bug_applications.type ".	
									 "LEFT OUTER JOIN group_type ON group_id = group_type.id ".
									 " WHERE bug_applications.id IS NOT NULL ".
									 $which_project.
									 $which_group.
									 " ORDER BY `bug_applications`.`application` ASC, `data_cycle_type`.`name` ASC ";
					
		$list = A("db:".$sql_query);
		return($list);
	}	
	public function sort_data($all=""){
		if(($all == "yes")||($this->which_reference != "")){
			$only_last_issue="";
		}
		else {
			$only_last_issue = "LEFT OUTER JOIN data_last ON data_last.data_id = list_docs.id ";  /* avant RIGHT */
		}					
		$baseline_query = "baseline_join_data.baseline_id as baseline, ";
		$baseline_query .= "LEFT OUTER JOIN baseline_join_data ON baseline_join_data.data_id = list_docs.id ";
		$baseline_query .= " GROUP BY baseline_join_data.data_id ";
		$baseline_query .= $this->which_baseline;
		if($this->which_reference == ""){
			$sub_query = "SELECT application, MAX(date_published) AS max_sup FROM bug_applications ".
						"LEFT OUTER JOIN baseline_join_data ON baseline_join_data.data_id = bug_applications.id ".
						"LEFT OUTER JOIN projects ON (projects.id = bug_applications.project OR bug_applications.project=0) ".
						"WHERE bug_applications.id IS NOT NULL ".
					   $this->which_id.
					   $this->which_assignee.
					   $this->which_reference.  
					   $this->which_baseline.				   
					   $this->which_status.	
					   $this->which_aircraft.				   
					   $this->which_project.
					   $this->which_sub_project.
					   $this->which_type.
					   $this->search_query.
						"GROUP BY application ";
			$sql = "SELECT ".
					"list_docs.application,".
					"list_docs.id as id, ".
					"list_docs.previous_data_id, ".
					"list_docs.author_id, ".
					"list_docs.date_review as date_review_sql, ".
					"list_docs.description, ".
					"list_docs.abstract, ".
					"list_docs.version, ".
					"list_docs.project as project_id, ".
					"list_docs.lru as lru_id, ".
					"list_docs.status as status_id, ".
					"list_docs.password, ".
					"list_docs.location, ".
					"list_docs.peer_review, ".
					"list_docs.date_published, ".
					"list_docs.acceptance, ".
					"projects.project, ".
					"projects.aircraft_id, ".
					"lrus.lru, ".
					"bug_status.name as status, ".
					"data_cycle_type.name as type, ".
					"data_cycle_type.id as type_id, ".
					"data_cycle_type.description as type_description, ".
					"data_cycle_type.review, ".
					"data_cycle_type.group_id, ".
					"bug_users.fname as author_fname, ".
					"bug_users.lname as author_lname, ".
					"bug_users.email as author_email, ".
					"data_location.id as uploaded_id, ".
					"data_location.name as extension ".
					"FROM bug_applications list_docs ".
					"INNER JOIN ({$sub_query}) t2 ON t2.application = list_docs.application AND t2.max_sup = list_docs.date_published ".
					$only_last_issue.
					"LEFT OUTER JOIN lrus ON lrus.id = list_docs.lru ".
					"LEFT OUTER JOIN data_location ON data_location.data_id = list_docs.id AND data_location.name LIKE 'pdf' ".
					"LEFT OUTER JOIN bug_users ON bug_users.id = list_docs.author_id ".
					"LEFT OUTER JOIN projects ON projects.id = list_docs.project ".
					"LEFT OUTER JOIN data_cycle_type ON data_cycle_type.id = list_docs.type ".
					"LEFT OUTER JOIN bug_status ON bug_status.id = list_docs.status ".
					"WHERE list_docs.id IS NOT NULL ".
					$this->which_group.
					" ORDER BY {$this->getOrder()} list_docs.application,  application ASC, version ASC";							
		}
		else{
			$sql = "SELECT ".
					"bug_applications.application,".
					"bug_applications.id as id, ".
					"bug_applications.previous_data_id, ".
					"bug_applications.author_id, ".
					"bug_applications.date_review as date_review_sql, ".
					"bug_applications.description, ".
					"bug_applications.abstract, ".
					"bug_applications.version, ".
					"bug_applications.project as project_id, ".
					"bug_applications.lru as lru_id, ".
					"bug_applications.status as status_id, ".
					"bug_applications.password, ".
					"bug_applications.location, ".
					"bug_applications.peer_review, ".
					"bug_applications.date_published, ".
					"bug_applications.acceptance, ".
					"projects.aircraft_id, ".					
					"projects.project, ".
					"lrus.lru, ".
					"bug_status.name as status, ".
					"data_cycle_type.name as type, ".
					"data_cycle_type.id as type_id, ".
					"data_cycle_type.description as type_description, ".
					"data_cycle_type.review, ".
					"data_cycle_type.group_id, ".
					"bug_users.fname as author_fname, ".
					"bug_users.lname as author_lname, ".
					"bug_users.email as author_email, ".
					"data_location.id as uploaded_id, ".
					"data_location.name as extension ".
					"FROM bug_applications ".
					"LEFT OUTER JOIN lrus ON lrus.id = bug_applications.lru ".
					"LEFT OUTER JOIN data_location ON data_location.data_id = bug_applications.id AND data_location.name LIKE 'pdf' ".
					"LEFT OUTER JOIN bug_users ON bug_users.id = bug_applications.author_id ".
					"LEFT OUTER JOIN projects ON projects.id = bug_applications.project ".
					"LEFT OUTER JOIN data_cycle_type ON data_cycle_type.id = bug_applications.type ".
					"LEFT OUTER JOIN bug_status ON bug_status.id = bug_applications.status ".
					"LEFT OUTER JOIN baseline_join_data ON baseline_join_data.data_id = bug_applications.id ".
					"WHERE bug_applications.id IS NOT NULL ".
					   $this->which_id.
					   $this->which_assignee.
					   $this->which_reference.  
					   $this->which_baseline.				   
					   $this->which_status.	
					   $this->which_aircraft.
					   $this->which_project.
					   $this->which_sub_project.
					   $this->which_type.
					   $this->search_query.				
					   $this->which_group.
					" ORDER BY {$this->getOrder()} application ASC, version ASC";						   			
		}	
		// echo $sub_query."<br/><br/>";
		// echo $sql."<br/><br/>";exit();
		return($sql);	
	}			
	public function count_data($all="") {
		if($all == "yes"){
			$only_last_issue="";
		}
		else {
			$only_last_issue = " GROUP BY bug_applications.application ";
		}		
		$sql_query = "SELECT ".
					"bug_applications.application ".
					"FROM bug_applications ".
					"LEFT OUTER JOIN lrus ON lrus.id = bug_applications.lru ".
					"LEFT OUTER JOIN data_location ON data_location.data_id = bug_applications.id ".
					"LEFT OUTER JOIN baseline_join_data ON bug_applications.id = baseline_join_data.data_id ".
					"LEFT OUTER JOIN bug_users ON bug_applications.author_id = bug_users.id ".
					"LEFT OUTER JOIN projects ON projects.id = bug_applications.project ".
					"LEFT OUTER JOIN data_cycle_type ON data_cycle_type.id = bug_applications.type ".
					"LEFT OUTER JOIN bug_status ON bug_status.id = bug_applications.status ".
					"WHERE bug_applications.id IS NOT NULL ".
				   $this->which_group.
				   $this->which_status.
				   $this->which_type.
				   $this->which_aircraft.
				   $this->which_project.
				   $this->which_sub_project.
				   $this->which_assignee.
				   $this->which_baseline.
				   $this->which_reference.
				   $this->search_query;
					// " AND bug_applications.id IN (".
									 // "SELECT id FROM bug_applications X WHERE bug_applications.application LIKE X.application AND `version` REGEXP '^[0-9]+$' ORDER BY X.date_published DESC".
									 // ")".	
				   if ($this->which_reference == ""){
				  	 $sql_query .= " GROUP BY bug_applications.application ";
				   }
				   $sql_query .= " ORDER BY bug_applications.application, bug_applications.date_published DESC";			
		// echo $sql_query;
		$result = $this->db->db_query($sql_query)->fetchAll(PDO::FETCH_ASSOC);

		$nb = count($result);
		/* create database qams */
$sql_query = <<<____SQL
		SELECT t1.id,t1.application,t1.date_published
		FROM bug_applications t1
		INNER JOIN
		(
			SELECT application, MAX(date_published) AS max_sup
			FROM bug_applications
			GROUP BY application
		) t2 
			ON t2.application = t1.application
			AND t2.max_sup = t1.date_published
		WHERE author_id = 54
____SQL;
	    return($nb);	
	}
	/**
	 * Get color of the status
	 *
	 * @return color
	 */
	public function getStatusColor () {
		Atomik::needed('Remark.class');
		$color= StatRemarks::getStatusColor($this->type,
											$this->status_id,
											"");
		return ($color);
	}	

   public static function upload ($filename,$info){
		$uploadSize = $_FILES['filename']['size'];  // The size of our uploaded file
		$uploadType = $_FILES['filename']['type'];  // The type of the file.

		$src_path = dirname(__FILE__).DIRECTORY_SEPARATOR.
				"..".DIRECTORY_SEPARATOR.
				"..".DIRECTORY_SEPARATOR.
				"..".DIRECTORY_SEPARATOR.$info['location'];	
		$_filename = basename( $_FILES['filename']['name']);
		$uploadName = $src_path.$filename;
		//echo "T:".$extensionFichier."File uploaded:".$filename."<br/>";
		if ($uploadSize < $info['maxSize']) {              // Make sure the file size isn't too big.
		   move_uploaded_file($_FILES['filename']['tmp_name'], $uploadName);   // save file.
		   $info['error'] = "Document uploaded with success !"; 
		   $info['upload_status'] = "success";			   
		}
		return($uploadName);
	}	
	public static function getStatusList($type="data"){
		$list = Atomik_Db::findAll('bug_status',"`type` = '{$type}'","`name` ASC");
		return($list);		
	}	
	public function getSelectStatus($selected,$onchange="inactive",$type="data"){
		$html ='<label for="show_status">Status:</label>';
		$html.='<select class="combobox"';
		if ($onchange=="active") {
			$html .= 'onchange="this.form.submit()"';
		}
		$html.= ' name="show_status">';
		$html.='<option value=""/> --All--';
		foreach(Data::getStatusList($type) as $row):
			$html .= '<option value="'.$row['id'].'"';
			if ($row['id'] == $selected){ 
				$html .= " SELECTED ";
			}
			$html .=">".$row['name'];
		endforeach;
		$html .='</select>';
		return($html);		
	}
	public static function getType($id){
		$item = Atomik_Db::find("data_cycle_type","id = ".$id);
		return($item);
	}
	public static function getTypeList($order='group',$group_id=""){
		$which_group = Tool::setFilter("data_cycle_type.group_id",$group_id);
		if ($order == 'group'){
			$which_order = "ORDER BY `group_id` ASC,`data_cycle_type`.`name` ASC";
		}
		else{
			$which_order = "ORDER BY `data_cycle_type`.`name` ASC,`group_id` ASC";
		}
		$sql_query = "SELECT data_cycle_type.id, ".
					"data_cycle_type.name, ".
					"data_cycle_type.comment, ".
					"group_type.name as group_name, ".
					"description,group_id ".
					"FROM data_cycle_type ".
					"LEFT OUTER JOIN group_type ON group_type.id = data_cycle_type.group_id ".
					"WHERE data_cycle_type.id != 'NULL' ".
					$which_group.
					$which_order;
		$list = A('db:'.$sql_query);
		return($list);		
	}
	public static function combo_box_type_query () {
		$sql_query = "SELECT data_cycle_type.id, ".
				"data_cycle_type.name, ".
				"group_type.name as group_name, ".
				"description,group_id ".
				"FROM data_cycle_type ".
				"LEFT OUTER JOIN group_type ON group_type.id = data_cycle_type.group_id ".
				"ORDER BY `data_cycle_type`.`group_id` ASC,`data_cycle_type`.`name` ASC";
		return(A("db:".$sql_query));	
	}	
	public static function getDataList($project_id,$sub_project_id){
		$which_project 		= Tool::setFilter("projects.id",$project_id);
		$which_sub_project 	= Tool::setFilter("lrus.id",$sub_project_id);	
		$sql_query = "SELECT bug_applications.id as id, ".
            "projects.project, ".
            "bug_applications.application, ".
            "bug_applications.version, ".
			"bug_applications.project as project_id, ".
			"bug_applications.lru as lru_id, ".
            "lrus.lru, ".
            "data_cycle_type.name as type ".
            "FROM bug_applications ".
			"LEFT OUTER JOIN lrus ON lrus.id = bug_applications.lru ".
            "LEFT OUTER JOIN projects ON projects.id = bug_applications.project ".
			"LEFT OUTER JOIN data_cycle_type ON data_cycle_type.id = bug_applications.type ".
            "WHERE bug_applications.id != 'NULL' ".		   
			   $which_project.
			   $which_sub_project.
         " GROUP BY bug_applications.id ORDER BY application ASC, date_published DESC,version ASC";			     	   	
		// echo $sql_query;
		return(A("db:".$sql_query));		
	}
	public static function getReferenceList($project_id,
											$sub_project_id){
		$which_project 		= Tool::setFilter("projects.id",$project_id);
		$which_sub_project 	= Tool::setFilter("lrus.id",$sub_project_id);
		$sql_query = "SELECT bug_applications.application as reference, ".
            "bug_applications.id as id, ".
            "bug_applications.version, ".
			"bug_applications.description, ".
			"bug_applications.project as project_id, ".
			"bug_applications.lru as lru_id, ".
			"projects.project, ".
            "lrus.lru, ".
            "data_cycle_type.name as type ".
            "FROM bug_applications ".
			"LEFT OUTER JOIN lrus ON lrus.id = bug_applications.lru ".
            "LEFT OUTER JOIN projects ON projects.id = bug_applications.project ".
			"LEFT OUTER JOIN data_cycle_type ON data_cycle_type.id = bug_applications.type ".
            "WHERE bug_applications.id != 'NULL' ".
			   $which_project.
			   $which_sub_project.
         " GROUP BY reference ORDER BY application ASC, date_published DESC,version ASC";			     	   	
		// echo $sql_query;
		return(A("db:".$sql_query));		
	}		
	public static function getSelectData ($project_id,
										$sub_project_id,
										$selected,
										$onchange="inactive"){
		$html ='<label for="show_application">Documents:</label>';
		$html.='<select class="combobox"';
		if ($onchange=="active") {
			$html .= 'onchange="this.form.submit()"';
		}
		$html.= ' name="show_application">';
		$html.='<option value=""/> --All--';
		foreach(Data::getDataList($project_id,$sub_project_id) as $row):
			$html .= '<option value="'.$row['id'].'"';
			if ($row['id'] == $selected){ 
				$html .= " SELECTED ";
			}
			if ($row['version'] != ""){
				$html .=">".$row['application']." ".$row['type']." issue ".$row['version'];
			}
			else{
				$html .=">".$row['application']." ".$row['type'];
			}
		endforeach;
		$html .='</select>';
		return($html);
	}	
	public function getSelectType($selected,$onchange="inactive"){
		$html ='<label for="show_type">Type:</label>';
		$html.='<select class="combobox"';
		if ($onchange=="active") {
			$html .= 'onchange="this.form.submit()"';
		}
		$html.= ' name="show_type">';
		$html.='<option value=""/> --All--';
		foreach(Data::getTypeList() as $row):
			$html .= '<option value="'.$row['id'].'"';
			if ($row['id'] == $selected){ 
				$html .= " SELECTED ";
			}
			$html .=">".$row['name'].": ".$row['description'];
		endforeach;
		$html .='</select>';
		return($html);		
	}
	public static function isInGroup($data_id,$group_id){
		if (($data_id != "")&&($group_id != "")){
			// echo $data_id."<br/>".$group_id."<br/>";
			$result = Atomik_Db::find("data_cycle_type",
									array("id"=>$data_id,
										"group_id"=>$group_id));
			if ($result !== false){
				$found = true;
			}
			else{
				$found = false;
			}
		}
		else{
			$found = true;
		}		
		return($found);
	}
	public static function getGroupName($id){
		$name_array = Atomik_Db::find("group_type","id = ".$id);
		$name=$name_array['name'];
		return($name);
	}
	public static function getGroup(){
		$group = array(Specification => "Specification", 
						Design => "Design", 
						Plans => "Plans",
						Interfaces => "Interfaces",
						Certification => "Certification", 
						Safety => "Safety", 
						Production => "Production", 
						Verification => "Verification", 
						Configuration => "Configuration", 
						Notes => "Notes", 
						Methodology => "Methodology"					
						);	
		return($group);
	}
	public function getSelectTypeGroup($selected,
										$onchange="inactive",
										$group_id=""){
		$first_time = array("safety" => true, 
							"spec" => true, 
							"plan" => true,
							"certif" => true,
							"prod" => true, 
							"verif" => true, 
							"interface" => true, 
							"design" => true, 
							"config" => true, 
							"notes" => true, 
							"methodology" => true,
							"miscelleanous" => true						
							);
		$list_type = Data::getTypeList(true,$group_id);
		$html ='<label for="show_type">Type:</label>';
		$html.='<select class="combobox"';
		if ($onchange=="active") {
			$html .= 'onchange="this.form.submit()"';
		}
		$html.= ' name="show_type">';
		$html.='<option value=""/> --All--';
		foreach($list_type as $row):
			switch ($row['group_id']) {
				case Specification:
					if ($first_time["spec"]) {
						$first_time["spec"] = false;
						$html .= '<optgroup label="Specification">';
					}			
					break;
				case Design:
					if ($first_time["design"]){
						$first_time["design"] = false;
						$html .= "</optgroup>";
						$html .= '<optgroup label="Design">';
					}				
					break;
				case Plans:
					if ($first_time["plan"]){
						$first_time["plan"] = false;
						$html .= "</optgroup>";
						$html .= '<optgroup id="2" label="Plans" ondblclick="return submitGroup(2)"';
					}				
					break;	
				case Interfaces:
					if ($first_time["interface"]){
						$first_time["interface"] = false;
						$html .= "</optgroup>";
						$html .= '<optgroup label="Interface">';
					}				
					break;	
				case Certification:
					if ($first_time["certif"]){
						$first_time["certif"] = false;
						$html .= "</optgroup>";
						$html .= '<optgroup label="Certification">';
					}				
					break;
				case Safety:
					if ($first_time["safety"]){
						$first_time["safety"] = false;
						$html .= "</optgroup>";
						$html .= '<optgroup label="Safety">';
					}				
					break;
				case Production:
					if ($first_time["prod"]){
						$first_time["prod"] = false;
						$html .= "</optgroup>";
						$html .= '<optgroup label="Production">';
					}				
					break;	
				case Verification:
					if ($first_time["verif"]){
						$first_time["verif"] = false;
						$html .= "</optgroup>";
						$html .= '<optgroup label="Verification">';
					}				
					break;
				case Configuration:
					if ($first_time["config"]){
						$first_time["config"] = false;
						$html .= "</optgroup>";
						$html .= '<optgroup label="Configuration">';
					}				
					break;	
				case Notes:
					if ($first_time["notes"]){
						$first_time["notes"] = false;
						$html .= "</optgroup>";
						$html .= '<optgroup label="Notes">';
					}				
					break;
				case Methodology:
					if ($first_time["methodology"]){
						$first_time["methodology"] = false;
						$html .= "</optgroup>";
						$html .= '<optgroup label="Methodology">';
					}				
					break;	
				default:
				if ($first_time["miscelleanous"]){
					$first_time["miscelleanous"] = false;
					$html .= "<optgroup label='Miscelleanous'>";
					break;
				}
			}
			$html .= '<option value="'.$row['id'].'"';
			if ($row['id'] == $selected){ 
				$html .= " SELECTED ";
			}
			$html .=">".$row['name'].": ".$row['description'];

		endforeach;
		$html .= "</optgroup>";	
		$html .= '</select>';
		return($html);		
	}
	public function getPrevious(){
		/*
		 * Access to previous and next issue of the document
		 * To put in Data.class
		 */
		/* search previous date issue */
		$previous_data_found = false;
		if ($this->previous_data_id != "") {// && ($data->version != "")) {
			$item = Atomik_Db::find("bug_applications","id = ".$this->previous_data_id);
		}
		else {
			$item = false;
		}
		if($item){
			$previous_data_found = $item;
		}
		else {
			/* numeric (not alpha) */
			$count = 0;
			if (isfloat($this->version)){
				$dec = 0.1; /* IN */
				$decimals = 1;			
			}
			else{
				$dec = 1; /* ECE */
				$decimals = 0;
			}
			$previous_version = $this->version;	
			$is_draft = true;	
			$previous_draft_exists = true;			
			do {
				/* if ECE check for draft version */
				if ((preg_match("#^([0-9]+)D([0-9]+)$#",$this->version,$draft_match_numero)) && ($is_draft)&& ($previous_draft_exists)) {
					$dec_draft = $draft_match_numero[2] - 1;
					$previous_version = $draft_match_numero[1]."D".$dec_draft;
					$is_draft = false;
					//echo "Test 1: {$previous_version}<br/>";
				}
				else {
					$previous_draft_exists = false;
					$previous_version = $previous_version - $dec;
					/* keep one decimal if needed */
					$previous_version = sprintf('%01.'.$decimals.'f',$previous_version);
				}	
				$previous_data_found = Atomik_Db::find("bug_applications","application LIKE '%".$this->reference."%' AND version = '".$previous_version);//A("db:".$sql_query);
				//echo $sql_query."<br/>";
				$count++;
				if (($previous_data_found === false) && ($previous_draft_exists)) {
					/* reset version counter */
					$count = 0;
					$previous_draft_exists = false;
					$previous_version = $draft_match_numero[1];
				}
			} while (($previous_data_found == false)&&($count < 10));		
		}
		if ($previous_data_found) {
			$line  = '<td class="td_arrow" ><a href="'.Atomik::url('edit_data',array('id'=>$previous_data_found['id'])).'">';
			$line .= '<img src="assets/images/toundra/tooltipConnectorLeft.png" width="16" height="16" border="0" title="Previous version '.$previous_data_found['version'].'"></a></td>';
		}	
		else {
			$line = "";
		}
		return ($line);
	}
	public function getNext(){
		/* 
		 * search next date issue 
		 */
		$next_data_found = false;
		if ($this->id != "") {
			$next_data_found = Atomik_Db::find("bug_applications","previous_data_id = ".$this->id);
			if($next_data_found){
			}
			else if ($this->version != "") {
				/* numeric (not alpha) */
				$count = 0;
				/* Check id the version is a float */
				if (isfloat($this->version)){
					$inc = 0.1; /* IN */
					//echo "Test after: {$inc}<br/>";
					$decimals = 1;			
				}
				else{
					$inc = 1; /* ECE */
					$decimals = 0;
				}
				$first_draft_check = true;
				$next_draft_exists = true;
				$is_draft = true;
				$next_version = $this->version;		
				do {
					/* if ECE check for draft version */
					if ((preg_match("#^([0-9]+)D([0-9]+)$#",$this->version,$draft_match_numero)) && ($is_draft)&& ($next_draft_exists)) {
						$inc_draft = $draft_match_numero[2] + 1;
						$next_version = $draft_match_numero[1]."D".$inc_draft;
						$is_draft = false;
					}
					else {
						$next_draft_exists = false;
						$next_version = $next_version + $inc;
						/* keep one decimal if needed */
						$next_version = sprintf('%01.'.$decimals.'f',$next_version);
					}
					$count++;
					$next_data_found = Atomik_Db::find("bug_applications","application LIKE '%".$this->reference."%' AND version = '".$next_version);
					if (($next_data_found == false) && ($next_draft_exists)) {
						/* reset version counter */
						$count = 0;
						$next_draft_exists = false;
						/* decrement to get final version ex: 1D1 => 1 */
						$next_version = $draft_match_numero[1] - 1;
					}
				} while (($next_data_found == false)&&($count < 10));			
			}
		}
		if ($next_data_found) {
			$line  = '<td class="td_arrow"><a href="'.Atomik::url('edit_data',array('id'=>$next_data_found['id'])).'">';
			$line  .= '<img src="assets/images/toundra/tooltipConnectorRight.png" width="16" height="16" border="0" title="Next version '.$next_data_found['version'].'"></a></td>';
		}
		else {
			$line = false;
		}
		return ($line);
	}
	public function getExportFilename(){
		$today_date_underscore = date("Y").'_'.date("M").'_'.date("d");
		Atomik::needed("Project.class");
		$project = new Project();
		$project_name = $project->get_project_name($this->project_id);
		$sub_project_name = $project->get_sub_project_name($this->lru_id);
		if ($sub_project_name != "")$sub_project_name .= "_";
		$filename = $project_name."_".$sub_project_name."Data_list_".$today_date_underscore.".xlsx";
		$filename = str_replace("/","_",$filename);
		$filename = str_replace(" ","_",$filename);

		return($filename);
	}
	public function getExportTitle(){
		Atomik::needed("Project.class");
		$project = new Project();
		$project_name = $project->get_project_name($this->project_id);
		$sub_project_name = $project->get_sub_project_name($this->lru_id);
		if ($sub_project_name != "")$sub_project_name .= " ";
		$title = $project_name." ".$sub_project_name."Data List";
		return($title);
	}		
	public function exportXlsx($baseline_dir="",$list_prr=null){
		require_once "../excel/Classes/PHPExcel.php";
		require_once '../excel/Classes/PHPExcel/IOFactory.php';
		require_once '../excel/Classes/PHPExcel/Worksheet/RowIterator.php';
		Atomik::needed("ExportXls.class");
		include("app/includes/ExportXls.class.php");
		require_once("pChart/pData.class");  
		require_once("pChart/pChart.class");
		
		// Set the enviroment variable for GD
		putenv('GDFONTPATH=' . realpath('.'));
		error_reporting(E_ALL);
		$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
		PHPExcel_Settings::setCacheStorageMethod($cacheMethod);
		$qams_path = Atomik::url("qams");
		$file_template = dirname(__FILE__).DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."assets".DIRECTORY_SEPARATOR."template".DIRECTORY_SEPARATOR."data_list.xlsx";
		if (!file_exists($file_template)) {
			echo "Warning: Excel data list template is missing.<br/>".$file_template;
			exit();
			$objPHPExcel = new PHPExcel;
			$objPHPExcel->getActiveSheet()->setTitle('Header');
			$objWorksheet = $objPHPExcel->createSheet();
			$objWorksheet->setTitle('Data list');			
			$sheet_tab=array('Data list'=>0);
		}
		else {
			$objPHPExcel = PHPExcel_IOFactory::load($file_template);
			$sheet_tab=array('Data list'=>0);
		}
		$filename= "../result/".$baseline_dir.$this->getExportFilename();
		/*
		 *  Intro
		 */   
		$objPHPExcel->setActiveSheetIndex($sheet_tab['Data list']);
		$objPHPExcel->getActiveSheet()->setCellValue('A1', $this->getExportTitle());
		/* Draw header */
		$header=array("Reference", 
						"Issue", 
						"Type",
						"Author",
						"Description",
						"Released",
						"Status",
						"Acceptance",
						"Peer reviews");
		for($i=0;$i<count($header);$i++) {
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, 2, $header[$i]);
		}
		/* commnents */
		$objCommentRichText = $objPHPExcel->getActiveSheet()->getComment('H2')->getText()->createTextRun('Comment:');
		$objCommentRichText->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()->getComment('H2')->getText()->createTextRun("\r\n");
		$objPHPExcel->getActiveSheet()->getComment('H2')->getText()->createTextRun('Quality comments.');
		$objCommentRichText = $objPHPExcel->getActiveSheet()->getComment('I2')->getText()->createTextRun('Comment:');
		$objCommentRichText->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()->getComment('I2')->getText()->createTextRun("\r\n");
		$objPHPExcel->getActiveSheet()->getComment('I2')->getText()->createTextRun('This column is the list of peer reviews or validation matrix related to each documents.');
		/* draw thick border around the data */
		$list_data = $this->getData();
		$amount_data=count($list_data);
		$last_column = "I";
		$objPHPExcel->getActiveSheet()->getStyle('A2:'.$last_column.strval($amount_data + 2))->applyFromArray($style_encadrement);
		$row_counter = 3;		
		foreach($list_data as $doc) {
			if ($row_counter % 2) {
				/* alternate white and grey line color */
				$objPHPExcel->getActiveSheet()->getStyle('A'.$row_counter.':'.$last_column.$row_counter)->applyFromArray($style_white_line);
			}
			$this->get($doc['id']);
			$description = Tool::clean_text($this->description);
			$description = Tool::convert_html2txt($description);
			//echo $description;
			$acceptance = Tool::clean_text($this->acceptance);
			$acceptance = Tool::convert_html2txt($acceptance);
			$data = array ($this->reference,
							$this->version,
							$this->type,
							$this->author,
							$description,
							$this->date_published,
							$this->status,
							$acceptance);

			$index = 0;
			if ($this->status == "Approved") {
				$objPHPExcel->getActiveSheet()->getStyle('G'.$row_counter)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
				$objPHPExcel->getActiveSheet()->getStyle('G'.$row_counter)->getFill()->getStartColor()->setARGB('00FF00');
			}
			foreach ($data as $val) {
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($index++, $row_counter, $val);
			}
			/* put html link for reference */
			// $objPHPExcel->getActiveSheet()->getCell('A'.$row_counter)->getHyperlink()->setUrl('file://///Spar-nas2/commun%20qualite/Appere/qams/docs/'.$baseline_dir.$this->smart_filename);
			$objPHPExcel->getActiveSheet()->getCell('A'.$row_counter)->getHyperlink()->setUrl($this->smart_filename);
			$list_peer_reviews = "";
			if ($list_prr == null){
				// $sql_query = "SELECT peer_review_location.id,".
									// "name,".
									// "ext,".
									// "date".
									// " FROM peer_review_location ".
									// "LEFT OUTER JOIN bug_applications ON bug_applications.id = data_id ".
									// "WHERE data_id = {$this->id}";	
				// $result_prr = $this->db->db_query($sql_query)->fetchAll(PDO::FETCH_OBJ);
				$result_prr = $this->getExternalPeerReviewList();
				
				foreach($result_prr as $peer_reviews) {
						$list_peer_reviews .= $peer_reviews->name."\n";
				}
			}
			else {
				if (isset($list_prr[$this->id])){
					$result_prr = $list_prr[$this->id];
					foreach($result_prr as $peer_reviews) {
							$list_peer_reviews .= $peer_reviews."\n";
					}	
				}
			}
			if ($list_peer_reviews != ""){
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($index, $row_counter, $list_peer_reviews);
			}
			$row_counter++;
		}
		/* To apply an autofilter to a range of cells */
		$objPHPExcel->getActiveSheet()->setAutoFilter('A2:'.$last_column.'2');
		$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
		$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
		$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
		$objPHPExcel->getActiveSheet()->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(2, 2);
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save($filename);	
	}
	public static function copyExcel($filename){
		Atomik::needed("Db.class");
		Atomik::needed("User.class");
		Atomik::needed("Tool.class");
		$user = new User;
		// addslashes(
		$backup_dir = $user->getFolder();
		// var_dump($backup_dir);
		// var_dump(A('db_config/backup_dir'));
		$txt="";
		$base_path = dirname(__FILE__).DIRECTORY_SEPARATOR.
							"..".DIRECTORY_SEPARATOR.
							"..".DIRECTORY_SEPARATOR.
							"..".DIRECTORY_SEPARATOR;
		$src=$base_path."result".DIRECTORY_SEPARATOR.$filename;
		
		$dest=$backup_dir.DIRECTORY_SEPARATOR."docs".DIRECTORY_SEPARATOR.$filename;
		// echo "FILE SRC:".$src."<br/>";
		// echo "FILE DEST:".$dest."<br/>";
		if (file_exists($src)){
			$res = Tool::copy($src,$dest);
			echo $res;			
		}
		else{
			$txt .= "Failed to copy {$filename} document.<br/>";
		}
		return($txt);		
	}
}

function isfloat($f) {
	if (preg_match("#^([0-9]+)\.([0-9]+)$#",$f))  {
		$float = true;
	}
	else {
		$float = false;
	}
	return ($float);
	//return (is_float($f));
	//return ($f == (string)(float)$f);
}
